<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
$uniacid = intval($_GPC['uniacid']);
$_W['page']['title'] = '添加/编辑公众号';
load()->func('file');
$step = intval($_GPC['step']) ? intval($_GPC['step']) : 1;
if($step == 1) {
	if(!empty($uniacid)) {
		$state = uni_permission($uid, $uniacid);
		if($state != 'founder' && $state != 'manager') {
			message('没有该公众号操作权限！');
		}
		if (is_error($permission = uni_create_permission($_W['uid'], 2))) {
			message($permission['message'], '' , 'error');
		}
	} else {
		if(empty($_W['isfounder']) && is_error($permission = uni_create_permission($_W['uid'], 1))) {
			message($permission['message'], '' , 'error');
			if(is_error($permission = uni_create_permission($_W['uid'], 2))) {
				message($permission['message'], '' , 'error');
			}
		}
	}
} elseif($step == 2) {
	if(checksubmit('getinfo')) {
		$username = trim($_GPC['wxusername']);
		$password = md5(trim($_GPC['wxpassword']));
		if(!empty($username) && !empty($password)) {
			$loginstatus = account_weixin_login($username, $password, trim($_GPC['verify']));
			if(is_error($loginstatus)) {
				message('模拟登陆微信公众平台出错,错误详情:' . $loginstatus['message'], url('account/post-step', array('uniacid' => $uniacid, 'step' => 1)), 'error');
			}
			$basicinfo = account_weixin_basic($username);
			if (empty($basicinfo['name'])) {
				message('一键获取信息失败,请手动设置公众号信息！错误详情:' . $basicinfo['message'], url('account/post-step/', array('uniacid' => $uniacid, 'step' => 2)), 'error');
			}
			$account = array(
				'name' => $basicinfo['name'],
				'account' => $basicinfo['account'],
				'description' => $basicinfo['signature'],
				'level' => $basicinfo['level'],
				'key' => $basicinfo['key'],
				'acid' => $basicinfo['account'],
				'original' => $basicinfo['original'],
			);
			file_put_contents(IA_ROOT . '/attachment/headimg_'.$account['acid'].'.jpg', $basicinfo['headimg']);
			file_put_contents(IA_ROOT . '/attachment/qrcode_'.$account['acid'].'.jpg', $basicinfo['qrcode']);
		} else {
			message('请填写公众平台用户名和密码', url('account/post-step', array('uniacid' => $uniacid, 'step' => 1)), 'error');
		}
	}
		if (checksubmit('submit')) {
		$update = array();
		$update['name'] = trim($_GPC['cname']);
		if(empty($update['name'])) {
			message('公众号名称必须填写');
		}
				if(empty($uniacid)) {
			$name = trim($_GPC['cname']);
			$description = trim($_GPC['description']);
			$data = array(
				'name' => $name,
				'description' => $description,
				'groupid' => 0,
			);
			if(!pdo_insert('uni_account', $data)) {
				message('添加公众号失败');
			}
			$uniacid = pdo_insertid();
						$template = pdo_fetch('SELECT id,title FROM ' . tablename('site_templates') . " WHERE name = 'default'");
			$styles['uniacid'] = $uniacid;
			$styles['templateid'] = $template['id'];
			$styles['name'] = $template['title'] . '_' . random(4);
			pdo_insert('site_styles', $styles);
			$styleid = pdo_insertid();
						$multi['uniacid'] = $uniacid;
			$multi['title'] = $data['name'];
			$multi['styleid'] = $styleid;
			pdo_insert('site_multi', $multi);
			$multi_id = pdo_insertid();

			$unisettings['creditnames'] = array('credit1' => array('title' => '积分', 'enabled' => 1), 'credit2' => array('title' => '余额', 'enabled' => 1));
			$unisettings['creditnames'] = iserializer($unisettings['creditnames']);
			$unisettings['creditbehaviors'] = array('activity' => 'credit1', 'currency' => 'credit2');
			$unisettings['creditbehaviors'] = iserializer($unisettings['creditbehaviors']);
			$unisettings['uniacid'] = $uniacid;
			$unisettings['default_site'] = $multi_id;
			$unisettings['sync'] = iserializer(array('switch' => 0, 'acid' => ''));
			pdo_insert('uni_settings', $unisettings);

			pdo_insert('mc_groups', array('uniacid' => $uniacid, 'title' => '默认会员组', 'isdefault' => 1));
			$fields = pdo_getall('profile_fields');
			foreach($fields as $field) {
				$data = array(
					'uniacid' => $uniacid,
					'fieldid' => $field['id'],
					'title' => $field['title'],
					'available' => $field['available'],
					'displayorder' => $field['displayorder'],
				);
				pdo_insert('mc_member_fields', $data);
			}
			load()->model('module');
			module_build_privileges();
		}
		$update['account'] = trim($_GPC['account']);
		$update['original'] = trim($_GPC['original']);
		$update['level'] = intval($_GPC['level']);
		$update['key'] = trim($_GPC['key']);
		$update['secret'] = trim($_GPC['secret']);
		$update['type'] = 1;
		$update['encodingaeskey'] = trim($_GPC['encodingaeskey']);
		if(empty($acid)) {
			$acid = account_create($uniacid, $update);
			if(is_error($acid)) {
				message('添加公众号信息失败', '', url('account/post-step/', array('uniacid' => intval($_GPC['uniacid']), 'step' => 2), 'error'));
			}
			pdo_update('uni_account', array('default_acid' => $acid), array('uniacid' => $uniacid));
			if (empty($_W['isfounder'])) {
				pdo_insert('uni_account_users', array('uniacid' => $uniacid, 'uid' => $_W['uid'], 'role' => 'owner'));
			}
		} else {
			pdo_update('account', array('type' => 1, 'hash' => ''), array('acid' => $acid, 'uniacid' => $uniacid));
			unset($update['type']);
			pdo_update('account_wechats', $update, array('acid' => $acid, 'uniacid' => $uniacid));
		}
				$oauth = uni_setting($uniacid, array('oauth'));
		if($acid && !empty($update['key']) && !empty($update['secret']) && empty($oauth['oauth']['account']) && $update['level'] == 4) {
			pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
		}
		if (!empty($_FILES['qrcode']['tmp_name'])) {
			$_W['uploadsetting'] = array();
			$_W['uploadsetting']['image']['folder'] = '';
			$_W['uploadsetting']['image']['extentions'] = array('jpg');
			$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
			$upload = file_upload($_FILES['qrcode'], 'image', "qrcode_{$acid}");
			if(is_array($upload)) {
				$result = file_remote_upload($upload['path']);
				if (!is_error($result) && $result !== false) {
					file_delete($upload['path']);
				}
			}
		} else {
			if (file_exists(IA_ROOT . '/attachment/qrcode_'.$update['account'].'.jpg')) {
				file_move(IA_ROOT . '/attachment/qrcode_'.$update['account'].'.jpg', IA_ROOT . '/attachment/qrcode_'.$acid.'.jpg');
				$result = file_remote_upload('qrcode_'.$acid.'.jpg');
				if (!is_error($result) && $result !== false) {
					file_delete('qrcode_'.$acid.'.jpg');
				}
			}
		}
		if (!empty($_FILES['headimg']['tmp_name'])) {
			$_W['uploadsetting'] = array();
			$_W['uploadsetting']['image']['folder'] = '';
			$_W['uploadsetting']['image']['extentions'] = array('jpg');
			$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
			$upload = file_upload($_FILES['headimg'], 'image', "headimg_{$acid}");
			if(is_array($upload)) {
				$result = file_remote_upload($upload['path']);
				if (!is_error($result) && $result !== false) {
					file_delete($upload['path']);
				}
			}
		} else {
			if (file_exists(IA_ROOT . '/attachment/headimg_'.$update['account'].'.jpg')) {
				file_move(IA_ROOT . '/attachment/headimg_'.$update['account'].'.jpg', IA_ROOT . '/attachment/headimg_'.$acid.'.jpg');
				$result = file_remote_upload('headimg_'.$acid.'.jpg');
				if (!is_error($result) && $result !== false) {
					file_delete('headimg_'.$acid.'.jpg');
				}
			}
		}
		cache_delete("unisetting:{$uniacid}");
		if (!empty($_GPC['uniacid']) || empty($_W['isfounder'])) {
			header("Location: ".url('account/post-step/', array('uniacid' => $uniacid, 'acid' => $acid, 'step' => 4)));
		} else {
			header("Location: ".url('account/post-step/', array('uniacid' => $uniacid, 'acid' => $acid, 'step' => 3)));
		}
		exit;
	}
} elseif ($step == 3) {
	load()->model('cloud');
	$sms_info = cloud_sms_info();
	$max_num = empty($sms_info['sms_count']) ? 0 : $sms_info['sms_count'];
	$signatures = $sms_info['sms_sign'];
	if (empty($_W['isfounder'])) {
		message('您无权进行该操作！');
	}
	if ($do == 'edit_sms') {
		$max_num = empty($sms_info['sms_count']) ? 0 : $sms_info['sms_count'];
		if ($max_num == 0) {
			message(error(-1), '', 'ajax');
		}
		$settings = uni_setting($uniacid, array('notify'));
		$notify = $settings['notify'] ? $settings['notify'] : array();
		$balance = intval($_GPC['balance']);
		$notify['sms']['balance'] = $_GPC['status'] == 'add' ? $notify['sms']['balance'] + $balance : $notify['sms']['balance'] - $balance;
		$notify['sms']['balance'] = min(max(0, $notify['sms']['balance']), $max_num);
		$count_num = $max_num - $notify['sms']['balance'];
		$num = $notify['sms']['balance'];
		uni_setting_save('notify', $notify);
		$notify = iserializer($notify);
		$updatedata['notify'] = $notify;
		pdo_update('uni_settings', $updatedata , array('uniacid' => $uniacid));
		message(error(1, array('count' => $count_num, 'num' => $num)), '', 'ajax');
	}
	if ($do == 'userinfo') {
		$result = array();
		$uid = intval($_GPC['uid']);
		$groupid = intval($_GPC['groupid']);
		$user = user_single(array('uid' => $uid));
		if (empty($user)) {
			message(error(1, '用户不存在或是已经被删除'), '', 'ajax');
		}
		if (!empty($groupid)) {
			$user['groupid'] = $groupid;
		}
		$result['username'] = $user['username'];
		$result['uid'] = $user['uid'];
		$result['group'] = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $user['groupid']));
		$result['package'] = iunserializer($result['group']['package']);
		message($result, '', 'ajax');
	}
	if (checksubmit('submit')) {
				$uid = intval($_GPC['uid']);
		$groupid = intval($_GPC['groupid']);
		$uniacid = intval($_GPC['uniacid']);
		if (!empty($_GPC['signature'])) {
			$signature = trim($_GPC['signature']);
			$setting = pdo_get('uni_settings', array('uniacid' => $_W['uniacid']));
			$notify = iunserializer($setting['notify']);
			$notify['sms']['signature'] = $signature;

			uni_setting_save('notify', $notify);
			$notify = serialize($notify);
			pdo_update('uni_settings', array('notify' => $notify), array('uniacid' => $uniacid));
		}
		if (!empty($uid)) {
						pdo_delete('uni_account_users', array('uniacid' => $uniacid, 'uid' => $uid));
			$owner = pdo_get('uni_account_users', array('uniacid' => $uniacid, 'role' => 'owner'));
			if (!empty($owner)) {
				pdo_update('uni_account_users', array('uid' => $uid), array('uniacid' => $uniacid, 'role' => 'owner'));
			} else {
				$account_users = array('uniacid' => $uniacid, 'uid' => $uid, 'role' => 'owner');
				pdo_insert('uni_account_users', $account_users);
			}
		}
		$user = array(
			'uid' => $uid,
			'groupid' => $groupid,
		);
		if($_GPC['is-set-endtime'] == 1 && !empty($_GPC['endtime'])) {
			$user['endtime'] = strtotime($_GPC['endtime']);
		} else {
			$user['endtime'] = 0;
		}
		if (!empty($user)) {
			user_update($user);
		}
				pdo_delete('uni_account_group', array('uniacid' => $uniacid));
		if (!empty($_GPC['package'])) {
			$group = pdo_get('users_group', array('id' => $groupid));
			$group['package'] = iunserializer($group['package']);
			if (!is_array($group['package']) || !in_array('-1', $group['package'])) {
				foreach ($_GPC['package'] as $packageid) {
					if (!empty($packageid)) {
						pdo_insert('uni_account_group', array(
							'uniacid' => $uniacid,
							'groupid' => $packageid,
						));
					}
				}
			}
		}
				if (!empty($_GPC['extra']['modules']) || !empty($_GPC['extra']['templates'])) {
			$data = array(
				'modules' => iserializer($_GPC['extra']['modules']),
				'templates' => iserializer($_GPC['extra']['templates']),
				'uniacid' => $uniacid,
				'name' => '',
			);
			$id = pdo_fetchcolumn("SELECT id FROM ".tablename('uni_group')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
			if (empty($id)) {
				pdo_insert('uni_group', $data);
			} else {
				pdo_update('uni_group', $data, array('id' => $id));
			}
		} else {
			pdo_delete('uni_group', array('uniacid' => $uniacid));
		}
		cache_delete("unisetting:{$uniacid}");
		cache_delete("unimodules:{$uniacid}:1");
		cache_delete("unimodules:{$uniacid}:");
		cache_delete("uniaccount:{$uniacid}");
		cache_delete("accesstoken:{$acid}");
		cache_delete("jsticket:{$acid}");
		cache_delete("cardticket:{$acid}");
		load()->model('module');
		module_build_privileges();
		if (!empty($_GPC['from'])) {
			message('公众号权限修改成功', url('account/post-step/', array('uniacid' => $uniacid, 'step' => 3, 'from' => 'list')), 'success');
		} else {
			header("Location: ".url('account/post-step/', array('uniacid' => $uniacid, 'acid' => $acid, 'step' => 4)));
			exit;
		}
	}
	$unigroups = uni_groups();
	$settings = uni_setting($uniacid, array('notify'));
	$notify = $settings['notify'] ? $settings['notify'] : array();
	$ownerid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	if (!empty($ownerid)) {
		$owner = user_single(array('uid' => $ownerid));
		$owner['group'] = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $owner['groupid']));
		$owner['group']['package'] = iunserializer($owner['group']['package']);
	}
	$extend = pdo_fetch("SELECT * FROM ".tablename('uni_group')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
	$extend['modules'] = iunserializer($extend['modules']);
	$extend['templates'] = iunserializer($extend['templates']);
	if (!empty($extend['modules'])) {
		$owner['extend']['modules'] = pdo_getall('modules', array('name' => $extend['modules']));
	}
	if (!empty($extend['templates'])) {
		$owner['extend']['templates'] = pdo_getall('site_templates', array('id' => $extend['templates']));
	}
	$extend['package'] = pdo_getall('uni_account_group', array('uniacid' => $uniacid), array(), 'groupid');
	$groups = pdo_fetchall("SELECT id, name, package FROM ".tablename('users_group')." ORDER BY id ASC", array(), 'id');
	$modules = pdo_fetchall("SELECT mid, name, title FROM " . tablename('modules') . ' WHERE issystem != 1', array(), 'name');
	$templates  = pdo_fetchall("SELECT * FROM ".tablename('site_templates'));
} elseif($step == '4') {
	$uniacid = intval($_GPC['uniacid']);
	$acid = intval($_GPC['acid']);
	$uni_account = pdo_fetch('SELECT * FROM ' . tablename('uni_account') . ' WHERE uniacid = ' . $uniacid);
	if(empty($uni_account)) {
		message('非法访问');
	}
	$account = account_fetch($uni_account['default_acid']);
}

template('account/post-step');