<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
define('TM_COMMISSION_AGENT_NEW', 'commission_agent_new');
define('TM_BONUS_ORDER_PAY', 'bonus_order_pay');
define('TM_BONUS_ORDER_FINISH', 'bonus_order_finish');
define('TM_COMMISSION_APPLY', 'commission_apply');
define('TM_COMMISSION_CHECK', 'commission_check');
define('TM_BONUS_PAY', 'bonus_pay');
define('TM_BONUS_GLOBAL_PAY', 'bonus_global_pay');
define('TM_BONUS_UPGRADE', 'bonus_upgrade');
define('TM_COMMISSION_BECOME', 'commission_become');
if (!class_exists('BonusModel')) {
    class BonusModel extends PluginModel
    {
        private $agents = array();
        private $parentAgents = array();

        public function getSet()
        {
            $_var_0 = parent::getSet();
            $_var_0['texts'] = array('agent' => empty($_var_0['texts']['agent']) ? '代理商' : $_var_0['texts']['agent'], 'premiername' => empty($_var_0['texts']['premiername']) ? '全球分红' : $_var_0['texts']['premiername'], 'center' => empty($_var_0['texts']['center']) ? '分红中心' : $_var_0['texts']['center'], 'commission' => empty($_var_0['texts']['commission']) ? '佣金' : $_var_0['texts']['commission'], 'commission1' => empty($_var_0['texts']['commission1']) ? '分红佣金' : $_var_0['texts']['commission1'], 'commission_total' => empty($_var_0['texts']['commission_total']) ? '累计分红佣金' : $_var_0['texts']['commission_total'], 'commission_ok' => empty($_var_0['texts']['commission_ok']) ? '待分红佣金' : $_var_0['texts']['commission_ok'], 'commission_apply' => empty($_var_0['texts']['commission_apply']) ? '已申请佣金' : $_var_0['texts']['commission_apply'], 'commission_check' => empty($_var_0['texts']['commission_check']) ? '待打款佣金' : $_var_0['texts']['commission_check'], 'commission_lock' => empty($_var_0['texts']['commission_lock']) ? '未结算佣金' : $_var_0['texts']['commission_lock'], 'commission_detail' => empty($_var_0['texts']['commission_detail']) ? '分红明细' : $_var_0['texts']['commission_detail'], 'commission_pay' => empty($_var_0['texts']['commission_pay']) ? '已分红佣金' : $_var_0['texts']['commission_pay'], 'order' => empty($_var_0['texts']['order']) ? '分红订单' : $_var_0['texts']['order'], 'order_area' => empty($_var_0['texts']['order_area']) ? '区域订单' : $_var_0['texts']['order_area'], 'mycustomer' => empty($_var_0['texts']['mycustomer']) ? '我的下线' : $_var_0['texts']['mycustomer'], 'agent_province' => empty($_var_0['texts']['agent_province']) ? '省级代理' : $_var_0['texts']['agent_province'], 'agent_city' => empty($_var_0['texts']['agent_city']) ? '市级代理' : $_var_0['texts']['agent_city'], 'agent_district' => empty($_var_0['texts']['agent_district']) ? '区级代理' : $_var_0['texts']['agent_district']);
            return $_var_0;
        }

        public function getParentAgents($_var_1, $_var_2 = 0)
        {
            global $_W;
            $_var_3 = 'select id, agentid, bonuslevel, bonus_status from ' . tablename('sz_yi_member') . " where id={$_var_1} and uniacid=" . $_W['uniacid'];
            $_var_4 = pdo_fetch($_var_3);
            if (empty($_var_4)) {
                return $this->parentAgents;
            } else {
                if (empty($this->parentAgents[$_var_4['bonuslevel']])) {
                    $this->parentAgents[$_var_4['bonuslevel']] = $_var_4['id'];
                }
                if ($_var_4['agentid'] != 0) {
                    return $this->getParentAgents($_var_4['agentid']);
                } else {
                    return $this->parentAgents;
                }
            }
        }

        public function calculate($_var_5 = 0, $_var_6 = true)
        {
            global $_W;
            $_var_0 = $this->getSet();
            $_var_7 = $this->getLevels();
            $_var_8 = time();
            $_var_9 = pdo_fetch('select openid, address from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $_var_5));
            $_var_10 = $_var_9['openid'];
            $_var_11 = unserialize($_var_9['address']);
            $_var_12 = pdo_fetchall('select og.id,og.realprice,og.price,og.goodsid,og.total,og.optionname,g.hascommission,g.nocommission,g.bonusmoney from ' . tablename('sz_yi_order_goods') . '  og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id = og.goodsid' . ' where og.orderid=:orderid and og.uniacid=:uniacid', array(':orderid' => $_var_5, ':uniacid' => $_W['uniacid']));
            $_var_13 = m('member')->getInfo($_var_10);
            $_var_7 = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_bonus_level') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY level asc");
            foreach ($_var_12 as $_var_14) {
                $_var_15 = $_var_14['bonusmoney'] > 0 && !empty($_var_14['bonusmoney']) ? $_var_14['bonusmoney'] * $_var_14['total'] : $_var_14['price'];
                if (empty($_var_0['selfbuy'])) {
                    $_var_16 = $_var_13['agentid'];
                } else {
                    $_var_16 = $_var_13['id'];
                }
                if (!empty($_var_16)) {
                    $_var_17 = $this->getParentAgents($_var_16, 1);
                    $_var_18 = 0;
                    foreach ($_var_7 as $_var_19 => $_var_20) {
                        $_var_21 = $_var_20['id'];
                        if (array_key_exists($_var_21, $_var_17)) {
                            if ($_var_20['agent_money'] > 0) {
                                $_var_22 = $_var_20['agent_money'] / 100;
                            } else {
                                continue;
                            }
                            $_var_23 = round($_var_15 * $_var_22, 2);
                            if (empty($_var_0['isdistinction'])) {
                                $_var_24 = $_var_23 - $_var_18;
                                $_var_18 = $_var_23;
                            } else {
                                $_var_24 = $_var_23;
                            }
                            $_var_25 = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $_var_14['goodsid'], 'orderid' => $_var_5, 'total' => $_var_14['total'], 'optionname' => $_var_14['optionname'], 'mid' => $_var_17[$_var_21], 'levelid' => $_var_21, 'money' => $_var_24, 'createtime' => $_var_8);
                            pdo_insert('sz_yi_bonus_goods', $_var_25);
                        }
                    }
                }
                if (!empty($_var_0['area_start'])) {
                    $_var_26 = 0;
                    $_var_27 = floatval($_var_0['bonus_commission1']);
                    if (!empty($_var_27)) {
                        $_var_28 = pdo_fetch('select id, bonus_area_commission from ' . tablename('sz_yi_member') . ' where bonus_province=\'' . $_var_11['province'] . '\' and bonus_area=1 and uniacid=' . $_W['uniacid']);
                        if (!empty($_var_28)) {
                            if ($_var_28['bonus_area_commission'] > 0) {
                                $_var_29 = round($_var_15 * $_var_28['bonus_area_commission'] / 100, 2);
                            } else {
                                $_var_29 = round($_var_15 * $_var_0['bonus_commission1'] / 100, 2);
                            }
                            if (empty($_var_0['isdistinction_area'])) {
                                $_var_29 = $_var_29 - $_var_26;
                                $_var_26 = $_var_29;
                            }
                            if ($_var_29 > 0) {
                                $_var_25 = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $_var_14['goodsid'], 'orderid' => $_var_5, 'total' => $_var_14['total'], 'optionname' => $_var_14['optionname'], 'mid' => $_var_28['id'], 'bonus_area' => 1, 'money' => $_var_29, 'createtime' => $_var_8);
                                pdo_insert('sz_yi_bonus_goods', $_var_25);
                            }
                        }
                    }
                    $_var_30 = floatval($_var_0['bonus_commission2']);
                    if (!empty($_var_30)) {
                        $_var_31 = pdo_fetch('select id, bonus_area_commission from ' . tablename('sz_yi_member') . ' where bonus_province=\'' . $_var_11['province'] . '\' and bonus_city=\'' . $_var_11['city'] . '\' and bonus_area=2 and uniacid=' . $_W['uniacid']);
                        if (!empty($_var_31)) {
                            if ($_var_31['bonus_area_commission'] > 0) {
                                $_var_29 = round($_var_15 * $_var_31['bonus_area_commission'] / 100, 2);
                            } else {
                                $_var_29 = round($_var_15 * $_var_0['bonus_commission2'] / 100, 2);
                            }
                            if (empty($_var_0['isdistinction_area'])) {
                                $_var_29 = $_var_29 - $_var_26;
                                $_var_26 = $_var_29;
                            }
                            if ($_var_29 > 0) {
                                $_var_25 = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $_var_14['goodsid'], 'orderid' => $_var_5, 'total' => $_var_14['total'], 'optionname' => $_var_14['optionname'], 'mid' => $_var_31['id'], 'bonus_area' => 2, 'money' => $_var_29, 'createtime' => $_var_8);
                                pdo_insert('sz_yi_bonus_goods', $_var_25);
                            }
                        }
                    }
                    $_var_32 = floatval($_var_0['bonus_commission3']);
                    if (!empty($_var_32)) {
                        $_var_33 = pdo_fetch('select id, bonus_area_commission from ' . tablename('sz_yi_member') . ' where bonus_province=\'' . $_var_11['province'] . '\' and bonus_city=\'' . $_var_11['city'] . '\' and bonus_district=\'' . $_var_11['area'] . '\' and bonus_area=3 and uniacid=' . $_W['uniacid']);
                        if (!empty($_var_33)) {
                            if ($_var_33['bonus_area_commission'] > 0) {
                                $_var_29 = round($_var_15 * $_var_33['bonus_area_commission'] / 100, 2);
                            } else {
                                $_var_29 = round($_var_15 * $_var_0['bonus_commission3'] / 100, 2);
                            }
                            if (empty($_var_0['isdistinction_area'])) {
                                $_var_29 = $_var_29 - $_var_26;
                                $_var_26 = $_var_29;
                            }
                            if ($_var_29 > 0) {
                                $_var_25 = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $_var_14['goodsid'], 'orderid' => $_var_5, 'total' => $_var_14['total'], 'optionname' => $_var_14['optionname'], 'mid' => $_var_33['id'], 'bonus_area' => 3, 'money' => $_var_29, 'createtime' => $_var_8);
                            }
                            pdo_insert('sz_yi_bonus_goods', $_var_25);
                        }
                    }
                }
            }
        }

        public function getChildAgents($_var_1)
        {
            global $_W;
            $_var_3 = 'select id from ' . tablename('sz_yi_member') . " where agentid={$_var_1} and status=1 and isagent = 1 and uniacid=" . $_W['uniacid'];
            $_var_34 = pdo_fetchall($_var_3);
            foreach ($_var_34 as $_var_35) {
                $this->agents[] = $_var_35['id'];
                $this->getChildAgents($_var_35['id']);
            }
            return $this->agents;
        }

        public function getLevels($_var_36 = true)
        {
            global $_W;
            if ($_var_36) {
                return pdo_fetchall('select * from ' . tablename('sz_yi_bonus_level') . ' where uniacid=:uniacid order by level asc', array(':uniacid' => $_W['uniacid']));
            } else {
                return pdo_fetchall('select * from ' . tablename('sz_yi_bonus_level') . ' where uniacid=:uniacid and (ordermoney>0 or commissionmoney>0) order by level asc', array(':uniacid' => $_W['uniacid']));
            }
        }

        public function premierInfo($_var_10, $_var_37 = null)
        {
            if (empty($_var_37) || !is_array($_var_37)) {
                $_var_37 = array();
            }
            global $_W;
            $_var_0 = $this->getSet();
            $_var_13 = m('member')->getInfo($_var_10);
            $_var_38 = 0;
            $_var_39 = 0;
            $_var_40 = 0;
            $_var_41 = 0;
            $_var_42 = 0;
            $_var_8 = time();
            $_var_43 = intval($_var_0['settledaysdf']) * 3600 * 24;
            if (in_array('ok', $_var_37)) {
                $_var_3 = 'select sum(o.price) as money from ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and ({$_var_8} - o.createtime > {$_var_43}) ORDER BY o.createtime DESC,o.status DESC";
                $_var_39 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid']));
            }
            if (in_array('total', $_var_37)) {
                $_var_3 = 'select sum(o.price) as money from ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where o.status>=1 and o.uniacid=:uniacid  ORDER BY o.createtime DESC,o.status DESC';
                $_var_38 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid']));
            }
            if (in_array('pay', $_var_37)) {
                $_var_3 = 'select sum(money) from ' . tablename('sz_yi_bonus_log') . ' where openid=:openid and isglobal=1 and uniacid=:uniacid';
                $_var_40 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid'], 'openid' => $_var_13['openid']));
            }
            if (in_array('myorder', $_var_44)) {
                $_var_45 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.openid=:openid and o.status>=3 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_13['openid']));
                $_var_41 = $_var_45['ordermoney'];
                $_var_42 = $_var_45['ordercount'];
            }
            $_var_13['commission_ok'] = round($_var_39, 2);
            $_var_13['commission_total'] = round($_var_38, 2);
            $_var_13['commission_pay'] = $_var_40;
            $_var_13['myordermoney'] = $_var_41;
            $_var_13['myordercount'] = $_var_42;
            return $_var_13;
        }

        public function getInfo($_var_10, $_var_37 = null)
        {
            if (empty($_var_37) || !is_array($_var_37)) {
                $_var_37 = array();
            }
            global $_W;
            $_var_0 = $this->getSet();
            $_var_13 = m('member')->getInfo($_var_10);
            $_var_38 = 0;
            $_var_39 = 0;
            $_var_46 = 0;
            $_var_47 = 0;
            $_var_48 = 0;
            $_var_40 = 0;
            $_var_49 = 0;
            $_var_50 = 0;
            $_var_51 = 0;
            $_var_41 = 0;
            $_var_42 = 0;
            $_var_52 = $_var_13['id'];
            $_var_8 = time();
            $_var_43 = intval($_var_0['settledaysdf']) * 3600 * 24;
            $this->agents = array();
            if (in_array('totaly', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=0 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=0 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} and cg.bonus_area = 0";
                $_var_49 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid']));
            }
            if (in_array('totaly_area', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=0 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=0 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} and cg.bonus_area!=0";
                $_var_50 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid']));
            }
            if (in_array('ok', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=0 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} and ({$_var_8} - o.createtime > {$_var_43}) ORDER BY o.createtime DESC,o.status DESC";
                $_var_39 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid']));
            }
            if (in_array('total', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where o.status>=1 and o.uniacid=:uniacid and cg.mid = {$_var_52} ORDER BY o.createtime DESC,o.status DESC";
                $_var_38 = pdo_fetchcolumn($_var_3, array(':uniacid' => $_W['uniacid']));
            }
            if (in_array('ordercount', $_var_37)) {
                $_var_53 = pdo_fetchcolumn('select count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_bonus_goods') . ' cg on cg.orderid=o.id  where o.status>=0 and cg.status>=0 and o.uniacid=' . $_W['uniacid'] . ' and cg.mid =' . $_var_52 . ' and cg.bonus_area=0 limit 1');
            }
            if (in_array('ordercount_area', $_var_37)) {
                $_var_51 = pdo_fetchcolumn('select count(distinct o.id) as ordercount_area from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_bonus_goods') . ' cg on cg.orderid=o.id  where o.status>=0 and cg.status>=0 and o.uniacid=' . $_W['uniacid'] . ' and cg.mid =' . $_var_52 . ' and cg.bonus_area!=0 limit 1');
            }
            if (in_array('apply', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=1 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} and ({$_var_8} - o.createtime <= {$_var_43}) ORDER BY o.createtime DESC,o.status DESC";
                $_var_46 = pdo_fetchcolumn($_var_3);
            }
            if (in_array('check', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=2 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} and ({$_var_8} - o.createtime <= {$_var_43}) ORDER BY o.createtime DESC,o.status DESC";
                $_var_47 = pdo_fetchcolumn($_var_3);
            }
            if (in_array('pay', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=3 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} ORDER BY o.createtime DESC,o.status DESC";
                $_var_40 = pdo_fetchcolumn($_var_3);
            }
            if (in_array('lock', $_var_37)) {
                $_var_3 = 'select sum(money) as money from ' . tablename('sz_yi_order') . ' o left join  ' . tablename('sz_yi_bonus_goods') . '  cg on o.id=cg.orderid and cg.status=1 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and cg.mid = {$_var_52} and ({$_var_8} - o.createtime <= {$_var_43}) ORDER BY o.createtime DESC,o.status DESC";
                $_var_48 = pdo_fetchcolumn($_var_3);
            }
            if (in_array('myorder', $_var_44)) {
                $_var_45 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.openid=:openid and o.status>=3 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_13['openid']));
                $_var_41 = $_var_45['ordermoney'];
                $_var_42 = $_var_45['ordercount'];
            }
            $_var_54 = $this->getChildAgents($_var_13['id']);
            $_var_55 = count($_var_54);
            $_var_13['commission_ok'] = isset($_var_39) ? $_var_39 : 0;
            $_var_13['commission_total'] = isset($_var_38) ? $_var_38 : 0;
            $_var_13['commission_pay'] = isset($_var_40) ? $_var_40 : 0;
            $_var_13['commission_apply'] = isset($_var_46) ? $_var_46 : 0;
            $_var_13['commission_check'] = isset($_var_47) ? $_var_47 : 0;
            $_var_13['commission_lock'] = isset($_var_48) ? $_var_48 : 0;
            $_var_13['commission_totaly'] = isset($_var_49) ? $_var_49 : 0;
            $_var_13['commission_totaly_area'] = isset($_var_50) ? $_var_50 : 0;
            $_var_13['ordercount'] = $_var_53;
            $_var_13['ordercount_area'] = $_var_51;
            $_var_13['agentcount'] = $_var_55;
            $_var_13['agentids'] = $_var_54;
            $_var_13['myordermoney'] = $_var_41;
            $_var_13['myordercount'] = $_var_42;
            return $_var_13;
        }

        public function checkOrderConfirm($_var_5 = '0')
        {
            global $_W, $_GPC;
            $_var_0 = $this->getSet();
            if (empty($_var_0['start'])) {
                return;
            }
            $this->calculate($_var_5);
        }

        public function checkOrderPay($_var_5 = '0')
        {
            global $_W, $_GPC;
            $_var_0 = $this->getSet();
            if (empty($_var_0['start'])) {
                return;
            }
            $_var_9 = pdo_fetch('select id,openid,ordersn,goodsprice,agentid,paytime from ' . tablename('sz_yi_order') . ' where id=:id and status>=1 and uniacid=:uniacid limit 1', array(':id' => $_var_5, ':uniacid' => $_W['uniacid']));
            if (empty($_var_9)) {
                return;
            }
            $_var_10 = $_var_9['openid'];
            $_var_13 = m('member')->getMember($_var_10);
            if (empty($_var_13)) {
                return;
            }
            $_var_56 = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_9['id']));
            $_var_12 = '';
            $_var_57 = 0;
            foreach ($_var_56 as $_var_58) {
                $_var_12 .= "" . $_var_58['title'] . '( ';
                if (!empty($_var_58['optiontitle'])) {
                    $_var_12 .= ' 规格: ' . $_var_58['optiontitle'];
                }
                $_var_12 .= ' 单价: ' . $_var_58['realprice'] / $_var_58['total'] . ' 数量: ' . $_var_58['total'] . ' 总价: ' . $_var_58['realprice'] . '); ';
                $_var_57 += $_var_58['realprice'];
            }
            $_var_59 = pdo_fetchall('select distinct mid from ' . tablename('sz_yi_bonus_goods') . ' where uniacid=:uniacid and orderid=:orderid', array(':orderid' => $_var_9['id'], ':uniacid' => $_W['uniacid']));
            foreach ($_var_59 as $_var_19 => $_var_60) {
                $_var_10 = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where id=' . $_var_60['mid'] . ' and uniacid=' . $_W['uniacid']);
                $_var_61 = pdo_fetchcolumn('select sum(money) from ' . tablename('sz_yi_bonus_goods') . ' where mid=' . $_var_60['mid'] . ' and orderid=' . $_var_9['id'] . ' and bonus_area=0 and uniacid=' . $_W['uniacid']);
                $this->sendMessage($_var_10, array('nickname' => $_var_13['nickname'], 'ordersn' => $_var_9['ordersn'], 'price' => $_var_57, 'goods' => $_var_12, 'commission' => $_var_61, 'paytime' => $_var_9['paytime']), TM_BONUS_ORDER_PAY);
            }
        }

        public function checkOrderFinish($_var_5 = '')
        {
            global $_W, $_GPC;
            if (empty($_var_5)) {
                return;
            }
            $_var_0 = $this->getSet();
            if (empty($_var_0['start'])) {
                return;
            }
            $_var_9 = pdo_fetch('select id,openid,ordersn,goodsprice,agentid,paytime,finishtime from ' . tablename('sz_yi_order') . ' where id=:id and status>=1 and uniacid=:uniacid limit 1', array(':id' => $_var_5, ':uniacid' => $_W['uniacid']));
            if (empty($_var_9)) {
                return;
            }
            $_var_10 = $_var_9['openid'];
            $_var_13 = m('member')->getMember($_var_10);
            if (empty($_var_13)) {
                return;
            }
            $_var_56 = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $_var_9['id']));
            $_var_12 = '';
            $_var_57 = 0;
            foreach ($_var_56 as $_var_58) {
                $_var_12 .= "" . $_var_58['title'] . '( ';
                if (!empty($_var_58['optiontitle'])) {
                    $_var_12 .= ' 规格: ' . $_var_58['optiontitle'];
                }
                $_var_12 .= ' 单价: ' . $_var_58['realprice'] / $_var_58['total'] . ' 数量: ' . $_var_58['total'] . ' 总价: ' . $_var_58['realprice'] . '); ';
                $_var_57 += $_var_58['realprice'];
            }
            $_var_59 = pdo_fetchall('select distinct mid from ' . tablename('sz_yi_bonus_goods') . ' where uniacid=:uniacid and orderid=:orderid', array(':orderid' => $_var_5, ':uniacid' => $_W['uniacid']));
            foreach ($_var_59 as $_var_19 => $_var_60) {
                $_var_10 = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where id=' . $_var_60['mid'] . ' and uniacid=' . $_W['uniacid']);
                $_var_61 = pdo_fetchcolumn('select sum(money) from ' . tablename('sz_yi_bonus_goods') . ' where mid=' . $_var_60['mid'] . ' and orderid=' . $_var_9['id'] . ' and bonus_area=0 and uniacid=' . $_W['uniacid']);
                $this->sendMessage($_var_10, array('nickname' => $_var_13['nickname'], 'ordersn' => $_var_9['ordersn'], 'price' => $_var_57, 'goods' => $_var_12, 'commission' => $_var_61, 'finishtime' => $_var_9['finishtime']), TM_BONUS_ORDER_FINISH);
            }
        }

        public function getLevel($_var_10)
        {
            global $_W;
            if (empty($_var_10)) {
                return false;
            }
            $_var_13 = m('member')->getMember($_var_10);
            if (empty($_var_13['bonuslevel'])) {
                return false;
            }
            $_var_20 = pdo_fetch('select * from ' . tablename('sz_yi_bonus_level') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_13['bonuslevel']));
            return $_var_20;
        }

        public function upgradeLevelByAgent($_var_62)
        {
            global $_W;
            if (empty($_var_62)) {
                return false;
            }
            $_var_0 = $this->getSet();
            $_var_13 = p('commission')->getInfo($_var_62, array('ordercount3'));
            if (empty($_var_13)) {
                return;
            }
            if (empty($_var_13['bonuslevel'])) {
                $_var_63 = false;
                $_var_64 = pdo_fetch('select * from ' . tablename('sz_yi_bonus_level') . ' where uniacid=' . $_W['uniacid'] . ' order by level asc');
            } else {
                $_var_63 = $this->getLevel($_var_13['openid']);
                $_var_65 = pdo_fetchcolumn('select level from ' . tablename('sz_yi_bonus_level') . ' where  uniacid=:uniacid and id=:bonuslevel order by level asc', array(':uniacid' => $_W['uniacid'], ':bonuslevel' => $_var_13['bonuslevel']));
                $_var_64 = pdo_fetch('select * from ' . tablename('sz_yi_bonus_level') . ' where  uniacid=:uniacid and level>:levelby order by level asc', array(':uniacid' => $_W['uniacid'], ':levelby' => $_var_65));
            }
            if (empty($_var_64)) {
                return false;
            }
            $_var_66 = $_var_0['leveltype'];
            $_var_67 = true;
            if (in_array('4', $_var_66)) {
                $_var_68 = pdo_fetchcolumn('select sum(price) from ' . tablename('sz_yi_order') . ' where openid=:openid and status>=3 and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_13['openid']));
                if (!empty($_var_64['ordermoney'])) {
                    if ($_var_68 < $_var_64['ordermoney']) {
                        $_var_67 = false;
                    }
                }
            }
            if (in_array('8', $_var_66)) {
                if (!empty($_var_64['downcount'])) {
                    if ($_var_13['agentcount'] < $_var_64['downcount']) {
                        $_var_67 = false;
                    }
                }
            }
            if (in_array('9', $_var_66)) {
                if (!empty($_var_64['downcountlevel1'])) {
                    if ($_var_13['level1'] < $_var_64['downcountlevel1']) {
                        $_var_67 = false;
                    }
                }
            }
            if (in_array('11', $_var_66)) {
                if (!empty($_var_64['commissionmoney'])) {
                    if ($_var_13['ordermoney'] < $_var_64['commissionmoney']) {
                        $_var_67 = false;
                    }
                }
            }
            if ($_var_67 == true) {
                pdo_update('sz_yi_member', array('bonuslevel' => $_var_64['id'], 'bonus_status' => _func_0), array('id' => $_var_13['id']));
                $_var_69 = $this->upgradeLevelByAgent($_var_13['id']);
                if ($_var_69 == false) {
                    $this->sendMessage($_var_13['openid'], array('nickname' => $_var_13['nickname'], 'oldlevel' => $_var_63, 'newlevel' => $_var_64), TM_BONUS_UPGRADE);
                }
                return true;
            }
            return false;
        }

        function sendMessage($_var_10 = '', $_var_25 = array(), $_var_70 = '')
        {
            global $_W, $_GPC;
            $_var_0 = $this->getSet();
            $_var_71 = $_var_0['tm'];
            $_var_72 = $_var_71['templateid'];
            $_var_13 = m('member')->getMember($_var_10);
            $_var_73 = unserialize($_var_13['noticeset']);
            if (!is_array($_var_73)) {
                $_var_73 = array();
            }
            if ($_var_70 == TM_COMMISSION_AGENT_NEW && !empty($_var_71['commission_agent_new']) && empty($_var_73['commission_agent_new'])) {
                $_var_74 = $_var_71['commission_agent_new'];
                $_var_74 = str_replace('[昵称]', $_var_25['nickname'], $_var_74);
                $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_25['childtime']), $_var_74);
                $_var_75 = array('keyword1' => array('value' => !empty($_var_71['commission_agent_newtitle']) ? $_var_71['commission_agent_newtitle'] : '新增下线通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                if (!empty($_var_72)) {
                    m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                } else {
                    m('message')->sendCustomNotice($_var_10, $_var_75);
                }
            } else {
                if ($_var_70 == TM_BONUS_ORDER_PAY && !empty($_var_71['bonus_order_pay']) && empty($_var_73['bonus_order_pay'])) {
                    $_var_74 = $_var_71['bonus_order_pay'];
                    $_var_74 = str_replace('[昵称]', $_var_25['nickname'], $_var_74);
                    $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_25['paytime']), $_var_74);
                    $_var_74 = str_replace('[订单编号]', $_var_25['ordersn'], $_var_74);
                    $_var_74 = str_replace('[订单金额]', $_var_25['price'], $_var_74);
                    $_var_74 = str_replace('[分红金额]', $_var_25['commission'], $_var_74);
                    $_var_74 = str_replace('[商品详情]', $_var_25['goods'], $_var_74);
                    $_var_75 = array('keyword1' => array('value' => !empty($_var_71['bonus_order_paytitle']) ? $_var_71['bonus_order_paytitle'] : '分红下线付款通知'), 'keyword2' => array('value' => $_var_74));
                    if (!empty($_var_72)) {
                        m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                    } else {
                        m('message')->sendCustomNotice($_var_10, $_var_75);
                    }
                } else {
                    if ($_var_70 == TM_BONUS_ORDER_FINISH && !empty($_var_71['bonus_order_finish']) && empty($_var_73['bonus_order_finish'])) {
                        $_var_74 = $_var_71['bonus_order_finish'];
                        $_var_74 = str_replace('[昵称]', $_var_25['nickname'], $_var_74);
                        $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_25['finishtime']), $_var_74);
                        $_var_74 = str_replace('[订单编号]', $_var_25['ordersn'], $_var_74);
                        $_var_74 = str_replace('[订单金额]', $_var_25['price'], $_var_74);
                        $_var_74 = str_replace('[分红金额]', $_var_25['commission'], $_var_74);
                        $_var_74 = str_replace('[商品详情]', $_var_25['goods'], $_var_74);
                        $_var_75 = array('keyword1' => array('value' => !empty($_var_71['bonus_order_finishtitle']) ? $_var_71['bonus_order_finishtitle'] : '分红下线确认收货通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                        if (!empty($_var_72)) {
                            m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                        } else {
                            m('message')->sendCustomNotice($_var_10, $_var_75);
                        }
                    } else {
                        if ($_var_70 == TM_COMMISSION_APPLY && !empty($_var_71['commission_apply']) && empty($_var_73['commission_apply'])) {
                            $_var_74 = $_var_71['commission_apply'];
                            $_var_74 = str_replace('[昵称]', $_var_13['nickname'], $_var_74);
                            $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_74);
                            $_var_74 = str_replace('[金额]', $_var_25['commission'], $_var_74);
                            $_var_74 = str_replace('[提现方式]', $_var_25['type'], $_var_74);
                            $_var_75 = array('keyword1' => array('value' => !empty($_var_71['commission_applytitle']) ? $_var_71['commission_applytitle'] : '提现申请提交成功', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                            if (!empty($_var_72)) {
                                m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                            } else {
                                m('message')->sendCustomNotice($_var_10, $_var_75);
                            }
                        } else {
                            if ($_var_70 == TM_COMMISSION_CHECK && !empty($_var_71['commission_check']) && empty($_var_73['commission_check'])) {
                                $_var_74 = $_var_71['commission_check'];
                                $_var_74 = str_replace('[昵称]', $_var_13['nickname'], $_var_74);
                                $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_74);
                                $_var_74 = str_replace('[金额]', $_var_25['commission'], $_var_74);
                                $_var_74 = str_replace('[提现方式]', $_var_25['type'], $_var_74);
                                $_var_75 = array('keyword1' => array('value' => !empty($_var_71['commission_checktitle']) ? $_var_71['commission_checktitle'] : '提现申请审核处理完成', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                                if (!empty($_var_72)) {
                                    m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                                } else {
                                    m('message')->sendCustomNotice($_var_10, $_var_75);
                                }
                            } else {
                                if ($_var_70 == TM_BONUS_PAY && !empty($_var_71['bonus_pay']) && empty($_var_73['bonus_pay'])) {
                                    $_var_74 = $_var_71['bonus_pay'];
                                    $_var_74 = str_replace('[昵称]', $_var_13['nickname'], $_var_74);
                                    $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_74);
                                    $_var_74 = str_replace('[金额]', $_var_25['commission'], $_var_74);
                                    $_var_74 = str_replace('[打款方式]', $_var_25['type'], $_var_74);
                                    $_var_74 = str_replace('[代理等级]', $_var_25['levename'], $_var_74);
                                    $_var_75 = array('keyword1' => array('value' => !empty($_var_71['bonus_paytitle']) ? $_var_71['bonus_paytitle'] : '分红打款通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                                    if (!empty($_var_72)) {
                                        m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                                    } else {
                                        m('message')->sendCustomNotice($_var_10, $_var_75);
                                    }
                                } else {
                                    if ($_var_70 == TM_BONUS_GLOBAL_PAY && !empty($_var_71['bonus_global_pay']) && empty($_var_73['bonus_global_pay'])) {
                                        $_var_74 = $_var_71['bonus_global_pay'];
                                        $_var_74 = str_replace('[昵称]', $_var_13['nickname'], $_var_74);
                                        $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_74);
                                        $_var_74 = str_replace('[金额]', $_var_25['commission'], $_var_74);
                                        $_var_74 = str_replace('[打款方式]', $_var_25['type'], $_var_74);
                                        $_var_74 = str_replace('[代理等级]', $_var_25['levename'], $_var_74);
                                        $_var_75 = array('keyword1' => array('value' => !empty($_var_71['bonus_global_paytitle']) ? $_var_71['bonus_global_paytitle'] : '分红打款通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                                        if (!empty($_var_72)) {
                                            m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                                        } else {
                                            m('message')->sendCustomNotice($_var_10, $_var_75);
                                        }
                                    } else {
                                        if ($_var_70 == TM_BONUS_UPGRADE && !empty($_var_71['bonus_upgrade']) && empty($_var_73['bonus_upgrade'])) {
                                            $_var_74 = $_var_71['bonus_upgrade'];
                                            if (!empty($_var_25['newlevel']['msgcontent'])) {
                                                $_var_74 = $_var_25['newlevel']['msgcontent'];
                                            }
                                            $_var_74 = str_replace('[昵称]', $_var_13['nickname'], $_var_74);
                                            $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_74);
                                            $_var_74 = str_replace('[旧等级]', $_var_25['oldlevel']['levelname'], $_var_74);
                                            $_var_74 = str_replace('[旧分红比例]', $_var_25['oldlevel']['agent_money'] . '%', $_var_74);
                                            $_var_74 = str_replace('[新等级]', $_var_25['newlevel']['levelname'], $_var_74);
                                            $_var_74 = str_replace('[新分红比例]', $_var_25['newlevel']['agent_money'] . '%', $_var_74);
                                            $_var_75 = array('keyword1' => array('value' => !empty($_var_25['newlevel']['msgtitle']) ? $_var_25['newlevel']['msgtitle'] : $_var_71['bonus_upgradetitle'], 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                                            if (!empty($_var_72)) {
                                                m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                                            } else {
                                                m('message')->sendCustomNotice($_var_10, $_var_75);
                                            }
                                        } else {
                                            if ($_var_70 == TM_COMMISSION_BECOME && !empty($_var_71['commission_become']) && empty($_var_73['commission_become'])) {
                                                $_var_74 = $_var_71['commission_become'];
                                                $_var_74 = str_replace('[昵称]', $_var_25['nickname'], $_var_74);
                                                $_var_74 = str_replace('[时间]', date('Y-m-d H:i:s', $_var_25['agenttime']), $_var_74);
                                                $_var_75 = array('keyword1' => array('value' => !empty($_var_71['commission_becometitle']) ? $_var_71['commission_becometitle'] : '成为分销商通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_74, 'color' => '#73a68d'));
                                                if (!empty($_var_72)) {
                                                    m('message')->sendTplNotice($_var_10, $_var_72, $_var_75);
                                                } else {
                                                    m('message')->sendCustomNotice($_var_10, $_var_75);
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
        }

        function perms()
        {
            return array('commission' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('cover' => array('text' => '入口设置'), 'agent' => array('text' => '分销商', 'view' => '浏览', 'check' => '审核-log', 'edit' => '修改-log', 'agentblack' => '黑名单操作-log', 'delete' => '删除-log', 'user' => '查看下线', 'order' => '查看推广订单(还需有订单权限)', 'changeagent' => '设置分销商'), 'level' => array('text' => '分销商等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'apply' => array('text' => '佣金审核', 'view1' => '浏览待审核', 'view2' => '浏览已审核', 'view3' => '浏览已打款', 'view_1' => '浏览无效', 'export1' => '导出待审核-log', 'export2' => '导出已审核-log', 'export3' => '导出已打款-log', 'export_1' => '导出无效-log', 'check' => '审核-log', 'pay' => '打款-log', 'cancel' => '重新审核-log'), 'notice' => array('text' => '通知设置-log'), 'increase' => array('text' => '分销商趋势图'), 'changecommission' => array('text' => '修改佣金-log'), 'set' => array('text' => '基础设置-log'))));
        }

        public function autosend()
        {
            global $_W, $_GPC;
            $_var_8 = time();
            $_var_76 = 0;
            $_var_24 = 0;
            $_var_77 = false;
            $_var_0 = $this->getSet();
            $_var_78 = m('common')->getSysset('shop');
            if (empty($_var_0['sendmethod'])) {
                return false;
            }
            $_var_79 = strtotime(date('Y-m-d 00:00:00'));
            if (empty($_var_0['sendmonth'])) {
                $_var_80 = $_var_79 - 1;
            } else {
                if ($_var_0['sendmonth'] == 1) {
                    $_var_80 = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
                }
            }
            $_var_43 = intval($_var_0['settledaysdf']) * 3600 * 24;
            $_var_3 = 'select distinct cg.mid from ' . tablename('sz_yi_bonus_goods') . ' cg left join  ' . tablename('sz_yi_order') . '  o on o.id=cg.orderid and cg.status=0 left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_W['uniacid']} and ({$_var_80} - o.finishtime > {$_var_43})  ORDER BY o.finishtime DESC,o.status DESC";
            $_var_81 = pdo_fetchall($_var_3);
            $_var_82 = 0;
            if (empty($_var_81)) {
                return false;
            }
            foreach ($_var_81 as $_var_19 => $_var_83) {
                $_var_13 = $this->getInfo($_var_83['mid'], array('ok'));
                $_var_84 = $_var_13['commission_ok'];
                if ($_var_84 <= 0) {
                    continue;
                }
                $_var_77 = true;
                $_var_85 = 1;
                $_var_20 = $this->getlevel($_var_13['openid']);
                if (empty($_var_0['paymethod'])) {
                    m('member')->setCredit($_var_13['openid'], 'credit2', $_var_84);
                } else {
                    $_var_86 = m('common')->createNO('bonus_log', 'logno', 'RB');
                    $_var_87 = m('finance')->pay($_var_13['openid'], 1, $_var_84 * 100, $_var_86, '【' . $_var_78['name'] . '】' . $_var_20['levelname'] . '分红');
                    if (is_error($_var_87)) {
                        $_var_85 = 0;
                        $_var_76 = 1;
                    }
                }
                pdo_insert('sz_yi_bonus_log', array('openid' => $_var_13['openid'], 'uid' => $_var_13['uid'], 'money' => $_var_84, 'uniacid' => $_W['uniacid'], 'paymethod' => $_var_0['paymethod'], 'sendpay' => $_var_85, 'status' => 1, 'ctime' => time(), 'send_bonus_sn' => $_var_8));
                if ($_var_85 == 1) {
                    $this->sendMessage($_var_13['openid'], array('nickname' => $_var_13['nickname'], 'levelname' => $_var_20['levelname'], 'commission' => $_var_84, 'type' => empty($_var_0['paymethod']) ? '余额' : '微信钱包'), TM_BONUS_PAY);
                }
                $_var_59 = array('status' => 3, 'applytime' => $_var_8, 'checktime' => $_var_8, 'paytime' => $_var_8, 'invalidtime' => $_var_8);
                pdo_update('sz_yi_bonus_goods', $_var_59, array('mid' => $_var_13['id'], 'uniacid' => $_W['uniacid']));
                $_var_82 += $_var_13['commission_ok'];
            }
            if ($_var_77) {
                $_var_88 = array('uniacid' => $_W['uniacid'], 'money' => $_var_82, 'status' => 1, 'ctime' => time(), 'paymethod' => $_var_0['paymethod'], 'sendpay_error' => $_var_76, 'utime' => $_var_79, 'send_bonus_sn' => $_var_8, 'total' => count($_var_81));
                pdo_insert('sz_yi_bonus', $_var_88);
                return true;
            }
        }

        public function autosendall()
        {
            $_var_8 = time();
            $_var_76 = 0;
            $_var_24 = 0;
            $_var_82 = 0;
            $_var_77 = false;
            $_var_0 = $this->getSet();
            $_var_78 = m('common')->getSysset('shop');
            if (empty($_var_0['sendmethod'])) {
                return false;
            }
            $_var_79 = strtotime(date('Y-m-d 00:00:00'));
            if (empty($_var_0['sendmonth'])) {
                $_var_80 = $_var_79 - 1;
            } else {
                if ($_var_0['sendmonth'] == 1) {
                    $_var_80 = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
                }
            }
            $_var_3 = 'select sum(o.price) from ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid={$_var_89['uniacid']} and ({$_var_8} - o.finishtime > {$_var_80})  ORDER BY o.finishtime DESC,o.status DESC";
            $_var_90 = pdo_fetchcolumn($_var_3);
            $_var_91 = pdo_fetchall('select * from ' . tablename('sz_yi_bonus_level') . " where uniacid={$_var_89['uniacid']} and premier=1");
            $_var_92 = array();
            $_var_82 = 0;
            foreach ($_var_91 as $_var_19 => $_var_83) {
                $_var_93 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member') . " where uniacid={$_var_89['uniacid']} and bonuslevel=" . $_var_83['id'] . ' and bonus_status = 1');
                if ($_var_93 > 0) {
                    $_var_94 = round($_var_90 * $_var_83['pcommission'] / 100, 2);
                    if ($_var_94 > 0) {
                        $_var_95 = round($_var_94 / $_var_93, 2);
                        if ($_var_95 > 0) {
                            $_var_92[$_var_83['id']] = $_var_95;
                            $_var_82 += $_var_95;
                        }
                    }
                }
            }
            $_var_96 = pdo_fetchall('select m.* from ' . tablename('sz_yi_member') . ' m left join ' . tablename('sz_yi_bonus_level') . " l on m.bonuslevel=l.id and m.bonus_status=1 where 1 and l.premier=1 and m.uniacid={$_var_89['uniacid']}");
            foreach ($_var_96 as $_var_19 => $_var_83) {
                $_var_20 = pdo_fetch('select id, levelname from ' . tablename('sz_yi_bonus_level') . ' where id=' . $_var_97['bonuslevel']);
                $_var_84 = $_var_92[$_var_20['id']];
                if ($_var_84 <= 0) {
                    continue;
                }
                $_var_77 = true;
                $_var_85 = 1;
                $_var_20 = $this->getlevel($_var_13['openid']);
                if (empty($_var_0['paymethod'])) {
                    m('member')->setCredit($_var_83['openid'], 'credit2', $_var_84);
                } else {
                    $_var_86 = m('common')->createNO('bonus_log', 'logno', 'RB');
                    $_var_87 = m('finance')->pay($_var_83['openid'], 1, $_var_84 * 100, $_var_86, '【' . $_var_78['name'] . '】' . $_var_83['levelname'] . '分红');
                    if (is_error($_var_87)) {
                        $_var_85 = 0;
                        $_var_76 = 1;
                    }
                }
                pdo_insert('sz_yi_bonus_log', array('openid' => $_var_13['openid'], 'uid' => $_var_13['uid'], 'money' => $_var_84, 'uniacid' => $_var_89['uniacid'], 'paymethod' => $_var_0['paymethod'], 'sendpay' => $_var_85, 'isglobal' => 1, 'status' => 1, 'ctime' => time(), 'send_bonus_sn' => $_var_8));
                if ($_var_85 == 1) {
                    $this->sendMessage($_var_13['openid'], array('nickname' => $_var_13['nickname'], 'levelname' => $_var_20['levelname'], 'commission' => $_var_84, 'type' => empty($_var_0['paymethod']) ? '余额' : '微信钱包'), TM_BONUS_GLOPAL_PAY);
                }
            }
            if ($_var_77) {
                $_var_88 = array('uniacid' => $_var_89['uniacid'], 'money' => $_var_82, 'status' => 1, 'ctime' => time(), 'paymethod' => $_var_0['paymethod'], 'sendpay_error' => $_var_76, 'isglobal' => 1, 'utime' => $_var_79, 'send_bonus_sn' => $_var_8, 'total' => $_var_98);
                pdo_insert('sz_yi_bonus', $_var_88);
            }
        }
    }
}