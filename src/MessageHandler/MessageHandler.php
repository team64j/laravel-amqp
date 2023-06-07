<?php

declare(strict_types=1);

namespace Team64j\LaravelAmqp\MessageHandler;

use Anik\Amqp\ConsumableMessage;

class MessageHandler implements MessageHandlerInterface
{

    public function handle(ConsumableMessage $message): void
    {
        // TODO: Implement handle() method.
    }
}
