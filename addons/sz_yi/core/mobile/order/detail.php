<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation      = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid         = m('user')->getOpenid();
$uniacid        = $_W['uniacid'];
$orderid        = intval($_GPC['id']);
$diyform_plugin = p('diyform');
$order          = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(
    ':id' => $orderid,
    ':uniacid' => $uniacid,
    ':openid' => $openid
));
if (!empty($order)) {
    $order['virtual_str'] = str_replace("\n", "<br/>", $order['virtual_str']);
    $diyformfields        = "";
    if ($diyform_plugin) {
        $diyformfields = ",og.diyformfields,og.diyformdata";
    }
    $goods        = pdo_fetchall("select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids{$diyformfields}  from " . tablename('sz_yi_order_goods') . " og " . " left join " . tablename('sz_yi_goods') . " g on g.id=og.goodsid " . " where og.orderid=:orderid and og.uniacid=:uniacid ", array(
        ':uniacid' => $uniacid,
        ':orderid' => $orderid
    ));
    $show         = 1;
    $diyform_flag = 0;
    foreach ($goods as &$g) {
        $g['thumb'] = tomedia($g['thumb']);
        if ($diyform_plugin) {
            $diyformdata   = iunserializer($g['diyformdata']);
            $fields        = iunserializer($g['diyformfields']);
            $diyformfields = array();
            foreach ($fields as $key => $value) {
                $tp_value = "";
                $tp_css   = "";
                if ($value['data_type'] == 1 || $value['data_type'] == 3) {
                    $tp_css .= " dline1";
                }
                if ($value['data_type'] == 5) {
                    $tp_css .= " dline2";
                }
                if ($value['data_type'] == 0 || $value['data_type'] == 1 || $value['data_type'] == 2 || $value['data_type'] == 6 || $value['data_type'] == 7) {
                    $tp_value = str_replace("\n", "<br/>", $diyformdata[$key]);
                } else if ($value['data_type'] == 3 || $value['data_type'] == 8) {
                    if (is_array($diyformdata[$key])) {
                        foreach ($diyformdata[$key] as $k1 => $v1) {
                            $tp_value .= $v1 . " ";
                        }
                    }
                } else if ($value['data_type'] == 5) {
                    if (is_array($diyformdata[$key])) {
                        foreach ($diyformdata[$key] as $k1 => $v1) {
                            $tp_value .= "<img style='height:25px;padding:1px;border:1px solid #ccc'  src='" . tomedia($v1) . "'/>";
                        }
                    }
                } else if ($value['data_type'] == 9) {
                    $tp_value = ($diyformdata[$key]['province'] != '请选择省份' ? $diyformdata[$key]['province'] : '') . " - " . ($diyformdata[$key]['city'] != '请选择城市' ? $diyformdata[$key]['city'] : '');
                }
                $diyformfields[] = array(
                    'tp_name' => $value['tp_name'],
                    "tp_value" => $tp_value,
                    'tp_css' => $tp_css
                );
            }
            $g['diyformfields'] = $diyformfields;
            $g['diyformdata']   = $diyformdata;
            if (!empty($g['diyformdata'])) {
                $diyform_flag = 1;
            }
        } else {
            $g['diyformfields'] = array();
            $g['diyformdata']   = array();
        }
        unset($g);
    }
}
if ($_W['isajax']) {
    if (empty($order)) {
        show_json(0);
    }
    $order['virtual_str']     = str_replace("\n", "<br/>", $order['virtual_str']);
    $order['goodstotal']      = count($goods);
    $order['finishtimevalue'] = $order['finishtime'];
    $order['finishtime']      = date('Y-m-d H:i:s', $order['finishtime']);
    $address                  = false;
    $carrier                  = false;
    $stores                   = array();
    if ($order['isverify'] == 1) {
        $storeids = array();
        foreach ($goods as $g) {
            if (!empty($g['storeids'])) {
                $storeids = array_merge(explode(',', $g['storeids']), $storeids);
            }
        }
        if (empty($storeids)) {
            $stores = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where  uniacid=:uniacid and status=1', array(
                ':uniacid' => $_W['uniacid']
            ));
        } else {
            $stores = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where id in (' . implode(',', $storeids) . ') and uniacid=:uniacid and status=1', array(
                ':uniacid' => $_W['uniacid']
            ));
        }
    } else {
        if ($order['dispatchtype'] == 0) {
            $address = iunserializer($order['address']);
            if (!is_array($address)) {
                $address = pdo_fetch('select realname,mobile,address from ' . tablename('sz_yi_member_address') . ' where id=:id limit 1', array(
                    ':id' => $order['addressid']
                ));
            }
        }
    }
    if ($order['dispatchtype'] == 1 || $order['isverify'] == 1 || !empty($order['virtual'])) {
        $carrier = unserialize($order['carrier']);
    }
    $set       = set_medias(m('common')->getSysset('shop'), 'logo');
    $canrefund = false;
    if ($order['status'] == 1) {
        $canrefund = true;
    } else if ($order['status'] == 3) {
        if ($order['isverify'] != 1 && empty($order['virtual'])) {
            $tradeset   = m('common')->getSysset('trade');
            $refunddays = intval($tradeset['refunddays']);
            if ($refunddays > 0) {
                $days = intval((time() - $order['finishtimevalue']) / 3600 / 24);
                if ($days <= $refunddays) {
                    $canrefund = true;
                }
            }
        }
    }
    $order['canrefund'] = $canrefund;
    show_json(1, array(
        'order' => $order,
        'goods' => $goods,
        'address' => $address,
        'carrier' => $carrier,
        'stores' => $stores,
        'isverify' => $isverify,
        'set' => $set
    ));
}
include $this->template('order/detail');

