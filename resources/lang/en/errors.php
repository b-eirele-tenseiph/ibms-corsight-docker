<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for exception messages for known
    | errors currently being handled.
    */

    'lorawan' => [
        'command' => [
            'thingpark'        => [
                400   => 'E012',
                401   => 'E013',
                500   => 'E014',
                2001  => 'E015'
            ],
            'thingpark_token'  => [
                400   => 'E016',
                401   => 'E017',
                404   => 'E018',
                500   => 'E019',
            ],
            'kerlink'          => [
                6     => 'E020',
                1000  => 'E021',
                2001  => 'E015'
            ],
            'milesight'          => [
                6     => 'E020',
                1000  => 'E021',
                2001  => 'E015'
            ]
        ]
    ],

    'switch_bot' => [
        'command' => [
            151  => 'E003',
            160  => 'E005',
            171  => 'E004',
            174  => 'E010',
            190  => 'E009',
            401  => 'E006',
            403  => 'E007',
            500  => 'E008',
        ],
        'account' => [
            '401' => 'Invalid credentials',
            '403' => 'Access denied',
            '429' => 'Rate limit exceeded',
            '500' => 'SwitchBot server error',
        ],
        'connection' => [
            'failed' => 'Connection failed: :message',
        ],
        'http' => [
            'error' => 'HTTP error (:status)',
        ],
        'unknown_error' => 'Unknown error',

    ],

    'error_messages' => [
        400 => [
            'title' => 'Bad Request',
            'message' => 'We were unable to process your request due to invalid or missing information. Please check your input and try again.',
        ],
        401 => [
            'title' => 'Unauthorized',
            'message' => 'You must be logged in or properly authenticated to access this page. Please sign in and try again.',
        ],
        403 => [
            'title' => 'Forbidden',
            'message' => 'Access to this resource is restricted. You do not have sufficient permissions to proceed.',
        ],
        404 => [
            'title' => 'Page Not Found',
            'message' => 'The page or resource you are looking for could not be found. It may have been moved or no longer exists.',
        ],
        405 => [
            'title' => 'Method Not Allowed',
            'message' => 'This action is not allowed for the requested resource. Please use a supported request method.',
        ],
        408 => [
            'title' => 'Request Timeout',
            'message' => 'The request took too long to process and has timed out. Please try again.',
        ],
        410 => [
            'title' => 'Gone',
            'message' => 'This resource is no longer available and has been permanently removed.',
        ],
        419 => [
            'title' => 'Page Expired',
            'message' => 'Your session has expired for security reasons. Please refresh the page and try again.',
        ],
        429 => [
            'title' => 'Too Many Requests',
            'message' => 'You have made too many requests in a short period of time. Please wait a moment before trying again.',
        ],
        500 => [
            'title' => 'Server Error',
            'message' => 'An unexpected error occurred on our server. Please try again later.',
        ],
        502 => [
            'title' => 'Bad Gateway',
            'message' => 'We received an invalid response from an internal service. Please try again shortly.',
        ],
        503 => [
            'title' => 'Service Unavailable',
            'message' => 'The service is temporarily unavailable. Please try again later.',
        ],
        504 => [
            'title' => 'Gateway Timeout',
            'message' => 'The server took too long to respond. Please try again in a few moments.',
        ],
    ],

];
