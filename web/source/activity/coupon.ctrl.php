<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('activity_coupon_display');
$_W['page']['title'] = '折扣券-积分兑换';
$dos = array('display', 'post', 'del');
$do = in_array($do, $dos) ? $do : 'display';

$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames'));
foreach ($unisettings['creditnames'] as $key=>$credit) {
	if (!empty($credit['enabled'])) {
		$creditnames[$key] = $credit['title'];
	}
}

if($do == 'post') {
	global $_W, $_GPC;
	$couponid = intval($_GPC['id']);
	$_W['page']['title'] = !empty($couponid) ? '折扣券编辑 - 折扣券 - 会员营销' : '折扣券添加 - 折扣券 - 会员营销';
	$item = pdo_fetch('SELECT * FROM ' . tablename('activity_coupon') . " WHERE uniacid = '{$_W['uniacid']}' AND couponid = '{$couponid}'");
		if(empty($item) || $couponid == 0) {
		$item['starttime'] = time();
		$item['endtime'] = time() + 6 * 86400;
	}
		$coupongroup = pdo_fetchall('SELECT groupid FROM ' . tablename('activity_coupon_allocation') . " WHERE uniacid = '{$_W['uniacid']}' AND couponid = '{$couponid}'");
	if(!empty($coupongroup)) {
		foreach($coupongroup as $cgroup) {
			$grouparr[] = $cgroup['groupid'];
		}
	}
		$group = pdo_fetchall('SELECT groupid,title FROM ' . tablename('mc_groups') . " WHERE uniacid = '{$_W['uniacid']}' ");
	if(!empty($grouparr)) {
		foreach($group as &$g){
			if(in_array($g['groupid'], $grouparr)) {
				$g['groupid_select'] = 1;
			}
		}
	}

		$coupon_modules =  pdo_fetchall('SELECT module FROM ' . tablename('activity_coupon_modules') . " WHERE uniacid = '{$_W['uniacid']}' AND couponid = '{$couponid}'", array(), 'module');
	if(!empty($coupon_modules)) {
		$module = uni_modules();
		$keys = array_keys($coupon_modules);
		$item['module'] = implode('@', $keys);
	}

	if(checksubmit('submit')) {
		$title = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请输入折扣券名称！');
		$condition = floatval($_GPC['condition']);
		$discount = !empty($_GPC['discount']) ? trim($_GPC['discount']) : message('请输入折扣！');
		$groups = !empty($_GPC['group']) ? $_GPC['group'] : message('请选择可使用的会员组！');
		$thumb = !empty($_GPC['thumb']) ? $_GPC['thumb'] : message('请上传缩略图！');
		$description = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请填写折扣券说明！');
		$credittype = !empty($_GPC['credittype']) ? trim($_GPC['credittype']) : message('请选择积分类型！');
		$credit =  intval($_GPC['credit']);	
		$starttime = strtotime($_GPC['datelimit']['start']);
		$endtime = strtotime($_GPC['datelimit']['end']);
		if($endtime == $starttime) {
			$endtime = $endtime + 86399;
		}
		$limit = intval($_GPC['limit']) ? intval($_GPC['limit']) : message('每人限领数目必须为数字！');
		$amount = intval($_GPC['amount']) ? intval($_GPC['amount']) : message('折扣券总数必须为数字！');

		$data = array(
			'uniacid' => $_W['uniacid'],
			'title' => $title,
			'type' => '1', 			'condition' => $condition,
			'discount' => $discount,
			'thumb' => $thumb,
			'description' => $description,
			'credittype' => $credittype,
			'credit' => $credit,
			'limit' => $limit,
			'amount' => $amount,
			'starttime' => $starttime,
			'endtime' => $endtime,
		);
		if ($couponid) {
			if(empty($item['couponsn'])) {
				$data['couponsn'] = 'AB' . $_W['uniacid'] . date('YmdHis');
			}
			pdo_update('activity_coupon', $data, array('uniacid' => $_W['uniacid'], 'couponid' => $couponid));
		} else {
			$data['couponsn'] = 'AB' . $_W['uniacid'] . date('YmdHis');
			pdo_insert('activity_coupon', $data);
			$couponid = pdo_insertid();
		}
		pdo_delete('activity_coupon_allocation', array('uniacid' => $_W['uniacid'], 'couponid' => $couponid));
		if(!empty($groups) && $couponid) {
			foreach($groups as $gid) {
				$gid = intval($gid);
				$insert = array(
					'uniacid' => $_W['uniacid'],
					'couponid' => $couponid,
					'groupid' => $gid
				);
				pdo_insert('activity_coupon_allocation', $insert) ? '' : message('抱歉，折扣券更新失败！', referer(), 'error');
				unset($insert);
			}
		}
				pdo_delete('activity_coupon_modules', array('uniacid' => $_W['uniacid'], 'couponid' => $couponid));
		$module = trim($_GPC['module-select']);
		if(!empty($module) && $couponid) {
			$arr = explode('@', $module);
			foreach($arr as $li) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'couponid' => $couponid,
					'module' => $li
				);
				$i++;
				pdo_insert('activity_coupon_modules', $data);
			}
		}
		message('折扣券更新成功！', url('activity/coupon/display'), 'success');
	}
}

if($do == 'display') {
	$_W['page']['title'] = '折扣券管理 - 折扣券 - 会员营销';
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$condition = '';
	if(!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
	}
	if(!empty($_GPC['couponsn'])) {
		$condition .= " AND couponsn LIKE '%{$_GPC['couponsn']}%'";
	}
	if(intval($_GPC['groupid'])) {
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon') . " WHERE uniacid = '{$_W['uniacid']}' AND type = 1 " . $condition . "  AND couponid IN (SELECT couponid FROM ".tablename('activity_coupon_allocation')." WHERE groupid = '{$_GPC['groupid']}')");
		$list = pdo_fetchall('SELECT * FROM ' . tablename('activity_coupon') . " WHERE uniacid = '{$_W['uniacid']}' AND type = 1 " . $condition . " AND couponid IN (SELECT couponid FROM ".tablename('activity_coupon_allocation')." WHERE groupid = '{$_GPC['groupid']}') ORDER BY couponid DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	} else {
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon') . " WHERE uniacid = '{$_W['uniacid']}' AND type = 1" . $condition);
		$list = pdo_fetchall('SELECT * FROM ' . tablename('activity_coupon') . " WHERE uniacid = '{$_W['uniacid']}' AND type = 1 " . $condition . " ORDER BY couponid DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	}
		$groupall = pdo_fetchall('SELECT groupid,title FROM ' . tablename('mc_groups') . " WHERE uniacid = '{$_W['uniacid']}' ");
	foreach($list as &$li) {
		$group = pdo_fetchall('SELECT m.* FROM ' . tablename('activity_coupon_allocation') . " AS a LEFT JOIN ".tablename('mc_groups')." AS m ON a.groupid = m.groupid WHERE a.uniacid = '{$_W['uniacid']}' AND a.couponid = '{$li['couponid']}'");
		$li['group'] = $group;
	}
	foreach($list as &$li) {
		$li['thumb'] = tomedia($li['thumb']);
	}
	$pager = pagination($total, $pindex, $psize);
}

if($do == 'del') {
	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT couponid FROM ".tablename('activity_coupon')." WHERE uniacid = '{$_W['uniacid']}' AND couponid = :couponid", array(':couponid' => $id));
	if (empty($row)) {
		message('抱歉，折扣券不存在或是已经被删除！');
	}
	pdo_delete('activity_coupon_allocation', array('uniacid' => $_W['uniacid'],'couponid' => $id));
	pdo_delete('activity_coupon', array('couponid' => $id, 'uniacid' => $_W['uniacid']));
	pdo_delete('activity_coupon_record', array('uniacid' => $_W['uniacid'], 'couponid' => $id));
	message('折扣券删除成功！',url('activity/coupon/display'), 'success');
}
template('activity/coupon');