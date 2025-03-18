<?php

return [
    'apps' => [
        [
            'id' => env('PUSHER_APP_ID', 'local'),
            'name' => env('APP_NAME', 'Laravel'),
            'key' => env('PUSHER_APP_KEY', 'local'),
            'secret' => env('PUSHER_APP_SECRET', 'local'),
            'enable_client_messages' => true,
            'enable_statistics' => false,
        ],
    ],

    'debug' => false,

    'log_file' => storage_path('logs/websockets.log'),

    'port' => env('PUSHER_PORT', 6001),
];