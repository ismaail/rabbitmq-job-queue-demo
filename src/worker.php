<?php

/*
 * This is a worker process that will run forever and execute tasks as they appear in the queue.
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
    password: $config['password']
);

$channel = $connection->channel();

# Create the queue if it doesn't already exist.
$channel->queue_declare(
    queue: $config['queue_name'],
    passive: false,
    durable: true,
    exclusive: false,
    auto_delete: false,
    nowait: false,
    arguments: null,
    ticket: null
);

echo PHP_EOL, ' [*] Starting Work..', "\n";

/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
$callback = function (Message $message) {
    echo " [x] Received {$message->body}\n";

    $job = Job::fromJson($message->body);
    sleep($job->sleepPeriod);

    echo " [x] Done\n";
    $message->ack();
};

//$channel->basic_qos(null, 1, null);

$channel->basic_consume(
    queue: $config['queue_name'],
    consumer_tag: '',
    no_local: false,
    no_ack: false,
    exclusive: false,
    nowait: false,
    callback: $callback,
);

while ($channel->is_consuming()) {
    $channel->wait(allowed_methods: null, non_blocking: true, timeout: 0);
}

$channel->close();
$connection->close();
echo ' [x] Connection closed.', "\n";
