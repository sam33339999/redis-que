<?php
/**
 * 這是 blpop + lua 版本，一次取 10 個，然後使用 httpCli 一個一個打
 */

require_once './vendor/autoload.php';
ini_set('memory_limit', '256M');

$httpCli = new GuzzleHttp\Client;
$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);

print_r('🌚 這是 blpop + lua 版本，一次取 10 個，然後使用 httpCli 一個一個打。' . PHP_EOL);
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

// $luaScript =<<<LUA
//     return KEYS
// LUA;

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
        
        $result = $rdb->eval($luaScript, ['LIST_QUEUE', REQ_BUF_NUMS - 1]);
        array_unshift($result, $val);

        foreach ($result as $s) {
            singlePost($httpCli, 'POST', $s);
            $counter++;
        }
        $result = [];
    }
}