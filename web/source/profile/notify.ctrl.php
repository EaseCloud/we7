<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('profile_notify');
$row = pdo_fetchcolumn("SELECT `notify` FROM ".tablename('uni_settings') . " WHERE uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
$notify = iunserializer($row);
if(!is_array($notify)) {
	$notify['sms'] = array();
	$notify['sms']['balance'] = 0;
	$notify['sms']['signature'] = '系统默认';
	$notify['mail'] = array();
}

$dos = array('mail', 'sms', 'wechat');
$do = in_array($do, $dos) ? $do : 'mail';
$_W['page']['title'] = 'APP通知 - 通知参数 - 通知中心';
if($do == 'mail') {
	$_W['page']['title'] = '邮件通知 - 通知参数 - 通知中心';
	if(checksubmit('submit')) {
		$notify['mail'] = array(
			'username' => $_GPC['username'],
			'password' => $_GPC['password'],
			'smtp' => $_GPC['smtp'],
			'sender' => $_GPC['sender'],
			'signature' => $_GPC['signature'],
		);
		$row = array();
		$row['notify'] = iserializer($notify);
		pdo_update('uni_settings', $row, array('uniacid' => $_W['uniacid']));
		load()->func('communication');
		if (!empty($_GPC['testsend']) && !empty($_GPC['receiver'])) {
			$result = ihttp_email($_GPC['receiver'], $_W['account']['name'] . '验证邮件'.date('Y-m-d H:i:s'), '如果您收到这封邮件则表示您系统的发送邮件配置成功！');
			if (is_error($result)) {
				message('配置失败，请检查配置信息');
			}
		} else {
			$result = ihttp_email($notify['mail']['username'], $_W['account']['name'] . '验证邮件'.date('Y-m-d H:i:s'), '如果您收到这封邮件则表示您系统的发送邮件配置成功！');
			if (is_error($result)) {
				message('配置失败，请检查配置信息');
			}
		}
		cache_delete("unisetting:{$_W['uniacid']}");
		message('更新设置成功！', url('profile/notify',array('do' => 'mail')));
	}
}

if($do == 'sms') {
	$_W['page']['title'] = '短信通知 - 通知参数 - 通知中心';
	$notify['sms'] = array(
		'balance' => $notify['sms']['balance'],
		'signature' => $notify['sms']['signature']
	);
}

if($do == 'wechat') {
	$_W['page']['title'] = '微信通知 - 通知参数 - 通知中心';
	$acid = pdo_fetchall("SELECT acid,name FROM ".tablename('account_wechats')." WHERE uniacid = :uniacid AND level = 4", array(':uniacid' => $_W['uniacid']));
	if(!empty($notify['wechat']['items'])) {
		$itemsarr = explode(',',$notify['wechat']['items']);
	}
	foreach($acid as &$li) {
		if($itemsarr) {
			if(in_array($li['acid'],$itemsarr)) {
				$li['is_open'] = 1;
			} else {
				$li['is_open'] = 0;
			}
		}
	}
	if(checksubmit('submit')) {
		if(!empty($_GPC['item'])) {
			$items = implode(',',$_GPC['item']);
		}
		$notify['wechat'] = array(
			'switch' => intval($_GPC['switch']),
			'items' => $items	
		);
		$row = array();
		$row['notify'] = iserializer($notify);
		pdo_update('uni_settings', $row, array('uniacid' => $_W['uniacid']));
		cache_delete("unisetting:{$_W['uniacid']}");
		message('更新设置成功！',  url('profile/notify',array('do' => 'wechat')));
	}
	
}

template('profile/notify');