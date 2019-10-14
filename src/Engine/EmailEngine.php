<?php
declare(strict_types = 1);
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

use Origin\Email\Email;
use InvalidArgumentException;

class EmailEngine extends BaseEngine
{
    /**
     * Holds the last email sent
     *
     * @var \Origin\Email\Message;
     */
    protected $lastEmail = null;

    /**
     * Default configuration
     *
     * @var array
     */
    protected $defaultConfig = [
        'to' => null, // email address string/or array [email => name]
        'from' => null, // email address
        'levels' => [],
        'channels' => [],
        // email settings
        'host' => 'localhost',
        'port' => 25,
        'username' => null,
        'password' => null,
        'tls' => false,
        'ssl' => false,
        'timeout' => 30,
        'debug' => false
    ];

    /**
     * To address [email,name]
     *
     * @var array
     */
    protected $to = null;

    /**
     * To address [email, name]
     *
     * @var array
     */
    protected $from = null;

    /**
     * To reduce the risk of issues with this, lets do some simple sanity checks
     * when the logger is created
     *
     * @param array $config
     * @return void
     */
    public function initialize(array $config) : void
    {
        $this->to = $this->convertEmailSetting($this->config('to'));
        if (! $this->validateEmail($this->to)) {
            throw new InvalidArgumentException('Invalid Email Address for To.');
        }
        $this->from = $this->convertEmailSetting($this->config('from'));
        if (! $this->validateEmail($this->from)) {
            throw new InvalidArgumentException('Invalid Email Address for From.');
        }
    }

    /**
     * Convert an email to/from to special format
     *
     * 'hello@example.com' or ['hello@example.com'] or ['hello@example.com'=>$name] to [$email,$name]
     *
     * @param string|array|null $setting
     * @return array|null
     */
    protected function convertEmailSetting($setting) : ?array
    {
        if ($setting === null) {
            return null;
        }
        if (is_string($setting)) {
            $setting = [$setting,null];
        }
        $email = key($setting);
        $name = $setting[$email];

        if (is_int($email)) {
            $email = $name;
            $name = null;
        }
        
        return [$email,$name];
    }

    /**
     * A basic email validation to ensure params are set
     *
     * @param array|null
     * @return bool
     */
    protected function validateEmail(array $email = null) : bool
    {
        if ($email === null or empty($email[0])) {
            return false;
        }

        return (bool) filter_var($email[0], FILTER_VALIDATE_EMAIL);
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
        $subject = 'Log: ' . strtoupper($level);

        $this->send($subject, $message);
    }

    /**
     * Sends the email
     *
     * @param string $subject
     * @param string $message
     * @return boolean
     */
    protected function send(string $subject, string $message) : bool
    {

        /**
         * Prevent recursion
         */
        try {
            $config = $this->config;
            if (! empty($config['debug'])) {
                $config = ['engine' => 'Test'];
            }
            $email = new Email($config);
            $email->to($this->to[0], $this->to[1])
                ->from($this->from[0], $this->from[1])
                ->subject($subject)
                ->htmlMessage("<p>{$message}</p>")
                ->textMessage($message)
                ->format('both');
            $this->lastEmail = $email->send();
        } catch (\Exception $e) {
            // Don't log failures since this will create recursion
            return false;
        }

        return true;
    }
}
