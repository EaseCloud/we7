<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post', 'mine', 'use', 'deliver', 'confirm');
$do = in_array($_GPC['do'], $dos) ? $_GPC['do'] : 'display';
if($do == 'display') {
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '. tablename('activity_exchange'). ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime' , array(':uniacid' => $_W['uniacid'], ':type' => 3, ':endtime' => TIMESTAMP));
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$lists = pdo_fetchall('SELECT id,title,extra,thumb,type,credittype,endtime,description,credit FROM ' . tablename('activity_exchange') . ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime ORDER BY endtime ASC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid'], ':type' => 3, ':endtime' => TIMESTAMP));
	foreach($lists as &$li) {
		$li['extra'] = iunserializer($li['extra']);
		if(!is_array($li['extra'])) {
			$li['extra'] = array();
		}
	}
	$pager = pagination($total, $pindex, $psize);
}
if($do == 'post') {
	$id = intval($_GPC['id']); 
	$goods = activity_exchange_info($id, $_W['uniacid']);
	if(empty($goods)){
		message(error(-1, '没有指定的礼品兑换'), '', 'ajax');
	}
	$credit = mc_credit_fetch($_W['member']['uid'], array($goods['credittype']));
	if ($credit[$goods['credittype']] < $goods['credit']) {
		message(error(-1, "您的 {$creditnames[$token['credittype']]} 数量不够,无法兑换."), '', 'ajax');
	}
	
	$ret = activity_goods_grant($_W['member']['uid'], $id, 'system', '用户使用' . $goods['credit'] . $creditnames[$goods['credittype']] . '兑换');
	if(is_error($ret)) {
		message($ret, '', 'ajax');
	}
	mc_credit_update($_W['member']['uid'], $goods['credittype'], -1 * $goods['credit'], array($_W['member']['uid'], '礼品兑换:' . $goods['title'] . ' 消耗 ' . $creditnames[$goods['credittype']] . ':' . $goods['credit']));
		if($goods['credittype'] == 'credit1') {
		mc_notice_credit1($_W['openid'], $_W['member']['uid'], -1 * $goods['credit'], '兑换礼品消耗积分');
	} else {
		mc_notice_credit2($_W['openid'], $_W['member']['uid'], -1 * $goods['credit'], 0, '线上消费，兑换礼品');
	}
	message(error($ret, "兑换成功,您消费了 {$goods['credit']} {$creditnames[$goods['credittype']]},现在去完善订单信息"), '', 'ajax');
}
if($do == 'deliver') {
	load()->func('tpl');
	$tid = intval($_GPC['tid']);
	$ship = pdo_fetch('SELECT * FROM ' . tablename('activity_exchange_trades_shipping') . ' WHERE uid = :uid AND tid = :tid', array(':uid' => $_W['member']['uid'], ':tid' => $tid));
	if(empty($ship)) {
		message('没有找到该兑换的收货人信息', '', 'error');
	}
	$member = mc_fetch($_W['member']['uid'], array('uid','realname','resideprovince','residecity','residedist','address','zipcode','mobile'));
	$ship['name'] = !empty($ship['name']) ? $ship['name'] : $member['realname'];
	$ship['province'] = !empty($ship['province']) ? $ship['province'] : $member['resideprovince'];
	$ship['city'] = !empty($ship['city']) ? $ship['city'] : $member['residecity'];
	$ship['district'] = !empty($ship['district']) ? $ship['district'] : $member['residedist'];
	$ship['address'] = !empty($ship['address']) ? $ship['address'] : $member['address'];
	$ship['zipcode'] = !empty($ship['zipcode']) ? $ship['zipcode'] : $member['zipcode'];
	$ship['mobile'] = !empty($ship['mobile']) ? $ship['mobile'] : $member['mobile'];
	if(checksubmit('submit')) {
		$data = array(
			'name'=>$_GPC['realname'],
			'mobile'=>$_GPC['mobile'],
			'province'=>$_GPC['reside']['province'],
			'city'=>$_GPC['reside']['city'],
			'district'=>$_GPC['reside']['district'],
			'address'=>$_GPC['address'],
			'zipcode'=>$_GPC['zipcode'],
		);
		pdo_update('activity_exchange_trades_shipping', $data, array('tid' => $tid, 'uid' => $_W['member']['uid']));
		message('收货人信息更新成功', url('activity/goods/mine'));
	}
}
if($do == 'mine') {
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_exchange_trades_shipping') . ' WHERE uid = :uid AND status = :status', array(':uid' => $_W['member']['uid'], ':status' => intval($_GPC['status']))); 
	$lists = pdo_fetchall('SELECT a.*, b.id AS gid,b.title,b.extra,b.thumb,b.type,b.credittype,b.endtime,b.description,b.credit FROM ' . tablename('activity_exchange_trades_shipping') . ' AS a LEFT JOIN ' . tablename('activity_exchange'). ' AS b ON a.exid = b.id WHERE a.uid = :uid AND a.status = :status LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':uid' => $_W['member']['uid'], ':status' => intval($_GPC['status'])));
	
	foreach($lists as &$list) {
		$list['extra'] = iunserializer($list['extra']);
		if(!is_array($list['extra'])) {
			$list['extra'] = array();
		}
	}	
	$pager = pagination($total, $pindex, $psize);
}
if($do == 'confirm') {
	$tid = intval($_GPC['tid']);	$ship = pdo_fetch('SELECT tid FROM ' . tablename('activity_exchange_trades_shipping') . ' WHERE tid = :tid AND uid = :uid', array(':tid' => $tid, ':uid' => $_W['member']['uid']));
	if(empty($ship)) {
		message('没有找到订单信息', '', 'error');
	}
	pdo_update('activity_exchange_trades_shipping', array('status' => 2), array('uid' => $_W['member']['uid'], 'tid' => $tid));
	message('确认收货成功', url('activity/goods/mine', array('status' => 2)), 'success');
}

template('activity/goods');
