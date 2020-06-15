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

namespace Origin\Test\Log;

use BadMethodCallException;
use InvalidArgumentException;
use Origin\Log\Engine\FileEngine;

class MockFileEngine extends FileEngine
{
    public function getSize()
    {
        return $this->maxSize;
    }
}

class FileEngineTest extends \PHPUnit\Framework\TestCase
{
    public function testLog()
    {
        $file = sys_get_temp_dir() . '/application.log';

        $engine = new FileEngine(['file' => $file]);
        $id = uniqid();
        $this->assertNull($engine->log('error', 'Error code {value}', ['value' => $id]));
        $log = file_get_contents($file);
        $date = date('Y-m-d G:i:s');
        $this->assertStringContainsString("[{$date}] application ERROR: Error code {$id}", $log);
    }

    public function testLogRotationDelete()
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        mkdir($path);

        $file = $path . '/application.log';

        $engine = new FileEngine(['file' => $file,'size' => 1, 'rotate' => 0]);

        $engine->log('debug', 'line 1');
        $this->assertStringContainsString('line 1', file_get_contents($file));
        
        $engine->log('debug', 'line 2');
        $this->assertStringNotContainsString('line 1', file_get_contents($file));
        $this->assertStringContainsString('line 2', file_get_contents($file));
    }

    public function testSizeToBytes()
    {
        $file = sys_get_temp_dir() . '/application.log';

        $engine = new MockFileEngine(['file' => $file,'size' => '10MB']);
        $this->assertEquals(10485760, $engine->getSize());

        $this->expectException(InvalidArgumentException::class);
        new FileEngine(['file' => $file,'size' => '1 foo']);
    }

    public function testLogRotation3()
    {
        $path = sys_get_temp_dir() . '/' . uniqid();
        mkdir($path);

        $file = $path . '/application.log';

        $engine = new FileEngine(['file' => $file,'size' => 1, 'rotate' => 3]);

        $engine->log('debug', 'line 1');
        $this->assertStringContainsString('line 1', file_get_contents($file));
     
        $engine->log('debug', 'line 2');
        $this->assertStringNotContainsString('line 1', file_get_contents($file));
        $this->assertStringContainsString('line 2', file_get_contents($file));

        $engine->log('debug', 'line 3');
        $this->assertStringNotContainsString('line 1', file_get_contents($file));
        $this->assertStringNotContainsString('line 2', file_get_contents($file));
        $this->assertStringContainsString('line 3', file_get_contents($file));

        $engine->log('debug', 'line 4');
        $this->assertStringNotContainsString('line 1', file_get_contents($file));
        $this->assertStringNotContainsString('line 2', file_get_contents($file));
        $this->assertStringNotContainsString('line 3', file_get_contents($file));
        $this->assertStringContainsString('line 4', file_get_contents($file));

        $engine->log('debug', 'line 5');
        $this->assertStringNotContainsString('line 1', file_get_contents($file));
        $this->assertStringNotContainsString('line 2', file_get_contents($file));
        $this->assertStringNotContainsString('line 3', file_get_contents($file));
        $this->assertStringNotContainsString('line 4', file_get_contents($file));
        $this->assertStringContainsString('line 5', file_get_contents($file));

        /**
         * There should be 4 files, 1 log and 3 rotations
         */
        $files = glob($path . '/application.log*');
        $this->assertEquals(4, count($files));
    }

    public function testNotFile()
    {
        $this->expectException(BadMethodCallException::class);
        new FileEngine();
    }

    public function testInvalidFile()
    {
        $this->expectException(InvalidArgumentException::class);
        new FileEngine(['file' => '/somewhere/that/does/not/exists']);
    }
}
