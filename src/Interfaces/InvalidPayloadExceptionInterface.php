<?php

namespace Team64j\LaravelAmqp\Interfaces;

use Throwable;

interface InvalidPayloadExceptionInterface extends Throwable
{
    /**
     * @return array
     */
    public function getErrors(): array;
}
