<?php
	if (empty($_GPC['id'])) {
            message('抱歉，参数错误！', '', 'error');
    }
	$id = intval($_GPC['id']);
	$uniacid=$_W['uniacid'];
	$goods = pdo_fetch("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and id = '{$id}' ");
	$pindex = 1;
	$psize = 10;
	$list = pdo_fetchall("SELECT * FROM ".tablename('feng_record')." WHERE uniacid = '{$uniacid}' and sid = '{$id}' and status=1 ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	include $this->template('details');
?>