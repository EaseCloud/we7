<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$openid = $_W['openid'];
$dos = array('register', 'uc');
$do = in_array($do, $dos) ? $do : 'register';

$setting = uni_setting($_W['uniacid'], array('uc', 'passport'));
$uc_setting = $setting['uc'] ? $setting['uc'] : array();
$item = empty($setting['passport']['item']) ? 'random' : $setting['passport']['item'];
$audit = intval($setting['passport']['audit']);

$forward = url('mc');
if(!empty($_GPC['forward'])) {
	$forward = './index.php?' . base64_decode($_GPC['forward']) . '#wechat_redirect';
}
if(!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
	header('location: ' . $forward);
	exit;
}

if($do == 'register') {
	if($_W['ispost'] && $_W['isajax']) {
		$post = $_GPC['__input'];
		$username = trim($post['username']);
		$code = trim($post['code']);
		$password = trim($post['password']);
		$repassword = trim($post['repassword']);
		$repassword != $password && exit('两次密码输入不一致');
		$sql = 'SELECT `uid` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
				if($item == 'email') {
			if(preg_match(REGULAR_EMAIL, $username)) {
				$type = 'email';
				$sql .= ' AND `email`=:email';
				$pars[':email'] = $username;
			} else {
				exit('邮箱格式不正确');
			}
		} elseif($item == 'mobile') {
			if(preg_match(REGULAR_MOBILE, $username)) {
				$type = 'mobile';
				$sql .= ' AND `mobile`=:mobile';
				$pars[':mobile'] = $username;
			} else {
				exit('手机号格式不正确');
			}
		} else {
			if(preg_match(REGULAR_MOBILE, $username)) {
				$type = 'mobile';
				$sql .= ' AND `mobile`=:mobile';
				$pars[':mobile'] = $username;
			} elseif(preg_match(REGULAR_EMAIL, $username)) {
				$type = 'email';
				$sql .= ' AND `email`=:email';
				$pars[':email'] = $username;
			} else {
				exit('您输入的用户名格式错误');
			}
		}
		if($audit == 1) {
			load()->model('utility');
			if(!code_verify($_W['uniacid'], $post['username'], $post['code'])) {
				exit('验证码错误.');
			}
		}
		$user = pdo_fetch($sql, $pars);
		if(!empty($user)) {
			exit('该用户名已被注册，请输入其他用户名。');
		}
				if(!empty($_W['openid'])) {
			$fan = mc_fansinfo($_W['openid']);
			if (!empty($fan)) {
				$map_fans = $fan['tag'];
			}
			if (empty($map_fans) && isset($_SESSION['userinfo'])) {
				$map_fans = unserialize(base64_decode($_SESSION['userinfo']));
			}
		}
		
		$default_groupid = pdo_fetchcolumn('SELECT groupid FROM ' .tablename('mc_groups') . ' WHERE uniacid = :uniacid AND isdefault = 1', array(':uniacid' => $_W['uniacid']));
		$data = array(
			'uniacid' => $_W['uniacid'], 
			'salt' => random(8),
			'groupid' => $default_groupid, 
			'createtime' => TIMESTAMP,
		);
		
		$data['email']  = $type == 'email'  ? $username : '';
		$data['mobile'] = $type == 'mobile' ? $username : '';
		$data['password'] = md5($password . $data['salt'] . $_W['config']['setting']['authkey']);
		
		if(!empty($map_fans)) {
			$data['nickname'] = $map_fans['nickname'];
			$data['gender'] = $map_fans['sex'];
			$data['residecity'] = $map_fans['city'] ? $map_fans['city'] . '市' : '';
			$data['resideprovince'] = $map_fans['province'] ? $map_fans['province'] . '省' : '';
			$data['nationality'] = $map_fans['country'];
			$data['avatar'] = rtrim($map_fans['headimgurl'], '0') . 132;
		}
		
		pdo_insert('mc_members', $data);
		$user['uid'] = pdo_insertid();
		
		if (!empty($fan) && !empty($fan['fanid'])) {
			pdo_update('mc_mapping_fans', array('uid'=>$user['uid']), array('fanid'=>$fan['fanid']));
		}
		if(_mc_login($user)) {
			exit('success');
		}
		exit('未知错误导致注册失败');
	}
	template('auth/register');
	exit;
}
