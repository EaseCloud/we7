<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Sz_DYi_Order
{
    function getDispatchPrice($_var_0, $_var_1, $_var_2 = -1)
    {
        if (empty($_var_1)) {
            return 0;
        }
        $_var_3 = 0;
        if ($_var_2 == -1) {
            $_var_2 = $_var_1['calculatetype'];
        }
        if ($_var_2 == 1) {
            if ($_var_0 <= $_var_1['firstnum']) {
                $_var_3 = floatval($_var_1['firstnumprice']);
            } else {
                $_var_3 = floatval($_var_1['firstnumprice']);
                $_var_4 = $_var_0 - floatval($_var_1['firstnum']);
                $_var_5 = floatval($_var_1['secondnum']) <= 0 ? 1 : floatval($_var_1['secondnum']);
                $_var_6 = 0;
                if ($_var_4 % $_var_5 == 0) {
                    $_var_6 = $_var_4 / $_var_5 * floatval($_var_1['secondnumprice']);
                } else {
                    $_var_6 = ((int)($_var_4 / $_var_5) + 1) * floatval($_var_1['secondnumprice']);
                }
                $_var_3 += $_var_6;
            }
        } else {
            if ($_var_0 <= $_var_1['firstweight']) {
                $_var_3 = floatval($_var_1['firstprice']);
            } else {
                $_var_3 = floatval($_var_1['firstprice']);
                $_var_4 = $_var_0 - floatval($_var_1['firstweight']);
                $_var_5 = floatval($_var_1['secondweight']) <= 0 ? 1 : floatval($_var_1['secondweight']);
                $_var_6 = 0;
                if ($_var_4 % $_var_5 == 0) {
                    $_var_6 = $_var_4 / $_var_5 * floatval($_var_1['secondprice']);
                } else {
                    $_var_6 = ((int)($_var_4 / $_var_5) + 1) * floatval($_var_1['secondprice']);
                }
                $_var_3 += $_var_6;
            }
        }
        return $_var_3;
    }

    function getCityDispatchPrice($_var_7, $_var_8, $_var_0, $_var_1)
    {
        if (is_array($_var_7) && count($_var_7) > 0) {
            foreach ($_var_7 as $_var_9) {
                $_var_10 = explode(';', $_var_9['citys']);
                if (in_array($_var_8, $_var_10) && !empty($_var_10)) {
                    return $this->getDispatchPrice($_var_0, $_var_9, $_var_1['calculatetype']);
                }
            }
        }
        return $this->getDispatchPrice($_var_0, $_var_1);
    }

    public function payResult($_var_11)
    {
        global $_W;
        $_var_12 = $_var_11['fee'];
        $_var_13 = array('status' => $_var_11['result'] == 'success' ? 1 : 0);
        $_var_14 = $_var_11['tid'];
        $_var_15 = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where  ordersn=:ordersn and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':ordersn' => $_var_14));
        $_var_16 = pdo_fetch('select * from ' . tablename('core_paylog') . ' where `uniacid`=:uniacid and fee=:fee and `module`=:module and `tid`=:tid limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'sz_yi', ':fee' => $_var_12, ':tid' => $_var_15['ordersn']));
        if (empty($_var_16)) {
            show_json(-1, '订单金额错误, 请重试!');
            die;
        }
        $_var_17 = $_var_15['id'];
        if ($_var_11['from'] == 'return') {
            $_var_18 = false;
            if (empty($_var_15['dispatchtype'])) {
                $_var_18 = pdo_fetch('select realname,mobile,address from ' . tablename('sz_yi_member_address') . ' where id=:id limit 1', array(':id' => $_var_15['addressid']));
            }
            $_var_19 = false;
            if ($_var_15['dispatchtype'] == 1 || $_var_15['isvirtual'] == 1) {
                $_var_19 = unserialize($_var_15['carrier']);
            }
            if ($_var_11['type'] == 'cash') {
                return array('result' => 'success', 'order' => $_var_15, 'address' => $_var_18, 'carrier' => $_var_19);
            } else {
                if ($_var_15['status'] == 0) {
                    $_var_20 = p('virtual');
                    if (!empty($_var_15['virtual']) && $_var_20) {
                        $_var_20->pay($_var_15);
                    } else {
                        pdo_update('sz_yi_order', array('status' => 1, 'paytime' => time()), array('id' => $_var_17));
                        if ($_var_15['deductcredit2'] > 0) {
                            $_var_21 = m('common')->getSysset('shop');
                            m('member')->setCredit($_var_15['openid'], 'credit2', -$_var_15['deductcredit2'], array(0, $_var_21['name'] . "余额抵扣: {$_var_15['deductcredit2']} 订单号: " . $_var_15['ordersn']));
                        }
                        $this->setStocksAndCredits($_var_17, 1);
                        if (p('coupon') && !empty($_var_15['couponid'])) {
                            p('coupon')->backConsumeCoupon($_var_15['id']);
                        }
                        m('notice')->sendOrderMessage($_var_17);
                        if (p('commission')) {
                            p('commission')->checkOrderPay($_var_15['id']);
                        }
                    }
                }
                if (p('supplier')) {
                    p('supplier')->order_split($_var_17);
                }
                $_var_22 = pdo_fetch('select o.dispatchprice,o.ordersn,o.price,og.optionname as optiontitle,og.optionid,og.total from ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_order_goods') . 'og on og.orderid = o.id  where o.id = :id and o.uniacid=:uniacid', array(':id' => $_var_17, ':uniacid' => $_W['uniacid']));
                $_var_23 = 'SELECT og.goodsid,og.total,g.title,g.thumb,og.price,og.optionname as optiontitle,og.optionid FROM ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on og.goodsid = g.id ' . ' where og.orderid=:orderid order by og.id asc';
                $_var_22['goods1'] = set_medias(pdo_fetchall($_var_23, array(':orderid' => $_var_17)), 'thumb');
                $_var_22['goodscount'] = count($_var_22['goods1']);
                return array('result' => 'success', 'order' => $_var_15, 'address' => $_var_18, 'carrier' => $_var_19, 'virtual' => $_var_15['virtual'], 'goods' => $_var_22);
            }
        }
    }

    function setStocksAndCredits($_var_17 = '', $_var_24 = 0)
    {
        global $_W;
        $_var_15 = pdo_fetch('select id,ordersn,price,openid,dispatchtype,addressid,carrier,status from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $_var_17));
        $_var_25 = pdo_fetchall('select og.goodsid,og.total,g.totalcnf,og.realprice, g.credit,og.optionid,g.total as goodstotal,og.optionid,g.sales,g.salesreal from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.orderid=:orderid and og.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_17));
        $_var_26 = 0;
        foreach ($_var_25 as $_var_27) {
            $_var_28 = 0;
            if ($_var_24 == 0) {
                if ($_var_27['totalcnf'] == 0) {
                    $_var_28 = -1;
                }
            } else {
                if ($_var_24 == 1) {
                    if ($_var_27['totalcnf'] == 1) {
                        $_var_28 = -1;
                    }
                } else {
                    if ($_var_24 == 2) {
                        if ($_var_15['status'] >= 1) {
                            if ($_var_27['totalcnf'] == 1) {
                                $_var_28 = 1;
                            }
                        } else {
                            if ($_var_27['totalcnf'] == 0) {
                                $_var_28 = 1;
                            }
                        }
                    }
                }
            }
            if (!empty($_var_28)) {
                if (!empty($_var_27['optionid'])) {
                    $_var_29 = m('goods')->getOption($_var_27['goodsid'], $_var_27['optionid']);
                    if (!empty($_var_29) && $_var_29['stock'] != -1) {
                        $_var_30 = -1;
                        if ($_var_28 == 1) {
                            $_var_30 = $_var_29['stock'] + $_var_27['total'];
                        } else {
                            if ($_var_28 == -1) {
                                $_var_30 = $_var_29['stock'] - $_var_27['total'];
                                $_var_30 <= 0 && ($_var_30 = 0);
                            }
                        }
                        if ($_var_30 != -1) {
                            pdo_update('sz_yi_goods_option', array('stock' => $_var_30), array('uniacid' => $_W['uniacid'], 'goodsid' => $_var_27['goodsid'], 'id' => $_var_27['optionid']));
                        }
                    }
                }
                if (!empty($_var_27['goodstotal']) && $_var_27['goodstotal'] != -1) {
                    $_var_31 = -1;
                    if ($_var_28 == 1) {
                        $_var_31 = $_var_27['goodstotal'] + $_var_27['total'];
                    } else {
                        if ($_var_28 == -1) {
                            $_var_31 = $_var_27['goodstotal'] - $_var_27['total'];
                            $_var_31 <= 0 && ($_var_31 = 0);
                        }
                    }
                    if ($_var_31 != -1) {
                        pdo_update('sz_yi_goods', array('total' => $_var_31), array('uniacid' => $_W['uniacid'], 'id' => $_var_27['goodsid']));
                    }
                }
            }
            $_var_32 = trim($_var_27['credit']);
            if (!empty($_var_32)) {
                if (strexists($_var_32, '%')) {
                    $_var_26 += intval(floatval(str_replace('%', '', $_var_32)) / 100 * $_var_27['realprice']);
                } else {
                    $_var_26 += intval($_var_27['credit']) * $_var_27['total'];
                }
            }
            if ($_var_24 == 0) {
                pdo_update('sz_yi_goods', array('sales' => $_var_27['sales'] + $_var_27['total']), array('uniacid' => $_W['uniacid'], 'id' => $_var_27['goodsid']));
            } elseif ($_var_24 == 1) {
                if ($_var_15['status'] >= 1) {
                    $_var_33 = pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where og.goodsid=:goodsid and o.status>=1 and o.uniacid=:uniacid limit 1', array(':goodsid' => $_var_27['goodsid'], ':uniacid' => $_W['uniacid']));
                    pdo_update('sz_yi_goods', array('salesreal' => $_var_33), array('id' => $_var_27['goodsid']));
                }
            }
        }
        if ($_var_26 > 0) {
            $_var_21 = m('common')->getSysset('shop');
            if ($_var_24 == 1) {
                m('member')->setCredit($_var_15['openid'], 'credit1', $_var_26, array(0, $_var_21['name'] . '购物积分 订单号: ' . $_var_15['ordersn']));
            } elseif ($_var_24 == 2) {
                if ($_var_15['status'] >= 1) {
                    m('member')->setCredit($_var_15['openid'], 'credit1', -$_var_26, array(0, $_var_21['name'] . '购物取消订单扣除积分 订单号: ' . $_var_15['ordersn']));
                }
            }
        }
    }

    function getDefaultDispatch()
    {
        global $_W;
        $_var_34 = 'select * from ' . tablename('sz_yi_dispatch') . ' where isdefault=1 and uniacid=:uniacid and enabled=1 Limit 1';
        $_var_35 = array(':uniacid' => $_W['uniacid']);
        $_var_36 = pdo_fetch($_var_34, $_var_35);
        return $_var_36;
    }

    function getNewDispatch()
    {
        global $_W;
        $_var_34 = 'select * from ' . tablename('sz_yi_dispatch') . ' where uniacid=:uniacid and enabled=1 order by id desc Limit 1';
        $_var_35 = array(':uniacid' => $_W['uniacid']);
        $_var_36 = pdo_fetch($_var_34, $_var_35);
        return $_var_36;
    }

    function getOneDispatch($_var_37)
    {
        global $_W;
        $_var_34 = 'select * from ' . tablename('sz_yi_dispatch') . ' where id=:id and uniacid=:uniacid and enabled=1 Limit 1';
        $_var_35 = array(':id' => $_var_37, ':uniacid' => $_W['uniacid']);
        $_var_36 = pdo_fetch($_var_34, $_var_35);
        return $_var_36;
    }
}