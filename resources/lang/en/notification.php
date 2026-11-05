<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Language Lines
    |--------------------------------------------------------------------------
    |
    | These language lines are used for system notifications and alerts
    | displayed to users throughout the application.
    |
    */

    // Notification nature
    'promo'    => 'promos',
    'amenity'  => 'amenities',
    'order'    => 'orders',
    'food'     => 'foods',

    // Amenities message
    'amenities' => [
        'title_add'    => 'Discover Our Newest Amenities!',
        'title_update' => 'Check Out Our Latest Amenities!',
    ],

    // Promos message
    'promos' => [
        'title_add'    => 'Explore New Offerings!',
        'title_update' => 'Check Out Our Latest Offerings!'
    ],

    // Room access message
    'check_in_out' => [
        'title'    => 'Room Access',
    ],
    'scene' => [
        'title' => 'Scene Executed',
    ],

    // Electric Meter Alerts
    'electric_meter' => [
        'critical_load_unbalance' => [
            'title'   => 'Critical Load Imbalance Detected',
        ],
        'report_generation_success' => [
            'title'   => 'Report Ready',
            'message' => 'Your electric report is ready to download.'
        ],
        'report_generation_fail' => [
            'title'   => 'Report Generation Failed',
            'message' => 'Failed to generate report: [:filename].'
        ],
    ],

    // Order message
    'orders' => [
        'new_order_placed' => 'New Order Placed',
        'order_preparing' => 'Order Preparing',
        'order_completed' => 'Order Completed',
        'order_cancelled' => 'Order Cancelled'
    ],
];
