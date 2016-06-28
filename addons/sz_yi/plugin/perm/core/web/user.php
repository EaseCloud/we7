<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->model('user');
if ($operation == 'display') {
	ca('perm.user.view');
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$status = $_GPC['status'];
	$condition = " and u.uniacid = :uniacid and u.deleted=0 and u.uid<>{$_W['uid']}";
	$params = array(':uniacid' => $_W['uniacid']);
	if (!empty($_GPC['keyword'])) {
		$_GPC['keyword'] = trim($_GPC['keyword']);
		$condition .= ' and ( u.realname like :keyword or u.username like :keyword or u.mobile like :keyword)';
		$params[':keyword'] = "%{$_GPC['keyword']}%";
	}
	if ($_GPC['roleid'] != '') {
		$condition .= ' and u.roleid=' . intval($_GPC['roleid']);
	}
	if ($_GPC['status'] != '') {
		$condition .= ' and u.status=' . intval($_GPC['status']);
	}
	$list = pdo_fetchall('SELECT u.*,r.rolename FROM ' . tablename('sz_yi_perm_user') . ' u  ' . ' left join ' . tablename('sz_yi_perm_role') . ' r on u.roleid =r.id  ' . " WHERE 1 {$condition} ORDER BY id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('sz_yi_perm_user') . ' u  ' . ' left join ' . tablename('sz_yi_perm_role') . ' r on u.roleid =r.id  ' . " WHERE 1 {$condition} ", $params);
	$pager = pagination($total, $pindex, $psize);
	$roles = pdo_fetchall('select id,rolename from ' . tablename('sz_yi_perm_role') . ' where uniacid=:uniacid and deleted=0', array(':uniacid' => $_W['uniacid']));
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		ca('perm.user.add');
	} else {
		ca('perm.user.view|perm.user.edit');
	}
	$item = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_perm_user') . ' WHERE id =:id and deleted=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	$perms = $this->model->allPerms();
	$role_perms = array();
	$user_perms = array();
	if (!empty($item)) {
		if ($item['uid'] == $_W['uid']) {
			message('无法修改自己的权限！', referer(), 'error');
		}
		$role = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_perm_role') . ' WHERE id =:id and deleted=0 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $item['roleid']));
		if (!empty($role)) {
			$role_perms = explode(',', $role['perms']);
		}
		$user_perms = explode(',', $item['perms']);
	}
	if ($_W['isajax'] && $_W['ispost']) {
		$data = array('uniacid' => $_W['uniacid'], 'username' => trim($_GPC['username']), 'realname' => trim($_GPC['realname']), 'mobile' => trim($_GPC['mobile']), 'roleid' => intval($_GPC['roleid']), 'status' => intval($_GPC['status']), 'perms' => is_array($_GPC['perms']) ? implode(',', $_GPC['perms']) : '');
		if (!empty($item['id'])) {
			user_update(array('uid' => $item['uid'], 'password' => $_GPC['password']));
			$user = user_single(array('username' => $item['username']));
			$data['uid'] = $user['uid'];
			$data['password'] = $user['password'];
			pdo_update('sz_yi_perm_user', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
			plog('perm.user.edit', "编辑操作员 ID: {$id} 用户名: {$data['username']} ");
		} else {
			if (user_check(array('username' => $data['username']))) {
				if (!user_check(array('username' => $data['username'], 'password' => $_GPC['password']))) {
					die(json_encode(array('result' => 0, 'message' => '此用户为系统存在用户，但是您输入的密码不正确，无法添加')));
				}
				$user = user_single(array('username' => $item['username']));
				$data['uid'] = $user['uid'];
				$data['password'] = $user['password'];
			} else {
				$data['uid'] = user_register(array('username' => $data['username'], 'password' => $_GPC['password']));
				pdo_insert('uni_account_users', array('uid' => $data['uid'], 'uniacid' => $data['uniacid'], 'role' => 'operator'));
			}
			pdo_insert('sz_yi_perm_user', $data);
			$id = pdo_insertid();
			plog('perm.user.add', "添加操作员 ID: {$id} 用户名: {$data['username']} ");
		}
		die(json_encode(array('result' => 1)));
	}
} elseif ($operation == 'delete') {
	ca('perm.user.delete');
	$id = intval($_GPC['id']);
	$item = pdo_fetch('SELECT id,uid,username FROM ' . tablename('sz_yi_perm_user') . " WHERE id = '$id'");
	if (empty($item)) {
		message('抱歉，操作员不存在或是已经被删除！', $this->createPluginWebUrl('perm/user', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_perm_user', array('id' => $id, 'uniacid' => $_W['uniacid']));
	plog('perm.user.delete', "删除操作员 ID: {$id} 用户名: {$item['username']} ");
	message('操作员删除成功！', $this->createPluginWebUrl('perm/user', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('user');
