<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
define('SZ_YI_DEBUG', false);//false
!defined('SZ_YI_PATH') && define('SZ_YI_PATH', IA_ROOT . '/addons/sz_yi/');
!defined('SZ_YI_CORE') && define('SZ_YI_CORE', SZ_YI_PATH . 'core/');
!defined('SZ_YI_PLUGIN') && define('SZ_YI_PLUGIN', SZ_YI_PATH . 'plugin/');
!defined('SZ_YI_INC') && define('SZ_YI_INC', SZ_YI_CORE . 'inc/');
!defined('SZ_YI_URL') && define('SZ_YI_URL', $_W['siteroot'] . 'addons/sz_yi/');
!defined('SZ_YI_STATIC') && define('SZ_YI_STATIC', SZ_YI_URL . 'static/');
!defined('SZ_YI_PREFIX') && define('SZ_YI_PREFIX', 'sz_yi_');
