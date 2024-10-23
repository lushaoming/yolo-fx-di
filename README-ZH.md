# PHP 依赖注入

这是一个简单高效的 PHP 依赖注入包，它使用了 PHP 的最新特性：注解。通过注解，我们可以将一个类标记为单例。

## PHP 版本
最低支持的 PHP 版本：`8.0`

## 介绍
- 无依赖
- 支持单例模式
- 支持类别名
- 支持类映射
- 反射对象缓存消除了重复创建相同类反射的需求，并提高了性能
- 循环依赖检测，能够检测循环依赖问题（包括直接和间接循环依赖）

## 安装
```
composer require yolo-fx/di
```

## 使用方法

- 创建类的实例。

你可以使用 `Yolo\Di\DI::use()` 来创建类的实例，例如：

`Yolo\Di\DI::use(User::class)`

- 单例

你可以使用 `Yolo\Di\Annotations\Singleton` 注解来将一个类标记为单例。

- 初始化器

你可以使用 `Yolo\Di\Annotations\Initializer` 注解来将一个方法标记为初始化器。

- 使用类映射

当你的构造函数参数是一个接口类型时，这会非常有用。例如：
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
你可以使用 `Yolo\Di\DI::bind()` 来设置类映射。

```php
DI::bind(LoggerInterface::class, ConsoleLogger::class);
```
这样 `LoggerInterface::class` 将被 `ConsoleLogger::class` 替换。

现在，你可以使用 `Yolo\Di\DI::use()` 来创建类的实例。
```php
$runner = DI::use(TestRunner::class);
$runner->sayHello();
```

- 使用类别名
```php
DI::alias(TestRunner::class, 'runner');
$runner = DI::use('runner');
$runner->sayHello();
```

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

- 使用 DI 创建实例
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

## 注意事项
- 不要有循环依赖，包括直接和间接的循环依赖，比如 A 依赖于 B 而 B 又依赖于 A。