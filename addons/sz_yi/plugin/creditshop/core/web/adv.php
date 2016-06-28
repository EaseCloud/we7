<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	ca('creditshop.adv.view');
	if (!empty($_GPC['displayorder'])) {
		ca('creditshop.adv.edit');
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('sz_yi_creditshop_adv', array('displayorder' => $displayorder), array('id' => $id));
		}
		plog('creditshop.adv.edit', '批量修改幻灯片的排序');
		message('分类排序更新成功！', $this->createPluginWebUrl('creditshop/adv', array('op' => 'display')), 'success');
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_creditshop_adv') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY displayorder DESC");
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		ca('creditshop.adv.add');
	} else {
		ca('creditshop.adv.edit|creditshop.adv.view');
	}
	if (checksubmit('submit')) {
		$data = array('uniacid' => $_W['uniacid'], 'advname' => trim($_GPC['advname']), 'link' => trim($_GPC['link']), 'enabled' => intval($_GPC['enabled']), 'displayorder' => intval($_GPC['displayorder']), 'thumb' => save_media($_GPC['thumb']));
		if (!empty($id)) {
			pdo_update('sz_yi_creditshop_adv', $data, array('id' => $id));
			plog('creditshop.adv.edit', "修改积分商城幻灯片 ID: {$id}");
		} else {
			pdo_insert('sz_yi_creditshop_adv', $data);
			$id = pdo_insertid();
			plog('creditshop.adv.add', "添加积分商城幻灯片 ID: {$id}");
		}
		message('更新幻灯片成功！', $this->createPluginWebUrl('creditshop/adv', array('op' => 'display')), 'success');
	}
	$item = pdo_fetch("select * from " . tablename('sz_yi_creditshop_adv') . " where id=:id and uniacid=:uniacid limit 1", array(":id" => $id, ":uniacid" => $_W['uniacid']));
} elseif ($operation == 'delete') {
	ca('creditshop.adv.delete');
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT id,advname FROM " . tablename('sz_yi_creditshop_adv') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
	if (empty($item)) {
		message('抱歉，幻灯片不存在或是已经被删除！', $this->createPluginWebUrl('creditshop/adv', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_creditshop_adv', array('id' => $id));
	plog('creditshop.adv.delete', "删除积分商城幻灯片 ID: {$id} 标题: {$item['advname']} ");
	message('幻灯片删除成功！', $this->createPluginWebUrl('creditshop/adv', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('adv');
