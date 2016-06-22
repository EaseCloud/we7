<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
paycenter_check_login();
$store_name = $_W['user']['store_name'];
$clerk_name = $_W['user']['name'];
if($_GPC['do'] == 'more') {
	$clerk_info = pdo_get('mc_members',array('uid' => $_W['user']['uid']),array('mobile'));
}

include $this->template('more');