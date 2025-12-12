<?php

return [
    'container' => env('NGINX_CONTAINER', 'nginx-manager-nginx-hosts'),
    'conf_path' => env('NGINX_CONF_PATH', '/home/appuser/conf.d'),
    'hosts_root' => env('NGINX_HOSTS_ROOT', '/home/appuser/virtual'),
];

