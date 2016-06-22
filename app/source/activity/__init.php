<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
checkauth();
load()->model('activity');
load()->model('mc');
$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames'));
if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
	foreach ($unisettings['creditnames'] as $key=>$credit) {
		$creditnames[$key] = $credit['title'];
	}
}


$sql = 'SELECT `status` FROM ' . tablename('mc_card') . " WHERE `uniacid` = :uniacid";
$cardstatus = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));

if($do == 'token_qrcode') {
	require_once('../framework/library/qrcode/phpqrcode.php');
	$errorCorrectionLevel = "L";
	$matrixPointSize = "8";
	$token_id = intval($_GPC['id']);
	$recid = intval($_GPC['recid']);
	$type = intval($_GPC['type']);
	$url = $_W['siteroot'] . 'app' . ltrim(murl('entry', array('m' => 'paycenter', 'do' => 'syscard', 'op' => 'consume', 'recid' => $recid, 'type' => $type)), '.');
	QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
	exit();
}


