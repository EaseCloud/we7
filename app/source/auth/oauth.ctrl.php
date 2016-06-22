<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

$code = $_GPC['code'];
$scope = $_GPC['scope'];

if (empty($_W['account']['oauth']) || empty($code)) {
	exit('通信错误，请在微信中重新发起请求');
}
$oauth_account = WeAccount::create($_W['account']['oauth']);
$oauth = $oauth_account->getOauthInfo($code);

if (is_error($oauth) || empty($oauth['openid'])) {
	$state = 'we7sid-'.$_W['session_id'];
	$str = '';
	if(uni_is_multi_acid()) {
		$str = "&j={$_W['acid']}";
	}
	$url = "{$_W['siteroot']}app/index.php?i={$_W['uniacid']}{$str}&c=auth&a=oauth&scope=snsapi_base";
	$callback = urlencode($url);
	$forward = $oauth_account->getOauthCodeUrl($callback, $state);
	header('Location: ' . $forward);
	exit;
}

$_SESSION['oauth_openid'] = $oauth['openid'];
$_SESSION['oauth_acid'] = $_W['account']['oauth']['acid'];

if (intval($_W['account']['level']) == 4) {
	$fan = mc_fansinfo($oauth['openid']);
	if (!empty($fan)) {
		$_SESSION['openid'] = $oauth['openid'];
		if (!empty($fan['uid'])) {
			$member = mc_fetch($fan['uid'], array('uid'));
			if (!empty($member) && $member['uniacid'] == $_W['uniacid']) {
				$_SESSION['uid'] = $member['uid'];
			}
		}
	} else {
		$accObj = WeAccount::create($_W['account']);
		$userinfo = $accObj->fansQueryInfo($oauth['openid']);
		if(!is_error($userinfo) && !empty($userinfo) && !empty($userinfo['subscribe'])) {
			$userinfo['nickname'] = stripcslashes($userinfo['nickname']);
			if (!empty($userinfo['headimgurl'])) {
				$userinfo['headimgurl'] = rtrim($userinfo['headimgurl'], '0') . 132;
			}
			$userinfo['avatar'] = $userinfo['headimgurl'];
			$_SESSION['userinfo'] = base64_encode(iserializer($userinfo));

			$record = array(
				'openid' => $userinfo['openid'],
				'uid' => 0,
				'acid' => $_W['acid'],
				'uniacid' => $_W['uniacid'],
				'salt' => random(8),
				'updatetime' => TIMESTAMP,
				'nickname' => stripslashes($userinfo['nickname']),
				'follow' => $userinfo['subscribe'],
				'followtime' => $userinfo['subscribe_time'],
				'unfollowtime' => 0,
				'tag' => base64_encode(iserializer($userinfo))
			);
			if (!isset($unisetting['passport']) || empty($unisetting['passport']['focusreg'])) {
				$default_groupid = pdo_fetchcolumn('SELECT groupid FROM ' .tablename('mc_groups') . ' WHERE uniacid = :uniacid AND isdefault = 1', array(':uniacid' => $_W['uniacid']));
				$data = array(
					'uniacid' => $_W['uniacid'],
					'email' => md5($oauth['openid']).'@we7.cc',
					'salt' => random(8),
					'groupid' => $default_groupid,
					'createtime' => TIMESTAMP,
					'password' => md5($message['from'] . $data['salt'] . $_W['config']['setting']['authkey']),
					'nickname' => stripslashes($userinfo['nickname']),
					'avatar' => $userinfo['headimgurl'],
					'gender' => $userinfo['sex'],
					'nationality' => $userinfo['country'],
					'resideprovince' => $userinfo['province'] . '省',
					'residecity' => $userinfo['city'] . '市',
				);
				pdo_insert('mc_members', $data);
				$uid = pdo_insertid();
				$record['uid'] = $uid;
				$_SESSION['uid'] = $uid;
			}
			pdo_insert('mc_mapping_fans', $record);
		} else {
			$record = array(
				'openid' => $oauth['openid'],
				'nickname' => '',
				'subscribe' => '0',
				'subscribe_time' => '',
				'headimgurl' => '',
			);
		}
		$_SESSION['openid'] = $oauth['openid'];
		$_W['fans'] = $record;
		$_W['fans']['from_user'] = $record['openid'];
	}
}
if (intval($_W['account']['level']) != 4) {
	$mc_oauth_fan = mc_oauth_fans($oauth['openid'], $_W['acid']);
	if (empty($mc_oauth_fan) && (!empty($_SESSION['openid']) || !empty($_SESSION['uid']))) {
		$data = array(
			'acid' => $_W['acid'],
			'oauth_openid' => $oauth['openid'],
			'uid' => intval($_SESSION['uid']),
			'openid' => $_SESSION['openid']
		);
		pdo_insert('mc_oauth_fans', $data);
	}
	if (!empty($mc_oauth_fan)) {
		if (empty($_SESSION['uid']) && !empty($mc_oauth_fan['uid'])) {
			$_SESSION['uid'] = intval($mc_oauth_fan['uid']);
		}
		if (empty($_SESSION['openid']) && !empty($mc_oauth_fan['openid'])) {
			$_SESSION['openid'] = strval($mc_oauth_fan['openid']);
		}
	}
}
if ($scope == 'userinfo') {
	$userinfo = $oauth_account->getOauthUserInfo($oauth['access_token'], $oauth['openid']);
	if (!is_error($userinfo)) {
		$userinfo['nickname'] = stripcslashes($userinfo['nickname']);
		if (!empty($userinfo['headimgurl'])) {
			$userinfo['headimgurl'] = rtrim($userinfo['headimgurl'], '0') . 132;
		}
		$userinfo['avatar'] = $userinfo['headimgurl'];
		$_SESSION['userinfo'] = base64_encode(iserializer($userinfo));
		$fan = mc_fansinfo($oauth['openid']);
		if (!empty($fan)) {
			$record = array();
			$record['updatetime'] = TIMESTAMP;
			$record['nickname'] = stripslashes($userinfo['nickname']);
			$record['tag'] = base64_encode(iserializer($userinfo));
			pdo_update('mc_mapping_fans', $record, array('openid' => $fan['openid'], 'acid' => $_W['acid'], 'uniacid' => $_W['uniacid']));
		}
		if(!empty($fan['uid']) || !empty($_SESSION['uid'])) {
			$uid = $fan['uid'];
			if(empty($uid)){
				$uid = $_SESSION['uid'];
			}
			$user = mc_fetch($uid, array('nickname', 'gender', 'residecity', 'resideprovince', 'nationality', 'avatar'));
			$record = array();
			if(empty($user['nickname']) && !empty($userinfo['nickname'])) {
				$record['nickname'] = stripslashes($userinfo['nickname']);
			}
			if(empty($user['gender']) && !empty($userinfo['sex'])) {
				$record['gender'] = $userinfo['sex'];
			}
			if(empty($user['residecity']) && !empty($userinfo['city'])) {
				$record['residecity'] = $userinfo['city'] . '市';
			}
			if(empty($user['resideprovince']) && !empty($userinfo['province'])) {
				$record['resideprovince'] = $userinfo['province'] . '省';
			}
			if(empty($user['nationality']) && !empty($userinfo['country'])) {
				$record['nationality'] = $userinfo['country'];
			}
			if(empty($user['avatar']) && !empty($userinfo['headimgurl'])) {
				$record['avatar'] = $userinfo['headimgurl'];
			}
			if(!empty($record)) {
				pdo_update('mc_members', $record, array('uid' => intval($user['uid'])));
			}
		}
	} else {
		message('微信授权获取用户信息失败,错误信息为: ' . $response['message']);
	}
}

$forward = urldecode($_SESSION['dest_url']);
$str = '';
if(uni_is_multi_acid()) {
	$str = "&j={$_W['acid']}";
}
$forward = strexists($forward, 'i=') ? $forward : "{$forward}&i={$_W['uniacid']}{$str}";
header('Location: ' . $forward . '&wxref=mp.weixin.qq.com#wechat_redirect');
exit;