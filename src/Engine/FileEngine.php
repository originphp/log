<?php
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
declare(strict_types = 1);
namespace Origin\Log\Engine;

use BadMethodCallException;
use InvalidArgumentException;

class FileEngine extends BaseEngine
{
    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaultConfig = [
        'file' => null,
        'levels' => [],
        'channels' => [],
    ];
    
    public function initialize(array $config) : void
    {
        if (empty($config['file'])) {
            throw new BadMethodCallException('File not provided');
        }

        if (! is_file($config['file']) and ! $this->createLogFile($config['file'])) {
            throw new InvalidArgumentException('Unable to create log file');
        }
    }
    
    /**
      * Workhorse for the logging methods
      *
      * @param string $level e.g debug, info, notice, warning, error, critical, alert, emergency.
      * @param string $message 'this is a {what}'
      * @param array $context  ['what'='string']
      * @return void
      */
    public function log(string $level, string $message, array $context = []) : void
    {
        $message = $this->format($level, $message, $context) . "\n";
        file_put_contents($this->config('file'), $message, FILE_APPEND);
    }

    /**
    * @param string $file
    * @return boolean
    */
    private function createLogFile(string $file) : bool
    {
        return is_dir(dirname($file)) and touch($file) and chmod($file, 0775);
    }
}
