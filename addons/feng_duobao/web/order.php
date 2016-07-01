<?php
	$send_state=$_GPC['state'];
	if ($send_state==0 or $send_state==1) {
		$goodses=pdo_fetchall("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and status =1 and send_state='{$send_state}' ORDER BY id DESC" );
	}else{
		$goodses=pdo_fetchall("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and status =1 ORDER BY id DESC" );
	}
	
	include $this->template('order');
?>