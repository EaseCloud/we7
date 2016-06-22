<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

@session_start();
$cookieid = "__cookie_sz_yi_userid_{$_W['uniacid']}";
setcookie($cookieid, '');

$url = $this->createMobileUrl('shop');
redirect($url);
