<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Sz_DYi_Common
{
    public function dataMove()
    {
        $_var_0 = 'ewei_shop';
        $_var_1 = 'sz_yi';
        $_var_2 = pdo_fetchall('SHOW TABLES LIKE \'%' . $_var_1 . '%\'');
        if (!$_var_2) {
            return false;
        }
        foreach ($_var_2 as $_var_3) {
            foreach ($_var_3 as $_var_4) {
                $_var_5 = 'drop table `' . $_var_4 . '`';
                pdo_query($_var_5);
            }
        }
        $_var_2 = pdo_fetchall('SHOW TABLES LIKE \'%' . $_var_0 . '%\'');
        if (!$_var_2) {
            return false;
        }
        foreach ($_var_2 as $_var_3) {
            foreach ($_var_3 as $_var_4) {
                $_var_5 = 'rename table `' . $_var_4 . '` to `' . str_replace($_var_0, $_var_1, $_var_4) . '`';
                pdo_query($_var_5);
            }
        }
        if (!pdo_fieldexists('sz_yi_member', 'regtype')) {
            pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `regtype` tinyint(3) DEFAULT \'1\';');
        }
        if (!pdo_fieldexists('sz_yi_member', 'isbindmobile')) {
            pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isbindmobile` tinyint(3) DEFAULT \'0\';');
        }
        if (!pdo_fieldexists('sz_yi_member', 'isjumpbind')) {
            pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isjumpbind` tinyint(3) DEFAULT \'0\';');
        }
        if (!pdo_fieldexists('sz_yi_member', 'pwd')) {
            pdo_query('ALTER TABLE  ' . tablename('sz_yi_member') . ' CHANGE  `pwd`  `pwd` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');
        }
        pdo_query('UPDATE `ims_sz_yi_plugin` SET `name` = \'芸众分销\' WHERE `identity` = \'commission\'');
        pdo_query('UPDATE `ims_qrcode` SET `name` = \'SZ_YI_POSTER_QRCODE\', `keyword`=\'SZ_YI_POSTER\' WHERE `keyword` = \'EWEI_SHOP_POSTER\'');
        if (!pdo_fieldexists('sz_yi_goods', 'cates')) {
            pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD     `cates` text;');
        }
    }

    public function getSetData($_var_6 = 0)
    {
        global $_W;
        if (empty($_var_6)) {
            $_var_6 = $_W['uniacid'];
        }
        $_var_7 = m('cache')->getArray('sysset', $_var_6);
        if (empty($_var_7)) {
            $_var_7 = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_var_6));
            if (empty($_var_7)) {
                $_var_7 = array();
            }
            m('cache')->set('sysset', $_var_7, $_var_6);
        }
        return $_var_7;
    }

    public function getSysset($_var_8 = '', $_var_6 = 0)
    {
        global $_W, $_GPC;
        $_var_7 = $this->getSetData($_var_6);
        $_var_9 = unserialize($_var_7['sets']);
        $_var_10 = array();
        if (!empty($_var_8)) {
            if (is_array($_var_8)) {
                foreach ($_var_8 as $_var_11) {
                    $_var_10[$_var_11] = isset($_var_9[$_var_11]) ? $_var_9[$_var_11] : array();
                }
            } else {
                $_var_10 = isset($_var_9[$_var_8]) ? $_var_9[$_var_8] : array();
            }
            return $_var_10;
        } else {
            return $_var_9;
        }
    }

    public function alipay_build($_var_12, $_var_13 = array(), $_var_14 = 0, $_var_15 = '')
    {
        global $_W;
        $_var_16 = $_var_12['tid'];
        $_var_7 = array();
        $_var_7['service'] = 'alipay.wap.create.direct.pay.by.user';
        $_var_7['partner'] = $_var_13['partner'];
        $_var_7['_input_charset'] = 'utf-8';
        $_var_7['sign_type'] = 'MD5';
        if (empty($_var_14)) {
            $_var_7['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify.php';
            $_var_7['return_url'] = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=order&p=pay&op=return&openid=" . $_var_15;
        } else {
            $_var_7['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify.php';
            $_var_7['return_url'] = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=member&p=recharge&op=return&openid=" . $_var_15;
        }
        $_var_7['out_trade_no'] = $_var_16;
        $_var_7['subject'] = $_var_12['title'];
        $_var_7['total_fee'] = $_var_12['fee'];
        $_var_7['seller_id'] = $_var_13['account'];
        $_var_7['payment_type'] = 1;
        $_var_7['body'] = $_W['uniacid'] . ':' . $_var_14;
        $_var_17 = array();
        foreach ($_var_7 as $_var_8 => $_var_18) {
            if ($_var_8 != 'sign' && $_var_8 != 'sign_type') {
                $_var_17[] = "{$_var_8}={$_var_18}";
            }
        }
        sort($_var_17);
        $_var_19 = implode($_var_17, '&');
        $_var_19 .= $_var_13['secret'];
        $_var_7['sign'] = md5($_var_19);
        return array('url' => ALIPAY_GATEWAY . '?' . http_build_query($_var_7, '', '&'));
    }

    function wechat_build($_var_12, $_var_20, $_var_14 = 0)
    {
        global $_W;
        load()->func('communication');
        if (empty($_var_20['version']) && !empty($_var_20['signkey'])) {
            $_var_20['version'] = 1;
        }
        $_var_21 = array();
        if ($_var_20['version'] == 1) {
            $_var_21['appId'] = $_var_20['appid'];
            $_var_21['timeStamp'] = TIMESTAMP . "";
            $_var_21['nonceStr'] = random(8) . "";
            $_var_22 = array();
            $_var_22['bank_type'] = 'WX';
            $_var_22['body'] = urlencode($_var_12['title']);
            $_var_22['attach'] = $_W['uniacid'] . ':' . $_var_14;
            $_var_22['partner'] = $_var_20['partner'];
            $_var_22['device_info'] = 'sz_yi';
            $_var_22['out_trade_no'] = $_var_12['tid'];
            $_var_22['total_fee'] = $_var_12['fee'] * 100;
            $_var_22['fee_type'] = '1';
            $_var_22['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php';
            $_var_22['spbill_create_ip'] = CLIENT_IP;
            $_var_22['input_charset'] = 'UTF-8';
            ksort($_var_22);
            $_var_23 = '';
            foreach ($_var_22 as $_var_8 => $_var_24) {
                if (empty($_var_24)) {
                    continue;
                }
                $_var_23 .= "{$_var_8}={$_var_24}&";
            }
            $_var_23 .= "key={$_var_20['key']}";
            $_var_25 = strtoupper(md5($_var_23));
            $_var_26 = '';
            foreach ($_var_22 as $_var_8 => $_var_24) {
                $_var_24 = urlencode($_var_24);
                $_var_26 .= "{$_var_8}={$_var_24}&";
            }
            $_var_26 .= "sign={$_var_25}";
            $_var_21['package'] = $_var_26;
            $_var_19 = '';
            $_var_27 = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
            sort($_var_27);
            foreach ($_var_27 as $_var_8) {
                $_var_24 = $_var_21[$_var_8];
                if ($_var_8 == 'appKey') {
                    $_var_24 = $_var_20['signkey'];
                }
                $_var_8 = strtolower($_var_8);
                $_var_19 .= "{$_var_8}={$_var_24}&";
            }
            $_var_19 = rtrim($_var_19, '&');
            $_var_21['signType'] = 'SHA1';
            $_var_21['paySign'] = sha1($_var_19);
            return $_var_21;
        } else {
            $_var_22 = array();
            $_var_22['appid'] = $_var_20['appid'];
            $_var_22['mch_id'] = $_var_20['mchid'];
            $_var_22['nonce_str'] = random(8) . "";
            $_var_22['body'] = $_var_12['title'];
            $_var_22['device_info'] = 'sz_yi';
            $_var_22['attach'] = $_W['uniacid'] . ':' . $_var_14;
            $_var_22['out_trade_no'] = $_var_12['tid'];
            $_var_22['total_fee'] = $_var_12['fee'] * 100;
            $_var_22['spbill_create_ip'] = CLIENT_IP;
            $_var_22['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php';
            $_var_22['trade_type'] = $_var_12['trade_type'] == 'NATIVE' ? 'NATIVE' : 'JSAPI';
            $_var_22['openid'] = $_W['fans']['from_user'];
            ksort($_var_22, SORT_STRING);
            $_var_23 = '';
            foreach ($_var_22 as $_var_8 => $_var_24) {
                if (empty($_var_24)) {
                    continue;
                }
                $_var_23 .= "{$_var_8}={$_var_24}&";
            }
            $_var_23 .= "key={$_var_20['signkey']}";
            $_var_22['sign'] = strtoupper(md5($_var_23));
            $_var_28 = array2xml($_var_22);
            $_var_29 = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $_var_28);
            if (is_error($_var_29)) {
                return $_var_29;
            }
            $_var_30 = @simplexml_load_string($_var_29['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
            if (strval($_var_30->return_code) == 'FAIL') {
                return error(-1, strval($_var_30->return_msg));
            }
            if (strval($_var_30->result_code) == 'FAIL') {
                return error(-1, strval($_var_30->err_code) . ': ' . strval($_var_30->err_code_des));
            }
            $_var_31 = $_var_30->prepay_id;
            $_var_21['appId'] = $_var_20['appid'];
            $_var_21['timeStamp'] = TIMESTAMP . "";
            $_var_21['nonceStr'] = random(8) . "";
            $_var_21['package'] = 'prepay_id=' . $_var_31;
            $_var_21['signType'] = 'MD5';
            if ($_var_12['trade_type'] == 'NATIVE') {
                $_var_32 = (array)$_var_30->code_url;
                $_var_21['code_url'] = $_var_32[0];
            }
            ksort($_var_21, SORT_STRING);
            foreach ($_var_21 as $_var_8 => $_var_24) {
                $_var_19 .= "{$_var_8}={$_var_24}&";
            }
            $_var_19 .= "key={$_var_20['signkey']}";
            $_var_21['paySign'] = strtoupper(md5($_var_19));
            return $_var_21;
        }
    }

    public function getAccount()
    {
        global $_W;
        load()->model('account');
        if (!empty($_W['acid'])) {
            return WeAccount::create($_W['acid']);
        } else {
            $_var_33 = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid LIMIT 1', array(':uniacid' => $_W['uniacid']));
            return WeAccount::create($_var_33);
        }
        return false;
    }

    public function shareAddress()
    {
        global $_W, $_GPC;
        $_var_34 = $_W['account']['key'];
        $_var_35 = $_W['account']['secret'];
        load()->func('communication');
        $_var_36 = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];
        if (empty($_GPC['code'])) {
            $_var_37 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $_var_34 . '&redirect_uri=' . urlencode($_var_36) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
            header("location: {$_var_37}");
            die;
        }
        $_var_38 = $_GPC['code'];
        $_var_39 = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $_var_34 . '&secret=' . $_var_35 . '&code=' . $_var_38 . '&grant_type=authorization_code';
        $_var_40 = ihttp_get($_var_39);
        $_var_41 = @json_decode($_var_40['content'], true);
        if (empty($_var_41) || !is_array($_var_41) || empty($_var_41['access_token']) || empty($_var_41['openid'])) {
            return false;
        }
        $_var_22 = array('appid' => $_var_34, 'url' => $_var_36, 'timestamp' => time() . "", 'noncestr' => random(8, true) . "", 'accesstoken' => $_var_41['access_token']);
        ksort($_var_22, SORT_STRING);
        $_var_42 = array();
        foreach ($_var_22 as $_var_11 => $_var_24) {
            $_var_42[] = "{$_var_11}={$_var_24}";
        }
        $_var_19 = implode('&', $_var_42);
        $_var_43 = strtolower(sha1(trim($_var_19)));
        $_var_44 = array('appId' => $_var_34, 'scope' => 'jsapi_address', 'signType' => 'sha1', 'addrSign' => $_var_43, 'timeStamp' => $_var_22['timestamp'], 'nonceStr' => $_var_22['noncestr']);
        return $_var_44;
    }

    public function createNO($_var_45, $_var_46, $_var_47)
    {
        $_var_48 = date('YmdHis') . random(6, true);
        while (1) {
            $_var_49 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_' . $_var_45) . " where {$_var_46}=:billno limit 1", array(':billno' => $_var_48));
            if ($_var_49 <= 0) {
                break;
            }
            $_var_48 = date('YmdHis') . random(6, true);
        }
        return $_var_47 . $_var_48;
    }

    public function html_images($_var_50 = '')
    {
        $_var_50 = htmlspecialchars_decode($_var_50);
        preg_match_all('/<img.*?src=[\\\'| "](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\'|"].*?[\\/]?>/', $_var_50, $_var_51);
        $_var_52 = array();
        if (isset($_var_51[1])) {
            foreach ($_var_51[1] as $_var_53) {
                $_var_54 = array('old' => $_var_53, 'new' => save_media($_var_53));
                $_var_52[] = $_var_54;
            }
        }
        foreach ($_var_52 as $_var_53) {
            $_var_50 = str_replace($_var_53['old'], $_var_53['new'], $_var_50);
        }
        return $_var_50;
    }

    public function getSec($_var_6 = 0)
    {
        global $_W;
        if (empty($_var_6)) {
            $_var_6 = $_W['uniacid'];
        }
        $_var_7 = pdo_fetch('select sec from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_var_6));
        if (empty($_var_7)) {
            $_var_7 = array();
        }
        return $_var_7;
    }

    public function paylog($_var_55 = '')
    {
        global $_W;
        $_var_56 = m('cache')->getString('paylog', 'global');
        if (!empty($_var_56)) {
            $_var_57 = IA_ROOT . '/addons/sz_yi/data/paylog/' . $_W['uniacid'] . '/' . date('Ymd');
            if (!is_dir($_var_57)) {
                load()->func('file');
                @mkdirs($_var_57, '0777');
            }
            $_var_58 = $_var_57 . '/' . date('H') . '.log';
            file_put_contents($_var_58, $_var_55, FILE_APPEND);
        }
    }
}