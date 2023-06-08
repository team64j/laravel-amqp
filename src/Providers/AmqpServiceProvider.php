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

            $this->publishes([
                __DIR__ . '/../../config/amqp.php' => config_path('amqp.php'),
            ], 'amqp-config');
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        if (!$this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/amqp.php', 'amqp');
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
