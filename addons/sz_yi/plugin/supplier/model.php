<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
define('TM_SUPPLIER_PAY', 'supplier_pay');
if (!class_exists('SupplierModel')) {
    class SupplierModel extends PluginModel
    {
        public $parentAgents = "";

        public function getInfo($_var_0, $_var_1 = null)
        {
            if (empty($_var_1) || !is_array($_var_1)) {
                $_var_1 = array();
            }
            global $_W;
            $_var_2 = $this->getSet();
            $_var_3 = intval($_var_2['level']);
            $_var_4 = m('member')->getMember($_var_0);
            $_var_5 = $this->getLevel($_var_0);
            $_var_6 = time();
            $_var_7 = intval($_var_2['settledays']) * 3600 * 24;
            $_var_8 = 0;
            $_var_9 = 0;
            $_var_10 = 0;
            $_var_11 = 0;
            $_var_12 = 0;
            $_var_13 = 0;
            $_var_14 = 0;
            $_var_15 = 0;
            $_var_16 = 0;
            $_var_17 = 0;
            $_var_18 = 0;
            $_var_19 = 0;
            $_var_20 = 0;
            $_var_21 = 0;
            $_var_22 = 0;
            $_var_23 = 0;
            $_var_24 = 0;
            $_var_25 = 0;
            $_var_26 = 0;
            $_var_27 = 0;
            $_var_28 = 0;
            $_var_29 = 0;
            $_var_30 = 0;
            $_var_31 = 0;
            $_var_32 = 0;
            $_var_33 = 0;
            $_var_34 = 0;
            $_var_35 = 0;
            if ($_var_3 >= 1) {
                if (in_array('ordercount0', $_var_1)) {
                    $_var_36 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=0 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    $_var_24 += $_var_36['ordercount'];
                    $_var_9 += $_var_36['ordercount'];
                    $_var_10 += $_var_36['ordermoney'];
                }
                if (in_array('ordercount', $_var_1)) {
                    $_var_36 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=1 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    $_var_27 += $_var_36['ordercount'];
                    $_var_11 += $_var_36['ordercount'];
                    $_var_12 += $_var_36['ordermoney'];
                }
                if (in_array('ordercount3', $_var_1)) {
                    $_var_37 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    $_var_30 += $_var_37['ordercount'];
                    $_var_13 += $_var_37['ordercount'];
                    $_var_14 += $_var_37['ordermoney'];
                    $_var_33 += $_var_37['ordermoney'];
                }
                if (in_array('total', $_var_1)) {
                    $_var_38 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    foreach ($_var_38 as $_var_39) {
                        $_var_40 = iunserializer($_var_39['commissions']);
                        $_var_41 = iunserializer($_var_39['commission1']);
                        if (empty($_var_40)) {
                            $_var_15 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                        } else {
                            $_var_15 += isset($_var_40['level1']) ? floatval($_var_40['level1']) : 0;
                        }
                    }
                }
                if (in_array('ok', $_var_1)) {
                    $_var_38 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$_var_6} - o.createtime > {$_var_7}) and og.status1=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    foreach ($_var_38 as $_var_39) {
                        $_var_40 = iunserializer($_var_39['commissions']);
                        $_var_41 = iunserializer($_var_39['commission1']);
                        if (empty($_var_40)) {
                            $_var_16 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                        } else {
                            $_var_16 += isset($_var_40['level1']) ? $_var_40['level1'] : 0;
                        }
                    }
                }
                if (in_array('lock', $_var_1)) {
                    $_var_42 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$_var_6} - o.createtime <= {$_var_7})  and og.status1=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    foreach ($_var_42 as $_var_39) {
                        $_var_40 = iunserializer($_var_39['commissions']);
                        $_var_41 = iunserializer($_var_39['commission1']);
                        if (empty($_var_40)) {
                            $_var_19 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                        } else {
                            $_var_19 += isset($_var_40['level1']) ? $_var_40['level1'] : 0;
                        }
                    }
                }
                if (in_array('apply', $_var_1)) {
                    $_var_43 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    foreach ($_var_43 as $_var_39) {
                        $_var_40 = iunserializer($_var_39['commissions']);
                        $_var_41 = iunserializer($_var_39['commission1']);
                        if (empty($_var_40)) {
                            $_var_17 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                        } else {
                            $_var_17 += isset($_var_40['level1']) ? $_var_40['level1'] : 0;
                        }
                    }
                }
                if (in_array('check', $_var_1)) {
                    $_var_43 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=2 and og.nocommission=0 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    foreach ($_var_43 as $_var_39) {
                        $_var_40 = iunserializer($_var_39['commissions']);
                        $_var_41 = iunserializer($_var_39['commission1']);
                        if (empty($_var_40)) {
                            $_var_18 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                        } else {
                            $_var_18 += isset($_var_40['level1']) ? $_var_40['level1'] : 0;
                        }
                    }
                }
                if (in_array('pay', $_var_1)) {
                    $_var_43 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=3 and og.nocommission=0 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']));
                    foreach ($_var_43 as $_var_39) {
                        $_var_40 = iunserializer($_var_39['commissions']);
                        $_var_41 = iunserializer($_var_39['commission1']);
                        if (empty($_var_40)) {
                            $_var_20 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                        } else {
                            $_var_20 += isset($_var_40['level1']) ? $_var_40['level1'] : 0;
                        }
                    }
                }
                $_var_44 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid=:agentid and isagent=1 and status=1 and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_4['id']), 'id');
                $_var_21 = count($_var_44);
                $_var_8 += $_var_21;
            }
            if ($_var_3 >= 2) {
                if ($_var_21 > 0) {
                    if (in_array('ordercount0', $_var_1)) {
                        $_var_45 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=0 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_25 += $_var_45['ordercount'];
                        $_var_9 += $_var_45['ordercount'];
                        $_var_10 += $_var_45['ordermoney'];
                    }
                    if (in_array('ordercount', $_var_1)) {
                        $_var_45 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=1 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_28 += $_var_45['ordercount'];
                        $_var_11 += $_var_45['ordercount'];
                        $_var_12 += $_var_45['ordermoney'];
                    }
                    if (in_array('ordercount3', $_var_1)) {
                        $_var_46 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=3 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_31 += $_var_46['ordercount'];
                        $_var_13 += $_var_46['ordercount'];
                        $_var_14 += $_var_46['ordermoney'];
                        $_var_34 += $_var_46['ordermoney'];
                    }
                    if (in_array('total', $_var_1)) {
                        $_var_47 = pdo_fetchall('select og.commission2,og.commissions from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_47 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission2']);
                            if (empty($_var_40)) {
                                $_var_15 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_15 += isset($_var_40['level2']) ? $_var_40['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('ok', $_var_1)) {
                        $_var_47 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ")  and ({$_var_6} - o.createtime > {$_var_7}) and o.status>=3 and og.status2=0 and og.nocommission=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_47 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission2']);
                            if (empty($_var_40)) {
                                $_var_16 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_16 += isset($_var_40['level2']) ? $_var_40['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('lock', $_var_1)) {
                        $_var_48 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ")  and ({$_var_6} - o.createtime <= {$_var_7}) and og.status2=0 and o.status>=3 and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_48 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission2']);
                            if (empty($_var_40)) {
                                $_var_19 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_19 += isset($_var_40['level2']) ? $_var_40['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('apply', $_var_1)) {
                        $_var_49 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=3 and og.status2=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_49 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission2']);
                            if (empty($_var_40)) {
                                $_var_17 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_17 += isset($_var_40['level2']) ? $_var_40['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('check', $_var_1)) {
                        $_var_50 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=3 and og.status2=2 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_50 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission2']);
                            if (empty($_var_40)) {
                                $_var_18 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_18 += isset($_var_40['level2']) ? $_var_40['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('pay', $_var_1)) {
                        $_var_50 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_44)) . ')  and o.status>=3 and og.status2=3 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_50 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission2']);
                            if (empty($_var_40)) {
                                $_var_20 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_20 += isset($_var_40['level2']) ? $_var_40['level2'] : 0;
                            }
                        }
                    }
                    $_var_51 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid in( ' . implode(',', array_keys($_var_44)) . ') and isagent=1 and status=1 and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']), 'id');
                    $_var_22 = count($_var_51);
                    $_var_8 += $_var_22;
                }
            }
            if ($_var_3 >= 3) {
                if ($_var_22 > 0) {
                    if (in_array('ordercount0', $_var_1)) {
                        $_var_52 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=0 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_26 += $_var_52['ordercount'];
                        $_var_9 += $_var_52['ordercount'];
                        $_var_10 += $_var_52['ordermoney'];
                    }
                    if (in_array('ordercount', $_var_1)) {
                        $_var_52 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=1 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_29 += $_var_52['ordercount'];
                        $_var_11 += $_var_52['ordercount'];
                        $_var_12 += $_var_52['ordermoney'];
                    }
                    if (in_array('ordercount3', $_var_1)) {
                        $_var_53 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=3 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_32 += $_var_53['ordercount'];
                        $_var_13 += $_var_53['ordercount'];
                        $_var_14 += $_var_53['ordermoney'];
                        $_var_35 += $_var_52['ordermoney'];
                    }
                    if (in_array('total', $_var_1)) {
                        $_var_54 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_54 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission3']);
                            if (empty($_var_40)) {
                                $_var_15 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_15 += isset($_var_40['level3']) ? $_var_40['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('ok', $_var_1)) {
                        $_var_54 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ")  and ({$_var_6} - o.createtime > {$_var_7}) and o.status>=3 and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_54 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission3']);
                            if (empty($_var_40)) {
                                $_var_16 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_16 += isset($_var_40['level3']) ? $_var_40['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('lock', $_var_1)) {
                        $_var_55 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ")  and o.status>=3 and ({$_var_6} - o.createtime > {$_var_7}) and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_55 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission3']);
                            if (empty($_var_40)) {
                                $_var_19 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_19 += isset($_var_40['level3']) ? $_var_40['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('apply', $_var_1)) {
                        $_var_56 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=3 and og.status3=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_56 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission3']);
                            if (empty($_var_40)) {
                                $_var_17 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_17 += isset($_var_40['level3']) ? $_var_40['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('check', $_var_1)) {
                        $_var_57 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=3 and og.status3=2 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_57 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission3']);
                            if (empty($_var_40)) {
                                $_var_18 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_18 += isset($_var_40['level3']) ? $_var_40['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('pay', $_var_1)) {
                        $_var_57 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_51)) . ')  and o.status>=3 and og.status3=3 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_57 as $_var_39) {
                            $_var_40 = iunserializer($_var_39['commissions']);
                            $_var_41 = iunserializer($_var_39['commission3']);
                            if (empty($_var_40)) {
                                $_var_20 += isset($_var_41['level' . $_var_5['id']]) ? $_var_41['level' . $_var_5['id']] : $_var_41['default'];
                            } else {
                                $_var_20 += isset($_var_40['level3']) ? $_var_40['level3'] : 0;
                            }
                        }
                    }
                    $_var_58 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and agentid in( ' . implode(',', array_keys($_var_51)) . ') and isagent=1 and status=1', array(':uniacid' => $_W['uniacid']), 'id');
                    $_var_23 = count($_var_58);
                    $_var_8 += $_var_23;
                }
            }
            $_var_4['agentcount'] = $_var_8;
            $_var_4['ordercount'] = $_var_11;
            $_var_4['ordermoney'] = $_var_12;
            $_var_4['order1'] = $_var_27;
            $_var_4['order2'] = $_var_28;
            $_var_4['order3'] = $_var_29;
            $_var_4['ordercount3'] = $_var_13;
            $_var_4['ordermoney3'] = $_var_14;
            $_var_4['order13'] = $_var_30;
            $_var_4['order23'] = $_var_31;
            $_var_4['order33'] = $_var_32;
            $_var_4['order13money'] = $_var_33;
            $_var_4['order23money'] = $_var_34;
            $_var_4['order33money'] = $_var_35;
            $_var_4['ordercount0'] = $_var_9;
            $_var_4['ordermoney0'] = $_var_10;
            $_var_4['order10'] = $_var_24;
            $_var_4['order20'] = $_var_25;
            $_var_4['order30'] = $_var_26;
            $_var_4['commission_total'] = round($_var_15, 2);
            $_var_4['commission_ok'] = round($_var_16, 2);
            $_var_4['commission_lock'] = round($_var_19, 2);
            $_var_4['commission_apply'] = round($_var_17, 2);
            $_var_4['commission_check'] = round($_var_18, 2);
            $_var_4['commission_pay'] = round($_var_20, 2);
            $_var_4['level1'] = $_var_21;
            $_var_4['level1_agentids'] = $_var_44;
            $_var_4['level2'] = $_var_22;
            $_var_4['level2_agentids'] = $_var_51;
            $_var_4['level3'] = $_var_23;
            $_var_4['level3_agentids'] = $_var_58;
            $_var_4['agenttime'] = date('Y-m-d H:i', $_var_4['agenttime']);
            return $_var_4;
        }

        function perms()
        {
            return array('commission' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('cover' => array('text' => '入口设置'), 'agent' => array('text' => '分销商', 'view' => '浏览', 'check' => '审核-log', 'edit' => '修改-log', 'agentblack' => '黑名单操作-log', 'delete' => '删除-log', 'user' => '查看下线', 'order' => '查看推广订单(还需有订单权限)', 'changeagent' => '设置分销商'), 'level' => array('text' => '分销商等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'apply' => array('text' => '佣金审核', 'view1' => '浏览待审核', 'view2' => '浏览已审核', 'view3' => '浏览已打款', 'view_1' => '浏览无效', 'export1' => '导出待审核-log', 'export2' => '导出已审核-log', 'export3' => '导出已打款-log', 'export_1' => '导出无效-log', 'check' => '审核-log', 'pay' => '打款-log', 'cancel' => '重新审核-log'), 'notice' => array('text' => '通知设置-log'), 'increase' => array('text' => '分销商趋势图'), 'changecommission' => array('text' => '修改佣金-log'), 'set' => array('text' => '基础设置-log'))));
        }

        public function allPerms()
        {
            $_var_59 = array('shop' => array('text' => '商城管理', 'child' => array('goods' => array('text' => '商品', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'category' => array('text' => '商品分类', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'dispatch' => array('text' => '配送方式', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'adv' => array('text' => '幻灯片', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'notice' => array('text' => '公告', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'comment' => array('text' => '评价', 'view' => '浏览', 'add' => '添加评论-log', 'edit' => '回复-log', 'delete' => '删除-log'))), 'member' => array('text' => '会员管理', 'child' => array('member' => array('text' => '会员', 'view' => '浏览', 'edit' => '修改-log', 'delete' => '删除-log', 'export' => '导出-log'), 'group' => array('text' => '会员组', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'level' => array('text' => '会员等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'))), 'order' => array('text' => '订单管理', 'child' => array('view' => array('text' => '浏览', 'status_1' => '浏览关闭订单', 'status0' => '浏览待付款订单', 'status1' => '浏览已付款订单', 'status2' => '浏览已发货订单', 'status3' => '浏览完成的订单', 'status4' => '浏览退货申请订单', 'status5' => '浏览已退货订单', 'status9' => '浏览提现申请'), 'op' => array('text' => '操作', 'pay' => '确认付款-log', 'send' => '发货-log', 'sendcancel' => '取消发货-log', 'finish' => '确认收货(快递单)-log', 'verify' => '确认核销(核销单)-log', 'fetch' => '确认取货(自提单)-log', 'close' => '关闭订单-log', 'refund' => '退货处理-log', 'export' => '导出订单-log', 'changeprice' => '订单改价-log'))), 'finance' => array('text' => '财务管理', 'child' => array('recharge' => array('text' => '充值', 'view' => '浏览', 'credit1' => '充值积分-log', 'credit2' => '充值余额-log', 'refund' => '充值退款-log', 'export' => '导出充值记录-log'), 'withdraw' => array('text' => '提现', 'view' => '浏览', 'withdraw' => '提现-log', 'export' => '导出提现记录-log'), 'downloadbill' => array('text' => '下载对账单'))), 'statistics' => array('text' => '数据统计', 'child' => array('view' => array('text' => '浏览权限', 'sale' => '销售指标', 'sale_analysis' => '销售统计', 'order' => '订单统计', 'goods' => '商品销售统计', 'goods_rank' => '商品销售排行', 'goods_trans' => '商品销售转化率', 'member_cost' => '会员消费排行', 'member_increase' => '会员增长趋势'), 'export' => array('text' => '导出', 'sale' => '导出销售统计-log', 'order' => '导出订单统计-log', 'goods' => '导出商品销售统计-log', 'goods_rank' => '导出商品销售排行-log', 'goods_trans' => '商品销售转化率-log', 'member_cost' => '会员消费排行-log'))), 'sysset' => array('text' => '系统设置', 'child' => array('view' => array('text' => '浏览', 'shop' => '商城设置', 'follow' => '引导及分享设置', 'notice' => '模板消息设置', 'trade' => '交易设置', 'pay' => '支付方式设置', 'template' => '模板设置', 'member' => '会员设置', 'category' => '分类层级设置', 'contact' => '联系方式设置'), 'save' => array('text' => '修改', 'shop' => '修改商城设置-log', 'follow' => '修改引导及分享设置-log', 'notice' => '修改模板消息设置-log', 'trade' => '修改交易设置-log', 'pay' => '修改支付方式设置-log', 'template' => '模板设置-log', 'member' => '会员设置-log', 'category' => '分类层级设置-log', 'contact' => '联系方式设置-log'))));
            $_var_60 = m('plugin')->getAll();
            foreach ($_var_60 as $_var_61) {
                $_var_62 = p($_var_61['identity']);
                if ($_var_62) {
                    if (method_exists($_var_62, 'perms')) {
                        $_var_63 = $_var_62->perms();
                        $_var_59 = array_merge($_var_59, $_var_63);
                    }
                }
            }
            return $_var_59;
        }

        public function getSet()
        {
            $_var_2 = parent::getSet();
            return $_var_2;
        }

        public function sendMessage($_var_64 = '', $_var_65 = array(), $_var_66 = '')
        {
            $_var_4 = m('member')->getMember($_var_64);
            if ($_var_66 == TM_SUPPLIER_PAY) {
                $_var_67 = '恭喜您，您的提现将通过 [提现方式] 转账提现金额为[金额]已在[时间]转账到您的账号，敬请查看';
                $_var_67 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_67);
                $_var_67 = str_replace('[金额]', $_var_65['money'], $_var_67);
                $_var_67 = str_replace('[提现方式]', $_var_65['type'], $_var_67);
                $_var_68 = array('keyword1' => array('value' => '供应商打款通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_67, 'color' => '#73a68d'));
                m('message')->sendCustomNotice($_var_64, $_var_68);
            }
        }

        public function sendSupplierInform($_var_64 = '', $_var_69 = '')
        {
            if ($_var_69 == 1) {
                $_var_70 = '驳回';
            } else {
                $_var_70 = '通过';
            }
            $_var_71 = $this->getSet();
            $_var_72 = $_var_71['tm'];
            $_var_67 = $_var_72['commission_become'];
            $_var_67 = str_replace('[状态]', $_var_70, $_var_67);
            $_var_67 = str_replace('[时间]', date('Y-m-d H:i', time()), $_var_67);
            if (!empty($_var_72['commission_becometitle'])) {
                $_var_73 = $_var_72['commission_becometitle'];
            } else {
                $_var_73 = '会员申请供应商通知';
            }
            $_var_68 = array('keyword1' => array('value' => $_var_73, 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_67, 'color' => '#73a68d'));
            m('message')->sendCustomNotice($_var_64, $_var_68);
        }

        public function order_split($_var_74)
        {
            global $_W;
            if (empty($_var_74)) {
                return;
            }
            $_var_75 = pdo_fetchall('select distinct supplier_uid from ' . tablename('sz_yi_order_goods') . ' where orderid=:orderid and uniacid=:uniacid', array(':orderid' => $_var_74, ':uniacid' => $_W['uniacid']));
            if (count($_var_75) == 1) {
                pdo_update('sz_yi_order', array('supplier_uid' => $_var_75[0]['supplier_uid']), array('id' => $_var_74, 'uniacid' => $_W['uniacid']));
                return;
            }
            $_var_76 = pdo_fetchall('select supplier_uid, id from ' . tablename('sz_yi_order_goods') . ' where orderid=:orderid and uniacid=:uniacid ', array(':orderid' => $_var_74, ':uniacid' => $_W['uniacid']));
            $_var_77 = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where  id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_74));
            $_var_78 = ture;
            $_var_79 = array();
            foreach ($_var_76 as $_var_80 => $_var_81) {
                $_var_79[$_var_81['supplier_uid']][]['id'] = $_var_81['id'];
            }
            $_var_82 = false;
            unset($_var_77['id']);
            unset($_var_77['uniacid']);
            $_var_83 = $_var_77['dispatchprice'];
            $_var_84 = $_var_77['olddispatchprice'];
            $_var_85 = $_var_77['changedispatchprice'];
            if (!empty($_var_79)) {
                foreach ($_var_79 as $_var_80 => $_var_81) {
                    $_var_86 = $_var_77;
                    $_var_87 = 0;
                    $_var_88 = 0;
                    $_var_89 = 0;
                    $_var_90 = 0;
                    $_var_91 = 0;
                    $_var_92 = 0;
                    $_var_93 = 0;
                    $_var_94 = 0;
                    $_var_95 = 0;
                    foreach ($_var_81 as $_var_96) {
                        $_var_70 = pdo_fetch('select price,realprice,oldprice,supplier_uid from ' . tablename('sz_yi_order_goods') . ' where id=:id and uniacid=:uniacid ', array(':id' => $_var_96['id'], ':uniacid' => $_W['uniacid']));
                        $_var_87 += $_var_70['price'];
                        $_var_88 += $_var_70['realprice'];
                        $_var_89 += $_var_70['oldprice'];
                        $_var_91 += $_var_70['price'];
                        $_var_97 = $_var_80;
                        $_var_90 += $_var_70['changeprice'];
                        $_var_98 = $_var_70['price'] / $_var_86['goodsprice'];
                        $_var_92 += round($_var_98 * $_var_86['couponprice'], 2);
                        $_var_93 += round($_var_98 * $_var_86['discountprice'], 2);
                        $_var_94 += round($_var_98 * $_var_86['deductprice'], 2);
                        $_var_95 += round($_var_98 * $_var_86['deductcredit2'], 2);
                    }
                    $_var_86['oldprice'] = $_var_89;
                    $_var_86['goodsprice'] = $_var_91;
                    $_var_86['supplier_uid'] = $_var_97;
                    $_var_86['couponprice'] = $_var_92;
                    $_var_86['discountprice'] = $_var_93;
                    $_var_86['deductprice'] = $_var_94;
                    $_var_86['deductcredit2'] = $_var_95;
                    $_var_86['changeprice'] = $_var_90;
                    $_var_86['dispatchprice'] = round($_var_83 / count($_var_70), 2);
                    $_var_86['olddispatchprice'] = round($_var_84 / count($_var_70), 2);
                    $_var_86['changedispatchprice'] = round($_var_85 / count($_var_70), 2);
                    $_var_86['price'] = $_var_88 - $_var_92 - $_var_93 - $_var_94 - $_var_95 + $_var_86['dispatchprice'];
                    if ($_var_82 == false) {
                        pdo_update('sz_yi_order', $_var_86, array('id' => $_var_74, 'uniacid' => $_W['uniacid']));
                        $_var_82 = ture;
                    } else {
                        $_var_86['uniacid'] = $_W['uniacid'];
                        $_var_99 = m('common')->createNO('order', 'ordersn', 'SH');
                        $_var_86['ordersn'] = $_var_99;
                        pdo_insert('sz_yi_order', $_var_86);
                        $_var_100 = pdo_insertid();
                        $_var_101 = array('orderid' => $_var_100);
                        foreach ($_var_81 as $_var_102) {
                            pdo_update('sz_yi_order_goods', $_var_101, array('id' => $_var_102['id'], 'uniacid' => $_W['uniacid']));
                        }
                    }
                }
            }
        }
    }
}