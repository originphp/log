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

use Origin\Log\Log;
use Origin\Log\LogTrait;

class Controller
{
    use LogTrait;
}

class LogTraitTest extends \PHPUnit\Framework\TestCase
{
    public function setUp() :void
    {
        Log::reset();
    }
    public function testTrait()
    {
        $file = sys_get_temp_dir() . '/' . uniqid() . '.log';
        Log::config('default', ['engine' => 'File','file' => $file]);
        $controller = new Controller();
        $id = uniqid();
        $controller->log('debug', 'XXX {id} ', ['id' => $id]);
        $this->assertStringContainsString($id, file_get_contents($file));
    }
}
