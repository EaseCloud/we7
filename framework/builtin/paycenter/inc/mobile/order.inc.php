<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';

if($op == 'index') {
	global $_W, $_GPC;
	if(checksubmit()) {
		$fee = trim($_GPC['fee']) ? trim($_GPC['fee']) : message('订单金额不能为空', '', 'error');
		$body = trim($_GPC['body']);
		$openid = trim($_GPC['openid']) ? trim($_GPC['openid']) : message('用户信息错误',  '', 'error');

		$data = array(
			'uniacid' => $_W['uniacid'],
			'nickname' => trim($_GPC['nickname']),
			'openid' => $openid,
			'status' => 0,
			'type' => 'wechat',
			'trade_type' => 'jsapi',
			'fee' => $fee,
			'body' => $body,
			'createtime' => TIMESTAMP,
		);
		pdo_insert('paycenter_order', $data);
		$id = pdo_insertid();
		message('提交订单成功,转入支付页面...', $this->createMobileUrl('detail', array('id' => $id)), 'success');
	}
	if(!empty($_GPC['id'])) {
		$order = json_decode(base64_decode(urldecode($_GPC['id'])), true);
	} else {
		$order = array();
	}
		if(is_error($fans) || empty($fans)) {
			}

	$setting = uni_setting_load('payment');
	$payment = $setting['payment'];
}

include $this->template('order');
