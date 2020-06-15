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

use Origin\Log\Log;
use Origin\Log\Logger;
use InvalidArgumentException;
use Origin\Log\Engine\BaseEngine;
use Origin\Log\Engine\FileEngine;
use Psr\Log\InvalidArgumentException as LogInvalidArgumentException;

class LoggerTestEngine extends BaseEngine
{
    protected $data = null;

    public function log(string $level, string $message, array $context = []): void
    {
        $this->data = $this->format($level, $message, $context) . "\n";
    }
    
    public function getLog()
    {
        return $this->data;
    }
}

class Foo
{
    public function __toString()
    {
        return 'foo';
    }
}

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testEmergency()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->emergency('This is an emergency');
        $this->assertStringContainsString("[{$date}] application EMERGENCY: This is an emergency", $logger->engine('default')->getLog());
       
        $logger->emergency('This is an {value}', ['value' => 'emergency']);
        $this->assertStringContainsString("[{$date}] application EMERGENCY: This is an emergency", $logger->engine('default')->getLog());
    }
    public function testAlert()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->alert('Some system message');
        $this->assertStringContainsString("[{$date}] application ALERT: Some system message", $logger->engine('default')->getLog());
       
        $logger->alert('Some system message with the value:{value}', ['value' => 'not-important']);
        $this->assertStringContainsString("[{$date}] application ALERT: Some system message with the value:not-important", $logger->engine('default')->getLog());
    }
    public function testCritical()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->critical('This is critical');
        $this->assertStringContainsString("[{$date}] application CRITICAL: This is critical", $logger->engine('default')->getLog());
       
        $logger->critical('This is {value}', ['value' => 'critical']);
        $this->assertStringContainsString("[{$date}] application CRITICAL: This is critical", $logger->engine('default')->getLog());
    }
    public function testError()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->error('Some system message');
        $this->assertStringContainsString("[{$date}] application ERROR: Some system message", $logger->engine('default')->getLog());
       
        $logger->error('Some system message with the value:{value}', ['value' => 'not-important']);
        $this->assertStringContainsString("[{$date}] application ERROR: Some system message with the value:not-important", $logger->engine('default')->getLog());
    }
    public function testWarning()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->warning('Some system message');
        $this->assertStringContainsString("[{$date}] application WARNING: Some system message", $logger->engine('default')->getLog());
       
        $logger->warning('Some system message with the value:{value}', ['value' => 'not-important']);
        $this->assertStringContainsString("[{$date}] application WARNING: Some system message with the value:not-important", $logger->engine('default')->getLog());
    }
    public function testNotice()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);

        $date = date('Y-m-d G:i:s');
        $logger->notice('Some system message');
        $this->assertStringContainsString("[{$date}] application NOTICE: Some system message", $logger->engine('default')->getLog());
       
        $logger->notice('Some system message with the value:{value}', ['value' => 'not-important']);
        $this->assertStringContainsString("[{$date}] application NOTICE: Some system message with the value:not-important", $logger->engine('default')->getLog());
    }
    public function testInfo()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->info('Some system message');
        $this->assertStringContainsString("[{$date}] application INFO: Some system message", $logger->engine('default')->getLog());
       
        $logger->info('Some system message with the value:{value}', ['value' => 'not-important']);
        $this->assertStringContainsString("[{$date}] application INFO: Some system message with the value:not-important", $logger->engine('default')->getLog());
    }
    public function testDebug()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $date = date('Y-m-d G:i:s');
        $logger->debug('Some system message');
        $this->assertStringContainsString("[{$date}] application DEBUG: Some system message", $logger->engine('default')->getLog());
       
        $logger->debug('Some system message with the value:{value}', ['value' => 'not-important']);
        $this->assertStringContainsString("[{$date}] application DEBUG: Some system message with the value:not-important", $logger->engine('default')->getLog());
    }

    public function testConfigObjectAfterCreate()
    {
        $date = date('Y-m-d G:i:s');

        $logger = new Logger();
        $logger->config('default', [
            'className' => LoggerTestEngine::class
        ]);

        $logger->debug('Some system message');
        $this->assertStringContainsString("[{$date}] application DEBUG: Some system message", $logger->engine('default')->getLog());

        $file = sys_get_temp_dir() . '/' . uniqid();
        $logger->config('my-file', [
            'className' => FileEngine::class,
            'file' => $file
        ]);

        $date = date('Y-m-d G:i:s');
        $logger->error('Another one bytes the dust');
        $this->assertStringContainsString(
            "[{$date}] application ERROR: Another one bytes the dust",
            file_get_contents($file)
        );
    }




    public function testCastToString()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $logger->log('debug', new Foo());
        $date = date('Y-m-d G:i:s');
        $this->assertStringContainsString("[{$date}] application DEBUG: foo", $logger->engine('default')->getLog());
    }

    public function testInvalidLevel()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $this->expectException(LogInvalidArgumentException::class);
        $logger->log('foo', 'using an invalid level');
    }

    public function testChannel()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $logger->debug('Some system message', ['channel' => 'custom']);
        $date = date('Y-m-d G:i:s');
        $this->assertStringContainsString("[{$date}] custom DEBUG: Some system message", $logger->engine('default')->getLog());
    }

    public function testLevelsRestriction()
    {
        $logger = new Logger([
            'className' => LoggerTestEngine::class,
            'levels' => ['critical']
            ]);
   
        $logger->debug('This will not be logged');
        $this->assertEmpty($logger->engine('default')->getLog());

        $logger->critical('This will be logged');
        $this->assertStringContainsString('This will be logged', $logger->engine('default')->getLog());
    }

    public function testChannelsRestriction()
    {
        $logger = new Logger([
            'className' => LoggerTestEngine::class,
            'channels' => ['payments'],
        ]);

        $logger->debug('This will not be logged', ['channel' => 'application']);
        $this->assertEmpty($logger->engine('default')->getLog());
        $logger->critical('This will be logged', ['channel' => 'payments']);
        $this->assertStringContainsString('This will be logged', $logger->engine('default')->getLog());
    }

    public function testCustomData()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $logger->info('User registered', ['username' => 'pinkpotato','channel' => 'custom']);
        $date = date('Y-m-d G:i:s');
        $this->assertStringContainsString("[{$date}] custom INFO: User registered {\"username\":\"pinkpotato\"}", $logger->engine('default')->getLog());
    }

    public function testInvalidClassName()
    {
        $this->expectException(InvalidArgumentException::class);
        $logger = new Logger([
            'default' => ['className' => 'Origin\DoesNotExist\FooEngine']
        ]);
        $logger->debug('wont work');
    }
    
    public function testInvalidLogLevel()
    {
        $logger = new Logger(['className' => LoggerTestEngine::class]);
        $this->expectException(LogInvalidArgumentException::class);
        $logger->log('informational', 'This is an invalid log level');
    }

    public function testInvalidEngine()
    {
        $this->expectException(InvalidArgumentException::class);
        $logger = new Logger(['className' => 'FooBar']);
    }
}
