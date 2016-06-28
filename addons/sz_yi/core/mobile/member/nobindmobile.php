<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$openid = m('user')->getOpenid();

$preUrl = $_COOKIE['preUrl'];
pdo_update('sz_yi_member', array('isjumpbind' => 1), array('openid' => $openid));
$url = $this->createMobileUrl('shop');
redirect($url);
