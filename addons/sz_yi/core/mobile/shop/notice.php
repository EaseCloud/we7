<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
if ($_W['isajax']) {
	if ($operation == 'display') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$condition = ' and `uniacid` = :uniacid and status=1';
		$params = array(':uniacid' => $_W['uniacid']);
		$sql = 'SELECT COUNT(*) FROM ' . tablename('sz_yi_notice') . " where 1 $condition";
		$total = pdo_fetchcolumn($sql, $params);
		$sql = 'SELECT * FROM ' . tablename('sz_yi_notice') . ' where 1 ' . $condition . ' ORDER BY displayorder desc,createtime desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		$list = pdo_fetchall($sql, $params);
		foreach ($list as &$row) {
			$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
		}
		unset($row);
		$list = set_medias($list, 'thumb');
		show_json(1, array('list' => $list, 'pagesize' => $psize));
	} else if ($operation == 'get') {
		$id = intval($_GPC['id']);
		$data = pdo_fetch('select * from ' . tablename('sz_yi_notice') . ' where uniacid=:uniacid and id=:id and status=1 limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
		if (!empty($data)) {
			$data['createtime'] = date('Y-m-d H:i', $data['createtime']);
		}
		show_json(1, array('notice' => $data));
	}
}
include $this->template('shop/notice');
