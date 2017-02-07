<?php
return [
    'route' => [
        '/^\/crossdomain\.xml$/i' => [
            'controller_sys_crossdomain'
        ],
        '/^\/robot\.txt$/i' => [
            'controller_sys_robot'
        ],
        '/^\/(\w+)-(\w+)-(\w+)/i' => [
            'controller_app_test',
            [
                's1',
                's2',
                's3'
            ],
            '/{s1}-{s2}-{s3}'
        ]
    ]
];