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

namespace Origin\Test\Core;

use Origin\Log\Engine\ConsoleEngine;

class MockConsoleEngine extends ConsoleEngine
{
    protected $output = '';
    protected function write(string $message) : void
    {
        $this->output .= $message;
    }
    public function getOutput()
    {
        return $this->output;
    }
}
class ConsoleEngineTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultConfig()
    {
        $engine = new MockConsoleEngine();
        $this->assertEquals('php://stderr', $engine->config('stream'));
        $this->assertEquals([], $engine->config('levels'));
        $this->assertEquals([], $engine->config('channels'));
    }
    public function testLog()
    {
        $engine = new MockConsoleEngine();
        $id = uniqid();
        $engine->log('error', 'Error code {value}', ['value' => $id]);
        $date = date('Y-m-d G:i:s');
        $this->assertStringContainsString("[{$date}] application ERROR: Error code {$id}", $engine->getOutput());
    }

    public function testNoErrorsWhenWriting()
    {
        $this->assertNull((new ConsoleEngine())->log('debug', 'Checking no errors when trying to write'));
    }
}
