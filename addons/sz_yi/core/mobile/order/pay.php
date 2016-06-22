<?php
///wwwroot/addons/sz_yi/core/mobile/order/

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
if ($operation == 'display' && $_W['isajax']) {
    if (empty($orderid)) {
        show_json(0, '参数错误!');
    }
    $order = pdo_fetch("select * from " . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(
        ':id' => $orderid,
        ':uniacid' => $uniacid,
        ':openid' => $openid
    ));
    if (empty($order)) {
        show_json(0, '订单未找到!');
    }
    if ($order['status'] == -1) {
        show_json(-1, '订单已关闭, 无法付款!');
    } else if ($order['status'] >= 1) {
        show_json(-1, '订单已付款, 无需重复支付!');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(
        ':uniacid' => $uniacid,
        ':module' => 'sz_yi',
        ':tid' => $order['ordersn']
    ));
    if (!empty($log) && $log['status'] != '0') {
        show_json(-1, '订单已支付, 无需重复支付!');
    }
    if (!empty($log) && $log['status'] == '0') {
        pdo_delete('core_paylog', array(
            'plid' => $log['plid']
        ));
        $log = null;
    }
    $plid = $log['plid'];
    if (empty($log)) {
        $log = array(
            'uniacid' => $uniacid,
            'openid' => $member['uid'],
            'module' => "sz_yi",
            'tid' => $order['ordersn'],
            'fee' => $order['price'],
            'status' => 0
        );
        pdo_insert('core_paylog', $log);
        $plid = pdo_insertid();
    }
    $set           = m('common')->getSysset(array(
        'shop',
        'pay'
    ));
    $credit        = array(
        'success' => false
    );
    $currentcredit = 0;
    if (isset($set['pay']) && $set['pay']['credit'] == 1) {
        if ($order['deductcredit2'] <= 0) {
            $credit = array(
                'success' => true,
                'current' => m('member')->getCredit($openid, 'credit2')
            );
        }
    }
    load()->model('payment');
    $setting = uni_setting($_W['uniacid'], array(
        'payment'
    ));
    $wechat  = array(
        'success' => false
    );
    if (is_weixin()) {
        if (isset($set['pay']) && $set['pay']['weixin'] == 1) {
            if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
                $wechat['success'] = true;
            }
        }
    }
    $alipay = array(
        'success' => false
    );
    if (isset($set['pay']) && $set['pay']['alipay'] == 1) {
        if (is_array($setting['payment']['alipay']) && $setting['payment']['alipay']['switch']) {
            $alipay['success'] = true;
        }
    }

    $pluginy = p('yunpay');
    if ($pluginy) {
        $yunpay = array(
            'success' => false
        );

        $yunpayinfo = $pluginy->getYunpay();
        
        if (isset($yunpayinfo) && $yunpayinfo['switch']) {
            $yunpay['success'] = true;
        }
    }
	
    $unionpay = array(
        'success' => false
    );
    if (isset($set['pay']) && $set['pay']['unionpay'] == 1) {
        if (is_array($setting['payment']['unionpay']) && $setting['payment']['unionpay']['switch']) {
            $unionpay['success'] = true;
        }
    }
    $cash      = array(
        'success' => $order['cash'] == 1 && isset($set['pay']) && $set['pay']['cash'] == 1
    );
    $returnurl = urlencode($this->createMobileUrl('order/pay', array(
        'orderid' => $orderid
    )));
    show_json(1, array(
        'order' => $order,
        'set' => $set,
        'credit' => $credit,
        'wechat' => $wechat,
        'alipay' => $alipay,
        'unionpay' => $unionpay,
		'yunpay' => $yunpay,
        'cash' => $cash,
        'isweixin' => is_weixin(),
        'currentcredit' => $currentcredit,
        'returnurl' => $returnurl
    ));
} else if ($operation == 'pay' && $_W['ispost']) {
    $set   = m('common')->getSysset(array(
        'shop',
        'pay'
    ));
    $order = pdo_fetch("select * from " . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(
        ':id' => $orderid,
        ':uniacid' => $uniacid,
        ':openid' => $openid
    ));
    if (empty($order)) {
        show_json(0, '订单未找到!');
    }
    $type = $_GPC['type'];
    if (!in_array($type, array(
        'weixin',
        'alipay',
        'unionpay',
        'yunpay'
    ))) {
        show_json(0, '未找到支付方式');
    }

    if($member['credit2'] < $order['deductcredit2'] && $order['deductcredit2'] > 0){
        show_json(0, '余额不足，请充值后在试！');
    }

    $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(
        ':uniacid' => $uniacid,
        ':module' => 'sz_yi',
        ':tid' => $order['ordersn']
    ));
    if (empty($log)) {
        show_json(0, '支付出错,请重试!');
    }
    $order_goods = pdo_fetchall('select og.id,g.title, og.goodsid,og.optionid,g.total as stock,og.total as buycount,g.status,g.deleted,g.maxbuy,g.usermaxbuy,g.istime,g.timestart,g.timeend,g.buylevels,g.buygroups from  ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on og.goodsid = g.id ' . ' where og.orderid=:orderid and og.uniacid=:uniacid ', array(
        ':uniacid' => $_W['uniacid'],
        ':orderid' => $orderid
    ));
    foreach ($order_goods as $data) {
        if (empty($data['status']) || !empty($data['deleted'])) {
            show_json(-1, $data['title'] . '<br/> 已下架!');
        }
        if ($data['maxbuy'] > 0) {
            if ($data['buycount'] > $data['maxbuy']) {
                show_json(-1, $data['title'] . '<br/> 一次限购 ' . $data['maxbuy'] . $unit . "!");
            }
        }
        if ($data['usermaxbuy'] > 0) {
            $order_goodscount = pdo_fetchcolumn('select ifnull(sum(og.total),0)  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id ' . ' where og.goodsid=:goodsid and  o.status>=1 and o.openid=:openid  and og.uniacid=:uniacid ', array(
                ':goodsid' => $data['goodsid'],
                ':uniacid' => $uniacid,
                ':openid' => $openid
            ));
            if ($order_goodscount >= $data['usermaxbuy']) {
                show_json(-1, $data['title'] . '<br/> 最多限购 ' . $data['usermaxbuy'] . $unit . "!");
            }
        }
        if ($data['istime'] == 1) {
            if (time() < $data['timestart']) {
                show_json(-1, $data['title'] . '<br/> 限购时间未到!');
            }
            if (time() > $data['timeend']) {
                show_json(-1, $data['title'] . '<br/> 限购时间已过!');
            }
        }
        if ($data['buylevels'] != '') {
            $buylevels = explode(',', $data['buylevels']);
            if (!in_array($member['level'], $buylevels)) {
                show_json(-1, '您的会员等级无法购买<br/>' . $data['title'] . '!');
            }
        }
        if ($data['buygroups'] != '') {
            $buygroups = explode(',', $data['buygroups']);
            if (!in_array($member['groupid'], $buygroups)) {
                show_json(-1, '您所在会员组无法购买<br/>' . $data['title'] . '!');
            }
        }
        if (!empty($data['optionid'])) {
            $option = pdo_fetch('select id,title,marketprice,goodssn,productsn,stock,virtual from ' . tablename('sz_yi_goods_option') . ' where id=:id and goodsid=:goodsid and uniacid=:uniacid  limit 1', array(
                ':uniacid' => $uniacid,
                ':goodsid' => $data['goodsid'],
                ':id' => $data['optionid']
            ));
            if (!empty($option)) {
                if ($option['stock'] != -1) {
                    if (empty($option['stock'])) {
                        show_json(-1, $data['title'] . "<br/>" . $option['title'] . " 库存不足!");
                    }
                }
            }
        } else {
            if ($data['stock'] != -1) {
                if (empty($data['stock'])) {
                    show_json(-1, $data['title'] . "<br/>库存不足!");
                }
            }
        }
    }
    $plid        = $log['plid'];
    $param_title = $set['shop']['name'] . "订单: " . $order['ordersn'];
    if ($type == 'weixin') {
        if (!is_weixin()) {
            show_json(0, '非微信环境!');
        }
        if (empty($set['pay']['weixin'])) {
            show_json(0, '未开启微信支付!');
        }
        $wechat        = array(
            'success' => false
        );
        $params        = array();
        $params['tid'] = $log['tid'];
        if (!empty($order['ordersn2'])) {
            $var = sprintf("%02d", $order['ordersn2']);
            $params['tid'] .= "GJ" . $var;
        }
        $params['user']  = $openid;
        $params['fee']   = $order['price'];
        $params['title'] = $param_title;
        load()->model('payment');
        $setting = uni_setting($_W['uniacid'], array(
            'payment'
        ));
        if (is_array($setting['payment'])) {
            $options           = $setting['payment']['wechat'];
            $options['appid']  = $_W['account']['key'];
            $options['secret'] = $_W['account']['secret'];
            $wechat            = m('common')->wechat_build($params, $options, 0);
            $wechat['success'] = false;
            if (!is_error($wechat)) {
                $wechat['success'] = true;
            } else {
                show_json(0, $wechat['message']);
            }
        }
        if (!$wechat['success']) {
            show_json(0, '微信支付参数错误!');
        }
        pdo_update('sz_yi_order', array(
            'paytype' => 21
        ), array(
            'id' => $order['id']
        ));
        show_json(1, array(
            'wechat' => $wechat
        ));
    } else if ($type == 'alipay') {
        pdo_update('sz_yi_order', array(
            'paytype' => 22
        ), array(
            'id' => $order['id']
        ));
        show_json(1);
    }else if ($type == 'yunpay') {
        pdo_update('sz_yi_order', array(
            'paytype' => 24
        ), array(
            'id' => $order['id']
        ));
        show_json(1);
    }
} else if ($operation == 'complete' && $_W['ispost']) {
    $order = pdo_fetch("select * from " . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(
        ':id' => $orderid,
        ':uniacid' => $uniacid,
        ':openid' => $openid
    ));
    if (empty($order)) {
        show_json(0, '订单未找到!');
    }
    $type = $_GPC['type'];
    if (!in_array($type, array(
        'weixin',
        'alipay',
        'credit',
        'cash'
    ))) {
        show_json(0, '未找到支付方式');
    }
    if($member['credit2'] < $order['deductcredit2'] && $order['deductcredit2'] > 0){
        show_json(0, '余额不足，请充值后在试！');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(
        ':uniacid' => $uniacid,
        ':module' => 'sz_yi',
        ':tid' => $order['ordersn']
    ));
    if (empty($log)) {
        show_json(0, '支付出错,请重试1!');
    }
    $plid = $log['plid'];
    if ($type == 'cash') {
        pdo_update('sz_yi_order', array(
            'paytype' => 3
        ), array(
            'id' => $order['id']
        ));
        $ret            = array();
        $ret['result']  = 'success';
        $ret['type']    = 'cash';
        $ret['from']    = 'return';
        $ret['tid']     = $log['tid'];
        $ret['user']    = $order['openid'];
        $ret['fee']     = $order['price'];
        $ret['weid']    = $_W['uniacid'];
        $ret['uniacid'] = $_W['uniacid'];
        $payresult      = $this->payResult($ret);
        show_json(2, $payresult);
    }
    $ps          = array();
    $ps['tid']   = $log['tid'];
    $ps['user']  = $openid;
    $ps['fee']   = $log['fee'];
    $ps['title'] = $log['title'];
    if ($type == 'credit') {
        $credits = m('member')->getCredit($openid, 'credit2');
        if ($credits < $ps['fee']) {
            show_json(0, "余额不足,请充值");
        }
        $fee    = floatval($ps['fee']);
        $result = m('member')->setCredit($openid, 'credit2', -$fee, array(
            $_W['member']['uid'],
            '消费' . $setting['creditbehaviors']['currency'] . ':' . $fee
        ));
        if (is_error($result)) {
            show_json(0, $result['message']);
        }
        $record           = array();
        $record['status'] = '1';
        $record['type']   = 'cash';
        pdo_update('core_paylog', $record, array(
            'plid' => $log['plid']
        ));
        pdo_update('sz_yi_order', array(
            'paytype' => 1
        ), array(
            'id' => $order['id']
        ));
        $ret            = array();
        $ret['result']  = 'success';
        $ret['type']    = $log['type'];
        $ret['from']    = 'return';
        $ret['tid']     = $log['tid'];
        $ret['user']    = $log['openid'];
        $ret['fee']     = $log['fee'];
        $ret['weid']    = $log['weid'];
        $ret['uniacid'] = $log['uniacid'];
        $pay_result     = $this->payResult($ret);
        show_json(1, $pay_result);
    } else if ($type == 'weixin') {
        $ordersn = $order['ordersn'];
        if (!empty($order['ordersn2'])) {
            $ordersn .= "GJ" . sprintf("%02d", $order['ordersn2']);
        }
        $payquery = m('finance')->isWeixinPay($ordersn);
        if (!is_error($payquery)) {
            $record           = array();
            $record['status'] = '1';
            $record['type']   = 'wechat';
            pdo_update('core_paylog', $record, array(
                'plid' => $log['plid']
            ));
            $ret            = array();
            $ret['result']  = 'success';
            $ret['type']    = 'wechat';
            $ret['from']    = 'return';
            $ret['tid']     = $log['tid'];
            $ret['user']    = $log['openid'];
            $ret['fee']     = $log['fee'];
            $ret['weid']    = $log['weid'];
            $ret['uniacid'] = $log['uniacid'];
            $ret['deduct']  = intval($_GPC['deduct']) == 1;
            $pay_result     = $this->payResult($ret);
            show_json(1, $pay_result);
        }
        show_json(0, '支付出错,请重试!');
    }
} else if ($operation == 'return') {
    $tid = $_GPC['out_trade_no'];
    if (!m('finance')->isAlipayNotify($_GET)) {
        die('支付出现错误，请重试!');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(
        ':uniacid' => $uniacid,
        ':module' => 'sz_yi',
        ':tid' => $tid
    ));
    if (empty($log)) {
        die('支付出现错误，请重试!');
    }
    if ($log['status'] != 1) {
        $record           = array();
        $record['status'] = '1';
        $record['type']   = 'alipay';
        pdo_update('core_paylog', $record, array(
            'plid' => $log['plid']
        ));
        $ret            = array();
        $ret['result']  = 'success';
        $ret['type']    = 'alipay';
        $ret['from']    = 'return';
        $ret['tid']     = $log['tid'];
        $ret['user']    = $log['openid'];
        $ret['fee']     = $log['fee'];
        $ret['weid']    = $log['weid'];
        $ret['uniacid'] = $log['uniacid'];
        $this->payResult($ret);
    }
    $orderid = pdo_fetchcolumn('select id from ' . tablename('sz_yi_order') . ' where ordersn=:ordersn and uniacid=:uniacid', array(
        ':ordersn' => $log['tid'],
        ':uniacid' => $_W['uniacid']
    ));
    $url     = $this->createMobileUrl('order/detail', array(
        'id' => $orderid
    ));
    die("<script>top.window.location.href='{$url}'</script>");
} else if ($operation == 'returnyunpay') {
	
    $tids = $_REQUEST['i2'];
	$strs          = explode(':', $tids);
	$tid=$strs [0];
    $pluginy = p('yunpay');
    if (!$pluginy->isYunpayNotify($_GET)) {
        die('支付出现错误，请重试!');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(
        ':uniacid' => $uniacid,
        ':module' => 'sz_yi',
        ':tid' => $tid
    ));
    if (empty($log)) {
        die('支付出现错误，请重试!');
    }
    if ($log['status'] != 1) {
        $record           = array();
        $record['status'] = '1';
        $record['type']   = 'yunpay';
        pdo_update('core_paylog', $record, array(
            'plid' => $log['plid']
        ));
        $ret            = array();
        $ret['result']  = 'success';
        $ret['type']    = 'yunpay';
        $ret['from']    = 'return';
        $ret['tid']     = $log['tid'];
        $ret['user']    = $log['openid'];
        $ret['fee']     = $log['fee'];
        $ret['weid']    = $log['weid'];
        $ret['uniacid'] = $log['uniacid'];
        $this->payResult($ret);
    }
    $orderid = pdo_fetchcolumn('select id from ' . tablename('sz_yi_order') . ' where ordersn=:ordersn and uniacid=:uniacid', array(
        ':ordersn' => $log['tid'],
        ':uniacid' => $_W['uniacid']
    ));
    $url     = $this->createMobileUrl('order/detail', array(
        'id' => $orderid
    ));
    die("<script>top.window.location.href='{$url}'</script>");
}
if ($operation == 'display') {
    include $this->template('order/pay');
}
