<?php

/*
 * This is a process that continuously publishes work to the queue.
 * You could call it a"job creator" =)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Job;
use PhpAmqpLib\Message\AMQPMessage as Message;
use PhpAmqpLib\Connection\AMQPStreamConnection as Connection;

$config = require __DIR__ . '/../config/rabbitmq.php';

$connection = new Connection(
    host: $config['host'],
    port: $config['port'],
    user: $config['username'],
    password: $config['password'],
);

$channel = $connection->channel();

/* Create the queue if it doesn't already exist. */
$channel->queue_declare(
    queue: $config['queue_name'],
    passive: false,
    durable: true,
    exclusive: false,
    auto_delete: false,
    nowait: false,
    arguments: null,
    ticket: null,
);

$jobId = 0;

while (true) {
    $job = new Job($jobId++, 'sleep', random_int(0, 3));

    $message = new Message(
        body: $job->toJson(),
        properties: ['delivery_mode' => Message::DELIVERY_MODE_PERSISTENT],
    );

    $channel->basic_publish($message, exchange: '', routing_key: $config['queue_name']);
    echo 'Job created', PHP_EOL;
    sleep(1);
}

$channel->close();
$connection->close();
