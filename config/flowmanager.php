<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FlowManager Administrator
    |--------------------------------------------------------------------------
    |
    | Initial administrator account used to access a fresh installation.
    | Credentials are stored in the local environment file and must never
    | be committed to the repository.
    |
    */

    'admin' => [
        'name' => env('FLOWMANAGER_ADMIN_NAME', 'FlowManager Administrator'),
        'email' => env('FLOWMANAGER_ADMIN_EMAIL', 'admin@flowmanager.test'),
        'password' => env('FLOWMANAGER_ADMIN_PASSWORD'),
    ],

];