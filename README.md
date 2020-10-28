# Jogger :running:
![Codecov](https://img.shields.io/codecov/c/github/Shikachuu/JoggerPHP) ![GitHub](https://img.shields.io/github/license/Shikachuu/JoggerPHP)
---
PSR-3 compatible, opinionated logger library for PHP mostly based on a Go project [rs/zerolog](https://github.com/rs/zerolog)
and PHP project [Seldaek/monolog](https://github.com/Seldaek/monolog).

I built this library because I mostly feel like interpolation for json log strings isn't able to provide
as much flexibility and harder to filter on, than for additional fields and since json syntax provides it
then we shall use it's full potential.
## Install
```sh
composer require Shikachuu/JoggerPHP
```
## Features and Examples
### Time formats:
Jogger supports 2 types of date formats:
- ISO8601 `$logger->setTimeFormatISO8601();`
- Unix timestamp `$logger->setTimeFormatUnix();`
### Additional, chained fields:
Jogger just like zerolog, supports a number of primitive types (int, float...) and non-primitive types (currently only array)
as additional fields next to the almost standard `message` and `timestamp`. These fields are also chainable.
(has fluent return value) 
```php
<?php
declare(strict_types=1);

use Jogger\Logger;
use Jogger\Output\StdoutOutput;

require_once "vendor/autoload.php";

$logger = new Logger("default", [new StdoutOutput("info")], "Europe/Budapest");
$logger->setTimeFormatISO8601();
$logger->addString("name", "John")
    ->addFloat("favoriteNumber", 1.33)
    ->addArray("favoritePokemons", ["Lucario", "Terrakion", "Darkrai"])
    ->alert("New user created for role {role}", ["role" => "admin"]);
```
**^** This code's log message roughly going to look like this:
```json
{"timestamp":"2020-10-28T21:13:40.909549+01:00","level":"alert","message":"New user created for role admin","name":"John","favoriteNumber":1.33,"favoritePokemons":["Lucario","Terrakion","Darkrai"]}
```
### Exceptions:
Zerolog has the ability to add Golang errors to your log messages. Since in PHP we mostly use Exceptions,
Jogger has the ability to add any Exception that inherits from PHP's default `\Exception` class.
```php
<?php
declare(strict_types=1);

use Jogger\Logger;
use Jogger\Output\StdoutOutput;

require_once "vendor/autoload.php";

$logger = new Logger("default", [new StdoutOutput("error")], "Europe/Budapest");
$logger->setTimeFormatUnix();

try {
    throw new DomainException("Oh something went wrong", 2034);
} catch (DomainException $exception) {
    $logger->addException("domainError", $exception)->error("Failed to serve client");
    // do the error handling
}
```
**^** This code's log message roughly going to look like this:
```json
{"timestamp":1603916386,"level":"error","message":"Failed to serve client","domainError":{"exception":"DomainException","code":2034,"message":"Oh something went wrong","file":"\/usr\/src\/myapp\/index.php","line":13,"trace":"#0 {main}"}}
```

### Output Plugins:
To this point I assume you noticed a pattern around line 9, there is an array with an Output.
Jogger supports output plugins made by its users. In fact, it contains 4 by default:
- `NOOP` for no operation, useful for test or mock something
- `Stream` for files and any PHP streams really
- `STDOUT` for utilizing PHP's stdout stream it is based on the `Stream` Output
- `STDERR` for utilizing PHP's stderr stream it is based on the `Stream` Output

In the constructor's array you have the ability to provide the loglevel,
so you can use different output for different levels.

The core library `Shikachuu/JoggerPHP` **does not**, intend to contain (anymore) than these baked in outputs,
anyhow it provides an [interface](src/Output/OutputPlugin.php), and an [abstract base class](src/Output/BaseOutput.php)
to write your own solutions, and you are more than welcome to open a Pull Request to add yours to the README file.

## Community Outputs: