<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


class WeSession {
	
	public static $uniacid;
	
	public static $openid;
	
	public static $expire;

	
	public static function start($uniacid, $openid, $expire = 3600) {
		if (empty($GLOBALS['_W']['config']['setting']['memcache']['session']) || empty($GLOBALS['_W']['config']['setting']['memcache']['server'])) {
			WeSession::$uniacid = $uniacid;
			WeSession::$openid = $openid;
			WeSession::$expire = $expire;
			$sess = new WeSession();
			session_set_save_handler(
				array(&$sess, 'open'),
				array(&$sess, 'close'),
				array(&$sess, 'read'),
				array(&$sess, 'write'),
				array(&$sess, 'destroy'),
				array(&$sess, 'gc')
			);
			register_shutdown_function('session_write_close');
		}
		session_start();
	}

	public function open() {
		return true;
	}

	public function close() {
		return true;
	}

	
	public function read($sessionid) {
		$sql = 'SELECT * FROM ' . tablename('core_sessions') . ' WHERE `sid`=:sessid AND `expiretime`>:time';
		$params = array();
		$params[':sessid'] = $sessionid;
		$params[':time'] = TIMESTAMP;
		$row = pdo_fetch($sql, $params);
		if(is_array($row) && !empty($row['data'])) {
			return $row['data'];
		}
		return false;
	}

	
	public function write($sessionid, $data) {
		$row = array();
		$row['sid'] = $sessionid;
		$row['uniacid'] = WeSession::$uniacid;
		$row['openid'] = WeSession::$openid;
		$row['data'] = $data;
		$row['expiretime'] = TIMESTAMP + WeSession::$expire;

		return pdo_insert('core_sessions', $row, true) == 1;
	}

	
	public function destroy($sessionid) {
		$row = array();
		$row['sid'] = $sessionid;

		return pdo_delete('core_sessions', $row) == 1;
	}

	
	public function gc($expire) {
		$sql = 'DELETE FROM ' . tablename('core_sessions') . ' WHERE `expiretime`<:expire';

		return pdo_query($sql, array(':expire' => TIMESTAMP)) == 1;
	}
}