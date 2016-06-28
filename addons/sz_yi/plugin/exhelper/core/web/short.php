<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    print 'Access Denied';
}
global $_W, $_GPC;
$perm_role = 0;
if (p('supplier')) {
    $roleid = pdo_fetchcolumn('select roleid from' . tablename('sz_yi_perm_user') . ' where uid=' . $_W['uid'] . ' and uniacid=' . $_W['uniacid']);
    if ($roleid == 0) {
        $perm_role = 0;
    } else {
        if (p('supplier')) {
            $perm_role = pdo_fetchcolumn('select status1 from' . tablename('sz_yi_perm_role') . ' where id=' . $roleid);
        } else {
            $perm_role = 0;
        }
    }
}
$shopset = m('common')->getSysset('shop');
$sql = 'SELECT * FROM ' . tablename('sz_yi_category') . ' WHERE `uniacid` = :uniacid ORDER BY `parentid`, `displayorder` DESC';
$category = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']), 'id');
$parent = $children = array();
if (!empty($category)) {
    foreach ($category as $cid => $cate) {
        if (!empty($cate['parentid'])) {
            $children[$cate['parentid']][] = $cate;
        } else {
            $parent[$cate['id']] = $cate;
        }
    }
}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('exhelper.short.view');
    if (!empty($_GPC['shorttitle'])) {
        ca('exhelper.short.save');
        foreach ($_GPC['shorttitle'] as $id => $shorttitle) {
            pdo_update('sz_yi_goods', array('shorttitle' => $shorttitle), array('id' => $id, 'uniacid' => $_W['uniacid']));
        }
        plog('exhelper.short.edit', '批量修改商品简称');
        message('商品简称成功！', $this->createPluginWebUrl('exhelper/short', array('op' => 'display')), 'success');
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    if ($perm_role == 0) {
        $condition = ' WHERE `uniacid` = :uniacid AND `deleted` = :deleted';
    }
    if ($perm_role == 1) {
        $condition = " WHERE `uniacid` = :uniacid AND `deleted` = :deleted and supplier_uid={$_W['uid']}";
    }
    $params = array(':uniacid' => $_W['uniacid'], ':deleted' => '0');
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
        $condition .= ' AND `title` LIKE :title';
        $params[':title'] = '%' . trim($_GPC['keyword']) . '%';
    }
    if (!empty($_GPC['category']['thirdid'])) {
        $condition .= ' AND `tcate` = :tcate';
        $params[':tcate'] = intval($_GPC['category']['thirdid']);
    }
    if (!empty($_GPC['category']['childid'])) {
        $condition .= ' AND `ccate` = :ccate';
        $params[':ccate'] = intval($_GPC['category']['childid']);
    }
    if (!empty($_GPC['category']['parentid'])) {
        $condition .= ' AND `pcate` = :pcate';
        $params[':pcate'] = intval($_GPC['category']['parentid']);
    }
    if (isset($_GPC['status'])) {
        $condition .= ' AND `status` = :status';
        $params[':status'] = intval($_GPC['status']);
    }
    if ($_GPC['shortstatus'] == '0') {
        $condition .= ' AND `shorttitle` =\'\'';
    } else {
        if ($_GPC['shortstatus'] == '1') {
            $condition .= ' AND `shorttitle` <>\'\'';
        }
    }
    $sql = 'SELECT COUNT(*) FROM ' . tablename('sz_yi_goods') . $condition;
    $total = pdo_fetchcolumn($sql, $params);
    if (!empty($total)) {
        $sql = 'SELECT id,title,thumb,shorttitle FROM ' . tablename('sz_yi_goods') . $condition . " ORDER BY `status` DESC, `displayorder` DESC,\r\n\t\t\t\t\t\t`id` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
        $list = pdo_fetchall($sql, $params);
        $pager = pagination($total, $pindex, $psize);
    }
}
load()->func('tpl');
include $this->template('short');