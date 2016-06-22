<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
if ($operation == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$status = intval($_GPC['status']);
	$condition = " uniacid=:uniacid and openid=:openid";
	$params = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
	if ($status == 2) {
		$condition .= " and ( status=1 or status=2 )";
	} else {
		$condition .= " and status=$status";
	}
	$list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_order') . " " . " where 1" . $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_order') . " WHERE 1 {$condition}");
	$pager = pagination($total, $pindex, $psize);
	if (!empty($list)) {
		foreach ($list as &$row) {
			$goodsid = pdo_fetchall("SELECT goodsid,total FROM " . tablename('sz_yi_order_goods') . " WHERE orderid = '{$row['id']}'", array(), 'goodsid');
			$goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice,o.total,o.optionid FROM " . tablename('sz_yi_order_goods') . " o left join " . tablename('shopping_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$row['id']}'");
			$goods = set_medias($goods, 'thumb');
			foreach ($goods as &$item) {
				$option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("shopping_goods_option") . " where id=:id limit 1", array(":id" => $item['optionid']));
				if ($option) {
					$item['title'] = "[" . $option['title'] . "]" . $item['title'];
					$item['marketprice'] = $option['marketprice'];
				}
			}
			unset($item);
			$row['goods'] = $goods;
			$row['total'] = $goodsid;
			$row['dispatch'] = pdo_fetch("select id,dispatchname from " . tablename('shopping_dispatch') . " where id=:id limit 1", array(":id" => $row['dispatch']));
		}
		unset($row);
	}
	show_json(1, array('total' => $total, 'list' => $list));
} else if ($operation == 'detail') {
	$orderid = intval($_GPC['orderid']);
	$item = pdo_fetch("SELECT * FROM " . tablename('sz_yi_order') . " WHERE uniacid = '{$_W['uniacid']}' AND from_user = '{$_W['fans']['from_user']}' and id='{$orderid}' limit 1");
	if (empty($item)) {
		message('抱歉，您的订单不存或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
	}
	$goodsid = pdo_fetch("SELECT goodsid,total FROM " . tablename('sz_yi_order_goods') . " WHERE orderid = '{$orderid}'", array(), 'goodsid');
	$goods = pdo_fetchall("SELECT g.id, g.title, g.thumb, g.unit, g.marketprice, o.total,o.optionid FROM " . tablename('sz_yi_order_goods') . " o left join " . tablename('shopping_goods') . " g on o.goodsid=g.id " . " WHERE o.orderid='{$orderid}'");
	$goods = set_medias($goods, 'thumb');
	foreach ($goods as &$g) {
		$option = pdo_fetch("select title,marketprice,weight,stock from " . tablename("shopping_goods_option") . " where id=:id limit 1", array(":id" => $g['optionid']));
		if ($option) {
			$g['title'] = "[" . $option['title'] . "]" . $g['title'];
			$g['marketprice'] = $option['marketprice'];
		}
	}
	unset($g);
	$dispatch = pdo_fetch("select id,dispatchname from " . tablename('shopping_dispatch') . " where id=:id limit 1", array(":id" => $item['dispatch']));
	show_json(1, array('goods' => $goods, 'dispatch' => $dispatch));
} else if ($operation == 'confirm') {
	$orderid = intval($_GPC['orderid']);
	$order = pdo_fetch("SELECT status FROM " . tablename('sz_yi_order') . " WHERE id = :id AND from_user = :from_user", array(':id' => $orderid, ':from_user' => $_W['fans']['from_user']));
	if (empty($order)) {
		showmessage('抱歉，订单不存在或是已经被取消！', $this->createMobileUrl('myorder'), 'error');
	}
	if ($order['status'] != 2) {
		showmessage('订单未支付，无法进行收货！', $this->createMobileUrl('shop/order', null, true), 'error');
	}
	pdo_update('sz_yi_order', array('status' => 3), array('uniacid' => $_W['uniacid'], 'id' => $orderid, 'openid' => $openid));
	showmessage('确认收货完成！', $this->createMobileUrl('order'), 'success');
} elseif ($operation == 'cancel') {
	$orderid = intval($_GPC['orderid']);
	$order = pdo_fetch("SELECT status FROM " . tablename('sz_yi_order') . " WHERE id = :id AND from_user = :from_user", array(':id' => $orderid, ':from_user' => $_W['fans']['from_user']));
	if (empty($order)) {
		showmessage('抱歉，订单不存在或是已经被取消！', $this->createMobileUrl('shop/order', null, true), 'error');
	}
	if ($order['status'] >= 1) {
		showmessage('订单已支付或已完成，不能取消！', $this->createMobileUrl('shop/order', null, true), 'error');
	}
	pdo_update('sz_yi_order', array('status' => -1), array('uniacid' => $_W['uniacid'], 'id' => $orderid, 'openid' => $openid));
	showmessage('订单已经取消成功！', $this->createMobileUrl('shop/order', null, true), 'success');
}
include $this->template('mobile/shop/order');
