<?php

if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('exhelper.senduser');
    $list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_exhelper_senduser') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY isdefault desc , id DESC");
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('exhelper.senduser.add');
    } else {
        ca('exhelper.senduser.edit|exhelper.senduser.view');
    }
    if (checksubmit('submit')) {
        $data = array('uniacid' => $_W['uniacid'], 'sendername' => trim($_GPC['sendername']), 'sendertel' => trim($_GPC['sendertel']), 'sendersign' => trim($_GPC['sendersign']), 'sendercode' => trim($_GPC['sendercode']), 'senderaddress' => trim($_GPC['senderaddress']), 'sendercity' => trim($_GPC['sendercity']), 'isdefault' => intval($_GPC['isdefault']));
        if (!empty($id)) {
            pdo_update('sz_yi_exhelper_senduser', $data, array('id' => $id));
            plog('exhelper.senduser.edit', "修改快递单信息 ID: {$id}");
        } else {
            pdo_insert('sz_yi_exhelper_senduser', $data);
            $id = pdo_insertid();
            plog('exhelper.senduser.add', "添加快递单信息 ID: {$id}");
        }
        if (!empty($data['isdefault'])) {
            pdo_update('sz_yi_exhelper_senduser', array('isdefault' => 0), array('uniacid' => $_W['uniacid']));
            pdo_update('sz_yi_exhelper_senduser', array('isdefault' => 1), array('id' => $id));
        }
        message('更新模板成功！', $this->createPluginWebUrl('exhelper/senduser', array('op' => 'display')), 'success');
    }
    $item = pdo_fetch('select * from ' . tablename('sz_yi_exhelper_senduser') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
} elseif ($operation == 'delete') {
    ca('exhelper.senduser.delete');
    $id = intval($_GPC['id']);
    $item = pdo_fetch('SELECT id,sendername FROM ' . tablename('sz_yi_exhelper_senduser') . " WHERE id = '{$id}' AND uniacid=" . $_W['uniacid'] . '');
    if (empty($item)) {
        message('抱歉，模板不存在或是已经被删除！', $this->createPluginWebUrl('exhelper/senduser', array('op' => 'display')), 'error');
    }
    pdo_delete('sz_yi_exhelper_senduser', array('id' => $id));
    plog('exhelper.senduser.delete', "删除快递单信息 ID: {$id} 发件人: {$item['sendername']} ");
    message('模板删除成功！', $this->createPluginWebUrl('exhelper/senduser', array('op' => 'display')), 'success');
} elseif ($operation == 'setdefault') {
    ca('exhelper.senduser.setdefault');
    $id = intval($_GPC['id']);
    $item = pdo_fetch('SELECT id,sendername FROM ' . tablename('sz_yi_exhelper_senduser') . " WHERE id = '{$id}' AND uniacid=" . $_W['uniacid'] . '');
    if (empty($item)) {
        message('抱歉，信息不存在或是已经被删除！', $this->createPluginWebUrl('exhelper/senduser', array('op' => 'display')), 'error');
    }
    pdo_update('sz_yi_exhelper_senduser', array('isdefault' => 0), array('uniacid' => $_W['uniacid']));
    pdo_update('sz_yi_exhelper_senduser', array('isdefault' => 1), array('id' => $id));
    plog('exhelper.senduser.delete', "设置快递单信息默认信息 ID: {$id} 发件人: {$item['sendername']} ");
    message('模板删除成功！', $this->createPluginWebUrl('exhelper/senduser', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('senduser');