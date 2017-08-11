<?php

return [
    'components' => [
        /**
         * 后台数据库
         */
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
            'dsn' => 'mysql:host=10.66.222.80;port=3306;dbname=scp_2144_cn',
            'username' => 'scp_2144_cn',
            'password' => 'kIEmTOB4yOYM',
        ],
        'log_scp' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
            'dsn' => 'mysql:host=10.66.222.80;port=3306;dbname=log_scp_2144_cn',
            'username' => 'scp_2144_cn',
            'password' => 'kIEmTOB4yOYM',
        ],
        /**
         * Redis 缓存
         */
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => '10.66.226.69',
                'port' => 6379,
                'database' => 10,
                'password' => 'crs-m84lsvay:fdCA9tUi4IJI',
            ],
        ],

        /**
         * 统计Redis
         * database 0
         */
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '10.66.226.69',
            'port' => 6379,
            'database' => 0,
            'password' => 'crs-m84lsvay:fdCA9tUi4IJI',
        ],
    ],
];
