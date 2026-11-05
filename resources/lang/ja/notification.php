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
    'promo'    => 'プロモーション',
    'amenity'  => 'アメニティ',
    'order'    => '注文',
    'food'     => '食品',

    // Amenities message
    'amenities' => [
        'title_add'    => '新しいアメニティを発見しよう！',
        'title_update' => '最新のアメニティをチェックしよう！',
    ],

    // Promos message
    'promos' => [
        'title_add'    => '新しいオファーを探してみよう！',
        'title_update' => '最新のオファーをチェックしよう！',
    ],

    // Room access message
    'check_in_out' => [
        'title'    => '客室アクセス',
    ],
    'scene' => [
        'title' => 'シーン実行',
    ],

    // Electric Meter Alerts
    'electric_meter' => [
        'critical_load_unbalance' => [
            'title' => '重大な負荷不均衡を検出',
        ],
        'report_generation_success' => [
            'title'   => 'レポート完成',
            'message' => '電気レポートのダウンロードの準備が整いました。'
        ],
        'report_generation_fail' => [
            'title'   => 'レポート生成失敗',
            'message' => 'レポートの生成に失敗しました：[:filename]。'
        ],
    ],

    // Order message
    'orders' => [
        'new_order_placed' => '新しい注文が入りました',
        'order_preparing' => '注文を準備中です',
        'order_completed' => '注文が完了しました',
    ],
];
