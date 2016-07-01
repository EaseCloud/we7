<?php
	if (empty($_GPC['id']) or empty($_GPC['sid'])) {
        message('抱歉，参数错误！', '', 'error');
    }
	$goods = pdo_fetch("SELECT * FROM " . tablename('feng_goodslist') . " WHERE uniacid= '{$_W['uniacid']}' AND id= '{$_GPC['id']}'");
	$allgoods = pdo_fetchall("SELECT * FROM " . tablename('feng_goodslist') . " WHERE uniacid= '{$_W['uniacid']}' AND sid= '{$_GPC['sid']}' AND status=1 ORDER BY q_end_time DESC");

	include $this->template('period');
?>