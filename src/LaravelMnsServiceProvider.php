<?php

namespace Milkmeowo\LaravelMns;

use Illuminate\Support\ServiceProvider;
use Milkmeowo\LaravelMns\Connectors\MnsConnector;
use Milkmeowo\LaravelMns\Console\MnsCreateQueueCommand;
use Milkmeowo\LaravelMns\Console\MnsDeleteQueueCommand;
use Milkmeowo\LaravelMns\Console\MnsFlushCommand;
use Milkmeowo\LaravelMns\Console\MnsListQueueCommand;
use Milkmeowo\LaravelMns\Console\MnsShowQueueCommand;

class LaravelMnsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        $this->registerConnector($this->app['queue']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                MnsListQueueCommand::class,
                MnsShowQueueCommand::class,
                MnsCreateQueueCommand::class,
                MnsDeleteQueueCommand::class,
                MnsFlushCommand::class,
            ]);
        }
    }

    /**
     * Register the MNS queue connector.
     *
     * @param \Illuminate\Queue\QueueManager $manager
     *
     * @return void
     */
    protected function registerConnector($manager)
    {
        $manager->addConnector('mns', function () {
            return new MnsConnector();
        });
    }

    /**
     * Add the connector to the queue drivers.
     *
     * @return void
     */
    public function register()
    {
    }
}
