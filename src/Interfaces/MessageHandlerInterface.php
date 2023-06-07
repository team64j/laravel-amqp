<?php

namespace Team64j\LaravelAmqp\Interfaces;

use Anik\Amqp\ConsumableMessage;

interface MessageHandlerInterface
{
    /**
     * @param ConsumableMessage $message
     *
     * @return void
     */
    public function handle(ConsumableMessage $message): void;
}
