<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post', 'mine', 'use', 'deliver', 'confirm');
$do = in_array($_GPC['do'], $dos) ? $_GPC['do'] : 'display';
if($do == 'display') {
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '. tablename('activity_exchange'). ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime' , array(':uniacid' => $_W['uniacid'], ':type' => 5, ':endtime' => TIMESTAMP));
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$lists = pdo_fetchall('SELECT id,title,extra,thumb,type,credittype,endtime,description,credit FROM ' . tablename('activity_exchange') . ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime ORDER BY endtime ASC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid'], ':type' => 5, ':endtime' => TIMESTAMP));
	foreach($lists as &$li) {
		$li['extra'] = iunserializer($li['extra']);
	}
	$pager = pagination($total, $pindex, $psize);
}
if($do == 'post') {
	$id = intval($_GPC['id']); 
	$partime = activity_exchange_info($id, $_W['uniacid']);
	if(empty($partime)){
		message('没有指定的礼品兑换.');
	}
	$credit = mc_credit_fetch($_W['member']['uid'], array($partime['credittype']));
	if ($credit[$partime['credittype']] < $partime['credit']) {
		message('您的' . $creditnames[$partime['credittype']] . '数量不够,无法兑换.');
	}
	
	$ret = activity_module_grant($_W['member']['uid'], $id, 'system', '用户使用' . $partime['credit'] . $creditnames[$partime['credittype']] . '兑换');
	if(is_error($ret)) {
		message($ret['message']);
	}
	mc_credit_update($_W['member']['uid'], $partime['credittype'], -1 * $partime['credit'], array($_W['member']['uid'], '礼品兑换:' . $partime['title'] . ' 消耗 ' . $creditnames[$partime['credittype']] . ':' . $partime['credit']));
	message("兑换成功,您消费了 {$partime['credit']} {$creditnames[$partime['credittype']]}", url('activity/partimes/mine'));
}

if($do == 'mine') {
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$condition = '';
	if(empty($_GPC['status']) || $_GPC['status'] == 1) {
		$condition .= ' AND a.available > 0';
	} else {
		$condition .= ' AND a.available = 0';
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_modules') . ' AS a WHERE uid = :uid AND uniacid = :uniacid ' . $condition, array(':uid' => $_W['member']['uid'], 'uniacid' => $_W['uniacid']));
	$lists = pdo_fetchall('SELECT a.*, b.id AS gid,b.title,b.extra,b.thumb,b.type,b.credittype,b.endtime,b.description,b.credit FROM ' . tablename('activity_modules') . ' AS a LEFT JOIN ' . tablename('activity_exchange'). ' AS b ON a.exid = b.id WHERE a.uid = :uid ' . $condition . ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['member']['uid']));

	foreach($lists as &$list) {
		$list['extra'] = iunserializer($list['extra']);
	}
	$pager = pagination($total, $pindex, $psize);
}

if ($do == 'deliver') {
	$exid = intval($_GPC['exid']);
	$sql = 'SELECT * FROM ' . tablename('activity_exchange_trades_shipping') . ' WHERE uid = :uid AND exid = :exid';
	$ship = pdo_fetch($sql, array(':uid' => $_W['member']['uid'], ':exid' => $exid));
	if(checksubmit('submit')) {
		$data = array(
			'name'=>$_GPC['realname'],
			'mobile'=>$_GPC['mobile'],
			'province'=>$_GPC['reside']['province'],
			'city'=>$_GPC['reside']['city'],
			'district'=>$_GPC['reside']['district'],
			'address'=>$_GPC['address'],
			'zipcode'=>$_GPC['zipcode']
		);
		if (empty($ship)) {
			$data['uniacid'] = $_W['uniacid'];
			$data['exid'] = $exid;
			$data['uid'] = $_W['member']['uid'];
			$data['createtime'] = TIMESTAMP;
			pdo_insert('activity_exchange_trades_shipping', $data);
		} else {
			pdo_update('activity_exchange_trades_shipping', $data, array('exid' => $exid, 'uid' => $_W['member']['uid']));
		}
		message('收货人信息更新成功', url('activity/partimes/mine'));
	}
	if(empty($ship)) {
		$getInfo = array('uid','realname','resideprovince','residecity','residedist','address','zipcode','mobile');
		$ship = mc_fetch($_W['member']['uid'], $getInfo);
		$ship['name'] = $ship['realname'];
		$ship['province'] = $ship['resideprovince'];
		$ship['city'] = $ship['residecity'];
	} else {
		$ship['residedist'] = $ship['district'];
	}
	load()->func('tpl');
}
template('activity/partimes');
