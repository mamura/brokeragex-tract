<?php

return [
    // Path
    'root'      => dirname(__DIR__),
    'temp'      => dirname(__DIR__) . '/tmp',
    'public'    => dirname(__DIR__) . '/public',

    // Database
    'db'    => [
        'driver'    => 'mysql',
        'host'      => 'mysql-databases.test',
        'username'  => 'root',
        'password'  => 'root',
        'database'  => 'leoa',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'flags'     => [
            PDO::ATTR_PERSISTENT            => false,
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES      => true,
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND    => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
        ]
    ]
];
