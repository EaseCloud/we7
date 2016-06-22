<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
set_time_limit(0);
load()->model('cloud');
load()->func('communication');
load()->model('extension');
$r = cloud_prepare();
if(is_error($r)) {
	message($r['message'], url('cloud/profile'), 'error');
}

$do = !empty($_GPC['do']) && in_array($do, array('module', 'system')) ? $_GPC['do'] : exit('Access Denied');
if ($do == 'system') {
	$lock = cache_load('checkupgrade:system');
	if (empty($lock) || (TIMESTAMP - 3600 > $lock['lastupdate'])) {
		$upgrade = cloud_build();
		if(!is_error($upgrade) && !empty($upgrade['upgrade'])) {
			$upgrade = array('version' => $upgrade['version'], 'release' => $upgrade['release'], 'upgrade' => 1, 'lastupdate' => TIMESTAMP);
			cache_write('checkupgrade:system', $upgrade);
			cache_delete('cloud:transtoken');
			message($upgrade, '', 'ajax');
		} else {
			$upgrade = array('lastupdate' => TIMESTAMP);
			cache_delete('cloud:transtoken');
			cache_write('checkupgrade:system', $upgrade);
		}
	} else {
		message($lock, '', 'ajax');
	}
} elseif ($do == 'module') {
	$modulename = $_GPC['m'];
	$module = pdo_fetch("SELECT mid, name, version, title FROM " . tablename('modules') . " WHERE name = :name", array(':name' => $modulename));
	if (!empty($module)) {
		$info = cloud_m_info($modulename);
		$manifest = ext_module_manifest($modulename);

		if (is_error($info) && $info['errno'] == -10) {
			message($info, '', 'ajax');
		}
		if (!empty($info) && !empty($info['version']['version'])) {
			if (ver_compare($module['version'], $info['version']['version'])) {
				$upgrade = array('name' => $module['title'], 'version' => $info['version']['version'], 'upgrade' => 1, 'lastupdate' => TIMESTAMP);
				message($upgrade, '', 'ajax');
			}
		} else {
			if (!empty($manifest)) {
				if (ver_compare($module['version'], $manifest['application']['version'])) {
					$upgrade = array('name' => $module['title'], 'version' => $manifest['application']['version'], 'upgrade' => 1, 'lastupdate' => TIMESTAMP);
					message($upgrade, '', 'ajax');
				}
			}
		}
	}
}