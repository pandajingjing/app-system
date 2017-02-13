<?php
return [
    'aRouteList' => [
        'controller_sys_crossdomain' => [
            '/^\/crossdomain\.xml$/i'
        ],
        'controller_sys_robot' => [
            '/^\/robot\.txt$/i'
        ],
        'controller_app_test' => [
            '/^\/(\w+)-(\w+)-(\w+)/i',
            [
                's1',
                's2',
                's3'
            ],
            '/{s1}-{s2}-{s3}'
        ]
    ]
];