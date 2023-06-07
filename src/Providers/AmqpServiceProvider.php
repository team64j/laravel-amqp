<?php

declare(strict_types=1);

namespace Team64j\LaravelAmqp\Providers;

use Illuminate\Support\ServiceProvider;
use Team64j\LaravelAmqp\Commands\MessageQueueConsumerCommand;

class AmqpServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            MessageQueueConsumerCommand::class,
        ]);
    }
}
