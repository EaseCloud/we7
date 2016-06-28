<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;

$shopset = m('common')->getSysset('shop');
ca('taobao.fetch');
$sql      = 'SELECT * FROM ' . tablename('sz_yi_category') . ' WHERE `uniacid` = :uniacid ORDER BY `parentid`, `displayorder` DESC';
$category = pdo_fetchall($sql, array(
    ':uniacid' => $_W['uniacid']
), 'id');
$parent   = $children = array();
if (!empty($category)) {
    foreach ($category as $cid => $cate) {
        if (!empty($cate['parentid'])) {
            $children[$cate['parentid']][] = $cate;
        } else {
            $parent[$cate['id']] = $cate;
        }
    }
}
load()->func('tpl');
include $this->template('index');
