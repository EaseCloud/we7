<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
if (empty($openid)) {
    $openid = $_GPC['openid'];
}
$member  = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
$logid   = intval($_GPC['logid']);
$shopset = m('common')->getSysset('shop');
//if ($_W['isajax']) {
    if (!empty($orderid)) {
		
        $order = pdo_fetch("select * from " . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(
            ':id' => $orderid,
            ':uniacid' => $uniacid,
            ':openid' => $openid
        ));
        if (empty($order)) {
            show_json(0, '订单未找到!');
        }
        $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(
            ':uniacid' => $uniacid,
            ':module' => 'sz_yi',
            ':tid' => $order['ordersn']
        ));
        if (!empty($log) && $log['status'] != '0') {
            show_json(0, '订单已支付, 无需重复支付!');
        }
        $param_title     = $shopset['name'] . "订单: " . $order['ordersn'];
        $yunpay         = array(
            'success' => false
        );
        $params          = array();
        $params['tid']   = $log['tid'];
        $params['user']  = $openid;
        $params['fee']   = $order['price'];
        $params['title'] = $param_title;
        load()->func('communication');
        load()->model('payment');
		
        $pluginy = p('yunpay');
        if ($pluginy) {
            $yunpayinfo = $pluginy->getYunpay();
            
            if (isset($yunpayinfo) && $yunpayinfo['switch']) {
                $yunpay  = $pluginy->yunpay_build($params, $yunpayinfo, 0, $openid);
                echo $yunpay;
                die();
            }
        }

    } elseif (!empty($logid)) {
        $log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `id`=:id and `uniacid`=:uniacid limit 1', array(
            ':uniacid' => $uniacid,
            ':id' => $logid
        ));
        if (empty($log)) {
            show_json(0, '充值出错!');
        }
        if (!empty($log['status'])) {
            show_json(0, '已经充值成功,无需重复支付!');
        }
        $yunpay          = array(
            'success' => false
        );
        $params          = array();
        $params['tid']   = $log['logno'];
        $params['user']  = $log['openid'];
        $params['fee']   = $log['money'];
        $params['title'] = $log['title'];

        load()->func('communication');
        load()->model('payment');

        $pluginy = p('yunpay');
        if ($pluginy) {
            $yunpayinfo = $pluginy->getYunpay();
            if (isset($yunpayinfo) && $yunpayinfo['switch']) {
                $yunpay  = $pluginy->yunpay_build($params, $yunpayinfo, 1, $openid);
                echo $yunpay;
                die();
            }
        }

    }

//}
