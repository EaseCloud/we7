<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('sign_display', 'sign', 'sign_record', 'recommend', 'notice', 'sign_strategy', 'share');
$do = in_array($do, $dos) ? $do : 'sign_display';
load()->model('user');
load()->model('card');
$notice_count = card_notice_stat();
$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
if($do == 'sign_display') {
	$title = '签到-会员卡';
	$credits = mc_credit_fetch($_W['member']['uid']);
	$time = intval($_GPC['e']) ? intval($_GPC['e']) : TIMESTAMP;
	$pretime = strtotime('-1 month', $time);
	$nexttime = strtotime('+1 month', $time);
	$year = date('Y', $time);
	$month = date('m', $time);
	$day = date('d', $time);
	$record = pdo_fetch('SELECT id FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid AND addtime >= :addtime', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':addtime' => strtotime(date('Y-m-d'))));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid']));
	$month_record = pdo_fetchall('SELECT id,addtime FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid AND addtime >= :starttime AND addtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':starttime' => strtotime(date('Y-m', $time)), ':endtime' => strtotime('+1 month', strtotime(date('Y-m', $time)))));
	$flags = array();
	if(!empty($month_record)) {
		foreach($month_record as $li) {
			$flags[] = date('j', $li['addtime']);
		}
	}
	$flags = json_encode($flags);
}

if($do == 'sign') {
	if($_W['isajax']) {
		$record = pdo_fetch('SELECT id FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid AND addtime >= :addtime', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':addtime' => strtotime(date('Y-m-d'))));
		if(!empty($record)) {
			exit(json_encode(array('error' => 1, 'message' => '今天已签到')));
		}
		$set = card_credit_set();
		if(empty($set)) {
			exit(json_encode(array('error' => 1, 'message' => '商家未开启签到功能')));
		}
		$everydaynum = intval($set['sign']['everydaynum']);
		$lastday = intval($set['sign']['lastday']);
		$lastnum = intval($set['sign']['lastnum']);

		$data = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $_W['member']['uid'],
			'credit' => $everydaynum,
			'is_grant' => 0,
			'addtime' => TIMESTAMP,
		);
		pdo_insert('mc_card_sign_record', $data);
		$credit = $everydaynum;
		if($credit > 0) {
			$log = "用户签到赠送【{$credit}】积分";
		}
				if($lastday > 1 && $lastnum > 0) {
			$count = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid AND is_grant = 0 AND addtime >= :addtime', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':addtime' => strtotime("-{$lastday} days", date('Y-m-d'))));
			if($count >= $lastday) {
				pdo_update('mc_card_sign_record', array('is_grant' => 1), array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'is_grant' => 0));
				$credit += $lastnum;
				$log .= "，连续签到{$lastday}天，赠送【{$lastnum}】积分，本次总共赠送【{$credit}】积分";
			}
		}
		mc_credit_update($_W['member']['uid'], 'credit1', $credit, array(0, $log, 'sign'));
		$status = mc_notice_credit1($_W['openid'], $_W['member']['uid'], $credit, $log);
		exit(json_encode(array('error' => 0, 'message' => "签到成功，赠送{$credit}积分")));
	}
}

if($do == 'sign_record') {
	$title = '签到记录-会员卡';
	$psize = 30;
	$pindex = max(1, intval($_GPC['page']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid']));
	$data = pdo_fetchall('SELECT * FROM ' . tablename('mc_card_sign_record') . ' WHERE uniacid = :uniacid AND uid = :uid ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ", {$psize}", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid']));
	$pager = pagination($total, $pindex, $psize);
}

if($do == 'sign_strategy') {
	$set = card_credit_set();
	$content = $set['content'];
}

if($do == 'recommend') {
	$title = '签到记录-会员卡';
	$psize = 30;
	$pindex = max(1, intval($_GPC['page']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_recommend') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
	$data = pdo_fetchall('SELECT * FROM ' . tablename('mc_card_recommend') . ' WHERE uniacid = :uniacid ORDER BY displayorder DESC, id DESC LIMIT ' . ($pindex - 1) * $psize . ", {$psize}", array(':uniacid' => $_W['uniacid']));
	$pager = pagination($total, $pindex, $psize);
}

if($do == 'share') {
		if(!$_W['isajax']) {
		exit();
	}
	$set = card_credit_set();
	if($set['share']['times'] > 0 && $set['share']['num'] > 0 ) {
		$total = intval(pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND touid = :touid AND module = :module  AND action = :action AND createtime >= :createtime', array(':uniacid' => $_W['uniacid'], ':touid' => $_W['member']['uid'], ':module' => 'card', ':action' => 'share', ':createtime' => strtotime(date('Y-m-d')))));
		if($total < $set['share']['times']) {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'touid' => $_W['member']['uid'],
				'module' => 'card',
				'action' => 'share',
				'createtime' => TIMESTAMP,
				'credit_value' => intval($set['share']['num']),
			);
			pdo_insert('mc_handsel', $data);
			if($set['share']['num'] > 0) {
				$log = "用户分享会员卡的每日推荐页面，赠送【{$set['share']['num']}】积分";
				mc_credit_update($_W['member']['uid'], 'credit1', $set['share']['num'], array(0, $log, 'card'));
				$status = mc_notice_credit1($_W['openid'], $_W['member']['uid'], $set['share']['num'], $log);
				if(is_error($status)) {
					exit($log);
				}
			}
		} else {
			exit("每天只会赠送{$set['share']['times']}次积分，明天再来吧");
		}
	} else {
		exit('商家没有开始积分赠送');
	}
	exit();
}

if($do == 'notice') {
	$title = '系统消息-会员卡';
	if($_W['isajax']) {
		$id = intval($_GPC['id']);
		if($id > 0) {
			pdo_update('mc_card_notices_unread', array('is_new' => 0), array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'notice_id' => $id));
			$total = card_notice_stat();
			exit($total);
		}
	}
	$psize = 20;
	$pindex = max(1, intval($_GPC['page']));
	$type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_notices_unread') . ' WHERE uniacid = :uniacid AND uid = :uid AND type = :type', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':type' => $type));
	$data = pdo_fetchall('SELECT a.*,b.* FROM ' . tablename('mc_card_notices_unread') . ' AS a LEFT JOIN ' . tablename('mc_card_notices') . ' AS b ON a.notice_id = b.id WHERE a.uniacid = :uniacid AND a.uid = :uid AND a.type = :type ORDER BY a.notice_id DESC LIMIT ' . ($pindex - 1) * $psize . ", {$psize}", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':type' => $type));
	$pager = pagination($total, $pindex, $psize);
}

template('mc/card');