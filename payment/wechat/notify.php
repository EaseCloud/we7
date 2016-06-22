<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
define('IN_MOBILE', true);
require '../../framework/bootstrap.inc.php';
$input = file_get_contents('php://input');
$isxml = true;
if (!empty($input) && empty($_GET['out_trade_no'])) {
	$obj = isimplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
	$data = json_decode(json_encode($obj), true);
	if (empty($data)) {
		$result = array(
			'return_code' => 'FAIL',
			'return_msg' => ''
		);
		echo array2xml($result);
		exit;
	}
	if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
		$result = array(
			'return_code' => 'FAIL',
			'return_msg' => empty($data['return_msg']) ? $data['err_code_des'] : $data['return_msg']
		);
		echo array2xml($result);
		exit;
	}
	$get = $data;
} else {
	$isxml = false;
	$get = $_GET;
}

$_W['uniacid'] = $_W['weid'] = intval($get['attach']);
$setting = uni_setting($_W['uniacid'], array('payment'));
if(is_array($setting['payment'])) {
	$wechat = $setting['payment']['wechat'];
	WeUtility::logging('pay', var_export($get, true));
	if(!empty($wechat)) {
		ksort($get);
		$string1 = '';
		foreach($get as $k => $v) {
			if($v != '' && $k != 'sign') {
				$string1 .= "{$k}={$v}&";
			}
		}

		$wechat['signkey'] = ($wechat['version'] == 1) ? $wechat['key'] : $wechat['signkey'];
		$sign = strtoupper(md5($string1 . "key={$wechat['signkey']}"));
		if($sign == $get['sign']) {
			$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
			$params = array();
			$params[':uniontid'] = $get['out_trade_no'];
			$log = pdo_fetch($sql, $params);
			if(!empty($log) && $log['status'] == '0') {
				$log['tag'] = iunserializer($log['tag']);
				$log['tag']['transaction_id'] = $get['transaction_id'];
				$log['uid'] = $log['tag']['uid'];
				$log['transaction_id'] = $get['transaction_id'];
				$record = array();
				$record['status'] = '1';
				$record['tag'] = iserializer($log['tag']);
				pdo_update('core_paylog', $record, array('plid' => $log['plid']));
				if($log['is_usecard'] == 1 && $log['card_type'] == 1 &&  !empty($log['encrypt_code']) && $log['acid']) {
					load()->classs('coupon');
					$acc = new coupon($log['acid']);
					$codearr['encrypt_code'] = $log['encrypt_code'];
					$codearr['module'] = $log['module'];
					$codearr['card_id'] = $log['card_id'];
					$acc->PayConsumeCode($codearr);
				}

				if($log['is_usecard'] == 1 && $log['card_type'] == 2) {
					$log['card_id'] = intval($log['card_id']);
					pdo_update('activity_coupon_record', array('status' => '2', 'usetime' => time(), 'usemodule' => $log['module']), array('uniacid' => $_W['uniacid'], 'recid' => $log['card_id'], 'status' => '1'));
				}

				$site = WeUtility::createModuleSite($log['module']);
				if(!is_error($site)) {
					$method = 'payResult';
					if (method_exists($site, $method)) {
						$ret = array();
						$ret['weid'] = $log['weid'];
						$ret['uniacid'] = $log['uniacid'];
						$ret['acid'] = $log['acid'];
						$ret['result'] = 'success';
						$ret['type'] = $log['type'];
						$ret['from'] = 'notify';
						$ret['tid'] = $log['tid'];
						$ret['uniontid'] = $log['uniontid'];
						$ret['transaction_id'] = $log['transaction_id'];
						$ret['trade_type'] = $get['trade_type'];
						$ret['follow'] = $get['is_subscribe'] == 'Y' ? 1 : 0;
						$ret['user'] = empty($get['openid']) ? $log['openid'] : $get['openid'];
						$ret['fee'] = $log['fee'];
						$ret['tag'] = $log['tag'];
						$ret['is_usecard'] = $log['is_usecard'];
						$ret['card_type'] = $log['card_type'];
						$ret['card_fee'] = $log['card_fee'];
						$ret['card_id'] = $log['card_id'];
						if(!empty($get['time_end'])) {
							$ret['paytime'] = strtotime($get['time_end']);
						}
						$site->$method($ret);
						if($isxml) {
							$result = array(
								'return_code' => 'SUCCESS',
								'return_msg' => 'OK'
							);
							echo array2xml($result);
							exit;
						} else {
							exit('success');
						}
					}
				}
			}
		}
	}
}
if($isxml) {
	$result = array(
		'return_code' => 'FAIL',
		'return_msg' => ''
	);
	echo array2xml($result);
	exit;
} else {
	exit('fail');
}
