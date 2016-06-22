<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

if (isset($_W['uniacid'])) {
	$_W['weid'] = $_W['uniacid'];
}
if (isset($_W['openid'])) {
	$_W['fans']['from_user'] = $_W['openid'];
}
if (isset($_W['member']['uid'])) {
	if (empty($_W['fans']['from_user'])) {
		$_W['fans']['from_user'] = $_W['member']['uid'];
	}
}


if (!function_exists('fans_search')) {
	function fans_search($user, $fields = array()) {
		global $_W;
		load()->model('mc');
		$uid = intval($user);
		if(empty($uid)) {
			$uid = pdo_fetchcolumn("SELECT uid FROM ".tablename('mc_mapping_fans')." WHERE openid = :openid AND acid = :acid", array(':openid' => $user, ':acid' => $_W['acid']));
			if (empty($uid)) {
				return array(); 			}
		}
		return mc_fetch($uid, $fields);
	}
}

if (!function_exists('fans_fields')) {
	function fans_fields() {
		load()->model('mc');
		return mc_fields();
	}
}

if(!function_exists('fans_update')) {
	function fans_update($user, $fields) {
		global $_W;
		load()->model('mc');
		$uid = intval($user);
		if(empty($uid)) {
			$uid = pdo_fetchcolumn("SELECT uid FROM ".tablename('mc_mapping_fans')." WHERE openid = :openid AND acid = :acid", array(':openid' => $user, ':acid' => $_W['acid']));
			if (empty($uid)) {
				return false; 			}
		}
		return mc_update($uid, $fields);
	}
}

if (!function_exists('create_url')) {
	function create_url($segment = '', $params = array(), $noredirect = false) {
		return url($segment, $params, $noredirect);
	}
}

if (!function_exists('toimage')) {
	function toimage($src) {
		return tomedia($src);
	}
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
