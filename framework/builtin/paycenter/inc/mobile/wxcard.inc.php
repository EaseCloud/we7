<?php

defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'consume';

if($op == 'consume') {
	$acid = $_W['acid'];
	if($_W['isajax']) {
		$code = trim($_GPC['code']);
		$record = pdo_get('coupon_record',array('acid' => $acid,'code' => $code),array('code','status','id'));
		if(empty($record)) {
			message(error('-1', '卡券记录不存在'), '', 'ajax');
		}
		if($record['status'] == 1) {
			load()->classs('coupon');
			$acc = new coupon($acid);
			$status = $acc->ConsumeCode(array('code' => $record['code']));
			if(is_error($status)) {
 				message(error('-1', $status['message']),'' , 'ajax');
			} else {
				pdo_update('coupon_record', array('status' => 3, 'clerk_name' => $_W['user']['name'], 'clerk_id' => $_W['user']['clerk_id'], 'store_id' => $_W['user']['store_id'], 'clerk_type' => $_W['user']['clerk_type'], 'usetime' => TIMESTAMP), array('acid' => $acid, 'code' => $code, 'id' => $record['id']));
				message(error('0', ''),'', 'ajax');
			}
		} else {
			message(error('-1', '卡券已核销或已失效'), '', 'ajax');
		}
	}
}
include $this->template('wxcard');