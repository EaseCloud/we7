<?php


global $_W, $_GPC;

if (!$_W['isfounder']) {
    message('您无权操作!', '', 'error');
}
$wechatid = intval($_GPC['wechatid']);
if (!empty($wechatid) && $wechatid != -1) {
    $copyrights = pdo_fetch('select * from ' . tablename('sz_yi_system_copyright') . " where uniacid={$wechatid} limit 1");
}
if (empty($copyrights)) {
    $copyrights = pdo_fetch('select * from ' . tablename('sz_yi_system_copyright') . " where uniacid=-1 limit 1");
}
if (empty($copyrights['bgcolor'])) {
    $copyrights['bgcolor'] = "#fff";
}
if (checksubmit('submit')) {
    $condition = "";
    $acid      = 0;
    $where     = array();
    $sets      = pdo_fetchall('select uniacid from ' . tablename('sz_yi_sysset'));
    $post      = htmlspecialchars_decode($_GPC['copyright']);
    foreach ($sets as $set) {
        $uniacid = $set['uniacid'];
        if ($wechatid == $uniacid || $wechatid == -1) {
            $cs = pdo_fetch('select * from ' . tablename('sz_yi_system_copyright') . " where uniacid=:uniacid limit 1", array(
                ':uniacid' => $uniacid
            ));
            if (empty($cs)) {
                pdo_insert('sz_yi_system_copyright', array(
                    'uniacid' => $uniacid,
                    'copyright' => $post,
                    'bgcolor' => $_GPC['bgcolor']
                ));
            } else {
                pdo_update('sz_yi_system_copyright', array(
                    'copyright' => $post,
                    'bgcolor' => $_GPC['bgcolor']
                ), array(
                    'uniacid' => $uniacid
                ));
            }
        }
    }
    if ($wechatid == -1) {
        $global_copyrights = pdo_fetch('select * from ' . tablename('sz_yi_system_copyright') . " where uniacid=-1 limit 1");
        if (empty($global_copyrights['id'])) {
            pdo_insert('sz_yi_system_copyright', array(
                'uniacid' => -1,
                'copyright' => $post,
                'bgcolor' => $_GPC['bgcolor']
            ));
        } else {
            pdo_update('sz_yi_system_copyright', array(
                'copyright' => $post,
                'bgcolor' => $_GPC['bgcolor']
            ), array(
                'uniacid' => -1
            ));
        }
    }
    $copyrights = pdo_fetchall('select *  from ' . tablename('sz_yi_system_copyright'), array(), 'uniacid');
    m('cache')->set('systemcopyright', $copyrights, 'global');
    message('版权设置成功!', $this->createPluginWebUrl('system/copyright'), 'success');
}
$wechats = $this->model->get_wechats();
load()->func('tpl');
include $this->template('copyright');
