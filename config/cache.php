<?php
return [
    'filecache' => [
        'sType' => 'file',
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