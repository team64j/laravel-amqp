<?php

use PhpAmqpLib\Connection\AMQPLazySSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

return [
    'connections' => [
        'test' => [
            'connection' => [
                /**
                 * Lazy connection does not support more than 1 host
                 * Change connection **class** if you want to try more than one host
                 */
                'class' => AMQPLazySSLConnection::class,
                'hosts' => [
                    [
                        'host' => env('AMQP_HOST', 'localhost'),
                        'port' => env('AMQP_PORT', 5672),
                        'user' => env('AMQP_USER', ''),
                        'password' => env('AMQP_PASSWORD', ''),
                        'vhost' => env('AMQP_VHOST', '/'),
                    ],
                ],
                /**
                 * Pass additional options that are required for the AMQP*Connection class
                 * You can check *Connection::try_create_connection method to check
                 * if you want to pass additional data
                 */
                'options' => [],
            ],

            'message' => [
                'handler' => \App\MessageHandler\TestMessageHandler::class,
                'content_type' => env('AMQP_MESSAGE_CONTENT_TYPE', 'text/plain'),
                'delivery_mode' => env('AMQP_MESSAGE_DELIVERY_MODE', AMQPMessage::DELIVERY_MODE_PERSISTENT),
                'content_encoding' => env('AMQP_MESSAGE_CONTENT_ENCODING', 'UTF-8'),
            ],

            'exchange' => [
                'name' => 'test.test',
                'type' => env('AMQP_EXCHANGE_TYPE', 'direct'),
                'declare' => env('AMQP_EXCHANGE_DECLARE', false),
            ],

            'queue' => [
                'name' => 'test.test',
                'declare' => env('AMQP_QUEUE_DECLARE', false),
            ],
        ]
    ]
];
