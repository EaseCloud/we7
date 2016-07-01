<?php
	$people = pdo_fetch("SELECT * FROM " . tablename('feng_member') . " WHERE uniacid= '{$_W['uniacid']}' AND from_user= '{$_W['fans']['from_user']}'");
	if (!$people) {
		message('请先填写您的资料！', $this->createMobileUrl('prodata'), 'warning');
	}
	load()->model('mc');
	$result = mc_credit_fetch($_W['member']['uid']);
	include $this->template('profile');
?>