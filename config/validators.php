<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validator interfaces namespace
    |--------------------------------------------------------------------------
    |
    | You can specify the namespace used in your validator interfaces.
    | Once again, I like to put everything under the namespace of my app,
    | so my validator interfaces usually live under the namespace of my
    | application: "MyApp\Validators".
    |
    */
    'validator_interfaces_namespace' => 'Hi\Validators',

    /*
    |--------------------------------------------------------------------------
    | Base validators path
    |--------------------------------------------------------------------------
    |
    | By default the package considers that your interfaces live in
    | App/Validation and subfolders. You can however set this path to whatever
    | value you want. I personally like to locate all my project files inside
    | a folder located in "app", something like "app/MyApp/Validators".
    |
    */
    'validators_path' => app_path('Hi/Validators'),
];
