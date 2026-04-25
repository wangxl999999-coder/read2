<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'novel_reading',
            'username' => 'root',
            'password' => '',
            'hostport' => '3306',
            'charset' => 'utf8mb4',
            'prefix' => '',
            'debug' => true,
            'deploy' => 0,
            'rw_separate' => false,
            'master_num' => 1,
            'params' => [],
            'fields_strict' => true,
            'resultset_type' => 'array',
            'auto_timestamp' => false,
            'id_field_type' => 'int',
            'sql_explain' => false,
        ],
    ],
];
