<?php
/**
 * config_cache
 *
 * 缓存相关配置
 *
 * @package config
 */
return [
    'filecache' => [
        'sType' => 'file',
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
    'memcache' => [
        'sType' => 'memcached',
        'aServerList' => []
    ],
    'orm' => [
        'sType' => 'file',
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