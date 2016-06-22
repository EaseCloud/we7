<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('virtual.temp.view');
    $page   = empty($_GPC['page']) ? "" : $_GPC['page'];
    $pindex = max(1, intval($page));
    $psize  = 12;
    $kw     = empty($_GPC['keyword']) ? "" : $_GPC['keyword'];
    $items  = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_virtual_type') . ' WHERE uniacid=:uniacid and title like :name order by id desc limit ' . ($pindex - 1) * $psize . ',' . $psize, array(
        ':name' => "%{$kw}%",
        ':uniacid' => $_W['uniacid']
    ));
    $total  = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sz_yi_virtual_type') . " WHERE uniacid=:uniacid and title like :name order by id desc ", array(
        ':uniacid' => $_W['uniacid'],
        ':name' => "%{$kw}%"
    ));
    $pager  = pagination($total, $pindex, $psize);
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('virtual.temp.add');
    } else {
        ca('virtual.temp.view|verify.temp.edit');
    }
    $datacount = 0;
    if (!empty($id)) {
        $item           = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_virtual_type') . ' WHERE id=:id and uniacid=:uniacid ', array(
            ':id' => $id,
            ':uniacid' => $_W['uniacid']
        ));
        $item['fields'] = iunserializer($item['fields']);
        $datacount      = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_virtual_data') . " where typeid=:typeid and uniacid=:uniacid and openid='' limit 1", array(
            ':typeid' => $id,
            ':uniacid' => $_W['uniacid']
        ));
    }
    if ($_W['ispost']) {
        $keywords = $_GPC['tp_kw'];
        $names    = $_GPC['tp_name'];
        if (!empty($keywords)) {
            $data = array();
            foreach ($keywords as $key => $val) {
                $data[$keywords[$key]] = $names[$key];
            }
        }
        $insert = array(
            'uniacid' => $_W['uniacid'],
            'cate' => intval($_GPC['cate']),
            'title' => trim($_GPC['tp_title']),
            'fields' => iserializer($data)
        );
        if (empty($id)) {
            pdo_insert('sz_yi_virtual_type', $insert);
            $id = pdo_insertid();
            plog('virtual.temp.edit', "编辑模板 ID: {$id}");
        } else {
            pdo_update('sz_yi_virtual_type', $insert, array(
                'id' => $id
            ));
            plog('virtual.temp.edit', "编辑模板 ID: {$id}");
        }
        message('保存成功！', $this->createPluginWebUrl('virtual/temp'));
    }
} elseif ($operation == 'addtype') {
    ca('virtual.temp.edit|virtual.temp.add');
    $addt = $_GPC['addt'];
    $kw   = $_GPC['kw'];
    if ($addt == 'type') {
        include $this->template('tp_type');
    } elseif ($addt == 'data') {
        $item           = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_virtual_type') . ' WHERE id=:id and uniacid=:uniacid ', array(
            ':id' => $_GPC['typeid'],
            ':uniacid' => $_W['uniacid']
        ));
        $item['fields'] = iunserializer($item['fields']);
        $num            = $_GPC['numlist'];
        include $this->template('tp_data');
    }
    exit;
} elseif ($operation == 'delete') {
    ca('virtual.temp.delete');
    $id = $_GPC['id'];
    if (!empty($id)) {
        pdo_delete('sz_yi_virtual_type', array(
            'id' => $id
        ));
        pdo_delete('sz_yi_virtual_data', array(
            'typeid' => $id
        ));
        plog('virtual.temp.delete', "删除模板 ID: {$id}");
        message('删除成功！', $this->createPluginWebUrl('virtual/temp'));
    } else {
        message('Url参数错误！请重试！', $this->createPluginWebUrl('virtual/temp'), 'error');
    }
    exit;
}
$category = pdo_fetchall('select * from ' . tablename('sz_yi_virtual_category') . ' where uniacid=:uniacid order by id desc', array(
    ':uniacid' => $_W['uniacid']
), 'id');
load()->func('tpl');
include $this->template('temp');
