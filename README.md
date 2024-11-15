# PHP dependency injection

This is a simple and efficient PHP dependency injection package that uses the latest feature of PHP: annotations. Through annotations, we can mark a class as a singleton.

## PHP version
Minimum PHP version support: `8.0`

## Introduction
- No dependencies
- Support singleton mode
- Support class alias
- Support class mappings
- Support initializer method
  > Different from the constructor method of the class, it will be executed after the constructor method is executed (if any)

- Support custom property injection
  > Implement `PropertyAttributeInterface`

- Reflection object caching eliminates the need to repeatedly create the same class reflection and improves performance
- Circular dependency detection, capable of detecting circular dependency problems (including direct and indirect circular dependencies)

## Installation
```
composer require yolo-fx/di
```

## Usage

- Create an instance of a class.

You can use the `Yolo\Di\DI::use()` to create an instance of the class, like:

`Yolo\Di\DI::use(User::class)`

- Singleton

You can use the `Yolo\Di\Annotations\Singleton` annotation to mark a class as a singleton.

- Initializer

You can use the `Yolo\Di\Annotations\Initializer` annotation to mark a method as an initializer.

- Custom property injection 

Define a custom property injection attribute class that implements `PropertyAttributeInterface`.

```php
<?php

use Yolo\Di\DI;
use Yolo\Di\PropertyAttributeInterface;

#[Attribute]
class Cache implements PropertyAttributeInterface
{
    public function __construct(
        private string $driver = 'default'
    ){}

    public function inject(): mixed
    {
        return match ($this->driver) {
            'file' => DI::use(FileLogger::class),
            default => DI::use(ConsoleLogger::class),
        };
    }
}
```
Add the custom property injection attribute class to DI.
```php
DI::addCustomPropertyAttribute(Cache::class);
```

And now, you can use the `Cache` attribute in your class.

```php
class  TestRunner
{
    public function __construct(
        #[Cache('file')]
        private LoggerInterface $logger
    ){
    }

    public function sayHello(): void
    {
        $this->logger->log("Hello World");
    }
}
```

So the `$logger` is a `FileLogger` instance.

- Use class Mappings

This is useful when your constructor parameter is an interface type. For example:
```php
class TestRunner
{
    /**
     * @param LoggerInterface $logger It will be replaced by ConsoleLogger
     */
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function sayHello(): void
    {
        $this->logger->log("Hello World");
    }
}
```
You can you use the `Yolo\Di\DI::bind()` to set class mappings.

```php
DI::bind(LoggerInterface::class, ConsoleLogger::class);
```
So the `LoggerInterface::class` will be replaced by `ConsoleLogger::class`.

Now, you can use the `Yolo\Di\DI::use()` to create an instance of the class.
```php
$runner = DI::use(TestRunner::class);
$runner->sayHello();
```

> `DI::bind()` will affect the global class mapping. If you only want this effect on a certain instance, please use custom property attribute injection.


- Use class alias
```php
DI::alias(TestRunner::class, 'runner');
$runner = DI::use('runner');
$runner->sayHello();
```

## Example

- Create a class: UserTest.php
```php
<?php

namespace DI\Tests;

use Yolo\Di\Annotations\Initializer;

class UserTest
{
    public function __construct(private AnimalTest $animal)
    {
        echo "you can see me twice." . PHP_EOL;
    }

    #[Initializer]
    public function init()
    {
        echo "User.init" . PHP_EOL;
    }

    /**
     * Say hello
     * @return void
     */
    public function sayHello()
    {
        echo "Hello, I am a user." . PHP_EOL;
        $this->animal->sayHello();
    }
}
```

- Create another class: AnimalTest.php
```php
<?php

namespace DI\Tests;

use Yolo\Di\Annotations\Singleton;

#[Singleton]
class AnimalTest
{
    public function __construct()
    {
        echo 'you can only see me once' . PHP_EOL;
    }
    public function sayHello()
    {
        echo 'Hello, I am an animal.' . PHP_EOL;
    }
}
```

- Use DI to create an instance
```php
<?php

use Yolo\Di\DI;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/LoggerInterface.php';
require_once __DIR__ . '/ConsoleLogger.php';
require_once __DIR__ . '/FileLogger.php';

class TestRunner
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function sayHello(): void
    {
        $this->logger->log("Hello World");
    }
}

$start = microtime(true);

DI::bind(LoggerInterface::class, ConsoleLogger::class);

// Use DI::alias to create an alias for a class
DI::alias(TestRunner::class, 'runner');
// And then use the alias to create an instance of TestRunner
$runner = DI::use('runner');
$runner->sayHello();

// Also, you can use the class name to create an instance of TestRunner
$runner = DI::use(TestRunner::class);
$runner->sayHello();

$end = microtime(true);

echo 'Spent ' . round(($end - $start) * 6, 3) . 'ms' . PHP_EOL;

echo 'Memory usage: ' . round(memory_get_usage() / 1024, 3) . 'kb' . PHP_EOL;
```

## Notes
- Do not have circular dependencies, including direct and indirect circular dependencies, such as A depends on B and B depends on A.
