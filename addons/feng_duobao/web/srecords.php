<?php
	$sid=$_GPC['id'];
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	
	$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_record')." WHERE uniacid = '{$uniacid}' and sid = '{$sid}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('feng_record') . " WHERE uniacid = '{$uniacid}' and sid = '{$sid}' ");
	$pager = pagination($total, $pindex, $psize);

	include $this->template('srecords');
?>