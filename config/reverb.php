<?php

return [

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('REVERB_ALLOWED_ORIGINS', '*')))),

];
