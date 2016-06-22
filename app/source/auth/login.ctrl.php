<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$openid = $_W['openid'];
$dos = array('basic', 'uc');
$do = in_array($do, $dos) ? $do : 'basic';

$setting = uni_setting($_W['uniacid'], array('uc', 'passport'));
$uc_setting = $setting['uc'] ? $setting['uc'] : array();
$ltype = empty($setting['passport']['type']) ? 'hybird' : $setting['passport']['type'];

$forward = url('mc');
if(!empty($_GPC['forward'])) {
	$forward = './index.php?' . base64_decode($_GPC['forward']) . '#wechat_redirect';
}
if(!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
	header('location: ' . $forward);
	exit;
}
if($do == 'basic') {
	if($_W['ispost'] && $_W['isajax']) {
		$post = $_GPC['__input'];
		$mode = $post['mode'];
		$modes = array('basic', 'code');
		$mode = in_array($mode, $modes) ? $mode : 'basic';

		if($ltype == 'password' || ($ltype == 'hybird' && $mode == 'basic')) {
			$sql = 'SELECT `uid`,`salt`,`password` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid';
			$pars = array();
			$pars[':uniacid'] = $_W['uniacid'];
			if(preg_match(REGULAR_MOBILE, $post['username'])) {
				$sql .= ' AND `mobile`=:mobile';
				$pars[':mobile'] = $post['username'];
			} else {
				$sql .= ' AND `email`=:email';
				$pars[':email'] = $post['username'];
			}
			$user = pdo_fetch($sql, $pars);
			if(empty($user)) {
				exit('不存在该账号的用户资料');
			}
			$hash = md5($post['password'] . $user['salt'] . $_W['config']['setting']['authkey']);
			if($user['password'] != $hash) {
				exit('密码错误');
			}
		} elseif($ltype == 'code' || ($ltype == 'hybird' && $mode == 'code')) {
			load()->model('utility');
			if(!code_verify($_W['uniacid'], $post['username'], $post['password'])) {
				exit('验证码错误.');
			}
			$sql = 'SELECT `uid`,`salt`,`password` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid';
			$pars = array();
			$pars[':uniacid'] = $_W['uniacid'];
			if(preg_match(REGULAR_MOBILE, $post['username'])) {
				$sql .= ' AND `mobile`=:mobile';
				$pars[':mobile'] = $post['username'];
			} else {
				$sql .= ' AND `email`=:email';
				$pars[':email'] = $post['username'];
			}
			$user = pdo_fetch($sql, $pars);
			if(empty($user)) {
				exit('不存在该账号的用户资料');
			}
		}
		if(_mc_login($user)) {
			exit('success');
		}
		exit('未知错误导致登陆失败');
	}
	template('auth/login');
	exit;
} elseif($do == 'uc') {
	if($_W['ispost'] && $_W['isajax']) {
		if(empty($uc_setting) || $uc_setting['status'] <> 1) {
			exit('系统尚未开启UC');
		}
		
		$post = $_GPC['__input'];
		$username = trim($post['username']);
		$password = trim($post['password']);
		empty($username) && exit('请填写' . $uc_setting['title'] . '用户名');
		empty($password) && exit('请填写' . $uc_setting['title'] . '密码！');
		
		mc_init_uc();
		$data = uc_user_login($username, $password);
		if($data[0] < 0) {
			if($data[0] == -1) exit('用户不存在，或者被删除！');
			elseif ($data[0] == -2) exit('密码错误！');
			elseif ($data[0] == -3) exit('安全提问错误！');
		}
		
		$exist = pdo_fetch('SELECT * FROM ' . tablename('mc_mapping_ucenter') . ' WHERE `uniacid`=:uniacid AND `centeruid`=:centeruid', array(':uniacid' => $_W['uniacid'], 'centeruid' => $data[0]));
		if(!empty($exist)) {
			$user['uid'] = $exist['uid'];
			if(_mc_login($user)) {
				exit('success');
			} else {
				exit('未知错误导致登陆失败');
			}
		} else {
			if (!empty($_SESSION['openid'])) {
				$default_groupid = pdo_fetchcolumn('SELECT groupid FROM ' .tablename('mc_groups') . ' WHERE uniacid = :uniacid AND isdefault = 1', array(':uniacid' => $_W['uniacid']));
				$user = array(
					'uniacid' => $_W['uniacid'],
					'email' => $data[3],
					'salt' => random(8),
					'groupid' => $default_groupid,
					'createtime' => TIMESTAMP,
				);
				$user['password'] = md5($data[2] . $user['salt'] . $_W['config']['setting']['authkey']);
				pdo_insert('mc_members', $user);
				$uid = pdo_insertid();
				pdo_insert('mc_mapping_ucenter', array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'centeruid' => $data[0]));
				pdo_update('mc_mapping_fans', array('uid' => $uid), array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid'], 'openid' => $_SESSION['openid']));
				$user['uid'] = $uid;
				if(_mc_login($user)) {
					exit('success');
				} else {
					exit('未知错误导致登陆失败');
				}
			}
			exit('该' . $uc_setting['title'] . '账号尚未绑定系统账号');
		}
	}
	template('auth/uc-login');
	exit;
}
