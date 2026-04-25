<?php

return [
    'default_timezone' => 'Asia/Shanghai',
    'app_debug' => true,
    'app_trace' => false,
    'default_lang' => 'zh-cn',
    'default_module' => 'index',
    'default_controller' => 'Index',
    'default_action' => 'index',
    'url_route_on' => true,
    'url_route_must' => false,
    'url_convert' => true,
    'url_suffix' => '',
    'session' => [
        'id'             => '',
        'var_session_id' => '',
        'prefix'         => 'think',
        'type'           => '',
        'auto_start'     => true,
        'httponly'       => true,
        'secure'         => false,
    ],
    'cookie' => [
        'prefix'    => '',
        'expire'    => 0,
        'path'      => '/',
        'domain'    => '',
        'secure'    => false,
        'httponly'  => false,
        'setcookie' => true,
    ],
];
