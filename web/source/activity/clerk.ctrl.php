<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('activity_clerk_list');
$dos = array('switch', 'list', 'post', 'del', 'post', 'verify', 'checkname');
$do = in_array($do, $dos) ? $do : 'list';
$_W['page']['title'] = '店员列表 - 门店营销参数 - 会员营销';
if ($do == 'list') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$limit = 'ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_clerks')." WHERE uniacid = :uniacid ", array(':uniacid' => $_W['uniacid']));
	$list = pdo_fetchall("SELECT * FROM ".tablename('activity_clerks')." WHERE uniacid = :uniacid {$limit}", array(':uniacid' => $_W['uniacid']));
	$uids = array(0);
	foreach($list as $row) {
		if ($row['uid'] > 0) {
			$uids[] = $row['uid'];
		}
	}
	$uids = implode(',', $uids);
	$users = pdo_fetchall('SELECT username,uid FROM ' . tablename('users') . " WHERE uid IN ({$uids})", array(), 'uid');
	$pager = pagination($total, $pindex, $psize);
	$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'), 'id');
}
if ($do == 'checkname' && $_W['isajax']) {
	$username = trim($_GPC['username']);
	$uid = intval($_GPC['uid']);
	if (!empty($uid)) {
		$exist = pdo_fetch("SELECT * FROM ". tablename('users'). " WHERE uid <> :uid AND username = :username", array(':uid' => $uid, ':username' => trim($_GPC['username'])));
	} else {
		$exist = pdo_get('users', array('username' => $username));
	}
	if (empty($exist)) {
		message(error(1), '', 'ajax');
	}else {
		message(error(0), '', 'ajax');
	}
}
if ($do == 'post') {
	$id = intval($_GPC['id']);
	if (!empty($id)){
		$sql = 'SELECT * FROM ' . tablename('activity_clerks') . " WHERE id = :id AND uniacid = :uniacid";
		$clerk = pdo_fetch($sql, array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if (empty($clerk)) {
			message('店员不存在', referer(), 'error');
		}
		if (!empty($clerk['uid'])) {
			$user = pdo_get('users', array('uid' => $clerk['uid']));
			$clerk['username'] = $user['username'];
			$clerk['uid'] = $user['uid'];
			if (!$clerk['uid']) {
				$_W['uid'] = 0;
			}
			$clerk['permission'] = uni_user_permission('system', $clerk['uid']);
		}
	} else {
		$clerk = array(
			'permission' => array()
		);
	}
	if (checksubmit()) {
		load()->model('user');
		$name = trim($_GPC['name']) ? trim($_GPC['name']) : message('店员名称不能为空');
		$mobile =  trim($_GPC['mobile']) ? trim($_GPC['mobile']) : message('手机号不能为空');
		$storeid =  intval($_GPC['storeid']) ? intval($_GPC['storeid']) : message('请选择所在门店');
		if (!$clerk['uid']) {
			$user = array();
			$user['username'] = trim($_GPC['username']);
			if (empty($user['username'])) {
				message('必须输入用户名，格式为 1-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。');
			}
			if (user_check(array('username' => $user['username']))) {
				message('非常抱歉，此用户名已经被注册，你需要更换注册名称！');
			}
			$user['password'] = trim($_GPC['password']);
			if (istrlen($user['password']) < 8) {
				message('必须输入密码，且密码长度不得低于8位。');
			}
			$user['type'] = 3;
			$clerk['uid'] = user_register($user);
			if (!$clerk['uid']) {
				message('注册账号失败');
			}
		} else {
			$_GPC['username'] = trim($_GPC['username']);
			if (!preg_match(REGULAR_USERNAME, $_GPC['username'])) {
				message('必须输入用户名，格式为 3-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。');
			}
			$is_exist = pdo_fetchcolumn('SELECT uid FROM ' . tablename('users') . ' WHERE username = :username AND uid != :uid', array(':username' => $_GPC['username'], ':uid' => $clerk['uid']));
			if (!empty($is_exist)) {
				message('非常抱歉，此用户名已经被注册，你需要更换注册名称！');
			}
			$_GPC['password'] = trim($_GPC['password']);
			if (!empty($_GPC['password']) && istrlen($_GPC['password']) < 8) {
				message('必须输入密码，且密码长度不得低于8位。');
			}
			$record = array();
			$record['uid'] = $clerk['uid'];
			$record['password'] = $_GPC['password'];
			$record['salt'] = $user['salt'];
			$record['username'] = $_GPC['username'];
			$record['type'] = 3;
			user_update($record);
		}
		$permission = $_GPC['permission'];
		if (!empty($permission)) {
			$permission = implode('|', array_unique($permission));
		} else {
			$permission = '';
		}
		$permission_exist = pdo_get('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'type' => 'system'));
		if (empty($permission_exist)) {
			pdo_insert('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'type' => 'system', 'permission' => $permission));
		} else {
			pdo_update('users_permission', array('permission' => $permission), array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'type' => 'system'));
		}
		$permission = $_GPC['permission'];
		$modules_permission = array();
		foreach ($permission as $permi) {
			if (strexists($permi, 'menu')) {
				$permis = $permi;
				$permi = explode('_', $permi);
				$num = count($permi);
				unset($permi[$num-1]);
				unset($permi[$num-2]);
				$module_name = implode('_', $permi);
				$modules = uni_modules_app_binding();
				if (in_array($module_name, array_keys($modules))) {
					$modules_permission[$module_name] = $permis.'|'.$modules_permission[$module_name];
				}
			}
		}
		foreach ($modules_permission as $module_name => $module_p) {
			$module_p = trim($module_p, '|');
			$module_permission = pdo_get('users_permission', array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'type' => $module_name));
			if (!empty($module_permission)) {
				pdo_update('users_permission', array('permission' => $module_p), array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'type' => $module_name));
			} else {
				pdo_insert('users_permission', array('permission' => $module_p.'|'.$permis, 'uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'type' => $module_name));
			}
		}
		$account_user = pdo_get('uni_account_users', array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid']));
		if (empty($account_user)) {
			pdo_insert('uni_account_users', array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid'], 'role' => 'clerk'));
		} else {
			pdo_update('uni_account_users', array('role' => 'clerk'), array('uniacid' => $_W['uniacid'], 'uid' => $clerk['uid']));
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'storeid' => $storeid,
			'name' => $name,
			'mobile' => $mobile,
			'openid' => trim($_GPC['openid']),
			'nickname' => trim($_GPC['nickname']),
			'uid' => $clerk['uid'],
			'password' => $_GPC['password']
		);
		if (empty($_GPC['password'])) {
			unset($data['password']);
		}
		if (empty($clerk['id'])) {
			pdo_insert('activity_clerks', $data);
		} else {
			pdo_update('activity_clerks', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
		}
		message('编辑店员资料成功', url('activity/clerk/list'), 'success');
	}
	$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'));
	load()->model('clerk');
	$permission = clerk_permission_list();
	$clerk_p = pdo_fetchall("SELECT * FROM ". tablename('activity_clerk_menu'). " WHERE (uniacid = :uniacid OR system = '1') AND pid = 0 ORDER BY system DESC", array(':uniacid' =>  $_W['uniacid']), 'group_name');
	$clerk_c = pdo_fetchall("SELECT * FROM ". tablename('activity_clerk_menu'). " WHERE (uniacid = :uniacid OR system = '1') AND pid <> 0 ORDER BY displayorder ASC,system DESC", array(':uniacid' =>  $_W['uniacid']));
	$permission = array();
	foreach ($clerk_p as $p) {
		$permission[$p['id']]['title'] = $p['title'];
		$permission[$p['id']]['group_name'] = $p['group_name'];
	}
	foreach ($clerk_c as $c) {
		$permission[$c['pid']]['items'][] = $c;
	}
}

if ($do == 'verify') {
	if ($_W['isajax']) {
		$openid = trim($_GPC['openid']);
		$nickname = trim($_GPC['nickname']);
		if (!empty($openid)) {
			$sql = 'SELECT openid,nickname FROM ' . tablename('mc_mapping_fans') . " WHERE acid =:acid AND openid = :openid";
			$exist = pdo_fetch($sql, array(':openid' => $openid, ':acid' => $_W['acid']));
		} else {
			$sql = 'SELECT openid,nickname FROM ' . tablename('mc_mapping_fans') . " WHERE acid =:acid AND nickname = :nickname";
			$exist = pdo_fetch($sql, array(':nickname' => $nickname, ':acid' => $_W['acid']));
		}
		if (empty($exist)) {
			message(error(-1, '未找到对应的粉丝编号，请检查昵称或openid是否有效'), '', 'ajax');
		}
		message(error(0, $exist), '', 'ajax');
	}
}
if ($do == 'del') {
	$id = intval($_GPC['id']);
	$clerk = pdo_get('activity_clerks', array('id' => $id, 'uniacid' => $_W['uniacid']));
	if ($clerk['uid'] > 0) {
		pdo_delete('users',array('uid' => $clerk['uid']));
		pdo_delete('uni_account_users',array('uid' => $clerk['uid'], 'uniacid' => $_W['uniacid']));
	}
	pdo_delete('activity_clerks',array('id' => intval($_GPC['id']), 'uniacid' => $_W['uniacid']));
	message("删除成功",referer(),'success');
}
if ($do == 'switch') {
	$clerkid = intval($_GPC['id']);
	$clerk = pdo_get('activity_clerks', array('id' => $clerkid, 'uniacid' => $_W['uniacid']));
	load()->model('user');
	$user = user_single(array('uid' => $clerk['uid']));
	$cookie = array();
	$cookie['uid'] = $user['uid'];
	$cookie['lastvisit'] = $user['lastvisit'];
	$cookie['lastip'] = $user['lastip'];
	$cookie['hash'] = md5($user['password'] . $user['salt']);
	$session = base64_encode(json_encode($cookie));
	isetcookie('__session', $session, 7 * 86400);
	header('Location:' . url('account/switch', array('uniacid' => $clerk['uniacid'])));
	exit;
}
template('activity/clerk');
