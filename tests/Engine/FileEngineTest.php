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

namespace Origin\Test\Log;

use BadMethodCallException;
use InvalidArgumentException;
use Origin\Log\Engine\FileEngine;

class FileEngineTest extends \PHPUnit\Framework\TestCase
{
    public function testLog()
    {
        $file = sys_get_temp_dir() . '/application.log';

        $engine = new FileEngine(['file'=>$file]);
        $id = uniqid();
        $this->assertNull($engine->log('error', 'Error code {value}', ['value' => $id]));
        $log = file_get_contents($file);
        $date = date('Y-m-d G:i:s');
        $this->assertStringContainsString("[{$date}] application ERROR: Error code {$id}", $log);
    }

    public function testNotFile()
    {
        $this->expectException(BadMethodCallException::class);
        new FileEngine();
    }

    public function testInvalidFile()
    {
        $this->expectException(InvalidArgumentException::class);
        new FileEngine(['file'=>'/somewhere/that/does/not/exists']);
    }
}
