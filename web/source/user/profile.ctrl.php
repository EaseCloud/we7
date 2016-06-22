<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$do = !empty($_GPC['do']) && in_array($_GPC['do'], array('profile','base')) ? $_GPC['do'] : 'profile';
if ($do == 'profile') {
	$_W['page']['title'] = '账号信息 - 我的账户 - 用户管理';
	$sql = "SELECT username, password, salt, groupid, starttime, endtime FROM " . tablename('users') . " WHERE `uid` = '{$_W['uid']}'";
	$user = pdo_fetch($sql);
	if (empty($user)) {
		message('抱歉，用户不存在或是已经被删除！', url('user/profile'), 'error');
	}
	$user['groupname'] = pdo_fetchcolumn('SELECT name FROM ' . tablename('users_group') . ' WHERE id = :id', array(':id' => $user['groupid']));

	if (checksubmit('submit')) {
		if (empty($_GPC['name']) || empty($_GPC['pw']) || empty($_GPC['pw2'])) {
			message('管理账号或者密码不能为空，请重新填写！', url('user/profile'), 'error');
		}
		if ($_GPC['pw'] == $_GPC['pw2']) {
			message('新密码与原密码一致，请检查！', url('user/profile'), 'error');
		}
		$password_old = user_hash($_GPC['pw'], $user['salt']);
		if ($user['password'] != $password_old) {
			message('原密码错误，请重新填写！', url('user/profile'), 'error');
		}
		$result = '';
		$members = array(
			'username' => $_GPC['name'],
			'password' => user_hash($_GPC['pw2'], $user['salt']),
		);
		$result = pdo_update('users', $members, array('uid' => $_W['uid']));
		message('修改成功！', url('index'), 'success');
	}
}

if($do == 'base') {
	$_W['page']['title'] = '基本信息 - 我的账户 - 用户管理';
	$extendfields = pdo_fetchall("SELECT field, title, description, required FROM ".tablename('profile_fields')." WHERE available = '1' AND showinregister = '1' ORDER BY displayorder DESC");
	
	if (checksubmit('submit')) {
		if (!empty($extendfields)) {
			if (!empty($_GPC['birth'])) {
				$profile['birthyear'] = $_GPC['birth']['year'];
				$profile['birthmonth'] = $_GPC['birth']['month'];
				$profile['birthday'] = $_GPC['birth']['day'];
			}
			if (!empty($_GPC['reside'])) {
				$profile['resideprovince'] = $_GPC['reside']['province'];
				$profile['residecity'] = $_GPC['reside']['city'];
				$profile['residedist'] = $_GPC['reside']['district'];
			}
			foreach ($extendfields as $row) {
				$_GPC[$row['field']] = trim($_GPC[$row['field']]);
				if (!empty($row['required']) && empty($_GPC[$row['field']])) {
					message('“'.$row['title'].'”此项为必填项，请返回填写完整！');
				}
				if (in_array($row['field'], array('resideprovince','birthyear'))) {
					continue;
				}
				$profile[$row['field']] = $_GPC[$row['field']];
			}
			if($_W['uid'] > 0) {
								if (!empty($profile)) {
					$exist = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('users_profile').' WHERE `uid` = :uid',array(':uid' => $_W['uid']));
					if($exist == '0') {
						$profile['uid'] = $_W['uid'];
						pdo_insert('users_profile', $profile);
					} else {
						pdo_update('users_profile', $profile, array('uid' => $_W['uid']));
					}
				}
				message('保存成功',url('user/profile/base'),success);
			} else {
				message('用户不存在',url('user/profile/base'),error);
			}
		}
	}
	
	$profile = pdo_fetch('SELECT * FROM '.tablename('users_profile').' WHERE `uid` = :uid LIMIT 1',array(':uid' => $_W['uid']));
	$profile['reside'] = array(
		'province' => $profile['resideprovince'],
		'city' => $profile['residecity'],
		'district' => $profile['residedist']);
	$profile['birth'] = array(
		'year' => $profile['birthyear'],
		'month' => $profile['birthmonth'],
		'day' => $profile['birthday'],
	);
}

template('user/profile');
