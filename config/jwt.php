<?php

return [
    'issuer' => env('JWT_ISSUER', 'CREASOFT'),
    'audience' => env('JWT_AUDIENCE', 'Client'),
    'signature' => env('JWT_SIGNATURE', ''),
    'leeway' => (int) env('JWT_LEEWAY', 0),
];
