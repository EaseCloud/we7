<?php
global $_W, $_GPC;
$openid    = m('user')->getOpenid();
$member    = $this->model->getInfo($openid);
$condition = '';
$total = pdo_fetchcolumn('select count(id) from ' . tablename('sz_yi_member') . ' where agentid=:agentid and ((isagent=1 and status=0) or isagent=0) and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $member['id']));
if ($_W['isajax']) {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = array();
	$sql = 'select * from ' . tablename('sz_yi_member') . " where agentid={$member['id']} and ((isagent=1 and status=0) or isagent=0) and uniacid = " . $_W['uniacid'] . " {$condition}  ORDER BY id desc limit " . ($pindex - 1) * $psize . ',' . $psize;
	$list = pdo_fetchall($sql);
	foreach ($list as &$row) {
		$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
		$ordercount = pdo_fetchcolumn('select count(id) from ' . tablename('sz_yi_order') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $row['openid']));;
		$row['ordercount'] = number_format(intval($ordercount), 0);
		$moneycount = pdo_fetchcolumn('select sum(og.realprice) from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id where o.openid=:openid  and o.status>=1 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $row['openid']));;
		$row['moneycount'] = number_format(floatval($moneycount), 2);
	}
	unset($row);
	show_json(1, array('list' => $list, 'pagesize' => $psize));
}
include $this->template('customer');
