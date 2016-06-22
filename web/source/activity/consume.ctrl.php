<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('consume', 'display', 'del');
$do = in_array($do, $dos) ? $do : 'token';
load()->model('activity');
$_W['page']['title'] = '优惠券核销-积分兑换';
$type = intval($_GPC['type']) ? intval($_GPC['type']) : 1;
$types = array(
	'1' => array(
		'title' => '折扣券',
		'name' => 'coupon'
	),
	'2' => array(
		'title' => '代金券',
		'name' => 'token'
	),
);

if($do == 'consume') {
	uni_user_permission_check("activity_consume_{$types[$type]['name']}");
	$couponid = intval($_GPC['couponid']);
	$recid = intval($_GPC['id']);
	$record = pdo_get('activity_coupon_record', array('uniacid' => $_W['uniacid'], 'recid' => $recid));
	if(empty($record)) {
		message('兑换记录不存在', referer(), 'error');
	}
	$update = array(
		'status' => 2,
		'usemodule' => 'system',
		'usetime' => TIMESTAMP,
		'clerk_id' => $_W['user']['clerk_id'],
		'clerk_type' => $_W['user']['clerk_type'],
		'store_id' =>  $_W['user']['store_id']
	);
	pdo_update('activity_coupon_record', $update, array('uniacid' => $_W['uniacid'], 'recid' => $recid));
	message('核销成功！', referer(), 'success');
}

if($do == 'display') {
	uni_user_permission_check("activity_consume_{$types[$type]['name']}");
	$clerks = pdo_getall('activity_clerks', array('uniacid' => $_W['uniacid']), array('id', 'name'), 'id');
	$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'), 'id');

	$coupons = pdo_fetchall('SELECT couponid, title FROM ' . tablename('activity_coupon') . ' WHERE uniacid = :uniacid AND type = :type ORDER BY couponid DESC', array(':uniacid' => $_W['uniacid'], ':type' => $type), 'couponid');
	$starttime = empty($_GPC['time']['start']) ? strtotime('-1 month') : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;

	$where = " WHERE a.uniacid = {$_W['uniacid']} AND b.type = :type AND a.granttime>=:starttime AND a.granttime<:endtime";
	$params = array(
		':starttime' => $starttime,
		':endtime' => $endtime,
		':type' => $type
	);
	$code = trim($_GPC['code']);
	if (!empty($code)) {
		$where .=' AND a.code=:code';
		$params[':code'] = $code;
	}
	$uid = intval($_GPC['uid']);
	if (!empty($uid)) {
		$where .= ' AND a.uid=:uid';
		$params[':uid'] = $uid;
	}
	$couponid = intval($_GPC['couponid']);
	if (!empty($couponid)) {
		$where .= " AND a.couponid = {$couponid}";
	}
	$clerk_id = intval($_GPC['clerk_id']);
	if (!empty($clerk_id)) {
		$where .= ' AND a.clerk_id = :clerk_id';
		$params[':clerk_id'] = $clerk_id;
	}
	$status = intval($_GPC['status']);
	if (!empty($status)) {
		$where .= " AND a.status = {$status}";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = pdo_fetchall("SELECT a.*, b.title,b.thumb,b.discount,b.type FROM ".tablename('activity_coupon_record'). ' AS a LEFT JOIN ' . tablename('activity_coupon') . ' AS b ON a.couponid = b.couponid ' . " $where ORDER BY a.usetime DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_coupon_record') . ' AS a LEFT JOIN ' . tablename('activity_coupon') . ' AS b ON a.couponid = b.couponid '. $where , $params);
	if(!empty($list)) {
		load()->model('mc');
		$uids = array();
		foreach ($list as &$row) {
			$uids[] = $row['uid'];
			if($row['status'] == 2) {
				$operator = mc_account_change_operator($row['clerk_type'], $row['store_id'], $row['clerk_id']);
				$row['clerk_cn'] = $operator['clerk_cn'];
				$row['store_cn'] = $operator['store_cn'];
			}
		}
		load()->model('mc');
		$members = mc_fetch($uids, array('uid', 'nickname'));
		foreach ($list as &$row) {
			$row['nickname'] = $members[$row['uid']]['nickname'];
			$row['thumb'] = tomedia($row['thumb']);
		}
	}
	$pager = pagination($total, $pindex, $psize);
	$status = array('1' => '未使用', '2' => '已使用');
	$clerks = pdo_getall('activity_clerks', array('uniacid' => $_W['uniacid']), array('id', 'name'), 'id');
}

if($do == 'del') {
	uni_user_permission_check("activity_consume_{$types[$type]['name']}");
	$id = intval($_GPC['id']);
	if(empty($id)) {
		message('没有要删除的记录', '', 'error');
	}
	pdo_delete('activity_coupon_record', array('uniacid' => $_W['uniacid'], 'recid' => $id));
	message('删除兑换记录成功', referer(), 'success');
}

template('activity/consume');