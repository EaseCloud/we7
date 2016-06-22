<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
$orderid   = intval($_GPC['id']);
$saler     = pdo_fetch('select * from ' . tablename('sz_yi_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(
    ':uniacid' => $_W['uniacid'],
    ':openid' => $openid
));
if (empty($saler)) {
    show_json(0, '您无核销权限!');
}
$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid  limit 1', array(
    ':id' => $orderid,
    ':uniacid' => $uniacid
));
if (empty($order)) {
    show_json(0, "订单不存在!");
}
if ($order['status'] < 1) {
    show_json(0, "订单未付款，无法核销!");
}
if (empty($order['isverify'])) {
    show_json(0, "订单无需核销!");
}
if (!empty($order['verified'])) {
    show_json(0, "订单此已核销，无需要重复核!");
}
$storeids = array();
$goods    = pdo_fetchall("select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,g.isverify,g.storeids from " . tablename('sz_yi_order_goods') . " og " . " left join " . tablename('sz_yi_goods') . " g on g.id=og.goodsid " . " where og.orderid=:orderid and og.uniacid=:uniacid ", array(
    ':uniacid' => $uniacid,
    ':orderid' => $orderid
));
foreach ($goods as $g) {
    if (!empty($g['storeids'])) {
        $storeids = array_merge(explode(',', $g['storeids']), $storeids);
    }
}
if (!empty($storeids)) {
    if (!empty($saler['storeid'])) {
        if (!in_array($saler['storeid'], $storeids)) {
            show_json(0, '您无此门店的核销权限!');
        }
    }
}
$time = time();
pdo_update('sz_yi_order', array(
    'status' => 3,
    'sendtime' => $time,
    'finishtime' => $time,
    'verifytime' => $time,
    'verified' => 1,
    'verifyopenid' => $openid
), array(
    'id' => $order['id']
));
m('notice')->sendOrderMessage($orderid);
if (p('commission')) {
    p('commission')->checkOrderFinish($orderid);
}
show_json(1);
