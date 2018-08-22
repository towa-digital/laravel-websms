<?php

return [

    /**
     * The endpoints url
     */
    'endpoint' => 'https://api.websms.com/rest/',

    'auth' => [

        /**
         * The username and password combination
         */
        'username' => env('WEBSMS_USERNAME', null),
        'password' => env('WEBSMS_PASSWORD', null),

        /**
         * The access token
         */
        'accessToken' => env('WEBSMS_ACCESS_TOKEN', null),
    ],

];