<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$moduels = uni_modules();

$params = @json_decode(base64_decode($_GPC['id']), true);
$params['m'] = trim($params['m']);
$params['id'] = intval($params['id']);
if(empty($params) || !array_key_exists($params['m'], $moduels)) {
	message('访问错误.');
}

load()->model('activity');
$check = false;
$site = WeUtility::createModuleSite($params['m']);
if(!is_error($site)) {
	$site->weid = $_W['weid'];
	$site->uniacid = $_W['uniacid'];
	$site->inMobile = true;
	$method = 'grantCherk';
	if (method_exists($site, $method)) {
		$ret = array();
		$ret['couponid'] = $status['couponid']; 		$ret['type'] = $status['type']; 		$ret['uid'] = $_W['member']['uid'];
		$ret['weid'] = $_W['weid'];
		$ret['uniacid'] = $_W['uniacid'];
		$status = $site->$method($ret);
		if(!is_error($status)) {
			$check = true;
		}
	}
}

if($check) {
	$status = activity_module_card_grant($_W['member']['uid'], $params['id'], $params['m']);
	if(is_error($status)) {
		message($status['message'], referer(), 'error');
	}
} else {
	message('领取优惠券失败', referer(), 'error');
}

if(!is_error($site)) {
	$site->weid = $_W['weid'];
	$site->uniacid = $_W['uniacid'];
	$site->inMobile = true;
	$method = 'grantResult';
	if (method_exists($site, $method)) {
		$ret = array();
		$ret['result'] = 'success';
		$ret['couponid'] = $status['couponid']; 		$ret['type'] = $status['type']; 		$ret['uid'] = $_W['member']['uid'];
		$ret['weid'] = $_W['weid'];
		$ret['uniacid'] = $_W['uniacid'];
		exit($site->$method($ret));
	}
}




