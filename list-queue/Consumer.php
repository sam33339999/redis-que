<?php

$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

$counter = 0; // 計數器
$startFlag = false; // 開始 flag
$st = null;

while (true) {

    if ($counter >= 10000) {
        $spend = microtime(true) - $st;
        print_r("spend ...: $spend\n");
    }

    [, $val] = $rdb->blpop('LIST_QUEUE', 300);
    
    if ($val) {
        if (! $startFlag) {
            $startFlag = true;
            $st = microtime(true);
        }

        $counter += 1;
    }
}