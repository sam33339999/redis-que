<?php
// const API_URL = 'https://api.line.me/v2/bot/message/push';
const API_URL = 'http://127.0.0.1:8080';

function singlePost(
    \GuzzleHttp\Client &$client,
    string $method,
    string $body
) {
    try {
        $client->request($method, API_URL, [
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
    foreach($payloads as $payload) {
        yield new \GuzzleHttp\Psr7\Request('POST', API_URL, [], $payload);
    }
}


