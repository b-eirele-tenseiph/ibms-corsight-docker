<?php

namespace App\Queue;

use Illuminate\Queue\Connectors\ConnectorInterface;

class GoogleCloudTasksConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        // Return a new instance of your GoogleCloudTasksQueue with the 
        // passed configuration
        return new GoogleCloudTasksQueue($config);
    }
}
