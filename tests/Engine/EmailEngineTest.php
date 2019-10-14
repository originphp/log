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

use Origin\Email\Email;
use InvalidArgumentException;
use Origin\Log\Engine\EmailEngine;

class MockEmailEngine extends EmailEngine
{
    public $emailSent = false;

    public function email()
    {
        return $this->lastEmail;
    }

    public function emailSetting($mixed)
    {
        return $this->convertEmailSetting($mixed);
    }

    protected function send(string $subject, string $message) : bool
    {
        return $this->emailSent = parent::send($subject, $message);
    }
}
class EmailEngineTest extends \PHPUnit\Framework\TestCase
{
    public function testDefaultConfig()
    {
        $engine = new MockEmailEngine(['to' => 'foo@example.com','from' => 'foo@example.com','debug' => true]);
        $this->assertEquals(true, $engine->config('debug'));
        $this->assertEquals([], $engine->config('levels'));
        $this->assertEquals([], $engine->config('channels'));
    }
    public function testInvalidToAddress()
    {
        $this->expectException(InvalidArgumentException::class);
        $engine = new MockEmailEngine(['from' => 'foo@example.com']);
    }
    public function testInvalidToAddressNotNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $engine = new MockEmailEngine(['to' => 'foo','from' => 'foo@example.com']);
    }
    public function testAccountFromNotSet()
    {
        $this->expectException(InvalidArgumentException::class);
        $engine = new MockEmailEngine(['to' => 'foo@example.com','debug' => true]);
    }

    public function testAccountFromInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $engine = new MockEmailEngine(['to' => 'foo@example.com','from' => 'foo','debug' => true]);
    }
   
    public function testLog()
    {
        $engine = new MockEmailEngine([
            'to' => 'you@example.com',
            'from' => 'me@example.com',
            'debug' => true,
        ]);
        $id = uniqid();
        $this->assertNull($engine->log('error', 'Error code {value}', ['value' => $id]));
        $date = date('Y-m-d G:i:s');
        $message = $engine->email()->message();
        $this->assertStringContainsString("[{$date}] application ERROR: Error code {$id}", $message);
        $this->assertStringContainsString('From: me@example.com', $message);
        $this->assertStringContainsString('To: you@example.com', $message);
    }

    public function testLogEmailArray()
    {
        $engine = new MockEmailEngine([
            'to' => ['you@example.com' => 'jimbo'],
            'from' => ['me@example.com'],
            'debug' => true,
        ]);
        $id = uniqid();
     
        $this->assertNull($engine->log('error', 'Error code {value}', ['value' => $id]));
        $date = date('Y-m-d G:i:s');

        $message = $engine->email()->message();
        $this->assertStringContainsString("[{$date}] application ERROR: Error code {$id}", $message);
        $this->assertStringContainsString('From: me@example.com', $message);
        $this->assertStringContainsString('jimbo <you@example.com>', $message);
    }

    /**
     * This has invalid credentials and will cause the email
     *
     * @return void
     */
    public function testSendFailureException()
    {
        $engine = new MockEmailEngine([
            'to' => 'foo@example.com',
            'from' => 'foo@example.com',
            'host' => 'smtp.gmail.com',
            'password' => 'your_password'
        ]);
        $this->assertNull($engine->log('error', 'This will go into a blackhole'));
    }

    public function testEmailSetting()
    {
        $engine = new MockEmailEngine([
            'to' => 'foo@example.com',
            'from' => 'foo@example.com',
            'host' => 'smtp.gmail.com',
            'password' => 'your_password'
        ]);
        $this->assertEquals(null, $engine->emailSetting(null));
        $this->assertEquals(['foo@example.com',null], $engine->emailSetting('foo@example.com'));
        $this->assertEquals(['foo@example.com',null], $engine->emailSetting(['foo@example.com']));
        $this->assertEquals(['foo@example.com','name'], $engine->emailSetting(['foo@example.com' => 'name']));
    }

    public function testSending()
    {
        if (! $this->env('EMAIL_USERNAME') or ! $this->env('EMAIL_PASSWORD')) {
            $this->markTestSkipped(
                'EMAIL username and password not setup'
            );
        }
        $config = [
            'to' => $this->env('EMAIL_ADDRESS'),
            'from' => $this->env('EMAIL_ADDRESS'),
            'host' => $this->env('EMAIL_HOST'),
            'port' => $this->env('EMAIL_PORT'),
            'username' => $this->env('EMAIL_USERNAME'),
            'password' => $this->env('EMAIL_PASSWORD'),
            'ssl' => (bool) $this->env('EMAIL_SSL'),
            'tls' => (bool) $this->env('EMAIL_TLS'),
        ];
       
        $engine = new MockEmailEngine($config);
        $engine->log('debug', 'Just testing that an actual send works');
        $this->assertTrue($engine->emailSent);
    }

    /**
    * Work with ENV vars
    *
    * @param string $key
    * @return mixed
    */
    protected function env(string $key)
    {
        $result = getenv($key);

        return $result ? $result : null;
    }
}
