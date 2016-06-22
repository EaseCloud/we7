<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'detail', 'use', 'qr');
$do = in_array($do, $dos) ? $do : 'display';
$logo = pdo_fetchcolumn('SELECT logourl FROM  ' . tablename('coupon_setting') . ' WHERE uniacid = :aid AND acid = :cid', array(':aid' => $_W['uniacid'], ':cid' => $_W['acid']));
$colors = array(
	'Color010' => '#55bd47', 'Color020' => '#10ad61', 'Color030' => '#35a4de', 'Color040' => '#3d78da', 'Color050' => '#9058cb',
	'Color060' => '#de9c33', 'Color070' => '#ebac16', 'Color080' => '#f9861f', 'Color081' => '#f08500', 'Color082' => '#a9d92d',
	'Color090' => '#e75735', 'Color100' => '#d54036', 'Color101' => '#cf3e36'
);

if($do == 'display') {
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'discount';
	$condition = ' WHERE acid = :acid AND type = :type AND is_display = 1 AND status = 3';
		$parma[':acid'] = $_W['acid'];
	$parma[':type'] = $type;
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('coupon') . $condition, $parma);
	$data = pdo_fetchall('SELECT id,card_id,title,color,brand_name,date_info FROM ' . tablename('coupon') . $condition . ' ORDER BY id DESC LIMIT ' .($pindex - 1) * $psize.','.$psize, $parma);

	if(!empty($data)) {
		foreach($data as &$da) {
			$da['date_info'] = @iunserializer($da['date_info']);
			if($da['date_info']['time_type'] == 1) {
				$da['endtime'] = '有效期至:' . $da['date_info']['time_limit_end'];
			} else {
				$da['endtime'] = '领取后' . $da['date_info']['deadline'] . '天生效' . $da['date_info']['limit'] . '天内有效';
			}
		}
	}
	$pager = pagination($total, $pindex, $psize);
}

if($do == 'detail') {
	$id = intval($_GPC['id']);
	load()->classs('coupon');
	$acc = new coupon($_W['acid']);
	$status = $acc->AddCard($id);
	$out['errno'] = 0;
	$out['error'] = '';
	if(is_error($status)) {
		$out['errno'] = 1;
		$out['error'] = $status['message'];
		exit(json_encode($out));
	}
	$out['error'] = $status;
	exit(json_encode($out));
}

if($do == 'use') {
		$card_id = trim($_GPC['card_id']);
	$encrypt_code = trim($_GPC['encrypt_code']);
	$openid = trim($_GPC['openid']);
	if(empty($card_id) || empty($encrypt_code)) {
		message('卡券签名参数错误');
	}
	$card = pdo_get('coupon', array('acid' => $_W['acid'], 'card_id' => $card_id));
	if(empty($card)) {
		message('卡券不存在或已删除');
	}
	$card['date_info'] = iunserializer($card['date_info']);
	$error_code = 0;
		$coupon = new coupon($_W['acid']);
	if(is_null($coupon)) {
		message('系统错误');
	}
	$code = $coupon->DecryptCode(array('encrypt_code' => $encrypt_code));
	if(is_error($code)) {
		$error_code = 1;
	} else {
		$record = pdo_get('coupon_record',  array('acid' => $_W['acid'], 'card_id' => $card_id, 'code' => $code['code']));
	}

	if(checksubmit()) {
		$password = trim($_GPC['password']);
		$clerk = pdo_get('activity_clerks', array('uniacid' => $_W['uniacid'], 'password' => $password));
		if(empty($clerk)) {
			message('店员密码错误', referer(), 'error');
		}
		$code = $code['code'];
		if(!$code) {
			message('code码错误', referer(), 'error');
		}
				$status = $coupon->ConsumeCode(array('code' => $code));
		if(is_error($status)) {
			message($status['message'], referer(), 'error');
		}
		pdo_update('coupon_record', array('status' => 3, 'clerk_id' => $clerk['id'], 'clerk_type' => 3, 'store_id' => $clerk['storeid'], 'clerk_name' => $clerk['name'], 'usetime' => TIMESTAMP), array('acid' => $_W['acid'], 'card_id' => $card_id, 'openid' => $openid, 'code' => $code));
		message('核销微信卡券成功', url('mc/home'), 'success');
	}
}

if($do == 'qr') {
		require_once('../framework/library/qrcode/phpqrcode.php');
	$errorCorrectionLevel = "L";
	$matrixPointSize = "5";
	$id = intval($_GPC['id']);
	$code = trim($_GPC['code']);
	$url = murl('clerk/wechat', array('uid' => $_W['member']['uid'], 'id' => $id, 'code' => $code), false, true);
	QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);
	exit();
}

template('wechat/card');
