<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
if ($action != 'cash') {
	checkauth();
}
load()->model('activity');
$filter = array();
$coupons = activity_coupon_owned($_W['member']['uid'], $filter);
$tokens = activity_token_owned($_W['member']['uid'], $filter);

$setting = uni_setting($_W['uniacid'], array('creditnames', 'creditbehaviors', 'uc', 'payment', 'passport'));
$behavior = $setting['creditbehaviors'];
$creditnames = $setting['creditnames'];
$credits = mc_credit_fetch($_W['member']['uid'], '*');


$sql = 'SELECT `status` FROM ' . tablename('mc_card') . " WHERE `uniacid` = :uniacid";
$cardstatus = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));

$ucpage = pdo_fetch("SELECT * FROM ".tablename('site_page')." WHERE uniacid = :uniacid AND type = '3'", array(':uniacid' => $_W['uniacid']));
if (!empty($ucpage['params'])) {
	$ucpage['params'] = json_decode($ucpage['params'], true);
}

$title = $ucpage['title'];