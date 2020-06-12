<?php

/*
 * This is a worker process that will run forever and execute tasks as they appear in the queue.
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

# Create the queue if it doesn't already exist.
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

echo PHP_EOL, ' [*] Starting Work..', "\n";

/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
$callback = function (AMQPMessage $message) {
    echo " [x] Received {$message->body}\n";

    $job = json_decode($message->body, true);
    sleep($job['sleep_period']);

    echo " [x] Done\n";
    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);

$channel->basic_consume(
    $config['queue_name'],
    '',    // consumer_tag
    false, // no_local
    false, // no_ack
    false, // exclusive
    false, // nowait
    $callback
);

$channel->wait(null, true, 0);

// This is the best I could find for a non-blocking wait. Unfortunately one has to have
// a timeout (for now), and simply setting nonBlocking=true on its own appears do to nothing.
// An exception is thrown when the timout is reached, breaking the loop, and you should catch it
// to exit gracefully.
// try {
//     while (count($channel->callbacks)) {
//         print "running non blocking wait." . PHP_EOL;
//         $channel->wait($allowed_methods = null, $nonBlocking = true, $timeout = 1);
//     }
// } catch (Exception $e) {
//     print "There are no more tasks in the queue." . PHP_EOL;
// }


$channel->close();
$connection->close();
echo ' [x] Connection closed.', "\n";
