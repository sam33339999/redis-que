<?php


function singlePost(
    \GuzzleHttp\Client &$client,
    string $method,
    string $body
) {
    try {
        $client->request($method, 'https://api.line.me/v2/bot/message/push', [
            'body' => $body,
        ]);        
    } catch (\GuzzleHttp\Exception\RequestException) {
        // ...
    } finally {
        return 1;
    }
}

function generateReqs($payloads)
{
    $uri = 'https://api.line.me/v2/bot/message/push';

    foreach($payloads as $payload) {
        yield new \GuzzleHttp\Psr7\Request('POST', $uri, [], $payload);
    }
}


