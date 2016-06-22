<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$moduels = uni_modules();
$params = @json_decode(base64_decode($_GPC['params']), true);
if(empty($params) || !array_key_exists($params['module'], $moduels)) {
	message('访问错误.');
}
$setting = uni_setting($_W['uniacid'], 'payment');
$dos = array();
if(!empty($setting['payment']['credit']['switch'])) {
	$dos[] = 'credit';
}
if(!empty($setting['payment']['alipay']['switch'])) {
	$dos[] = 'alipay';
}
if(!empty($setting['payment']['wechat']['switch'])) {
	$dos[] = 'wechat';
}
if(!empty($setting['payment']['delivery']['switch'])) {
	$dos[] = 'delivery';
}
if(!empty($setting['payment']['unionpay']['switch'])) {
	$dos[] = 'unionpay';
}
if(!empty($setting['payment']['baifubao']['switch'])) {
	$dos[] = 'baifubao';
}
$do = $_GET['do'];
$type = in_array($do, $dos) ? $do : '';

if(empty($type)) {
	message('支付方式错误,请联系商家', '', 'error');
}
if(!empty($type)) {
	$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
	$pars  = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$pars[':module'] = $params['module'];
	$pars[':tid'] = $params['tid'];
	$log = pdo_fetch($sql, $pars);
	if(!empty($log) && ($type != 'credit' && !empty($_GPC['notify'])) && $log['status'] != '0') {
		message('这个订单已经支付成功, 不需要重复支付.');
	}
	$update_card_log = array(
		'is_usecard' => '0',
		'card_type' => '0',
		'card_id' => '0',
		'card_fee' => $log['fee']
	);
	pdo_update('core_paylog', $update_card_log, array('plid' => $log['plid']));
	$log['is_usecard'] = '0';
	$log['card_type'] = '0';
	$log['card_id'] = '0';
	$log['card_fee'] = $log['fee'];
	
	$moduleid = pdo_fetchcolumn("SELECT mid FROM ".tablename('modules')." WHERE name = :name", array(':name' => $params['module']));
	$moduleid = empty($moduleid) ? '000000' : sprintf("%06d", $moduleid);
	
	$record = array();
	$record['type'] = $type;
	if (empty($log['uniontid'])) {
		$record['uniontid'] = $log['uniontid'] = date('YmdHis').$moduleid.random(8,1);
	}
	if($type != 'delivery') {
				if($setting['payment']['card']['switch'] == 2 && !empty($_GPC['card_id']) && !empty($_GPC['encrypt_code']) && !empty($_W['acid'])) {
			$card_id = base64_decode($_GPC['card_id']);
			$card = pdo_fetch('SELECT id,card_id,type,extra FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $_W['acid'], ':card_id' => $card_id));
			$card['fee'] = $record['card_fee'];
			if(!empty($card)) {
				$record['is_usecard'] = 1;
				$record['card_type'] = 1;
				if($card['type'] == 'discount') {
					$card['fee'] = sprintf("%.2f", ($log['fee'] * ($card['extra'] / 100)));
				} elseif($card['type'] == 'cash') {
					$cash = iunserializer($card['extra']);
					if($log['fee'] >= $cash['least_cost']) {
												$card['fee'] =  sprintf("%.2f", ($log['fee'] -  $cash['reduce_cost']));
					}
				}
				$record['card_fee'] = $card['fee'];
				$record['card_id'] = $card['card_id'];
				$record['encrypt_code'] = trim($_GPC['encrypt_code']);
			}
		}
		if($setting['payment']['card']['switch'] == 3 && !empty($_GPC['coupon_id'])) {
			$coupon_id = intval($_GPC['coupon_id']);
						$coupon = pdo_fetch('SELECT * FROM ' . tablename('activity_coupon') . ' WHERE uniacid = :aid AND couponid = :id', array(':aid' => $_W['uniacid'], ':id' => $coupon_id));
			$use_modules = pdo_fetchall('SELECT module FROM ' . tablename('activity_coupon_modules') . ' WHERE uniacid = :uniacid AND couponid = :couponid', array(':uniacid' => $_W['uniacid'], ':couponid' => $coupon_id), 'module');
			$use_modules = array_keys($use_modules);
			if(!empty($coupon) && ($coupon['starttime'] <= TIMESTAMP  && $coupon['endtime'] >= TIMESTAMP) && in_array($params['module'], $use_modules)) {
				$coupon['fee'] = $record['card_fee'];
								$coupon_record = pdo_get('activity_coupon_record', array('uid' => $_W['member']['uid'], 'uniacid' => $_W['uniacid'], 'couponid' => $coupon_id, 'status' => '1'));
				if(!empty($coupon_record)) {
					$record['is_usecard'] = 1;
					$record['card_type'] = 2;
					if($coupon['type'] == '1') {
						$coupon['fee'] = sprintf("%.2f", ($log['fee'] * $coupon['discount']));
					} elseif($coupon['type'] == '2') {
						if($log['fee'] >= $coupon['condition']) {
														$coupon['fee'] = sprintf("%.2f", ($log['fee'] -  $coupon['discount']));
						}
					}
				}
				
				$record['card_fee'] = $coupon['fee'];
				$record['card_id'] = $coupon_record['recid'];
				$record['encrypt_code'] = '';
			}
		}
	}
	if (empty($log)) {
		message('系统支付错误, 请稍后重试.');
	} else {
		pdo_update('core_paylog', $record, array('plid' => $log['plid']));
		if (!empty($log['uniontid']) && $record['card_fee']) {
			$log['card_fee'] = $record['card_fee'];
			$log['card_id'] = $record['card_id'];
			$log['card_type'] = $record['card_type'];
			$log['is_usecard'] = $record['is_usecard'];
		}
	}
	$ps = array();
	$ps['tid'] = $log['plid'];
	$ps['uniontid'] = $log['uniontid'];
	$ps['user'] = $_W['fans']['from_user'];
	$ps['fee'] = $log['card_fee'];
	$ps['title'] = $params['title'];
	if($type == 'alipay') {
		if(!empty($plid)) {
			pdo_update('core_paylog', array('openid' => $_W['member']['uid']), array('plid' => $plid));
		}
		load()->model('payment');
		load()->func('communication');
		$ret = alipay_build($ps, $setting['payment']['alipay']);
		if($ret['url']) {
			echo '<script type="text/javascript" src="../payment/alipay/ap.js"></script><script type="text/javascript">_AP.pay("'.$ret['url'].'")</script>';
			exit();
		}
	}
	if($type == 'wechat') {
		if(!empty($plid)) {
			$tag = array();
			$tag['acid'] = $_W['acid'];
			$tag['uid'] = $_W['member']['uid'];
			pdo_update('core_paylog', array('openid' => $_W['openid'], 'tag' => iserializer($tag)), array('plid' => $plid));
		}
		$ps['title'] = urlencode($params['title']);
		load()->model('payment');
		load()->func('communication');
		$sl = base64_encode(json_encode($ps));
		$auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
		header("location: ../payment/wechat/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}");
		exit();
	}
	if($type == 'credit') {
		$setting = uni_setting($_W['uniacid'], array('creditbehaviors'));
		$credtis = mc_credit_fetch($_W['member']['uid']);
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
		$pars = array();
		$pars[':plid'] = $ps['tid'];
		$log = pdo_fetch($sql, $pars);
				if(empty($_GPC['notify'])) {
			if(!empty($log) && $log['status'] == '0') {
				if($credtis[$setting['creditbehaviors']['currency']] < $ps['fee']) {
					message("余额不足以支付, 需要 {$ps['fee']}, 当前 {$credtis[$setting['creditbehaviors']['currency']]}");
				}
				$fee = floatval($ps['fee']);
				$result = mc_credit_update($_W['member']['uid'], $setting['creditbehaviors']['currency'], -$fee, array($_W['member']['uid'], '消费' . $setting['creditbehaviors']['currency'] . ':' . $fee));
				if (is_error($result)) {
					message($result['message'], '', 'error');
				}
				if (!empty($_W['openid'])) {
					mc_notice_credit2($_W['openid'], $_W['member']['uid'], $fee, 0, '线上消费');
				}
				$record = array();
				$record['status'] = '1';
				pdo_update('core_paylog', $record, array('plid' => $log['plid']));
								if($log['is_usecard'] == 1 && $log['card_type'] == 1 && !empty($log['encrypt_code']) && $_W['acid']) {
					load()->classs('coupon');
					$acc = new coupon($_W['acid']);
					$codearr['encrypt_code'] = $log['encrypt_code'];
					$codearr['module'] = $log['module'];
					$codearr['card_id'] = $log['card_id'];
					$a = $acc->PayConsumeCode($codearr);
				}
								if($log['is_usecard'] == 1 && $log['card_type'] == 2) {
					$log['card_id'] = intval($log['card_id']);
					pdo_update('activity_coupon_record', array('status' => '2', 'usetime' => time(), 'usemodule' => $log['module']), array('uniacid' => $_W['uniacid'], 'recid' => $log['card_id'], 'status' => '1'));
				}
				$site = WeUtility::createModuleSite($log['module']);
				if(!is_error($site)) {
					$site->weid = $_W['weid'];
					$site->uniacid = $_W['uniacid'];
					$site->inMobile = true;
					$method = 'payResult';
					if (method_exists($site, $method)) {
						$ret = array();
						$ret['result'] = 'success';
						$ret['type'] = $log['type'];
						$ret['from'] = 'return';
						$ret['tid'] = $log['tid'];
						$ret['user'] = $log['openid'];
						$ret['fee'] = $log['fee'];
						$ret['weid'] = $log['weid'];
						$ret['uniacid'] = $log['uniacid'];
						$ret['acid'] = $log['acid'];
												$ret['is_usecard'] = $log['is_usecard'];
						$ret['card_type'] = $log['card_type']; 						$ret['card_fee'] = $log['card_fee'];
						$ret['card_id'] = $log['card_id'];
						echo '<iframe style="display:none;" src="'.murl('mc/cash/credit', array('notify' => 'yes', 'params' => $_GPC['params']), true, true).'"></iframe>';
						$site->$method($ret);
					}
				}
			}
		} else {
			$site = WeUtility::createModuleSite($log['module']);
			if(!is_error($site)) {
				$site->weid = $_W['weid'];
				$site->uniacid = $_W['uniacid'];
				$site->inMobile = true;
				$method = 'payResult';
				if (method_exists($site, $method)) {
					$ret = array();
					$ret['result'] = 'success';
					$ret['type'] = $log['type'];
					$ret['from'] = 'notify';
					$ret['tid'] = $log['tid'];
					$ret['user'] = $log['openid'];
					$ret['fee'] = $log['fee'];
					$ret['weid'] = $log['weid'];
					$ret['uniacid'] = $log['uniacid'];
					$ret['acid'] = $log['acid'];
										$ret['is_usecard'] = $log['is_usecard'];
					$ret['card_type'] = $log['card_type']; 					$ret['card_fee'] = $log['card_fee'];
					$ret['card_id'] = $log['card_id'];
					$site->$method($ret);
				}
			}
		}
	}
	
	if ($type == 'delivery') {
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `plid`=:plid';
		$pars = array();
		$pars[':plid'] = $ps['tid'];
		$log = pdo_fetch($sql, $pars);
		if(!empty($log) && $log['status'] == '0') {
						if($log['is_usecard'] == 1  && $log['card_type'] == 1 && !empty($log['encrypt_code']) && $_W['acid']) {
				load()->classs('coupon');
				$acc = new coupon($_W['acid']);
				$codearr['encrypt_code'] = $log['encrypt_code'];
				$codearr['module'] = $log['module'];
				$codearr['card_id'] = $log['card_id'];
				$acc->PayConsumeCode($codearr);
			}

						if($log['is_usecard'] == 1 && $log['card_type'] == 2) {
				$now = time();
				$log['card_id'] = intval($log['card_id']);
				pdo_query('UPDATE ' . tablename('activity_coupon_record') . " SET status = 2, usetime = {$now}, usemodule = '{$log['module']}' WHERE uniacid = :aid AND couponid = :cid AND uid = :uid AND status = 1 LIMIT 1", array(':aid' => $_W['uniacid'], ':uid' => $log['openid'], ':cid' => $log['card_id']));
			}
			$site = WeUtility::createModuleSite($log['module']);

			if(!is_error($site)) {
				$site->weid = $_W['weid'];
				$site->uniacid = $_W['uniacid'];
				$site->inMobile = true;
				$method = 'payResult';
				if (method_exists($site, $method)) {
					$ret = array();
					$ret['result'] = 'failed';
					$ret['type'] = $log['type'];
					$ret['from'] = 'return';
					$ret['tid'] = $log['tid'];
					$ret['user'] = $log['openid'];
					$ret['fee'] = $log['fee']; 					$ret['weid'] = $log['weid'];
					$ret['uniacid'] = $log['uniacid'];
										$ret['is_usecard'] = $log['is_usecard'];
					$ret['card_type'] = $log['card_type']; 					$ret['card_fee'] = $log['card_fee'];
					$ret['card_id'] = $log['card_id'];
					exit($site->$method($ret));
				}
			}
		}
	}
	if ($type == 'unionpay') {
		$sl = base64_encode(json_encode($ps));
		$auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
		header("location: ../payment/unionpay/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}");
		exit();
	}
	if ($type == 'baifubao') {
		$sl = base64_encode(json_encode($ps));
		$auth = sha1($sl . $_W['uniacid'] . $_W['config']['setting']['authkey']);
		header("location: ../payment/baifubao/pay.php?i={$_W['uniacid']}&auth={$auth}&ps={$sl}");
		exit();
	}
}
