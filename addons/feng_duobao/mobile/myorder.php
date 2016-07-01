<?php
	$ar = pdo_fetchall("SELECT * FROM " . tablename('feng_record') . " WHERE uniacid = '{$_W['uniacid']}' and from_user ='{$_W['fans']['from_user']}' and status =1 ORDER BY createtime DESC ");
	if (!empty($ar)) {
		foreach($ar as $item) {
		  $res[$item['sid']]+= $item['count'];
		}
		$number=0;
		/*$status=$_GPC['status'];*/
		foreach($res as $key=>$value) {
		  $p_record[$number]=pdo_fetch("SELECT * FROM " . tablename('feng_goodslist') . " WHERE uniacid = '{$_W['uniacid']}' and id ='{$key}'");
		  $p_record[$number]['allcount']=$value;
		  $number++;
		}
	}
	
	include $this->template('myorder');
?>