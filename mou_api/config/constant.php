<?php

return [

    'gender' => [
        'woman' => 0,
        'man' => 1,
    ],
    /**
     * EVENT TABLE
     */
    'event' => [
        'type' => [
            'event' => 'EVENT',
            'task' => 'TASK',
            'project_task' => 'PROJECT_TASK',
            'roster' => 'ROSTER',
        ],
        'repeat' => [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ],
        'alarm' => [
            '5m' => '5m',
            '10m' => '10m',
            '30m' => '30m',
            '1h' => '1h',
            '1d' => '1d',
            '1w' => '1w',
        ],
        'status' => [
            'waiting' => 'W',
            'confirm' => 'Y',
            'deny' => 'N',
        ],
        'colors' => [
            'blue' => '#0E4AA4',
            'red' => '#F44336',
            'business' => [
                'not_waiting' => '#797878',
                'waiting' => '#EE3338',
            ],
            'personal' => [
                'not_waiting' => '#D9B066',
                'waiting' => '#EE3338',
            ],
        ],
    ],

    /**
     * Support language
     */
    'languages' => [
        'english' => 'en',
        'spanish' => 'es',
        'portuguese' => 'pt',
    ],
    'working_days' => [1, 2, 3, 4, 5],

    /**
     * Deeplink: open app
     * Use in email/sms
     */
    'personal_link' => env('PERSONAL_LINK', 'https://moupersonal.page.link'),
    'bussiness_link' => env('BUSSINESS_LINK', 'https://moubusiness.page.link'),

    'personal_bundle' => env('PERSONAL_BUNDLE', 'com.mou.personal'),
    'bussiness_bundle' => env('BUSSINESS_BUNDLE', 'com.mou.business'),

    'app_deeplink' => env('APP_DEEPLINK', 'https://moupersonal.page.link/bjYi'),
    'app_business_deeplink' => env('APP_BUSINESS_DEEPLINK', 'https://moubusiness.page.link/29hQ'),

    'pdfcrowd_name' => env('PDFCROWD_NAME', 'adquang'),
    'pdfcrowd_key' => env('PDFCROWD_KEY', '75e9e5c1d2dd9d604374d2b6f4dec544'),

    'app_store_id' => env('APP_STORE_ID', '1529384268'),
    'expired_time_email_change_phone' => 24, // hour

    'mail_contact' => 'contact@mou.center',
];
