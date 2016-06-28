<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
if (!class_exists('YunpayModel')) {
    class YunpayModel extends PluginModel
    {
        function getYunpay()
        {
            global $_W;
            $_var_0 = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
            $_var_1 = unserialize($_var_0['sets']);
            return $_var_1['pay']['yunpay'];
        }

        function isYunpayNotify($_var_2)
        {
            global $_W;
            $_var_3 = $this->getYunpay();
            if (!isset($_var_3) or !$_var_3['switch']) {
                return false;
            }
            $_var_4 = $_var_2['i1'] . $_var_2['i2'] . $_var_3['partner'] . $_var_3['secret'];
            $_var_5 = md5($_var_4);
            if ($_var_5 != $_var_2['i3']) {
                return false;
            } else {
                return true;
            }
        }

        public function yunpay_build($_var_6, $_var_3 = array(), $_var_7 = 0, $_var_8 = '')
        {
            global $_W;
            $_var_9 = $_var_6['tid'] . ':' . $_W['uniacid'] . ':' . $_var_7;
            if (empty($_var_7)) {
                $_var_10 = $_W['siteroot'] . 'addons/sz_yi/plugin/yunpay/notify.php';
                $_var_11 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=order&p=pay&op=returnyunpay&openid=" . $_var_8;
            } else {
                $_var_10 = $_W['siteroot'] . 'addons/sz_yi/plugin/yunpay/notify.php';
                $_var_11 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=member&p=recharge&op=returnyunpay&openid=" . $_var_8;
            }
            $_var_12 = $_var_9;
            $_var_13 = $_var_6['title'];
            $_var_14 = $_var_6['fee'];
            $_var_15 = $_W['uniacid'] . ':' . $_var_7;
            $_var_16 = "";
            $_var_17 = "";
            $_var_18 = array('partner' => trim($_var_3['partner']), 'seller_email' => $_var_3['account'], 'out_trade_no' => $_var_12, 'subject' => $_var_13, 'total_fee' => floor($_var_14), 'body' => $_var_15, 'nourl' => $_var_10, 'reurl' => $_var_11, 'orurl' => $_var_16, 'orimg' => $_var_17);
            foreach ($_var_18 as $_var_19) {
                $_var_20 .= $_var_19;
            }
            $_var_21 = md5($_var_20 . 'i2eapi' . $_var_3['secret']);
            $_var_22 = '<form name=\'yunsubmit\' action=\'http://pay.yunpay.net.cn/i2eorder/yunpay/\' accept-charset=\'utf-8\' method=\'get\'><input type=\'hidden\' name=\'body\' value=\'' . $_var_18['body'] . '\'/><input type=\'hidden\' name=\'out_trade_no\' value=\'' . $_var_18['out_trade_no'] . '\'/><input type=\'hidden\' name=\'partner\' value=\'' . $_var_18['partner'] . '\'/><input type=\'hidden\' name=\'seller_email\' value=\'' . $_var_18['seller_email'] . '\'/><input type=\'hidden\' name=\'subject\' value=\'' . $_var_18['subject'] . '\'/><input type=\'hidden\' name=\'total_fee\' value=\'' . $_var_18['total_fee'] . '\'/><input type=\'hidden\' name=\'nourl\' value=\'' . $_var_18['nourl'] . '\'/><input type=\'hidden\' name=\'reurl\' value=\'' . $_var_18['reurl'] . '\'/><input type=\'hidden\' name=\'orurl\' value=\'' . $_var_18['orurl'] . '\'/><input type=\'hidden\' name=\'orimg\' value=\'' . $_var_18['orimg'] . '\'/><input type=\'hidden\' name=\'sign\' value=\'' . $_var_21 . '\'/></form><script>document.forms[\'yunsubmit\'].submit();</script>';
            return $_var_22;
        }

        function perms()
        {
            return array('yunpay' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('yunpay' => array('text' => '云支付'))));
        }
    }
}