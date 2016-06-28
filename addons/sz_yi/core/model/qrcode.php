<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Sz_DYi_Qrcode
{
    public function createShopQrcode($_var_0 = 0, $_var_1 = 0)
    {
        global $_W, $_GPC;
        $_var_2 = IA_ROOT . '/addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/';
        if (!is_dir($_var_2)) {
            load()->func('file');
            mkdirs($_var_2);
        }
        $_var_3 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=shop&mid=' . $_var_0;
        if (!empty($_var_1)) {
            $_var_3 .= '&posterid=' . $_var_1;
        }
        $_var_4 = 'shop_qrcode_' . $_var_1 . '_' . $_var_0 . '.png';
        $_var_5 = $_var_2 . $_var_4;
        if (!is_file($_var_5)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($_var_3, $_var_5, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $_var_4;
    }

    public function createGoodsQrcode($_var_0 = 0, $_var_6 = 0, $_var_1 = 0)
    {
        global $_W, $_GPC;
        $_var_2 = IA_ROOT . '/addons/sz_yi/data/qrcode/' . $_W['uniacid'];
        if (!is_dir($_var_2)) {
            load()->func('file');
            mkdirs($_var_2);
        }
        $_var_3 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=shop&p=detail&id=' . $_var_6 . '&mid=' . $_var_0;
        if (!empty($_var_1)) {
            $_var_3 .= '&posterid=' . $_var_1;
        }
        $_var_4 = 'goods_qrcode_' . $_var_1 . '_' . $_var_0 . '_' . $_var_6 . '.png';
        $_var_5 = $_var_2 . '/' . $_var_4;
        if (!is_file($_var_5)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($_var_3, $_var_5, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $_var_4;
    }

    public function createWechatQrcode($_var_7)
    {
        global $_W, $_GPC;
        $_var_3 = urldecode($_var_7);
        $_var_2 = IA_ROOT . '/addons/sz_yi/data/qrcode/' . $_W['uniacid'];
        if (!is_dir($_var_2)) {
            load()->func('file');
            mkdirs($_var_2);
        }
        $_var_4 = 'wechat_qrcode_' . time() . '.png';
        $_var_5 = $_var_2 . '/' . $_var_4;
        if (!is_file($_var_5)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($_var_3, $_var_5, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $_var_4;
    }
}