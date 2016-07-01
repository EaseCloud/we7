<?php
	if (empty($_GPC['id'])) {
        message('抱歉，参数错误！', '', 'error');
    }
	$id = intval($_GPC['id']);
	$uniacid=$_W['uniacid'];
	$goods = pdo_fetch("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and id = '{$id}' ");
	include $this->template('exchange');
?>