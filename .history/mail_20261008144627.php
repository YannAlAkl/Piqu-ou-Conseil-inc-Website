<?php

return [
'default' => env('MAIL_MAILER', 'smtp'),

'mailers' => [
    
    'smtp' => [
        'transport' => 'smtp',
        'scheme' => env(' '),
        'url' => env('MAIL_URL'),
        'host' => env('MAIL_HOST', '127.0.0.1')
    ]
]
]
