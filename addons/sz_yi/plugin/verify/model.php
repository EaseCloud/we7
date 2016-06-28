<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
if (!class_exists('VerifyModel')) {
    class VerifyModel extends PluginModel
    {
        public function createQrcode($orderid = 0)
        {
            global $_W, $_GPC;
            $path = IA_ROOT . "/addons/sz_yi/data/qrcode/" . $_W['uniacid'];
            if (!is_dir($path)) {
                load()->func('file');
                mkdirs($path);
            }
            $url         = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=verify&method=detail&id=' . $orderid;
            $file        = 'order_verify_qrcode_' . $orderid . '.png';
            $qrcode_file = $path . '/' . $file;
            if (!is_file($qrcode_file)) {
                require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
                QRcode::png($url, $qrcode_file, QR_ECLEVEL_H, 4);
            }
            return $_W['siteroot'] . '/addons/sz_yi/data/qrcode/' . $_W['uniacid'] . '/' . $file;
        }
        public function perms()
        {
            return array(
                'verify' => array(
                    'text' => $this->getName(),
                    'isplugin' => true,
                    'child' => array(
                        'keyword' => array(
                            'text' => '关键词设置-log'
                        ),
                        'store' => array(
                            'text' => '门店',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log'
                        ),
                        'saler' => array(
                            'text' => '核销员',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log'
                        )
                    )
                )
            );
        }
    }
}
