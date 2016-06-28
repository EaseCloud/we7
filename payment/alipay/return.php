<?php
error_reporting(0);
define('IN_MOBILE', true);
if (empty($_GET['out_trade_no'])) {
	exit('request failed.');
}
require '../../framework/bootstrap.inc.php';
load()->app('common');
load()->app('template');
$_W['uniacid'] = $_W['weid'] = intval($_GET['body']);
$setting = uni_setting($_W['uniacid'], array('payment'));
if (!is_array($setting['payment'])) {
	exit('request failed.');
}
$alipay = $setting['payment']['alipay'];
if (empty($alipay)) {
	exit('request failed.');
}
$prepares = array();
foreach ($_GET as $key => $value) {
	if ($key != 'sign' && $key != 'sign_type') {
		$prepares[] = "{$key}={$value}";
	}
}
sort($prepares);
$string = implode($prepares, '&');
$string .= $alipay['secret'];
$sign = md5($string);
if($sign == $_GET['sign'] && $_GET['is_success'] == 'T' && ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS')) {
	$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
	WeUtility::logging('pay-alipay', var_export($_GET, true));
	$params = array();
	$params[':uniontid'] = $_GET['out_trade_no'];
	$log = pdo_fetch($sql, $params);
	if (!empty($log)) {
		if (!empty($log['status']) && ($_GET['total_fee'] == $log['card_fee'])) {
			$record = array();
			$record['status'] = $log['status'] = '1';
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
				$now = time();
				$log['card_id'] = intval($log['card_id']);
				pdo_query('UPDATE ' . tablename('activity_coupon_record') . " SET status = 2, usetime = {$now}, usemodule = '{$log['module']}' WHERE uniacid = :aid AND couponid = :cid AND uid = :uid AND status = 1 {$condition} LIMIT 1", array(':aid' => $_W['uniacid'], ':uid' => $log['openid'], ':cid' => $log['card_id']));
			}
		}
		$site = WeUtility::createModuleSite($log['module']);
		if (!is_error($site)) {
			$site->weid = $_W['weid'];
			$site->uniacid = $_W['uniacid'];
			$site->inMobile = true;
			$method = 'payResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['weid'] = $log['weid'];
				$ret['uniacid'] = $log['uniacid'];
				$ret['acid'] = $log['acid'];
				$ret['uniontid'] = $log['uniontid'];
				$ret['result'] = $log['status'] == '1' ? 'success' : 'failed';
				$ret['type'] = $log['type'];
				$ret['from'] = 'notify';
				$ret['tid'] = $log['tid'];
				$ret['user'] = $log['openid'];
				$ret['fee'] = $log['fee'];
				$ret['is_usecard'] = $log['is_usecard'];
				$ret['card_type'] = $log['card_type'];
				$ret['card_fee'] = $log['card_fee'];
				$ret['card_id'] = $log['card_id'];
				$site->$method($ret);
				$ret['from'] = 'return';
				exit($site->$method($ret));
			}
		}
	}
} else {
	message('支付异常，请返回微信客户端查看订单状态或是联系管理员', '', 'error');
}