<?php

if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($op == 'display') {
    ca('exhelper.printset.view');
    $printset = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_exhelper_sys') . ' WHERE uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
}
if ($_W['ispost']) {
    ca('exhelper.printset.save');
    $port = $_GPC['port'];
    $ip = 'localhost';
    if (empty($printset)) {
        pdo_insert('sz_yi_exhelper_sys', array('port' => $port, 'ip' => $ip, 'uniacid' => $_W['uniacid']));
    } else {
        pdo_update('sz_yi_exhelper_sys', array('port' => $port, 'ip' => $ip), array('uniacid' => $_W['uniacid']));
    }
    message('保存成功！', $this->createPluginWebUrl('exhelper/printset', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('printset');