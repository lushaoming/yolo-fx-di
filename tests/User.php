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
        echo "User.init" . PHP_EOL;
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
