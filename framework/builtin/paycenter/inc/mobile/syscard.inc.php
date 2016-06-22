<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
load()->model('activity');
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'consume';
if($op == 'consume') {
	$type = intval($_GPC['type']);
	$recid = trim($_GPC['recid']);
	$code_info = pdo_get('activity_coupon_record', array('recid' => $recid), array('code'));
	$qr_code = $code_info['code'];
	if($_W['isajax']) {
		$code = trim($_GPC['code']);
		$record = pdo_get('activity_coupon_record', array('code' => $code));
		if(empty($record)) {
			message(error('-1', '优惠券记录不存在'), '', 'ajax');
		}
		$operator = $_W['user']['name'];
		$clerk_id = $_W['user']['clerk_id'];
		$store_id = $_W['user']['store_id'];
		if($type == '1') {
			$status = activity_coupon_use($record['uid'], $code, $operator, $clerk_id, '', 'system', 3, $store_id);
		} else {
			$status = activity_token_use($record['uid'], $code, $operator, $clerk_id, '', 'system', 3, $store_id);
		}
		if (!is_error($status)) {
			message(error('0', ''),'', 'ajax');
		} else {
			message(error('-1', $status['message']),'' , 'ajax');
		}
	}
}
include $this->template('syscard');