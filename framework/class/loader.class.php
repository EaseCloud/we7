<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function load() {
	static $loader;
	if(empty($loader)) {
		$loader = new Loader();
	}
	return $loader;
}


class Loader {
	
	private $cache = array();
	
	function func($name) {
		global $_W;
		if (isset($this->cache['func'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/function/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['func'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Helper Function /framework/function/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function model($name) {
		global $_W;
		if (isset($this->cache['model'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/model/' . $name . '.mod.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['model'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Model /framework/model/' . $name . '.mod.php', E_USER_ERROR);
			return false;
		}
	}
	
	function classs($name) {
		global $_W;
		if (isset($this->cache['class'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/framework/class/' . $name . '.class.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['class'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Class /framework/class/' . $name . '.class.php', E_USER_ERROR);
			return false;
		}
	}
	
	function web($name) {
		global $_W;
		if (isset($this->cache['web'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/web/common/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['web'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid Web Helper /web/common/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
	function app($name) {
		global $_W;
		if (isset($this->cache['app'][$name])) {
			return true;
		}
		$file = IA_ROOT . '/app/common/' . $name . '.func.php';
		if (file_exists($file)) {
			include $file;
			$this->cache['app'][$name] = true;
			return true;
		} else {
			trigger_error('Invalid App Function /app/common/' . $name . '.func.php', E_USER_ERROR);
			return false;
		}
	}
	
}
