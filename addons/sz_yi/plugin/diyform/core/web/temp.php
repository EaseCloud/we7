<?php


global $_W, $_GPC;

$operation           = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$data_type_config    = $this->model->_data_type_config;
$default_data_config = $this->model->_default_data_config;
$default_date_config = $this->model->_default_date_config;
if ($operation == 'display') {
    ca('diyform.temp.view');
    $page   = empty($_GPC['page']) ? "" : $_GPC['page'];
    $pindex = max(1, intval($page));
    $psize  = 12;
    $kw     = empty($_GPC['keyword']) ? "" : $_GPC['keyword'];
    $items  = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_diyform_type') . ' WHERE uniacid=:uniacid and title like :name order by id desc limit ' . ($pindex - 1) * $psize . ',' . $psize, array(
        ':name' => "%{$kw}%",
        ':uniacid' => $_W['uniacid']
    ));
    $total  = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sz_yi_diyform_type') . " WHERE uniacid=:uniacid and title like :name order by id desc ", array(
        ':uniacid' => $_W['uniacid'],
        ':name' => "%{$kw}%"
    ));
    $pager  = pagination($total, $pindex, $psize);
    $set    = $this->getSet();
    foreach ($items as $key => $value) {
        if ($set['user_diyform_open'] && ($set['user_diyform'] == $value['id'])) {
            $items[$key]['use_flag1'] = 1;
        }
        if ($set['commission_diyform_open'] && ($set['commission_diyform'] == $value['id'])) {
            $items[$key]['use_flag2'] = 1;
        }
        $items[$key]['datacount3'] = $this->model->getCountGoodsUsed($value['id']);
    }
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('diyform.temp.add');
    } else {
        ca('diyform.temp.view|verify.temp.edit');
    }
    if (!empty($id)) {
        $item           = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_diyform_type') . ' WHERE id=:id and uniacid=:uniacid ', array(
            ':id' => $id,
            ':uniacid' => $_W['uniacid']
        ));
        $item['fields'] = iunserializer($item['fields']);
        $set            = $this->getSet();
        if ($set['user_diyform_open'] && ($set['user_diyform'] == $id)) {
            $use_flag1 = 1;
        }
        if ($set['commission_diyform_open'] && ($set['commission_diyform'] == $id)) {
            $use_flag2 = 1;
        }
        $datacount3 = $this->model->getCountGoodsUsed($id);
    }
    if ($_W['ispost']) {
        $tp_type            = $_GPC['tp_type'];
        $tp_name            = $_GPC['tp_name'];
        $tp_is_default      = $_GPC['tp_is_default'];
        $tp_default         = $_GPC['tp_default'];
        $tp_must            = $_GPC['tp_must'];
        $tp_text            = $_GPC['tp_text'];
        $tp_max             = $_GPC['tp_max'];
        $default_time_type  = $_GPC['default_time_type'];
        $default_time       = $_GPC['default_time'];
        $default_btime_type = $_GPC['default_btime_type'];
        $default_btime      = $_GPC['default_btime'];
        $default_etime_type = $_GPC['default_etime_type'];
        $default_etime      = $_GPC['default_etime'];
        $m_pinyin           = m('pinyin');
        if (!empty($tp_name)) {
            $data = array();
            $j    = 0;
            foreach ($tp_name as $key => $val) {
                $i = $m_pinyin->getPinyin($val, 'diy');
                if (array_key_exists($i, $data)) {
                    $i .= $j;
                    $j++;
                }
                $temp_tp_type          = $tp_type[$key];
                $data[$i]['data_type'] = trim($temp_tp_type);
                $data[$i]['tp_name']   = trim($val);
                $data[$i]['tp_must']   = trim($tp_must[$key]);
                if ($temp_tp_type == 0) {
                    $data[$i]['tp_is_default'] = trim($tp_is_default[$key]);
                    if ($data[$i]['tp_is_default']) {
                        $data[$i]['tp_default'] = trim($tp_default[$key]);
                        switch ($data[$i]['tp_is_default']) {
                            case 'diy':
                                $data[$i]['tp_default'] = trim($tp_default[$key]);
                                break;
                        }
                    }
                } else if ($temp_tp_type == 2 || $temp_tp_type == 3) {
                    $text_array = explode("\n", trim($tp_text[$key]));
                    foreach ($text_array as $k => $v) {
                        $text_array[$k] = trim($v);
                    }
                    $data[$i]['tp_text'] = $text_array;
                } else if ($temp_tp_type == 5) {
                    $data[$i]['tp_max'] = intval(trim($tp_max[$key]));
                } else if ($temp_tp_type == 7) {
                    $data[$i]['default_time_type'] = intval($default_time_type[$key]);
                    if ($data[$i]['default_time_type'] == 2) {
                        $data[$i]['default_time'] = trim($default_time[$key]);
                    }
                } else if ($temp_tp_type == 8) {
                    $data[$i]['default_btime_type'] = intval($default_btime_type[$key]);
                    $data[$i]['default_etime_type'] = intval($default_etime_type[$key]);
                    if ($data[$i]['default_btime_type'] == 2) {
                        $data[$i]['default_btime'] = trim($default_btime[$key]);
                    }
                    if ($data[$i]['default_etime_type'] == 2) {
                        $data[$i]['default_etime'] = trim($default_etime[$key]);
                    }
                }
            }
        }
        $insert = array(
            'uniacid' => $_W['uniacid'],
            'cate' => intval($_GPC['cate']),
            'title' => trim($_GPC['tp_title']),
            'fields' => iserializer($data)
        );
        if (empty($id)) {
            pdo_insert('sz_yi_diyform_type', $insert);
            $id = pdo_insertid();
            plog('diyform.temp.add', "新建模板 ID: {$id}");
        } else {
            pdo_update('sz_yi_diyform_type', $insert, array(
                'id' => $id
            ));
            plog('diyform.temp.edit', "编辑模板 ID: {$id}");
        }
        message('保存成功！', $this->createPluginWebUrl('diyform/temp'));
    }
} elseif ($operation == 'addtype') {
    ca('diyform.temp.edit|diyform.temp.add');
    $addt      = $_GPC['addt'];
    $kw        = $_GPC['kw'];
    $flag      = intval($_GPC['flag']);
    $data_type = $_GPC['data_type'];
    $tmp_key   = $kw;
    if ($addt == 'type') {
        include $this->template('tp_type');
    }
    exit;
} elseif ($operation == 'delete') {
    ca('diyform.temp.delete');
    $id = $_GPC['id'];
    if (!empty($id)) {
        $ret = '';
        $set = $this->getSet();
        if ($set['user_diyform_open'] && ($set['user_diyform'] == $id)) {
            $use_flag1 = 1;
            $ret .= '用户资料正在使用该表单，请关闭后再进行删除。';
        }
        if ($set['commission_diyform_open'] && ($set['commission_diyform'] == $id)) {
            $use_flag2 = 1;
            $ret .= '分销商申请资料正在使用该表单，请关闭后再进行删除。';
        }
        $datacount3 = $this->model->getCountGoodsUsed($id);
        if ($datacount3) {
            $ret .= '有' . $datacount3 . '种商品正在使用该表单，请关闭后再进行删除。';
        }
        if ($use_flag1 || $use_flag2 || $datacount3) {
            show_json(0, $ret);
        } else {
            pdo_delete('sz_yi_diyform_type', array(
                'id' => $id
            ));
            $ret = "删除成功";
            show_json(1, $ret);
        }
    } else {
        $ret = "Url参数错误！请重试！";
        show_json(0, $ret);
    }
    exit;
}
$category = pdo_fetchall('select * from ' . tablename('sz_yi_diyform_category') . ' where uniacid=:uniacid order by id desc', array(
    ':uniacid' => $_W['uniacid']
), 'id');
load()->func('tpl');
include $this->template('temp');
