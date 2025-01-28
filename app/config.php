<?php

return [
    'name' => 'TinyExplorer',
    'folder_name' => 'tinyexplorer',
    'jwtSecretKey' => '67e15397648bd6c3652d3f33de8612bd',
    'origins' => [
        'http://localhost',
    ],
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'dbname' => 'tinyexplorer',
        'port' => '3306',
        'charset' => 'utf8',
    ],
    'roles' => [
        'admin' => 3,
        'normal' => 0,
    ],
    'env' => 'dev',
    'version' => '1.3',
];