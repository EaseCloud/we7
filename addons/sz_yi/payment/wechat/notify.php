<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL ^ E_NOTICE);
define('IN_MOBILE', true);
$input = file_get_contents('php://input');
if (!empty($input) && empty($_GET['out_trade_no'])) {
	$obj = simplexml_load_string($input, 'SimpleXMLElement', LIBXML_NOCDATA);
	$data = json_decode(json_encode($obj), true);
	if (empty($data)) {
		exit('fail');
	}
	if ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS') {
		exit('fail');
	}
	$get = $data;
} else {
	$get = $_GET;
}
require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
$strs = explode(':', $get['attach']);
$_W['uniacid'] = $_W['weid'] = intval($strs[0]);
$type = intval($strs[1]);
$total_fee = $get['total_fee'] / 100;
if ($type == 0) {
	$paylog = '
-------------------------------------------------
';
	$paylog .= 'orderno: ' . $get['out_trade_no'] . '
';
	$paylog .= 'paytype: alipay
';
	$paylog .= 'data: ' . json_encode($_POST) . '
';
	m('common')->paylog($paylog);
}
$setting = uni_setting($_W['uniacid'], array('payment'));
if (is_array($setting['payment'])) {
	$wechat = $setting['payment']['wechat'];
	if (!empty($wechat)) {
		m('common')->paylog('setting: ok');
		ksort($get);
		$string1 = '';
		foreach ($get as $k => $v) {
			if ($v != '' && $k != 'sign') {
				$string1 .= "{$k}={$v}&";
			}
		}
		$wechat['signkey'] = ($wechat['version'] == 1) ? $wechat['key'] : $wechat['signkey'];
		$sign = strtoupper(md5($string1 . "key={$wechat['signkey']}"));
		if ($sign == $get['sign']) {
			m('common')->paylog('sign: ok');
			if (empty($type)) {
				$tid = $get['out_trade_no'];
				if (strexists($tid, 'GJ')) {
					$tids = explode('GJ', $tid);
					$tid = $tids[0];
				}
				$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';
				$params = array();
				$params[':tid'] = $tid;
				$params[':module'] = 'sz_yi';
				$log = pdo_fetch($sql, $params);
				m('common')->paylog('log: ' . (empty($log) ? '' : json_encode($log)) . '');
				if (!empty($log) && $log['status'] == '0' && $log['fee'] == $total_fee) {
					m('common')->paylog('corelog: ok');
					$site = WeUtility::createModuleSite($log['module']);

					if (!is_error($site)) {
						$method = 'payResult';
						if (method_exists($site, $method)) {
							$ret = array();
							$ret['weid'] = $log['weid'];
							$ret['uniacid'] = $log['uniacid'];
							$ret['result'] = 'success';
							$ret['type'] = $log['type'];
							$ret['from'] = 'return';
							$ret['tid'] = $log['tid'];
							$ret['user'] = $log['openid'];
							$ret['fee'] = $log['fee'];
							$ret['tag'] = $log['tag'];
							$result = $site->$method($ret);
							m('common')->paylog('payResult: ' . json_encode($result) . '.
');
							if (is_array($result) && $result['result'] == 'success') {
								$log['tag'] = iunserializer($log['tag']);
								$log['tag']['transaction_id'] = $get['transaction_id'];
								$record = array();
								$record['status'] = '1';
								$record['tag'] = iserializer($log['tag']);
								pdo_update('core_paylog', $record, array('plid' => $log['plid']));
								exit('success');
							}
						}
					}
				}
			} else if ($type == 1) {
				$logno = trim($get['out_trade_no']);
				if (empty($logno)) {
					exit;
				}
				$log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `uniacid`=:uniacid and `logno`=:logno limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $logno));
				if (!empty($log) && empty($log['status']) && $log['fee'] == $total_fee) {
					pdo_update('sz_yi_member_log', array('status' => 1, 'rechargetype' => 'wechat'), array('id' => $log['id']));
					m('member')->setCredit($log['openid'], 'credit2', $log['money'], array(0, '芸众商城会员充值:credit2:' . $log['money']));
					m('member')->setRechargeCredit($log['openid'], $log['money']);
					if (p('sale')) {
						p('sale')->setRechargeActivity($log);
					}
					if (!empty($log['couponid'])) {
						$pc = p('coupon');
						if ($pc) {
							$pc->useRechargeCoupon($log);
						}
					}
					m('notice')->sendMemberLogMessage($log['id']);
				}
			} else if ($type == 2) {
				$logno = trim($get['out_trade_no']);
				if (empty($logno)) {
					exit;
				}
				$log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_creditshop_log') . ' WHERE `logno`=:logno and `uniacid`=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $logno));
				if (!empty($log) && empty($log['status'])) {
					pdo_update('sz_yi_creditshop_log', array('paystatus' => 1, 'paytype' => 1), array('id' => $log['id']));
				}
			} else if ($type == 3) {
				$dispatchno = trim($get['out_trade_no']);
				if (empty($dispatchno)) {
					exit;
				}
				$log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_creditshop_log') . ' WHERE `dispatchno`=:dispatchno and `uniacid`=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':dispatchno' => $dispatchno));
				if (!empty($log) && empty($log['dispatchstatus'])) {
					pdo_update('sz_yi_creditshop_log', array('dispatchstatus' => 1), array('id' => $log['id']));
				}
			} else if ($type == 4) {
				$plugincoupon = p('coupon');
				if ($plugincoupon) {
					$logno = trim($get['out_trade_no']);
					$plugincoupon->payResult($logno);
				}
			}
		}
	}
}
exit('fail');
