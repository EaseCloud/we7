<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
//check_shop_auth
ca('coupon.set.view');
$set = $this->getSet();
if (checksubmit('submit')) {
	ca('coupon.set.save');
	$data = is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array();
	if (!$_W['isfounder']) {
		unset($data['backruntime']);
	}
	$this->updateSet($data);
	plog('coupon.set.save', '修改基本设置');
	message('设置保存成功!', referer(), 'success');
}
load()->func('tpl');
include $this->template('set');