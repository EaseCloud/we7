<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$id = intval($_GPC['id']);
$data = pdo_fetch('select * from ' . tablename('sz_yi_coupon_data') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
if (empty($data)) {
	if (empty($coupon)) {
		header('location: ' . $this->createPluginMobileUrl('coupon/my'));
		exit;
	}
}
$coupon = pdo_fetch('select * from ' . tablename('sz_yi_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $data['couponid'], ':uniacid' => $_W['uniacid']));
if (empty($coupon)) {
	header('location: ' . $this->createPluginMobileUrl('coupon/my'));
	exit;
}
$coupon['gettime'] = $data['gettime'];
$coupon['back'] = $data['back'];
$coupon['backtime'] = $data['backtime'];
$coupon['used'] = $data['used'];
$coupon['usetime'] = $data['usetime'];
$time = time();
$coupon = $this->model->setMyCoupon($coupon, $time);
$num = pdo_fetchcolumn('select ifnull(count(*),0) from ' . tablename('sz_yi_coupon_data') . ' where couponid=:couponid and openid=:openid and uniacid=:uniacid and used=0 ', array(':couponid' => $coupon['id'], ':openid' => $openid, ':uniacid' => $_W['uniacid']));
$canuse = !$coupon['past'] && empty($data['used']);
$useurl = $this->createMobileUrl('shop/list');
if ($coupon['coupontype'] == 1) {
	$useurl = $this->createMobileUrl('member/recharge');
}
$set = $this->model->getSet();
$this->model->setShare();
include $this->template('mydetail');
