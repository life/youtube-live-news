<?php

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '5432',
        'name' => getenv('DB_NAME') ?: 'youtube_live',
        'user' => getenv('DB_USER') ?: 'app',
        'pass' => getenv('DB_PASS') ?: 'secret',
    ],
];
