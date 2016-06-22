<?php
/*=============================================================================
#     FileName: group.php
#         Desc:  
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:25:34
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('member.group.view');
    $list    = array(
        array(
            'groupname' => '无分组',
            'membercount' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and groupid=0 limit 1', array(
                ':uniacid' => $_W['uniacid']
            ))
        )
    );
    $alllist = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_member_group') . " WHERE uniacid = '{$_W['uniacid']}' ORDER BY id asc");
    foreach ($alllist as &$row) {
        $row['membercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and groupid=:groupid limit 1', array(
            ':uniacid' => $_W['uniacid'],
            ':groupid' => $row['id']
        ));
    }
    unset($row);
    $list = array_merge($list, $alllist);
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('member.group.add');
    } else {
        ca('member.group.edit|member.group.view');
    }
    $group = pdo_fetch("SELECT * FROM " . tablename('sz_yi_member_group') . " WHERE id = '$id'");
    if (checksubmit('submit')) {
        if (empty($_GPC['groupname'])) {
            message('抱歉，请输入分类名称！');
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'groupname' => trim($_GPC['groupname'])
        );
        if (!empty($id)) {
            pdo_update('sz_yi_member_group', $data, array(
                'id' => $id,
                'uniacid' => $_W['uniacid']
            ));
            plog('member.group.edit', "修改会员分组 ID: {$id}");
        } else {
            pdo_insert('sz_yi_member_group', $data);
            $id = pdo_insertid();
            plog('member.group.add', "添加会员分组 ID: {$id}");
        }
        message('更新分组成功！', $this->createWebUrl('member/group', array(
            'op' => 'display'
        )), 'success');
    }
} elseif ($operation == 'delete') {
    ca('member.group.delete');
    $id    = intval($_GPC['id']);
    $group = pdo_fetch("SELECT id,groupname FROM " . tablename('sz_yi_member_group') . " WHERE id = '$id'");
    if (empty($group)) {
        message('抱歉，分组不存在或是已经被删除！', $this->createWebUrl('member/group', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_member_group', array(
        'id' => $id,
        'uniacid' => $_W['uniacid']
    ));
    pdo_update('sz_yi_member', array(
        'groupid' => 0
    ), array(
        'uniacid' => $_W['uniacid']
    ));
    plog('member.group.delete', "删除会员分组 ID: {$id} 分组名称: {$group['groupname']}");
    message('分组删除成功！', $this->createWebUrl('member/group', array(
        'op' => 'display'
    )), 'success');
}
load()->func('tpl');
include $this->template('web/member/group');
