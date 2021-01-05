<?php

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    printf($request);
    $response->status(101);
    $response->end();
});

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();

