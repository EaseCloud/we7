<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	ca('creditshop.category.view');
	if (!empty($_GPC['displayorder'])) {
		ca('creditshop.category.edit');
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('sz_yi_creditshop_category', array('displayorder' => $displayorder), array('id' => $id));
		}
		plog('creditshop.category.edit', '批量修改分类的排序');
		message('分类排序更新成功！', $this->createPluginWebUrl('creditshop/category', array('op' => 'display')), 'success');
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_creditshop_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY displayorder DESC");
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		ca('creditshop.category.add');
	} else {
		ca('creditshop.category.edit|creditshop.category.view');
	}
	if (checksubmit('submit')) {
		$data = array('uniacid' => $_W['uniacid'], 'name' => trim($_GPC['catename']), 'enabled' => intval($_GPC['enabled']), 'isrecommand' => intval($_GPC['isrecommand']), 'displayorder' => intval($_GPC['displayorder']), 'thumb' => save_media($_GPC['thumb']));
		if (!empty($id)) {
			pdo_update('sz_yi_creditshop_category', $data, array('id' => $id));
			plog('creditshop.category.edit', "修改积分商城分类 ID: {$id}");
		} else {
			pdo_insert('sz_yi_creditshop_category', $data);
			$id = pdo_insertid();
			plog('creditshop.category.add', "添加积分商城分类 ID: {$id}");
		}
		message('更新分类成功！', $this->createPluginWebUrl('creditshop/category', array('op' => 'display')), 'success');
	}
	$item = pdo_fetch("select * from " . tablename('sz_yi_creditshop_category') . " where id=:id and uniacid=:uniacid limit 1", array(":id" => $id, ":uniacid" => $_W['uniacid']));
} elseif ($operation == 'delete') {
	ca('creditshop.category.delete');
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT id,name FROM " . tablename('sz_yi_creditshop_category') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
	if (empty($item)) {
		message('抱歉，分类不存在或是已经被删除！', $this->createPluginWebUrl('creditshop/category', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_creditshop_category', array('id' => $id));
	plog('creditshop.category.delete', "删除积分商城分类 ID: {$id} 标题: {$item['name']} ");
	message('分类删除成功！', $this->createPluginWebUrl('creditshop/category', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('category');
