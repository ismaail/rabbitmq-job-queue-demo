<?php

/*
 * This is a process that continuously publishes work to the queue. You could call it a
 * "job creator" =)
 */

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/rabbitmq.php';

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
    'localhost',
    $config['port'],
    $config['username'],
    $config['password']
);

$channel = $connection->channel();

# Create the queue if it doesn't already exist.
$channel->queue_declare(
    $queue = $config['queue_name'],
    $passive = false,
    $durable = true,
    $exclusive = false,
    $auto_delete = false,
    $nowait = false,
    $arguments = null,
    $ticket = null
);

$jobId = 0;

while (true) {
    $jobs = [
        'id' => $jobId++,
        'task' => 'sleep',
        'sleep_period' => rand(0, 3),
    ];

    $message = new \PhpAmqpLib\Message\AMQPMessage(
        json_encode($jobs, JSON_UNESCAPED_SLASHES),
        ['delivery_mode' => 2] # make message persistent
    );

    $channel->basic_publish($message, '', $config['queue_name']);
    echo 'Job created', PHP_EOL;
    sleep(1);
}
