<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'import';
ca('virtual.data.import');
if ($operation == 'temp') {
    $id   = intval($_GPC['id']);
    $item = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_virtual_type') . ' WHERE id=:id and uniacid=:uniacid ', array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($item)) {
        message('虚拟物品模板不存在', referer(), 'error');
    }
    $item['fields'] = iunserializer($item['fields']);
    $columns        = array();
    foreach ($item['fields'] as $key => $name) {
        $columns[] = array(
            'title' => $name . "(" . $key . ")",
            'field' => '',
            'width' => 24
        );
    }
    m('excel')->export(array(), array(
        'title' => '数据模板',
        'columns' => $columns
    ));
} else if ($operation == 'import') {
    $id   = intval($_GPC['typeid']);
    $item = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_virtual_type') . ' WHERE id=:id and uniacid=:uniacid ', array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($item)) {
        message('虚拟物品模板不存在', referer(), 'error');
    }
    $rows           = m('excel')->import('excelfile');
    $item['fields'] = iunserializer($item['fields']);
    foreach ($rows as $rownum => $col) {
        $data  = array(
            'typeid' => $id,
            'pvalue' => $col[0],
            'fields' => array(),
            'uniacid' => $_W['uniacid']
        );
        $index = 0;
        foreach ($item['fields'] as $k => $f) {
            $data['fields'][$k] = $col[$index];
            $index++;
        }
        $data['fields'] = iserializer($data['fields']);
        $datas[]        = $data;
    }
    foreach ($datas as $d) {
        $olddata = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_virtual_data') . ' WHERE pvalue=:pvalue and typeid=:typeid and uniacid=:uniacid ', array(
            ':pvalue' => $d['pvalue'],
            ':typeid' => $_GPC['typeid'],
            ':uniacid' => $_W['uniacid']
        ));
        if (empty($olddata)) {
            pdo_insert('sz_yi_virtual_data', $d);
            pdo_update('sz_yi_virtual_type', 'alldata=alldata+1', array(
                'id' => $item['id']
            ));
        } else {
            if (empty($olddata['openid'])) {
                pdo_update('sz_yi_virtual_data', $d, array(
                    'id' => $olddata['id']
                ));
            } else {
                $noinsert .= $d['pvalue'] . ',';
            }
        }
        $noinsert = '';
    }
    $this->model->updateStock($typeid);
    if (!empty($noinsert)) {
        $tip = '<br>未保存成功的数据：主键=' . $noinsert . '<br>失败原因：已经使用无法更改';
        message('部分数据保存成功！' . $tip, '', 'warning');
    } else {
        message('导入成功！' . $tip, $this->createPluginWebUrl('virtual/data', array(
            'typeid' => $_GPC['typeid']
        )));
    }
}
