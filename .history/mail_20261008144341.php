<?php

return [
'default' => env('MAIL_MAILER', 'smtp'),

'mailers' => [
    
    'smtp' => [
        'transport' => 'smtp',
        'scheme' => env('MAIL_S')
    ]
]
]
