<?php
$G_CONFIG['router']['route'] = [];
$G_CONFIG['router']['route']['/^\/crossdomain\.xml$/i'] = array(
    'controller_app_crossdomain',
);
$G_CONFIG['router']['route']['/^\/robot\.txt$/i'] = array(
    'controller_app_robot',
);
// $G_CONFIG['router']['route']['/^\/(\w+)-(\w+)-(\w+)/i'] = [
//     'controller_app_test',
//     [
//         's1',
//         's2',
//         's3'
//     ],
//     '/{s1}-{s2}-{s3}'
// ];