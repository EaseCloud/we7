<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('use');
$do = in_array($do, $dos) ? $do : 'use';

if($do == 'use') {
	$id = intval($_GPC['id']);
	$code = trim($_GPC['code']);
	if($id == 0 || empty($code)) {
		message('参数错误');
	}
	$record = pdo_get('coupon_record',  array('acid' => $_W['acid'], 'id' => $id, 'code' => $code));
	if(empty($record)) {
		message('卡券领取记录不存在');
	}
	$card = pdo_get('coupon', array('acid' => $_W['acid'], 'card_id' => $record['card_id']));
	if(empty($card)) {
		message('卡券不存在或已删除');
	}
	$card['date_info'] = iunserializer($card['date_info']);
	if(checksubmit()) {
		load()->classs('coupon');
		$coupon = new coupon($_W['acid']);
		if(is_null($coupon)) {
			message('系统错误');
		}
		$status = $coupon->ConsumeCode(array('code' => $record['code']));
		if(is_error($status)) {
			message($status['message']);
		}
		pdo_update('coupon_record', array('status' => 3, 'clerk_id' => $clerk['id'], 'clerk_name' => $clerk['name'], 'usetime' => TIMESTAMP), array('acid' => $_W['acid'], 'code' => $record['code']));
		message('核销微信卡券成功', url('clerk/check'), 'success');
	}
}
template('clerk/wechat');