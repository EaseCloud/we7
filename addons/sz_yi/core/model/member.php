<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Sz_DYi_Member
{
    public function getInfo($_var_0 = '')
    {
        global $_W;
        $_var_1 = intval($_var_0);
        if ($_var_1 == 0) {
            $_var_2 = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_0));
        } else {
            $_var_2 = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where id=:id  and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_1));
        }
        if (!empty($_var_2['uid'])) {
            load()->model('mc');
            $_var_1 = mc_openid2uid($_var_2['openid']);
            $_var_3 = mc_fetch($_var_1, array('credit1', 'credit2', 'birthyear', 'birthmonth', 'birthday', 'gender', 'avatar', 'resideprovince', 'residecity', 'nickname'));
            $_var_2['credit1'] = $_var_3['credit1'];
            $_var_2['credit2'] = $_var_3['credit2'];
            $_var_2['birthyear'] = empty($_var_2['birthyear']) ? $_var_3['birthyear'] : $_var_2['birthyear'];
            $_var_2['birthmonth'] = empty($_var_2['birthmonth']) ? $_var_3['birthmonth'] : $_var_2['birthmonth'];
            $_var_2['birthday'] = empty($_var_2['birthday']) ? $_var_3['birthday'] : $_var_2['birthday'];
            $_var_2['nickname'] = empty($_var_2['nickname']) ? $_var_3['nickname'] : $_var_2['nickname'];
            $_var_2['gender'] = empty($_var_2['gender']) ? $_var_3['gender'] : $_var_2['gender'];
            $_var_2['sex'] = $_var_2['gender'];
            $_var_2['avatar'] = empty($_var_2['avatar']) ? $_var_3['avatar'] : $_var_2['avatar'];
            $_var_2['headimgurl'] = $_var_2['avatar'];
            $_var_2['province'] = empty($_var_2['province']) ? $_var_3['resideprovince'] : $_var_2['province'];
            $_var_2['city'] = empty($_var_2['city']) ? $_var_3['residecity'] : $_var_2['city'];
        }
        if (!empty($_var_2['birthyear']) && !empty($_var_2['birthmonth']) && !empty($_var_2['birthday'])) {
            $_var_2['birthday'] = $_var_2['birthyear'] . '-' . (strlen($_var_2['birthmonth']) <= 1 ? '0' . $_var_2['birthmonth'] : $_var_2['birthmonth']) . '-' . (strlen($_var_2['birthday']) <= 1 ? '0' . $_var_2['birthday'] : $_var_2['birthday']);
        }
        if (empty($_var_2['birthday'])) {
            $_var_2['birthday'] = '';
        }
        return $_var_2;
    }

    public function getMember($_var_0 = '')
    {
        global $_W;
        $_var_1 = intval($_var_0);
        if (empty($_var_1)) {
            $_var_2 = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where  openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_0));
        } else {
            $_var_2 = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_1));
        }
        if (!empty($_var_2)) {
            $_var_0 = $_var_2['openid'];
            if (empty($_var_2['uid'])) {
                $_var_4 = m('user')->followed($_var_0);
                if ($_var_4) {
                    load()->model('mc');
                    $_var_1 = mc_openid2uid($_var_0);
                    if (!empty($_var_1)) {
                        $_var_2['uid'] = $_var_1;
                        $_var_5 = array('uid' => $_var_1);
                        if ($_var_2['credit1'] > 0) {
                            mc_credit_update($_var_1, 'credit1', $_var_2['credit1']);
                            $_var_5['credit1'] = 0;
                        }
                        if ($_var_2['credit2'] > 0) {
                            mc_credit_update($_var_1, 'credit2', $_var_2['credit2']);
                            $_var_5['credit2'] = 0;
                        }
                        if (!empty($_var_5)) {
                            pdo_update('sz_yi_member', $_var_5, array('id' => $_var_2['id']));
                        }
                    }
                }
            }
            $_var_6 = $this->getCredits($_var_0);
            $_var_2['credit1'] = $_var_6['credit1'];
            $_var_2['credit2'] = $_var_6['credit2'];
        }
        return $_var_2;
    }

    public function getMid()
    {
        global $_W;
        $_var_0 = m('user')->getOpenid();
        $_var_7 = $this->getMember($_var_0);
        return $_var_7['id'];
    }

    public function setCredit($_var_0 = '', $_var_8 = 'credit1', $_var_6 = 0, $_var_9 = array())
    {
        global $_W;
        load()->model('mc');
        $_var_1 = mc_openid2uid($_var_0);
        if (!empty($_var_1)) {
            $_var_10 = pdo_fetchcolumn("SELECT {$_var_8} FROM " . tablename('mc_members') . ' WHERE `uid` = :uid', array(':uid' => $_var_1));
            $_var_11 = $_var_6 + $_var_10;
            if ($_var_11 <= 0) {
                $_var_11 = 0;
            }
            pdo_update('mc_members', array($_var_8 => $_var_11), array('uid' => $_var_1));
            if (empty($_var_9) || !is_array($_var_9)) {
                $_var_9 = array($_var_1, '未记录');
            }
            $_var_12 = array('uid' => $_var_1, 'credittype' => $_var_8, 'uniacid' => $_W['uniacid'], 'num' => $_var_6, 'createtime' => TIMESTAMP, 'operator' => intval($_var_9[0]), 'remark' => $_var_9[1]);
            pdo_insert('mc_credits_record', $_var_12);
        } else {
            $_var_10 = pdo_fetchcolumn("SELECT {$_var_8} FROM " . tablename('sz_yi_member') . ' WHERE  uniacid=:uniacid and openid=:openid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_0));
            $_var_11 = $_var_6 + $_var_10;
            if ($_var_11 <= 0) {
                $_var_11 = 0;
            }
            pdo_update('sz_yi_member', array($_var_8 => $_var_11), array('uniacid' => $_W['uniacid'], 'openid' => $_var_0));
        }
    }

    public function getCredit($_var_0 = '', $_var_8 = 'credit1')
    {
        global $_W;
        load()->model('mc');
        $_var_1 = mc_openid2uid($_var_0);
        if (!empty($_var_1)) {
            return pdo_fetchcolumn("SELECT {$_var_8} FROM " . tablename('mc_members') . ' WHERE `uid` = :uid', array(':uid' => $_var_1));
        } else {
            return pdo_fetchcolumn("SELECT {$_var_8} FROM " . tablename('sz_yi_member') . ' WHERE  openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_0));
        }
    }

    public function getCredits($_var_0 = '', $_var_13 = array('credit1', 'credit2'))
    {
        global $_W;
        load()->model('mc');
        $_var_1 = mc_openid2uid($_var_0);
        $_var_8 = implode(',', $_var_13);
        if (!empty($_var_1)) {
            return pdo_fetch("SELECT {$_var_8} FROM " . tablename('mc_members') . ' WHERE `uid` = :uid limit 1', array(':uid' => $_var_1));
        } else {
            return pdo_fetch("SELECT {$_var_8} FROM " . tablename('sz_yi_member') . ' WHERE  openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_0));
        }
    }

    public function checkMember($_var_0 = '')
    {
        global $_W, $_GPC;
        if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
            return;
        }
        if (empty($_var_0)) {
            $_var_0 = m('user')->getOpenid();
        }
        if (empty($_var_0)) {
            return;
        }
        $_var_7 = m('member')->getMember($_var_0);
        $_var_14 = m('user')->getInfo();
        $_var_4 = m('user')->followed($_var_0);
        $_var_1 = 0;
        $_var_15 = array();
        load()->model('mc');
        if ($_var_4) {
            $_var_1 = mc_openid2uid($_var_0);
            $_var_15 = mc_fetch($_var_1, array('realname', 'mobile', 'avatar', 'resideprovince', 'residecity', 'residedist'));
        }
        $_var_16 = false;
        if (empty($_var_7)) {
            if ($_var_4) {
                $_var_1 = mc_openid2uid($_var_0);
                $_var_15 = mc_fetch($_var_1, array('realname', 'mobile', 'avatar', 'resideprovince', 'residecity', 'residedist'));
            }
            $_var_7 = array('uniacid' => $_W['uniacid'], 'uid' => $_var_1, 'openid' => $_var_0, 'realname' => !empty($_var_15['realname']) ? $_var_15['realname'] : '', 'mobile' => !empty($_var_15['mobile']) ? $_var_15['mobile'] : '', 'nickname' => !empty($_var_15['nickname']) ? $_var_15['nickname'] : $_var_14['nickname'], 'avatar' => !empty($_var_15['avatar']) ? $_var_15['avatar'] : $_var_14['avatar'], 'gender' => !empty($_var_15['gender']) ? $_var_15['gender'] : $_var_14['sex'], 'province' => !empty($_var_15['residecity']) ? $_var_15['resideprovince'] : $_var_14['province'], 'city' => !empty($_var_15['residecity']) ? $_var_15['residecity'] : $_var_14['city'], 'area' => !empty($_var_15['residedist']) ? $_var_15['residedist'] : '', 'createtime' => time(), 'status' => 0);
            $_var_16 = true;
            pdo_insert('sz_yi_member', $_var_7);
        } else {
            $_var_5 = array();
            if ($_var_14['nickname'] != $_var_7['nickname']) {
                $_var_5['nickname'] = $_var_14['nickname'];
            }
            if ($_var_14['avatar'] != $_var_7['avatar']) {
                $_var_5['avatar'] = $_var_14['avatar'];
            }
            if (!empty($_var_5)) {
                pdo_update('sz_yi_member', $_var_5, array('id' => $_var_7['id']));
            }
        }
        if (p('commission')) {
            p('commission')->checkAgent();
        }
        if (p('poster')) {
            p('poster')->checkScan();
        }
        if ($_var_16 && is_weixin()) {
        }
    }

    function getLevels()
    {
        global $_W;
        return pdo_fetchall('select * from ' . tablename('sz_yi_member_level') . ' where uniacid=:uniacid order by level asc', array(':uniacid' => $_W['uniacid']));
    }

    function getLevel($_var_0)
    {
        global $_W;
        if (empty($_var_0)) {
            return false;
        }
        $_var_7 = m('member')->getMember($_var_0);
        if (empty($_var_7['level'])) {
            return array('discount' => 10);
        }
        $_var_17 = pdo_fetch('select * from ' . tablename('sz_yi_member_level') . ' where id=:id and uniacid=:uniacid order by level asc', array(':uniacid' => $_W['uniacid'], ':id' => $_var_7['level']));
        if (empty($_var_17)) {
            return array('discount' => 10);
        }
        return $_var_17;
    }

    function upgradeLevel($_var_0)
    {
        global $_W;
        if (empty($_var_0)) {
            return;
        }
        $_var_18 = m('common')->getSysset('shop');
        $_var_19 = intval($_var_18['leveltype']);
        $_var_7 = m('member')->getMember($_var_0);
        if (empty($_var_7)) {
            return;
        }
        $_var_17 = false;
        if (empty($_var_19)) {
            $_var_20 = pdo_fetchcolumn('select ifnull( sum(og.realprice),0) from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on o.id=og.orderid ' . ' where o.openid=:openid and o.status=3 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_7['openid']));
            $_var_17 = pdo_fetch('select * from ' . tablename('sz_yi_member_level') . " where uniacid=:uniacid  and {$_var_20} >= ordermoney and ordermoney>0  order by level desc limit 1", array(':uniacid' => $_W['uniacid']));
        } else {
            if ($_var_19 == 1) {
                $_var_21 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=3 and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_7['openid']));
                $_var_17 = pdo_fetch('select * from ' . tablename('sz_yi_member_level') . " where uniacid=:uniacid  and {$_var_21} >= ordercount and ordercount>0  order by level desc limit 1", array(':uniacid' => $_W['uniacid']));
            }
        }
        if (empty($_var_17)) {
            return;
        }
        if ($_var_17['id'] == $_var_7['level']) {
            return;
        }
        $_var_22 = $this->getLevel($_var_0);
        $_var_23 = false;
        if (empty($_var_22['id'])) {
            $_var_23 = true;
        } else {
            if ($_var_17['level'] > $_var_22['level']) {
                $_var_23 = true;
            }
        }
        if ($_var_23) {
            pdo_update('sz_yi_member', array('level' => $_var_17['id']), array('id' => $_var_7['id']));
            m('notice')->sendMemberUpgradeMessage($_var_0, $_var_22, $_var_17);
        }
    }

    function getGroups()
    {
        global $_W;
        return pdo_fetchall('select * from ' . tablename('sz_yi_member_group') . ' where uniacid=:uniacid order by id asc', array(':uniacid' => $_W['uniacid']));
    }

    function getGroup($_var_0)
    {
        if (empty($_var_0)) {
            return false;
        }
        $_var_7 = m('member')->getMember($_var_0);
        return $_var_7['groupid'];
    }

    function setRechargeCredit($_var_0 = '', $_var_24 = 0)
    {
        if (empty($_var_0)) {
            return;
        }
        global $_W;
        $_var_25 = 0;
        $_var_26 = m('common')->getSysset(array('trade', 'shop'));
        if ($_var_26['trade']) {
            $_var_27 = floatval($_var_26['trade']['money']);
            $_var_28 = intval($_var_26['trade']['credit']);
            if ($_var_27 > 0) {
                if ($_var_24 % $_var_27 == 0) {
                    $_var_25 = intval($_var_24 / $_var_27) * $_var_28;
                } else {
                    $_var_25 = (intval($_var_24 / $_var_27) + 1) * $_var_28;
                }
            }
        }
        if ($_var_25 > 0) {
            $this->setCredit($_var_0, 'credit1', $_var_25, array(0, $_var_26['shop']['name'] . '会员充值积分:credit2:' . $_var_25));
        }
    }

    function writelog($_var_29, $_var_30 = 'Error')
    {
        $_var_31 = fopen($_var_30 . '.txt', 'a');
        fwrite($_var_31, $_var_29);
        fclose($_var_31);
    }
}