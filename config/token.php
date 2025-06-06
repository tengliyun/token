<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Token Guard
    |--------------------------------------------------------------------------
    |
    | Here you may specify which authentication guard Token will use when
    | authenticating users. This value should correspond with one of your
    | guards that is already present in your "auth" configuration file.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | Token uses encryption keys while generating secure access tokens for
    | your application. By default, the keys are stored as local files but
    | can be set via environment variables when that is more convenient.
    |
    */

    'private_key' => env('TOKEN_PRIVATE_KEY'),

    'public_key' => env('TOKEN_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Token Database Connection
    |--------------------------------------------------------------------------
    |
    | By default, Token's models will utilize your application's default
    | database connection. If you wish to use a different connection you
    | may specify the configured name of the database connection here.
    |
    */

    'connection' => env('TOKEN_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Client UUIDs
    |--------------------------------------------------------------------------
    |
    | By default, Token uses auto-incrementing primary keys when assigning
    | IDs to clients. However, if Token is installed using the provided
    | --uuids switch, this will be set to "true" and UUIDs will be used.
    |
    */

    'client_uuids' => false,

    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    |
    | ISO 8601 duration format
    | You can customize how long access and refresh tokens should remain valid.
    | get used while issuing fresh personal access tokens to your users.
    |
    | For example:
    |   - 'PT2H' = 2 hours
    |   - 'P15D' = 15 days
    |
    */

    'expiration' => [
        'access_token'  => env('ACCESS_TOKEN_EXPIRES_IN', 'PT2H'),
        'refresh_token' => env('REFRESH_TOKEN_EXPIRES_IN', 'P15D'),
    ],

];
