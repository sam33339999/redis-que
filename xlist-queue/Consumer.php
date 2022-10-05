<?php

$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

$startFlag = false;
$st = null;
while (true) {

    [$key, $val] = $rdb->blpop('LIST_QUEUE', 300);
    
    if ($val) {
        $startFlag = true;
        $st = microtime(true);
    }

    if ($val == 10000) {
        $spend = microtime(true) - $st;
        printf("spend microtime: %g ! \n", $spend);
        
    }
    
    // print_r($val. PHP_EOL);
    usleep(100);
}