<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
global $_W, $_GPC;
////check_shop_auth
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	ca('coupon.category.view');
	if (!empty($_GPC['catname'])) {
		ca('coupon.category.edit|coupon.category.add');
		foreach ($_GPC['catid'] as $k => $v) {
			$data = array('name' => trim($_GPC['catname'][$k]), 'displayorder' => $_GPC['displayorder'][$k], 'status' => intval($_GPC['status'][$k]), 'uniacid' => $_W['uniacid']);
			if (empty($v)) {
				ca('coupon.category.add');
				pdo_insert('sz_yi_coupon_category', $data);
				$insert_id = pdo_insertid();
				plog('coupon.category.add', "添加分类 ID: {$insert_id}");
			} else {
				pdo_update('sz_yi_coupon_category', $data, array('id' => $v));
				plog('coupon.category.edit', "修改分类 ID: {$v}");
			}
		}
		plog('coupon.category.edit', '批量修改分类');
		message('分类更新成功！', $this->createPluginWebUrl('coupon/category', array('op' => 'display')), 'success');
	}
	$list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_coupon_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY displayorder DESC");
} elseif ($operation == 'delete') {
	ca('coupon.category.delete');
	$id = intval($_GPC['id']);
	$item = pdo_fetch('SELECT id,name FROM ' . tablename('sz_yi_coupon_category') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
	if (empty($item)) {
		message('抱歉，分类不存在或是已经被删除！', $this->createPluginWebUrl('coupon/category', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_coupon_category', array('id' => $id));
	plog('coupon.category.delete', "删除分类 ID: {$id} 标题: {$item['name']} ");
	message('分类删除成功！', $this->createPluginWebUrl('coupon/category', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('category');
