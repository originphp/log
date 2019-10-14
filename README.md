# Log

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/log.svg?branch=master)](https://travis-ci.org/originphp/log)
[![coverage](https://coveralls.io/repos/github/originphp/log/badge.svg?branch=master)](https://coveralls.io/github/originphp/log?branch=master)

There are 4 built in Log Engines, and it is easy to implement your own.

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

After placeholders have been replaced (if any), any remaining data will be converted to a json string.

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

The default Log Engine is file, you can use multiple engines at once, and you can customize which levels to Log on.

Edit your Log configuration in `config/log.php`.

### File Engine

The default log file is called `application.log`.

```php
use Origin\Log\Log;
Log::config('default',[
    'engine' => 'File',
    'file' => LOGS . '/somethingelse.log'
]);
```

Options for the File Engine are:

- file: file with full path
- levels: default `[]`. If you want to restrict this configuration to only certain levels, add the levels to an array e.g. `['critical','emergency','alert']`
- channels: default `[]`. If you want to restrict this configuration to only certain channels, add the channels to an array e.g. `['invoices','payments']`

### Email Engine

To also log email, set the configuration, if you only want to use email, then change the name to `default`.

```php
use Origin\Log\Log;
Log::config('email',[
    'engine' => 'Email',
    'to' => 'you@example.com', // string email only
    'from' => ['no-reply@example.com','Web Application'] // to add a name, use an array,
    'account' => 'gmail'
]);
```

Options for the Email Engine are:

- to: The to email address or an array with the email address and name which will be used. e.g. `you@example.com` or `['you@example.com','Tony Robbins']`.
- from: The from email address or an array with the email address and name which will be used. e.g. `no-reply@example.com` or `['no-reply@example.com','System Notifications']`.
- account: default:`default`. The name of the email account to use, as set in `Email::config()` see email account configured](/docs/utility/email) for more information.
- levels: default `[]`. If you want to restrict this configuration to only certain levels, add the levels to an array e.g. `['critical','emergency','alert']`
- channels: default `[]`. If you want to restrict this configuration to only certain channels, add the channels to an array e.g. `['invoices','payments']`

> You should always test your email configuration first, if an exception occurs when trying to send the email, it is caught and is not logged to prevent recursion.

### Console Engine

To configure the Console Engine

```php
use Origin\Log\Log;
Log::config('email',[
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
Log::config('email',[
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
Log::config('critial-emails',[
    'engine' => 'Email',
    'to' => 'you@example.com', 
    'from' => ['no-reply@example.com','Web Application'],
    'account' => 'gmail'
    'levels' => ['critical','emergency','alert']
]);

// Create a seperate log for everything from the payments channel
Log::config('payments',[
    'engine' => 'File',
    'file' => '/var/www/logs/payments.log',
    'channels' => ['payments']
]);
```

### Creating a Custom Logger

To create a custom Log Engine, create the folder structure `app/Log/Engine`, all you need is one function that is the log function

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

To use this Log engine, in your `config/log.php`

```php
use Origin\Log\Log;
Log::config('some-name',[
    'className' => 'App\Log\Engine\DabaseEngine'
]);
```