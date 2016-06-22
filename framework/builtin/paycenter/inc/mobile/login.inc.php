<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$do = 'login';

if($_W['isajax']) {
	load()->model('user');
	$user['username'] = trim($_GPC['username']);
	$user['password'] = trim($_GPC['password']);

	$user = user_single($user);
	if(empty($user)) {
		message(error(-1, '账号或密码错误'), '', 'ajax');
	}
	if($user['status'] == 1) {
		message(error(-1, '您的账号正在审核或是已经被系统禁止，请联系网站管理员解决'), '', 'ajax');
	}

	$cookie = array();
	$cookie['uid'] = $record['uid'];
	$cookie['lastvisit'] = $record['lastvisit'];
	$cookie['lastip'] = $record['lastip'];
	$cookie['hash'] = md5($record['password'] . $record['salt']);
	$session = base64_encode(json_encode($cookie));
	isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0, true);
	header('location:' . $this->createMobileUrl('home'));
	die;
}
include $this->template('login');
