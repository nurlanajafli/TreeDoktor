<?php

//$ci = &get_instance();
include(APPPATH . 'config/database.php');
return [
    'migrations' => config_item('migration_table'),
    'fetch' => \PDO::FETCH_OBJ,
    'default' => 'ci',
    'connections' => [
        'ci' => [
            'driver' => $db['default']['dbdriver'],
            'host' => $db['default']['hostname'],
            'database' => $db['default']['database'],
            'username' => $db['default']['username'],
            'password' => $db['default']['password'],
            'charset' => $db['default']['char_set'],
            'collation' => $db['default']['dbcollat'],
            'prefix' => $db['default']['dbprefix'],
        ]
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'client' => 'predis',

        'default' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0
        ],

        'cache' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0
        ],
    ],
];