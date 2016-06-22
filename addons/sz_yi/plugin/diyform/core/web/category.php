<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;


$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('diyform.category.view');
    if (!empty($_GPC['catname'])) {
        ca('diyform.category.edit|diyform.category.add');
        foreach ($_GPC['catname'] as $id => $catname) {
            if ($id == 'new') {
                ca('diyform.category.add');
                pdo_insert('sz_yi_diyform_category', array(
                    'name' => $catname,
                    'uniacid' => $_W['uniacid']
                ));
                $insert_id = pdo_insertid();
                plog('diyform.category.add', "添加分类 ID: {$insert_id}");
            } else {
                pdo_update('sz_yi_diyform_category', array(
                    'name' => $catname
                ), array(
                    'id' => $id
                ));
                plog('diyform.category.edit', "修改分类 ID: {$id}");
            }
        }
        plog('diyform.category.edit', '批量修改分类');
        message('分类更新成功！', $this->createPluginWebUrl('diyform/category', array(
            'op' => 'display'
        )), 'success');
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_diyform_category') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY id DESC");
} elseif ($operation == 'delete') {
    ca('diyform.category.delete');
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,name FROM " . tablename('sz_yi_diyform_category') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
    if (empty($item)) {
        message('抱歉，分类不存在或是已经被删除！', $this->createPluginWebUrl('diyform/category', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_diyform_category', array(
        'id' => $id
    ));
    plog('diyform.category.delete', "删除分类 ID: {$id} 标题: {$item['name']} ");
    message('分类删除成功！', $this->createPluginWebUrl('diyform/category', array(
        'op' => 'display'
    )), 'success');
}
load()->func('tpl');
include $this->template('category');
