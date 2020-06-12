<?php

/*
 * This is a process that continuously publishes work to the queue.
 * You could call it a"job creator" =)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

$config = require __DIR__ . '/../config/rabbitmq.php';

$connection = new AMQPStreamConnection(
    $config['host'],
    $config['port'],
    $config['username'],
    $config['password']
);

$channel = $connection->channel();

/* Create the queue if it doesn't already exist. */
$channel->queue_declare(
    $config['queue_name'],
    false, // passive
    true,  // durable
    false, // exclusive
    false, // auto_delete
    false, // nowait
    null,  // arguments
    null   // ticket
);

$jobId = 0;

while (true) {
    $jobs = [
        'id' => $jobId++,
        'task' => 'sleep',
        'sleep_period' => rand(0, 3),
    ];

    $message = new AMQPMessage(
        json_encode($jobs, JSON_UNESCAPED_SLASHES),
        ['delivery_mode' => 2] // make message persistent
    );

    $channel->basic_publish($message, '', $config['queue_name']);
    echo 'Job created', PHP_EOL;
    sleep(1);
}

$channel->close();
$connection->close();
