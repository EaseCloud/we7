<?php
	$url=$_W['siteroot'];
	$url.='app/';
	$url.=$this->createMobileUrl('prize');
	$myprize=pdo_fetchall("SELECT * FROM " . tablename('feng_goodslist') . " WHERE uniacid= '{$_W['uniacid']}' AND q_user= '{$_W['fans']['from_user']}' AND status=1 ORDER BY q_end_time DESC");

	include $this->template('prize');
?>