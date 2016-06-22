<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('paycenter_wxmicro_pay');
$_W['page']['title'] = '刷卡支付-微信收款';
$dos = array('pay', 'query');
$do = in_array($do, $dos) ? $do : 'pay';
load()->model('paycenter');

if($do == 'pay') {
	if($_W['isajax']) {
		$post = $_GPC['__input'];
		$fee = trim($post['fee']) ? trim($post['fee']) : message(error(-1, '订单金额不能为空'),  '', 'ajax');
		$body = trim($post['body']) ? trim($post['body']) : message(error(-1, '商品名称不能为空'),  '', 'ajax');
		$code = trim($post['code']);
		if($post['cash'] > 0 && empty($post['code'])) {
			message(error(-1, '授权码不能为空'),  '', 'ajax');
		}
		$total = $money = floatval($post['fee']);
		if(!$total) {
			message(error(-1, '消费金额不能为空'),  '', 'ajax');
		}
		$log = "系统日志:会员消费【{$total}】元";
		if($post['member']['uid'] > 0) {
			$user = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], 'uid' => $post['member']['uid']));
			if(empty($user)) {
				message(error(-1, '用户不存在'), '', 'ajax');
			}
			$user['groupname'] = $_W['account']['groups'][$user['groupid']]['title'];
			load()->model('card');
			$card = card_setting();
			load()->model('card');
			$member = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $user['uid']));
			if(!empty($card) && $card['status'] == 1 && !empty($member)) {
				$user['discount'] = $card['discount'][$user['groupid']];
				if(!empty($user['discount']) && !empty($user['discount']['discount'])) {
					if($total >= $user['discount']['condition']) {
						$log .= ",所在会员组【{$user['groupname']}】,可享受满【{$user['discount']['condition']}】元";
						if($card['discount_type'] == 1) {
							$log .= "减【{$user['discount']['discount']}】元";
							$money = $total - $user['discount']['discount'];
						} else {
							$discount = $user['discount']['discount'] * 10;
							$log .= "打【{$discount}】折";
							$money = $total * $user['discount']['discount'];
						}
						if($money < 0) {
							$money = 0;
						}
						$log .= ",实收金额【{$money}】元";
					}
				}
				$post_money = floatval($post['fact_fee']);
				if($post_money != $money) {
					message(error(-1, '实收金额错误'),  '', 'ajax');
				}

				$post_credit1 = intval($post['credit1']);
				if($post_credit1 > 0) {
					if($post_credit1 > $user['credit1']) {
						message(error(-1, '超过会员账户可用积分'),  '', 'ajax');
					}
				}

				$post_offset_money = trim($post['offset_money']);
				$offset_money = 0;
				if($post_credit1 && $card['offset_rate'] > 0 && $card['offset_max'] > 0) {
					$offset_money = min($card['offset_max'], $post_credit1/$card['offset_rate']);
					if($offset_money != $post_offset_money) {
						message(error(-1, '积分抵消金额错误'),  '', 'ajax');
					}
					$credit1 = $post_credit1;
					$log .= ",使用【{$post_credit1}】积分抵消【{$offset_money}】元";
				}
			}

			$credit2 = floatval($post['credit2']);
			if($credit2 > 0) {
				if($credit2 > $user['credit2']) {
					message(error(-1, '超过会员账户可用余额'),  '', 'ajax');
				}
				$log .= ",使用余额支付【{$credit2}】元";
			}
		} else {
			$post['cash'] = $post['fee'];
		}
		$cash = floatval($post['cash']);
		$sum = $credit2 + $cash + $offset_money;
		if($sum != $money) {
			message(error(-1, '支付金额不等于实收金额'),  '', 'ajax');
		}
		if($cash > 0) {
			$log .= ",使用刷卡支付【{$cash}】元";
		} else {
						$data = array(
				'uniacid' => $_W['uniacid'],
				'uid' => $member['uid'],
				'status' => 0,
				'type' => 'wechat',
				'trade_type' => 'micropay',
				'fee' => $total,
				'final_fee' => $money,
				'credit1' => $post_credit1,
				'credit1_fee' => $offset_money,
				'credit2' => $credit2,
				'cash' => $cash,
				'remark' => $log,
				'clerk_id' => $_W['user']['clerk_id'],
				'store_id' => $_W['user']['store_id'],
				'clerk_type' => $_W['user']['clerk_type'],
				'createtime' => TIMESTAMP,
				'status' => 1,
				'paytime' => TIMESTAMP,
				'credit_status' => 1,
			);
			pdo_insert('paycenter_order', $data);
			load()->model('mc');
			if($post_credit1 > 0) {
				$status = mc_credit_update($order['uid'], 'credit1', -$post_credit1, array(0, "会员刷卡消费,使用积分抵现,扣除{$order['credit1']}积分", 'system', $_W['user']['clerk_id'], $order['store_id'], $_W['user']['clerk_type']));
			}
			if($credit2 > 0) {
				$status = mc_credit_update($order['uid'], 'credit2', -$credit2, array(0, "会员刷卡消费,使用余额支付,扣除{$order['credit2']}余额", 'system', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
			}
			message(error(0, '支付成功'), '', 'ajax');
		}
		if(!empty($_GPC['remark'])) {
			$note = "店员备注：{$_GPC['remark']}";
		}
		$log = $note.$log;

		$isexist = pdo_get('paycenter_order', array('uniacid' => $_W['uniacid'], 'auth_code' => $code));
		if($isexist) {
			message(error(-1, '每个二维码仅限使用一次，请刷新再试'), '', 'ajax');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $member['uid'],
			'status' => 0,
			'type' => 'wechat',
			'trade_type' => 'micropay',
			'fee' => $total,
			'final_fee' => $money,
			'credit1' => $post_credit1,
			'credit1_fee' => $offset_money,
			'credit2' => $credit2,
			'cash' => $cash,
			'remark' => $log,
			'body' => $body,
			'auth_code' => $code,
			'clerk_id' => $_W['user']['clerk_id'],
			'store_id' => $_W['user']['store_id'],
			'clerk_type' => $_W['user']['clerk_type'],
			'createtime' => TIMESTAMP,
		);
		pdo_insert('paycenter_order', $data);
		$id = pdo_insertid();
		load()->classs('pay');
		$pay = Pay::create();
		$params = array(
			'tid' => $id,
			'module' => 'paycenter',
			'type' => 'wechat',
			'fee' => $cash,
			'body' => $body,
			'auth_code' => $code,
		);
		$pid = $pay->buildPayLog($params);
		if(is_error($pid)) {
			message($pid,  '', 'ajax');
		}
		$log = pdo_get('core_paylog', array('plid' => $pid));
		pdo_update('paycenter_order', array('pid' => $pid, 'uniontid' => $log['uniontid']), array('id' => $id));
		$data = array(
			'out_trade_no' => $log['uniontid'],
			'body' => $body,
			'total_fee' => $log['fee'] * 100,
			'auth_code' => $code,
			'uniontid' => $log['uniontid']
		);
		$result = $pay->buildMicroOrder($data);
		if(is_error($result)) {
			message($result,  '', 'ajax');
		} else {
			$status = $pay->NoticeMicroSuccessOrder($result);
			if(is_error($status)) {
				message($status, '', 'ajax');
			}
			message(error(0, '支付成功'), '', 'ajax');
		}
		exit();
	}
	load()->model('card');
	$card_set = card_setting();
	$card_params = json_decode($card_set['params'], true);
	$grant_rate = $card_params[1]['params']['grant_rate'];
	unset($card_set['params'], $card_set['nums'], $card_set['times'], $card_set['business'], $card_set['html'], $card_set['description']);
	$card_set_str = json_encode($card_set);
}

if($do == 'query') {
	if($_W['isajax']) {
		$post = $_GPC['__input'];
		$uniontid = trim($post['uniontid']);
		load()->classs('pay');
		$pay = Pay::create();
		$result = $pay->queryOrder($uniontid, 2);
		if(is_error($result)) {
			message($result, '', 'ajax');
		}
		if($result['trade_state'] == 'SUCCESS') {
			$status = $pay->NoticeMicroSuccessOrder($result);
			if(is_error($status)) {
				message($status, '', 'ajax');
			}
			message(error(0, '支付成功'), '', 'ajax');
		}
		message(error(-1, '支付失败,当前订单状态:' . $result['trade_state']), '', 'ajax');
	}
}

template('paycenter/wxmicro');
