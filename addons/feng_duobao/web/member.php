<?php
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	
	$members = pdo_fetchall("SELECT * FROM ".tablename('feng_member')." WHERE uniacid = '{$uniacid}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('feng_member') . " WHERE uniacid = '{$uniacid}' ");
	
	$pager = pagination($total, $pindex, $psize);

	include $this->template('members');
?>