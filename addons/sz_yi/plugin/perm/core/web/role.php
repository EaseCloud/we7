<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('perm.role.view');
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $status    = $_GPC['status'];
    $condition = " and uniacid = :uniacid and deleted=0";
    $params    = array(
        ':uniacid' => $_W['uniacid']
    );
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
        $condition .= ' and rolename like :keyword';
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    if ($_GPC['status'] != '') {
        $condition .= ' and status=' . intval($_GPC['status']);
    }
    $list = pdo_fetchall("SELECT *  FROM " . tablename('sz_yi_perm_role') . " WHERE 1 {$condition} ORDER BY id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as &$row) {
        $row['usercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_perm_user') . ' where roleid=:roleid limit 1', array(
            ':roleid' => $row['id']
        ));
    }
    unset($row);
    $total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('sz_yi_perm_role') . "  WHERE 1 {$condition} ", $params);
    $pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('perm.role.add');
    } else {
        ca('perm.role.edit|perm.role.view');
    }
    $item       = pdo_fetch("SELECT * FROM " . tablename('sz_yi_perm_role') . " WHERE id =:id and deleted=0 and uniacid=:uniacid limit 1", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $id
    ));
    $perms      = $this->model->allPerms();
    $role_perms = array();
    $user_perms = array();
    if (!empty($item)) {
        $role_perms = explode(',', $item['perms']);
    }
    if (checksubmit('submit')) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'rolename' => trim($_GPC['rolename']),
            'status' => intval($_GPC['status']),
            'perms' => is_array($_GPC['perms']) ? implode(',', $_GPC['perms']) : ''
        );
        if (!empty($id)) {
            pdo_update('sz_yi_perm_role', $data, array(
                'id' => $id,
                'uniacid' => $_W['uniacid']
            ));
            plog('perm.role.edit', "修改角色 ID: {$id}");
        } else {
            pdo_insert('sz_yi_perm_role', $data);
            $id = pdo_insertid();
            plog('perm.role.add', "添加角色 ID: {$id} ");
        }
        message('更新角色成功！', $this->createPluginWebUrl('perm/role', array(
            'op' => 'display'
        )), 'success');
    }
} elseif ($operation == 'delete') {
    ca('perm.role.delete');
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,rolename FROM " . tablename('sz_yi_perm_role') . " WHERE id = '$id'");
    if (empty($item)) {
        message('抱歉，角色不存在或是已经被删除！', $this->createPluginWebUrl('perm/role', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_update('sz_yi_perm_role', array(
        'deleted' => 1
    ), array(
        'id' => $id,
        'uniacid' => $_W['uniacid']
    ));
    plog('perm.role.delete', "删除角色 ID: {$id} 角色名称: {$item['rolename']} ");
    message('角色删除成功！', $this->createPluginWebUrl('perm/role', array(
        'op' => 'display'
    )), 'success');
} elseif ($operation == 'query') {
    $kwd                = trim($_GPC['keyword']);
    $params             = array();
    $params[':uniacid'] = $_W['uniacid'];
    $condition          = " and uniacid=:uniacid and deleted=0";
    if (!empty($kwd)) {
        $condition .= " AND `rolename` LIKE :keyword";
        $params[':keyword'] = "%{$kwd}%";
    }
    $ds = pdo_fetchall('SELECT id,rolename,perms FROM ' . tablename('sz_yi_perm_role') . " WHERE 1 {$condition} order by id asc", $params);
    include $this->template('query_role');
    exit;
}
load()->func('tpl');
include $this->template('role');
