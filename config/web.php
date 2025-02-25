<?php

return [
    'folder_name' => 'tinyexplorer',
    'jwtSecretKey' => '67e15397648bd6c3652d3f33de8612bd',
//    'origins' => [
//        'http://localhost',
//    ],
    'db' => [
        'driver' => 'sqlite',
        'mysql' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'dbname' => 'tinyexplorer',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'sqlite' => [
            'path' => 'database.sqlite',
        ]
    ],
//    'env' => 'dev',
];