<?php 

return [
    'pdo' => [
        'driver' => ENV['db_driver'],
        'dbname' => ENV['db_name'],
        'host' => ENV['db_host'],
        'port' => ENV['db_port'],
        'username' => ENV['db_username'],
        'passwd' => ENV['db_password'],
        'options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ]
    ],
    'migrations' => ['path' => 'src/Database/Migrations', 'namespace' => 'Src\Database\Migrations'],
    'seeders' => ['path' => 'src/Database/Seeders', 'namespace' => 'Src\Database\Seeders']
];