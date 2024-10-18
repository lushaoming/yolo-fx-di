# PHP dependency injection

This is a simple and efficient PHP dependency injection package that uses the latest feature of PHP: annotations. Through annotations, we can mark a class as a singleton.

## PHP version
Minimum PHP version support: `8.0`

## Introduction
- No dependencies, only one method `DI::use()` is provided
- Support singleton mode
- Reflection object caching eliminates the need to repeatedly create the same class reflection and improves performance
- Circular dependency detection, capable of detecting circular dependency problems (including direct and indirect circular dependencies)

## Installation
```
composer require yolo-fx/di
```

## Usage

You can use the `Yolo\Di\DI::use()` to create an instance of the class, like:

`Yolo\Di\DI::use(User::class)`

And, you can use the `Yolo\Di\Annotations\Singleton` annotation to mark a class as a singleton.

Also, you can use the `Yolo\Di\Annotations\Initializer` annotation to mark a method as an initializer.

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

use DI\Tests\UserTest;
use Yolo\Di\DI;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/UserTest.php';
require_once __DIR__ . '/AnimalTest.php';

$start = microtime(true);

$user1 = DI::use(UserTest::class);
$user1->sayHello();

$user2 = DI::use(UserTest::class);
$user2->sayHello();

$end = microtime(true);

echo 'Spent ' . round(($end - $start) * 6, 3) . 'ms';

```
