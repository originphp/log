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
declare(strict_types = 1);
namespace Origin\Log\Engine;

use BadMethodCallException;
use InvalidArgumentException;

class FileEngine extends BaseEngine
{
    /**
     * Default configuration
     *
     * Log Rotation settings should be similar/same as logrotate
     * @see https://linux.die.net/man/8/logrotate
     *
     * @var array
     */
    protected $defaultConfig = [
        'file' => null,
        'levels' => [],
        'channels' => [],
        'size' => 10485760,
        'rotate' => 3
    ];

    /**
     * @var int|null
     */
    protected $maxSize;

    /**
     * @var boolean
     */
    private $rotate = false;
    
    protected function initialize(array $config): void
    {
        if (empty($config['file'])) {
            throw new BadMethodCallException('File not provided');
        }

        if (! is_file($config['file']) && ! $this->createLogFile($config['file'])) {
            throw new InvalidArgumentException('Unable to create log file');
        }

        $size = $this->config('size'); // #! important
        if (! empty($size)) {
            $this->maxSize = is_string($size) && ! ctype_digit($size) ? $this->convertToBytes($size) : (int) $size;
            $this->rotate = true;
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
    public function log(string $level, string $message, array $context = []): void
    {
        $message = $this->format($level, $message, $context) . "\n";

        $file = $this->config('file');
        
        if ($this->rotate) {
            clearstatcache(true, pathinfo($file, PATHINFO_DIRNAME));
        
            if (file_exists($file) && filesize($file) >= $this->maxSize) {
                $this->rotateLogfile($file, (int) $this->config('rotate'));
            }
        }
       
        file_put_contents($file, $message, FILE_APPEND);
    }

    /**
     * Handles the rotation of the log file
     *
     * @param string $file
     * @param integer $rotate
     * @return boolean
     */
    private function rotateLogfile(string $file, int $rotate): bool
    {
        if ($rotate === 0) {
            return unlink($file);
        }
        
        $pattern = $file . '.*';

        $files = glob($pattern); // rotate
        $count = count($files);

        for ($i = $count; $i > 0 ; $i --) {
            $logFile = str_replace('*', (string) $i, $pattern);

            if ($i >= $rotate) {
                if (file_exists($logFile)) {
                    unlink($logFile);
                }
                continue;
            }
            //  rename application.log.1 -> application.log.2
            rename($logFile, str_replace('*', (string) $i + 1, $pattern));
        }
        // rename application.log -> application.log.1
        return rename($file, str_replace('*', '1', $pattern));
    }

    /**
    * @param string $file
    * @return boolean
    */
    private function createLogFile(string $file): bool
    {
        return is_dir(dirname($file)) && touch($file) && chmod($file, 0664);
    }

    /**
     * Convert 1MB/1GB to bytes
     *
     * @param string $value
     * @return integer
     */
    private function convertToBytes(string $value): int
    {
        preg_match('/(?P<value>[0-9]+)(?P<unit>MB|GB)/', $value, $matches);

        if (! $matches) {
            throw new InvalidArgumentException('Invalid value or unit type.');
        }
        $multipler = $matches['unit'] === 'MB' ? 1048576 : 1073741824;

        $bytes = $matches['value'] * $multipler;

        return $bytes;
    }
}
