# PHP dependency injection

This is a simple and efficient PHP dependency injection package that uses the latest feature of PHP: annotations. Through annotations, we can mark a class as a singleton.

## PHP version
Minimum PHP version support: `8.0`

## Installation
```
composer require yolo-fx/di
```

## Usage

You can use the `Yolo\DI\DI::use()` to create an instance of the class, like:

`Yolo\DI\DI::use(User::class)`

And, you can use the `Yolo\DI\Annotations\Singleton` annotation to mark a class as a singleton.

Also, you can use the `Yolo\DI\Annotations\Initializer` annotation to mark a method as an initializer.

## Example

- Create a class: User.php
```php
<?php

namespace DI\Tests;

use Yolo\Di\Annotations\Initializer;

class User
{
    public function __construct(Animal $animal)
    {
        echo "you can see me twice." . PHP_EOL;
    }

    #[Initializer]
    public function init()
    {
        echo "User.init after constructor" . PHP_EOL;
    }

    /**
     * Say hello
     * @return void
     */
    public function sayHello()
    {
        echo "Hello, I am a user." . PHP_EOL;
    }

}
```

- Create another class: Animal.php
```php
<?php

namespace DI\Tests;

use Yolo\Di\Annotations\Singleton;

#[Singleton]
class Animal
{
    public function __construct()
    {
        echo 'you can only see me once' . PHP_EOL;
    }
    
    public function sayHello()
    {
        return 'Hello, I am an animal.' . PHP_EOL;
    }
}
```

- Use DI to create an instance
```php
<?php

use DI\Tests\User;
use Yolo\Di\DI;

// require autoload
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Animal.php';

$start = microtime(true);

$user1 = DI::use(User::class);
$user1->sayHello();

$user2 = DI::use(User::class);
$user2->sayHello();

$end = microtime(true);

echo 'Spent ' . round(($end - $start) * 6, 3) . 'ms';

```
