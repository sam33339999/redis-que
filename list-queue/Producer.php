<?php
require_once './vendor/autoload.php';

$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

for ($i = 0; $i <= 500000; $i++) {
    $rdb->rpush(QUEUE_NAME, $i);
}