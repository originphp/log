<?php

declare(strict_types=1);
/**
 * OriginPHP Framework
 * Copyright 2018 - 2019 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Origin\Log\Engine;

class ConsoleEngine extends BaseEngine
{
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

    public function initialize(array $config): void
    {
        $this->stream = fopen($this->config('stream'), 'w');
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
        $message = "<{$level}>{$message}</{$level}>";
        $this->write($message);
    }
    /**
     * Wrapper for testing
     *
     * @param string $message
     * @return void
     */
    protected function write(string $message) : void
    {
        fwrite($this->stream, $message);
    }

    public function __destruct()
    {
        fclose($this->stream);
    }
}
