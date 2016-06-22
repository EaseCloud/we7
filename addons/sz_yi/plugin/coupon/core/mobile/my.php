<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$used = intval($_GPC['used']);
$past = intval($_GPC['past']);
if ($_W['isajax']) {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$time = time();
	$sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb from ' . tablename('sz_yi_coupon_data') . ' d';
	$sql .= ' left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id';
	$sql .= ' where d.openid=:openid and d.uniacid=:uniacid ';
	if (!empty($past)) {
		$sql .= ' and  ( (c.timelimit =0 and c.timedays<>0 and  c.timedays*86400 + d.gettime <unix_timestamp()) or (c.timelimit=1 and c.timeend<unix_timestamp() ))';
	} else if (!empty($used)) {
		$sql .= ' and d.used =1 ';
	} else if (empty($used)) {
		$sql .= " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<={$time} && c.timeend>={$time})) and  d.used =0 ";
	}
	$sql .= ' order by d.gettime desc  LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
	$coupons = set_medias(pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid'])), 'thumb');
	foreach ($coupons as &$row) {
		$row = $this->model->setMyCoupon($row, $time);
	}
	unset($row);
	show_json(1, array('list' => $coupons, 'pagesize' => $psize));
}
$set = $this->model->getSet();
$this->model->setShare();
include $this->template('my');
