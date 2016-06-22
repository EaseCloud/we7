<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'detail';
load()->model('paycenter');
load()->model('mc');
if($op == 'detail') {
	$id = intval($_GPC['id']);
	$order = pdo_get('paycenter_order', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($order)) {
		$info = '订单不存在';
	} elseif($order['status'] == 0) {
		$info = '订单尚未支付';
	} else {
		$store_id = $order['store_id'];
		$types = pc_order_types();
		$trade_types = pc_order_trade_types();
		$status = pc_order_status();
		$store_name = pdo_get('activity_stores', array('id' => $store_id), array('business_name'));
	}
}
include $this->template('trading-detail');