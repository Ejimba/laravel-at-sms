<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | API Key for Africa's Talking API
    |
    | Supported: string
    |
    */

    'api_key' => '',

    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | Username for Africa's Talking API
    |
    | Supported: string
    |
    */

    'username' => '',

    /*
    |--------------------------------------------------------------------------
    | Table prefix
    |--------------------------------------------------------------------------
    |
    | A prefix for the packages table names
    |
    | Supported: string
    |
    */

    'table_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Incoming SMS Configurations
    |--------------------------------------------------------------------------
    |
    | Configurations dealing with incoming sms.
    |
    */

    'incoming_sms' => array(

        /*
        |--------------------------------------------------------------------------
        | Incoming SMS Model
        |--------------------------------------------------------------------------
        |
        | The model used by the package for incoming sms. 
        |
        | Supported: Eloquent model
        |
        */

        'model' => 'Ejimba\LaravelAtSms\Models\IncomingSms',

        /*
        |--------------------------------------------------------------------------
        | Incoming SMS Callback
        |--------------------------------------------------------------------------
        |
        | The method that is called when a new sms arrives. 
        |
        | Supported: Function
        |
        */

        'callback' => '',

    ),

    /*
    |--------------------------------------------------------------------------
    | Outgoing SMS Configurations
    |--------------------------------------------------------------------------
    |
    | Configurations dealing with outgoing sms. 
    |
    */

    'outgoing_sms' => array(

        /*
        |--------------------------------------------------------------------------
        | Outgoing SMS Model
        |--------------------------------------------------------------------------
        |
        | The model used by the package for outgoing sms. 
        |
        | Supported: Eloquent model
        |
        */

        'model' => 'Ejimba\LaravelAtSms\Models\OutgoingSms',

        /*
        |--------------------------------------------------------------------------
        | Outgoing SMS Callback
        |--------------------------------------------------------------------------
        |
        | The method that is called when an sms is sent. 
        |
        | Supported: Function
        |
        */

        'callback' => '',

    ),
    
);