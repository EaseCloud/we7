<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
if($_W['role'] != 'clerk') {
	uni_user_permission_check('mc_member');
}
$_W['page']['title'] = '会员交易-会员管理';
$dos = array('consume', 'user', 'modal', 'credit', 'card', 'cardsn', 'tpl');
$do = in_array($do, $dos) ? $do : 'tpl';
load()->model('mc');

if($do == 'user') {
	$type = trim($_GPC['type']);
	if(!in_array($type, array('uid', 'mobile'))) {
		$type = 'mobile';
	}
	$username = trim($_GPC['username']);
	$data = pdo_getall('mc_members', array('uniacid' => $_W['uniacid'], $type => $username));
	if(empty($data)) {
		exit(json_encode(array('error' => 'empty', 'message' => '没有找到对应用户')));
	} elseif(count($data) > 1) {
		exit(json_encode(array('error' => 'not-unique', 'message' => '用户不唯一,请重新输入用户信息')));
	} else {
		load()->model('card');
		$user = $data[0];
		$user['groupname'] = $_W['account']['groups'][$user['groupid']]['title'];

		$card = card_setting();
		$member = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $user['uid']));
		if(!empty($card) && $card['status'] == 1) {
			if(!empty($member)) {
				$str = "会员卡号:{$member['cardsn']}.";
				$user['discount'] = $card['discount'][$user['groupid']];
				$user['cardsn'] = $member['cardsn'];
				if(!empty($user['discount']) && !empty($user['discount']['discount'])) {
					$str .= "折扣:满{$user['discount']['condition']}元";
					if($card['discount_type'] == 1) {
						$str .= "减{$user['discount']['discount']}元";
					} else {
						$discount = $user['discount']['discount'] * 10;
						$str .= "打{$discount}折";
					}
					$user['discount_cn'] = $str;
				}
			} else {
				$user['discount_cn'] = '会员未领取会员卡,不能享受优惠';
			}
		} else {
			$user['discount_cn'] = '商家未开启会员卡功能';
		}
		$html = "姓名:{$user['realname']},会员组:{$user['groupname']}<br>";
		$html .= "{$user['discount_cn']}<br>";
		$html .= "余额:{$user['credit2']}元,积分:{$user['credit1']},贡献:{$user['credit6']}<br>";

		if(!empty($card) && $card['offset_rate'] > 0 && $card['offset_max'] > 0) {
			$html .= "{$card['offset_rate']}积分可抵消1元。最多可抵消{$card['offset_max']}元";
		}
		exit(json_encode(array('error' => 'none', 'user' => $user, 'html' => $html, 'card' => $card, 'group' => $_W['account']['groups'], 'grouplevel' => $_W['account']['grouplevel'])));
	}
}

if($do == 'cardsn') {
	$uid = intval($_GPC['uid']);
	$cardsn = trim($_GPC['cardsn']);
	$type = trim($_GPC['type']);
	if($_W['isajax'] && $type == 'check') {
		$data = pdo_get('mc_card_members', array('cardsn' => $cardsn, 'uniacid' => $_W['uniacid']));
		if(!empty($data) ) {
			exit(json_encode(array('valid' => false)));
		} else {
			exit(json_encode(array('valid' => true)));
		}
	} else {
		pdo_update('mc_card_members', array('cardsn' => $cardsn), array('uid' => $uid, 'uniacid' => $_W['uniacid']));
		exit('success');
	}
}

if($_W['isajax'] && !in_array($do, array('user', 'clerk', 'cardsn'))) {
	$uid = intval($_GPC['uid']);
	$user = pdo_get('mc_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	if(empty($user)) {
		exit('会员不存在');
	}
}

if($do == 'consume') {
	$total = $money = floatval($_GPC['total']);
	if(!$total) {
		exit('消费金额不能为空');
	}
	$log = "系统日志:会员消费【{$total}】元";
	load()->model('card');
	$user['groupname'] = $_W['account']['groups'][$user['groupid']]['title'];

	$card = array();
	$card = card_setting();
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
	}
	$post_money = floatval($_GPC['money']);
	if($post_money != $money) {
		exit('实收金额错误');
	}

	$post_credit1 = intval($_GPC['credit1']);
	if($post_credit1 > 0) {
		if($post_credit1 > $user['credit1']) {
			exit('超过会员账户可用积分');
		}
	}
	$post_offset_money = intval($_GPC['offset_money']);
	$offset_money = 0;
	if($post_credit1 && $card['offset_rate'] > 0 && $card['offset_max'] > 0) {
		$offset_money = min($card['offset_max'], $post_credit1/$card['offset_rate']);
		if($offset_money != $post_offset_money) {
			exit('积分抵消金额错误');
		}
		$credit1 = $post_credit1;
		$log .= ",使用【{$post_credit1}】积分抵消【{$offset_money}】元";
	}

	$credit2 = floatval($_GPC['credit2']);
	if($credit2 > 0) {
		if($credit2 > $user['credit2']) {
			exit('超过会员账户可用余额');
		}
		$log .= ",使用余额支付【{$credit2}】元";
	}

	$cash = floatval($_GPC['cash']);
	$sum = $credit2 + $cash + $offset_money;
	$final_cash = $money - $credit2 - $offset_money;
	$return_cash = $sum - $money;
	if($sum < $money) {
		exit('支付金额小于实收金额');
	}
	if($cash > 0) {
		$log .= ",使用现金支付【{$cash}】元";
	}
	if($return_cash > 0) {
		$log .= ",找零【{$return_cash}】元";
	}
	if(!empty($_GPC['remark'])) {
		$note = "店员备注：{$_GPC['remark']}";
	}
	$log = $note.$log;
	if($credit2 > 0) {
		$status = mc_credit_update($uid, 'credit2', -$credit2, array(0, $log, 'system', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
		if(is_error($status)) {
			exit($status['message']);
		}
	}
	if($credit1 > 0) {
		$status = mc_credit_update($uid, 'credit1', -$credit1, array(0, $log, 'system', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
		if(is_error($status)) {
			exit($status['message']);
		}
	}

	$data = array(
		'uniacid' => $_W['uniacid'],
		'uid' => $uid,
		'fee' => $total,
		'final_fee' => $money,
		'credit1' => $post_credit1,
		'credit1_fee' => $offset_money,
		'credit2' => $credit2,
		'cash' => $cash,
		'final_cash' => $final_cash,
		'return_cash' => $return_cash,
		'remark' => $log,
		'clerk_id' => $_W['user']['clerk_id'],
		'store_id' => $_W['user']['store_id'],
		'clerk_type' => $_W['user']['clerk_type'],
		'createtime' => TIMESTAMP,
	);
	pdo_insert('mc_cash_record', $data);

	$tips = "用户消费{$money}元,使用{$data['credit1']}积分,抵现{$data['credit1_fee']}元,使用余额支付{$data['credit2']}元,现金支付{$data['final_cash']}元";
		if(!empty($card) && $card['grant_rate'] > 0 && !empty($member)) {
		$num = $money * $card['grant_rate'];
		$tips .= "，积分赠送比率为:【1：{$card['grant_rate']}】,共赠送【{$num}】积分";
		mc_credit_update($uid, 'credit1', $num, array(0, $tips, 'system', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
	}
		$openid = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $uid));
	$consume_tips = array(
		'uid' => $uid,
		'credit2_num' => $money,
		'credit1_num' => $num,
		'store' => '系统后台',
		'remark' => $tips,
	);
	if(!empty($openid)) {
		mc_notice_consume($openid, '会员消费通知', $consume_tips);
	}
	exit('success');
}

if($do == 'credit') {
	$type = trim($_GPC['type']);
	$num = floatval($_GPC['num']);
	$names = array('credit1' => '积分', 'credit2' => '余额');
	$credits = mc_credit_fetch($uid);
	if($num < 0 && abs($num) > $credits[$type]) {
		exit("会员账户{$names[$type]}不够");
	}
	$status = mc_credit_update($uid, $type, $num, array($_W['user']['uid'], trim($_GPC['remark']), 'system', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
	if(is_error($status)) {
		exit($status['message']);
	}
		if($type == 'credit1') {
		mc_group_update($uid);
	}
	$openid = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $uid));
	if(!empty($openid)) {
		if($type == 'credit1') {
			mc_notice_credit1($openid, $uid, $num, '管理员后台操作积分');
		}
		if($type == 'credit2') {
			if($num > 0) {
				mc_notice_recharge($openid, $uid, $num, '', "管理员后台操作余额,增加{$value}余额");
			} else {
				mc_notice_credit2($openid, $uid, $num, 0, '', '',  "管理员后台操作余额,减少{$value}余额");
			}
		}
	}
	exit('success');
}

if($do == 'card') {
	load()->model('card');
	$card = card_setting();
	if(empty($card)) {
		exit('公众号未设置会员卡');
	}
	$member = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $user['uid']));
	if(!empty($member)) {
		exit('该会员已领取会员卡');
	}
	$cardsn = $card['format'];
	preg_match_all('/(\*+)/', $card['format'], $matchs);
	if (!empty($matchs)) {
		foreach ($matchs[1] as $row) {
			$cardsn = str_replace($row, random(strlen($row), 1), $cardsn);
		}
	}
	preg_match('/(\#+)/', $card['format'], $matchs);
	$length = strlen($matchs[1]);
	$pos = strpos($card['format'], '#');
	$cardsn = str_replace($matchs[1], str_pad($card['snpos']++, $length - strlen($number), '0', STR_PAD_LEFT), $cardsn);

	$record = array(
		'uniacid' => $_W['uniacid'],
		'openid' => '',
		'uid' => $uid,
		'cid' => $card['id'],
		'cardsn' => $cardsn,
		'status' => '1',
		'createtime' => TIMESTAMP,
		'endtime' => TIMESTAMP
	);
	if(pdo_insert('mc_card_members', $record)) {
		pdo_update('mc_card', array('snpos' => $card['snpos']), array('uniacid' => $_W['uniacid'], 'id' => $card['id']));
				$notice = '';
		if($card['grant']['credit1'] > 0) {
			$log = array(
				$uid,
				"领取会员卡，赠送{$card['grant']['credit1']}积分",
				'system',
				$_W['user']['clerk_id'],
				$_W['user']['store_id'],
				$_W['user']['clerk_type']
			);
			mc_credit_update($uid, 'credit1', $card['grant']['credit1'], $log);
		}
		if($card['grant']['credit2'] > 0) {
			$log = array(
				$uid,
				"领取会员卡，赠送{$card['credit2']['credit1']}余额",
				'system',
				$_W['user']['clerk_id'],
				$_W['user']['store_id'],
				$_W['user']['clerk_type']
			);
			mc_credit_update($uid, 'credit2', $card['grant']['credit2'], $log);
		}
		if($card['grant']['coupon'] > 0) {
			if($card['grant']['coupon'] > 0) {
				$coupon = pdo_fetch('SELECT couponid,title,type FROM ' . tablename('activity_coupon') . ' WHERE uniacid = :uniacid AND couponid = :couponid', array(':uniacid' => $_W['uniacid'], ':couponid' => $card['grant']['coupon']));
			}
			load()->model('activity');
			if($coupon['type'] == 1) {
				$status = activity_coupon_grant($uid, $coupon['couponid'], 'card', '领取会员卡，赠送优惠券');
			} else {
				$status = activity_token_grant($uid, $coupon['couponid'], 'card', '领取会员卡，赠送优惠券');
			}
		}
		exit('success');
	}
}

if($do == 'group') {
	$credit6 = floatval($_GPC['credit6']);
	$credit = $credit1 + $credit6;
	if($credit < 0) {
		exit('积分和贡献相加不能小于0');
	}
	if($credit6 != $user['credit6']) {
		mc_credit_update($uid, 'credit6', (-$user['credit6'] + $credit6), array(0, "通过修改贡献值,来变更会员用户组", 'group', $_W['user']['clerk_id'], $_W['user']['store_id'], $_W['user']['clerk_type']));
	}
	$groupid = $user['groupid'];
	$_W['member'] = $user;
	$_W['openid'] = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $user['uid']));
	mc_group_update();
	exit('success');
}
template('mc/trade');