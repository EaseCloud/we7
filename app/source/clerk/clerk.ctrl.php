<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('check', 'card', 'token');
$do = in_array($do, $dos) ? $do : 'check';
load()->model('clerk');

$clerk = clerk_check();
if(is_error($clerk)) {
	message('您不是操作店员，没有使用该功能的权限', '', 'error');
}

if($do != 'check') {
} else {
	template('mc/clerk');
}

if($do == 'card') {
	load()->model('card');
	$card_setting = card_setting();
	$card_params = json_decode($card_setting['params'], true);
	if (!empty($card_params)) {
		foreach ($card_params as $key => $value) {
			if ($value['id'] == 'cardActivity') {
				$grant_rate = $value['params']['grant_rate'];
			}
		}
	}
	$card_setting['grant_rate'] = $grant_rate;
	if(is_error($card_setting)) {
		message($card_setting['message'], referer(), 'error');
	}
	$card_member = card_member($uid);
	if(is_error($card_member)) {
		message($card_member['message'], referer(), 'error');
	}
		$stores = pdo_fetchall('SELECT id,business_name FROM ' . tablename('activity_stores') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']), 'id');

	if(checksubmit()) {
		$credit = max(0, floatval($_GPC['credit']));
		$discount_credit = $credit;
		$store_id = intval($_GPC['store_id']);
		$store_str = (!$store_id || empty($stores[$store_id])) ? '未知' : $stores[$store_id]['business_name'];
		if(!$credit || $credit <= 0) {
			message('请输入消费金额', referer(), 'error');
		}
				if($card_setting['discount_type'] > 0) {
			$discount = $card_setting['discount'][$_W['member']['groupid']];
			if(!empty($discount['condition']) && !empty($discount['discount']) && $credit >= $discount['condition']) {
				if($card_setting['discount_type'] == 1) {
					$discount_credit = $credit - $discount['discount'];
					$discount_str = "，该会员属于【{$_W['member']['groupname']}】，可享受【满{$discount['condition']}元减{$discount['discount']}元】，最终支付【{$discount_credit}】元";
				} else {
					$rate = $discount['discount']/10;
					$discount_credit = $credit * $rate;
					$discount_str = "，该会员属于【{$_W['member']['groupname']}】，可享受【满{$discount['condition']}元打{$rate}折】，最终支付【{$discount_credit}】元";
				}
				if($discount_credit < 0) {
					$discount_credit = 0;
				}
			}
		}
		if($member['credit2'] < $discount_credit) {
			message('余额不足', referer(), 'error');
		}
				if($card_setting['grant_rate'] > 0) {
			$credit1 = $discount_credit * $card_setting['grant_rate'];
			$log_credit1 = array(
				$_W['member']['uid'],
				"使用会员卡消费【{$discount_credit}】元,消费返积分比率：【1:{$card_setting['grant_rate']}】,共赠送积分{$credit1}",
				'card',
				$clerk['id']
			);
			mc_credit_update($_W['member']['uid'], 'credit1', $credit1, $log_credit1);
			$discount_str .= "，消费返积分比率：【1:{$card_setting['grant_rate']}】,共赠送积分{$credit1}";
		}
		$log_credit2 = array(
			$_W['member']['uid'],
			"使用会员卡消费【{$credit}】元 {$discount_str},消费门店：{$store_str}",
			'card',
			$clerk['id']
		);
		mc_credit_update($_W['member']['uid'], 'credit2', -$discount_credit, $log_credit2);
		message("消费成功，共扣除余额{$discount_credit}元，赠送{$credit1}积分", url('mc/clerk'), 'success');
	}

	if($card_setting['discount_type'] != 0) {
		$discount = $card_setting['discount'];
		$tips = "该会员所在的会员组: {$_W['account']['groups'][$member['groupid']]['title']} ,可享受满 {$discount[$member['groupid']]['condition']} ";
		if($card_setting['discount_type'] == 2) {
			$tips .= "打 {$discount[$member['groupid']]['discount']} 折";
		} else {
			$tips .= "减 {$discount[$member['groupid']]['discount']} 元";
		}
		$mine_discount = $discount[$member['groupid']];
	}
	template('mc/clerk-card');
	exit();
}

if($do == 'token') {
	$id = intval($_GPC['id']);
	load()->model('activity');
	$token = pdo_get('activity_coupon', array('uniacid' => $_W['uniacid'], 'id' => $id));
	if(empty($token)) {
		message('优惠券不存在或已删除', referer(), 'error');
	}
	$own_func = 'activity_token_owned';
	$use_func = 'activity_token_use';
	if($token['type'] == 1) {
		$own_func = 'activity_coupon_owned';
		$use_func = 'activity_coupon_use';
	}
	$data = $own_func($_W['member']['uid'], array('couponid' => $id, 'used' => 1));
	$data = $data['data'][$id];
	if(empty($data)) {
		message('该会员没有领取该优惠券或领取的优惠券已核销', referer(), 'error');
	}
	if(checksubmit('submit')) {
		load()->model('user');
		$password = $_GPC['password'];
		$sql = 'SELECT * FROM ' . tablename('activity_clerks') . " WHERE `uniacid` = :uniacid AND `password` = :password";
		$clerk = pdo_fetch($sql, array(':uniacid' => $_W['uniacid'], ':password' => $password));
		if(!empty($clerk)) {
			$status = $use_func($_W['member']['uid'], $id, $clerk['name']);
			if (!is_error($status)) {
				message('代金券使用成功！', url('activity/token/mine', array('type' => $_GPC['type'])), 'success');
			} else {
				message($status['message'], url('activity/token/mine', array('type' => $_GPC['type'])), 'error');
			}
		}
		message('密码错误！', referer(), 'error');
	}

	template('mc/clerk-card');
	exit();
}
