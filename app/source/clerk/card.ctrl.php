<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('use');
$do = in_array($do, $dos) ? $do : 'use';

if($do == 'use') {
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

				if($card_setting['discount_type'] > 0 && !empty($card_setting['discount'])) {
			$discount = $card_setting['discount'][$member['groupid']];
			if(!empty($discount['condition']) && !empty($discount['discount']) && $credit >= $discount['condition']) {
				if($card_setting['discount_type'] == 1) {
					$discount_credit = $credit - $discount['discount'];
					$discount_str = "，该会员属于【{$member['groupname']}】，可享受【满{$discount['condition']}元减{$discount['discount']}元】，最终支付【{$discount_credit}】元";
				} else {
					$rate = $discount['discount'];
					$discount_credit = $credit * $rate;
					$discount_str = "，该会员属于【{$member['groupname']}】，可享受【满{$discount['condition']}元打{$rate}折】，最终支付【{$discount_credit}】元";
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
				$member['uid'],
				"使用会员卡消费【{$discount_credit}】元,消费返积分比率：【1:{$card_setting['grant_rate']}】,共赠送积分{$credit1}",
				'card',
				$clerk['id']
			);
			mc_credit_update($member['uid'], 'credit1', $credit1, $log_credit1);
			$discount_str .= "，消费返积分比率：【1:{$card_setting['grant_rate']}】,共赠送积分{$credit1}";
		}
		$log_credit2 = array(
			$member['uid'],
			"使用会员卡消费【{$credit}】元 {$discount_str},消费门店：{$store_str}",
			'card',
			$clerk['id']
		);
		mc_credit_update($member['uid'], 'credit2', -$discount_credit, $log_credit2);
		mc_notice_credit2($card_member['openid'], $member['uid'], -$discount_credit, $credit1, $store_str);
		message("消费成功，共扣除余额{$discount_credit}元，赠送{$credit1}积分", url('clerk/check'), 'success');
	}

	if($card_setting['discount_type'] != 0 && !empty($card_setting['discount'])) {
		$discount = $card_setting['discount'];
		if(!empty($discount[$member['groupid']])) {
			$tips = "该会员所在的会员组: {$_W['account']['groups'][$member['groupid']]['title']} ,可享受满 {$discount[$member['groupid']]['condition']} ";
			if($card_setting['discount_type'] == 2) {
				$tips .= "打 {$discount[$member['groupid']]['discount']} 折";
			} else {
				$tips .= "减 {$discount[$member['groupid']]['discount']} 元";
			}
			$mine_discount = $discount[$member['groupid']];
		}
	}
}
template('clerk/card');
