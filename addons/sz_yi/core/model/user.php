<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Sz_DYi_User
{
    private $sessionid;

    public function __construct()
    {
        global $_W;
        $this->sessionid = "__cookie_sz_yi_201507200000_{$_W['uniacid']}";
    }

    function getOpenid()
    {
        $_var_0 = $this->getInfo(false, true);
        return $_var_0['openid'];
    }

    function getPerOpenid()
    {
        global $_W, $_GPC;
        $_var_1 = 24 * 3600 * 3;
        session_set_cookie_params($_var_1);
        @session_start();
        $_var_2 = "__cookie_sz_yi_openid_{$_W['uniacid']}";
        $_var_3 = base64_decode($_COOKIE[$_var_2]);
        if (!empty($_var_3)) {
            return $_var_3;
        }
        load()->func('communication');
        $_var_4 = $_W['account']['key'];
        $_var_5 = $_W['account']['secret'];
        $_var_6 = "";
        $_var_7 = $_GPC['code'];
        $_var_8 = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];
        if (empty($_var_7)) {
            $_var_9 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $_var_4 . '&redirect_uri=' . urlencode($_var_8) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
            header('location: ' . $_var_9);
            die;
        } else {
            $_var_10 = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $_var_4 . '&secret=' . $_var_5 . '&code=' . $_var_7 . '&grant_type=authorization_code';
            $_var_11 = ihttp_get($_var_10);
            $_var_12 = @json_decode($_var_11['content'], true);
            if (!empty($_var_12) && is_array($_var_12) && $_var_12['errmsg'] == 'invalid code') {
                $_var_9 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $_var_4 . '&redirect_uri=' . urlencode($_var_8) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
                header('location: ' . $_var_9);
                die;
            }
            if (is_array($_var_12) && !empty($_var_12['openid'])) {
                $_var_6 = $_var_12['access_token'];
                $_var_3 = $_var_12['openid'];
                setcookie($_var_2, base64_encode($_var_3));
            } else {
                $_var_13 = explode('&', $_SERVER['QUERY_STRING']);
                $_var_14 = array();
                foreach ($_var_13 as $_var_15) {
                    if (!strexists($_var_15, 'code=') && !strexists($_var_15, 'state=') && !strexists($_var_15, 'from=') && !strexists($_var_15, 'isappinstalled=')) {
                        $_var_14[] = $_var_15;
                    }
                }
                $_var_16 = $_W['siteroot'] . 'app/index.php?' . implode('&', $_var_14);
                $_var_9 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $_var_4 . '&redirect_uri=' . urlencode($_var_16) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
                header('location: ' . $_var_9);
                die;
            }
        }
        return $_var_3;
    }

    function isLogin()
    {
        global $_W, $_GPC;
        @session_start();
        $_var_2 = "__cookie_sz_yi_userid_{$_W['uniacid']}";
        $_var_3 = base64_decode($_COOKIE[$_var_2]);
        if (empty($_SERVER['HTTP_USER_AGENT']) && empty($_var_3) && $_GPC['token']) {
            $_var_3 = $_GPC['token'];
        }
        if (!empty($_var_3)) {
            return $_var_3;
        }
        return false;
    }

    function getUserInfo()
    {
        global $_W, $_GPC;
        $_var_17 = array('address', 'commission', 'cart');
        $_var_18 = array('category', 'login', 'receive', 'close', 'designer', 'register', 'sendcode', 'bindmobile', 'forget', 'article');
        $_var_19 = array('shop', 'login', 'register');
        if (!$_GPC['p'] && $_GPC['do'] == 'shop') {
            return;
        }
        if (!in_array($_GPC['p'], $_var_18) && !in_array($_GPC['do'], $_var_19) or in_array($_GPC['p'], $_var_17)) {
            if ($_GPC['method'] != 'myshop' or $_GPC['c'] != 'entry') {
                $_var_3 = $this->isLogin();
                if (!$_var_3 && $_GPC['p'] != 'cart') {
                    if ($_GPC['do'] != 'runtasks') {
                        setcookie('preUrl', $_W['siteurl']);
                    }
                    $_var_20 = $_GPC['mid'] ? '&mid=' . $_GPC['mid'] : "";
                    $_var_8 = "/app/index.php?i={$_W['uniacid']}&c=entry&p=login&do=member&m=sz_yi" . $_var_20;
                    redirect($_var_8);
                } else {
                    $_var_0 = array('openid' => $_var_3, 'headimgurl' => '');
                    return $_var_0;
                }
            }
        }
    }

    function getInfo($_var_21 = false, $_var_22 = false)
    {
        global $_W, $_GPC;
        if (!is_weixin()) {
            return $this->getUserInfo();
        }
        $_var_0 = array();
        if (SZ_YI_DEBUG) {
            $_var_0 = array('openid' => 'oVwSVuJXB7lGGc93d0gBXQ_h-czc', 'nickname' => '小萝莉', 'headimgurl' => '', 'province' => '香港', 'city' => '九龙');
        } else {
            load()->model('mc');
            if (empty($_GPC['directopenid'])) {
                $_var_0 = mc_oauth_userinfo();
            } else {
                $_var_0 = array('openid' => $this->getPerOpenid());
            }
            $_var_23 = false;
            if ($_W['container'] != 'wechat') {
                if ($_GPC['do'] == 'order' && $_GPC['p'] == 'pay') {
                    $_var_23 = false;
                }
                if ($_GPC['do'] == 'member' && $_GPC['p'] == 'recharge') {
                    $_var_23 = false;
                }
                if ($_GPC['do'] == 'plugin' && $_GPC['p'] == 'article' && $_GPC['preview'] == '1') {
                    $_var_23 = false;
                }
            }
        }
        if ($_var_21) {
            return urlencode(base64_encode(json_encode($_var_0)));
        }
        return $_var_0;
    }

    function oauth_info()
    {
        global $_W, $_GPC;
        if ($_W['container'] != 'wechat') {
            if ($_GPC['do'] == 'order' && $_GPC['p'] == 'pay') {
                return array();
            }
            if ($_GPC['do'] == 'member' && $_GPC['p'] == 'recharge') {
                return array();
            }
        }
        $_var_1 = 24 * 3600 * 3;
        session_set_cookie_params($_var_1);
        @session_start();
        $_var_24 = "__cookie_sz_yi_201507100000_{$_W['uniacid']}";
        $_var_25 = json_decode(base64_decode($_SESSION[$_var_24]), true);
        $_var_3 = is_array($_var_25) ? $_var_25['openid'] : '';
        $_var_26 = is_array($_var_25) ? $_var_25['openid'] : '';
        if (!empty($_var_3)) {
            return $_var_25;
        }
        load()->func('communication');
        $_var_4 = $_W['account']['key'];
        $_var_5 = $_W['account']['secret'];
        $_var_6 = "";
        $_var_7 = $_GPC['code'];
        $_var_8 = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];
        if (empty($_var_7)) {
            $_var_9 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $_var_4 . '&redirect_uri=' . urlencode($_var_8) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
            header('location: ' . $_var_9);
            die;
        } else {
            $_var_10 = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $_var_4 . '&secret=' . $_var_5 . '&code=' . $_var_7 . '&grant_type=authorization_code';
            $_var_11 = ihttp_get($_var_10);
            $_var_12 = @json_decode($_var_11['content'], true);
            if (!empty($_var_12) && is_array($_var_12) && $_var_12['errmsg'] == 'invalid code') {
                $_var_9 = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $_var_4 . '&redirect_uri=' . urlencode($_var_8) . '&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect';
                header('location: ' . $_var_9);
                die;
            }
            if (empty($_var_12) || !is_array($_var_12) || empty($_var_12['access_token']) || empty($_var_12['openid'])) {
                die('获取token失败,请重新进入!');
            } else {
                $_var_6 = $_var_12['access_token'];
                $_var_3 = $_var_12['openid'];
            }
        }
        $_var_27 = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $_var_6 . '&openid=' . $_var_3 . '&lang=zh_CN';
        $_var_11 = ihttp_get($_var_27);
        $_var_0 = @json_decode($_var_11['content'], true);
        if (isset($_var_0['nickname'])) {
            $_SESSION[$_var_24] = base64_encode(json_encode($_var_0));
            return $_var_0;
        } else {
            die('获取用户信息失败，请重新进入!');
        }
    }

    function followed($_var_3 = '')
    {
        global $_W;
        $_var_28 = !empty($_var_3);
        if ($_var_28) {
            $_var_29 = pdo_fetch('select follow from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_var_3, ':uniacid' => $_W['uniacid']));
            $_var_28 = $_var_29['follow'] == 1;
        }
        return $_var_28;
    }
}