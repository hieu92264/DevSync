<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authorization Enforcement
    |--------------------------------------------------------------------------
    |
    | Authentication (JWT) and context resolution always remain enabled. Set
    | this to false only in a local development environment to bypass
    | membership and permission checks while building features.
    |
    */
    'enforced' => env('AUTHORIZATION_ENFORCED', true),
];
