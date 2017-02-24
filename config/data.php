<?php
/**
 * config_data
 *
 * 数据存储相关配置
 *
 * @package config
 */
return [
    'test_db' => [
        'sType' => 'mysql',
        'sDSN' => 'mysql:host=127.0.0.1;dbname=test_db;port=3306',
        'sUserName' => 'app',
        'sUserPassword' => 'N71rS2bx+VcloDgnQEg',
        'aInitSQL' => [
            'set names utf8'
        ]
    ],
    'filecache' => [
        'sType' => 'filecache',
        'bCompress' => true,
        'aDirList' => [
            [
                '/tmp/filecache1',
                1
            ],
            [
                '/tmp/filecache2',
                5
            ]
        ]
    ],
    'memcached' => [
        'sType' => 'memcached',
        'aServerList' => [
            [
                '127.0.0.1',
                11211,
                1
            ],
            [
                '127.0.0.2',
                11211,
                5
            ]
        ]
    ],
    'redis' => [
        'sType' => 'redis',
        'aServer' => [
            '127.0.0.1',
            6379,
            1
        ],
        'sUserPassword' => '123456',
        'iIndex' => 1
    ],
    'ormcache' => [
        'sType' => 'filecache',
        'bCompress' => false,
        'aDirList' => [
            [
                '/tmp/filecache/orm',
                1
            ],
            [
                '/tmp/filecache/orm',
                5
            ]
        ]
    ]
];