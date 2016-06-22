<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
global $_W;
load()->func('file');
if (!in_array($do, array('local', 'wechat'))) {
	exit('Access Denied');
}

if ($do == 'local') {
	$condition = ' WHERE uniacid = :uniacid AND (amount-dosage>0) AND endtime > :time';
	$param = array(
		':uniacid' => $_W['uniacid'],
		':time' => TIMESTAMP,
	);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '. tablename('activity_coupon') . $condition, $param);
	$data = pdo_fetchall('SELECT * FROM ' . tablename('activity_coupon') . $condition . ' ORDER BY couponid DESC LIMIT ' . ($pindex - 1) * $psize . ', ' . $psize, $param, 'couponid');
	if(!empty($data)) {
		foreach($data as &$da) {
			$da['starttime_cn'] = date('Y-m-d', $da['starttime']);
			$da['endtime_cn'] = date('Y-m-d', $da['endtime']);
		}
	}
	message(array('page'=> pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '2', 'ajaxcallback'=>'null')), 'items' => $data), '', 'ajax');
}

if ($do == 'wechat') {
	$condition = ' WHERE uniacid = :uniacid AND is_display = 1';
	$param = array(
		':uniacid' => $_W['uniacid'],
	);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 15;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM '. tablename('coupon') . $condition, $param);
	$data = pdo_fetchall('SELECT * FROM ' . tablename('coupon') . $condition . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ', ' . $psize, $param, 'id');
	if(!empty($data)) {
		foreach($data as &$da) {
			$da['date_info'] = iunserializer($da['date_info']);
			$da['media_id'] = $da['card_id'];
			$da['logo_url'] = url('utility/wxcode/image', array('attach' => $da['logo_url']));
			$da['ctype'] = $da['type'];
			$da['type'] = 'wxcard';
		}
	}
	message(array('page'=> pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '2', 'ajaxcallback'=>'null')), 'items' => $data), '', 'ajax');
}

