<?php

use Token\JWT\Signature\Rsa\RS512;

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
    | Token Signer Class
    |--------------------------------------------------------------------------
    |
    | This option defines the signer class used for signing and verifying JWTs.
    |
    | Supported: ES256::class, ES384::class, ES512::class,
    |            RS256::class, RS384::class, RS512::class,
    |            HS256::class, HS384::class, HS512::class
    |
    */

    'signer' => RS512::class,

    /*
    |--------------------------------------------------------------------------
    | Encryption Keys
    |--------------------------------------------------------------------------
    |
    | These keys are used for signing and verifying tokens when using
    | asymmetric algorithms such as RS256, RS512, or ES512. By default,
    | the keys are stored as local files, but you may also load them
    | from environment variables if that is more convenient.
    |
    */

    'private_key' => env('TOKEN_PRIVATE_KEY', 'token_private_key.pem'),

    'public_key' => env('TOKEN_PUBLIC_KEY', 'token_public_key.pem'),

    /*
    |--------------------------------------------------------------------------
    | Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is used for signing tokens when using symmetric algorithms
    | such as HMAC (e.g., HS256, HS512). It must be provided as plain text
    | and can be set via environment variables for convenience.
    |
    */

    'secret_key' => env('TOKEN_SECRET_KEY', env('APP_KEY')),

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

    'table' => env('TOKEN_TABLE', 'auth_tokens'),

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
