<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_group');
$dos = array('display', 'post','delete','set');
$do = in_array($do, $dos) ? $do : 'display';
cache_delete("uniaccount:{$_W['uniacid']}");

if($do == 'display') {
	$_W['page']['title'] = '会员组列表 - 会员组 - 会员中心';
	$setting = pdo_get('uni_settings', array('uniacid' => $_W['uniacid']), array('grouplevel'));
	if(checksubmit('submit')) {
		$grouplevel = intval($_GPC['grouplevel']);
		pdo_update('uni_settings', array('grouplevel' => $grouplevel), array('uniacid' => $_W['uniacid']));

		cache_delete("unisetting:{$_W['uniacid']}");
		foreach($_GPC['credit'] as $key => $value) {
			$key = intval($key);
			$data['title'] = trim($_GPC['title'][$key]);
			$data['credit'] = intval($_GPC['credit'][$key]);
			pdo_update('mc_groups', $data, array('groupid' => $key, 'uniacid' => $_W['uniacid']));
			unset($data);
		}
		message('用户组更新成功！', referer(), 'success');
	}

	$list = pdo_fetchall("SELECT * FROM ".tablename('mc_groups')." WHERE uniacid = :uniacid ORDER BY isdefault DESC,credit ASC", array(':uniacid' => $_W['uniacid']));
	$count = pdo_fetchall('SELECT groupid,COUNT(*) AS num FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid GROUP BY groupid', array(':uniacid' => $_W['uniacid']), 'groupid');
}

if($do == 'post') {
	$_W['page']['title'] = '添加会员组 - 会员组 - 会员中心';
	$groupid = intval($_GPC['id']);
	if(!empty($groupid)) {
		$_W['page']['title'] = '编辑会员组 - 会员组 - 会员中心';
		$item = pdo_fetch("SELECT * FROM ".tablename('mc_groups') . " WHERE groupid = :id", array(':id' => $groupid));
	}
	if(checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('请输入用户组名称！');
		}
		$data = array(
			'title' => $_GPC['title'],
			'credit' => intval($_GPC['credit']),
			'uniacid' => intval($_W['uniacid']),
		);
		if (empty($groupid)) {
			pdo_insert('mc_groups', $data);
		} else {
			pdo_update('mc_groups', $data, array('groupid' => $groupid));
		}
		message('用户组更新成功！', url('mc/group/display'), 'success');
	}
}

if($do == 'delete') {
	$_W['page']['title'] = '删除会员组 - 会员组 - 会员中心';
	$groupid = intval($_GPC['id']);
	pdo_query("DELETE FROM " . tablename('mc_groups') . " WHERE uniacid = :uniacid AND groupid = :groupid",array(':uniacid' => $_W['uniacid'],':groupid' => $groupid));
	message('用户组删除成功！', url('mc/group/display'), 'success');
}

if($do == 'set') {
	$groupid = intval($_GPC['id']);
	pdo_update('mc_groups', array('isdefault' => 0), array('uniacid' => $_W['uniacid']));
	pdo_update('mc_groups', array('isdefault' => 1), array('uniacid' => $_W['uniacid'], 'groupid' => $groupid));
	message('设置默认用户组成功！', url('mc/group/display'), 'success');
}
template('mc/group');
