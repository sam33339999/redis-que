<?php
/**
 * 這是 blpop 版本，執行一個一個拿並且一個一個打。 - ok
 */

require_once './vendor/autoload.php';
ini_set('memory_limit', '256M');

$httpCli = new GuzzleHttp\Client;
$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);

print_r('🌚 這是 blpop 版本，執行一個一個拿並且一個一個打。' . PHP_EOL);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

$counter = 0; // 計數器
$startFlag = false; // 開始 flag
$st = null;

while (true) {

    if ($counter >= TEST_NUMS) {
        $spend = microtime(true) - $st;
        print_r("spend ...: $spend\n");
        exit;
    }

    [, $val] = $rdb->blpop('LIST_QUEUE', 300);
    
    if ($val) {
        if (! $startFlag) {
            $startFlag = true;
            $st = microtime(true);
        }

        singlePost($httpCli, 'POST', $val);
        $counter += 1;
    }
}