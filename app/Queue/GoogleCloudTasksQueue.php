<?php

namespace App\Queue;

use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;
use Google\Protobuf\Timestamp;

class GoogleCloudTasksQueue extends Queue implements QueueContract
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $client          (1.0)  The cloud tasks client for processing task
    // $projectId       (2.0)  The google project id
    // $location        (3.0)  The location of the google project
    // $queueName       (4.0)  The name of the queue
    // $handlerUrl      (5.0)  The target url handler of the task
    // __construct      (6.0)  The constructor method
    // size             (7.0)  Get the size of the queue.
    // push             (8.0)  Push a new job onto the queue.
    // pushRaw          (9.0)  Push a raw payload onto the queue.
    // later            (10.0) Push a new job onto the queue after a delay.
    // pop              (11.0) Pop the next job off of the queue.
    // createTask       (12.0) Create task on google cloud tasks

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $client
     * <Function> The cloud tasks client for processing task
     *
     * @var \Google\Cloud\Tasks\V2\CloudTasksClient
     */
    protected $client;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $projectId
     * <Function> The google project id
     *
     * @var string
     */
    protected $projectId;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $location
     * <Function> The location of the google project
     *
     * @var string
     */
    protected $location;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $queueName
     * <Function> The name of the queue
     *
     * @var string
     */
    protected $queueName;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $handlerUrl
     * <Function> The target url handler of the task
     *
     * @var string
     */
    protected $handlerUrl;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> __construct
     * <Function> The constructor method
     *
     * @param array $config
     */
    public function __construct($config)
    {
        // If environment is local, use json key
        if (app()->environment('local')) {
            $this->client = new CloudTasksClient([
                'credentials' => $config['credentials']
            ]);
        } else {
            $this->client = new CloudTasksClient();
        }

        // Set cloud task configuration
        $this->projectId = $config['project_id'];
        $this->location = $config['location'];
        $this->queueName = $config['queue'];
        $this->handlerUrl = $config['handler_url'];
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> methodName
     * <Function> Get the size of the queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        // Implement size check if needed (optional)
        return 0;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> push
     * <Function> Push a new job onto the queue.
     *
     * @param  string|object  $job
     * @param  mixed          $data
     * @param  string|null    $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {

        return $this->createTask($this->createPayload($job, $data));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> pushRaw
     * <Function> Push a raw payload onto the queue.
     *
     * @param  string       $payload
     * @param  string|null  $queue
     * @param  array        $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->createTask($payload);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> later
     * <Function> Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string|object                         $job
     * @param  mixed                                 $data
     * @param  string|null                           $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->createTask($this->createPayload($job, $data), $delay);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> pop
     * <Function> Pop the next job off of the queue.
     *
     * @param  string|null                          $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        // Google Cloud Tasks uses HTTP requests to push jobs, so we don't need
        // to implement popping.
        return null;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> createTask
     * <Function> Create task on google cloud tasks
     *
     * @param  string|object  $payload
     * @param  integer        $delay
     * @return void
     */
    protected function createTask($payload, $delay = 0)
    {
        $queuePath = $this->client->queueName($this->projectId, $this->location, $this->queueName);

        $task = new Task();
        $httpRequest = new HttpRequest();
        $httpRequest->setHttpMethod(HttpMethod::POST);
        $httpRequest->setUrl($this->handlerUrl); // Set the handler URL

        // Encode the payload and attach it to the request
        $httpRequest->setBody(base64_encode($payload));
        $task->setHttpRequest($httpRequest);

        // Handle delay if necessary
        if ($delay instanceof \DateTimeInterface) {
            // If $delay is a Carbon instance, convert it to seconds from now
            $delaySeconds = $delay->getTimestamp() - now()->getTimestamp();
        } else {
            // Assume the delay is already an integer
            $delaySeconds = (int) $delay;
        }

        // Handle delay if required
        if ($delaySeconds > 0) {
            $scheduleTime = new Timestamp();
            $scheduleTime->setSeconds(time() + $delaySeconds);
            $task->setScheduleTime($scheduleTime);
        }

        return $this->client->createTask($queuePath, $task);
    }
}
