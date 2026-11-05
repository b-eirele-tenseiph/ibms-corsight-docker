<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Logs Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during creating activity logs for
    | various events.
    |
    */

    // Log names
    'auth'     => '認証',
    'info'     => '情報',
    'warning'  => '警告',
    'error'    => 'エラー',

    // Device-related logs
    'battery' => [
        'message' => '現在のデバイスのバッテリーは :battery です。',
    ],

    // Environment-related logs
    'temperature' => [
        'message' => '現在の室温は :temp℃ です。',
    ],

    'co2' => [
        'message' => '現在の二酸化炭素濃度は :co2 PPM です。',
    ],

    'humidity' => [
        'message' => '現在の湿度は :humidity% です。',
    ],

    'scene' => [
        'need_to_configure' => '使用前にデバイスを設定する必要があります',
        'failed_to_execute' => 'シーンコマンドの実行に失敗しました',
    ]
];
