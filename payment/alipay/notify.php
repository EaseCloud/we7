<?php
error_reporting(0);
define('IN_MOBILE', true);

if(!empty($_POST)) {
	$out_trade_no = $_POST['out_trade_no'];
	require '../../framework/bootstrap.inc.php';
	$_W['uniacid'] = $_W['weid'] = intval($_POST['body']);
	$setting = uni_setting($_W['uniacid'], array('payment'));
	if(is_array($setting['payment'])) {
		$alipay = $setting['payment']['alipay'];
		if(!empty($alipay)) {
			$prepares = array();
			foreach($_POST as $key => $value) {
				if($key != 'sign' && $key != 'sign_type') {
					$prepares[] = "{$key}={$value}";
				}
			}
			sort($prepares);
			$string = implode($prepares, '&');
			$string .= $alipay['secret'];
			$sign = md5($string);
			if($sign == $_POST['sign']) {
				$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniontid`=:uniontid';
				$params = array();
				$params[':uniontid'] = $out_trade_no;
				$log = pdo_fetch($sql, $params);
				if(!empty($log) && $log['status'] == '0') {
					$log['transaction_id'] = $_POST['trade_no'];
					$record = array();
					$record['status'] = '1';
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
							$ret['result'] = 'success';
							$ret['type'] = $log['type'];
							$ret['from'] = 'notify';
							$ret['tid'] = $log['tid'];
							$ret['uniontid'] = $log['uniontid'];
							$ret['transaction_id'] = $log['transaction_id'];
							$ret['user'] = $log['openid'];
							$ret['fee'] = $log['fee'];
							$ret['is_usecard'] = $log['is_usecard'];
							$ret['card_type'] = $log['card_type'];
							$ret['card_fee'] = $log['card_fee'];
							$ret['card_id'] = $log['card_id'];
							$site->$method($ret);
							exit('success');
						}
					}
				}
			}
		}
	}
}
exit('fail');
