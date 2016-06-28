<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Core extends WeModuleSite
{
    public $footer = array();
    public $header = null;
    public $yzShopSet = array();

    public function __construct()
    {
        global $_W, $_GPC;
        if (is_weixin()) {
            m('member')->checkMember();
        } else {
            $_var_0 = array('poster', 'postera');
            if (p('commission') && !in_array($_GPC['p'], $_var_0) && !strpos($_SERVER['SCRIPT_NAME'], 'notify')) {
                if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
                    return;
                }
                p('commission')->checkAgent();
            }
        }
        $this->yzShopSet = m('common')->getSysset('shop');
    }

    public function sendSms($_var_1, $_var_2, $_var_3 = 'reg')
    {
        $_var_4 = m('common')->getSysset();
        if ($_var_4['sms']['type'] == 1) {
            return send_sms($_var_4['sms']['account'], $_var_4['sms']['password'], $_var_1, $_var_2);
        } else {
            return send_sms_alidayu($_var_1, $_var_2, $_var_3);
        }
    }

    public function runTasks()
    {
        global $_W;
        load()->func('communication');
        $_var_5 = strtotime(m('cache')->getString('receive', 'global'));
        $_var_6 = intval(m('cache')->getString('receive_time', 'global'));
        if (empty($_var_6)) {
            $_var_6 = 60;
        }
        $_var_6 *= 60;
        $_var_7 = time();
        if ($_var_5 + $_var_6 <= $_var_7) {
            m('cache')->set('receive', date('Y-m-d H:i:s', $_var_7), 'global');
            ihttp_request($_W['siteroot'] . 'addons/sz_yi/core/mobile/order/receive.php', null, null, 1);
        }
        $_var_5 = strtotime(m('cache')->getString('closeorder', 'global'));
        $_var_6 = intval(m('cache')->getString('closeorder_time', 'global'));
        if (empty($_var_6)) {
            $_var_6 = 60;
        }
        $_var_6 *= 60;
        $_var_7 = time();
        if ($_var_5 + $_var_6 <= $_var_7) {
            m('cache')->set('closeorder', date('Y-m-d H:i:s', $_var_7), 'global');
            ihttp_request($_W['siteroot'] . 'addons/sz_yi/core/mobile/order/close.php', null, null, 1);
        }
        if (p('coupon')) {
            $_var_8 = strtotime(m('cache')->getString('couponbacktime', 'global'));
            $_var_9 = p('coupon')->getSet();
            $_var_10 = intval($_var_9['backruntime']);
            if (empty($_var_10)) {
                $_var_10 = 60;
            }
            $_var_10 *= 60;
            $_var_11 = time();
            if ($_var_8 + $_var_10 <= $_var_11) {
                m('cache')->set('couponbacktime', date('Y-m-d H:i:s', $_var_11), 'global');
                ihttp_request($_W['siteroot'] . 'addons/sz_yi/plugin/coupon/core/mobile/back.php', null, null, 1);
            }
        }
        die('run finished.');
    }

    public function setHeader()
    {
        global $_W, $_GPC;
        $_var_12 = m('user')->getOpenid();
        $_var_13 = m('user')->followed($_var_12);
        $_var_14 = intval($_GPC['mid']);
        $_var_15 = m('member')->getMid();
        $this->setFooter();
        @session_start();
        if (!$_var_13 && $_var_15 != $_var_14 && isMobile()) {
            $_var_4 = m('common')->getSysset();
            $this->header = array('url' => $_var_4['share']['followurl']);
            $_var_16 = false;
            if (!empty($_var_14)) {
                if (!empty($_SESSION[SZ_YI_PREFIX . '_shareid']) && $_SESSION[SZ_YI_PREFIX . '_shareid'] == $_var_14) {
                    $_var_14 = $_SESSION[SZ_YI_PREFIX . '_shareid'];
                }
                $_var_17 = m('member')->getMember($_var_14);
                if (!empty($_var_17)) {
                    $_SESSION[SZ_YI_PREFIX . '_shareid'] = $_var_14;
                    $_var_16 = true;
                    $this->header['icon'] = $_var_17['avatar'];
                    $this->header['text'] = '来自好友 <span>' . $_var_17['nickname'] . '</span> 的推荐';
                }
            }
            if (!$_var_16) {
                $this->header['icon'] = tomedia($_var_4['shop']['logo']);
                $this->header['text'] = '欢迎进入 <span>' . $_var_4['shop']['name'] . '</span>';
            }
        }
    }

    public function setFooter()
    {
        global $_W, $_GPC;
        $_var_18 = strtolower(trim($_GPC['p']));
        $_var_19 = strtolower(trim($_GPC['method']));
        if (strexists($_var_18, 'poster') && $_var_19 == 'build') {
            return;
        }
        if (strexists($_var_18, 'designer') && ($_var_19 == 'index' || empty($_var_19)) && $_GPC['preview'] == 1) {
            return;
        }
        $_var_12 = m('user')->getOpenid();
        $_var_20 = p('designer');
        if ($_var_20 && $_GPC['p'] != 'designer') {
            $_var_21 = $_var_20->getDefaultMenu();
            if (!empty($_var_21)) {
                $this->footer['diymenu'] = true;
                $this->footer['diymenus'] = $_var_21['menus'];
                $this->footer['diyparams'] = $_var_21['params'];
                return;
            }
        }
        $_var_14 = intval($_GPC['mid']);
        $this->footer['first'] = array('text' => '首页', 'ico' => 'home', 'url' => $this->createMobileUrl('shop'));
        $this->footer['second'] = array('text' => '分类', 'ico' => 'list', 'url' => $this->createMobileUrl('shop/category'));
        $this->footer['commission'] = false;
        $_var_17 = m('member')->getMember($_var_12);
        if (!empty($_var_17['isblack'])) {
            if ($_GPC['op'] != 'black') {
                header('Location: ' . $this->createMobileUrl('member/login', array('op' => 'black')));
            }
        }
        if (p('commission')) {
            $_var_4 = p('commission')->getSet();
            if (empty($_var_4['level'])) {
                return;
            }
            $_var_22 = $_var_17['isagent'] == 1 && $_var_17['status'] == 1;
            if ($_GPC['do'] == 'plugin') {
                $this->footer['first'] = array('text' => empty($_var_4['closemyshop']) ? $_var_4['texts']['shop'] : '首页', 'ico' => 'home', 'url' => empty($_var_4['closemyshop']) ? $this->createPluginMobileUrl('commission/myshop', array('mid' => $_var_17['id'])) : $this->createMobileUrl('shop'));
                if ($_GPC['method'] == '') {
                    $this->footer['first']['text'] = empty($_var_4['closemyshop']) ? $_var_4['texts']['myshop'] : '首页';
                }
                if (empty($_var_17['agentblack'])) {
                    $this->footer['commission'] = array('text' => $_var_4['texts']['center'], 'ico' => 'sitemap', 'url' => $this->createPluginMobileUrl('commission'));
                }
            } else {
                if (empty($_var_17['agentblack'])) {
                    if (!$_var_22) {
                        $this->footer['commission'] = array('text' => $_var_4['texts']['become'], 'ico' => 'sitemap', 'url' => $this->createPluginMobileUrl('commission/register'));
                    } else {
                        $this->footer['commission'] = array('text' => empty($_var_4['closemyshop']) ? $_var_4['texts']['shop'] : $_var_4['texts']['center'], 'ico' => empty($_var_4['closemyshop']) ? 'heart' : 'sitemap', 'url' => empty($_var_4['closemyshop']) ? $this->createPluginMobileUrl('commission/myshop', array('mid' => $_var_17['id'])) : $this->createPluginMobileUrl('commission'));
                    }
                }
            }
        }
        if (strstr($_SERVER['REQUEST_URI'], 'app')) {
            if (!isMobile()) {
                if ($this->yzShopSet['ispc'] == 0) {
                }
            }
        }
        if (is_weixin()) {
            if (!empty($this->yzShopSet['isbindmobile'])) {
                if (empty($_var_17) || $_var_17['isbindmobile'] == 0) {
                    if ($_GPC['p'] != 'bindmobile' && $_GPC['p'] != 'sendcode') {
                        $_var_23 = $this->createMobileUrl('member/bindmobile');
                        redirect($_var_23);
                        die;
                    }
                }
            }
        }
    }

    public function createMobileUrl($_var_24, $_var_25 = array(), $_var_26 = true)
    {
        global $_W, $_GPC;
        $_var_24 = explode('/', $_var_24);
        if (isset($_var_24[1])) {
            $_var_25 = array_merge(array('p' => $_var_24[1]), $_var_25);
        }
        if (empty($_var_25['mid'])) {
            $_var_14 = intval($_GPC['mid']);
            if (!empty($_var_14)) {
                $_var_25['mid'] = $_var_14;
            }
        }
        return $_W['siteroot'] . 'app/' . substr(parent::createMobileUrl($_var_24[0], $_var_25, true), 2);
    }

    public function createWebUrl($_var_24, $_var_25 = array())
    {
        global $_W;
        $_var_24 = explode('/', $_var_24);
        if (count($_var_24) > 1 && isset($_var_24[1])) {
            $_var_25 = array_merge(array('p' => $_var_24[1]), $_var_25);
        }
        return $_W['siteroot'] . 'web/' . substr(parent::createWebUrl($_var_24[0], $_var_25, true), 2);
    }

    public function createPluginMobileUrl($_var_24, $_var_25 = array())
    {
        global $_W, $_GPC;
        $_var_24 = explode('/', $_var_24);
        $_var_25 = array_merge(array('p' => $_var_24[0]), $_var_25);
        $_var_25['m'] = 'sz_yi';
        if (isset($_var_24[1])) {
            $_var_25 = array_merge(array('method' => $_var_24[1]), $_var_25);
        }
        if (isset($_var_24[2])) {
            $_var_25 = array_merge(array('op' => $_var_24[2]), $_var_25);
        }
        if (empty($_var_25['mid'])) {
            $_var_14 = intval($_GPC['mid']);
            if (!empty($_var_14)) {
                $_var_25['mid'] = $_var_14;
            }
        }
        return $_W['siteroot'] . 'app/' . substr(parent::createMobileUrl('plugin', $_var_25, true), 2);
    }

    public function createPluginWebUrl($_var_24, $_var_25 = array())
    {
        global $_W;
        $_var_24 = explode('/', $_var_24);
        $_var_25 = array_merge(array('p' => $_var_24[0]), $_var_25);
        if (isset($_var_24[1])) {
            $_var_25 = array_merge(array('method' => $_var_24[1]), $_var_25);
        }
        if (isset($_var_24[2])) {
            $_var_25 = array_merge(array('op' => $_var_24[2]), $_var_25);
        }
        return $_W['siteroot'] . 'web/' . substr(parent::createWebUrl('plugin', $_var_25, true), 2);
    }

    public function _exec($_var_24, $_var_27 = '', $_var_28 = true)
    {
        global $_GPC;
        $_var_24 = strtolower(substr($_var_24, $_var_28 ? 5 : 8));
        $_var_29 = trim($_GPC['p']);
        empty($_var_29) && ($_var_29 = $_var_27);
        if ($_var_28) {
            $_var_30 = IA_ROOT . '/addons/sz_yi/core/web/' . $_var_24 . '/' . $_var_29 . '.php';
        } else {
            $this->setFooter();
            $_var_30 = IA_ROOT . '/addons/sz_yi/core/mobile/' . $_var_24 . '/' . $_var_29 . '.php';
        }
        if (!is_file($_var_30)) {
            message("未找到 控制器文件 {$_var_24}::{$_var_29} : {$_var_30}");
        }
        include $_var_30;
        die;
    }

    public function _execFront($_var_24, $_var_27 = '', $_var_28 = true)
    {
        global $_W, $_GPC;
        define('IN_SYS', true);
        $_W['templateType'] = 'web';
        $_var_24 = strtolower(substr($_var_24, 5));
        $_var_29 = trim($_GPC['p']);
        empty($_var_29) && ($_var_29 = $_var_27);
        $_var_30 = IA_ROOT . '/addons/sz_yi/core/web/' . $_var_24 . '/' . $_var_29 . '.php';
        if (!is_file($_var_30)) {
            message("未找到 控制器文件 {$_var_24}::{$_var_29} : {$_var_30}");
        }
        include $_var_30;
        die;
    }

    public function template($filename, $flag = TEMPLATE_INCLUDEPATH)
    {
        global $_W;
        $view_mode = isMobile() ? 'mobile' : 'pc';
        $sysset = m('common')->getSysset('shop');
        if (strstr($_SERVER['REQUEST_URI'], 'app')) {
            if (!isMobile()) {
                if ($sysset['ispc'] == 0) {
                    $view_mode = 'mobile';
                }
            }
        }
        if ($_W['templateType'] && $_W['templateType'] == 'web') {
        }
        $modulename = strtolower($this->modulename);
        if (defined('IN_SYS')) {
            $file_html = IA_ROOT . "/web/themes/{$_W['template']}/{$modulename}/{$filename}.html";
            $file_cache = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$modulename}/{$filename}.tpl.php";
            
            /** 支持模板开发二开目录优先
             * Alfred@Easecloud
             */
            if (!is_file($file_html)) {
                $file_html = DEVELOP_ROOT . "template/{$filename}.html";
            }
            /** END */
            
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/web/themes/default/{$modulename}/{$filename}.html";
            }
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/addons/{$modulename}/template/{$filename}.html";
            }
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/web/themes/{$_W['template']}/{$filename}.html";
            }
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/web/themes/default/{$filename}.html";
            }
            if (!is_file($file_html)) {
                $_var_37 = explode('/', $filename);
                $_var_38 = array_slice($_var_37, 1);
                $file_html = IA_ROOT . "/addons/{$modulename}/plugin/" . $_var_37[0] . '/template/' . implode('/', $_var_38) . '.html';
            }
        } else {
            $_var_39 = m('cache')->getString('template_shop');
            if (empty($_var_39)) {
                $_var_39 = 'default';
            }
            if (!is_dir(IA_ROOT . '/addons/sz_yi/template/' . $view_mode . '/' . $_var_39)) {
                $_var_39 = 'default';
            }
            $file_cache = IA_ROOT . "/data/tpl/app/sz_yi/{$_var_39}/{$view_mode}/{$filename}.tpl.php";
            $file_html = IA_ROOT . "/addons/{$modulename}/template/{$view_mode}/{$_var_39}/{$filename}.html";
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/addons/{$modulename}/template/{$view_mode}/default/{$filename}.html";
            }
            if (!is_file($file_html)) {
                $_var_40 = explode('/', $filename);
                $_var_41 = $_var_40[0];
                $_var_42 = m('cache')->getString('template_' . $_var_41);
                if (empty($_var_42)) {
                    $_var_42 = 'default';
                }
                if (!is_dir(IA_ROOT . '/addons/sz_yi/plugin/' . $_var_41 . "/template/{$view_mode}/" . $_var_42)) {
                    $_var_42 = 'default';
                }
                $_var_43 = $_var_40[1];
                $file_html = IA_ROOT . '/addons/sz_yi/plugin/' . $_var_41 . "/template/{$view_mode}/" . $_var_42 . "/{$_var_43}.html";
            }
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/app/themes/{$_W['template']}/{$filename}.html";
            }
            if (!is_file($file_html)) {
                $file_html = IA_ROOT . "/app/themes/default/{$filename}.html";
            }
        }
        if (!is_file($file_html)) {
            die("Error: template source '{$filename}' is not exist!");
        }
        if (DEVELOPMENT || !is_file($file_cache) || filemtime($file_html) > filemtime($file_cache)) {
            shop_template_compile($file_html, $file_cache, true);
        }
        return $file_cache;
    }

    public function getUrl()
    {
        if (p('commission')) {
            $_var_4 = p('commission')->getSet();
            if (!empty($_var_4['level'])) {
                return $this->createPluginMobileUrl('commission/myshop');
            }
        }
        return $this->createMobileUrl('shop');
    }
}
