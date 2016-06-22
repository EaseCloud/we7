<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('platform_url2qr');
$dos = array('display', 'change', 'qr', 'chat');
$do = !empty($_GPC['do']) && in_array($do, $dos) ? $do : 'display';
load()->model('account');
if($do == 'display') {
	template('platform/url2qr');
}

if($do == 'change') {
	if($_W['ispost']) {
		load()->func('communication');
		$longurl = trim($_GPC['longurl']);
		$token = WeAccount::token(WeAccount::TYPE_WEIXIN);
		$url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token={$token}";
		$send = array();
		$send['action'] = 'long2short';
		$send['long_url'] = $longurl;
		$response = ihttp_request($url, json_encode($send));
		if(is_error($response)) {
			$result = error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			$result =  error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			$result = error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}");
		}
		if(is_error($result)) {
			exit(json_encode(array('errcode' => -1, 'errmsg' => $result['message'])));
		}
		exit(json_encode($result));
	} else {
		exit('err');
	}
}

if($do == 'qr') {
	$url = $_GPC['url'];
	require(IA_ROOT . '/framework/library/qrcode/phpqrcode.php');
	$errorCorrectionLevel = "L";
	$matrixPointSize = "5";
	QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
	exit();
}
