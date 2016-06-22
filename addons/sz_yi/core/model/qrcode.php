<?php
/*=============================================================================
#     FileName: qrcode.php
#         Desc: ЖўЮЌТы
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:34:41
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Sz_DYi_Qrcode
{
    public function createShopQrcode($mid = 0, $posterid = 0)
    {
        global $_W, $_GPC;
        $path = IA_ROOT . "/addons/sz_yi/data/qrcode/" . $_W['uniacid'] . "/";
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        $url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=shop&mid=' . $mid;
        if (!empty($posterid)) {
            $url .= '&posterid=' . $posterid;
        }
        $file        = 'shop_qrcode_' . $posterid . '_' . $mid . '.png';
        $qrcode_file = $path . $file;
        if (!is_file($qrcode_file)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $file;
    }
    public function createGoodsQrcode($mid = 0, $goodsid = 0, $posterid = 0)
    {
        global $_W, $_GPC;
        $path = IA_ROOT . "/addons/sz_yi/data/qrcode/" . $_W['uniacid'];
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        $url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=shop&p=detail&id=' . $goodsid . '&mid=' . $mid;
        if (!empty($posterid)) {
            $url .= '&posterid=' . $posterid;
        }
        $file        = 'goods_qrcode_' . $posterid . '_' . $mid . '_' . $goodsid . '.png';
        $qrcode_file = $path . '/' . $file;
        if (!is_file($qrcode_file)) {
            require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
            QRcode::png($url, $qrcode_file, QR_ECLEVEL_L, 4);
        }
        return $_W['siteroot'] . 'addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $file;
    }
}
