<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->func('file');
define('CACHE_FILE_PATH', IA_ROOT . '/data/cache/');

function cache_read($key, $dir = '', $include = true) {
	$key = str_replace(':', '@', $key);
	$key = CACHE_FILE_PATH . $key;
	if (!is_file($key)) {
		return array();
	}
	return $include ? include $key : file_get_contents($key);
}

function cache_write($key, $data, $dir = '') {
	global $_W;
	if (empty($key) || !isset($data)) {
		return false;
	}
	$key = str_replace(':', '@', $key);
	if (!is_string($data)) {
		$data = "<?php \r\ndefined('IN_IA') or exit('Access Denied');\r\nreturn " . var_export($data, true) . ';';
	}
	$key = CACHE_FILE_PATH . $key;
	mkdirs(dirname($key));
	file_put_contents($key, $data);
	@chmod($key, $_W['config']['setting']['filemode']);
	return is_file($key);
}

function cache_delete($key, $dir = '') {
	$key = str_replace(':', '@', $key);
	$key = CACHE_FILE_PATH . $key;
	return file_delete($key);
}


function cache_clean($dir = '') {
	return rmdirs(CACHE_FILE_PATH, true);
}
