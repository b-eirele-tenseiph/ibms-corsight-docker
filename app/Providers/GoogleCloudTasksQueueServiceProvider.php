<?php

namespace App\Providers;

use App\Queue\GoogleCloudTasksConnector;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

class GoogleCloudTasksQueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(QueueManager $queue)
    {
        // Register the gcloud queue connector
        $queue->addConnector('gcloud', function () {
            return new GoogleCloudTasksConnector();
        });
    }
}
