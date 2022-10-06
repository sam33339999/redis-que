<?php
/**
 * 這是 blpop 版本，
 * 拿了 1 個後，再使用 lua 拿出 9 個
 * 節省 redis 重新連線時間
 * 之後在一次使用併發請求
 */

require_once './vendor/autoload.php';
ini_set('memory_limit', '256M');

$httpCli = new GuzzleHttp\Client;
$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);

print_r('🌚 這是 blpop + lua 版本，拿取一定數量後，併發去打。' . PHP_EOL);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

$luaScript =<<<LUA
    local foo = {}
    
    for i = 1 , ARGV[2] do
        local val = redis.call('lpop', ARGV[1])
        if val then
            table.insert(foo, val)
        end
    end
    return foo
LUA;

$counter = 0; // 計數器
$startFlag = false; // 開始 flag
$st = null;
$con = [];
while (true) {

    if ($counter >= TEST_NUMS) {
        $spend = microtime(true) - $st;
        print_r("spend ...: $spend\n");
        exit;
    }

    // 如果達到併發緩存量、或是隊列內已經無需要 push 的東西，我就去打 api
    if (count($con) == CONCURRENCY_NUMS || $rdb->llen(QUEUE_NAME) == 0) {

        $pool = new GuzzleHttp\Pool($httpCli, generateReqs($con), [
            'concurrency' => CONCURRENCY_NUMS,
            'fulfilled' => function (GuzzleHttp\Psr7\Response $response, $index) use (&$counter) {
                // print_r((string)$response->getBody().PHP_EOL);
                $counter++;
            },
            'rejected' => function (GuzzleHttp\Exception\RequestException $reason, $index) use (&$counter) {
                // print_r($reason->getMessage().PHP_EOL);
                $counter++;
            }
        ]);

        $promise = $pool->promise();
        $promise->wait();

        // 清除 con 陣列
        $con = [];
    }

    [, $val] = $rdb->blpop(QUEUE_NAME, 300);
    
    if ($val) {
        if (! $startFlag) {
            $startFlag = true;
            $st = microtime(true);
        }
        $result = $rdb->eval($luaScript, ['LIST_QUEUE', 9]);
        array_unshift($result, $val);
        $con = $result;
        // print_r($con);
    }
}