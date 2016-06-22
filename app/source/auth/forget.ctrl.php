<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$openid = $_W['openid'];
$dos = array('reset', 'forget');
$post = $_GPC['__input'];

$setting = uni_setting($_W['uniacid'], array('uc'));
$uc_setting = $setting['uc'] ? $setting['uc'] : array();

$do = in_array($post['mode'], $dos) ? $post['mode'] : 'forget';
$forward = url('mc');
if(!empty($_GPC['forward'])) {
	$forward = './index.php?' . base64_decode($_GPC['forward']) . '#wechat_redirect';
}
if(!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
	header('location: ' . $forward);
	exit;
}

if($do == 'forget') {
}
if($do == 'reset') {
	if($_W['ispost'] && $_W['isajax']) {
		$username = trim($post['username']);
		$password = trim($post['password']);
		$repassword = trim($post['repassword']);
		$repassword <> $password ? exit('两次密码输入不一致') : '';
		$code = trim($post['code']);
		load()->model('utility');
		if(!code_verify($_W['uniacid'], $username, $code)) {
			exit('验证码错误.');
		}
	
		$sql = 'SELECT `uid`,`salt` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		if(preg_match('/^\d{11}$/', $username)) {
			$type = 'mobile';
			$sql .= ' AND `mobile`=:mobile';
			$pars[':mobile'] = $username;
		} elseif(preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $username)) {
			$type = 'email';
			$sql .= ' AND `email`=:email';
			$pars[':email'] = $username;
		} else {
			exit('用户名格式不正确');
		}
		$user = pdo_fetch($sql, $pars);
		if(empty($user)) {
			exit('没有找到用户名为' . $username . '的用户信息');
		} else {
			$password = md5($password . $user['salt'] . $_W['config']['setting']['authkey']);
			pdo_update('mc_members', array('password' => $password), array('uniacid' => $_W['uniacid'], $type => $username));
		}
		exit('success');
	}
}
template('auth/forget');
exit;
