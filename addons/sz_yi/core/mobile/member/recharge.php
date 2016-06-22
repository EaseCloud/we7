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
$uniacid = $_W['uniacid'];
if ($operation == 'display' && $_W['isajax']) {
    $set = m('common')->getSysset(array(
        'shop',
        'pay',
        'trade'
    ));
    if (!empty($set['trade']['closerecharge'])) {
        show_json(-1, '系统未开启账户充值!');
    }
    pdo_delete('sz_yi_member_log', array(
        'openid' => $openid,
        'status' => 0,
        'type' => 0,
        'uniacid' => $_W['uniacid']
    ));
    $logno = m('common')->createNO('member_log', 'logno', 'RC');
    $log   = array(
        'uniacid' => $_W['uniacid'],
        'logno' => $logno,
        'title' => $set['shop']['name'] . "会员充值",
        'openid' => $openid,
        'type' => 0,
        'createtime' => time(),
        'status' => 0
    );
    pdo_insert('sz_yi_member_log', $log);
    $logid  = pdo_insertid();
    $credit = m('member')->getCredit($openid, 'credit2');
    $wechat = array(
        'success' => false
    );
    if (is_weixin()) {
        if (isset($set['pay']) && $set['pay']['weixin'] == 1) {
            load()->model('payment');
            $setting = uni_setting($_W['uniacid'], array(
                'payment'
            ));
            if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
                $wechat['success'] = true;
            }
        }
    }
    $alipay = array(
        'success' => false
    );
    if (isset($set['pay']['alipay']) && $set['pay']['alipay'] == 1) {
        load()->model('payment');
        $setting = uni_setting($_W['uniacid'], array(
            'payment'
        ));
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

    show_json(1, array(
        'set' => $set,
        'logid' => $logid,
        'isweixin' => is_weixin(),
        'wechat' => $wechat,
        'alipay' => $alipay,
        'credit' => $credit,
        'yunpay' => $yunpay
    ));
} else if ($operation == 'recharge' && $_W['ispost']) {
    $logid = intval($_GPC['logid']);
    if (empty($logid)) {
        show_json(0, '充值出错, 请重试!');
    }
    $money = floatval($_GPC['money']);
    if (empty($money)) {
        show_json(0, '请填写充值金额!');
    }
    $type = $_GPC['type'];
    if (!in_array($type, array(
        'weixin',
        'alipay',
        'yunpay'
    ))) {
        show_json(0, '未找到支付方式');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `id`=:id and `uniacid`=:uniacid limit 1', array(
        ':uniacid' => $uniacid,
        ':id' => $logid
    ));
    if (empty($log)) {
        show_json(0, '充值出错, 请重试!');
    }
    pdo_update('sz_yi_member_log', array(
        'money' => $money
    ), array(
        'id' => $log['id']
    ));
    $set = m('common')->getSysset(array(
        'shop',
        'pay'
    ));
    if ($type == 'weixin') {
        if (!is_weixin()) {
            show_json(0, '非微信环境!');
        }
        if (empty($set['pay']['weixin'])) {
            show_json(0, '未开启微信支付!');
        }
        $wechat          = array(
            'success' => false
        );
        $params          = array();
        $params['tid']   = $log['logno'];
        $params['user']  = $openid;
        $params['fee']   = $money;
        $params['title'] = $log['title'];
        load()->model('payment');
        $setting = uni_setting($_W['uniacid'], array(
            'payment'
        ));
        if (is_array($setting['payment'])) {
            $options           = $setting['payment']['wechat'];
            $options['appid']  = $_W['account']['key'];
            $options['secret'] = $_W['account']['secret'];
            $wechat            = m('common')->wechat_build($params, $options, 1);
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
        show_json(1, array(
            'wechat' => $wechat
        ));
    } else if ($type == 'alipay') {
        show_json(1);
    } else if ($type == 'yunpay') {
        show_json(1);
    }
} else if ($operation == 'complete' && $_W['ispost']) {
    $logid = intval($_GPC['logid']);
    $log   = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `id`=:id and `uniacid`=:uniacid limit 1', array(
        ':uniacid' => $uniacid,
        ':id' => $logid
    ));
    if (!empty($log) && empty($log['status'])) {
        $payquery = m('finance')->isWeixinPay($log['logno']);
        if (!is_error($payquery)) {
            pdo_update('sz_yi_member_log', array(
                'status' => 1,
                'rechargetype' => $_GPC['type']
            ), array(
                'id' => $logid
            ));
            m('member')->setCredit($openid, 'credit2', $log['money']);
            m('member')->setRechargeCredit($openid, $log['money']);
            if (p('sale')) {
                p('sale')->setRechargeActivity($log);
            }
            m('notice')->sendMemberLogMessage($logid);
        }
    }
    show_json(1);
} else if ($operation == 'return') {
    $logno     = trim($_GPC['out_trade_no']);
    $notify_id = trim($_GPC['notify_id']);
    $sign      = trim($_GPC['sign']);
    if (empty($logno)) {
        die('充值出现错误，请重试!');
    }
    if (!m('finance')->isAlipayNotify($_GET)) {
        die('充值出现错误，请重试!');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `logno`=:logno and `uniacid`=:uniacid limit 1', array(
        ':uniacid' => $uniacid,
        ':logno' => $logno
    ));
    if (!empty($log) && empty($log['status'])) {
        pdo_update('sz_yi_member_log', array(
            'status' => 1,
            'rechargetype' => 'alipay'
        ), array(
            'id' => $log['id']
        ));
        m('member')->setCredit($openid, 'credit2', $log['money']);
        m('member')->setRechargeCredit($openid, $log['money']);
        if (p('sale')) {
            p('sale')->setRechargeActivity($log);
        }
        m('notice')->sendMemberLogMessage($log['id']);
    }
    $url = $this->createMobileUrl('member');
    die("<script>top.window.location.href='{$url}'</script>");
}else if ($operation == 'returnyunpay') {
    $lognos = $_REQUEST['i2'];
	$strs          = explode(':', $lognos);
	$logno=$strs [0];
    if (empty($logno)) {
        die('充值出现错误，请重试!');
    }
    if (!m('finance')->isYunpayNotify($_GET)) {
        die('充值出现错误，请重试!');
    }
    $log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `logno`=:logno and `uniacid`=:uniacid limit 1', array(
        ':uniacid' => $uniacid,
        ':logno' => $logno
    ));
    if (!empty($log) && empty($log['status'])) {
        pdo_update('sz_yi_member_log', array(
            'status' => 1,
            'rechargetype' => 'yunpay'
        ), array(
            'id' => $log['id']
        ));
        m('member')->setCredit($openid, 'credit2', $log['money']);
        m('member')->setRechargeCredit($openid, $log['money']);
        if (p('sale')) {
            p('sale')->setRechargeActivity($log);
        }
        m('notice')->sendMemberLogMessage($log['id']);
    }
    $url = $this->createMobileUrl('member');
    die("<script>top.window.location.href='{$url}'</script>");
}

if ($operation == 'display') {
    include $this->template('member/recharge');
}

