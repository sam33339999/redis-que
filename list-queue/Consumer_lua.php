<?php

$rdb = new \Redis();
$rdb->connect('127.0.0.1', 6379);
print_r('ping: ' . print_r($rdb->ping(), true) . PHP_EOL);

$startFlag = false;
$st = null;

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

while (true) {

    [, $val] = $rdb->blpop('LIST_QUEUE', 300);
    
    if ($val) {
        $startFlag = true;
        $st = microtime(true);
        $result = $rdb->eval($luaScript, ['LIST_QUEUE', 10]);
        array_unshift($result, $val);
        print_r($result);
    }

    
    // print_r($val. PHP_EOL);
    usleep(100);
}