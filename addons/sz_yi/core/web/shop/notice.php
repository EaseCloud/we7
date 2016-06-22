<?php
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('shop.notice.view');
    $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_notice') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY displayorder DESC");
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('shop.notice.add');
    } else {
        ca('shop.notice.edit|shop.notice.view');
    }
    if (checksubmit('submit')) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'displayorder' => intval($_GPC['displayorder']),
            'title' => trim($_GPC['title']),
            'thumb' => save_media($_GPC['thumb']),
            'link' => trim($_GPC['link']),
            'detail' => htmlspecialchars_decode($_GPC['detail']),
            'status' => intval($_GPC['status']),
            'createtime' => time()
        );
        if (!empty($id)) {
            pdo_update('sz_yi_notice', $data, array(
                'id' => $id
            ));
            plog('shop.notice.edit', "修改公告 ID: {$id}");
        } else {
            pdo_insert('sz_yi_notice', $data);
            $id = pdo_insertid();
            plog('shop.notice.add', "修改公告 ID: {$id}");
        }
        message('更新店铺公告成功！', $this->createWebUrl('shop/notice', array(
            'op' => 'display'
        )), 'success');
    }
    $notice = pdo_fetch("SELECT * FROM " . tablename('sz_yi_notice') . " WHERE id = '$id' and uniacid = '{$_W['uniacid']}'");
} elseif ($operation == 'delete') {
    ca('shop.notice.delete');
    $id     = intval($_GPC['id']);
    $notice = pdo_fetch("SELECT id,title  FROM " . tablename('sz_yi_notice') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
    if (empty($notice)) {
        message('抱歉，店铺公告不存在或是已经被删除！', $this->createWebUrl('shop/notice', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_notice', array(
        'id' => $id
    ));
    plog('shop.notice.delete', "删除公告 ID: {$id} 标题: {$notice['title']}");
    message('店铺公告删除成功！', $this->createWebUrl('shop/notice', array(
        'op' => 'display'
    )), 'success');
}
load()->func('tpl');
include $this->template('web/shop/notice');
