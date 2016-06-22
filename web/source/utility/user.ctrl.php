<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$do = !empty($_GPC['do']) ? $_GPC['do'] : exit('Access Denied');
$dos = array('browser');
$do = in_array($do, $dos) ? $do: 'browser';

if ($do == 'browser') {
	
	$mode = empty($_GPC['mode']) ? 'visible' : $_GPC['mode'];
	$mode = in_array($mode, array('invisible','visible')) ? $mode : 'visible';
	
	$callback = $_GPC['callback'];
	
	$uids = $_GPC['uids'];
	$uidArr = array();
	if(empty($uids)){
		$uids='';
	}else{
		foreach (explode(',', $uids) as $uid) {
			$uidArr[] = intval($uid);
		}
		$uids = implode(',', $uidArr);
	}
	$where = " WHERE status = '2' and type != '".ACCOUNT_OPERATE_CLERK."'";
	if($mode == 'invisible' && !empty($uids)){
		$where .= " AND uid not in ( {$uids} )";
	}
	$params = array();
	if(!empty($_GPC['keyword'])) {
		$where .= ' AND `username` LIKE :username';
		$params[':username'] = "%{$_GPC['keyword']}%";
	}
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$total = 0;

	$list = pdo_fetchall("SELECT uid, groupid, username, remark FROM ".tablename('users')." {$where} ORDER BY `uid` LIMIT ".(($pindex - 1) * $psize).",{$psize}", $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('users'). $where , $params);
	$pager = pagination($total, $pindex, $psize, '', array('ajaxcallback'=>'null','mode'=>$mode,'uids'=>$uids));
	$usergroups = pdo_fetchall('SELECT id, name FROM '.tablename('users_group'), array(), 'id');
	template('utility/user-browser');
	exit;
}
