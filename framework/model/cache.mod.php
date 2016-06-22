<?php

/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
function cache_build_template() {
	load()->func('file');
	rmdirs(IA_ROOT . '/data/tpl', true);
}


function cache_build_setting() {
	$sql = "SELECT * FROM " . tablename('core_settings');
	$setting = pdo_fetchall($sql, array(), 'key');
	if (is_array($setting)) {
		foreach ($setting as $k => $v) {
			$setting[$v['key']] = iunserializer($v['value']);
		}
		cache_write("setting", $setting);
	}
}


function cache_build_account_modules() {
	$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
	foreach($uniacid_arr as $account){
		cache_delete("unimodules:{$account['uniacid']}:1");
		cache_delete("unimodules:{$account['uniacid']}:");
		cache_delete("unimodulesappbinding:{$account['uniacid']}");
	}
}

function cache_build_account() {
	global $_W;
	$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
	foreach($uniacid_arr as $account){
		cache_delete("uniaccount:{$account['uniacid']}");
		cache_delete("unisetting:{$account['uniacid']}");
		cache_delete("defaultgroupid:{$account['uniacid']}");
	}
}

function cache_build_accesstoken() {
	global $_W;
	$uniacid_arr = pdo_fetchall("SELECT acid FROM " . tablename('account_wechats'));
	foreach($uniacid_arr as $account){
		cache_delete("accesstoken:{$account['acid']}");
		cache_delete("jsticket:{$account['acid']}");
		cache_delete("cardticket:{$account['acid']}");
	}
}

function cache_build_users_struct() {
	$struct = array();
	$result = pdo_fetchall("SHOW COLUMNS FROM " . tablename('mc_members'));
	if (!empty($result)) {
		foreach ($result as $row) {
			$struct[] = $row['Field'];
		}
		cache_write('usersfields', $struct);
	}
	return $struct;
}

function cache_build_frame_menu() {
	$data = pdo_fetchall("SELECT * FROM " . tablename('core_menu') . " WHERE pid = 0 AND is_display = 1 ORDER BY is_system DESC, displayorder DESC, id ASC");
	$frames =array();
	if(!empty($data)) {
		foreach($data as $da) {
			$frames[$da['name']] = array();
			$childs = pdo_fetchall("SELECT * FROM " . tablename('core_menu') . " WHERE pid = :pid AND is_display = 1 ORDER BY is_system DESC, displayorder DESC, id ASC", array(':pid' => $da['id']));
			if(!empty($childs)) {
				foreach($childs as $child) {
					$temp = array();
					$temp['title'] = $child['title'];
					$grandchilds = pdo_fetchall("SELECT * FROM " . tablename('core_menu') . " WHERE pid = :pid AND is_display = 1 AND type = :type ORDER BY is_system DESC, displayorder DESC, id ASC", array(':pid' => $child['id'], ':type' => 'url'));
					if(!empty($grandchilds)) {
						foreach($grandchilds as $grandchild) {
							$item = array();
							$item['id'] = $grandchild['id'];
							$item['title'] = $grandchild['title'];
							$item['url'] = $grandchild['url'];
							$item['permission_name'] = $grandchild['permission_name'];
							if(!empty($grandchild['append_title'])) {
								$item['append']['title'] = '<i class="'.$grandchild['append_title'].'"></i>';
								$item['append']['url'] = $grandchild['append_url'];
							}
							$temp['items'][] = $item;
						}
					}
					$frames[$da['name']][] = $temp;
				}
			}
		}
	}
	cache_delete('system_frame');
	cache_write('system_frame', $frames);
}

function cache_build_module_subscribe_type() {
	global $_W;
	$modules = pdo_fetchall("SELECT name, subscribes FROM " . tablename('modules') . " WHERE subscribes <> ''");
	$subscribe = array();
	if (!empty($modules)) {
		foreach ($modules as $module) {
			$module['subscribes'] = unserialize($module['subscribes']);
			if (!empty($module['subscribes'])) {
				foreach ($module['subscribes'] as $event) {
					if ($event == 'text') {
						continue;
					}
					$subscribe[$event][] = $module['name'];
				}
			}
		}
	}
	$module_ban = $_W['setting']['module_receive_ban'];
	foreach ($subscribe as $event => $module_group) {
		if (!empty($module_group)) {
			foreach ($module_group as $index => $module) {
				if (!empty($module_ban[$module])) {
					unset($subscribe[$event][$index]);
				}
			}
		}
	}
	cache_write('module_receive_enable', $subscribe);
}

function cache_build_platform() {
	return pdo_query("DELETE FROM " . tablename('core_cache') . " WHERE `key` LIKE 'account%' AND `key` <> 'account:ticket';");
}


function cache_build_stat_fans() {
	global $_W;
	$uniacid_arr = pdo_fetchall("SELECT uniacid FROM " . tablename('uni_account'));
	foreach($uniacid_arr as $account){
		cache_delete("stat:todaylock:{$account['uniacid']}");
	}
}
