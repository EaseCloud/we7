<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

function cache_memcache() {
	global $_W;
	static $memcacheobj;
	if (!extension_loaded('memcache')) {
		return error(1, 'Class Memcache is not found');
	}
	if (empty($memcacheobj)) {
		$config = $_W['config']['setting']['memcache'];
		$memcacheobj = new Memcache();
		if($config['pconnect']) {
			$connect = $memcacheobj->pconnect($config['server'], $config['port']);
		} else {
			$connect = $memcacheobj->connect($config['server'], $config['port']);
		}
		if(!$connect) {
			return error(-1, 'Memcache is not in work');
		}
	}
	return $memcacheobj;
}


function cache_read($key) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	$result = $memcache->get(cache_prefix($key));
	if (empty($result)) {
		$dbcache = pdo_get('core_cache', array('key' => $key), array('value'));
		if (!empty($dbcache['value'])) {
			$result = iunserializer($dbcache['value']);
			$memcache->set(cache_prefix($key), $result);
		}
	}
	return $result;
}


function cache_search($key) {
	return cache_read(cache_prefix($key));
}


function cache_write($key, $value, $ttl = 0) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	$record = array();
	$record['key'] = $key;
	$record['value'] = iserializer($value);
	pdo_insert('core_cache', $record, true);
	
	if ($memcache->set(cache_prefix($key), $value, MEMCACHE_COMPRESSED, $ttl)) {
		return true;
	} else {
		return false;
	}
}


function cache_delete($key) {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	if ($memcache->delete(cache_prefix($key))) {
		pdo_delete('core_cache', array('key' => $key));
		return true;
	} else {
		pdo_delete('core_cache', array('key' => $key));
		return false;
	}
}



function cache_clean($prefix = '') {
	$memcache = cache_memcache();
	if (is_error($memcache)) {
		return $memcache;
	}
	if ($memcache->flush()) {
		unset($_W['cache']);
		pdo_delete('core_cache');
		return true;
	} else {
		return false;
	}
}

function cache_prefix($key) {
	return $GLOBALS['_W']['config']['setting']['authkey'] . $key;
}
