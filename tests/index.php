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
