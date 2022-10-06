<?php

$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

$luaScript =<<<LUA
    local foo = {}
    
    for i = 0 , ARGV[2] do
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
        
        $result = $rdb->eval($luaScript, ['LIST_QUEUE', 10]);
        array_unshift($result, $val);

        $counter += count($result);
    }
}