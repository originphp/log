# Log

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/log.svg?branch=master)](https://travis-ci.org/originphp/log)
[![coverage](https://coveralls.io/repos/github/originphp/log/badge.svg?branch=master)](https://coveralls.io/github/originphp/log?branch=master)

There are 4 built in Log Engines, and it is easy to implement your own. You can use the `Log` static class or PSR-3 `Logger` class.

## Installation

To install this package

```linux
$ composer require originphp/log
```

- `File` - Logs messages to files
- `Console` - Displays the log messages to the console screen
- `Email` - Sends log messages via email
- `Syslog` - Recommended for production systems

First you need to configure the Log library, in your application bootstrap or configuration.

```php
Log::config('default', [
    'engine' => 'File',
    'file' => '/var/www/logs/application.log'
]);
```

Then to log

```php
use Origin\Log\Log;
Log::error('Something has gone wrong.');
```

This will produce something like this in `/var/www/logs/application.log`.

```
[2019-03-10 13:37:49] application ERROR: Something has gone wrong.
```

## Channels

To group your log messages, set a channel name.

```php
use Origin\Log\Log;
Log::error('Something has gone wrong.',['channel'=>'invoices']);
```

This will create a log entry like this

```
[2019-03-10 13:37:49] invoices ERROR: Something has gone wrong.
```
## Placeholders

You can also use placeholders in the message.

```php
Log::info('Email sent to {email}',['email'=>'donny@example.com']);
```

## Adding data to messages

After placeholders any have been replaced, any remaining data will be converted to a JSON string.

```php
Log::info('User registered',['username'=>'pinkpotato']);

```

Which will output like this

```
[2019-03-10 13:37:49] application INFO: User registered {"username":"pinkpotato"}
```

## Log Levels

Log works with all the different levels as defined in the [RFC 5424 specifications](https://tools.ietf.org/html/rfc5424).

```php 
Log::emergency('system is unusable');
Log::alert('action must be taken immediately');
Log::critical('a critical condition');
Log::error('an error has occured');
Log::warning('warning low disk space');
Log::notice('normal, but significant, condition');
Log::info('informational message');
Log::debug('debug-level message');
```

## Configuration

You can use a single engine or multiple engines at once, and you can also customize which levels to Log on.

### File Engine

To configure the file engine logging

```php
use Origin\Log\Log;
Log::config('file',[
    'engine' => 'File',
    'file' => '/var/www/logs/application.log',
    'size' => 10485760, // or 10MB,
    'rotate' => 3
    
]);
```

Options for the File Engine are:

- file: file with full path
- levels: default `[]`. If you want to restrict this configuration to only certain levels, add the levels to an array e.g. `['critical','emergency','alert']`
- channels: default `[]`. If you want to restrict this configuration to only certain channels, add the channels to an array e.g. `['invoices','payments']`
- size: number of bytes when to rotate log, or you can use MB or GB, e.g. 10MB. To disable log rotation set this to `0`.
- rotate: the number of times to rotate, if set to 0, then it will delete once it reaches that size.

### Email Engine

To configure email logging

```php
use Origin\Log\Log;
Log::config('default',[
    'engine' => 'Email',
    'to' => 'you@example.com', // string email only
    'from' => ['no-reply@example.com' => 'Web Application'] // to add a name, use an array,
    'host' => 'smtp.example.com',
    'port' => 465,
    'username' => 'demo@example.com',
    'password' => 'secret',
    'timeout' => 5,
    'ssl' => true,
    'tls' => false
]);
```

Options for the Email Engine are:

- *levels*: default `[]`. If you want to restrict this configuration to only certain levels, add the levels to an array e.g. `['critical','emergency','alert']`
- *channels*: default `[]`. If you want to restrict this configuration to only certain channels, add the channels to an array e.g. `['invoices','payments']`
- *to*: The to email address or an array with the email address and name which will be used. e.g. `you@example.com` or `['you@example.com','Tony Robbins']`.
- *from*: The from email address or an array with the email address and name which will be used. e.g. `no-reply@example.com` or `['no-reply@example.com'=>'System Notifications']`.
- *host*: this is SMTP server hostname
- *port*: port number default 25
- *username*: the username to access this SMTP server
- *password*: the password to access this SMTP server
- *ssl*: default is false, set to true if you want to connect via SSL
- *tls*: default is false, set to true if you want to enable TLS
- *timeout*: how many seconds to timeout


> You should always test your email configuration first, if an exception occurs when trying to send the email, it is caught and is not logged to prevent recursion.

### Console Engine

![console](console-log.png)

To configure the Console Engine

```php
use Origin\Log\Log;
Log::config('default',[
    'engine' => 'Console'
]);
```

Options for the Console Engine are:

- stream: default:`php://stderr` this is the stream to use
- levels: default `[]`. If you want to restrict this configuration to only certain levels, add the levels to an array e.g. `['critical','emergency','alert']`
- channels: default `[]`. If you want to restrict this configuration to only certain channels, add the channels to an array e.g. `['invoices','payments']`

### Syslog Engine

You should use the Syslog engine on your production server. To configure the Syslog engine.

```php
use Origin\Log\Log;
Log::config('default',[
    'engine' => 'Syslog'
]);
```

Options for the Syslog Engine are:

- levels: default `[]`. If you want to restrict this configuration to only certain levels, add the levels to an array e.g. `['critical','emergency','alert']`
- channels: default `[]`. If you want to restrict this configuration to only certain channels, add the channels to an array e.g. `['invoices','payments']`

You can also pass settings to the `openlog` command, these are `identity`,`option`,`facility`, see [openlog](https://php.net/manual/en/function.openlog.php) for more information on what these do.

## Example

Lets say you want to configure the logger to log all events in a file as normal, send critical log entires by email and create a separate log for just payments.

```php
use Origin\Log\Log;
// Logs all items to file
Log::config('default',[
    'engine' => 'File',
    'file' => '/var/www/logs/master.log'
]);

// Send import log items by email
Log::config('critical-emails',[
    'engine' => 'Email',
    'to' => 'you@example.com', 
    'from' => ['nobody@gmail.com' => 'Web Application'],
    'levels' => ['critical','emergency','alert'],
    'host' => 'smtp.gmail.com',
    'port' => 465,
    'username' => 'nobody@gmail.com',
    'password' => 'secret',
    'ssl' => true,
]);

// Create a seperate log for everything from the payments channel
Log::config('payments',[
    'engine' => 'File',
    'file' => '/var/www/logs/payments.log',
    'channels' => ['payments']
]);
```


### Creating a Custom Log Engine

To create a custom Log Engine, create the folder structure `app/Log/Engine`, and create an engine class with the method `log`.

```php
namespace App\Log\Engine;

use Origin\Log\Engine\BaseEngine;

class DatabaseEngine extends BaseEngine
{
    /**
     * Setup your default config here
     *
     * @var array
     */
    protected $defaultConfig =  [];

     /**
     * This will be called when the class is constructed
     *
     * @var array
     */
    protected function initialize(array $config) : void
    {

    }

    /**
      * Logs an item
      *
      * @param string $level e.g debug, info, notice, warning, error, critical, alert, emergency.
      * @param string $message 'this is a {what}'
      * @param array $context  ['what'='string']
      * @return void
      */
    public function log(string $level, string $message, array $context = []) : void
    {
        $message = $this->format($level, $message, $context);
        // do something
    }
}
```

Then in your bootstrap configuration

```php
use Origin\Log\Log;
Log::config('default',[
    'className' => 'App\Log\Engine\DabaseEngine'
]);
```

### PSR-3 Logger

The Log library uses a PSR-3 Logger that you may want to use instead of the static `Log` class.

When you create the `Logger` instance you can pass a single engine configuration, which is common when just starting on a new app.

```php
use Origin\Log\Logger;
$logger = new Logger([
    'engine' => 'File',
    'file' => '/var/www/logs/master.log'
]);
```

To change the settings for an engine, or add additional engines or configurations of engines

```php

$logger->config('default',[
    'engine' => 'File',
    'file' => '/var/www/logs/application.log'
]);

// Send import log items by email
$logger->config('critical-emails',[
    'engine' => 'Email',
    'to' => 'you@example.com', 
    'from' => ['nobody@gmail.com' => 'Web Application'],
    'levels' => ['critical','emergency','alert'],
    'host' => 'smtp.gmail.com',
    'port' => 465,
    'username' => 'nobody@gmail.com',
    'password' => 'secret',
    'ssl' => true
]);

// Create a seperate log for everything from the payments channel
$logger->config('payments',[
    'engine' => 'File',
    'file' => '/var/www/logs/payments.log',
    'channels' => ['payments']
]);
```
