<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$_W['page']['title'] = '编辑用户 - 用户管理 - 用户管理';
load()->model('setting');

$do = $_GPC['do'];
$dos = array('delete', 'edit');
$do = in_array($do, $dos) ? $do: 'edit';

$uid = intval($_GPC['uid']);
$user = user_single($uid);
$founders = explode(',', $_W['config']['setting']['founder']);

if ($do == 'edit') {
	if (empty($user)) {
		message('访问错误, 未找到指定操作员.', url('user/display'), 'error');
	}
	$extendfields = pdo_fetchall("SELECT field, title, description, required FROM ".tablename('profile_fields')." WHERE available = '1' AND showinregister = '1'");
	if(checksubmit('profile_submit')) {
		$_GPC['password'] = trim($_GPC['password']);
		if(!empty($record['password']) && istrlen($record['password']) < 8) {
			message('必须输入密码，且密码长度不得低于8位。');
		}
		load()->model('user');
		$record = array();
				if(!empty($_GPC['endtime']) || $_GPC['endtime'] == 0) {
			$record['endtime'] = strtotime($_GPC['endtime']);
		}
		$_GPC['groupid'] = intval($_GPC['groupid']);
		if (empty($_GPC['groupid'])) {
			message('请选择所属用户组');
		}
				if($_GPC['groupid'] != $user['groupid']) {
			$group = pdo_fetch("SELECT id,timelimit FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $_GPC['groupid']));
			if(empty($group)) {
				message('会员组不存在');
			}
			$timelimit = intval($group['timelimit']);
			$timeadd = 0;
			if($timelimit > 0) {
				$timeadd = strtotime($timelimit . ' days');
			}
			$record['starttime'] = TIMESTAMP;
			$record['endtime'] = $timeadd;
		}
		$record['uid'] = $uid;
		$record['password'] = $_GPC['password'];
		$record['salt'] = $user['salt'];
		$record['groupid'] = intval($_GPC['groupid']);
		$record['remark'] = $_GPC['remark'];
		user_update($record);
		
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
		
		if (!empty($extendfields)) {
			foreach ($extendfields as $row) {
				if(!in_array($row['field'], array('profile','resideprovince','birthyear'))) {
					$profile[$row['field']] = $_GPC[$row['field']];
				}
			}
			if (!empty($profile)) {
				$exists = pdo_fetchcolumn("SELECT uid FROM ".tablename('users_profile')." WHERE uid = :uid", array(':uid' => $uid));
				if (!empty($exists)) {
					pdo_update('users_profile', $profile, array('uid' => $uid));
				} else {
					$profile['uid'] = $uid;
					pdo_insert('users_profile', $profile);
				}
			}
		}
		cache_build_account_modules();
		cache_build_account();
		message('保存用户资料成功！', 'refresh');
	}
	
	$user['profile'] = pdo_fetch("SELECT * FROM ".tablename('users_profile')." WHERE uid = :uid", array(':uid' => $uid));
	$user['profile']['reside'] = array(
		'province' => $user['profile']['resideprovince'],
		'city' => $user['profile']['residecity'],
		'district' => $user['profile']['residedist'],
	);
	unset($user['profile']['resideprovince']);
	unset($user['profile']['residecity']);
	unset($user['profile']['residedist']);
	$user['profile']['birth'] = array(
		'year' => $user['profile']['birthyear'],
		'month' => $user['profile']['birthmonth'],
		'day' => $user['profile']['birthday'],
	);
	unset($user['profile']['birthyear']);
	unset($user['profile']['birthmonth']);
	unset($user['profile']['birthday']);
	
	$groups = pdo_fetchall("SELECT id, name FROM ".tablename('users_group')." ORDER BY id ASC");
	
	template('user/edit');
	exit;
}

if($do == 'delete') {
	if($_W['ispost'] && $_W['isajax']) {
		if (in_array($uid, $founders)) {
			message('访问错误, 无法操作站长.', url('user/display'), 'error');
		}
		if (empty($user)) {
			exit('未指定用户,无法删除.');
		}
		$founders = explode(',', $_W['config']['setting']['founder']);
		if(in_array($uid, $founders)) {
			exit('站长不能删除.');
		}
		if(pdo_delete('users', array('uid' => $uid)) === 1) {
						cache_build_account_modules();
			pdo_delete('uni_account_users', array('uid' => $uid));
			pdo_delete('users_profile', array('uid' => $uid));
			exit('success');
		}
	}
}
