<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
load()->model('user');
load()->func('tpl');
$_W['token'] = token();
$session = json_decode(base64_decode($_GPC['__session']), true);
if(is_array($session)) {
	$user = user_single(array('uid'=>$session['uid']));
	if(is_array($user) && $session['hash'] == md5($user['password'] . $user['salt'])) {
		$_W['uid'] = $user['uid'];
		$_W['username'] = $user['username'];
		$user['currentvisit'] = $user['lastvisit'];
		$user['currentip'] = $user['lastip'];
		$user['lastvisit'] = $session['lastvisit'];
		$user['lastip'] = $session['lastip'];
		$_W['user'] = $user;
		$founders = explode(',', $_W['config']['setting']['founder']);
		$_W['isfounder'] = in_array($_W['uid'], $founders);
		unset($founders);
	} else {
		isetcookie('__session', false, -100);
	}
	unset($user);
}
unset($session);

if(!empty($_GPC['__uniacid'])) {
	$_W['uniacid'] = intval($_GPC['__uniacid']);
	$_W['uniaccount'] = $_W['account'] = uni_fetch($_W['uniacid']);
	$_W['acid'] = $_W['account']['acid'];
	$_W['weid'] = $_W['uniacid'];
	if(!empty($_W['uid'])) {
		$_W['role'] = uni_permission($_W['uid'], $_W['uniacid']);
	}
}
$_W['template'] = 'default';
if(!empty($_W['setting']['basic']['template'])) {
	$_W['template'] = $_W['setting']['basic']['template'];
}
load()->func('compat.biz');
