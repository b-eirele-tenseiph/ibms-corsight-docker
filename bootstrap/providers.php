<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\LorawanServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\SwitchBotServiceProvider::class,
    App\Providers\Lorawan\PayloadHandlerServiceProvider::class,
    App\Providers\GoogleCloudTasksQueueServiceProvider::class,
];
