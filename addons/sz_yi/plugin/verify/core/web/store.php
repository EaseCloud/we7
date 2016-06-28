<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('verify.store.view');
    $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_store') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY id asc");
    foreach ($list as &$row) {
        $row['salercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_saler') . ' where storeid=:storeid limit 1', array(
            ':storeid' => $row['id']
        ));
    }
    unset($row);
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('verify.store.add');
    } else {
        ca('verify.store.view|verify.store.edit');
    }
    $item = pdo_fetch("SELECT * FROM " . tablename('sz_yi_store') . " WHERE id =:id and uniacid=:uniacid limit 1", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $id
    ));
    if (checksubmit('submit')) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'storename' => trim($_GPC['storename']),
            'address' => trim($_GPC['address']),
            'tel' => trim($_GPC['tel']),
            'lng' => $_GPC['map']['lng'],
            'lat' => $_GPC['map']['lat'],
            'status' => intval($_GPC['status'])
        );
        if (!empty($id)) {
            pdo_update('sz_yi_store', $data, array(
                'id' => $id,
                'uniacid' => $_W['uniacid']
            ));
            plog('verify.store.edit', "编辑核销门店 ID: {$id}");
        } else {
            pdo_insert('sz_yi_store', $data);
            $id = pdo_insertid();
            plog('verify.store.add', "添加核销门店 ID: {$id}");
        }
        message('更新门店成功！', $this->createPluginWebUrl('verify/store', array(
            'op' => 'display'
        )), 'success');
    }
} elseif ($operation == 'delete') {
    ca('verify.store.delete');
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,storename FROM " . tablename('sz_yi_store') . " WHERE id = '$id'");
    if (empty($item)) {
        message('抱歉，门店不存在或是已经被删除！', $this->createPluginWebUrl('verify/store', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_store', array(
        'id' => $id,
        'uniacid' => $_W['uniacid']
    ));
    plog('verify.store.delete', "删除核销门店 ID: {$id} 门店名称: {$item['storename']}");
    message('门店删除成功！', $this->createPluginWebUrl('verify/store', array(
        'op' => 'display'
    )), 'success');
} elseif ($operation == 'query') {
    $kwd                = trim($_GPC['keyword']);
    $params             = array();
    $params[':uniacid'] = $_W['uniacid'];
    $condition          = " and uniacid=:uniacid";
    if (!empty($kwd)) {
        $condition .= " AND `storename` LIKE :keyword";
        $params[':keyword'] = "%{$kwd}%";
    }
    $ds = pdo_fetchall('SELECT id,storename FROM ' . tablename('sz_yi_store') . " WHERE 1 {$condition} order by id asc", $params);
    include $this->template('query_store');
    exit;
}
load()->func('tpl');
include $this->template('store');
