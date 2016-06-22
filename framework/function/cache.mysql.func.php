<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function cache_read($key) {
	$sql = 'SELECT `value` FROM ' . tablename('core_cache') . ' WHERE `key`=:key';
	$params = array();
	$params[':key'] = $key;
	$val = pdo_fetchcolumn($sql, $params);
	return iunserializer($val);
}


function cache_search($prefix) {
	$sql = 'SELECT * FROM ' . tablename('core_cache') . ' WHERE `key` LIKE :key';
	$params = array();
	$params[':key'] = "{$prefix}%";
	$rs = pdo_fetchall($sql, $params);
	$result = array();
	foreach ((array)$rs as $v) {
		$result[$v['key']] = iunserializer($v['value']);
	}
	return $result;
}


function cache_write($key, $data) {
	if (empty($key) || !isset($data)) {
		return false;
	}
	$record = array();
	$record['key'] = $key;
	$record['value'] = iserializer($data);
	return pdo_insert('core_cache', $record, true);
}


function cache_delete($key) {
	$sql = 'DELETE FROM ' . tablename('core_cache') . ' WHERE `key`=:key';
	$params = array();
	$params[':key'] = $key;
	$result = pdo_query($sql, $params);
	return $result;
}


function cache_clean($prefix = '') {
	global $_W;
	if (empty($prefix)) {
		$sql = 'DELETE FROM ' . tablename('core_cache');
		$result = pdo_query($sql);
		if ($result) {
			unset($_W['cache']);
		}
	} else {
		$sql = 'DELETE FROM ' . tablename('core_cache') . ' WHERE `key` LIKE :key';
		$params = array();
		$params[':key'] = "{$prefix}:%";
		$result = pdo_query($sql, $params);
	}
	return $result;
}
