<?php
	$uniacid=$_W['uniacid'];
	$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and status =2 ");
	$pindex = 1;
	$psize = 2;
	$condition = '';
	$s_pos = pdo_fetchall("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and status =2 $condition ORDER BY sid DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	include $this->template('index');
?>