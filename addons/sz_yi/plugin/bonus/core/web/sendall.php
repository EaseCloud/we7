<?php
global $_W, $_GPC;
ca('bonus.sendall');
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$set = $this->getSet();
$time             = time();
$pindex    = max(1, intval($_GPC['page']));
$psize     = 20;
$day_times        = intval($set['settledaysdf']) * 3600 * 24;
$daytime = strtotime(date("Y-m-d 00:00:00"));
if(empty($set['sendmonth'])){
    $stattime = $daytime - 86400;
    $endtime = $daytime - 1;
}else if($set['sendmonth'] == 1){
    $stattime = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
    $endtime = mktime(0, 0, 0, date('m'), 1, date('Y')) - 1;
}

$sql = "select sum(o.price) from ".tablename('sz_yi_order')." o left join " . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and  o.finishtime >={$stattime} and o.finishtime < {$endtime}  ORDER BY o.finishtime DESC,o.status DESC";
$ordermoney = pdo_fetchcolumn($sql);
$ordermoney = floatval($ordermoney);
$premierlevels = pdo_fetchall("select * from ".tablename('sz_yi_bonus_level')." where uniacid={$_W['uniacid']} and premier=1");
$levelmoneys = array();
$totalmoney = 0;
foreach ($premierlevels as $key => $value) {
    $leveldcount = pdo_fetchcolumn("select count(*) from ".tablename('sz_yi_member')." where uniacid={$_W['uniacid']} and bonuslevel=".$value['id']." and bonus_status = 1");
    if($leveldcount>0){
        $levelmembermoney = round($ordermoney*$value['pcommission']/100,2);
        if($levelmembermoney > 0){
            $membermoney = round($levelmembermoney/$leveldcount,2);
            if($membermoney > 0){
                $levelmoneys[$value['id']] = $membermoney;
                $totalmoney += $membermoney;
            }
        }
    }
}

$sql = "select count(*) from ".tablename('sz_yi_member')." m left join " . tablename('sz_yi_bonus_level') . " l on m.bonuslevel=l.id and m.bonus_status=1 where 1 and l.premier=1 and m.uniacid={$_W['uniacid']}";
$total = pdo_fetchcolumn($sql);
$sql = "select m.* from ".tablename('sz_yi_member')." m left join " . tablename('sz_yi_bonus_level') . " l on m.bonuslevel=l.id and m.bonus_status=1 where 1 and l.premier=1 and m.uniacid={$_W['uniacid']}";
$setshop = m('common')->getSysset('shop');
if (empty($_GPC['export'])) {
    $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
}

$list = pdo_fetchall($sql);

foreach ($list as $key => &$row) {
    $bonuspremier = $this->model->premierInfo($row['openid'], array('ok', 'pay', 'myorder'));
    //Author:ym Date:2016-04-08 Content:需消费一定金额，否则清除该用户不参与分红
    if($bonuspremier['myordermoney'] < $set['consume_withdraw']){
        unset($list[$key]);
    }else{
        $level = pdo_fetch("select id, levelname from " . tablename('sz_yi_bonus_level') . " where id=".$row['bonuslevel']);
        $row['levelname'] = $level['levelname'];
        $row['commission_ok'] = $levelmoneys[$level['id']];
        $row['commission_pay'] = number_format($bonuspremier['commission_pay'],2);  
    }
}
unset($row);
$send_bonus_sn = time();
$sendpay_error = 0;
$bonus_money = 0;
if (!empty($_POST)) {
    if($totalmoney<=0){
        message("总，不能发放", '', "success");
    }
    foreach ($list as $key => $value) {
        $send_money = $value['commission_ok'];
        $sendpay = 1;
        if(empty($set['paymethod'])){
            m('member')->setCredit($value['openid'], 'credit2', $send_money);
        }else{
            $logno = m('common')->createNO('bonus_log', 'logno', 'RB');
            $result = m('finance')->pay($value['openid'], 1, $send_money * 100, $logno, "【" . $setshop['name']. "】".$value['levelname']."分红");
            if (is_error($result)) {
                $sendpay = 0;
                $sendpay_error = 1;
            }
        }
        pdo_insert('sz_yi_bonus_log', array(
            "openid" => $value['openid'],
            "uid" => $value['uid'],
            "money" => $send_money,
            "uniacid" => $_W['uniacid'],
            "paymethod" => $set['paymethod'],
            "sendpay" => $sendpay,
            "isglobal" => 1,
            "status" => 1,
            "ctime" => time(),
            "send_bonus_sn" => $send_bonus_sn
        ));
        if($sendpay == 1){
            $this->model->sendMessage($member['openid'], array('nickname' => $value['nickname'], 'levelname' => $value['levelname'], 'commission' => $send_money, 'type' => empty($set['paymethod']) ? "余额" : "微信钱包"), TM_BONUS_GLOPAL_PAY);
        }
    }
    $log = array(
            "uniacid" => $_W['uniacid'],
            "money" => $totalmoney,
            "status" => 1,
            "ctime" => time(),
            "paymethod" => $set['paymethod'],
            "sendpay_error" => $sendpay_error,
            "isglobal" => 1,
            'utime' => $daytime,
            "send_bonus_sn" => $send_bonus_sn,
            "total" => $total
            );
    pdo_insert('sz_yi_bonus', $log);
    message("全球分红发放成功", $this->createPluginWebUrl('bonus/detail', array("sn" => $send_bonus_sn)), "success");
}
include $this->template('sendall');
