<?php
	$condition = '';
	$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and status =1 and sid = '{$_GPC['sid']}' $condition ORDER BY id DESC" );
	include $this->template('showperiod');
?>