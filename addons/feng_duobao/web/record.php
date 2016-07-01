<?php
	$status=$_GPC['status'];
	$uniacid=$_W['uniacid'];
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	
	if ($status==0) {
		$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_record')." WHERE uniacid = '{$uniacid}' and status =0 ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('feng_record') . " WHERE uniacid = '{$uniacid}' and status =0 ");
	}elseif ($status==1) {
		$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_record')." WHERE uniacid = '{$uniacid}' and status =1 ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('feng_record') . " WHERE uniacid = '{$uniacid}' and status =1 ");
	}else{
		$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_record')." WHERE uniacid = '{$uniacid}' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('feng_record') . " WHERE uniacid = '{$uniacid}' ");
	}
	
	$pager = pagination($total, $pindex, $psize);

	include $this->template('records');
?>