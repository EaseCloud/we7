<?php
	$result="";
	$data=array(
		'uniacid'=>$_W['uniacid'],
		'from_user'=>$_W['fans']['from_user'],
		'realname'=>$_GPC['acceptname'],
		'nickname'=>$_GPC['nickname'],
		'mobile'=>$_GPC['phone'],
		'address'=>$_GPC['addr'],
	);

	if (empty($_GPC['id'])) {
		if(pdo_insert(feng_member,$data))
		{
			$result="您的资料修改成功！";
		}
		else
		{
			$result="您的资料修改失败！";
		}
	}else{
		if(pdo_update(feng_member, $data, array('id' => $_GPC['id'])))
		{
			$result="您的资料修改成功！";
		}
		else
		{
			$result="您的资料修改失败！";
		}
	}
	echo $result;
?>