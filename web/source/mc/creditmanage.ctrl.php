<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_creditmanage');
$_W['page']['title'] = '积分列表 - 会员积分管理 - 会员中心';

$dos = array('display', 'manage', 'modal', 'credit_record', 'stat');
$do = in_array($do, $dos) ? $do : 'display';

$creditnames = uni_setting($_W['uniacid'], array('creditnames'));
$creditnames = $creditnames['creditnames'];
if($creditnames) {
	foreach($creditnames as $index => $creditname) {
		if(empty($creditname['enabled'])) {
			unset($creditnames[$index]);
		}
	}
	$select_credit = implode(', ', array_keys($creditnames));
} else {
	$select_credit = '';
}

if($do == 'display') {
	$where = ' WHERE uniacid = :uniacid ';
	$params = array(':uniacid' => $_W['uniacid']);
	$type = intval($_GPC['type']);
	if(!$type) {
		$type = intval($_GPC['cookietype']);
	} else {
		isetcookie('cookietype', $type, 86400 * 7);
	}
	$keyword = trim($_GPC['keyword']);
	if($type == 1 || $type == '') {
		$keyword = intval($_GPC['keyword']);

		if ($keyword > 0) {
			$where .= ' AND uid = :uid';
			$params[':uid'] = $keyword;
		}
	} elseif($type == 2) {
		if ($keyword) {
			$where .= " AND mobile LIKE :mobile";
			$params[':mobile'] = "%{$keyword}%";
		}
	} elseif($type == 4) {
		if ($keyword) {
			$where .= " AND realname LIKE :realname";
			$params[':realname'] = "%{$keyword}%";
		}
	} elseif($type == 3) {
		if ($keyword) {
			$where .= " AND nickname LIKE :nickname";
			$params[':nickname'] = "%{$keyword}%";
		}
	}

		if (!empty($_GPC['minimal'])) {
		$where .= ' AND `credit1` > :minimal';
		$params[':minimal'] = sprintf('%.2f', $_GPC['minimal']);
	}
	if (!empty($_GPC['maximal'])) {
		$where .= ' AND `credit1` < :maximal';
		$params[':maximal'] = sprintf('%.2f', $_GPC['maximal']);
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('mc_members') . $where, $params);
	$list = pdo_fetchall("SELECT uid, uniacid, email, nickname, realname, mobile, {$select_credit} FROM " . tablename('mc_members') . $where . ' ORDER BY uid DESC LIMIT ' . ($pindex - 1) * $psize .',' . $psize, $params);
	$pager = pagination($total, $pindex, $psize);
	if(count($list) == 1 && $list[0]['uid'] && !empty($keyword)) {
		$status = 1;
		$uid = $list[0]['uid'];
	} else {
		foreach($list as &$li) {
			if(empty($li['email']) || (!empty($li['email']) && substr($li['email'], -6) == 'we7.cc' && strlen($li['email']) == 39)) {
				$li['email'] = '未完善';
			}
		}
		$status = 0;
 	}
}

if($do == 'manage') {
	load()->model('mc');
	$clerk = pdo_get('activity_clerks', array('uniacid' => $_W['uniacid'], 'password' => trim($_GPC['password'])));
	if(empty($clerk)) {
		message('店员密码错误');
	}
	$uid = intval($_GPC['uid']);
	if($uid) {
		foreach($creditnames as $index => $creditname) {
			if(($_GPC[$index . '_type'] == 1 || $_GPC[$index . '_type'] == 2) && $_GPC[$index . '_value']) {
				$value = $_GPC[$index . '_type'] == 1 ? $_GPC[$index . '_value'] : - $_GPC[$index . '_value'];
				$return = mc_credit_update($uid, $index, $value, array($_W['uid'], trim($_GPC['remark']), 'system', $clerk['id'], $clerk['store_id']));
				if(is_error($return)) {
					message($return['message']);
				}
				$openid = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $uid));
				if(!empty($openid)) {
					if($index == 'credit1') {
						mc_notice_credit1($openid, $uid, $value, '管理员后台操作积分');
					}
					if($index == 'credit2') {
						if($value > 0) {
							mc_notice_recharge($openid, $uid, $value, '', "管理员后台操作余额,增加{$value}余额");
						} else {
							mc_notice_credit2($openid, $uid, $value, 0, "管理员后台操作余额,减少{$value}余额");
						}
					}
				}
			} else {
				continue;
			}
		}
		message('会员积分操作成功', url('mc/creditmanage/display'));
	} else {
		message('未找到指定用户', url('mc/creditmanage/display'), 'error');
	}
}

if($do == 'modal') {
	if($_W['isajax']) {
		$uid = intval($_GPC['uid']);
		$data = pdo_fetch("SELECT uid, nickname, realname, email, mobile, uniacid, {$select_credit} FROM " . tablename('mc_members') . ' WHERE uid = :uid AND uniacid = :uniacid', array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
		if(empty($data['email']) || (!empty($data['email']) && substr($data['email'], -6) == 'we7.cc' && strlen($data['email']) == 39)) {
			$data['email'] = '未完善';
		}
		$data ? template('mc/modal') : exit('dataerr');
		exit();
	}
}

if($do == 'credit_record') {
	$uid = intval($_GPC['uid']);
	$credits = array_keys($creditnames);
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : $credits[0];
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 50;
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('mc_credits_record') . ' WHERE uid = :uid AND uniacid = :uniacid AND credittype = :credittype ', array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':credittype' => $type));
	$data = pdo_fetchall("SELECT r.*, u.username FROM " . tablename('mc_credits_record') . ' AS r LEFT JOIN ' .tablename('users') . ' AS u ON r.operator = u.uid ' . ' WHERE r.uid = :uid AND r.uniacid = :uniacid AND r.credittype = :credittype ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize .',' . $psize, array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':credittype' => $type));
	$pager = pagination($total, $pindex, $psize);
	$modules = pdo_getall('modules', array('issystem' => 0), array('title', 'name'), 'name');
	$modules['card'] = array('title' => '会员卡', 'name' => 'card');
	template('mc/credit_record');
	exit;
}

if($do == 'stat') {
	$uid = intval($_GPC['uid']);
	$credits = array_keys($creditnames);
	$count = 5 - count($creditnames);
	for($i = $count; $i > 0; $i--) {
		$creditnames[] = array('title' => '***');
	}
	$type = intval($_GPC['type']);
	$starttime = strtotime('-7 day');
	$endtime = strtotime('7 day');
	if($type == 1) {
		$starttime = strtotime(date('Y-m-d'));
		$endtime = TIMESTAMP;
	} elseif($type == -1) {
		$starttime = strtotime('-1 day');
		$endtime = strtotime(date('Y-m-d'));
	} else{
		$starttime = strtotime($_GPC['datelimit']['start']);
		$endtime = strtotime($_GPC['datelimit']['end']) + 86399;
	}
	if(!empty($credits)) {
		$data = array();
		foreach($credits as $li) {
			$data[$li]['add'] = round(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :id AND uid = :uid AND createtime > :start AND createtime < :end AND credittype = :type AND num > 0', array(':id' => $_W['uniacid'], ':uid' => $uid, ':start' => $starttime, ':end' => $endtime, ':type' => $li)),2);
			$data[$li]['del'] = abs(round(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :id AND uid = :uid AND createtime > :start AND createtime < :end AND credittype = :type AND num < 0', array(':id' => $_W['uniacid'], ':uid' => $uid, ':start' => $starttime, ':end' => $endtime, ':type' => $li)),2));
			$data[$li]['end'] = $data[$key]['add'] - $data[$key]['del'];
		}
	}
	template('mc/credit_record');
	exit();
}

template('mc/creditmanage');
