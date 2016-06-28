<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
if ($operation == 'display') {
	$id = intval($_GPC['id']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and `uniacid` = :uniacid and goodsid=:goodsid and deleted=0';
	$params = array(':uniacid' => $_W['uniacid'], ':goodsid' => $id);
	$sql = 'SELECT COUNT(*) FROM ' . tablename('sz_yi_goods_comment') . $condition;
	$total = pdo_fetchcolumn($sql, $params);
	$list = array();
	if (!empty($total)) {
		$sql = 'SELECT * FROM ' . tablename('sz_yi_goods_comment') . ' where 1 ' . $condition . ' ORDER BY `id` DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		$list = pdo_fetchall($sql, $params);
	}
	show_json(1, array('total' => $total, 'list' => $list));
} else if ($operation == 'post') {
	$lastdata = pdo_fetch('select createime from ' . tablename('sz_yi_member_address') . ' where uniacid=:uniacid and openid=:openid order by id desc limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if (!empty($lastdata)) {
		if ($lastdata['createtime'] - time() <= 5) {
			show_json(0, '请过 5 秒钟后再次评论!');
		}
	}
	$data = $_GPC['commentdata'];
	$data['openid'] = $openid;
	$data['uniacid'] = $_W['uniacid'];
	pdo_insert('sz_yi_goods_comment', $data);
	show_json(1);
} else if ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$data = pdo_fetch('select id from ' . tablename('sz_yi_member_address') . ' where uniacid=:uniacid and id=:id and deleted=0 limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if (empty($data)) {
		show_json(0, '地址未找到');
	}
	pdo_update('sz_yi_member_address', array('deleted' => 1), array('id' => $id));
	show_json(1);
}
include $this->template('mobile/m/address');
