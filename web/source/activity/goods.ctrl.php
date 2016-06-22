<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('activity_goods_display');
$dos = array('display', 'post', 'del', 'record', 'deliver', 'receiver', 'record-del');
$do = in_array($do, $dos) ? $do : 'display';


$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames'));
foreach ($unisettings['creditnames'] as $key=>$credit) {
	if (!empty($credit['enabled'])) {
		$creditnames[$key] = $credit['title'];
	}
}
if($do == 'post') {
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$item = pdo_fetch('SELECT * FROM '.tablename('activity_exchange').' WHERE id=:id AND uniacid=:uniacid',array(':id'=>$id, ':uniacid'=>$_W['uniacid']));
		if(empty($item)) {
			message('未找到指定兑换礼品或已删除.',url('activity/goods'),'error');
		} else {
			$item['extra'] = iunserializer($item['extra']);
		}
	} else {
		$item['starttime'] = TIMESTAMP;
		$item['endtime'] = TIMESTAMP + 6 * 86400;
	}
	
	if(checksubmit('submit')) {
		$data['title'] = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请输入兑换名称！');
		$data['credittype'] = !empty($_GPC['credittype']) ? trim($_GPC['credittype']) : message('请选择积分类型！');
		$data['credit'] = intval($_GPC['credit']);
		if(empty($_GPC['extra']['title'])) {
			message('请输入兑换礼品的名称');
		}
		$data['extra'] = iserializer($_GPC['extra']);
		$data['thumb'] = trim($_GPC['thumb']);
		$data['pretotal'] = intval($_GPC['pretotal']) ? intval($_GPC['pretotal']) : message('请输入每人最大兑换次数');
		$data['total'] = intval($_GPC['total']) ? intval($_GPC['total']) : message('请输入兑换总数');
		$data['type'] = 3;
		$data['description'] = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请输入兑换说明！');
		
		$starttime = strtotime($_GPC['datelimit']['start']);
		$endtime = strtotime($_GPC['datelimit']['end']);
		if ($endtime == $starttime) {
			$endtime = $endtime + 86399;
		}
		$data['starttime'] = $starttime;
		$data['endtime'] = $endtime;
		if(empty($id)) {
			$data['uniacid'] = $_W['uniacid'];
			pdo_insert('activity_exchange', $data);
			message('添加真实物品兑换成功',url('activity/goods', array()),'success');
		} else {
			pdo_update('activity_exchange', $data, array('id' => $id, 'uniacid'=>$_W['uniacid']));
			message('更新真实物品兑换成功',url('activity/goods', array()),'success');
		}
	}
}
if($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$where = ' WHERE type = 3 AND uniacid = :uniacid ';
	$params = array(':uniacid' => $_W['uniacid']);
	$title = trim($_GPC['keyword']);
	if (!empty($title)) {
		$where .= " AND title LIKE '%{$title}%'";
	}
	
	$list = pdo_fetchall('SELECT * FROM '.tablename('activity_exchange')." $where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_exchange'). $where , $params);
	$pager = pagination($total, $pindex, $psize);
	foreach ($list as &$row) {
		$extra = iunserializer($row['extra']);
		$row['extra'] = $extra;
		$row['thumb'] = tomedia($row['thumb']);
	}
}
if($do == 'del') {
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$item = pdo_fetch('SELECT id FROM '.tablename('activity_exchange').' WHERE id=:id AND uniacid=:uniacid',array(':id'=>$id, ':uniacid'=>$_W['uniacid']));
	}
	if(empty($item)) {
		message('删除失败,指定兑换不存在或已删除.');
	}
	pdo_delete('activity_exchange', array('id'=>$id, 'uniacid'=>$_W['uniacid']));
	message('删除成功.', referer(),'success');
}
if($do == 'record') {
	$exchanges = pdo_fetchall('SELECT id, title FROM ' . tablename('activity_exchange') . ' WHERE uniacid = :uniacid ORDER BY id DESC', array(':uniacid' => $_W['uniacid']));
	$starttime = empty($_GPC['time']['start']) ? strtotime('-1 month') : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
	
	$where = " WHERE a.uniacid=:uniacid AND a.type = 3 AND a.createtime>=:starttime AND a.createtime<:endtime";
	$params = array(
		':uniacid' => $_W['uniacid'],
		':starttime' => $starttime,
		':endtime' => $endtime,
	);
	$uid = intval($_GPC['uid']);
	if (!empty($uid)) {
		$where .= ' AND a.uid=:uid';
		$params[':uid'] = $uid;
	}
	$exid = intval($_GPC['exid']);
	if (!empty($exid)) {
		$where .= " AND b.id = {$exid}";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	
	$list = pdo_fetchall("SELECT a.*, b.title,b.extra,b.thumb FROM ".tablename('activity_exchange_trades'). ' AS a LEFT JOIN ' . tablename('activity_exchange') . ' AS b ON a.exid = b.id ' . " $where ORDER BY tid DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_exchange_trades') . ' AS a LEFT JOIN ' . tablename('activity_exchange') . ' AS b ON a.exid = b.id '. $where , $params);
	$pager = pagination($total, $pindex, $psize);
	if(!empty($list)) {
		$uids = array();
		foreach ($list as $row) {
			$uids[] = $row['uid'];
		}	
		load()->model('mc');
		$members = mc_fetch($uids, array('uid', 'nickname'));
		foreach ($list as &$row) {
			$row['extra'] = iunserializer($row['extra']);
			$row['nickname'] = $members[$row['uid']]['nickname'];
			$row['thumb'] = tomedia($row['thumb']);
		}
	}
}
if($do == 'deliver') {
	$exchanges = pdo_fetchall('SELECT id, title FROM ' . tablename('activity_exchange') . ' WHERE uniacid = :uniacid ORDER BY id DESC', array(':uniacid' => $_W['uniacid']));
	$starttime = empty($_GPC['time']['start']) ? strtotime('-1 month') : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
	
	$where = " WHERE a.uniacid=:uniacid AND a.createtime>=:starttime AND a.createtime<:endtime";
	$params = array(
			':uniacid' => $_W['uniacid'],
			':starttime' => $starttime,
			':endtime' => $endtime,
	);
	$uid = intval($_GPC['uid']);
	if (!empty($uid)) {
		$where .= ' AND a.uid=:uid';
		$params[':uid'] = $uid;
	}
	$exid = intval($_GPC['exid']);
	if (!empty($exid)) {
		$where .= " AND b.id = {$exid}";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	
	$list = pdo_fetchall("SELECT a.*, b.title,b.extra,b.thumb FROM ".tablename('activity_exchange_trades_shipping'). ' AS a LEFT JOIN ' . tablename('activity_exchange') . ' AS b ON a.exid = b.id ' . " $where ORDER BY tid DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_exchange_trades_shipping') . ' AS a LEFT JOIN ' . tablename('activity_exchange') . ' AS b ON a.exid = b.id '. $where , $params);
	if(!empty($list)) {
		$uids = array();
		foreach ($list as $row) {
			$uids[] = $row['uid'];
		}
		load()->model('mc');
		$members = mc_fetch($uids, array('uid', 'nickname'));
		foreach ($list as &$row) {
			$row['extra'] = iunserializer($row['extra']);
			$row['nickname'] = $members[$row['uid']]['nickname'];
			$row['thumb'] = tomedia($row['thumb']);
		}
	}
	
	$pager = pagination($total, $pindex, $psize);
} 
if($do == 'receiver') {
	$id = intval($_GPC['id']);
	$shipping = pdo_fetch('SELECT * FROM ' . tablename('activity_exchange_trades_shipping') . ' WHERE uniacid = :uniacid AND tid = :tid', array(':uniacid' => $_W['uniacid'], ':tid' => $id) );
	if(checksubmit('submit')) {
		$data = array(
			'name'=>$_GPC['realname'],
			'mobile'=>$_GPC['mobile'],
			'province'=>$_GPC['reside']['province'],
			'city'=>$_GPC['reside']['city'],
			'district'=>$_GPC['reside']['district'],
			'address'=>$_GPC['address'],
			'zipcode'=>$_GPC['zipcode'],
			'status'=>intval($_GPC['status'])	
		);
		pdo_update('activity_exchange_trades_shipping', $data, array('tid' => $id));
		message('更新发货人信息成功', referer(), 'success');
	}
}
if($do == 'record-del') {
	$tid = intval($_GPC['id']);
	if(empty($tid)) {
		message('没有指定的兑换记录', url('activity/goods/record'), 'error');
	}
	pdo_delete('activity_exchange_trades_shipping', array('uniacid' => $_W['uniacid'], 'tid' => $tid));
	pdo_delete('activity_exchange_trades', array('uniacid' => $_W['uniacid'], 'tid' => $tid));
	message('删除兑换记录成功', url('activity/goods/record'), 'success');
}
template('activity/goods');