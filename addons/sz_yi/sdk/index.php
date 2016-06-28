<?php

define('SDK_ROOT', SZ_YI_PATH . 'sdk/');
define('DEVELOP_ROOT', SZ_YI_PATH . 'develop/');

require_once 'debugger.php';
require_once 'hook.php';

if (is_file(__DIR__ . '/../develop/index.php')) {
    require_once __DIR__ . '/../develop/index.php';
} 
