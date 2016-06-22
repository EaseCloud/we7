<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

if($_W['config']['setting']['cache'] == 'memcache') {
	if (extension_loaded('memcache')) {
		$config = $_W['config']['setting']['memcache'];
		if (!empty($config['server']) && !empty($config['port'])) {
			$memcacheobj = new Memcache();
			$connect = @$memcacheobj->connect($config['server'], $config['port'], 3);
			if (empty($memcacheobj) || empty($connect)) {
				$_W['config']['setting']['cache'] = 'mysql';
			}
		} else {
			$_W['config']['setting']['cache'] = 'mysql';
		}
	} else {
		$_W['config']['setting']['cache'] = 'mysql';
	}
}
load()->func('cache.' . $_W['config']['setting']['cache']);

function cache_load($key, $unserialize = false) {
	global $_W;
	if (!empty($_W['cache'][$key])) {
		return $_W['cache'][$key];
	}
	$data = $_W['cache'][$key] = cache_read($key);
	if ($key == 'setting') {
		$_W['setting'] = $data;
		return $_W['setting'];
	} elseif ($key == 'modules') {
		$_W['modules'] = $data;
		return $_W['modules'];
	} else {
		return $unserialize ? iunserializer($data) : $data;
	}
}


function &cache_global($key) {
	
}
