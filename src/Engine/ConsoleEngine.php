<?php
/**
 * OriginPHP Framework
 * Copyright 2018 - 2021 Jamiel Sharief.
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
namespace Origin\Log\Engine;

class ConsoleEngine extends BaseEngine
{
    /**
     * ANSI color mapping
     *
     * @internal Some systems do not support 9x/1xx colors, including TravisCI.
     * @var array
     */
    protected $colors = [
        'debug' => '37',
        'info' => '32',
        'notice' => '36',
        'warning' => '33',
        'error' => '31',
        'critical' => '1;31',
        'alert' => '41;37',
        'emergency' => '5;41;37',
    ];

    /**
     * Holds the resource
     *
     * @var resource
     */
    protected $stream = null;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaultConfig = [
        'stream' => 'php://stderr',
        'levels' => [],
        'channels' => [],
    ];

    protected $supportsAnsi = false;

    protected function initialize(array $config): void
    {
        $this->stream = fopen($this->config('stream'), 'w');
        $this->supportsAnsi = function_exists('posix_isatty') and posix_isatty($this->stream);
    }

    /**
     * Workhorse for the logging methods
     *
     * @param string $level e.g debug, info, notice, warning, error, critical, alert, emergency.
     * @param string $message 'this is a {what}'
     * @param array $context  ['what'='string']
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $message = $this->format($level, $message, $context);
        if ($this->supportsAnsi) {
            $message = $this->colorize($level, $message);
        }
        $this->write($message . "\n");
    }

    protected function colorize(string $level, string $message)
    {
        $code = $this->colors[$level];

        return "\033[{$code}m{$message}\033[0m";
    }

    /**
     * Wrapper for testing
     *
     * @param string $message
     * @return void
     */
    protected function write(string $message): void
    {
        fwrite($this->stream, $message);
    }

    public function __destruct()
    {
        fclose($this->stream);
    }
}
