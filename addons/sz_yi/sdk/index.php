<?php

define('SDK_ROOT', SZ_YI_PATH . 'sdk/');
define('DEVELOP_ROOT', IA_ROOT . '/develop/');

require_once 'debugger.php';
require_once 'hook.php';

if (is_file(DEVELOP_ROOT . 'index.php')) {
    require_once DEVELOP_ROOT . 'index.php';
} 
