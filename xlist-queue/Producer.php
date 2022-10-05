<?php

$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

for ($i = 0; $i <= 10000; $i++) {
    $rdb->xadd('XLIST', "*", ['field' => $i]);
}