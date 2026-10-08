<?php

return [
'default' => env('MAIL_MAILER', 'smtp'),

'mailers' => [
    
    'smtp' => [
        'transport' => 'smtp',
        'scheme' => env(' '),
        'url' => env('MAIL_URL'),
        'host' => env('MAIL_HOST', '127.0.0.1'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'Timeout' => null,
        'local_domain' => env('MAIL_EHLO_DOMAIN', pase_url((string) env('APP_URL'))[null),   
    ]
]
]
