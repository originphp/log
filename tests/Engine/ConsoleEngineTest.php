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

    public function testColorFormat()
    {
        $engine = new MockConsoleEngine();
   
        $engine->log('error', 'An error has occured.');
        $message = '[' . date('Y-m-d G:i:s'). '] application ERROR: An error has occured.';
        $this->assertStringContainsString("\033[31m{$message}\033[0m", $engine->getOutput());
        $this->demo();
    }

    public function demo()
    {
        system('cls');
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
        $engine = new ConsoleEngine();
        $engine->log('debug', $lorem);
        $engine->log('info', $lorem);
        $engine->log('notice', $lorem);
        $engine->log('warning', $lorem);
        $engine->log('error', $lorem);
        $engine->log('critical', $lorem);
        $engine->log('alert', $lorem);
        $engine->log('emergency', $lorem);
        $this->assertTrue(true);
    }
}
