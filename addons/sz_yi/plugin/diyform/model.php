<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
if (!class_exists('DiyformModel')) {
    class DiyformModel extends PluginModel
    {
        public $_data_type_config = array(0 => '单行文本', 1 => '多行文本', 2 => '下拉框', 3 => '多选框', 5 => '图片', 6 => '身份证号码', 7 => '日期', 8 => '日期范围', 9 => '城市');
        public $_default_data_config = array(0 => '', 1 => '自定义', 2 => '姓名', 3 => '电话', 4 => '微信号');
        public $_default_date_config = array(0 => '', 1 => '填写当天', 2 => '特定日期');
        public function getInsertData($fields, $memberdata)
        {
            global $_W;
            $data    = array();
            $m_data  = array();
            $mc_data = array();
            foreach ($fields as $key => $value) {
                $data_type = $value['data_type'];
                if ($data_type == 0) {
                    $data[$key] = trim($memberdata[$key]);
                    if (!empty($data[$key])) {
                        switch ($value['tp_is_default']) {
                            case 2:
                                $m_data['realname'] = $mc_data['realname'] = $data[$key];
                                break;
                            case 3:
                                $m_data['mobile'] = $mc_data['mobile'] = $data[$key];
                                break;
                            case 4:
                                $m_data['weixin'] = $data[$key];
                                break;
                        }
                    }
                } else if ($data_type == 3) {
                    $data[$key] = $memberdata[$key];
                } else if ($data_type == 5) {
                    $data[$key] = $memberdata[$key];
                } else if ($data_type == 6) {
                    $data[$key] = trim($memberdata[$key]);
                } else if ($data_type == 7) {
                    $data[$key] = trim($memberdata[$key]);
                } else if ($data_type == 8) {
                    $data[$key] = array(
                        0 => trim($memberdata[$key . "_0"]),
                        1 => trim($memberdata[$key . "_1"])
                    );
                } else if ($data_type == 9) {
                    $data[$key] = array(
                        'province' => trim($memberdata[$key][0]),
                        'city' => trim($memberdata[$key][1])
                    );
                } else {
                    $data[$key] = trim($memberdata[$key]);
                }
            }
            $insert_data['data']    = iserializer($data);
            $insert_data['m_data']  = $m_data;
            $insert_data['mc_data'] = $mc_data;
            return $insert_data;
        }
        public function getDiyformData($diyform_data, $fields, $member, $flag = 0, $last_flag = 0)
        {
            global $_W;
            if (!empty($diyform_data)) {
                if ($flag) {
                    $f_data = iunserializer($diyform_data);
                } else {
                    $f_data = $diyform_data;
                }
                if ($last_flag) {
                    foreach ($fields as $key => $value) {
                        if (!array_key_exists($key, $f_data)) {
                            if ($value['data_type'] == 0) {
                                switch ($value['tp_is_default']) {
                                    case 1:
                                        $f_data[$key] = $value['tp_default'];
                                        break;
                                    case 2:
                                        $f_data[$key] = $member['realname'];
                                        break;
                                    case 3:
                                        $f_data[$key] = $member['mobile'];
                                        break;
                                    case 4:
                                        $f_data[$key] = $member['weixin'];
                                        break;
                                }
                            } else if ($value['data_type'] == 7) {
                                switch ($value['default_time_type']) {
                                    case 0:
                                        $f_data[$key] = '';
                                        break;
                                    case 1:
                                        $f_data[$key] = date('Y-m-d');
                                        break;
                                    case 2:
                                        $f_data[$key] = $value['default_time'];
                                        break;
                                }
                            } else if ($value['data_type'] == 8) {
                                switch ($value['default_btime_type']) {
                                    case 0:
                                        $f_data[$key][0] = '';
                                        break;
                                    case 1:
                                        $f_data[$key][0] = date('Y-m-d');
                                        break;
                                    case 2:
                                        $f_data[$key][0] = $value['default_btime'];
                                        break;
                                }
                                switch ($value['default_etime_type']) {
                                    case 0:
                                        $f_data[$key][1] = '';
                                        break;
                                    case 1:
                                        $f_data[$key][1] = date('Y-m-d');
                                        break;
                                    case 2:
                                        $f_data[$key][1] = $value['default_etime'];
                                        break;
                                }
                            } else {
                                $f_data[$key] = '';
                            }
                        }
                    }
                }
            } else {
                $f_data = array();
                foreach ($fields as $key => $value) {
                    if ($value['data_type'] == 0) {
                        switch ($value['tp_is_default']) {
                            case 1:
                                $f_data[$key] = $value['tp_default'];
                                break;
                            case 2:
                                $f_data[$key] = $member['realname'];
                                break;
                            case 3:
                                $f_data[$key] = $member['mobile'];
                                break;
                            case 4:
                                $f_data[$key] = $member['weixin'];
                                break;
                        }
                    } else if ($value['data_type'] == 7) {
                        switch ($value['default_time_type']) {
                            case 0:
                                $f_data[$key] = '';
                                break;
                            case 1:
                                $f_data[$key] = date('Y-m-d');
                                break;
                            case 2:
                                $f_data[$key] = $value['default_time'];
                                break;
                        }
                    } else if ($value['data_type'] == 8) {
                        switch ($value['default_btime_type']) {
                            case 0:
                                $f_data[$key][0] = '';
                                break;
                            case 1:
                                $f_data[$key][0] = date('Y-m-d');
                                break;
                            case 2:
                                $f_data[$key][0] = $value['default_btime'];
                                break;
                        }
                        switch ($value['default_etime_type']) {
                            case 0:
                                $f_data[$key][1] = '';
                                break;
                            case 1:
                                $f_data[$key][1] = date('Y-m-d');
                                break;
                            case 2:
                                $f_data[$key][1] = $value['default_etime'];
                                break;
                        }
                    } else {
                        $f_data[$key] = '';
                    }
                }
            }
            return $f_data;
        }
        public function getFormatData($id, $fields, $member)
        {
            global $_W;
            if (!empty($id)) {
                $diyform_data = $this->getOneDiyformData($id);
            }
            $f_data = $this->getDiyformData($diyform_data, $fields, $member);
            return $f_data;
        }
        public function getLastData($type, $diymode, $diyformid, $cid, $fields, $member)
        {
            global $_W;
            if (!empty($cid)) {
                $table_name   = 'sz_yi_diyform_temp';
                $sql          = 'select * from ' . tablename($table_name) . ' where cid=:cid and diyformid=:diyformid and uniacid=:uniacid and openid=:openid and type=:type order by id desc Limit 1';
                $params       = array(
                    ':cid' => $cid,
                    ':diyformid' => $diyformid,
                    ':uniacid' => $_W['uniacid'],
                    ':openid' => $member['openid'],
                    ':type' => $type
                );
                $diyform_data = pdo_fetch($sql, $params);
                $data         = $diyform_data['diyformdata'];
                if (empty($data)) {
                    $table_name   = 'sz_yi_order_goods';
                    $sql          = 'select * from ' . tablename($table_name) . ' where id=:cid and diyformid=:diyformid and uniacid=:uniacid and openid=:openid order by id desc Limit 1';
                    $params       = array(
                        ':cid' => $cid,
                        ':diyformid' => $diyformid,
                        ':uniacid' => $_W['uniacid'],
                        ':openid' => $member['openid']
                    );
                    $diyform_data = pdo_fetch($sql, $params);
                    $data         = $diyform_data['diyformdata'];
                }
                $f_data = $this->getDiyformData($data, $fields, $member, 1, 1);
            }
            return $f_data;
        }
        public function getLastOrderData($diyformid, $member)
        {
            global $_W;
            $f_data = array();
            if (!empty($diyformid)) {
                $order = pdo_fetch('select diyformdata,diyformfields from ' . tablename('sz_yi_order') . " where diyformid=:diyformid and openid=:openid order by id desc limit 1", array(
                    ':diyformid' => $diyformid,
                    ':openid' => $member['openid']
                ));
                if (!empty($order)) {
                    $f_data = $this->getDiyformData($order['diyformdata'], iunserializer($order['diyformfields']), $member, 1, 1);
                }
            }
            return $f_data;
        }
        public function addDataNum($id)
        {
            global $_W;
            pdo_update('sz_yi_diyform_type', 'alldata=alldata+1', array(
                'id' => $id
            ));
        }
        public function getCountData($typeid, $type = 0)
        {
            global $_W;
            $sql    = 'select count(*) from ' . tablename('sz_yi_diyform_data') . " where typeid=:typeid and uniacid=:uniacid";
            $params = array(
                ':typeid' => $typeid,
                ':uniacid' => $_W['uniacid']
            );
            if ($type) {
                $sql .= " and type=:type";
                $params[':type'] = $type;
            }
            $datacount = pdo_fetchcolumn($sql, $params);
            return $datacount;
        }
        public function getGoodsTemp($goodsid, $diyformid, $openid)
        {
            global $_W;
            $sql    = 'select * from ' . tablename('sz_yi_diyform_temp') . " where cid=:cid and diyformid=:diyformid and openid=:openid and uniacid=:uniacid Limit 1";
            $params = array(
                ':cid' => $goodsid,
                ':diyformid' => $diyformid,
                ':openid' => $openid,
                ':uniacid' => $_W['uniacid']
            );
            $data   = pdo_fetch($sql, $params);
            return $data;
        }
        public function getCountGoodsUsed($diyformid, $diyformtype = -1)
        {
            global $_W;
            $sql    = 'select count(id) from ' . tablename('sz_yi_goods') . " where diyformid=:diyformid and uniacid=:uniacid";
            $params = array(
                ':diyformid' => $diyformid,
                ':uniacid' => $_W['uniacid']
            );
            if ($diyformtype == -1) {
                $sql .= " and diyformtype!=0";
            } else if ($diyformtype >= 0) {
                $sql .= " and diyformtype=:$diyformtype";
                $params[':diyformtype'] = $diyformtype;
            }
            $datacount = pdo_fetchcolumn($sql, $params);
            return $datacount;
        }
        public function getDiyformList()
        {
            global $_W;
            $form_list = pdo_fetchall('select * from ' . tablename('sz_yi_diyform_type') . ' where uniacid=:uniacid', array(
                ':uniacid' => $_W['uniacid']
            ));
            return $form_list;
        }
        public function getDiyformInfo($id, $flag = 1)
        {
            global $_W;
            $formInfo = pdo_fetch('select * from ' . tablename('sz_yi_diyform_type') . ' where id=:id and uniacid=:uniacid Limit 1', array(
                ':id' => $id,
                ':uniacid' => $_W['uniacid']
            ));
            if (!empty($formInfo) && $flag) {
                $formInfo['fields'] = iunserializer($formInfo['fields']);
            }
            return $formInfo;
        }
        public function getOneDiyformData($id, $flag = 1)
        {
            global $_W;
            $data = pdo_fetch('select * from ' . tablename('sz_yi_diyform_data') . ' where id=:id and uniacid=:uniacid limit 1', array(
                ':id' => $id,
                ':uniacid' => $_W['uniacid']
            ));
            if (!empty($data) && $flag) {
                $data['diyformfields'] = iunserializer($data['diyformfields']);
                $data['fields']        = iunserializer($data['fields']);
            }
            return $data;
        }
        public function getOneDiyformTemp($id, $flag = 1)
        {
            global $_W;
            $data = pdo_fetch('select * from ' . tablename('sz_yi_diyform_temp') . ' where id=:id and uniacid=:uniacid Limit 1', array(
                ':id' => $id,
                ':uniacid' => $_W['uniacid']
            ));
            if (!empty($data) && $flag) {
                $data['diyformfields'] = iunserializer($data['diyformfields']);
                $data['diyformdata']   = iunserializer($data['diyformdata']);
            }
            return $data;
        }
        public function getDatas($fields, $data)
        {
            $diyformfields = array();
            foreach ($fields as $key => $value) {
                $tp_value = "";
                if ($value['data_type'] == 0 || $value['data_type'] == 1 || $value['data_type'] == 2 || $value['data_type'] == 6 || $value['data_type'] == 7) {
                    $tp_value = str_replace("\n", "<br/>", $data[$key]);
                } else if ($value['data_type'] == 3 || $value['data_type'] == 8) {
                    if (is_array($data[$key])) {
                        foreach ($data[$key] as $k1 => $v1) {
                            $tp_value .= $v1 . " ";
                        }
                    }
                } else if ($value['data_type'] == 5) {
                    if (is_array($data[$key])) {
                        foreach ($data[$key] as $k1 => $v1) {
                            $tp_value .= "<img style='height:25px;padding:1px;border:1px solid #ccc'  src='" . tomedia($v1) . "'/>";
                        }
                    }
                } else if ($value['data_type'] == 9) {
                    $tp_value = ($data[$key]['province'] != '请选择省份' ? $data[$key]['province'] : '') . " - " . ($data[$key]['city'] != '请选择城市' ? $data[$key]['city'] : '');
                }
                $diyformfields[] = array(
                    'name' => $value['tp_name'],
                    "value" => $tp_value
                );
            }
            return $diyformfields;
        }
        public function perms()
        {
            return array(
                'diyform' => array(
                    'text' => $this->getName(),
                    'isplugin' => true,
                    'child' => array(
                        'temp' => array(
                            'text' => '模板',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log'
                        ),
                        'data' => array(
                            'text' => '数据',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log',
                            'import' => '导入-log',
                            'export' => '导出已使用数据-log'
                        ),
                        'category' => array(
                            'text' => '分类',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log'
                        )
                    )
                )
            );
        }
    }
}
