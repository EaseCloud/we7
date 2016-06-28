<?php
global $_W, $_GPC;
//check_shop_auth
ca('poster.log');
$pindex = max(1, intval($_GPC['page']));
$psize = 10;
$params = array(':uniacid' => $_W['uniacid']);
$condition = ' and log.uniacid=:uniacid and posterid=' . intval($_GPC['id']);
if (!empty($_GPC['keyword'])) {
	$_GPC['keyword'] = trim($_GPC['keyword']);
	$condition .= ' AND ( m.nickname LIKE :keyword or m.realname LIKE :keyword or m.mobile LIKE :keyword ) ';
	$params[':keyword'] = '%' . trim($_GPC['keyword']) . '%';
}
if (!empty($_GPC['keyword1'])) {
	$_GPC['keyword1'] = trim($_GPC['keyword1']);
	$condition .= ' AND ( m1.nickname LIKE :keyword1 or m1.realname LIKE :keyword1 or m1.mobile LIKE :keyword1 ) ';
	$params[':keyword1'] = '%' . trim($_GPC['keyword1']) . '%';
}
if (empty($starttime) || empty($endtime)) {
	$starttime = strtotime('-1 month');
	$endtime = time();
}
if (!empty($_GPC['time'])) {
	$starttime = strtotime($_GPC['time']['start']);
	$endtime = strtotime($_GPC['time']['end']);
	if ($_GPC['searchtime'] == '1') {
		$condition .= ' AND log.createtime >= :starttime AND log.createtime <= :endtime ';
		$params[':starttime'] = $starttime;
		$params[':endtime'] = $endtime;
	}
}
$list = pdo_fetchall('SELECT log.*, m.avatar,m.nickname,m.realname,m.mobile,m1.avatar as avatar1,m1.nickname as nickname1,m1.realname as realname1,m1.mobile as mobile1 FROM ' . tablename('sz_yi_postera_log') . ' log ' . ' left join ' . tablename('sz_yi_member') . ' m1 on m1.openid = log.openid and m1.uniacid = log.uniacid ' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid = log.from_openid  and m.uniacid = log.uniacid' . " WHERE 1 {$condition} ORDER BY log.createtime desc " . '  LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $params);
$total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('sz_yi_postera_log') . ' log ' . ' left join ' . tablename('sz_yi_member') . ' m1 on m1.openid = log.openid and m1.uniacid = log.uniacid ' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid = log.from_openid and m.uniacid = log.uniacid ' . " where 1 {$condition}  ", $params);
foreach ($list as &$row) {
	$row['times'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_postera_log') . ' where from_openid=:from_openid and posterid=:posterid and uniacid=:uniacid', array(':from_openid' => $row['from_openid'], ':posterid' => intval($_GPC['id']), ':uniacid' => $_W['uniacid']));
}
unset($row);
$pager = pagination($total, $pindex, $psize);
load()->func('tpl');
include $this->template('log');
