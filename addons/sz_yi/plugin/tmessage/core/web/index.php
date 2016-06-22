<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
if ($operation == 'addtype') {
    $kw = $_GPC['kw'];
    include $this->template('message_type', array(
        'op' => 'addtype'
    ));
    exit;
} elseif ($operation == 'display') {
    ca('tmessage.view');
    $list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_member_message_template') . ' WHERE uniacid=:uniacid order by id asc', array(
        ':uniacid' => $_W['uniacid']
    ));
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('tmessage.add');
    } else {
        ca('tmessage.edit|tmessage.view');
    }
    if (!empty($_GPC['id'])) {
        $list = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_message_template') . ' WHERE id=:id and uniacid=:uniacid ', array(
            ':id' => $_GPC['id'],
            ':uniacid' => $_W['uniacid']
        ));
        $data = iunserializer($list['data']);
    }
    if ($_W['ispost']) {
        $id       = $_GPC['id'];
        $keywords = $_GPC['tp_kw'];
        $value    = $_GPC['tp_value'];
        $color    = $_GPC['tp_color'];
        if (!empty($keywords)) {
            $data = array();
            foreach ($keywords as $key => $val) {
                $data[] = array(
                    'keywords' => $keywords[$key],
                    'value' => $value[$key],
                    'color' => $color[$key]
                );
            }
        }
        $insert = array(
            'title' => $_GPC['tp_title'],
            'template_id' => trim($_GPC['tp_template_id']),
            'first' => trim($_GPC['tp_first']),
            'firstcolor' => trim($_GPC['firstcolor']),
            'data' => iserializer($data),
            'remark' => trim($_GPC['tp_remark']),
            'remarkcolor' => trim($_GPC['remarkcolor']),
            'url' => trim($_GPC['tp_url']),
            'uniacid' => $_W['uniacid']
        );
        if (empty($id)) {
            pdo_insert('sz_yi_member_message_template', $insert);
            $id = pdo_insertid();
        } else {
            pdo_update('sz_yi_member_message_template', $insert, array(
                'id' => $id
            ));
        }
        if (checksubmit('submit')) {
            message('保存成功！', $this->createPluginWebUrl('tmessage'));
        } else if (checksubmit('submitsend')) {
            header('location: ' . $this->createPluginWebUrl('tmessage', array(
                'op' => 'send',
                'id' => $id
            )));
            exit;
        }
    }
} elseif ($operation == 'delete') {
    ca('tmessage.delete');
    $id = intval($_GPC['id']);
    pdo_delete('sz_yi_member_message_template', array(
        'id' => $id,
        'uniacid' => $_W['uniacid']
    ));
    message('删除成功！', $this->createPluginWebUrl('tmessage'), 'success');
} elseif ($operation == 'send') {
    ca('tmessage.send');
    $id   = intval($_GPC['id']);
    $send = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_message_template') . ' WHERE id=:id and uniacid=:uniacid ', array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($send)) {
        message('未找到群发模板!', '', 'error');
    }
    $data  = iunserializer($list['data']);
    $list  = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_member_level') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY level asc");
    $list2 = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_member_group') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY id asc");
    $list3 = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_commission_level') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY id asc");
} elseif ($operation == 'fetch') {
    if (!cv('tmessage.send')) {
        die(json_encode(array(
            'result' => 0,
            'message' => '您没有权限!'
        )));
    }
    $id   = intval($_GPC['id']);
    $send = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_message_template') . ' WHERE id=:id and uniacid=:uniacid ', array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($send)) {
        die(json_encode(array(
            'result' => 0,
            'message' => '未找到群发模板!'
        )));
    }
    $class1 = $_GPC['class1'];
    $value1 = $_GPC['value1'];
    $tpid1  = $_GPC['tpid'];
    pdo_update('sz_yi_member_message_template', array(
        'sendtimes' => $send['sendtimes'] + 1
    ), array(
        'id' => $id
    ));
    if ($class1 == 1) {
        $openids = explode(",", trim($value1));
        plog('tmessage.send', "会员群发 模板ID: {$id} 方式: 指定 OPENID 人数: " . count($openids));
        die(json_encode(array(
            'result' => 1,
            'openids' => $openids
        )));
    } elseif ($class1 == 2) {
        $where = '';
        if ($value1 != '') {
            $where .= " and level =" . intval($value1);
        }
        $member = pdo_fetchall("SELECT openid FROM " . tablename('sz_yi_member') . " WHERE uniacid = '{$_W['uniacid']}'" . $where, array(), 'openid');
        if (!empty($value1)) {
            $levelname = pdo_fetchcolumn('select levelname from ' . tablename('sz_yi_member_level') . ' where id=:id limit 1', array(
                ':id' => $value1
            ));
        } else {
            $levelname = "全部";
        }
        plog('tmessage.send', "会员群发 模板ID: {$id} 方式: 等级-{$levelname} 人数: " . count($member));
        die(json_encode(array(
            'result' => 1,
            'openids' => array_keys($member)
        )));
    } elseif ($class1 == 3) {
        $where = '';
        if ($value1 != '') {
            $where .= " and groupid =" . intval($value1);
        }
        $member = pdo_fetchall("SELECT openid FROM " . tablename('sz_yi_member') . " WHERE uniacid = '{$_W['uniacid']}'" . $where, array(), 'openid');
        if (!empty($value1)) {
            $groupname = pdo_fetchcolumn('select groupname from ' . tablename('sz_yi_member_group') . ' where id=:id limit 1', array(
                ':id' => $value1
            ));
        } else {
            $groupname = "全部分组";
        }
        plog('tmessage.send', "会员群发 模板ID: {$id}  方式: 分组-{$groupname} 人数: " . count($member));
        die(json_encode(array(
            'result' => 1,
            'openids' => array_keys($member)
        )));
    } elseif ($class1 == 4) {
        $member = pdo_fetchall("SELECT openid FROM " . tablename('sz_yi_member') . " WHERE uniacid = '{$_W['uniacid']}'" . $where, array(), 'openid');
        plog('tmessage.send', "会员群发 模板ID: {$id}  方式: 全部会员  分组:{$groupname} 人数: " . count($member));
        die(json_encode(array(
            'result' => 1,
            'openids' => array_keys($member)
        )));
    } elseif ($class1 == 5) {
        $where = '';
        if ($value1 != '') {
            $where .= " and agentlevel =" . intval($value1);
        }
        $member = pdo_fetchall("SELECT openid FROM " . tablename('sz_yi_member') . " WHERE uniacid = '{$_W['uniacid']}' and isagent=1 and status=1 " . $where, array(), 'openid');
        if (!empty($value1)) {
            $levelname = pdo_fetchcolumn('select levelname from ' . tablename('sz_yi_commission_level') . ' where id=:id limit 1', array(
                ':id' => $value1
            ));
        } else {
            $levelname = "全部";
        }
        plog('tmessage.send', "会员群发 模板ID: {$id}  方式: 分销商-{$levelname} 人数: " . count($member));
        die(json_encode(array(
            'result' => 1,
            'openids' => array_keys($member)
        )));
    }
} elseif ($operation == 'sendmessage') {
    $id       = intval($_GPC['id']);
    $template = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_message_template') . ' WHERE id=:id and uniacid=:uniacid ', array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($template)) {
        die(json_encode(array(
            'result' => 0,
            'mesage' => '未指定群发模板!',
            'openid' => $openid
        )));
    }
    if (empty($template['template_id'])) {
        die(json_encode(array(
            'result' => 0,
            'mesage' => '未指定群发模板ID!',
            'openid' => $openid
        )));
    }
    $openid = $_GPC['openid'];
    if (empty($openid)) {
        die(json_encode(array(
            'result' => 0,
            'mesage' => '未指定openid!',
            'openid' => $openid
        )));
    }
    $data = iunserializer($template['data']);
    if (!is_array($data)) {
        die(json_encode(array(
            'result' => 0,
            'mesage' => '模板有错误!',
            'openid' => $openid
        )));
    }
    $msg = array(
        'first' => array(
            'value' => $template['first'],
            'color' => $template['firstcolor']
        ),
        'remark' => array(
            'value' => $template['remark'],
            'color' => $template['remarkcolor']
        )
    );
    for ($i = 0; $i < count($data); $i++) {
        $msg[$data[$i]['keywords']] = array(
            'value' => $data[$i]['value'],
            'color' => $data[$i]['color']
        );
    }
    $result = m('message')->sendTplNotice($openid, $template['template_id'], $msg, $template['url']);
    if (is_error($result)) {
        die(json_encode(array(
            'result' => 0,
            'message' => $result['message'],
            'openid' => $openid
        )));
    }
    pdo_update('sz_yi_member_message_template', array(
        'sendcount' => $template['sendcount'] + 1
    ), array(
        'id' => $id
    ));
    die(json_encode(array(
        'result' => 1
    )));
}
include $this->template('message');
