<?php

return [

    

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // En desarrollo, permite todos los orígenes

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'supports_credentials' => false,

];