<?php

if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$type = intval($_GPC['type']);
if (empty($type)) {
    header('location: ' . $this->createPluginWebUrl('exhelper/express'));
    die;
}
$printset = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_exhelper_sys') . ' WHERE uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
if ($op == 'search') {
    if ($type == 1) {
        ca('exhelper.print.single');
    } elseif ($type == 2) {
        ca('exhelper.print.single');
    }
    $status = $_GPC['status'];
    $printstate = $_GPC['printstate'];
    $printstate2 = $_GPC['printstate2'];
    $sendtype = !isset($_GPC['sendtype']) ? 0 : $_GPC['sendtype'];
    $condition = ' o.uniacid = :uniacid and o.addressid<>0 and o.deleted=0';
    $paras = array(':uniacid' => $_W['uniacid']);
    if (empty($starttime) || empty($endtime)) {
        $starttime = strtotime('-1 month');
        $endtime = time();
    }
    if (!empty($_GPC['time'])) {
        $starttime = strtotime($_GPC['time']['start']);
        $endtime = strtotime($_GPC['time']['end']);
        if ($_GPC['searchtime'] == '1') {
            $condition .= ' AND o.createtime >= :starttime AND o.createtime <= :endtime ';
            $paras[':starttime'] = $starttime;
            $paras[':endtime'] = $endtime;
        }
    }
    if ($_GPC['paytype'] != '') {
        if ($_GPC['paytype'] == '2') {
            $condition .= ' AND ( o.paytype =21 or o.paytype=22 or o.paytype=23 )';
        } else {
            $condition .= ' AND o.paytype =' . intval($_GPC['paytype']);
        }
    }
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
        $condition .= " AND o.ordersn LIKE '%{$_GPC['keyword']}%'";
    }
    if (!empty($_GPC['expresssn'])) {
        $_GPC['expresssn'] = trim($_GPC['expresssn']);
        $condition .= " AND o.expresssn LIKE '%{$_GPC['expresssn']}%'";
    }
    if (!empty($_GPC['member'])) {
        $_GPC['member'] = trim($_GPC['member']);
        $condition .= " AND (m.realname LIKE '%{$_GPC['member']}%' or m.mobile LIKE '%{$_GPC['member']}%' or m.nickname LIKE '%{$_GPC['member']}%' " . " or a.realname LIKE '%{$_GPC['member']}%' or a.mobile LIKE '%{$_GPC['member']}%' or o.carrier LIKE '%{$_GPC['member']}%')";
    }
    if ($status != '') {
        if ($status == '-1') {
            $condition .= ' AND o.status=-1 and isnull(r.id)';
        } else {
            if ($status == '4') {
                $condition .= ' AND o.refundid<>0 and r.status=0';
            } else {
                if ($status == '5') {
                    $condition .= ' AND r.status=1';
                } else {
                    $condition .= ' AND o.status = \'' . intval($status) . '\'';
                }
            }
        }
    }
    if ($printstate != '') {
        $condition .= ' AND o.printstate=' . $printstate . ' ';
    }
    if ($printstate2 != '') {
        $condition .= ' AND o.printstate2=' . $printstate2 . ' ';
    }
    $sql = 'select o.* ,a.realname ,m.nickname, d.dispatchname,m.nickname,r.status as refundstatus from ' . tablename('sz_yi_order') . ' o' . ' left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id and ifnull(r.status,-1)<>-1' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid=o.openid ' . ' left join ' . tablename('sz_yi_member_address') . ' a on o.addressid = a.id ' . ' left join ' . tablename('sz_yi_dispatch') . ' d on d.id = o.dispatchid ' . " where {$condition} ORDER BY o.createtime DESC,o.status DESC  ";
    $orders = pdo_fetchall($sql, $paras);
    if ($type == 1) {
        $list = array();
        foreach ($orders as $order) {
            $order_address = iunserializer($order['address']);
            if (!is_array($order_address)) {
                $member_address = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_address') . ' WHERE id=:id and uniacid=:uniacid limit 1', array(':id' => $order['addressid'], ':uniacid' => $_W['uniacid']));
                $addresskey = $member_address['realname'] . $member_address['mobile'] . $member_address['province'] . $member_address['city'] . $member_address['area'] . $member_address['address'];
            } else {
                $addresskey = $order_address['realname'] . $order_address['mobile'] . $order_address['province'] . $order_address['city'] . $order_address['area'] . $order_address['address'];
            }
            if (!isset($list[$addresskey])) {
                $list[$addresskey] = array('realname' => $order['realname'], 'orderids' => array());
            }
            $list[$addresskey]['orderids'][] = $order['id'];
        }
        include $this->template('print_tpl');
    } elseif ($type == 2) {
        $totalmoney = 0;
        foreach ($orders as $i => $order) {
            $totalmoney = $totalmoney + $order['price'];
            $totalmoney = number_format($totalmoney, 2);
            $paytype = array('0' => array('css' => 'default', 'name' => '未支付'), '1' => array('css' => 'danger', 'name' => '余额支付'), '11' => array('css' => 'default', 'name' => '后台付款'), '2' => array('css' => 'danger', 'name' => '在线支付'), '21' => array('css' => 'success', 'name' => '微信支付'), '22' => array('css' => 'warning', 'name' => '支付宝支付'), '23' => array('css' => 'warning', 'name' => '银联支付'), '3' => array('css' => 'primary', 'name' => '货到付款'));
            $orderstatus = array('-1' => array('css' => 'default', 'name' => '已关闭'), '0' => array('css' => 'danger', 'name' => '待付款'), '1' => array('css' => 'info', 'name' => '待发货'), '2' => array('css' => 'warning', 'name' => '待收货'), '3' => array('css' => 'success', 'name' => '已完成'));
            $order_goods = pdo_fetchall('select g.id,g.title,g.shorttitle,g.thumb,g.unit,g.goodssn,og.optionid,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.printstate,og.id as ordergoodid from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $order['id']));
            foreach ($order_goods as $ii => $order_good) {
                if (!empty($order_good['optionid'])) {
                    $option = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_goods_option') . ' WHERE id=:id and uniacid=:uniacid limit 1', array(':id' => $order_good['optionid'], ':uniacid' => $_W['uniacid']));
                    $order_goods[$ii]['weight'] = $option['weight'];
                }
            }
            $order_goods = set_medias($order_goods, 'thumb');
            $p = $order['paytype'];
            $orders[$i]['goods'] = $order_goods;
            $orders[$i]['paytypename'] = $paytype[$p]['name'];
            $orders[$i]['css'] = $paytype[$p]['css'];
            $orders[$i]['dispatchname'] = empty($order['addressid']) ? '自提' : $order['dispatchname'];
            if (empty($orders[$i]['dispatchname'])) {
                $orders[$i]['dispatchname'] = '快递';
            }
            if ($order['isverify'] == 1) {
                $orders[i]['dispatchname'] = '线下核销';
            } else {
                if (!empty($order['virtual'])) {
                    $orders[$i]['dispatchname'] = '虚拟物品(卡密)<br/>自动发货';
                }
            }
            $s = $order['status'];
            $orders[$i]['statusvalue'] = $s;
            $orders[$i]['statuscss'] = $orderstatus[$s]['css'];
            $orders[$i]['status'] = $orderstatus[$s]['name'];
            if (!empty($order['address_send'])) {
                $orders[$i]['address'] = iunserializer($order['address_send']);
            } else {
                $orders[$i]['address'] = iunserializer($order['address']);
                $orders[$i]['address']['realname'] = $order['realname'];
            }
            $orders[$i]['address']['nickname'] = $order['nickname'];
        }
        include $this->template('print_tpl_detail');
    }
    die;
} elseif ($op == 'detail') {
    $orderids = trim($_GPC['orderids']);
    if (empty($orderids)) {
        die('无任何订单，无法查看');
    }
    $arr = explode(',', $orderids);
    if (empty($arr)) {
        die('无任何订单，无法查看');
    }
    $paytype = array('0' => array('css' => 'default', 'name' => '未支付'), '1' => array('css' => 'danger', 'name' => '余额支付'), '11' => array('css' => 'default', 'name' => '后台付款'), '2' => array('css' => 'danger', 'name' => '在线支付'), '21' => array('css' => 'success', 'name' => '微信支付'), '22' => array('css' => 'warning', 'name' => '支付宝支付'), '23' => array('css' => 'warning', 'name' => '银联支付'), '3' => array('css' => 'primary', 'name' => '货到付款'));
    $orderstatus = array('-1' => array('css' => 'default', 'name' => '已关闭'), '0' => array('css' => 'danger', 'name' => '待付款'), '1' => array('css' => 'info', 'name' => '待发货'), '2' => array('css' => 'warning', 'name' => '待收货'), '3' => array('css' => 'success', 'name' => '已完成'));
    $sql = 'select o.* , a.realname,a.mobile,a.province,a.city,a.area,a.address, d.dispatchname,m.nickname,r.status as refundstatus from ' . tablename('sz_yi_order') . ' o' . ' left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id and ifnull(r.status,-1)<>-1' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid=o.openid ' . ' left join ' . tablename('sz_yi_member_address') . ' a on o.addressid = a.id ' . ' left join ' . tablename('sz_yi_dispatch') . ' d on d.id = o.dispatchid ' . ' where o.id in ( ' . implode(',', $arr) . ") and o.uniacid={$_W['uniacid']} ORDER BY o.createtime DESC,o.status DESC  ";
    $list = pdo_fetchall($sql, $paras);
    foreach ($list as &$value) {
        $s = $value['status'];
        $value['statusvalue'] = $s;
        $value['statuscss'] = $orderstatus[$s]['css'];
        $value['status'] = $orderstatus[$s]['name'];
        if ($s == -1) {
            if ($value['refundstatus'] == 1) {
                $value['status'] = '已退款';
            }
        }
        $p = $value['paytype'];
        $value['css'] = $paytype[$p]['css'];
        $value['paytype'] = $paytype[$p]['name'];
        $value['dispatchname'] = empty($value['addressid']) ? '自提' : $value['dispatchname'];
        if (empty($value['dispatchname'])) {
            $value['dispatchname'] = '快递';
        }
        if ($value['isverify'] == 1) {
            $value['dispatchname'] = '线下核销';
        } else {
            if (!empty($value['virtual'])) {
                $value['dispatchname'] = '虚拟物品(卡密)<br/>自动发货';
            }
        }
        if (!empty($value['address_send'])) {
            $address = iunserializer($value['address_send']);
        } else {
            $address = iunserializer($value['address']);
        }
        if (is_array($address)) {
            $value['realname'] = $address['realname'];
            $value['mobile'] = $address['mobile'];
            $value['province'] = $address['province'];
            $value['city'] = $address['city'];
            $value['area'] = $address['area'];
            $value['address'] = $address['address'];
        }
        $value['address'] = array('realname' => $value['realname'], 'nickname' => $value['nickname'], 'mobile' => $value['mobile'], 'province' => $value['province'], 'city' => $value['city'], 'area' => $value['area'], 'address' => $value['address']);
        $order_goods = pdo_fetchall('select g.id,g.title,g.shorttitle,g.thumb,g.goodssn,og.optionid,g.unit,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.id as ordergoodid,og.printstate,og.printstate2 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $value['id']));
        foreach ($order_goods as $i => $order_good) {
            if (!empty($order_good['optionid'])) {
                $option = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_goods_option') . ' WHERE id=:id and uniacid=:uniacid limit 1', array(':id' => $order_good['optionid'], ':uniacid' => $_W['uniacid']));
                $order_goods[$i]['weight'] = $option['weight'];
            }
        }
        $goods = '';
        foreach ($order_goods as &$og) {
            $goods .= '' . $og['title'] . '

';
            if (!empty($og['optiontitle'])) {
                $goods .= ' 规格: ' . $og['optiontitle'];
            }
            if (!empty($og['option_goodssn'])) {
                $og['goodssn'] = $og['option_goodssn'];
            }
            if (!empty($og['option_productsn'])) {
                $og['productsn'] = $og['option_productsn'];
            }
            if (!empty($og['goodssn'])) {
                $goods .= ' 商品编号: ' . $og['goodssn'];
            }
            if (!empty($og['productsn'])) {
                $goods .= ' 商品条码: ' . $og['productsn'];
            }
            $goods .= ' 单价: ' . $og['price'] / $og['total'] . ' 折扣后: ' . $og['realprice'] / $og['total'] . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . ' 折扣后: ' . $og['realprice'] . '

 ';
        }
        unset($og);
        $value['goods'] = set_medias($order_goods, 'thumb');
        $value['goods_str'] = $goods;
    }
    unset($value);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_order') . ' o ' . ' left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id and ifnull(r.status,-1)<>-1' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid=o.openid  ' . ' left join ' . tablename('sz_yi_member_address') . ' a on o.addressid = a.id ' . ' WHERE o.id in ( ' . implode(',', $arr) . ") and o.uniacid={$_W['uniacid']}", $paras);
    $totalmoney = pdo_fetchcolumn('SELECT sum(o.price) FROM ' . tablename('sz_yi_order') . ' o ' . ' left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id and ifnull(r.status,-1)<>-1' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid=o.openid  ' . ' left join ' . tablename('sz_yi_member_address') . ' a on o.addressid = a.id ' . ' WHERE o.id in ( ' . implode(',', $arr) . ") and o.uniacid={$_W['uniacid']}", $paras);
    $address = false;
    if (!empty($list)) {
        $address = $list[0]['address'];
    }
    $address['sendinfo'] = '';
    $sendinfo = array();
    foreach ($list as $item) {
        foreach ($item['goods'] as $k => $g) {
            if (isset($sendinfo[$g['id']])) {
                $sendinfo[$g['id']]['num'] += $g['total'];
            } else {
                $sendinfo[$g['id']] = array('title' => empty($g['shorttitle']) ? $g['title'] : $g['shorttitle'], 'num' => $g['total'], 'optiontitle' => !empty($g['optiontitle']) ? '(' . $g['optiontitle'] . ')' : '');
            }
        }
    }
    $sendinfos = array();
    foreach ($sendinfo as $gid => $info) {
        $info['gid'] = $gid;
        $sendinfos[] = $info;
        $address['sendinfo'] .= $info['title'] . $info['optiontitle'] . ' x ' . $info['num'] . '; ';
    }
    include $this->template('print_tpl_detail');
    die;
} elseif ($op == 'shorttitle') {
    if ($_W['ispost']) {
        $gid = intval($_GPC['goodid']);
        $shorttitle = empty($_GPC['shorttitle']) ? '' : $_GPC['shorttitle'];
        if (!empty($gid)) {
            $do = pdo_update('sz_yi_goods', array('shorttitle' => $shorttitle), array('id' => $gid));
            $result = array('result' => 'success');
        } else {
            $result = array('result' => 'error', 'resp' => '提交参数错误！请刷新重试');
        }
    } else {
        $result = array('result' => 'error', 'resp' => '非法提交！');
    }
    die(json_encode($result));
} elseif ($op == 'pushdata') {
    if ($_W['ispost']) {
        $arr = $_GPC['arr'];
        $pt = $_GPC['pt'];
        if (empty($arr) || empty($pt)) {
            die(json_encode(array('result' => 'error', 'resp' => '数据错误')));
        }
        foreach ($arr as $i => $data) {
            $orderid = $data['orderid'];
            $ordergoodid = $data['ordergoodid'];
            $ordergood = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order_goods') . ' WHERE id=:id and uniacid=:uniacid limit 1', array('id' => $ordergoodid, ':uniacid' => $_W['uniacid']));
            if ($pt == 'print1') {
                pdo_update('sz_yi_order_goods', array('printstate' => $ordergood['printstate'] + 1), array('id' => $ordergood['id']));
                $orderprint = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_order_goods') . ' WHERE orderid=:orderid and printstate=0 and uniacid= :uniacid ', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid']));
            } elseif ($pt == 'print2') {
                pdo_update('sz_yi_order_goods', array('printstate2' => $ordergood['printstate2'] + 1), array('id' => $ordergood['id']));
                $orderprint = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_order_goods') . ' WHERE orderid=:orderid and printstate2=0 and uniacid= :uniacid ', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid']));
            }
            if ($orderprint == 0) {
                $printstatenum = 2;
            } else {
                $printstatenum = 1;
            }
            if ($pt == 'print1') {
                pdo_update('sz_yi_order', array('printstate' => $printstatenum), array('id' => $orderid));
            } elseif ($pt == 'print2') {
                pdo_update('sz_yi_order', array('printstate2' => $printstatenum), array('id' => $orderid));
            }
        }
        die(json_encode(array('result' => 'success', 'orderprintstate' => $printstatenum)));
    }
} elseif ($op == 'getPrintTemp') {
    if ($_W['ispost']) {
        $type = intval($_GPC['type']);
        if (empty($type)) {
            die(json_encode(array('result' => 'error', 'resp' => '加载模版错误! 请刷新重试。')));
        }
        $expSendTemp = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_exhelper_senduser') . ' WHERE isdefault=1 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
        $expTemp = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_exhelper_express') . ' WHERE type=:type and isdefault=1 and uniacid=:uniacid limit 1', array(':type' => $type, ':uniacid' => $_W['uniacid']));
        $shop_set = m('common')->getSysset('shop');
        $expDatas = json_decode($expTemp['datas'], true);
        $expTemp['shopname'] = $shop_set['name'];
        $repItems = array('sendername', 'sendertel', 'senderaddress', 'sendersign', 'sendertime', 'sendercode', 'sendercccc');
        $repDatas = array($expSendTemp['sendername'], $expSendTemp['sendertel'], $expSendTemp['senderaddress'], $expSendTemp['sendersign'], date('Y-m-d H:i'), $expSendTemp['sendercode'], $expSendTemp['sendercity']);
        if (!is_array($expDatas)) {
            die(json_encode(array('result' => 'error', 'resp' => '请先设置默认打印模版！')));
        }
        foreach ($expDatas as $index => $data) {
            $expDatas[$index]['items'] = str_replace($repItems, $repDatas, $data['items']);
        }
        die(json_encode(array('result' => 'success', 'respDatas' => $expDatas, 'respUser' => $expSendTemp, 'respTemp' => $expTemp)));
    }
} elseif ($op == 'getOrderState') {
    if ($_W['ispost']) {
        $ordersns = $_GPC['ordersns'];
        $type = $_GPC['type'];
        $arr = array();
        foreach ($ordersns as $ordersn) {
            $orderinfo = pdo_fetch('SELECT id,status,expresssn,expresscom FROM ' . tablename('sz_yi_order') . ' WHERE ordersn=:ordersn and status>-1 and uniacid=:uniacid limit 1', array('ordersn' => $ordersn, ':uniacid' => $_W['uniacid']));
            $arr[] = array('ordersn' => $ordersn, 'status' => $orderinfo['status'], 'expresssn' => $orderinfo['expresssn'], 'expresscom' => $orderinfo['expresscom']);
        }
        $printTemp = pdo_fetch('SELECT id,type,expressname,express,expresscom FROM ' . tablename('sz_yi_exhelper_express') . ' WHERE type=:type and isdefault=1 and uniacid=:uniacid limit 1', array(':type' => 1, ':uniacid' => $_W['uniacid']));
        die(json_encode(array('printTemp' => $printTemp, 'datas' => $arr)));
    }
} elseif ($op == 'dosend') {
    if ($_W['ispost']) {
        ca('exhelper.dosend');
        $ordersn = $_GPC['ordersn'];
        $express = $_GPC['express'];
        $expresssn = $_GPC['expresssn'];
        $expresscom = $_GPC['expresscom'];
        $orderinfo = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order') . ' WHERE ordersn=:ordersn and status>-1 and uniacid=:uniacid limit 1', array('ordersn' => $ordersn, ':uniacid' => $_W['uniacid']));
        if (empty($orderinfo)) {
            die(json_encode(array('result' => 'error', 'resp' => '订单不存在')));
        }
        if ($orderinfo['status'] == 1) {
            pdo_update('sz_yi_order', array('express' => trim($express), 'expresssn' => trim($expresssn), 'expresscom' => trim($expresscom), 'sendtime' => time(), 'status' => 2), array('ordersn' => $ordersn));
            if (!empty($orderinfo['refundid'])) {
                $refund = pdo_fetch('select * from ' . tablename('sz_yi_order_refund') . ' where id=:id limit 1', array(':id' => $orderinfo['refundid']));
                if (!empty($refund)) {
                    pdo_update('sz_yi_order_refund', array('status' => -1), array('id' => $orderinfo['refundid']));
                    pdo_update('sz_yi_order', array('refundid' => 0), array('id' => $orderinfo['id']));
                }
            }
            m('notice')->sendOrderMessage($orderinfo['id']);
            plog('order.op.send', "订单发货 ID: {$item['id']} 订单号: {$item['ordersn']} <br/>快递公司: {$_GPC['expresscom']} 快递单号: {$_GPC['expresssn']}");
            die(json_encode(array('result' => 'success')));
        }
    }
} elseif ($op == 'autonum') {
    $num = $_GPC['num'];
    $len = intval($_GPC['len']);
    $len == 0 && ($len = 1);
    $arr = array($num);
    $maxlen = strlen($num);
    for ($i = 1; $i <= $len; $i++) {
        $add = bcadd($num, $i) . '';
        $addlen = strlen($add);
        if ($addlen > $maxlen) {
            $maxlen = $addlen;
        }
        $arr[] = $add;
    }
    $len = count($arr);
    for ($i = 0; $i < $len; $i++) {
        $zerocount = $maxlen - strlen($arr[$i]);
        if ($zerocount > 0) {
            $arr[$i] = str_pad($arr[$i], $maxlen, '0', STR_PAD_LEFT);
        }
    }
    die(json_encode($arr));
} elseif ($op == 'saveaddress') {
    if ($_W['ispost']) {
        $ordersns = $_GPC['ordersns'];
        $address = array('realname' => $_GPC['realname'], 'mobile' => $_GPC['mobile'], 'province' => $_GPC['province'], 'city' => $_GPC['city'], 'area' => $_GPC['area'], 'address' => $_GPC['address']);
        $address_send = iserializer($address);
        if (empty($ordersns)) {
            die(json_encode(array('result' => 'error', 'resp' => '订单数据为空')));
        }
        foreach ($ordersns as $ordersn) {
            pdo_update('sz_yi_order', array('address_send' => $address_send), array('ordersn' => $ordersn));
        }
        die;
    }
}
load()->func('tpl');
include $this->template('doprint');