<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function uni_create_permission($uid, $type = 1) {
	$groupid = pdo_fetchcolumn('SELECT groupid FROM ' . tablename('users') . ' WHERE uid = :uid', array(':uid' => $uid));
	$groupdata = pdo_fetch('SELECT maxaccount, maxsubaccount FROM ' . tablename('users_group') . ' WHERE id = :id', array(':id' => $groupid));
	$list = pdo_fetchall('SELECT uniacid FROM ' . tablename('uni_account_users') . ' WHERE uid = :uid AND role = :role ', array(':uid' => $uid, ':role' => 'owner'));
	foreach ($list as $item) {
		$uniacids[] = $item['uniacid'];
	}
	unset($item);
	$uniacidnum = count($list);
		if ($type == 1) {
		if ($uniacidnum >= $groupdata['maxaccount']) {
			return error('-1', '您所在的用户组最多只能创建' . $groupdata['maxaccount'] . '个主公号');
		}
	} elseif ($type == 2) {
		$subaccountnum = 0;
		if (!empty($uniacids)) {
			$subaccountnum = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('account') . ' WHERE uniacid IN (' . implode(',', $uniacids) . ')');
		}
		if ($subaccountnum >= $groupdata['maxsubaccount']) {
			return error('-1', '您所在的用户组最多只能创建' . $groupdata['maxsubaccount'] . '个子公号');
		}
	}
	return true;
}


function uni_owned($uid = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : intval($uid);
	$uniaccounts = array();
	$founders = explode(',', $_W['config']['setting']['founder']);
	if (in_array($uid, $founders)) {
		$uniaccounts = pdo_fetchall("SELECT * FROM " . tablename('uni_account') . " ORDER BY `uniacid` DESC", array(), 'uniacid');
	} else {
		$uniacids = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account_users') . " WHERE uid = :uid", array(':uid' => $uid), 'uniacid');
		if (!empty($uniacids)) {
			$uniaccounts = pdo_fetchall("SELECT * FROM " . tablename('uni_account') . " WHERE uniacid IN (" . implode(',', array_keys($uniacids)) . ") ORDER BY `uniacid` DESC", array(), 'uniacid');
		}
	}
	
	return $uniaccounts;
}


function uni_permission($uid = 0, $uniacid = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : intval($uid);
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$founders = explode(',', $_W['config']['setting']['founder']);
	if (in_array($uid, $founders)) {
		return 'founder';
	}

	$sql = 'SELECT `role` FROM ' . tablename('uni_account_users') . ' WHERE `uid`=:uid AND `uniacid`=:uniacid';
	$pars = array();
	$pars[':uid'] = $uid;
	$pars[':uniacid'] = $uniacid;
	$role = pdo_fetchcolumn($sql, $pars);
	if(in_array($role, array('manager', 'owner'))) {
		$role = 'manager';
	}
	return $role;
}


function uni_accounts($uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$accounts = pdo_fetchall("SELECT w.*, a.type, a.isconnect FROM " . tablename('account') . " a INNER JOIN " . tablename('account_wechats') . " w USING(acid) WHERE a.uniacid = :uniacid AND a.isdeleted <> 1 ORDER BY a.acid ASC", array(':uniacid' => $uniacid), 'acid');
	return $accounts;
}


function uni_fetch($uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$cachekey = "uniaccount:{$uniacid}";
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	$account = uni_account_default($uniacid);
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
	$account['uid'] = $owner['uid'];
	$account['starttime'] = $owner['starttime'];
	$account['endtime'] = $owner['endtime'];
	load()->model('mc');
	$account['groups'] = mc_groups($uniacid);
	$account['grouplevel'] = pdo_fetchcolumn('SELECT grouplevel FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	cache_write($cachekey, $account);
	return $account;
}


function uni_modules($enabledOnly = true) {
	global $_W;
	$cachekey = "unimodules:{$_W['uniacid']}:{$enabledOnly}";
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $_W['uniacid']));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
		if (empty($owner)) {
		$groupid = '-1';
	} else {
		$groupid = $owner['groupid'];
	}
	$extend = pdo_getall('uni_account_group', array('uniacid' => $_W['uniacid']), array(), 'groupid');
	if (!empty($extend)) {
		$groupid = '-2';
	}
	if (empty($groupid)) {
		$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . " WHERE issystem = 1 ORDER BY issystem DESC, mid ASC", array(), 'name');
	} elseif ($groupid == '-1') {
		$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . " ORDER BY issystem DESC, mid ASC", array(), 'name');
	} else {
		$group = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $groupid));
		if (!empty($group)) {
			$packageids = iunserializer($group['package']);
		} else {
			$packageids = array();
		}
		if (!empty($extend)) {
			foreach ($extend as $extend_packageid => $row) {
				$packageids[] = $extend_packageid;
			}
		}
		if (in_array('-1', $packageids)) {
			$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . " ORDER BY issystem DESC, mid ASC", array(), 'name');
		} else {
			$wechatgroup = pdo_fetchall("SELECT `modules` FROM " . tablename('uni_group') . " WHERE id IN ('".implode("','", $packageids)."') OR uniacid = '{$_W['uniacid']}'");
			$ms = array();
			$mssql = '';
			if (!empty($wechatgroup)) {
				foreach ($wechatgroup as $row) {
					$row['modules'] = iunserializer($row['modules']);
					if (!empty($row['modules'])) {
						foreach ($row['modules'] as $modulename) {
							$ms[$modulename] = $modulename;
						}
					}
				}
				$mssql = " OR `name` IN ('".implode("','", $ms)."')";
			}
			$modules = pdo_fetchall("SELECT * FROM " . tablename('modules') . " WHERE issystem = 1{$mssql} ORDER BY issystem DESC, mid ASC", array(), 'name');
		}
	}
	if (!empty($modules)) {
		$ms = implode("','", array_keys($modules));
		$ms = "'{$ms}'";
		$mymodules = pdo_fetchall("SELECT `module`, `enabled`, `settings` FROM " . tablename('uni_account_modules') . " WHERE uniacid = '{$_W['uniacid']}' AND `module` IN ({$ms}) ORDER BY enabled DESC", array(), 'module');
	}
	if (!empty($mymodules)) {
		foreach ($mymodules as $name => $row) {
			if ($enabledOnly && !$modules[$name]['issystem']) {
				if ($row['enabled'] == 0 || empty($modules[$name])) {
					unset($modules[$name]);
					continue;
				}
			}
			if (!empty($row['settings'])) {
				$modules[$name]['config'] = iunserializer($row['settings']);
			}
			$modules[$name]['enabled'] = $row['enabled'];
		}
	}
	foreach ($modules as $name => &$row) {
		if ($row['issystem'] == 1) {
			$row['enabled'] = 1;
		} elseif (!isset($row['enabled'])) {
			$row['enabled'] = 1;
		}
		if (empty($row['config'])) {
			$row['config'] = array();
		}
		if (!empty($row['subscribes'])) {
			$row['subscribes'] = iunserializer($row['subscribes']);
		}
		if (!empty($row['handles'])) {
			$row['handles'] = iunserializer($row['handles']);
		}
		unset($modules[$name]['description']);
	}
	$modules['core'] = array('title' => '系统模块', 'name' => 'core');
	cache_write($cachekey, $modules);
	return $modules;
}

function uni_modules_app_binding() {
	global $_W;
	$cachekey = "unimodulesappbinding:{$_W['uniacid']}";
	$cache = cache_load($cachekey);
	if (!empty($cache)) {
		return $cache;
	}
	load()->model('module');
	$result = array();
	$modules = uni_modules();
	if(!empty($modules)) {
		foreach($modules as $module) {
			if($module['type'] == 'system') {
				continue;
			}
			$entries = module_app_entries($module['name'], array('home', 'profile', 'shortcut', 'function', 'cover'));
			if(empty($entries)) {
				continue;
			}
			if($module['type'] == '') {
				$module['type'] = 'other';
			}
			$result[$module['name']] = array(
				'name' => $module['name'],
				'type' => $module['type'],
				'title' => $module['title'],
				'entries' => array(
					'cover' => $entries['cover'],
					'home' => $entries['home'],
					'profile' => $entries['profile'],
					'shortcut' => $entries['shortcut'],
					'function' => $entries['function']
				)
			);
			unset($module);
		}
	}
	cache_write($cachekey, $result);
	return $result;
}


function uni_groups($groupids = array()) {
	$condition = ' WHERE uniacid = 0';
	if (!is_array($groupids)) {
		$groupids = array($groupids);
	}
	if (!empty($groupids)) {
		foreach ($groupids as $i => $row) {
			$groupids[$i] = intval($row);
		}
		unset($row);
		$condition .= " AND id IN (" . implode(',', $groupids) . ")";
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename('uni_group') . $condition . " ORDER BY id ASC", array(), 'id');
	if (in_array('-1', $groupids)) {
		$list[-1] = array('id' => -1, 'name' => '所有服务');
	}
	if (in_array('0', $groupids)) {
		$list[0] = array('id' => 0, 'name' => '基础服务');
	}
	if (!empty($list)) {
		foreach ($list as &$row) {
			if (!empty($row['modules'])) {
				$modules = iunserializer($row['modules']);
				if (is_array($modules)) {
					$row['modules'] = pdo_fetchall("SELECT name, title FROM " . tablename('modules') . " WHERE name IN ('" . implode("','", $modules) . "')");
				}
			}
			if (!empty($row['templates'])) {
				$templates = iunserializer($row['templates']);
				if (is_array($templates)) {
					$row['templates'] = pdo_fetchall("SELECT name, title FROM " . tablename('site_templates') . " WHERE id IN ('" . implode("','", $templates) . "')");
				}
			}
		}
	}
	return $list;
}


function uni_templates() {
	global $_W;
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $_W['uniacid']));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
		if (empty($owner)) {
		$groupid = '-1';
	} else {
		$groupid = $owner['groupid'];
	}
	$extend = pdo_getall('uni_account_group', array('uniacid' => $_W['uniacid']), array(), 'groupid');
	if (!empty($extend)) {
		$groupid = '-2';
	}
	if (empty($groupid)) {
		$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " WHERE name = 'default'", array(), 'id');
	} elseif ($groupid == '-1') {
		$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " ORDER BY id ASC", array(), 'id');
	} else {
		$group = pdo_fetch("SELECT id, name, package FROM ".tablename('users_group')." WHERE id = :id", array(':id' => $groupid));
		$packageids = iunserializer($group['package']);
		if (!empty($extend)) {
			foreach ($extend as $extend_packageid => $row) {
				$packageids[] = $extend_packageid;
			}
		}
		if(is_array($packageids)) {
			if (in_array('-1', $packageids)) {
				$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " ORDER BY id ASC", array(), 'id');
			} else {
				$wechatgroup = pdo_fetchall("SELECT `templates` FROM " . tablename('uni_group') . " WHERE id IN ('".implode("','", $packageids)."') OR uniacid = '{$_W['uniacid']}'");
				$ms = array();
				$mssql = '';
				if (!empty($wechatgroup)) {
					foreach ($wechatgroup as $row) {
						$row['templates'] = iunserializer($row['templates']);
						if (!empty($row['templates'])) {
							foreach ($row['templates'] as $templateid) {
								$ms[$templateid] = $templateid;
							}
						}
					}
					$ms[] = 1;
					$mssql = " `id` IN ('".implode("','", $ms)."')";
				}
				$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') .(!empty($mssql) ? " WHERE $mssql" : '')." ORDER BY id DESC", array(), 'id');
			}
		}
	}
	if (empty($templates)) {
		$templates = pdo_fetchall("SELECT * FROM " . tablename('site_templates') . " WHERE id = 1 ORDER BY id DESC", array(), 'id');
	}
	return $templates;
}


function uni_setting_save($name, $value) {
	global $_W;
	if (empty($name)) {
		return false;
	}
	if (is_array($value)) {
		$value = serialize($value);
	}
	$unisetting = pdo_get('uni_settings', array('uniacid' => $_W['uniacid']), array('uniacid'));
	if (!empty($unisetting)) {
		pdo_update('uni_settings', array($name => $value), array('uniacid' => $_W['uniacid']));
	} else {
		pdo_insert('uni_settings', array($name => $value, 'uniacid' => $_W['uniacid']));
	}
	$cachekey = "unisetting:{$_W['uniacid']}";
	cache_delete($cachekey);
	return true;
}


function uni_setting_load($name = '', $uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : $uniacid;
	$cachekey = "unisetting:{$uniacid}";
	$unisetting = cache_load($cachekey);
	if (empty($unisetting)) {
		$unisetting = pdo_get('uni_settings', array('uniacid' => $uniacid));
		if (!empty($unisetting)) {
			$serialize = array('site_info', 'stat', 'oauth', 'passport', 'uc', 'notify', 
								'creditnames', 'default_message', 'creditbehaviors', 'shortcuts', 'payment', 
								'recharge', 'tplnotice', 'mcplugin');
			foreach ($unisetting as $key => &$row) {
				if (in_array($key, $serialize) && !empty($row)) {
					$row = (array)iunserializer($row);
				}
			}
		}
		cache_write($cachekey, $unisetting);
	}
	if (empty($unisetting)) {
		return array();
	}
	if (empty($name)) {
		return $unisetting;
	}
	if (!is_array($name)) {
		$name = array($name);
	}
	return array_elements($name, $unisetting);
}

if (!function_exists('uni_setting')) {
	function uni_setting($uniacid = 0, $fields = '*', $force_update = false) {
		global $_W;
		load()->model('account');
		if ($fields == '*') {
			$fields = '';
		}
		return uni_setting_load($fields, $uniacid);
	}
}


function uni_account_default($uniacid = 0) {
	global $_W;
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$account = pdo_fetch("SELECT w.*, a.default_acid FROM ".tablename('uni_account')." a LEFT JOIN ".tablename('account_wechats')." w ON a.default_acid = w.acid WHERE a.uniacid = :uniacid", array(':uniacid' => $uniacid), 'acid');
	if (empty($account['acid'])) {
		$default_acid = pdo_fetchcolumn("SELECT acid FROM ".tablename('account_wechats')." WHERE uniacid = :uniacid ORDER BY level DESC", array(':uniacid' => $_W['uniacid']));
		$account = pdo_fetch("SELECT w.* FROM " . tablename('uni_account') . " AS a, " . tablename('account_wechats') ." AS w WHERE w.acid = '{$default_acid}'");
	}
	$account['type'] = pdo_fetchcolumn("SELECT type FROM ".tablename('account')." WHERE acid = :acid", array(':acid' => $account['acid']));
	return $account;
}

function uni_user_permission_exist($uid = 0, $uniacid = 0) {
	global $_W;
	$uid = intval($uid) > 0 ? $uid : $_W['uid'];
	$uniacid = intval($uniacid) > 0 ? $uniacid : $_W['uniacid'];
	if($_W['role'] == 'founder' || $_W['role'] == 'manager') {
		return true;
	}
	$is_exist = pdo_fetch('SELECT id FROM ' . tablename('users_permission') . ' WHERE `uid`=:uid AND `uniacid`=:uniacid', array(':uid' => $uid, ':uniacid' => $uniacid));
	if(empty($is_exist)) {
		if($_W['role'] != 'clerk') {
			return true;
		} else {
			return error(-1, '');
		}
	} else {
		return error(-1, '');
	}
}

function uni_user_permission($type = 'system', $uid = 0, $uniacid = 0) {
	global $_W;
	$uid = empty($uid) ? $_W['uid'] : intval($uid);
	$uniacid = empty($uniacid) ? $_W['uniacid'] : intval($uniacid);
	$sql = 'SELECT `permission` FROM ' . tablename('users_permission') . ' WHERE `uid`=:uid AND `uniacid`=:uniacid AND `type`=:type';
	$pars = array();
	$pars[':uid'] = $uid;
	$pars[':uniacid'] = $uniacid;
	$pars[':type'] = $type;
	$data = pdo_fetchcolumn($sql, $pars);
	$permission = array();
	if(!empty($data)) {
		$permission = explode('|', $data);
	}
	return $permission;
}

function uni_user_permission_check($permission_name, $is_html = true, $action = '') {
	global $_W, $_GPC;
	$status = uni_user_permission_exist();
	if(!is_error($status)) {
		return true;
	}
	$m = trim($_GPC['m']);
	$do = trim($_GPC['do']);
	$eid = intval($_GPC['eid']);
	if($action == 'reply') {
		$system_modules = system_modules();
		if(!empty($m) && !in_array($m, $system_modules)) {
			$permission_name = $m . '_rule';
			$users_permission = uni_user_permission($m);
		}
	} elseif($action == 'cover' && $eid > 0) {
		$entry = pdo_fetch('SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `eid`=:eid', array(':eid' => $eid));
		if(!empty($entry)) {
			$permission_name = $entry['module'] . '_cover_' . trim($entry['do']);
			$users_permission = uni_user_permission($entry['module']);
		}
	} elseif($action == 'nav') {
				if(!empty($m)) {
			$permission_name = "{$m}_{$do}";
			$users_permission = uni_user_permission($m);
		} else {
			return true;
		}
	} else {
		$users_permission = uni_user_permission('system');
	}
	if(!isset($users_permission)) {
		$users_permission = uni_user_permission('system');
	}
	if($users_permission[0] != 'all' && !in_array($permission_name, $users_permission)) {
		if($is_html) {
			message('您没有进行该操作的权限', referer(), 'error');
		} else {
			return false;
		}
	}
	return true;
}


function uni_user_module_permission_check($action = '', $module_name = '') {
	global $_GPC;
	$status = uni_user_permission_exist();
	if(!is_error($status)) {
		return true;
	}
	$do = $_GPC['do'];
	$m = $_GPC['m'];
	if(!empty($do) && !empty($m)) {
		$is_exist = pdo_fetch('SELECT eid FROM ' . tablename('modules_bindings') . ' WHERE module=:module AND do = :do AND entry = :entry', array(':module' => $m, ':do' => $do, ':entry' => 'menu'));
		if(empty($is_exist)) {
			return true;
		}
	}
	if(empty($module_name)) {
		$module_name = IN_MODULE;
	}
	$permission = uni_user_permission($module_name);
	if(empty($permission) || ($permission[0] != 'all' && !empty($action) && !in_array($action, $permission))) {
		return false;
	}
	return true;
}

function uni_update_week_stat() {
	global $_W;
	$cachekey = "stat:todaylock:{$_W['uniacid']}";
	$cache = cache_load($cachekey);
	if(!empty($cache) && $cache['expire'] > TIMESTAMP) {
		return true;
	}
	$seven_days = array(
		date('Ymd', strtotime('-1 days')),
		date('Ymd', strtotime('-2 days')),
		date('Ymd', strtotime('-3 days')),
		date('Ymd', strtotime('-4 days')),
		date('Ymd', strtotime('-5 days')),
		date('Ymd', strtotime('-6 days')),
		date('Ymd', strtotime('-7 days')),
	);
	$week_stat_fans = pdo_getall('stat_fans', array('date' => $seven_days, 'uniacid' => $_W['uniacid']), '', 'date');
	$stat_update_yes = false;
	foreach ($seven_days as $sevens) {
		if (empty($week_stat_fans[$sevens]) || $week_stat_fans[$sevens]['cumulate'] <=0) {
			$stat_update_yes = true;
			break;
		}
	}
	if (empty($stat_update_yes)) {
		return true;
	}
	foreach($seven_days as $sevens) {
		if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY || $_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
			$account_obj = WeAccount::create();
			$weixin_stat = $account_obj->getFansStat();
			if(is_error($weixin_stat) || empty($weixin_stat)) {
				return error(-1, '调用微信接口错误');
			} else {
				$update_stat = array();
				$update_stat = array(
					'uniacid' => $_W['uniacid'],
					'new' => $weixin_stat[$sevens]['new'],
					'cancel' => $weixin_stat[$sevens]['cancel'],
					'cumulate' => $weixin_stat[$sevens]['cumulate'],
					'date' => $sevens,
				);
			}
		} else {
			$update_stat = array();
			$update_stat['cumulate'] = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('mc_mapping_fans') . " WHERE acid = :acid AND uniacid = :uniacid AND follow = :follow AND followtime < :endtime", array(':acid' => $_W['acid'], ':uniacid' => $_W['uniacid'], ':endtime' => strtotime($sevens)+86400, ':follow' => 1));
			$update_stat['date'] = $sevens;
			$update_stat['new'] = $week_stat_fans[$sevens]['new'];
			$update_stat['cancel'] = $week_stat_fans[$sevens]['cancel'];
			$update_stat['uniacid'] = $_W['uniacid'];
		}
		if(empty($week_stat_fans[$sevens])) {
			pdo_insert('stat_fans', $update_stat);
		} elseif (empty($week_stat_fans[$sevens]['cumulate']) || $week_stat_fans[$sevens]['cumulate'] < 0) {
			pdo_update('stat_fans', $update_stat, array('id' => $week_stat_fans[$sevens]['id']));
		}
	}
	cache_write($cachekey, array('expire' => TIMESTAMP + 7200));
	return true;
}


function account_types() {
	static $types;
	if (empty($types)) {
		$types = array();
		$types['wechat'] = array(
			'title' => '微信',
			'name' => 'wechat',
			'sn' => '1',
			'table' => 'account_wechats'
		);
	}
	return $types;
}


function account_create($uniacid, $account) {
	$accountdata = array('uniacid' => $uniacid, 'type' => $account['type'], 'hash' => random(8));
	pdo_insert('account', $accountdata);
	$acid = pdo_insertid();
	$account['acid'] = $acid;
	$account['token'] = random(32);
	$account['encodingaeskey'] = random(43);
	$account['uniacid'] = $uniacid;
	unset($account['type']);
	pdo_insert('account_wechats', $account);
	return $acid;
}


function account_fetch($acid) {
	$account = pdo_fetch("SELECT w.*, a.type, a.isconnect FROM " . tablename('account') . " a INNER JOIN " . tablename('account_wechats') . " w USING(acid) WHERE acid = :acid AND a.isdeleted = '0'", array(':acid' => $acid));
	if (empty($account)) {
		return error(1, '公众号不存在');
	}
	$uniacid = $account['uniacid'];
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	load()->model('user');
	$owner = user_single(array('uid' => $owneruid));
	$account['uid'] = $owner['uid'];
	$account['starttime'] = $owner['starttime'];
	$account['endtime'] = $owner['endtime'];
	load()->model('mc');
	$account['groups'] = mc_groups($uniacid);
	$account['grouplevel'] = pdo_fetchcolumn('SELECT grouplevel FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	return $account;
}


function account_weixin_login($username = '', $password = '', $imgcode = '') {
	global $_W, $_GPC;
	if (empty($username) || empty($password)) {
		$username = $_W['account']['username'];
		$password = $_W['account']['password'];
	}
	$auth['token'] = cache_load('wxauth:' . $username . ':token');
	load()->func('communication');
	$loginurl = WEIXIN_ROOT . '/cgi-bin/login?lang=zh_CN';
	$post = array(
		'username' => $username,
		'pwd' => $password,
		'imgcode' => $imgcode,
		'f' => 'json',
	);
		$code_cookie = $_GPC['code_cookie'];
	$response = ihttp_request($loginurl, $post, array('CURLOPT_REFERER' => 'https://mp.weixin.qq.com/', 'CURLOPT_COOKIE' => $code_cookie));
	if (is_error($response)) {
		return false;
	}

	$data = json_decode($response['content'], true);
	if ($data['base_resp']['ret'] == 0) {
		preg_match('/token=([0-9]+)/', $data['redirect_url'], $match);
		cache_write('wxauth:' . $username . ':token', $match[1]);
		cache_write('wxauth:' . $username . ':cookie', implode('; ', $response['headers']['Set-Cookie']));
		isetcookie('code_cookie', '', -1000);
	} else {
		return error(-1, $data['base_resp']['err_msg']);
	}
	return true;
}


function account_weixin_basic($username) {
	$response = account_weixin_http($username, WEIXIN_ROOT . '/cgi-bin/settingpage?t=setting/index&action=index&lang=zh_CN&f=json');
	if(is_error($response)) {
		return $response;
	}
	$result =  json_decode($response['content'], true);
	if ($result['base_resp']['ret'] != 0) {
		return error(-1, $result['base_resp']['err_msg']);
	}

	$fakeid = $result['user_info']['fake_id'];
	$image = account_weixin_http($username, WEIXIN_ROOT . '/misc/getheadimg?fakeid=' . $fakeid);
	if (!is_error($image) && !empty($image['content'])) {
		$info['headimg'] = $image['content'];
	}
	$image = account_weixin_http($username, WEIXIN_ROOT . '/misc/getqrcode?fakeid=' . $fakeid . '&style=1&action=download');
	if (!is_error($image) && !empty($image['content'])) {
		$info['qrcode'] = $image['content'];
	}
	$info['original'] = $result['setting_info']['original_username'];
	$info['name'] = $result['user_info']['nick_name'];
	$info['account'] = $result['user_info']['user_name'];
	$info['signature'] = $result['setting_info']['intro']['signature'];
	$info['level'] = 1;
	if($result['user_info']['service_type'] == 1) {
		$info['level'] = 1;
		if($result['user_info']['is_wx_verify'] == 1) {
			$info['level'] = 3;
		}
	} elseif($result['user_info']['service_type'] == 2) {
		$info['level'] = 2;
		if($result['user_info']['is_wx_verify'] == 1) {
			$info['level'] = 4;
		}
	}
	$response = account_weixin_http($username, WEIXIN_ROOT . '/advanced/advanced?action=dev&t=advanced/dev&lang=zh_CN&f=json');
	if(!is_error($response)) {
		$result =  json_decode($response['content'], true);
		$info['key'] = $result['advanced_info']['dev_info']['app_id'];
		$info['secret'] = '';
	}
	return $info;
}

function account_weixin_interface($username, $account) {
	global $_W;
	$response = account_weixin_http($username, WEIXIN_ROOT . '/advanced/callbackprofile?t=ajax-response&lang=zh_CN',
		array(
			'url' => $_W['siteroot'].'api.php?id='.$account['id'],
			'callback_token' => $account['token'],
			'encoding_aeskey' => $account['encodingaeskey'],
			'callback_encrypt_mode' => '0',
			'operation_seq' => '203038881',
	));
	if (is_error($response)) {
		return $response;
	}
	$response = json_decode($response['content'], true);
	if (!empty($response['base_resp']['ret'])) {
		return error($response['ret'], $response['msg']);
	}
	$response = account_weixin_http($username, WEIXIN_ROOT . '/misc/skeyform?form=advancedswitchform', array('f' => 'json', 'lang' => 'zh_CN', 'flag' => '1', 'type' => '2', 'ajax' => '1', 'random' => random(5, 1)));
	if (is_error($response)) {
		return $response;
	}
	return true;
}

function account_weixin_http($username, $url, $post = '') {
	global $_W;
	if (empty($_W['cache']['wxauth:'.$username.':token']) || empty($_W['cache']['wxauth:'.$username.':cookie'])) {
		cache_load('wxauth:'.$username.':token');
		cache_load('wxauth:'.$username.':cookie');
	}
	$auth = $_W['cache'];
	return ihttp_request($url . '&token=' . $auth['wxauth:'.$username.':token'], $post, array('CURLOPT_COOKIE' => $auth['wxauth:'.$username.':cookie'], 'CURLOPT_REFERER' => WEIXIN_ROOT . '/advanced/advanced?action=edit&t=advanced/edit&token='.$auth['wxauth:'.$username.':token']));
}

function account_weixin_userlist($pindex = 0, $psize = 1, &$total = 0) {
	global $_W;
	$url = WEIXIN_ROOT . '/cgi-bin/contactmanagepage?t=wxm-friend&lang=zh_CN&type=0&keyword=&groupid=0&pagesize='.$psize.'&pageidx='.$pindex;
	$response = account_weixin_http($_W['account']['username'], $url);
	$html = $response['content'];
	preg_match('/PageCount \: \'(\d+)\'/', $html, $match);
	$total = $match[1];
	preg_match_all('/"fakeId" : "([0-9]+?)"/', $html, $match);
	return $match[1];
}

function account_weixin_send($uid, $message = '') {
	global $_W;
	$username = $_W['account']['username'];
	if (empty($_W['cache']['wxauth'][$username])) {
		cache_load('wxauth:'.$username.':');
	}
	$auth = $_W['cache']['wxauth'][$username];
	$url = WEIXIN_ROOT . '/cgi-bin/singlesend?t=ajax-response&lang=zh_CN';
	$post = array(
		'ajax' => 1,
		'content' => $message,
		'error' => false,
		'tofakeid' => $uid,
		'token' => $auth['token'],
		'type' => 1,
	);
	$response = ihttp_request($url, $post, array(
		'CURLOPT_COOKIE' => $auth['cookie'],
		'CURLOPT_REFERER' => WEIXIN_ROOT . '/cgi-bin/singlemsgpage?token='.$auth['token'].'&fromfakeid='.$uid.'&msgid=&source=&count=20&t=wxm-singlechat&lang=zh_CN',
	));
}

function account_txweibo_login($username, $password, $verify = '') {
	$cookie = cache_load("txwall:$username");
	if (!empty($cookie)) {
		$response = ihttp_request('http://t.qq.com', '', array(
			'CURLOPT_COOKIE' => $cookie,
			'CURLOPT_REFERER' => 'http://t.qq.com/',
			"User-Agent" => "Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0",
		));
		if (!strexists($response['content'], '登录框')) {
			return $cookie;
		}
	}
	$loginsign = '';

	$loginui = 'http://ui.ptlogin2.qq.com/cgi-bin/login?appid=46000101&s_url=http%3A%2F%2Ft.qq.com';
	$response = ihttp_request($loginui);
	preg_match('/login_sig:"(.*?)"/', $response['content'], $match);
	$loginsign = $match[1];
	
	$checkloginurl = 'http://check.ptlogin2.qq.com/check?uin='.$username.'&appid=46000101&r='.TIMESTAMP;
	$response = ihttp_request($checkloginurl);
	$cookie = implode('; ', $response['headers']['Set-Cookie']);
	preg_match_all("/'(.*?)'/", $response['content'], $match);
	list($needVerify, $verify1, $verify2) = $match[1];
	if (!empty($needVerify)) {
		if (empty($verify)) {
			return error(1, '请输入验证码！');
		}
		$verify1 = $verify;
		$cookie .= '; ' . cache_load('txwall:verify');
	}
	$verify2 = pack('H*', str_replace('\x', '', $verify2));
	$temp = md5($password, true);
	$temp = strtoupper(md5($temp . $verify2));
	$password = strtoupper(md5($temp . strtoupper($verify1)));
	$loginurl = "http://ptlogin2.qq.com/login?u={$username}&p={$password}&verifycode={$verify1}&login_sig={$loginsign}&low_login_enable=1&low_login_hour=720&aid=46000101&u1=http%3A%2F%2Ft.qq.com&ptredirect=1&h=1&from_ui=1&dumy=&fp=loginerroralert&g=1&t=1&dummy=&daid=6&";
	$response = ihttp_request($loginurl, '', array(
		'CURLOPT_COOKIE' => $cookie,
		'CURLOPT_REFERER' => 'http://t.qq.com/',
		"User-Agent" => "Mozilla/5.0 (Windows NT 5.1; rv:13.0) Gecko/20100101 Firefox/13.0",
	));
	$info = explode("'", $response['content']);
	if ($info[1] != 0) {
		return error('1', $info[9]);
	}
	$response = ihttp_request($info[5]);
	$cookie = implode('; ', $response['headers']['Set-Cookie']);
	cache_write("txwall:$username", $cookie);
	return $cookie;
}


function uni_setmeal($uniacid = 0) {
	global $_W;
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$owneruid = pdo_fetchcolumn("SELECT uid FROM ".tablename('uni_account_users')." WHERE uniacid = :uniacid AND role = 'owner'", array(':uniacid' => $uniacid));
	if(empty($owneruid)) {
		$user = array(
			'uid' => -1,
			'username' => '创始人',
			'timelimit' => '未设置',
			'groupid' => '-1',
			'groupname' => '所有服务'
		);
		return $user;
	}
	load()->model('user');
	$groups = pdo_getall('users_group', array(), array('id', 'name'), 'id');
	$owner = user_single(array('uid' => $owneruid));
	$user = array(
		'uid' => $owner['uid'],
		'username' => $owner['username'],
		'groupid' => $owner['groupid'],
		'groupname' => $groups[$owner['groupid']]['name']
	);
	if(empty($owner['endtime'])) {
		$user['timelimit'] = date('Y-m-d', $owner['starttime']) . ' ~ 无限制' ;
	} else {
		if($owner['endtime'] <= TIMESTAMP) {
			$user['timelimit'] = ' <strong class="text-danger"> 已到期</strong>';
		} else {
			$year = 0;
			$month = 0;
			$day = 0;
			$endtime = $owner['endtime'];
			$time = strtotime('+1 year');
			while ($endtime > $time)
			{
				$year = $year + 1;
				$time = strtotime("+1 year", $time);
			};
			$time = strtotime("-1 year", $time);
			$time = strtotime("+1 month", $time);
			while($endtime > $time)
			{
				$month = $month + 1;
				$time = strtotime("+1 month", $time);
			} ;
			$time = strtotime("-1 month", $time);
			$time = strtotime("+1 day", $time);
			while($endtime > $time)
			{
				$day = $day + 1;
				$time = strtotime("+1 day", $time);
			} ;
			if (empty($year)) {
				$timelimit = empty($month)? $day.'天' : date('Y-m-d', $owner['starttime']) . '~'. date('Y-m-d', $owner['endtime']);
			}else {
				$timelimit = date('Y-m-d', $owner['starttime']) . '~'. date('Y-m-d', $owner['endtime']);
			}
			$user['timelimit'] = $timelimit;
		}
	}
	return $user;
}


function uni_is_multi_acid($uniacid = 0) {
	global $_W;
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$cachekey = "unicount:{$uniacid}";
	$nums = cache_load($cachekey);
	$nums = intval($nums);
	if(!$nums) {
		$nums = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('account_wechats') . ' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
		cache_write($cachekey, $nums);
	}
	if($nums == 1) {
		return false;
	}
	return true;
}

function account_delete($acid) {
	global $_W;
	load()->func('file');
		$account = pdo_get('uni_account', array('default_acid' => $acid));
	if ($account) {
		$uniacid = $account['uniacid'];
		$state = uni_permission($_W['uid'], $uniacid);
		if($state != 'founder' && $state != 'manager') {
			message('没有该公众号操作权限！', url('accound/display'), 'error');
		}
		if($uniacid == $_W['uniacid']) {
			isetcookie('__uniacid', '');
		}
		cache_delete("unicount:{$uniacid}");
		$modules = array();
				$rules = pdo_fetchall("SELECT id, module FROM ".tablename('rule')." WHERE uniacid = '{$uniacid}'");
		if (!empty($rules)) {
			foreach ($rules as $index => $rule) {
				$deleteid[] = $rule['id'];
			}
			pdo_delete('rule', "id IN ('".implode("','", $deleteid)."')");
		}

		$subaccount = pdo_fetchall("SELECT acid FROM ".tablename('account')." WHERE uniacid = :uniacid", array(':uniacid' => $uniacid));
		if (!empty($subaccount)) {
			foreach ($subaccount as $account) {
				@unlink(IA_ROOT . '/attachment/qrcode_'.$account['acid'].'.jpg');
				@unlink(IA_ROOT . '/attachment/headimg_'.$account['acid'].'.jpg');
				file_remote_delete('qrcode_'.$account['acid'].'.jpg');
				file_remote_delete('headimg_'.$account['acid'].'.jpg');
			}
			if (!empty($acid)) {
				rmdirs(IA_ROOT . '/attachment/images/' . $uniacid);
				@rmdir(IA_ROOT . '/attachment/images/' . $uniacid);
				rmdirs(IA_ROOT . '/attachment/audios/' . $uniacid);
				@rmdir(IA_ROOT . '/attachment/audios/' . $uniacid);
			}
		}

				$tables = array(
			'account','account_wechats', 'activity_coupon',
			'activity_coupon_allocation','activity_coupon_modules','activity_clerks',
			'activity_coupon_record','activity_exchange','activity_exchange_trades','activity_exchange_trades_shipping',
			'activity_modules', 'core_attachment','core_paylog','core_queue','core_resource',
			'wechat_attachment','coupon','coupon_modules',
			'coupon_record','coupon_setting','cover_reply', 'mc_card','mc_card_members','mc_chats_record','mc_credits_recharge','mc_credits_record',
			'mc_fans_groups','mc_groups','mc_handsel','mc_mapping_fans','mc_mapping_ucenter','mc_mass_record',
			'mc_member_address','mc_member_fields','mc_members','menu_event',
			'qrcode','qrcode_stat', 'rule','rule_keyword','site_article','site_category','site_multi','site_nav','site_slide',
			'site_styles','site_styles_vars','stat_keyword','stat_msg_history',
			'stat_rule','uni_account','uni_account_modules','uni_account_users','uni_settings', 'uni_group', 'uni_verifycode','users_permission',
			'mc_member_fields',
		);
		if (!empty($tables)) {
			foreach ($tables as $table) {
				$tablename = str_replace($GLOBALS['_W']['config']['db']['tablepre'], '', $table);
				pdo_delete($tablename, array( 'uniacid'=> $uniacid));
			}
		}
	} else {
		$account = account_fetch($acid);
		if (empty($account)) {
			message('子公众号不存在或是已经被删除');
		}
		$uniacid = $account['uniacid'];
		$state = uni_permission($_W['uid'], $uniacid);
		if($state != 'founder' && $state != 'manager') {
			message('没有该公众号操作权限！', url('accound/display'), 'error');
		}
		$uniaccount = uni_fetch($account['uniacid']);
		if ($uniaccount['default_acid'] == $acid) {
			message('默认子公众号不能删除');
		}
		pdo_delete('account', array('acid' => $acid));
		pdo_delete('account_wechats', array('acid' => $acid, 'uniacid' => $uniacid));
		cache_delete("unicount:{$uniacid}");
		cache_delete("unisetting:{$uniacid}");
		cache_delete('account:auth:refreshtoken:'.$acid);
		$oauth = uni_setting($uniacid, array('oauth'));
		if($oauth['oauth']['account'] == $acid) {
			$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . " WHERE uniacid = :id AND level = 4 AND secret != '' AND `key` != ''", array(':id' => $uniacid));
			pdo_update('uni_settings', array('oauth' => iserializer(array('account' => $acid, 'host' => $oauth['oauth']['host']))), array('uniacid' => $uniacid));
		}
		@unlink(IA_ROOT . '/attachment/qrcode_'.$acid.'.jpg');
		@unlink(IA_ROOT . '/attachment/headimg_'.$acid.'.jpg');
		file_remote_delete('qrcode_'.$acid.'.jpg');
		file_remote_delete('headimg_'.$acid.'.jpg');
	}
	return true;
}
