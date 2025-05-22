<?php

return [
    'node_binary' => env('NODE_PATH', 'node'),
    'npm_binary' => env('NPM_PATH', 'npm'),
    'bin_path' => env('BROWSERSHOT_BIN_PATH'),
    
    // Optional: Timeout in seconds
    'timeout' => 60,
    
    // Optional: Set custom HTTP headers
    'headers' => [],
    
    // Optional: Set additional options
    'options' => [
        'args' => ['--no-sandbox'],
    ],
];