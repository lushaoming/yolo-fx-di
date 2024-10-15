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
    public function sayHello1()
    {
        return 'Hello, I am an animal.' . PHP_EOL;
    }
}
