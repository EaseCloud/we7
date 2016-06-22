<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('activity_offline');
$dos = array('introduce', 'list', 'post', 'del', 'post', 'verify');
$do = in_array($do, $dos) ? $do : 'introduce';
$_W['page']['title'] = '店员列表 - 门店营销参数 - 会员营销';

if($do == 'introduce') {
	template('mc/clerk');
	exit();
}
if($do == 'clerk') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$limit = 'ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_coupon_password')." WHERE uniacid = :uniacid ", array(':uniacid' => $_W['uniacid']));
	$list = pdo_fetchall("SELECT * FROM ".tablename('activity_coupon_password')." WHERE uniacid = :uniacid {$limit}", array(':uniacid' => $_W['uniacid']));
	$pager = pagination($total, $pindex, $psize);
	$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'), 'id');
}
if($do == 'post') {
	$id = intval($_GPC['id']);
	if($id > 0){
		$sql = 'SELECT * FROM ' . tablename('activity_coupon_password') . " WHERE id = :id AND uniacid = :uniacid";
		$clerk = pdo_fetch($sql, array(':id' => $id, ':uniacid' => $_W['uniacid']));
		if(empty($clerk)) {
			message('店员不存在', referer(), 'error');
		}
	}

	if(checksubmit()) {

	}

	$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'));
}
if($do == 'post'){
	if($_W['isajax']) {
		$data = array(
			'uniacid' => intval($_W['uniacid']),
			'storeid' => intval($_GPC['storeid']),
			'name' => trim($_GPC['name']),
			'password' => trim($_GPC['password']),
			'mobile' => trim($_GPC['mobile']),
			'openid' => trim($_GPC['openid']),
			'nickname' => trim($_GPC['nickname'])
		);
		$id = intval($_GPC['id']);
		if($id > 0) {
			pdo_update('activity_coupon_password',$data, array('id' => $id, 'uniacid' => $_W['uniacid']));
		} else {
			pdo_insert('activity_coupon_password', $data);
		}
		exit('success');
	}
}
if($do == 'verify') {
	if($_W['isajax']) {
		$openid = trim($_GPC['openid']);
		$nickname = trim($_GPC['nickname']);
		if(!empty($openid)) {
			$sql = 'SELECT openid,nickname FROM ' . tablename('mc_mapping_fans') . " WHERE acid =:acid AND openid = :openid";
			$exist = pdo_fetch($sql, array(':openid' => $openid, ':acid' => $_W['acid']));
		} else {
			$sql = 'SELECT openid,nickname FROM ' . tablename('mc_mapping_fans') . " WHERE acid =:acid AND nickname = :nickname";
			$exist = pdo_fetch($sql, array(':nickname' => $nickname, ':acid' => $_W['acid']));
		}
		if (empty($exist)) {
			message(error(-1, '未找到对应的粉丝编号，请检查昵称或openid是否有效'), '', 'ajax');
		}
		message(error(0, $exist), '', 'ajax');
	}
}
if($do == 'del') {
	pdo_delete('activity_coupon_password',array('id' => intval($_GPC['id']), 'uniacid' => $_W['uniacid']));
	message("删除成功",referer(),'success');
}
template('mc/clerk');
