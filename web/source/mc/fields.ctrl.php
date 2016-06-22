<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_fields');
$dos = array('display', 'post');
$do = in_array($do, $dos) ? $do : 'display';
if($do == 'display') {
	$_W['page']['title'] = '字段管理 - 会员字段管理 - 会员中心';
	if (checksubmit('submit')) {
		if (!empty($_GPC['displayorder'])) {
			$data = array('uniacid' => $_W['uniacid']);
			foreach ($_GPC['displayorder'] as $id => $displayorder) {
				$data['id'] = intval($_GPC['id'][$id]);
				$data['fieldid'] = intval($_GPC['fieldid'][$id]);
				$data['displayorder'] = intval($displayorder);
				$data['available'] = intval($_GPC['available'][$id]);
				if (empty($data['id'])) {
					$data['title'] = $_GPC['title'][$id];
					pdo_insert('mc_member_fields', $data);
				} else {
					pdo_update('mc_member_fields', $data, array('id' => $data['id']));
				}
			}
		}
		message('会员字段更新成功！', referer(), 'success');
	}

	$sql = 'SELECT `f`.`field`, `f`.`id` AS `fid`, `mf`.* FROM ' . tablename('profile_fields') . " AS `f` LEFT JOIN " .
			tablename('mc_member_fields') . " AS `mf` ON `f`.`id` = `mf`.`fieldid` WHERE `mf`.`uniacid` = :uniacid ORDER BY
			`displayorder` DESC";
	$fields = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));

		$sql = 'SELECT * FROM ' . tablename('mc_member_fields') . ' WHERE `uniacid` = :uniacid';
	$memberFields = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']), 'fieldid');
		$sql = 'SELECT * FROM ' . tablename('profile_fields');
	$sysFields = pdo_fetchall($sql, array(), 'id');
		$diffFields = array_diff(array_keys($sysFields), array_keys($memberFields));
		if (!empty($diffFields)) {
		$update = array('uniacid' => $_W['uniacid']);
		foreach ($diffFields as $fieldIndex) {
			$update['fieldid'] = $sysFields[$fieldIndex]['id'];
			$update['title'] = $sysFields[$fieldIndex]['title'];
			$update['available'] = $sysFields[$fieldIndex]['available'];
			$update['displayorder'] = $sysFields[$fieldIndex]['displayorder'];

			pdo_insert('mc_member_fields', $update);
			$insertId = pdo_insertid();
			$memberFields[$insertId]['id'] = $insertId;
			$memberFields[$insertId]['field'] = $sysFields[$fieldIndex]['field'];
			$memberFields[$insertId]['fid'] = $sysFields[$fieldIndex]['id'];
			$memberFields[$insertId] = array_merge($memberFields[$insertId], $update);
		}
	}
}

if ($do == 'post') {
	$_W['page']['title'] = '字段编辑 - 会员字段管理 - 会员中心';
	$id = intval($_GPC['id']);
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('抱歉，请填写资料名称！');
		}
		$data = array(
			'title' => $_GPC['title'],
			'displayorder' => intval($_GPC['displayorder']),
			'available' => intval($_GPC['available']),
		);
		pdo_update('mc_member_fields', $data, array('id' => $id));
		message('会员字段更新成功！', url('mc/fields'), 'success');
	}
	$item = pdo_fetch("SELECT * FROM ".tablename('mc_member_fields')." WHERE id = :id", array(':id' => $id));
}


template('mc/fields');