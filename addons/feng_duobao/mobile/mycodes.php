<?php
	if (empty($_GPC['id'])) {
        message('抱歉，参数错误！', '', 'error');
    }
	$goods['periods']=$_GPC['periods'];
	$goods['title']=$_GPC['title'];
	$re_list = pdo_fetchall("SELECT * FROM " . tablename('feng_record') . " WHERE uniacid = '{$_W['uniacid']}' and from_user ='{$_W['fans']['from_user']}' and sid = '{$_GPC['id']}' and status =1 ORDER BY createtime DESC ");

	include $this->template('mycodes');
?>