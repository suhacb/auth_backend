<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Keycloak Configuration
    |--------------------------------------------------------------------------
    |
    | These values are used to configure the Keycloak authentication service.
    | You should set these values in your ".env" file to match your Keycloak
    | server settings.
    |
    */

    'base_url' => env('KEYCLOAK_BASE_URL', 'http://localhost:7080'),
    'realm' => env('KEYCLOAK_REALM', 'myrealm'),
    'grant_type' => env('KEYCLOAK_GRANT_TYPE', 'password'),
    'client_id' => env('KEYCLOAK_CLIENT_ID', 'myclient'),
    'client_secret' => env('KEYCLOAK_CLIENT_SECRET', 'mysecret'),
    'scope' => env('KEYCLOAK_SCOPE', 'openid'),
    'redirect_uri' => env('KEYCLOAK_REDIRECT_URI', 'http://localhost/callback'),
    
    // HTTP client hardening
    'timeout'       => env('KEYCLOAK_TIMEOUT', 5), // seconds
    'retry'         => [
        'times' => (int) env('KEYCLOAK_HTTP_RETRY_TIMES', 1),
        'sleep' => (int) env('KEYCLOAK_HTTP_RETRY_SLEEP', 100), // ms
    ],
];