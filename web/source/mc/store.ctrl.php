<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_business');
$dos = array('display', 'post','delete');
$do = in_array($do, $dos) ? $do : 'display';
$_W['page']['title'] = '商家设置-粉丝营销';

if($do == 'post') {
	$id = intval($_GPC['id']);
	if($id > 0) {
		$sql = 'SELECT * FROM '.tablename('activity_stores').' WHERE id = :id AND uniacid = :uniacid';
		$item = pdo_fetch($sql, array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if(empty($item)) {
			message('商家不存在',referer(),'info');
		}
		$item['category'] = iunserializer($item['category']);
		$item['photo_list'] = iunserializer($item['photo_list']);
		$item['opentime'] = explode('-', $item['opentime']);
		$item['open_time_start'] = $item['opentime'][0];
		$item['open_time_end'] = $item['opentime'][1];
	}else {
		$item['open_time_start'] = '8:00';
		$item['open_time_end'] = '24:00';
	}
	if(checksubmit('submit')) {
		$insert = array();
		$insert['uniacid'] = intval($_W['uniacid']);
		$insert['business_name'] = trim($_GPC['business_name']);
		$insert['branch_name'] = trim($_GPC['branch_name']);
		$insert['category'] = iserializer(array(
				'cate' => trim($_GPC['class']['cate']),
				'sub' => trim($_GPC['class']['sub']),
				'clas' => trim($_GPC['class']['clas'])
			));
		$insert['province'] = trim($_GPC['reside']['province']);
		$insert['city'] = trim($_GPC['reside']['city']);
		$insert['district'] = trim($_GPC['reside']['district']);
		$insert['address'] = trim($_GPC['address']);
		$insert['longitude'] = trim($_GPC['baidumap']['lng']);
		$insert['latitude'] = trim($_GPC['baidumap']['lat']);
		$insert['telephone'] = trim($_GPC['telephone']);
		$insert['photo_list'] = iserializer($_GPC['photo_list']);
		$insert['avg_price'] = intval($_GPC['avg_price']);
		$insert['opentime'] = trim($_GPC['open_time_start']). '-'.trim($_GPC['open_time_end']);
		$insert['recommend'] = trim($_GPC['recommend']);
		$insert['special'] = trim($_GPC['special']);
		$insert['introduction'] = trim($_GPC['introduction']);
		if($id > 0) {
			pdo_update('activity_stores',$insert,array('id' => $id, 'uniacid' => $_W['uniacid']));
			message('更新商家成功',url('mc/business/display'),'success');
		}else {
			pdo_insert('activity_stores', $insert);
			message('添加门店成功', url('mc/business/display'), 'success');
		}
	}
}
if($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$limit = 'ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('activity_stores').' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
	$list = pdo_fetchall('SELECT * FROM '.tablename('activity_stores'). " WHERE uniacid = :uniacid {$limit}", array(':uniacid' => $_W['uniacid']));
	$pager = pagination($total,$pindex,$psize);
	foreach($list as &$key) {
		$key['category'] = iunserializer($key['category']);
		$key['category'] = implode('-', $key['category']);
	}
}
if($do =='delete') {
	pdo_delete('activity_stores',array('id' => $_GPC['id'], 'uniacid' => $_W['uniacid']));
	message('删除成功',referer(), 'success');
}
template('mc/store');