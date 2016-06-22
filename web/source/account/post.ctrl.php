<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$_W['page']['title'] = '公众号基本信息 - 编辑主公众号';

$uniacid = intval($_GPC['uniacid']);
$acid = intval($_GPC['acid']);

if (empty($uniacid)) {
	message('请选择要编辑的公众号');
}
$state = uni_permission($_W['uid'], $uniacid);
if($state != 'founder' && $state != 'manager') {
	message('没有该公众号操作权限！');
}

if (checksubmit('submit')) {
	load()->model('module');
	load()->func('file');
	$uniaccount = array(
		'name' => $_GPC['cname'],
		'groupid' => 0,
		'description' => $_GPC['description'],
	);

	pdo_update('uni_account', $uniaccount, array('uniacid' => $uniacid));
	$account = array(
		'account' => $_GPC['account'],
		'original' => $_GPC['original'],
		'level' => intval($_GPC['level']),
		'key' => trim($_GPC['key']),
		'secret' => trim($_GPC['secret']),
		'token' => trim($_GPC['wetoken']),
		'encodingaeskey' => trim($_GPC['encodingaeskey']),
		'subscribeurl' => $_GPC['subscribeurl'],
	);
	if (!empty($_GPC['wxusername']) && !empty($_GPC['wxpassword'])) {
		$account['username'] = $_GPC['wxusername'];
		$account['password'] = md5($_GPC['wxpassword']);
	}
	if (!empty($_GPC['subname'])) {
		$account['name'] = $_GPC['subname'];
	}
	if (empty($_GET['acid'])) {
		$account['name'] = $_GPC['cname'];
	}
	pdo_update('account_wechats', $account, array('acid' => $acid, 'uniacid' => $uniacid));
	if (!empty($_GPC['type'])) {
		pdo_update('account', array('type' => intval($_GPC['type'])), array('acid' => $acid, 'uniacid' => $uniacid));
	}
	$oauth = (array)uni_setting($uniacid, array('oauth'));
	if(!empty($account['key']) && !empty($account['secret']) && empty($oauth['oauth']['account']) && $account['level'] == 4) {
		pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
	} elseif($oauth['oauth'] == $acid && (empty($account['secret']) || empty($account['key']) || $account['level'] != 4)) {
		$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :uniacid AND level = 4 AND secret != '' AND `key` != ''", array(':uniacid' => $uniacid));
		pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
	}
	if (!empty($_FILES['qrcode']['tmp_name'])) {
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = '';
		$_W['uploadsetting']['image']['extentions'] = array('jpg');
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$upload = file_upload($_FILES['qrcode'], 'image', "qrcode_{$acid}");
		if(is_error($upload)) {
			message('二维码保存失败,'.$upload['message'],referer(),'info');
		} else {
			$result = file_remote_upload($upload['path']);
			if (!is_error($result) && $result !== false) {
				file_delete($upload['path']);
			}
		}
	}
	if (!empty($_FILES['headimg']['tmp_name'])) {
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = '';
		$_W['uploadsetting']['image']['extentions'] = array('jpg');
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$upload = file_upload($_FILES['headimg'], 'image', "headimg_{$acid}");
		if(is_error($upload)) {
			message('头像保存失败,'.$upload['message'], referer(), 'info');
		} else {
			$result = file_remote_upload($upload['path']);
			if (!is_error($result) && $result !== false) {
				file_delete($upload['path']);
			}
		}
	}
	cache_delete("uniaccount:{$uniacid}");
	cache_delete("unisetting:{$uniacid}");
	cache_delete("accesstoken:{$acid}");
	cache_delete("jsticket:{$acid}");
	cache_delete("cardticket:{$acid}");
	module_build_privileges();
	message('更新公众号成功！', referer(), 'success');
}

$settings = uni_setting($uniacid, array('notify'));
$notify = $settings['notify'] ? $settings['notify'] : array();
$qrcodesrc = tomedia('qrcode_'.$_W['acid'].'.jpg');
$headimgsrc = tomedia('headimg_'.$_W['acid'].'.jpg');
$account = $uniaccount = array();
$uniaccount = pdo_fetch("SELECT * FROM ".tablename('uni_account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
$acid = !empty($acid) ? $acid : $uniaccount['default_acid'];
$permission = pdo_get('account_wechats', array('uniacid' => $uniacid, 'acid' => $acid), array('acid'));
if(empty($permission)) {
	message('没有该公众号操作权限！', url('account/display'), 'error');
}
$account = account_fetch($acid);
if ($_W['setting']['platform']['authstate']) {
	load()->classs('weixin.platform');
	$account_platform = new WeiXinPlatform();
	$preauthcode = $account_platform->getPreauthCode();
	if (is_error($preauthcode)) {
		$authurl = "javascript:alert('{$preauthcode['message']}');";
	} else {
		$authurl = sprintf(ACCOUNT_PLATFORM_API_LOGIN, $account_platform->appid, $preauthcode, urlencode(urlencode($GLOBALS['_W']['siteroot'] . 'index.php?c=account&a=auth&do=forward')));
	}
}
template('account/post');