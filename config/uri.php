<?php
/**
 * config_uri
 *
 * 路径相关配置
 *
 * @package config
 */
return [
    'sCDNSchemeDomain' => [
        'sAlias' => [
            '/url/{s1}-{s2}-{s3}',
            [
                's1',
                's2',
                's3'
            ]
        ],
        'sAlias1' => [
            '/'
        ]
    ]
];