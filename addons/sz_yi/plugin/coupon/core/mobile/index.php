<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$catid = trim($_GPC['catid']);
if ($_W['isajax']) {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$time = time();
	$sql = 'select id,timelimit,timedays,timestart,timeend,thumb,couponname,enough,backtype,deduct,discount,backmoney,backcredit,backredpack,bgcolor,thumb,credit,money,getmax from ' . tablename('sz_yi_coupon') . ' c ';
	$sql .= ' where uniacid=:uniacid and gettype=1 and (total=-1 or total>0) and ( timelimit = 0 or  (timelimit=1 and timeend>unix_timestamp()))';
	if (!empty($catid)) {
		$sql .= ' and catid=' . $catid;
	}
	$sql .= ' order by displayorder desc, id desc  LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
	$coupons = set_medias(pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'])), 'thumb');
	foreach ($coupons as &$row) {
		$row = $this->model->setCoupon($row, $time);
	}
	unset($row);
	show_json(1, array('list' => $coupons, 'pagesize' => $psize));
}
$set = $this->model->getSet();
if (!empty($set['closecenter'])) {
	header('location: ' . $this->createMobileUrl('member'));
	exit;
}
$advs = is_array($set['advs']) ? $set['advs'] : array();
$shop = m('common')->getSysset('shop');
$category = pdo_fetchall('select * from ' . tablename('sz_yi_coupon_category') . ' where uniacid=:uniacid and status=1 order by displayorder desc', array(':uniacid' => $_W['uniacid']));
$this->model->setShare();
include $this->template('center');
