<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Xero Laravel configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the Langley Foxall
    | Xero Laravel package.
    |
    */

    'apps' => [
        'default' => [
            'client_id'     => '3719B57BC90A4AEAA803BC5AC2112052',
            'client_secret' => 'EZOcqxxwuTOzS9e9Y0HKoizGMarl6LXWUBtuiKdy3w_IP7Gv',
            'redirect_uri'  => 'https://cd33ee014b66.ngrok.io/xero/callback',
            'scope'         => 'openid email profile offline_access accounting.settings.read',
        ],
    ],
];
