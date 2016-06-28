<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
define('TM_COMMISSION_AGENT_NEW', 'commission_agent_new');
define('TM_COMMISSION_ORDER_PAY', 'commission_order_pay');
define('TM_COMMISSION_ORDER_FINISH', 'commission_order_finish');
define('TM_COMMISSION_APPLY', 'commission_apply');
define('TM_COMMISSION_CHECK', 'commission_check');
define('TM_COMMISSION_PAY', 'commission_pay');
define('TM_COMMISSION_UPGRADE', 'commission_upgrade');
define('TM_COMMISSION_BECOME', 'commission_become');

if (!class_exists('CommissionModel')) {
    
    class CommissionModel extends PluginModel
    {
        public function getSet()
        {
            $_var_0 = parent::getSet();
            $_var_0['texts'] = array('agent' => empty($_var_0['texts']['agent']) ? '分销商' : $_var_0['texts']['agent'], 'shop' => empty($_var_0['texts']['shop']) ? '小店' : $_var_0['texts']['shop'], 'myshop' => empty($_var_0['texts']['myshop']) ? '我的小店' : $_var_0['texts']['myshop'], 'center' => empty($_var_0['texts']['center']) ? '分销中心' : $_var_0['texts']['center'], 'become' => empty($_var_0['texts']['become']) ? '成为分销商' : $_var_0['texts']['become'], 'withdraw' => empty($_var_0['texts']['withdraw']) ? '提现' : $_var_0['texts']['withdraw'], 'commission' => empty($_var_0['texts']['commission']) ? '佣金' : $_var_0['texts']['commission'], 'commission1' => empty($_var_0['texts']['commission1']) ? '分销佣金' : $_var_0['texts']['commission1'], 'commission_total' => empty($_var_0['texts']['commission_total']) ? '累计佣金' : $_var_0['texts']['commission_total'], 'commission_ok' => empty($_var_0['texts']['commission_ok']) ? '可提现佣金' : $_var_0['texts']['commission_ok'], 'commission_apply' => empty($_var_0['texts']['commission_apply']) ? '已申请佣金' : $_var_0['texts']['commission_apply'], 'commission_check' => empty($_var_0['texts']['commission_check']) ? '待打款佣金' : $_var_0['texts']['commission_check'], 'commission_lock' => empty($_var_0['texts']['commission_lock']) ? '未结算佣金' : $_var_0['texts']['commission_lock'], 'commission_detail' => empty($_var_0['texts']['commission_detail']) ? '佣金明细' : $_var_0['texts']['commission_detail'], 'commission_pay' => empty($_var_0['texts']['commission_pay']) ? '成功提现佣金' : $_var_0['texts']['commission_pay'], 'order' => empty($_var_0['texts']['order']) ? '分销订单' : $_var_0['texts']['order'], 'myteam' => empty($_var_0['texts']['myteam']) ? '我的团队' : $_var_0['texts']['myteam'], 'c1' => empty($_var_0['texts']['c1']) ? '一级' : $_var_0['texts']['c1'], 'c2' => empty($_var_0['texts']['c2']) ? '二级' : $_var_0['texts']['c2'], 'c3' => empty($_var_0['texts']['c3']) ? '三级' : $_var_0['texts']['c3'], 'mycustomer' => empty($_var_0['texts']['mycustomer']) ? '我的客户' : $_var_0['texts']['mycustomer']);
            return $_var_0;
        }

        public function calculate($_var_1 = 0, $_var_2 = true)
        {
            global $_W;
            $_var_0 = $this->getSet();
            $_var_3 = $this->getLevels();
            $_var_4 = pdo_fetchcolumn('select agentid from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $_var_1));
            $_var_5 = pdo_fetchall('select og.id,og.realprice,og.total,g.hascommission,g.nocommission, g.commission1_rate,g.commission1_pay,g.commission2_rate,g.commission2_pay,g.commission3_rate,g.commission3_pay,og.commissions,og.optionid,g.productprice,g.marketprice,g.costprice from ' . tablename('sz_yi_order_goods') . '  og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id = og.goodsid' . ' where og.orderid=:orderid and og.uniacid=:uniacid', array(':orderid' => $_var_1, ':uniacid' => $_W['uniacid']));
            if ($_var_0['level'] > 0) {
                foreach ($_var_5 as &$_var_6) {
                    $_var_7 = $this->calculate_method($_var_6);
                    if (empty($_var_6['nocommission']) && $_var_7 > 0) {
                        if ($_var_6['hascommission'] == 1) {
                            $_var_6['commission1'] = array('default' => $_var_0['level'] >= 1 ? $_var_6['commission1_rate'] > 0 ? round($_var_6['commission1_rate'] * $_var_7 / 100, 2) . "" : round($_var_6['commission1_pay'] * $_var_6['total'], 2) : 0);
                            $_var_6['commission2'] = array('default' => $_var_0['level'] >= 2 ? $_var_6['commission2_rate'] > 0 ? round($_var_6['commission2_rate'] * $_var_7 / 100, 2) . "" : round($_var_6['commission2_pay'] * $_var_6['total'], 2) : 0);
                            $_var_6['commission3'] = array('default' => $_var_0['level'] >= 3 ? $_var_6['commission3_rate'] > 0 ? round($_var_6['commission3_rate'] * $_var_7 / 100, 2) . "" : round($_var_6['commission3_pay'] * $_var_6['total'], 2) : 0);
                            foreach ($_var_3 as $_var_8) {
                                $_var_6['commission1']['level' . $_var_8['id']] = $_var_6['commission1_rate'] > 0 ? round($_var_6['commission1_rate'] * $_var_7 / 100, 2) . "" : round($_var_6['commission1_pay'] * $_var_6['total'], 2);
                                $_var_6['commission2']['level' . $_var_8['id']] = $_var_6['commission2_rate'] > 0 ? round($_var_6['commission2_rate'] * $_var_7 / 100, 2) . "" : round($_var_6['commission2_pay'] * $_var_6['total'], 2);
                                $_var_6['commission3']['level' . $_var_8['id']] = $_var_6['commission3_rate'] > 0 ? round($_var_6['commission3_rate'] * $_var_7 / 100, 2) . "" : round($_var_6['commission3_pay'] * $_var_6['total'], 2);
                            }
                        } else {
                            $_var_6['commission1'] = array('default' => $_var_0['level'] >= 1 ? round($_var_0['commission1'] * $_var_7 / 100, 2) . "" : 0);
                            $_var_6['commission2'] = array('default' => $_var_0['level'] >= 2 ? round($_var_0['commission2'] * $_var_7 / 100, 2) . "" : 0);
                            $_var_6['commission3'] = array('default' => $_var_0['level'] >= 3 ? round($_var_0['commission3'] * $_var_7 / 100, 2) . "" : 0);
                            foreach ($_var_3 as $_var_8) {
                                $_var_6['commission1']['level' . $_var_8['id']] = $_var_0['level'] >= 1 ? round($_var_8['commission1'] * $_var_7 / 100, 2) . "" : 0;
                                $_var_6['commission2']['level' . $_var_8['id']] = $_var_0['level'] >= 2 ? round($_var_8['commission2'] * $_var_7 / 100, 2) . "" : 0;
                                $_var_6['commission3']['level' . $_var_8['id']] = $_var_0['level'] >= 3 ? round($_var_8['commission3'] * $_var_7 / 100, 2) . "" : 0;
                            }
                        }
                    } else {
                        $_var_6['commission1'] = array('default' => 0);
                        $_var_6['commission2'] = array('default' => 0);
                        $_var_6['commission3'] = array('default' => 0);
                        foreach ($_var_3 as $_var_8) {
                            $_var_6['commission1']['level' . $_var_8['id']] = 0;
                            $_var_6['commission2']['level' . $_var_8['id']] = 0;
                            $_var_6['commission3']['level' . $_var_8['id']] = 0;
                        }
                    }
                    if ($_var_2) {
                        $_var_9 = array('level1' => 0, 'level2' => 0, 'level3' => 0);
                        if (!empty($_var_4)) {
                            $_var_10 = m('member')->getMember($_var_4);
                            if ($_var_10['isagent'] == 1 && $_var_10['status'] == 1) {
                                $_var_11 = $this->getLevel($_var_10['openid']);
                                $_var_9['level1'] = empty($_var_11) ? round($_var_6['commission1']['default'], 2) : round($_var_6['commission1']['level' . $_var_11['id']], 2);
                                if (!empty($_var_10['agentid'])) {
                                    $_var_12 = m('member')->getMember($_var_10['agentid']);
                                    $_var_13 = $this->getLevel($_var_12['openid']);
                                    $_var_9['level2'] = empty($_var_13) ? round($_var_6['commission2']['default'], 2) : round($_var_6['commission2']['level' . $_var_13['id']], 2);
                                    if (!empty($_var_12['agentid'])) {
                                        $_var_14 = m('member')->getMember($_var_12['agentid']);
                                        $_var_15 = $this->getLevel($_var_14['openid']);
                                        $_var_9['level3'] = empty($_var_15) ? round($_var_6['commission3']['default'], 2) : round($_var_6['commission3']['level' . $_var_15['id']], 2);
                                    }
                                }
                            }
                        }
                        pdo_update('sz_yi_order_goods', array('commission1' => iserializer($_var_6['commission1']), 'commission2' => iserializer($_var_6['commission2']), 'commission3' => iserializer($_var_6['commission3']), 'commissions' => iserializer($_var_9), 'nocommission' => $_var_6['nocommission']), array('id' => $_var_6['id']));
                    }
                }
                unset($_var_6);
            }
            return $_var_5;
        }

        public function calculate_method($_var_16)
        {
            global $_W;
            $_var_0 = $this->getSet();
            $_var_17 = $_var_16['realprice'];
            if (empty($_var_0['culate_method'])) {
                return $_var_17;
            } else {
                $_var_18 = $_var_16['productprice'] * $_var_16['total'];
                $_var_19 = $_var_16['marketprice'] * $_var_16['total'];
                $_var_20 = $_var_16['costprice'] * $_var_16['total'];
                if ($_var_16['optionid'] != 0) {
                    $_var_21 = pdo_fetch('select productprice,marketprice,costprice from ' . tablename('sz_yi_goods_option') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_16['optionid'], ':uniacid' => $_W['uniacid']));
                    $_var_18 = $_var_21['productprice'] * $_var_16['total'];
                    $_var_19 = $_var_21['marketprice'] * $_var_16['total'];
                    $_var_20 = $_var_21['costprice'] * $_var_16['total'];
                }
                if ($_var_0['culate_method'] == 1) {
                    return $_var_18;
                } else {
                    if ($_var_0['culate_method'] == 2) {
                        return $_var_19;
                    } else {
                        if ($_var_0['culate_method'] == 3) {
                            return $_var_20;
                        } else {
                            if ($_var_0['culate_method'] == 4) {
                                $_var_7 = $_var_17 - $_var_20;
                                return $_var_7 > 0 ? $_var_7 : 0;
                            }
                        }
                    }
                }
            }
        }

        public function getOrderCommissions($_var_22 = 0, $_var_23 = 0)
        {
            global $_W;
            $_var_0 = $this->getSet();
            $_var_24 = pdo_fetchcolumn('select agentid from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $_var_22));
            $_var_25 = pdo_fetch('select commission1,commission2,commission3 from ' . tablename('sz_yi_order_goods') . ' where id=:id and orderid=:orderid and uniacid=:uniacid and nocommission=0 limit 1', array(':id' => $_var_23, ':orderid' => $_var_22, ':uniacid' => $_W['uniacid']));
            $_var_26 = array('level1' => 0, 'level2' => 0, 'level3' => 0);
            if ($_var_0['level'] > 0) {
                $_var_27 = iunserializer($_var_25['commission1']);
                $_var_28 = iunserializer($_var_25['commission2']);
                $_var_29 = iunserializer($_var_25['commission3']);
                if (!empty($_var_24)) {
                    $_var_30 = m('member')->getMember($_var_24);
                    if ($_var_30['isagent'] == 1 && $_var_30['status'] == 1) {
                        $_var_31 = $this->getLevel($_var_30['openid']);
                        $_var_26['level1'] = empty($_var_31) ? round($_var_27['default'], 2) : round($_var_27['level' . $_var_31['id']], 2);
                        if (!empty($_var_30['agentid'])) {
                            $_var_32 = m('member')->getMember($_var_30['agentid']);
                            $_var_33 = $this->getLevel($_var_32['openid']);
                            $_var_26['level2'] = empty($_var_33) ? round($_var_28['default'], 2) : round($_var_28['level' . $_var_33['id']], 2);
                            if (!empty($_var_32['agentid'])) {
                                $_var_34 = m('member')->getMember($_var_32['agentid']);
                                $_var_35 = $this->getLevel($_var_34['openid']);
                                $_var_26['level3'] = empty($_var_35) ? round($_var_29['default'], 2) : round($_var_29['level' . $_var_35['id']], 2);
                            }
                        }
                    }
                }
            }
            return $_var_26;
        }

        public function getInfo($_var_36, $_var_37 = null)
        {
            if (empty($_var_37) || !is_array($_var_37)) {
                $_var_37 = array();
            }
            global $_W;
            $_var_0 = $this->getSet();
            $_var_38 = intval($_var_0['level']);
            $_var_39 = m('member')->getMember($_var_36);
            $_var_40 = $this->getLevel($_var_36);
            $_var_41 = time();
            $_var_42 = intval($_var_0['settledays']) * 3600 * 24;
            $_var_43 = 0;
            $_var_44 = 0;
            $_var_45 = 0;
            $_var_46 = 0;
            $_var_47 = 0;
            $_var_48 = 0;
            $_var_49 = 0;
            $_var_50 = 0;
            $_var_51 = 0;
            $_var_52 = 0;
            $_var_53 = 0;
            $_var_54 = 0;
            $_var_55 = 0;
            $_var_56 = 0;
            $_var_57 = 0;
            $_var_58 = 0;
            $_var_59 = 0;
            $_var_60 = 0;
            $_var_61 = 0;
            $_var_62 = 0;
            $_var_63 = 0;
            $_var_64 = 0;
            $_var_65 = 0;
            $_var_66 = 0;
            $_var_67 = 0;
            $_var_68 = 0;
            $_var_69 = 0;
            $_var_70 = 0;
            $_var_71 = 0;
            $_var_72 = 0;
            if ($_var_38 >= 1) {
                if (in_array('ordercount0', $_var_37)) {
                    $_var_73 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=0 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    $_var_59 += $_var_73['ordercount'];
                    $_var_44 += $_var_73['ordercount'];
                    $_var_45 += $_var_73['ordermoney'];
                }
                if (in_array('ordercount', $_var_37)) {
                    $_var_73 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=1 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    $_var_62 += $_var_73['ordercount'];
                    $_var_46 += $_var_73['ordercount'];
                    $_var_47 += $_var_73['ordermoney'];
                }
                if (in_array('ordercount3', $_var_37)) {
                    $_var_74 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    $_var_65 += $_var_74['ordercount'];
                    $_var_48 += $_var_74['ordercount'];
                    $_var_49 += $_var_74['ordermoney'];
                    $_var_68 += $_var_74['ordermoney'];
                }
                if (in_array('total', $_var_37)) {
                    $_var_75 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    foreach ($_var_75 as $_var_76) {
                        $_var_26 = iunserializer($_var_76['commissions']);
                        $_var_77 = iunserializer($_var_76['commission1']);
                        if (empty($_var_26)) {
                            $_var_50 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                        } else {
                            $_var_50 += isset($_var_26['level1']) ? floatval($_var_26['level1']) : 0;
                        }
                    }
                }
                if (in_array('ok', $_var_37)) {
                    $_var_75 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$_var_41} - o.createtime > {$_var_42}) and og.status1=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    foreach ($_var_75 as $_var_76) {
                        $_var_26 = iunserializer($_var_76['commissions']);
                        $_var_77 = iunserializer($_var_76['commission1']);
                        if (empty($_var_26)) {
                            $_var_51 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                        } else {
                            $_var_51 += isset($_var_26['level1']) ? $_var_26['level1'] : 0;
                        }
                    }
                }
                if (in_array('lock', $_var_37)) {
                    $_var_78 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$_var_41} - o.createtime <= {$_var_42})  and og.status1=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    foreach ($_var_78 as $_var_76) {
                        $_var_26 = iunserializer($_var_76['commissions']);
                        $_var_77 = iunserializer($_var_76['commission1']);
                        if (empty($_var_26)) {
                            $_var_54 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                        } else {
                            $_var_54 += isset($_var_26['level1']) ? $_var_26['level1'] : 0;
                        }
                    }
                }
                if (in_array('apply', $_var_37)) {
                    $_var_79 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    foreach ($_var_79 as $_var_76) {
                        $_var_26 = iunserializer($_var_76['commissions']);
                        $_var_77 = iunserializer($_var_76['commission1']);
                        if (empty($_var_26)) {
                            $_var_52 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                        } else {
                            $_var_52 += isset($_var_26['level1']) ? $_var_26['level1'] : 0;
                        }
                    }
                }
                if (in_array('check', $_var_37)) {
                    $_var_79 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=2 and og.nocommission=0 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    foreach ($_var_79 as $_var_76) {
                        $_var_26 = iunserializer($_var_76['commissions']);
                        $_var_77 = iunserializer($_var_76['commission1']);
                        if (empty($_var_26)) {
                            $_var_53 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                        } else {
                            $_var_53 += isset($_var_26['level1']) ? $_var_26['level1'] : 0;
                        }
                    }
                }
                if (in_array('pay', $_var_37)) {
                    $_var_79 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=3 and og.nocommission=0 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']));
                    foreach ($_var_79 as $_var_76) {
                        $_var_26 = iunserializer($_var_76['commissions']);
                        $_var_77 = iunserializer($_var_76['commission1']);
                        if (empty($_var_26)) {
                            $_var_55 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                        } else {
                            $_var_55 += isset($_var_26['level1']) ? $_var_26['level1'] : 0;
                        }
                    }
                }
                $_var_80 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid=:agentid and isagent=1 and status=1 and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_39['id']), 'id');
                $_var_56 = count($_var_80);
                $_var_43 += $_var_56;
            }
            if ($_var_38 >= 2) {
                if ($_var_56 > 0) {
                    if (in_array('ordercount0', $_var_37)) {
                        $_var_81 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=0 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_60 += $_var_81['ordercount'];
                        $_var_44 += $_var_81['ordercount'];
                        $_var_45 += $_var_81['ordermoney'];
                    }
                    if (in_array('ordercount', $_var_37)) {
                        $_var_81 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=1 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_63 += $_var_81['ordercount'];
                        $_var_46 += $_var_81['ordercount'];
                        $_var_47 += $_var_81['ordermoney'];
                    }
                    if (in_array('ordercount3', $_var_37)) {
                        $_var_82 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=3 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_66 += $_var_82['ordercount'];
                        $_var_48 += $_var_82['ordercount'];
                        $_var_49 += $_var_82['ordermoney'];
                        $_var_69 += $_var_82['ordermoney'];
                    }
                    if (in_array('total', $_var_37)) {
                        $_var_83 = pdo_fetchall('select og.commission2,og.commissions from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_83 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission2']);
                            if (empty($_var_26)) {
                                $_var_50 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_50 += isset($_var_26['level2']) ? $_var_26['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('ok', $_var_37)) {
                        $_var_83 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ")  and ({$_var_41} - o.createtime > {$_var_42}) and o.status>=3 and og.status2=0 and og.nocommission=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_83 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission2']);
                            if (empty($_var_26)) {
                                $_var_51 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_51 += isset($_var_26['level2']) ? $_var_26['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('lock', $_var_37)) {
                        $_var_84 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ")  and ({$_var_41} - o.createtime <= {$_var_42}) and og.status2=0 and o.status>=3 and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_84 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission2']);
                            if (empty($_var_26)) {
                                $_var_54 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_54 += isset($_var_26['level2']) ? $_var_26['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('apply', $_var_37)) {
                        $_var_85 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=3 and og.status2=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_85 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission2']);
                            if (empty($_var_26)) {
                                $_var_52 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_52 += isset($_var_26['level2']) ? $_var_26['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('check', $_var_37)) {
                        $_var_86 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=3 and og.status2=2 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_86 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission2']);
                            if (empty($_var_26)) {
                                $_var_53 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_53 += isset($_var_26['level2']) ? $_var_26['level2'] : 0;
                            }
                        }
                    }
                    if (in_array('pay', $_var_37)) {
                        $_var_86 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_80)) . ')  and o.status>=3 and og.status2=3 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_86 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission2']);
                            if (empty($_var_26)) {
                                $_var_55 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_55 += isset($_var_26['level2']) ? $_var_26['level2'] : 0;
                            }
                        }
                    }
                    $_var_87 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid in( ' . implode(',', array_keys($_var_80)) . ') and isagent=1 and status=1 and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']), 'id');
                    $_var_57 = count($_var_87);
                    $_var_43 += $_var_57;
                }
            }
            if ($_var_38 >= 3) {
                if ($_var_57 > 0) {
                    if (in_array('ordercount0', $_var_37)) {
                        $_var_88 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=0 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_61 += $_var_88['ordercount'];
                        $_var_44 += $_var_88['ordercount'];
                        $_var_45 += $_var_88['ordermoney'];
                    }
                    if (in_array('ordercount', $_var_37)) {
                        $_var_88 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=1 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_64 += $_var_88['ordercount'];
                        $_var_46 += $_var_88['ordercount'];
                        $_var_47 += $_var_88['ordermoney'];
                    }
                    if (in_array('ordercount3', $_var_37)) {
                        $_var_89 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=3 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
                        $_var_67 += $_var_89['ordercount'];
                        $_var_48 += $_var_89['ordercount'];
                        $_var_49 += $_var_89['ordermoney'];
                        $_var_70 += $_var_88['ordermoney'];
                    }
                    if (in_array('total', $_var_37)) {
                        $_var_90 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_90 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission3']);
                            if (empty($_var_26)) {
                                $_var_50 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_50 += isset($_var_26['level3']) ? $_var_26['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('ok', $_var_37)) {
                        $_var_90 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ")  and ({$_var_41} - o.createtime > {$_var_42}) and o.status>=3 and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_90 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission3']);
                            if (empty($_var_26)) {
                                $_var_51 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_51 += isset($_var_26['level3']) ? $_var_26['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('lock', $_var_37)) {
                        $_var_91 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ")  and o.status>=3 and ({$_var_41} - o.createtime > {$_var_42}) and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_91 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission3']);
                            if (empty($_var_26)) {
                                $_var_54 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_54 += isset($_var_26['level3']) ? $_var_26['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('apply', $_var_37)) {
                        $_var_92 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=3 and og.status3=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_92 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission3']);
                            if (empty($_var_26)) {
                                $_var_52 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_52 += isset($_var_26['level3']) ? $_var_26['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('check', $_var_37)) {
                        $_var_93 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=3 and og.status3=2 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_93 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission3']);
                            if (empty($_var_26)) {
                                $_var_53 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_53 += isset($_var_26['level3']) ? $_var_26['level3'] : 0;
                            }
                        }
                    }
                    if (in_array('pay', $_var_37)) {
                        $_var_93 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_87)) . ')  and o.status>=3 and og.status3=3 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
                        foreach ($_var_93 as $_var_76) {
                            $_var_26 = iunserializer($_var_76['commissions']);
                            $_var_77 = iunserializer($_var_76['commission3']);
                            if (empty($_var_26)) {
                                $_var_55 += isset($_var_77['level' . $_var_40['id']]) ? $_var_77['level' . $_var_40['id']] : $_var_77['default'];
                            } else {
                                $_var_55 += isset($_var_26['level3']) ? $_var_26['level3'] : 0;
                            }
                        }
                    }
                    $_var_94 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and agentid in( ' . implode(',', array_keys($_var_87)) . ') and isagent=1 and status=1', array(':uniacid' => $_W['uniacid']), 'id');
                    $_var_58 = count($_var_94);
                    $_var_43 += $_var_58;
                }
            }
            if (in_array('myorder', $_var_37)) {
                $_var_95 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.openid=:openid and o.status>=3 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_39['openid']));
                $_var_71 = $_var_95['ordermoney'];
                $_var_72 = $_var_95['ordercount'];
            }
            $_var_39['agentcount'] = $_var_43;
            $_var_39['ordercount'] = $_var_46;
            $_var_39['ordermoney'] = $_var_47;
            $_var_39['order1'] = $_var_62;
            $_var_39['order2'] = $_var_63;
            $_var_39['order3'] = $_var_64;
            $_var_39['ordercount3'] = $_var_48;
            $_var_39['ordermoney3'] = $_var_49;
            $_var_39['order13'] = $_var_65;
            $_var_39['order23'] = $_var_66;
            $_var_39['order33'] = $_var_67;
            $_var_39['order13money'] = $_var_68;
            $_var_39['order23money'] = $_var_69;
            $_var_39['order33money'] = $_var_70;
            $_var_39['ordercount0'] = $_var_44;
            $_var_39['ordermoney0'] = $_var_45;
            $_var_39['order10'] = $_var_59;
            $_var_39['order20'] = $_var_60;
            $_var_39['order30'] = $_var_61;
            $_var_39['commission_total'] = round($_var_50, 2);
            $_var_39['commission_ok'] = round($_var_51, 2);
            $_var_39['commission_lock'] = round($_var_54, 2);
            $_var_39['commission_apply'] = round($_var_52, 2);
            $_var_39['commission_check'] = round($_var_53, 2);
            $_var_39['commission_pay'] = round($_var_55, 2);
            $_var_39['level1'] = $_var_56;
            $_var_39['level1_agentids'] = $_var_80;
            $_var_39['level2'] = $_var_57;
            $_var_39['level2_agentids'] = $_var_87;
            $_var_39['level3'] = $_var_58;
            $_var_39['level3_agentids'] = $_var_94;
            $_var_39['agenttime'] = date('Y-m-d H:i', $_var_39['agenttime']);
            $_var_39['myoedermoney'] = $_var_71;
            $_var_39['myordercount'] = $_var_72;
            return $_var_39;
        }

        public function getAgents($_var_22 = 0)
        {
            global $_W, $_GPC;
            $_var_96 = array();
            $_var_97 = pdo_fetch('select id,agentid,openid from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_22, ':uniacid' => $_W['uniacid']));
            if (empty($_var_97)) {
                return $_var_96;
            }
            $_var_30 = m('member')->getMember($_var_97['agentid']);
            if (!empty($_var_30) && $_var_30['isagent'] == 1 && $_var_30['status'] == 1) {
                $_var_96[] = $_var_30;
                if (!empty($_var_30['agentid'])) {
                    $_var_32 = m('member')->getMember($_var_30['agentid']);
                    if (!empty($_var_32) && $_var_32['isagent'] == 1 && $_var_32['status'] == 1) {
                        $_var_96[] = $_var_32;
                        if (!empty($_var_32['agentid'])) {
                            $_var_34 = m('member')->getMember($_var_32['agentid']);
                            if (!empty($_var_34) && $_var_34['isagent'] == 1 && $_var_34['status'] == 1) {
                                $_var_96[] = $_var_34;
                            }
                        }
                    }
                }
            }
            return $_var_96;
        }

        public function isAgent($_var_36)
        {
            if (empty($_var_36)) {
                return false;
            }
            if (is_array($_var_36)) {
                return $_var_36['isagent'] == 1 && $_var_36['status'] == 1;
            }
            $_var_39 = m('member')->getMember($_var_36);
            return $_var_39['isagent'] == 1 && $_var_39['status'] == 1;
        }

        public function getCommission($_var_25)
        {
            global $_W;
            $_var_0 = $this->getSet();
            $_var_77 = 0;
            if ($_var_25['hascommission'] == 1) {
                $_var_77 = $_var_0['level'] >= 1 ? $_var_25['commission1_rate'] > 0 ? $_var_25['commission1_rate'] * $_var_25['marketprice'] / 100 : $_var_25['commission1_pay'] : 0;
            } else {
                $_var_36 = m('user')->getOpenid();
                $_var_38 = $this->getLevel($_var_36);
                if (!empty($_var_38)) {
                    $_var_77 = $_var_0['level'] >= 1 ? round($_var_38['commission1'] * $_var_25['marketprice'] / 100, 2) : 0;
                } else {
                    $_var_77 = $_var_0['level'] >= 1 ? round($_var_0['commission1'] * $_var_25['marketprice'] / 100, 2) : 0;
                }
            }
            return $_var_77;
        }

        public function createMyShopQrcode($_var_98 = 0, $_var_99 = 0)
        {
            global $_W;
            $_var_100 = IA_ROOT . '/addons/sz_yi/data/qrcode/' . $_W['uniacid'];
            if (!is_dir($_var_100)) {
                load()->func('file');
                mkdirs($_var_100);
            }
            $_var_101 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=commission&method=myshop&mid=' . $_var_98;
            if (!empty($_var_99)) {
                $_var_101 .= '&posterid=' . $_var_99;
            }
            $_var_102 = 'myshop_' . $_var_99 . '_' . $_var_98 . '.png';
            $_var_103 = $_var_100 . '/' . $_var_102;
            if (!is_file($_var_103)) {
                require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
                QRcode::png($_var_101, $_var_103, QR_ECLEVEL_H, 4);
            }
            return $_W['siteroot'] . 'addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $_var_102;
        }

        private function createImage($_var_101)
        {
            load()->func('communication');
            $_var_104 = ihttp_request($_var_101);
            return imagecreatefromstring($_var_104['content']);
        }

        public function createGoodsImage($_var_25, $_var_105)
        {
            global $_W, $_GPC;
            $_var_25 = set_medias($_var_25, 'thumb');
            $_var_36 = m('user')->getOpenid();
            $_var_106 = m('member')->getMember($_var_36);
            if ($_var_106['isagent'] == 1 && $_var_106['status'] == 1) {
                $_var_107 = $_var_106;
            } else {
                $_var_98 = intval($_GPC['mid']);
                if (!empty($_var_98)) {
                    $_var_107 = m('member')->getMember($_var_98);
                }
            }
            $_var_100 = IA_ROOT . '/addons/sz_yi/data/poster/' . $_W['uniacid'] . '/';
            if (!is_dir($_var_100)) {
                load()->func('file');
                mkdirs($_var_100);
            }
            $_var_108 = empty($_var_25['commission_thumb']) ? $_var_25['thumb'] : tomedia($_var_25['commission_thumb']);
            $_var_109 = md5(json_encode(array('id' => $_var_25['id'], 'marketprice' => $_var_25['marketprice'], 'productprice' => $_var_25['productprice'], 'img' => $_var_108, 'openid' => $_var_36, 'version' => 4)));
            $_var_102 = $_var_109 . '.jpg';
            if (!is_file($_var_100 . $_var_102)) {
                set_time_limit(0);
                $_var_110 = IA_ROOT . '/addons/sz_yi/static/fonts/msyh.ttf';
                $_var_111 = imagecreatetruecolor(640, 1225);
                if (!is_weixin()) {
                    $_var_112 = 196;
                    $_var_113 = imagecreatefromjpeg(IA_ROOT . '/addons/sz_yi/plugin/commission/images/poster_pc.jpg');
                } else {
                    $_var_112 = 50;
                    $_var_113 = imagecreatefromjpeg(IA_ROOT . '/addons/sz_yi/plugin/commission/images/poster.jpg');
                }
                $_var_114 = $_var_107['realname'] ? $_var_107['realname'] : $_var_107['nickname'];
                $_var_114 = $_var_114 ? $_var_114 : $_var_107['mobile'];
                imagecopy($_var_111, $_var_113, 0, 0, 0, 0, 640, 1225);
                imagedestroy($_var_113);
                $_var_115 = preg_replace('/\\/0$/i', '/96', $_var_107['avatar']);
                $_var_116 = $this->createImage($_var_115);
                $_var_117 = imagesx($_var_116);
                $_var_118 = imagesy($_var_116);
                imagecopyresized($_var_111, $_var_116, 24, 32, 0, 0, 88, 88, $_var_117, $_var_118);
                imagedestroy($_var_116);
                $_var_119 = $this->createImage($_var_108);
                $_var_117 = imagesx($_var_119);
                $_var_118 = imagesy($_var_119);
                imagecopyresized($_var_111, $_var_119, 0, 160, 0, 0, 640, 640, $_var_117, $_var_118);
                imagedestroy($_var_119);
                $_var_120 = imagecreatetruecolor(640, 127);
                imagealphablending($_var_120, false);
                imagesavealpha($_var_120, true);
                $_var_121 = imagecolorallocatealpha($_var_120, 0, 0, 0, 25);
                imagefill($_var_120, 0, 0, $_var_121);
                imagecopy($_var_111, $_var_120, 0, 678, 0, 0, 640, 127);
                imagedestroy($_var_120);
                $_var_122 = tomedia(m('qrcode')->createGoodsQrcode($_var_107['id'], $_var_25['id']));
                $_var_123 = $this->createImage($_var_122);
                $_var_117 = imagesx($_var_123);
                $_var_118 = imagesy($_var_123);
                imagecopyresized($_var_111, $_var_123, $_var_112, 835, 0, 0, 250, 250, $_var_117, $_var_118);
                imagedestroy($_var_123);
                $_var_124 = imagecolorallocate($_var_111, 0, 3, 51);
                $_var_125 = imagecolorallocate($_var_111, 240, 102, 0);
                $_var_126 = imagecolorallocate($_var_111, 255, 255, 255);
                $_var_127 = imagecolorallocate($_var_111, 255, 255, 0);
                $_var_128 = '我是';
                imagettftext($_var_111, 20, 0, 150, 70, $_var_124, $_var_110, $_var_128);
                imagettftext($_var_111, 20, 0, 210, 70, $_var_125, $_var_110, $_var_114);
                $_var_129 = '我要为';
                imagettftext($_var_111, 20, 0, 150, 105, $_var_124, $_var_110, $_var_129);
                $_var_130 = $_var_105['name'];
                imagettftext($_var_111, 20, 0, 240, 105, $_var_125, $_var_110, $_var_130);
                $_var_131 = imagettfbbox(20, 0, $_var_110, $_var_130);
                $_var_132 = $_var_131[4] - $_var_131[6];
                $_var_133 = '代言';
                imagettftext($_var_111, 20, 0, 240 + $_var_132 + 10, 105, $_var_124, $_var_110, $_var_133);
                $_var_134 = mb_substr($_var_25['title'], 0, 50, 'utf-8');
                imagettftext($_var_111, 20, 0, 30, 730, $_var_126, $_var_110, $_var_134);
                $_var_135 = '￥' . number_format($_var_25['marketprice'], 2);
                imagettftext($_var_111, 25, 0, 25, 780, $_var_127, $_var_110, $_var_135);
                $_var_131 = imagettfbbox(26, 0, $_var_110, $_var_135);
                $_var_132 = $_var_131[4] - $_var_131[6];
                if ($_var_25['productprice'] > 0) {
                    $_var_136 = '￥' . number_format($_var_25['productprice'], 2);
                    imagettftext($_var_111, 22, 0, 25 + $_var_132 + 10, 780, $_var_126, $_var_110, $_var_136);
                    $_var_137 = 25 + $_var_132 + 10;
                    $_var_131 = imagettfbbox(22, 0, $_var_110, $_var_136);
                    $_var_132 = $_var_131[4] - $_var_131[6];
                    imageline($_var_111, $_var_137, 770, $_var_137 + $_var_132 + 20, 770, $_var_126);
                    imageline($_var_111, $_var_137, 771.5, $_var_137 + $_var_132 + 20, 771, $_var_126);
                }
                imagejpeg($_var_111, $_var_100 . $_var_102);
                imagedestroy($_var_111);
            }
            return $_W['siteroot'] . 'addons/sz_yi/data/poster/' . $_W['uniacid'] . '/' . $_var_102;
        }

        public function createShopImage($_var_105)
        {
            global $_W, $_GPC;
            $_var_105 = set_medias($_var_105, 'signimg');
            $_var_100 = IA_ROOT . '/addons/sz_yi/data/poster/' . $_W['uniacid'] . '/';
            if (!is_dir($_var_100)) {
                load()->func('file');
                mkdirs($_var_100);
            }
            $_var_98 = intval($_GPC['mid']);
            $_var_36 = m('user')->getOpenid();
            $_var_106 = m('member')->getMember($_var_36);
            if ($_var_106['isagent'] == 1 && $_var_106['status'] == 1) {
                $_var_107 = $_var_106;
            } else {
                $_var_98 = intval($_GPC['mid']);
                if (!empty($_var_98)) {
                    $_var_107 = m('member')->getMember($_var_98);
                }
            }
            $_var_109 = md5(json_encode(array('openid' => $_var_36, 'signimg' => $_var_105['signimg'], 'version' => 4)));
            $_var_102 = $_var_109 . '.jpg';
            if (!is_file($_var_100 . $_var_102)) {
                set_time_limit(0);
                @ini_set('memory_limit', '256M');
                $_var_110 = IA_ROOT . '/addons/sz_yi/static/fonts/msyh.ttf';
                $_var_111 = imagecreatetruecolor(640, 1225);
                $_var_124 = imagecolorallocate($_var_111, 0, 3, 51);
                $_var_125 = imagecolorallocate($_var_111, 240, 102, 0);
                $_var_126 = imagecolorallocate($_var_111, 255, 255, 255);
                $_var_127 = imagecolorallocate($_var_111, 255, 255, 0);
                if (!is_weixin()) {
                    $_var_113 = imagecreatefromjpeg(IA_ROOT . '/addons/sz_yi/plugin/commission/images/poster_pc.jpg');
                    $_var_112 = 196;
                } else {
                    $_var_113 = imagecreatefromjpeg(IA_ROOT . '/addons/sz_yi/plugin/commission/images/poster.jpg');
                    $_var_112 = 50;
                }
                $_var_114 = $_var_107['realname'] ? $_var_107['realname'] : $_var_107['nickname'];
                $_var_114 = $_var_114 ? $_var_114 : $_var_107['mobile'];
                imagecopy($_var_111, $_var_113, 0, 0, 0, 0, 640, 1225);
                imagedestroy($_var_113);
                $_var_115 = preg_replace('/\\/0$/i', '/96', $_var_107['avatar']);
                $_var_116 = $this->createImage($_var_115);
                $_var_117 = imagesx($_var_116);
                $_var_118 = imagesy($_var_116);
                imagecopyresized($_var_111, $_var_116, 24, 32, 0, 0, 88, 88, $_var_117, $_var_118);
                imagedestroy($_var_116);
                $_var_119 = $this->createImage($_var_105['signimg']);
                $_var_117 = imagesx($_var_119);
                $_var_118 = imagesy($_var_119);
                imagecopyresized($_var_111, $_var_119, 0, 160, 0, 0, 640, 640, $_var_117, $_var_118);
                imagedestroy($_var_119);
                $_var_138 = tomedia($this->createMyShopQrcode($_var_107['id']));
                $_var_123 = $this->createImage($_var_138);
                $_var_117 = imagesx($_var_123);
                $_var_118 = imagesy($_var_123);
                imagecopyresized($_var_111, $_var_123, $_var_112, 835, 0, 0, 250, 250, $_var_117, $_var_118);
                imagedestroy($_var_123);
                $_var_128 = '我是';
                imagettftext($_var_111, 20, 0, 150, 70, $_var_124, $_var_110, $_var_128);
                imagettftext($_var_111, 20, 0, 210, 70, $_var_125, $_var_110, $_var_114);
                $_var_129 = '我要为';
                imagettftext($_var_111, 20, 0, 150, 105, $_var_124, $_var_110, $_var_129);
                $_var_130 = $_var_105['name'];
                imagettftext($_var_111, 20, 0, 240, 105, $_var_125, $_var_110, $_var_130);
                $_var_131 = imagettfbbox(20, 0, $_var_110, $_var_130);
                $_var_132 = $_var_131[4] - $_var_131[6];
                $_var_133 = '代言';
                imagettftext($_var_111, 20, 0, 240 + $_var_132 + 10, 105, $_var_124, $_var_110, $_var_133);
                imagejpeg($_var_111, $_var_100 . $_var_102);
                imagedestroy($_var_111);
            }
            return $_W['siteroot'] . 'addons/sz_yi/data/poster/' . $_W['uniacid'] . '/' . $_var_102;
        }

        public function checkAgent()
        {
            global $_W, $_GPC;
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return;
            }
            $_var_36 = m('user')->getOpenid();
            if (empty($_var_36)) {
                return;
            }
            $_var_39 = m('member')->getMember($_var_36);
            if (empty($_var_39)) {
                return;
            }
            $_var_139 = false;
            $_var_98 = intval($_GPC['mid']);
            if (!empty($_var_98)) {
                $_var_139 = m('member')->getMember($_var_98);
            }
            $_var_140 = !empty($_var_139) && $_var_139['isagent'] == 1 && $_var_139['status'] == 1;
            if ($_var_140) {
                if ($_var_139['openid'] != $_var_36) {
                    $_var_141 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_commission_clickcount') . ' where uniacid=:uniacid and openid=:openid and from_openid=:from_openid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_36, ':from_openid' => $_var_139['openid']));
                    if ($_var_141 <= 0) {
                        $_var_142 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_36, 'from_openid' => $_var_139['openid'], 'clicktime' => time());
                        pdo_insert('sz_yi_commission_clickcount', $_var_142);
                        pdo_update('sz_yi_member', array('clickcount' => $_var_139['clickcount'] + 1), array('uniacid' => $_W['uniacid'], 'id' => $_var_139['id']));
                    }
                }
            }
            if ($_var_39['isagent'] == 1) {
                return;
            }
            if ($_var_143 == 0) {
                $_var_144 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member') . ' where id<:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_39['id']));
                if ($_var_144 <= 0) {
                    pdo_update('sz_yi_member', array('isagent' => 1, 'status' => 1, 'agenttime' => time(), 'agentblack' => 0), array('uniacid' => $_W['uniacid'], 'id' => $_var_39['id']));
                    return;
                }
            }
            $_var_41 = time();
            $_var_145 = intval($_var_0['become_child']);
            if ($_var_140 && empty($_var_39['agentid'])) {
                if ($_var_39['id'] != $_var_139['id']) {
                    if (empty($_var_145)) {
                        if (empty($_var_39['fixagentid'])) {
                            pdo_update('sz_yi_member', array('agentid' => $_var_139['id'], 'childtime' => $_var_41), array('uniacid' => $_W['uniacid'], 'id' => $_var_39['id']));
                            $this->sendMessage($_var_139['openid'], array('nickname' => $_var_39['nickname'], 'childtime' => $_var_41), TM_COMMISSION_AGENT_NEW);
                            $this->upgradeLevelByAgent($_var_139['id']);
                        }
                    } else {
                        pdo_update('sz_yi_member', array('inviter' => $_var_139['id']), array('uniacid' => $_W['uniacid'], 'id' => $_var_39['id']));
                    }
                }
            }
            $_var_146 = intval($_var_0['become_check']);
            if (empty($_var_0['become'])) {
                if (empty($_var_39['agentblack'])) {
                    pdo_update('sz_yi_member', array('isagent' => 1, 'status' => $_var_146, 'agenttime' => $_var_146 == 1 ? $_var_41 : 0), array('uniacid' => $_W['uniacid'], 'id' => $_var_39['id']));
                    if ($_var_146 == 1) {
                        $this->sendMessage($_var_36, array('nickname' => $_var_39['nickname'], 'agenttime' => $_var_41), TM_COMMISSION_BECOME);
                        if ($_var_140) {
                            $this->upgradeLevelByAgent($_var_139['id']);
                        }
                    }
                }
            }
        }

        public function checkOrderConfirm($_var_1 = '0')
        {
            global $_W, $_GPC;
            if (empty($_var_1)) {
                return;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return;
            }
            $_var_147 = pdo_fetch('select id,openid,ordersn,goodsprice,agentid,paytime from ' . tablename('sz_yi_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1', array(':id' => $_var_1, ':uniacid' => $_W['uniacid']));
            if (empty($_var_147)) {
                return;
            }
            $_var_148 = $_var_147['openid'];
            $_var_149 = m('member')->getMember($_var_148);
            if (empty($_var_149)) {
                return;
            }
            $_var_150 = p('bonus');
            if (!empty($_var_150)) {
                $_var_151 = $_var_150->getSet();
                if (!empty($_var_151['start'])) {
                    $_var_150->checkOrderConfirm($_var_1);
                }
            }
            $_var_152 = intval($_var_0['become_child']);
            $_var_153 = false;
            if (empty($_var_152)) {
                $_var_153 = m('member')->getMember($_var_149['agentid']);
            } else {
                $_var_153 = m('member')->getMember($_var_149['inviter']);
            }
            $_var_154 = !empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1;
            $_var_155 = time();
            $_var_152 = intval($_var_0['become_child']);
            if ($_var_154) {
                if ($_var_152 == 1) {
                    if (empty($_var_149['agentid']) && $_var_149['id'] != $_var_153['id']) {
                        if (empty($_var_149['fixagentid'])) {
                            $_var_149['agentid'] = $_var_153['id'];
                            pdo_update('sz_yi_member', array('agentid' => $_var_153['id'], 'childtime' => $_var_155), array('uniacid' => $_W['uniacid'], 'id' => $_var_149['id']));
                            $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'childtime' => $_var_155), TM_COMMISSION_AGENT_NEW);
                            $this->upgradeLevelByAgent($_var_153['id']);
                        }
                    }
                }
            }
            $_var_4 = $_var_149['agentid'];
            if ($_var_149['isagent'] == 1 && $_var_149['status'] == 1) {
                if (!empty($_var_0['selfbuy'])) {
                    $_var_4 = $_var_149['id'];
                }
            }
            if (!empty($_var_4)) {
                pdo_update('sz_yi_order', array('agentid' => $_var_4), array('id' => $_var_1));
            }
            $this->calculate($_var_1);
        }

        public function checkOrderPay($_var_1 = '0')
        {
            global $_W, $_GPC;
            if (empty($_var_1)) {
                return;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return;
            }
            $_var_147 = pdo_fetch('select id,openid,ordersn,goodsprice,agentid,paytime from ' . tablename('sz_yi_order') . ' where id=:id and status>=1 and uniacid=:uniacid limit 1', array(':id' => $_var_1, ':uniacid' => $_W['uniacid']));
            if (empty($_var_147)) {
                return;
            }
            $_var_148 = $_var_147['openid'];
            $_var_149 = m('member')->getMember($_var_148);
            if (empty($_var_149)) {
                return;
            }
            $_var_150 = p('bonus');
            if (!empty($_var_150)) {
                $_var_151 = $_var_150->getSet();
                if (!empty($_var_151['start'])) {
                    $_var_150->checkOrderPay($_var_1);
                }
            }
            $_var_152 = intval($_var_0['become_child']);
            $_var_153 = false;
            if (empty($_var_152)) {
                $_var_153 = m('member')->getMember($_var_149['agentid']);
            } else {
                $_var_153 = m('member')->getMember($_var_149['inviter']);
            }
            $_var_154 = !empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1;
            $_var_155 = time();
            $_var_152 = intval($_var_0['become_child']);
            if ($_var_154) {
                if ($_var_152 == 2) {
                    if (empty($_var_149['agentid']) && $_var_149['id'] != $_var_153['id']) {
                        if (empty($_var_149['fixagentid'])) {
                            $_var_149['agentid'] = $_var_153['id'];
                            pdo_update('sz_yi_member', array('agentid' => $_var_153['id'], 'childtime' => $_var_155), array('uniacid' => $_W['uniacid'], 'id' => $_var_149['id']));
                            $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'childtime' => $_var_155), TM_COMMISSION_AGENT_NEW);
                            $this->upgradeLevelByAgent($_var_153['id']);
                            if (empty($_var_147['agentid'])) {
                                $_var_147['agentid'] = $_var_153['id'];
                                pdo_update('sz_yi_order', array('agentid' => $_var_153['id']), array('id' => $_var_1));
                                $this->calculate($_var_1);
                            }
                        }
                    }
                }
            }
            $_var_156 = $_var_149['isagent'] == 1 && $_var_149['status'] == 1;
            if (!$_var_156) {
                if (intval($_var_0['become']) == 4 && !empty($_var_0['become_goodsid'])) {
                    $_var_157 = pdo_fetchall('select goodsid from ' . tablename('sz_yi_order_goods') . ' where orderid=:orderid and uniacid=:uniacid  ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']), 'goodsid');
                    if (in_array($_var_0['become_goodsid'], array_keys($_var_157))) {
                        if (empty($_var_149['agentblack'])) {
                            pdo_update('sz_yi_member', array('status' => 1, 'isagent' => 1, 'agenttime' => $_var_155), array('uniacid' => $_W['uniacid'], 'id' => $_var_149['id']));
                            $this->sendMessage($_var_148, array('nickname' => $_var_149['nickname'], 'agenttime' => $_var_155), TM_COMMISSION_BECOME);
                            if (!empty($_var_153)) {
                                $this->upgradeLevelByAgent($_var_153['id']);
                            }
                        }
                    }
                }
            }
            if (!$_var_156 && empty($_var_0['become_order'])) {
                $_var_155 = time();
                if ($_var_0['become'] == 2 || $_var_0['become'] == 3) {
                    $_var_158 = true;
                    if (!empty($_var_149['agentid'])) {
                        $_var_153 = m('member')->getMember($_var_149['agentid']);
                        if (empty($_var_153) || $_var_153['isagent'] != 1 || $_var_153['status'] != 1) {
                            $_var_158 = false;
                        }
                    }
                    if ($_var_158) {
                        $_var_159 = false;
                        if ($_var_0['become'] == '2') {
                            $_var_160 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and status>=1 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_148));
                            $_var_159 = $_var_160 >= intval($_var_0['become_ordercount']);
                        } else {
                            if ($_var_0['become'] == '3') {
                                $_var_161 = pdo_fetchcolumn('select sum(og.realprice) from ' . tablename('sz_yi_order_goods') . ' og left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id  where o.openid=:openid and o.status>=1 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_148));
                                $_var_159 = $_var_161 >= floatval($_var_0['become_moneycount']);
                            }
                        }
                        if ($_var_159) {
                            if (empty($_var_149['agentblack'])) {
                                $_var_162 = intval($_var_0['become_check']);
                                pdo_update('sz_yi_member', array('status' => $_var_162, 'isagent' => 1, 'agenttime' => $_var_155), array('uniacid' => $_W['uniacid'], 'id' => $_var_149['id']));
                                if ($_var_162 == 1) {
                                    $this->sendMessage($_var_148, array('nickname' => $_var_149['nickname'], 'agenttime' => $_var_155), TM_COMMISSION_BECOME);
                                    if ($_var_158) {
                                        $this->upgradeLevelByAgent($_var_153['id']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($_var_147['agentid'])) {
                $_var_153 = m('member')->getMember($_var_147['agentid']);
                if (!empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1) {
                    if ($_var_147['agentid'] == $_var_153['id']) {
                        $_var_16 = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']));
                        $_var_5 = '';
                        $_var_8 = $_var_153['agentlevel'];
                        $_var_163 = 0;
                        $_var_164 = 0;
                        foreach ($_var_16 as $_var_165) {
                            $_var_5 .= "" . $_var_165['title'] . '( ';
                            if (!empty($_var_165['optiontitle'])) {
                                $_var_5 .= ' 规格: ' . $_var_165['optiontitle'];
                            }
                            $_var_5 .= ' 单价: ' . $_var_165['realprice'] / $_var_165['total'] . ' 数量: ' . $_var_165['total'] . ' 总价: ' . $_var_165['realprice'] . '); ';
                            $_var_166 = iunserializer($_var_165['commission1']);
                            $_var_163 += isset($_var_166['level' . $_var_8]) ? $_var_166['level' . $_var_8] : $_var_166['default'];
                            $_var_164 += $_var_165['realprice'];
                        }
                        $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'ordersn' => $_var_147['ordersn'], 'price' => $_var_164, 'goods' => $_var_5, 'commission' => $_var_163, 'paytime' => $_var_147['paytime']), TM_COMMISSION_ORDER_PAY);
                    }
                }
                if (!empty($_var_0['remind_message']) && $_var_0['level'] >= 2) {
                    if (!empty($_var_153['agentid'])) {
                        $_var_153 = m('member')->getMember($_var_153['agentid']);
                        if (!empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1) {
                            if ($_var_147['agentid'] != $_var_153['id']) {
                                $_var_16 = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission2 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']));
                                $_var_5 = '';
                                $_var_8 = $_var_153['agentlevel'];
                                $_var_163 = 0;
                                $_var_164 = 0;
                                foreach ($_var_16 as $_var_165) {
                                    $_var_5 .= "" . $_var_165['title'] . '( ';
                                    if (!empty($_var_165['optiontitle'])) {
                                        $_var_5 .= ' 规格: ' . $_var_165['optiontitle'];
                                    }
                                    $_var_5 .= ' 单价: ' . $_var_165['realprice'] / $_var_165['total'] . ' 数量: ' . $_var_165['total'] . ' 总价: ' . $_var_165['realprice'] . '); ';
                                    $_var_166 = iunserializer($_var_165['commission2']);
                                    $_var_163 += isset($_var_166['level' . $_var_8]) ? $_var_166['level' . $_var_8] : $_var_166['default'];
                                    $_var_164 += $_var_165['realprice'];
                                }
                                $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'ordersn' => $_var_147['ordersn'], 'price' => $_var_164, 'goods' => $_var_5, 'commission' => $_var_163, 'paytime' => $_var_147['paytime']), TM_COMMISSION_ORDER_PAY);
                            }
                        }
                        if (!empty($_var_153['agentid']) && $_var_0['level'] >= 3) {
                            $_var_153 = m('member')->getMember($_var_153['agentid']);
                            if (!empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1) {
                                if ($_var_147['agentid'] != $_var_153['id']) {
                                    $_var_16 = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission3 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']));
                                    $_var_5 = '';
                                    $_var_8 = $_var_153['agentlevel'];
                                    $_var_163 = 0;
                                    $_var_164 = 0;
                                    foreach ($_var_16 as $_var_165) {
                                        $_var_5 .= "" . $_var_165['title'] . '( ';
                                        if (!empty($_var_165['optiontitle'])) {
                                            $_var_5 .= ' 规格: ' . $_var_165['optiontitle'];
                                        }
                                        $_var_5 .= ' 单价: ' . $_var_165['realprice'] / $_var_165['total'] . ' 数量: ' . $_var_165['total'] . ' 总价: ' . $_var_165['realprice'] . '); ';
                                        $_var_166 = iunserializer($_var_165['commission3']);
                                        $_var_163 += isset($_var_166['level' . $_var_8]) ? $_var_166['level' . $_var_8] : $_var_166['default'];
                                        $_var_164 += $_var_165['realprice'];
                                    }
                                    $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'ordersn' => $_var_147['ordersn'], 'price' => $_var_164, 'goods' => $_var_5, 'commission' => $_var_163, 'paytime' => $_var_147['paytime']), TM_COMMISSION_ORDER_PAY);
                                }
                            }
                        }
                    }
                }
            }
        }

        public function checkOrderFinish($_var_1 = '')
        {
            global $_W, $_GPC;
            if (empty($_var_1)) {
                return;
            }
            $_var_147 = pdo_fetch('select id,openid, ordersn,goodsprice,agentid,finishtime from ' . tablename('sz_yi_order') . ' where id=:id and status>=3 and uniacid=:uniacid limit 1', array(':id' => $_var_1, ':uniacid' => $_W['uniacid']));
            if (empty($_var_147)) {
                return;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return;
            }
            $_var_148 = $_var_147['openid'];
            $_var_149 = m('member')->getMember($_var_148);
            if (empty($_var_149)) {
                return;
            }
            $_var_150 = p('bonus');
            if (!empty($_var_150)) {
                $_var_151 = $_var_150->getSet();
                if (!empty($_var_151['start'])) {
                    $_var_150->checkOrderFinish($_var_1);
                }
            }
            $_var_155 = time();
            $_var_156 = $_var_149['isagent'] == 1 && $_var_149['status'] == 1;
            if (!$_var_156 && $_var_0['become_order'] == 1) {
                if ($_var_0['become'] == 2 || $_var_0['become'] == 3) {
                    $_var_158 = true;
                    if (!empty($_var_149['agentid'])) {
                        $_var_153 = m('member')->getMember($_var_149['agentid']);
                        if (empty($_var_153) || $_var_153['isagent'] != 1 || $_var_153['status'] != 1) {
                            $_var_158 = false;
                        }
                    }
                    if ($_var_158) {
                        $_var_159 = false;
                        if ($_var_0['become'] == '2') {
                            $_var_160 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and status>=3 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_148));
                            $_var_159 = $_var_160 >= intval($_var_0['become_ordercount']);
                        } else {
                            if ($_var_0['become'] == '3') {
                                $_var_161 = pdo_fetchcolumn('select sum(goodsprice) from ' . tablename('sz_yi_order') . ' where openid=:openid and status>=3 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_148));
                                $_var_159 = $_var_161 >= floatval($_var_0['become_moneycount']);
                            }
                        }
                        if ($_var_159) {
                            if (empty($_var_149['agentblack'])) {
                                $_var_162 = intval($_var_0['become_check']);
                                pdo_update('sz_yi_member', array('status' => $_var_162, 'isagent' => 1, 'agenttime' => $_var_155), array('uniacid' => $_W['uniacid'], 'id' => $_var_149['id']));
                                if ($_var_162 == 1) {
                                    $this->sendMessage($_var_149['openid'], array('nickname' => $_var_149['nickname'], 'agenttime' => $_var_155), TM_COMMISSION_BECOME);
                                    if ($_var_158) {
                                        $this->upgradeLevelByAgent($_var_153['id']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($_var_147['agentid'])) {
                $_var_153 = m('member')->getMember($_var_147['agentid']);
                if (!empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1) {
                    if ($_var_147['agentid'] == $_var_153['id']) {
                        $_var_16 = pdo_fetchall('select g.id,g.title,og.total,og.realprice,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']));
                        $_var_5 = '';
                        $_var_8 = $_var_153['agentlevel'];
                        $_var_163 = 0;
                        $_var_164 = 0;
                        foreach ($_var_16 as $_var_165) {
                            $_var_5 .= "" . $_var_165['title'] . '( ';
                            if (!empty($_var_165['optiontitle'])) {
                                $_var_5 .= ' 规格: ' . $_var_165['optiontitle'];
                            }
                            $_var_5 .= ' 单价: ' . $_var_165['realprice'] / $_var_165['total'] . ' 数量: ' . $_var_165['total'] . ' 总价: ' . $_var_165['realprice'] . '); ';
                            $_var_166 = iunserializer($_var_165['commission1']);
                            $_var_163 += isset($_var_166['level' . $_var_8]) ? $_var_166['level' . $_var_8] : $_var_166['default'];
                            $_var_164 += $_var_165['realprice'];
                        }
                        $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'ordersn' => $_var_147['ordersn'], 'price' => $_var_164, 'goods' => $_var_5, 'commission' => $_var_163, 'finishtime' => $_var_147['finishtime']), TM_COMMISSION_ORDER_FINISH);
                    }
                }
                if (!empty($_var_0['remind_message']) && $_var_0['level'] >= 2) {
                    if (!empty($_var_153['agentid'])) {
                        $_var_153 = m('member')->getMember($_var_153['agentid']);
                        if (!empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1) {
                            if ($_var_147['agentid'] != $_var_153['id']) {
                                $_var_16 = pdo_fetchall('select g.id,g.title,og.total,og.realprice,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission2 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']));
                                $_var_5 = '';
                                $_var_8 = $_var_153['agentlevel'];
                                $_var_163 = 0;
                                $_var_164 = 0;
                                foreach ($_var_16 as $_var_165) {
                                    $_var_5 .= "" . $_var_165['title'] . '( ';
                                    if (!empty($_var_165['optiontitle'])) {
                                        $_var_5 .= ' 规格: ' . $_var_165['optiontitle'];
                                    }
                                    $_var_5 .= ' 单价: ' . $_var_165['realprice'] / $_var_165['total'] . ' 数量: ' . $_var_165['total'] . ' 总价: ' . $_var_165['realprice'] . '); ';
                                    $_var_166 = iunserializer($_var_165['commission2']);
                                    $_var_163 += isset($_var_166['level' . $_var_8]) ? $_var_166['level' . $_var_8] : $_var_166['default'];
                                    $_var_164 += $_var_165['realprice'];
                                }
                                $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'ordersn' => $_var_147['ordersn'], 'price' => $_var_164, 'goods' => $_var_5, 'commission' => $_var_163, 'finishtime' => $_var_147['finishtime']), TM_COMMISSION_ORDER_FINISH);
                            }
                        }
                        if (!empty($_var_153['agentid']) && $_var_0['level'] >= 3) {
                            $_var_153 = m('member')->getMember($_var_153['agentid']);
                            if (!empty($_var_153) && $_var_153['isagent'] == 1 && $_var_153['status'] == 1) {
                                if ($_var_147['agentid'] != $_var_153['id']) {
                                    $_var_16 = pdo_fetchall('select g.id,g.title,og.total,og.realprice,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission3 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_147['id']));
                                    $_var_5 = '';
                                    $_var_8 = $_var_153['agentlevel'];
                                    $_var_163 = 0;
                                    $_var_164 = 0;
                                    foreach ($_var_16 as $_var_165) {
                                        $_var_5 .= "" . $_var_165['title'] . '( ';
                                        if (!empty($_var_165['optiontitle'])) {
                                            $_var_5 .= ' 规格: ' . $_var_165['optiontitle'];
                                        }
                                        $_var_5 .= ' 单价: ' . $_var_165['realprice'] / $_var_165['total'] . ' 数量: ' . $_var_165['total'] . ' 总价: ' . $_var_165['realprice'] . '); ';
                                        $_var_166 = iunserializer($_var_165['commission3']);
                                        $_var_163 += isset($_var_166['level' . $_var_8]) ? $_var_166['level' . $_var_8] : $_var_166['default'];
                                        $_var_164 += $_var_165['realprice'];
                                    }
                                    $this->sendMessage($_var_153['openid'], array('nickname' => $_var_149['nickname'], 'ordersn' => $_var_147['ordersn'], 'price' => $_var_164, 'goods' => $_var_5, 'commission' => $_var_163, 'finishtime' => $_var_147['finishtime']), TM_COMMISSION_ORDER_FINISH);
                                }
                            }
                        }
                    }
                }
            }
            $this->upgradeLevelByOrder($_var_148);
            $this->upgradeLevelByGood($_var_1);
        }

        function getShop($_var_167)
        {
            global $_W;
            $_var_39 = m('member')->getMember($_var_167);
            $_var_168 = pdo_fetch('select * from ' . tablename('sz_yi_commission_shop') . ' where uniacid=:uniacid and mid=:mid limit 1', array(':uniacid' => $_W['uniacid'], ':mid' => $_var_39['id']));
            $_var_169 = m('common')->getSysset(array('shop', 'share'));
            $_var_0 = $_var_169['shop'];
            $_var_170 = $_var_169['share'];
            $_var_171 = $_var_170['desc'];
            if (empty($_var_171)) {
                $_var_171 = $_var_0['description'];
            }
            if (empty($_var_171)) {
                $_var_171 = $_var_0['name'];
            }
            $_var_172 = $this->getSet();
            if (empty($_var_168)) {
                $_var_168 = array('name' => $_var_39['nickname'] . '的' . $_var_172['texts']['shop'], 'logo' => $_var_39['avatar'], 'desc' => $_var_171, 'img' => tomedia($_var_0['img']));
            } else {
                if (empty($_var_168['name'])) {
                    $_var_168['name'] = $_var_39['nickname'] . '的' . $_var_172['texts']['shop'];
                }
                if (empty($_var_168['logo'])) {
                    $_var_168['logo'] = tomedia($_var_39['avatar']);
                }
                if (empty($_var_168['img'])) {
                    $_var_168['img'] = tomedia($_var_0['img']);
                }
                if (empty($_var_168['desc'])) {
                    $_var_168['desc'] = $_var_171;
                }
            }
            return $_var_168;
        }

        function getLevels($_var_173 = true)
        {
            global $_W;
            if ($_var_173) {
                return pdo_fetchall('select * from ' . tablename('sz_yi_commission_level') . ' where uniacid=:uniacid order by commission1 asc', array(':uniacid' => $_W['uniacid']));
            } else {
                return pdo_fetchall('select * from ' . tablename('sz_yi_commission_level') . ' where uniacid=:uniacid and (ordermoney>0 or commissionmoney>0) order by commission1 asc', array(':uniacid' => $_W['uniacid']));
            }
        }

        function getLevel($_var_36)
        {
            global $_W;
            if (empty($_var_36)) {
                return false;
            }
            $_var_39 = m('member')->getMember($_var_36);
            if (empty($_var_39['agentlevel'])) {
                return false;
            }
            $_var_38 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_39['agentlevel']));
            return $_var_38;
        }

        function upgradeLevelByOrder($_var_36)
        {
            global $_W;
            if (empty($_var_36)) {
                return false;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return false;
            }
            $_var_167 = m('member')->getMember($_var_36);
            if (empty($_var_167)) {
                return;
            }
            $_var_150 = p('bonus');
            if (!empty($_var_150)) {
                $_var_151 = $_var_150->getSet();
                if (!empty($_var_151['start'])) {
                    $_var_150->upgradeLevelByAgent($_var_36);
                }
            }
            $_var_174 = intval($_var_0['leveltype']);
            if ($_var_174 == 4 || $_var_174 == 5) {
                if (!empty($_var_167['agentnotupgrade'])) {
                    return;
                }
                $_var_175 = $this->getLevel($_var_167['openid']);
                if (empty($_var_175['id'])) {
                    $_var_175 = array('levelname' => empty($_var_0['levelname']) ? '普通等级' : $_var_0['levelname'], 'commission1' => $_var_0['commission1'], 'commission2' => $_var_0['commission2'], 'commission3' => $_var_0['commission3']);
                }
                $_var_176 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.openid=:openid and o.status>=3 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_36));
                $_var_47 = $_var_176['ordermoney'];
                $_var_46 = $_var_176['ordercount'];
                if ($_var_174 == 4) {
                    $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_47} >= ordermoney and ordermoney>0  order by ordermoney desc limit 1", array(':uniacid' => $_W['uniacid']));
                    if (empty($_var_177)) {
                        return;
                    }
                    if (!empty($_var_175['id'])) {
                        if ($_var_175['id'] == $_var_177['id']) {
                            return;
                        }
                        if ($_var_175['ordermoney'] > $_var_177['ordermoney']) {
                            return;
                        }
                    }
                } else {
                    if ($_var_174 == 5) {
                        $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_46} >= ordercount and ordercount>0  order by ordercount desc limit 1", array(':uniacid' => $_W['uniacid']));
                        if (empty($_var_177)) {
                            return;
                        }
                        if (!empty($_var_175['id'])) {
                            if ($_var_175['id'] == $_var_177['id']) {
                                return;
                            }
                            if ($_var_175['ordercount'] > $_var_177['ordercount']) {
                                return;
                            }
                        }
                    }
                }
                pdo_update('sz_yi_member', array('agentlevel' => $_var_177['id']), array('id' => $_var_167['id']));
                $this->sendMessage($_var_167['openid'], array('nickname' => $_var_167['nickname'], 'oldlevel' => $_var_175, 'newlevel' => $_var_177), TM_COMMISSION_UPGRADE);
            } else {
                if ($_var_174 >= 0 && $_var_174 <= 3) {
                    $_var_96 = array();
                    if (!empty($_var_0['selfbuy'])) {
                        $_var_96[] = $_var_167;
                    }
                    if (!empty($_var_167['agentid'])) {
                        $_var_30 = m('member')->getMember($_var_167['agentid']);
                        if (!empty($_var_30)) {
                            $_var_96[] = $_var_30;
                            if (!empty($_var_30['agentid']) && $_var_30['isagent'] == 1 && $_var_30['status'] == 1) {
                                $_var_32 = m('member')->getMember($_var_30['agentid']);
                                if (!empty($_var_32) && $_var_32['isagent'] == 1 && $_var_32['status'] == 1) {
                                    $_var_96[] = $_var_32;
                                    if (empty($_var_0['selfbuy'])) {
                                        if (!empty($_var_32['agentid']) && $_var_32['isagent'] == 1 && $_var_32['status'] == 1) {
                                            $_var_34 = m('member')->getMember($_var_32['agentid']);
                                            if (!empty($_var_34) && $_var_34['isagent'] == 1 && $_var_34['status'] == 1) {
                                                $_var_96[] = $_var_34;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (empty($_var_96)) {
                        return;
                    }
                    foreach ($_var_96 as $_var_178) {
                        $_var_179 = $this->getInfo($_var_178['id'], array('ordercount3', 'ordermoney3', 'order13money', 'order13'));
                        if (!empty($_var_179['agentnotupgrade'])) {
                            continue;
                        }
                        $_var_175 = $this->getLevel($_var_178['openid']);
                        if (empty($_var_175['id'])) {
                            $_var_175 = array('levelname' => empty($_var_0['levelname']) ? '普通等级' : $_var_0['levelname'], 'commission1' => $_var_0['commission1'], 'commission2' => $_var_0['commission2'], 'commission3' => $_var_0['commission3']);
                        }
                        if ($_var_174 == 0) {
                            $_var_47 = $_var_179['ordermoney3'];
                            $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid and {$_var_47} >= ordermoney and ordermoney>0  order by ordermoney desc limit 1", array(':uniacid' => $_W['uniacid']));
                            if (empty($_var_177)) {
                                continue;
                            }
                            if (!empty($_var_175['id'])) {
                                if ($_var_175['id'] == $_var_177['id']) {
                                    continue;
                                }
                                if ($_var_175['ordermoney'] > $_var_177['ordermoney']) {
                                    continue;
                                }
                            }
                        } else {
                            if ($_var_174 == 1) {
                                $_var_47 = $_var_179['order13money'];
                                $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid and {$_var_47} >= ordermoney and ordermoney>0  order by ordermoney desc limit 1", array(':uniacid' => $_W['uniacid']));
                                if (empty($_var_177)) {
                                    continue;
                                }
                                if (!empty($_var_175['id'])) {
                                    if ($_var_175['id'] == $_var_177['id']) {
                                        continue;
                                    }
                                    if ($_var_175['ordermoney'] > $_var_177['ordermoney']) {
                                        continue;
                                    }
                                }
                            } else {
                                if ($_var_174 == 2) {
                                    $_var_46 = $_var_179['ordercount3'];
                                    $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_46} >= ordercount and ordercount>0  order by ordercount desc limit 1", array(':uniacid' => $_W['uniacid']));
                                    if (empty($_var_177)) {
                                        continue;
                                    }
                                    if (!empty($_var_175['id'])) {
                                        if ($_var_175['id'] == $_var_177['id']) {
                                            continue;
                                        }
                                        if ($_var_175['ordercount'] > $_var_177['ordercount']) {
                                            continue;
                                        }
                                    }
                                } else {
                                    if ($_var_174 == 3) {
                                        $_var_46 = $_var_179['order13'];
                                        $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_46} >= ordercount and ordercount>0  order by ordercount desc limit 1", array(':uniacid' => $_W['uniacid']));
                                        if (empty($_var_177)) {
                                            continue;
                                        }
                                        if (!empty($_var_175['id'])) {
                                            if ($_var_175['id'] == $_var_177['id']) {
                                                continue;
                                            }
                                            if ($_var_175['ordercount'] > $_var_177['ordercount']) {
                                                continue;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        pdo_update('sz_yi_member', array('agentlevel' => $_var_177['id']), array('id' => $_var_178['id']));
                        $this->sendMessage($_var_178['openid'], array('nickname' => $_var_178['nickname'], 'oldlevel' => $_var_175, 'newlevel' => $_var_177), TM_COMMISSION_UPGRADE);
                    }
                }
            }
        }

        function upgradeLevelByAgent($_var_36)
        {
            global $_W;
            if (empty($_var_36)) {
                return false;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return false;
            }
            $_var_167 = m('member')->getMember($_var_36);
            if (empty($_var_167)) {
                return;
            }
            $_var_150 = p('bonus');
            if (!empty($_var_150)) {
                $_var_151 = $_var_150->getSet();
                if (!empty($_var_151['start'])) {
                    $_var_150->upgradeLevelByAgent($_var_36);
                }
            }
            $_var_174 = intval($_var_0['leveltype']);
            if ($_var_174 < 6 || $_var_174 > 9) {
                return;
            }
            $_var_179 = $this->getInfo($_var_167['id'], array());
            if ($_var_174 == 6 || $_var_174 == 8) {
                $_var_96 = array($_var_167);
                if (!empty($_var_167['agentid'])) {
                    $_var_30 = m('member')->getMember($_var_167['agentid']);
                    if (!empty($_var_30)) {
                        $_var_96[] = $_var_30;
                        if (!empty($_var_30['agentid']) && $_var_30['isagent'] == 1 && $_var_30['status'] == 1) {
                            $_var_32 = m('member')->getMember($_var_30['agentid']);
                            if (!empty($_var_32) && $_var_32['isagent'] == 1 && $_var_32['status'] == 1) {
                                $_var_96[] = $_var_32;
                            }
                        }
                    }
                }
                if (empty($_var_96)) {
                    return;
                }
                foreach ($_var_96 as $_var_178) {
                    $_var_179 = $this->getInfo($_var_178['id'], array());
                    if (!empty($_var_179['agentnotupgrade'])) {
                        continue;
                    }
                    $_var_175 = $this->getLevel($_var_178['openid']);
                    if (empty($_var_175['id'])) {
                        $_var_175 = array('levelname' => empty($_var_0['levelname']) ? '普通等级' : $_var_0['levelname'], 'commission1' => $_var_0['commission1'], 'commission2' => $_var_0['commission2'], 'commission3' => $_var_0['commission3']);
                    }
                    if ($_var_174 == 6) {
                        $_var_180 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid=:agentid and uniacid=:uniacid ', array(':agentid' => $_var_167['id'], ':uniacid' => $_W['uniacid']), 'id');
                        $_var_181 += count($_var_180);
                        if (!empty($_var_180)) {
                            $_var_182 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid in( ' . implode(',', array_keys($_var_180)) . ') and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']), 'id');
                            $_var_181 += count($_var_182);
                            if (!empty($_var_182)) {
                                $_var_183 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid in( ' . implode(',', array_keys($_var_182)) . ') and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']), 'id');
                                $_var_181 += count($_var_183);
                            }
                        }
                        $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_181} >= downcount and downcount>0  order by downcount desc limit 1", array(':uniacid' => $_W['uniacid']));
                    } else {
                        if ($_var_174 == 8) {
                            $_var_181 = $_var_179['level1'] + $_var_179['level2'] + $_var_179['level3'];
                            $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_181} >= downcount and downcount>0  order by downcount desc limit 1", array(':uniacid' => $_W['uniacid']));
                        }
                    }
                    if (empty($_var_177)) {
                        continue;
                    }
                    if ($_var_177['id'] == $_var_175['id']) {
                        continue;
                    }
                    if (!empty($_var_175['id'])) {
                        if ($_var_175['downcount'] > $_var_177['downcount']) {
                            continue;
                        }
                    }
                    pdo_update('sz_yi_member', array('agentlevel' => $_var_177['id']), array('id' => $_var_178['id']));
                    $this->sendMessage($_var_178['openid'], array('nickname' => $_var_178['nickname'], 'oldlevel' => $_var_175, 'newlevel' => $_var_177), TM_COMMISSION_UPGRADE);
                }
            } else {
                if (!empty($_var_167['agentnotupgrade'])) {
                    return;
                }
                $_var_175 = $this->getLevel($_var_167['openid']);
                if (empty($_var_175['id'])) {
                    $_var_175 = array('levelname' => empty($_var_0['levelname']) ? '普通等级' : $_var_0['levelname'], 'commission1' => $_var_0['commission1'], 'commission2' => $_var_0['commission2'], 'commission3' => $_var_0['commission3']);
                }
                if ($_var_174 == 7) {
                    $_var_181 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member') . ' where agentid=:agentid and uniacid=:uniacid ', array(':agentid' => $_var_167['id'], ':uniacid' => $_W['uniacid']));
                    $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_181} >= downcount and downcount>0  order by downcount desc limit 1", array(':uniacid' => $_W['uniacid']));
                } else {
                    if ($_var_174 == 9) {
                        $_var_181 = $_var_179['level1'];
                        $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_181} >= downcount and downcount>0  order by downcount desc limit 1", array(':uniacid' => $_W['uniacid']));
                    }
                }
                if (empty($_var_177)) {
                    return;
                }
                if ($_var_177['id'] == $_var_175['id']) {
                    return;
                }
                if (!empty($_var_175['id'])) {
                    if ($_var_175['downcount'] > $_var_177['downcount']) {
                        return;
                    }
                }
                pdo_update('sz_yi_member', array('agentlevel' => $_var_177['id']), array('id' => $_var_167['id']));
                $this->sendMessage($_var_167['openid'], array('nickname' => $_var_167['nickname'], 'oldlevel' => $_var_175, 'newlevel' => $_var_177), TM_COMMISSION_UPGRADE);
            }
        }

        function upgradeLevelByCommissionOK($_var_36)
        {
            global $_W;
            if (empty($_var_36)) {
                return false;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['level'])) {
                return false;
            }
            $_var_167 = m('member')->getMember($_var_36);
            if (empty($_var_167)) {
                return;
            }
            $_var_150 = p('bonus');
            if (!empty($_var_150)) {
                $_var_151 = $_var_150->getSet();
                if (!empty($_var_151['start'])) {
                    $_var_150->upgradeLevelByAgent($_var_36);
                }
            }
            $_var_174 = intval($_var_0['leveltype']);
            if ($_var_174 != 10) {
                return;
            }
            if (!empty($_var_167['agentnotupgrade'])) {
                return;
            }
            $_var_175 = $this->getLevel($_var_167['openid']);
            if (empty($_var_175['id'])) {
                $_var_175 = array('levelname' => empty($_var_0['levelname']) ? '普通等级' : $_var_0['levelname'], 'commission1' => $_var_0['commission1'], 'commission2' => $_var_0['commission2'], 'commission3' => $_var_0['commission3']);
            }
            $_var_179 = $this->getInfo($_var_167['id'], array('pay'));
            $_var_184 = $_var_179['commission_pay'];
            $_var_177 = pdo_fetch('select * from ' . tablename('sz_yi_commission_level') . " where uniacid=:uniacid  and {$_var_184} >= commissionmoney and commissionmoney>0  order by commissionmoney desc limit 1", array(':uniacid' => $_W['uniacid']));
            if (empty($_var_177)) {
                return;
            }
            if ($_var_175['id'] == $_var_177['id']) {
                return;
            }
            if (!empty($_var_175['id'])) {
                if ($_var_175['commissionmoney'] > $_var_177['commissionmoney']) {
                    return;
                }
            }
            pdo_update('sz_yi_member', array('agentlevel' => $_var_177['id']), array('id' => $_var_167['id']));
            $this->sendMessage($_var_167['openid'], array('nickname' => $_var_167['nickname'], 'oldlevel' => $_var_175, 'newlevel' => $_var_177), TM_COMMISSION_UPGRADE);
        }

        function sendMessage($_var_36 = '', $_var_185 = array(), $_var_186 = '')
        {
            global $_W, $_GPC;
            $_var_0 = $this->getSet();
            $_var_187 = $_var_0['tm'];
            $_var_188 = $_var_187['templateid'];
            $_var_39 = m('member')->getMember($_var_36);
            $_var_189 = unserialize($_var_39['noticeset']);
            if (!is_array($_var_189)) {
                $_var_189 = array();
            }
            if ($_var_186 == TM_COMMISSION_AGENT_NEW && !empty($_var_187['commission_agent_new']) && empty($_var_189['commission_agent_new'])) {
                $_var_190 = $_var_187['commission_agent_new'];
                $_var_190 = str_replace('[昵称]', $_var_185['nickname'], $_var_190);
                $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_185['childtime']), $_var_190);
                $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_agent_newtitle']) ? $_var_187['commission_agent_newtitle'] : '新增下线通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                if (!empty($_var_188)) {
                    m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                } else {
                    m('message')->sendCustomNotice($_var_36, $_var_191);
                }
            } else {
                if ($_var_186 == TM_COMMISSION_ORDER_PAY && !empty($_var_187['commission_order_pay']) && empty($_var_189['commission_order_pay'])) {
                    $_var_190 = $_var_187['commission_order_pay'];
                    $_var_190 = str_replace('[昵称]', $_var_185['nickname'], $_var_190);
                    $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_185['paytime']), $_var_190);
                    $_var_190 = str_replace('[订单编号]', $_var_185['ordersn'], $_var_190);
                    $_var_190 = str_replace('[订单金额]', $_var_185['price'], $_var_190);
                    $_var_190 = str_replace('[佣金金额]', $_var_185['commission'], $_var_190);
                    $_var_190 = str_replace('[商品详情]', $_var_185['goods'], $_var_190);
                    $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_order_paytitle']) ? $_var_187['commission_order_paytitle'] : '下线付款通知'), 'keyword2' => array('value' => $_var_190));
                    if (!empty($_var_188)) {
                        m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                    } else {
                        m('message')->sendCustomNotice($_var_36, $_var_191);
                    }
                } else {
                    if ($_var_186 == TM_COMMISSION_ORDER_FINISH && !empty($_var_187['commission_order_finish']) && empty($_var_189['commission_order_finish'])) {
                        $_var_190 = $_var_187['commission_order_finish'];
                        $_var_190 = str_replace('[昵称]', $_var_185['nickname'], $_var_190);
                        $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_185['finishtime']), $_var_190);
                        $_var_190 = str_replace('[订单编号]', $_var_185['ordersn'], $_var_190);
                        $_var_190 = str_replace('[订单金额]', $_var_185['price'], $_var_190);
                        $_var_190 = str_replace('[佣金金额]', $_var_185['commission'], $_var_190);
                        $_var_190 = str_replace('[商品详情]', $_var_185['goods'], $_var_190);
                        $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_order_finishtitle']) ? $_var_187['commission_order_finishtitle'] : '下线确认收货通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                        if (!empty($_var_188)) {
                            m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                        } else {
                            m('message')->sendCustomNotice($_var_36, $_var_191);
                        }
                    } else {
                        if ($_var_186 == TM_COMMISSION_APPLY && !empty($_var_187['commission_apply']) && empty($_var_189['commission_apply'])) {
                            $_var_190 = $_var_187['commission_apply'];
                            $_var_190 = str_replace('[昵称]', $_var_39['nickname'], $_var_190);
                            $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_190);
                            $_var_190 = str_replace('[金额]', $_var_185['commission'], $_var_190);
                            $_var_190 = str_replace('[提现方式]', $_var_185['type'], $_var_190);
                            $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_applytitle']) ? $_var_187['commission_applytitle'] : '提现申请提交成功', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                            if (!empty($_var_188)) {
                                m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                            } else {
                                m('message')->sendCustomNotice($_var_36, $_var_191);
                            }
                        } else {
                            if ($_var_186 == TM_COMMISSION_CHECK && !empty($_var_187['commission_check']) && empty($_var_189['commission_check'])) {
                                $_var_190 = $_var_187['commission_check'];
                                $_var_190 = str_replace('[昵称]', $_var_39['nickname'], $_var_190);
                                $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_190);
                                $_var_190 = str_replace('[金额]', $_var_185['commission'], $_var_190);
                                $_var_190 = str_replace('[提现方式]', $_var_185['type'], $_var_190);
                                $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_checktitle']) ? $_var_187['commission_checktitle'] : '提现申请审核处理完成', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                                if (!empty($_var_188)) {
                                    m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                                } else {
                                    m('message')->sendCustomNotice($_var_36, $_var_191);
                                }
                            } else {
                                if ($_var_186 == TM_COMMISSION_PAY && !empty($_var_187['commission_pay']) && empty($_var_189['commission_pay'])) {
                                    $_var_190 = $_var_187['commission_pay'];
                                    $_var_190 = str_replace('[昵称]', $_var_39['nickname'], $_var_190);
                                    $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_190);
                                    $_var_190 = str_replace('[金额]', $_var_185['commission'], $_var_190);
                                    $_var_190 = str_replace('[提现方式]', $_var_185['type'], $_var_190);
                                    $_var_190 = str_replace('[微信比例]', $_var_0['withdraw_wechat'], $_var_190);
                                    $_var_190 = str_replace('[商城余额比例]', $_var_0['withdraw_balance'], $_var_190);
                                    $_var_190 = str_replace('[税费和服务费比例]', $_var_0['withdraw_factorage'], $_var_190);
                                    $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_paytitle']) ? $_var_187['commission_paytitle'] : '佣金打款通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                                    if (!empty($_var_188)) {
                                        m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                                    } else {
                                        m('message')->sendCustomNotice($_var_36, $_var_191);
                                    }
                                } else {
                                    if ($_var_186 == TM_COMMISSION_UPGRADE && !empty($_var_187['commission_upgrade']) && empty($_var_189['commission_upgrade'])) {
                                        $_var_190 = $_var_187['commission_upgrade'];
                                        $_var_190 = str_replace('[昵称]', $_var_39['nickname'], $_var_190);
                                        $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_190);
                                        $_var_190 = str_replace('[旧等级]', $_var_185['oldlevel']['levelname'], $_var_190);
                                        $_var_190 = str_replace('[旧一级分销比例]', $_var_185['oldlevel']['commission1'] . '%', $_var_190);
                                        $_var_190 = str_replace('[旧二级分销比例]', $_var_185['oldlevel']['commission2'] . '%', $_var_190);
                                        $_var_190 = str_replace('[旧三级分销比例]', $_var_185['oldlevel']['commission3'] . '%', $_var_190);
                                        $_var_190 = str_replace('[新等级]', $_var_185['newlevel']['levelname'], $_var_190);
                                        $_var_190 = str_replace('[新一级分销比例]', $_var_185['newlevel']['commission1'] . '%', $_var_190);
                                        $_var_190 = str_replace('[新二级分销比例]', $_var_185['newlevel']['commission2'] . '%', $_var_190);
                                        $_var_190 = str_replace('[新三级分销比例]', $_var_185['newlevel']['commission3'] . '%', $_var_190);
                                        $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_upgradetitle']) ? $_var_187['commission_upgradetitle'] : '分销等级升级通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                                        if (!empty($_var_188)) {
                                            m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                                        } else {
                                            m('message')->sendCustomNotice($_var_36, $_var_191);
                                        }
                                    } else {
                                        if ($_var_186 == TM_COMMISSION_BECOME && !empty($_var_187['commission_become']) && empty($_var_189['commission_become'])) {
                                            $_var_190 = $_var_187['commission_become'];
                                            $_var_190 = str_replace('[昵称]', $_var_185['nickname'], $_var_190);
                                            $_var_190 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_185['agenttime']), $_var_190);
                                            $_var_191 = array('keyword1' => array('value' => !empty($_var_187['commission_becometitle']) ? $_var_187['commission_becometitle'] : '成为分销商通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_190, 'color' => '#73a68d'));
                                            if (!empty($_var_188)) {
                                                m('message')->sendTplNotice($_var_36, $_var_188, $_var_191);
                                            } else {
                                                m('message')->sendCustomNotice($_var_36, $_var_191);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        function perms()
        {
            return array('commission' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('cover' => array('text' => '入口设置'), 'agent' => array('text' => '分销商', 'view' => '浏览', 'check' => '审核-log', 'edit' => '修改-log', 'agentblack' => '黑名单操作-log', 'delete' => '删除-log', 'user' => '查看下线', 'order' => '查看推广订单(还需有订单权限)', 'changeagent' => '设置分销商'), 'level' => array('text' => '分销商等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'apply' => array('text' => '佣金审核', 'view1' => '浏览待审核', 'view2' => '浏览已审核', 'view3' => '浏览已打款', 'view_1' => '浏览无效', 'export1' => '导出待审核-log', 'export2' => '导出已审核-log', 'export3' => '导出已打款-log', 'export_1' => '导出无效-log', 'check' => '审核-log', 'pay' => '打款-log', 'cancel' => '重新审核-log'), 'notice' => array('text' => '通知设置-log'), 'increase' => array('text' => '分销商趋势图'), 'changecommission' => array('text' => '修改佣金-log'), 'set' => array('text' => '基础设置-log'))));
        }

        function upgradeLevelByGood($_var_1)
        {
            global $_W;
            $_var_0 = $this->getSet();
            if (!$_var_0['upgrade_by_good']) {
                return;
            }
            $_var_5 = pdo_fetch('select g.commission_level_id from ' . tablename('sz_yi_order_goods') . ' AS og, ' . tablename('sz_yi_goods') . ' AS g WHERE og.goodsid = g.id AND og.orderid=:orderid AND og.uniacid=:uniacid LIMIT 1', array(':orderid' => $_var_1, ':uniacid' => $_W['uniacid']));
            $_var_192 = $_var_5['commission_level_id'];
            if ($_var_192) {
                $_var_3 = $this->getLevels();
                foreach ($_var_3 as $_var_8) {
                    if ($_var_8['id'] == $_var_192) {
                        $_var_193 = $_var_8['commission1'];
                        $_var_194 = $_var_8['commission2'];
                        $_var_195 = $_var_8['commission3'];
                    }
                }
                $_var_148 = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_order') . ' where uniacid=:uniacid and id=:orderid', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_1));
                $_var_196 = $this->getLevel($_var_148);
                if (!$_var_196 || $_var_196['commission1'] < $_var_193 || $_var_196['commission2'] < $_var_194 || $_var_196['commission3'] < $_var_195) {
                    pdo_update('sz_yi_member', array('agentlevel' => $_var_192), array('uniacid' => $_W['uniacid'], 'openid' => $_var_148));
                }
            }
        }
    }
}