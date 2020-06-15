<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
declare(strict_types=1);
namespace Origin\Log;

use Psr\Log\LoggerInterface;
use Origin\Log\Engine\BaseEngine;
use InvalidArgumentException;
use Origin\Configurable\InstanceConfigurable as Configurable;

/**
 * A PSR-3 Logger
 *
 * @link https://www.php-fig.org/psr/psr-3/
 */
class Logger implements LoggerInterface
{
    use Configurable {
        setConfig as protected traitSetConfig;
    }

    /**
     * Logging engines
     *
     * @var array|null
     */
    private $loaded = null;

    public function __construct(array $config = [])
    {
        if ($config) {
            $config = ['default' => $config];
            $this->config($config);
            $this->loadEngines($config);
        }
    }

    /**
     * Sets the config
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    protected function setConfig($key, $value = null) : void
    {
        $this->traitSetConfig($key, $value);
        $this->loaded = null;
    }

    /**
    * System is unusable.
    *
    * @param string $message
    * @param array $context
    * @return void
    */
    public function emergency($message, array $context = [])
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        if (! in_array($level, ['debug','info','notice','warning','error','critical','alert','emergency'])) {
            throw new \Psr\Log\InvalidArgumentException(sprintf('Invalid level `%s`', $level));
        }

        if (is_object($message) && method_exists($message, '__toString')) {
            $message = (string) $message;
        }

        if ($this->loaded == null) {
            $this->loadEngines($this->config);
        }

        $this->write($level, $message, $context);
    }

    /**
     * Log handler
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function write(string $level, string $message, array $context): void
    {
        $context += ['channel' => 'application'];
        $channel = $context['channel'];
        unset($context['channel']);

        foreach ($this->loaded as $logger) {
            $levels = $logger->levels();

            if (! empty($levels) && ! in_array($level, $levels)) {
                continue;
            }
            $channels = $logger->channels();
            if (! empty($channels) && ! in_array($channel, $channels)) {
                continue;
            }
            $logger->channel($channel);
            $logger->$level($message, $context);
        }
    }

    /**
     * Gets a configured logging engine
     *
     * @param string $name
     * @return \Origin\Log\BaseEngine
     */
    public function engine(string $name): BaseEngine
    {
        if (isset($this->loaded[$name])) {
            return $this->loaded[$name];
        }
        throw new InvalidArgumentException(sprintf('The log configuration `%s` does not exist.', $name));
    }

    /**
     * Loads the engines
     *
     * @return void
     */
    private function loadEngines(array $engines): void
    {
        $this->loaded = [];
        foreach ($engines as $name => $config) {
            $this->loadEngine($name, $config);
        }
    }

    private function loadEngine(string $name, array $config): void
    {
        if (isset($config['engine'])) {
            $config['className'] = __NAMESPACE__  . "\Engine\\{$config['engine']}Engine";
        }
        if (empty($config['className']) || ! class_exists($config['className'])) {
            throw new InvalidArgumentException("Log engine for {$name} could not be found");
        }

        $this->loaded[$name] = new $config['className']($config);
    }
}
