<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

ca('statistics.view.member_cost');
$condition = " and o.uniacid={$_W['uniacid']}";
$pindex    = max(1, intval($_GPC['page']));
$psize     = 20;
$params    = array();
$shop      = m('common')->getSysset('shop');
if (!empty($_GPC['datetime'])) {
    $starttime = strtotime($_GPC['datetime']['start']);
    $endtime   = strtotime($_GPC['datetime']['end']);
    $condition .= " AND o.createtime >={$starttime} AND o.createtime <= {$endtime} ";
}
$condition1 = ' and m.uniacid=:uniacid';
$params1 = array(':uniacid' => $_W['uniacid']);
if (!empty($_GPC['realname'])) {
    $condition1 .= " and ( m.realname like :realname or m.mobile like :realname or m.nickname like :realname)";
    $params1[':realname'] = "%{$_GPC['realname']}%";
}
$orderby = empty($_GPC['orderby']) ? 'ordermoney' : 'ordercount';
$sql     = "SELECT m.realname, m.mobile,m.avatar,m.nickname,l.levelname," . "(select ifnull( count(o.id) ,0) from  " . tablename('sz_yi_order') . " o where o.openid=m.openid and o.status>=1 {$condition})  as ordercount," . "(select ifnull(sum(o.price),0) from  " . tablename('sz_yi_order') . " o where o.openid=m.openid  and o.status>=1 {$condition})  as ordermoney" . " from " . tablename('sz_yi_member') . " m  " . " left join " . tablename('sz_yi_member_level') . " l on l.id = m.level" . " where 1 {$condition1} order by {$orderby} desc ";
if (empty($_GPC['export'])) {
    $sql .= "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
}
$list  = pdo_fetchall($sql, $params1);
$total = pdo_fetchcolumn("select  count(*) from " . tablename('sz_yi_member') . ' m ' . " where 1 {$condition1} ", $params1);
$pager = pagination($total, $pindex, $psize);
if ($_GPC['export'] == 1) {
	ca('statistics.export.member_cost');
	plog('statistics.export.member_cost', '导出会员消费排行');
	m('excel')->export($list, array("title" => "会员消费排行报告-" . date('Y-m-d-H-i', time()), "columns" => array(array('title' => '昵称', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '手机号', 'field' => 'mobile', 'width' => 12), array('title' => '消费金额', 'field' => 'ordermoney', 'width' => 12), array('title' => '订单数', 'field' => 'ordercount', 'width' => 12))));
}
load()->func('tpl');
include $this->template('web/statistics/member_cost');
exit;
