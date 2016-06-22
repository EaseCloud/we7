<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
$member    = m('member')->getMember($openid);
$notice    = iunserializer($member['noticeset']);
if ($_W['isajax']) {
    if ($operation == 'display') {
        $hascommission = false;
        if (p('commission')) {
            $cset          = p('commission')->getSet();
            $hascommission = !empty($cset['level']);
        }
        show_json(1, array(
            'notice' => $notice,
            'hascommission' => $hascommission
        ));
    } else if ($operation == 'set' && $_W['ispost']) {
        if (empty($_GPC['on'])) {
            unset($notice[$_GPC['notice']]);
        } else {
            $notice[$_GPC['notice']] = $_GPC['on'];
        }
        pdo_update('sz_yi_member', array(
            'noticeset' => iserializer($notice)
        ), array(
            'openid' => $openid,
            'uniacid' => $uniacid
        ));
        show_json(1);
    }
}
include $this->template('member/notice');
