<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
load()->model('paycenter');
if($op == 'index') {
	$condition = ' WHERE uniacid = :uniacid AND status = 1 AND clerk_id = :clerk_id';
	$params = array(':uniacid' => $_W['uniacid'], ':clerk_id' => $_W['user']['clerk_id']);
	$limit = " ORDER BY id DESC";
	$orders = pdo_fetchall('SELECT * FROM ' . tablename('paycenter_order') . $condition . $limit, $params);
}
include $this->template('trading-record');