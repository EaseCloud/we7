<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post');
$do = in_array($do, $dos) ? $do : 'display';
if ($do == 'display') {
	$pindex = max(1, $_GPC['page']);
	$psize = 15;
	$start = ($pindex - 1) * $psize;
	$tsql = "SELECT COUNT(*) FROM ". tablename('account'). " WHERE isdeleted = 1 GROUP BY uniacid" ;
	$total = pdo_fetchALL($tsql, array());
	$total = count($total);
	$sql = "SELECT * FROM ". tablename('account')." WHERE isdeleted = 1 GROUP BY uniacid LIMIT $start, $psize";
	$uni_accounts = pdo_fetchall($sql, array());
	$del_accounts = array();
	foreach ($uni_accounts as $account) {
		$uni_info = pdo_get('uni_account', array('uniacid' => $account['uniacid']));
		$del_accounts[$account['uniacid']] = $uni_info;
		$sql = "SELECT * FROM ". tablename('account'). " as a LEFT JOIN ". tablename('account_wechats'). " as w ON a.acid = w.acid WHERE a.isdeleted = '1' AND a.uniacid = :uniacid";
		$del_accounts[$account['uniacid']]['del_accounts'] = pdo_fetchall($sql, array(':uniacid' => $account['uniacid']), 'acid');
		$del_accounts[$account['uniacid']]['is_uniacid'] = in_array($uni_info['default_acid'], array_keys($del_accounts[$account['uniacid']]['del_accounts'])) ? 1 : 0;
	}
	$pager = pagination($total, $pindex, $psize);
}
if ($do == 'post') {
	load()->model('account');
	$acid = intval($_GPC['acid']);
	$uniacid = intval($_GPC['uniacid']);
	$op = trim($_GPC['op']);
	if ($op == 'recover') {
		if (!empty($uniacid)) {
			pdo_update('account', array('isdeleted' => 0), array('uniacid' => $uniacid));
		} else {
			pdo_update('account', array('isdeleted' => 0), array('acid' => $acid));
		}
		message('公众号恢复成功', referer(), 'success');
	}elseif ($op == 'delete') {
		if (!empty($acid)) {
			account_delete($acid);
		}
		message('删除公众号成功！', url('account/recycle/display'), 'success');
	}
}
template('account/recycle');