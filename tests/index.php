<?php

use DI\Tests\User;
use Yolo\Di\DI;

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
