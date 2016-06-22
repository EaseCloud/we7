<?php
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$set = $this->getSet();
$leveltype = intval($set['leveltype']);
if ($operation == 'display') {
    ca('commission.level.view');
    $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_commission_level') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY commission1 asc");
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		ca('commission.level.add');
	} else {
		ca('commission.level.view|commission.level.edit');
	}
	$level = pdo_fetch("SELECT * FROM " . tablename('sz_yi_commission_level') . " WHERE id = '$id'");
	if (checksubmit('submit')) {
		if (empty($_GPC['levelname'])) {
			message('抱歉，请输入分类名称！');
		}
		$data = array('uniacid' => $_W['uniacid'], 'levelname' => $_GPC['levelname'], 'commission1' => $_GPC['commission1'], 'commission2' => $_GPC['commission2'], 'commission3' => $_GPC['commission3'], 'commissionmoney' => $_GPC['commissionmoney'], 'ordermoney' => $_GPC['ordermoney'], 'ordercount' => intval($_GPC['ordercount']), 'downcount' => intval($_GPC['downcount']),);
		if (!empty($id)) {
			pdo_update('sz_yi_commission_level', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
			plog('commission.level.edit', "修改分销商等级 ID: {$id}");
		} else {
			pdo_insert('sz_yi_commission_level', $data);
			$id = pdo_insertid();
			plog('commission.level.add', "添加分销商等级 ID: {$id}");
		}
		message('更新等级成功！', $this->createPluginWebUrl('commission/level', array('op' => 'display')), 'success');
	}
} elseif ($operation == 'delete') {
	ca('commission.level.delete');
	$id = intval($_GPC['id']);
	$level = pdo_fetch("SELECT id,levelname FROM " . tablename('sz_yi_commission_level') . " WHERE id = '$id'");
	if (empty($level)) {
		message('抱歉，等级不存在或是已经被删除！', $this->createPluginWebUrl('commission/level', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_commission_level', array('id' => $id, 'uniacid' => $_W['uniacid']));
	plog('commission.level.delete', "删除分销商等级 ID: {$id} 等级名称: {$level['levelname']}");
	message('等级删除成功！', $this->createPluginWebUrl('commission/level', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('level');
