# PHP 依赖注入

这是一个简单高效的PHP依赖注入包，它使用了PHP 8的最新功能：注解。通过注解，我们可以实现很多功能，如可以将一个类标记为单例。

## PHP 版本
最低PHP版本支持: `8.0`

## 安装
```
composer require yolo-fx/di
```

## 使用

你可以使用 `Yolo\Di\DI::use()` 创建一个类的实例，如

`Yolo\Di\DI::use(User::class)`

并且，你可以使用 `Yolo\Di\Annotations\Singleton`注解将一个类标记为单例，这样，多次使用`Yolo\Di\DI::use()`创建此类的实例时，都将只会创建一个实例。

还有，你可以使用 `Yolo\Di\Annotations\Initializer` 注解将一个类的方法标记为初始化方法，这样在创建此类的实例后，将会自动调用此方法。（必须为`public`，单例只会在第一次调用）

## 示例

- 创建一个类: UserTest.php
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

- 创建另一个类: AnimalTest.php
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

- 使用DI创建实例
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

## 注意事项

- 不要循环依赖，包括直接和间接的循环依赖，如A依赖B，B依赖A这样。
