<?php

//decode by QQ:270656184 http://www.yunlu99.com/
global $_W, $_GPC;
$sets = pdo_fetchall('select uniacid from ' . tablename('sz_yi_sysset'));
foreach ($sets as $set) {
    $_W['uniacid'] = $set['uniacid'];
    if (empty($_W['uniacid'])) {
        continue;
    }
    $trade = m('common')->getSysset('trade', $_W['uniacid']);
    $days = intval($trade['receive']);
    if ($days <= 0) {
        continue;
    }
    $daytimes = 86400 * $days;
    $p = p('commission');
    $pcoupon = p('coupon');
    $orders = pdo_fetchall('select id,couponid from ' . tablename('sz_yi_order') . " where uniacid={$_W['uniacid']} and status=2 and sendtime + {$daytimes} <=unix_timestamp() ", array(), 'id');
    if (!empty($orders)) {
        $orderkeys = array_keys($orders);
        $orderids = implode(',', $orderkeys);
        if (!empty($orderids)) {
            pdo_query('update ' . tablename('sz_yi_order') . ' set status=3,finishtime=' . time() . ' where id in (' . $orderids . ')');
            foreach ($orders as $orderid => $o) {
                m('notice')->sendOrderMessage($orderid);
                if ($pcoupon) {
                    if (!empty($o['couponid'])) {
                        $pcoupon->backConsumeCoupon($o['id']);
                    }
                }
                if ($p) {
                    $p->checkOrderFinish($orderid);
                }
            }
        }
    }
}
$pbonus = p('bonus');
if (!empty($pbonus)) {
    foreach ($sets as $set) {
        $_W['uniacid'] = $set['uniacid'];
        if (empty($_W['uniacid'])) {
            continue;
        }
        $daytime = strtotime(date('Y-m-d 00:00:00'));
        $set = $pbonus->getSet();
        $bonus = pdo_fetch('select id from ' . tablename('sz_yi_bonus') . ' where uniacid=' . $_W['uniacid']);
        $isbonus = empty($bonus) ? 1 : 0;
        $bonuslog = pdo_fetch('select id from ' . tablename('sz_yi_bonus') . ' where utime<' . $daytime . ' and uniacid=' . $_W['uniacid']);
        if (!empty($isbonus) || !empty($bonuslog)) {
            $pbonus->autosend();
            $pbonus->autosendall();
        }
    }
}
echo 'ok...';