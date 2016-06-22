<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'display') {
	$_W['page']['title'] = '用户组列表 - 用户组 - 用户管理';
	if (checksubmit('submit')) {
		if (!empty($_GPC['delete'])) {
			pdo_query("DELETE FROM ".tablename('users_group')." WHERE id IN ('".implode("','", $_GPC['delete'])."')");
		}
		message('用户组更新成功！', referer(), 'success');
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('users_group'));
}

if ($do == 'post') {
	$id = intval($_GPC['id']);
	$_W['page']['title'] = $id ? '编辑用户组 - 用户组 - 用户管理' : '添加用户组 - 用户组 - 用户管理';
	if (!empty($id)) {
		$group = pdo_fetch("SELECT * FROM ".tablename('users_group') . " WHERE id = :id", array(':id' => $id));
		$group['package'] = iunserializer($group['package']);
	}
	$packages = uni_groups();
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入用户组名称！');
		}
		if (!empty($_GPC['package'])) {
			foreach ($_GPC['package'] as $value) {
				$package[] = intval($value);
			}
		}
		$data = array(
			'name' => $_GPC['name'],
			'package' => iserializer($package),
			'maxaccount' => intval($_GPC['maxaccount']),
			'timelimit' => intval($_GPC['timelimit'])
		);
		if (empty($id)) {
			pdo_insert('users_group', $data);
		} else {
			pdo_update('users_group', $data, array('id' => $id));
		}
		message('用户组更新成功！', url('user/group/display'), 'success');
	}
}

template('user/group');