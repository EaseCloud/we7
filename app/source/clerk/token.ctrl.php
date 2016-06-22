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
	load()->model('activity');
	$token = pdo_get('activity_coupon', array('uniacid' => $_W['uniacid'], 'couponid' => $id));
	if(empty($token)) {
		message('优惠券不存在或已删除', '', 'error');
	}
	$own_func = 'activity_token_owned';
	$use_func = 'activity_token_use';
	if($token['type'] == 1) {
		$own_func = 'activity_coupon_owned';
		$use_func = 'activity_coupon_use';
	}
	$data = $own_func($uid, array('couponid' => $id, 'used' => 1));
	$data = $data['data'][$id];
	if(empty($data)) {
		message('该会员没有领取该优惠券或领取的优惠券已核销', '', 'error');
	}

	if(checksubmit('submit')) {
		if(!empty($clerk)) {
			$status = $use_func($uid, $id, $clerk['name'], $clerk['id']);
			if (!is_error($status)) {
				message('核销优惠券成功！', url('clerk/check'), 'success');
			} else {
				message($status['message'], url('clerk/check'), 'error');
			}
		}
	}
}
template('clerk/token');


