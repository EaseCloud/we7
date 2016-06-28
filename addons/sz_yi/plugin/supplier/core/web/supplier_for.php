<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
load()->model('user');
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$condition = ' and uniacid=:uniacid';
$params = array(':uniacid' => $_W['uniacid']);
if ($operation == 'af_supplier') {
    $status = $_GPC['status'];
    $id = $_GPC['id'];
    $openid = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_af_supplier') . " where uniacid={$_W['uniacid']} and id={$id}");
    if (empty($openid)) {
        message('没有该条申请记录', $this->createPluginWebUrl('supplier/supplier_for'), 'error');
    } else {
        pdo_update('sz_yi_af_supplier', array('status' => $status), array('id' => $id, 'uniacid' => $_W['uniacid']));
        $this->model->sendSupplierInform($openid, $status);
        if ($status == 1) {
            $msg = '驳回申请成功';
        } else {
            $data = array();
            $msg = '审核通过成功';
            $supplier_usre = pdo_fetch('select * from ' . tablename('sz_yi_af_supplier') . " where uniacid={$_W['uniacid']} and id={$id}");
            $data['uid'] = user_register(array('username' => $supplier_usre['username'], 'password' => $supplier_usre['password']));
            $pwd = pdo_fetch('select password from ' . tablename('users') . ' where uid=:uid', array(':uid' => $data['uid']));
            $perm_role = pdo_fetchcolumn('select id,status from ' . tablename('sz_yi_perm_role') . ' where status1=1 and status=1');
            $data['password'] = $pwd['password'];
            $data['username'] = $supplier_usre['username'];
            $data['roleid'] = $perm_role['id'];
            $data['status'] = $perm_role['status'];
            $data['uniacid'] = $_W['uniacid'];
            $data['perms'] = '';
            pdo_insert('uni_account_users', array('uid' => $data['uid'], 'uniacid' => $supplier_usre['uniacid'], 'role' => 'operator'));
            pdo_insert('sz_yi_perm_user', $data);
        }
        message($msg, $this->createPluginWebUrl('supplier/supplier_for'), 'success');
    }
}
if (!empty($_GPC['mid'])) {
    $condition .= ' and id=:mid';
    $params[':mid'] = intval($_GPC['mid']);
}
if (!empty($_GPC['realname'])) {
    $_GPC['realname'] = trim($_GPC['realname']);
    $condition .= ' and realname like :realname';
    $params[':realname'] = "%{$_GPC['realname']}%";
}
$sql = 'select * from ' . tablename('sz_yi_af_supplier') . " where 1 and status=0 {$condition}";
if (empty($_GPC['export'])) {
    $sql .= ' limit ' . ($pindex - 1) * $psize . ',' . $psize;
}
$list = pdo_fetchall($sql, $params);
if ($_GPC['export1'] == '1') {
    plog('member.member.export', '导出会员数据');
    m('excel')->export($list, array('title' => '会员数据-' . date('Y-m-d-H-i', time()), 'columns' => array(array('title' => '会员ID', 'field' => 'id', 'width' => 12), array('title' => '会员姓名', 'field' => 'realname', 'width' => 12), array('title' => '手机号码', 'field' => 'mobile', 'width' => 12), array('title' => '产品名称', 'field' => 'weixin', 'width' => 12), array('title' => '产品名称', 'field' => 'productname', 'width' => 12), array('title' => '用户名', 'field' => 'productname', 'width' => 12), array('title' => '密码', 'field' => 'productname', 'width' => 12))));
}
$total = count($list);
$pager = pagination($total, $pindex, $psize);
load()->func('tpl');
include $this->template('supplier_for');