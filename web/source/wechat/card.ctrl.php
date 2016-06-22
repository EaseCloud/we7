<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('wechat_card_list');
$dos = array('module', 'coupon', 'location', 'discount', 'display', 'del', 'sync', 'modifystock', 'toggle', 'selfconsume', 'qr', 'record', 'cash', 'gift', 'groupon', 'general_coupon');
$do = in_array($do, $dos) ? $do : 'display';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'post';
$acid = intval($_W['acid']);
if(!$acid) {
	message('公众号不存在', url('wechat/account'), 'error');
}

if($do == 'location') {
		$location = pdo_fetchall('SELECT id,location_id, business_name, branch_name, address FROM ' . tablename('activity_stores') . " WHERE uniacid = :uniacid AND status = :status AND location_id != ''", array(':uniacid' => $_W['uniacid'], ':status' => 1));
	template('wechat/location_model');
	exit();
}

if(empty($_GPC['__color'])) {
	load()->classs('coupon');
	$acc = new coupon($acid);
	$status = $acc->GetColors();
	if(is_error($status)) {
		message($status['message'], referer(), 'error');
	}
	foreach($status['colors'] as $val) {
		$colors[$val['name']] = $val;
	}
	$colors = base64_encode(iserializer($colors));
	isetcookie('__color', $colors, 86400*7);
}
$colors = iunserializer(base64_decode($_GPC['__color']));

load()->model('coupon');
load()->classs('coupon');

$setting = pdo_fetch('SELECT * FROM  ' . tablename('coupon_setting') . ' WHERE uniacid = :aid AND acid = :cid', array(':aid' => $_W['uniacid'], ':cid' => $acid));
$setting['logourl_'] = media2local($setting['logourl']);

$types = array(
	'discount' => '折扣券',
	'cash' => '代金券',
	'gift' => '礼品券',
	'groupon' => '团购券',
	'general_coupon' => '优惠券',
);

if($do == 'display') {
	$condition = ' WHERE uniacid = :aid AND acid = :cid';
	$parma[':aid'] = $_W['uniacid'];
	$parma[':cid'] = $acid;
	if(!empty($_GPC['type'])) {
		$condition .= ' AND type = :type';
		$parma[':type'] = $_GPC['type'];
	}
	if(!empty($_GPC['title'])) {
		$title = trim($_GPC['title']);
		$condition .= " AND title LIKE '%{$title}%'";
	}
	if(!empty($_GPC['status'])) {
		$status = intval($_GPC['status']);
		$condition .= " AND status = {$status}";
	}
	if($_GPC['is_selfconsume'] == '1') {
		$condition .= " AND is_selfconsume = 1";
	} elseif ($_GPC['is_selfconsume'] == '0') {
		$condition .= " AND is_selfconsume = 0";
	} else {
		$condition = $condition;
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('coupon') . $condition, $parma);
	$data = pdo_fetchall('SELECT * FROM ' . tablename('coupon') . $condition . ' ORDER BY id DESC LIMIT ' .($pindex - 1) * $psize.','.$psize, $parma);
	if(!empty($data)) {
		foreach($data as &$da) {
			$da['date_info'] = @iunserializer($da['date_info']);
			$da['location_id_list'] = @iunserializer($da['location_id_list']);
		}
 	}
	$pager = pagination($total, $pindex, $psize);
	template('wechat/card');
}

if($do == 'sync') {
	$id = intval($_GPC['cid']);
	$card = pdo_fetch('SELECT id,status,card_id,acid FROM ' . tablename('coupon') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $id));
	if(empty($card) || empty($card['card_id'])) {
		message('卡券不存在或卡券id为空', referer(), 'error');
	}
	$coupon = new coupon($acid);
	$card = $coupon->fetchCard($card['card_id']);
	if(is_error($card)) {
		message($card['message'], referer(), 'error');
	}
	$type = strtolower($card['card_type']);
	$coupon_status = coupon_status();
	$status = $coupon_status[$card[$type]['base_info']['status']];
	pdo_update('coupon', array('status' => $status), array('acid' => $acid, 'id' => $id));
	message('更新卡券状态成功', referer(), 'success');
}

if($do == 'del') {
	$id = intval($_GPC['id']);
	$card_id = pdo_fetchcolumn('SELECT card_id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $id));
	$status = coupon_delete($id);
	if(!is_error($status) || $_GPC['force'] == 1) {
		pdo_delete('coupon', array('id' => $id, 'uniacid' => $_W['uniacid']));
				pdo_delete('qrcode', array('acid' => $acid, 'type' => 'card', 'extra' => $id));
				pdo_delete('coupon_record', array('acid' => $acid, 'card_id' => $card_id));
		pdo_delete('coupon_modules', array('acid' => $acid, 'id' => $id));
		message('删除卡券成功', url('wechat/card/display'), 'success');
	} else{
		$url_1 = url('wechat/card/display');
		$url_2 = url('wechat/card/del', array('id' => $id, 'force' => 1));
		$message = "<a href='{$url_1}' class='btn btn-default'>否</a> <a href='{$url_2}' class='btn btn-primary'>是</a> ";
		message($status['message']."<br>是否强制删除本地数据 {$message}", '', 'error');
	}
}

if($do == 'modifystock') {
	$status = coupon_modifystock($_GPC['id'], $_GPC['num']);
	$out['erron'] = 0;
	$out['error'] = '';
	if(!is_error($status)) {
		exit(json_encode($out));
	} else{
		$out['erron'] = 1;
		$out['error'] = $status['message'];
		exit(json_encode($out));
	}
}

if($do == 'qr') {
	if($op == 'post') {
		$cid = intval($_GPC['cid']);
		$id = intval($_GPC['id']);
		$title = pdo_fetchcolumn('SELECT title FROM ' . tablename('coupon') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $cid));
		if(empty($title)) {
			message('卡券不存在或已经删除', url('wechat/card/display'), 'error');
		}
		$row = pdo_fetch('SELECT * FROM ' . tablename('qrcode') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $id));
		if(checksubmit('submit')) {
			$title = !empty($_GPC['name']) ? trim($_GPC['name']) : message('场景名称不能为空');
			$qrctype = intval($_GPC['qrc-model']);
			$data['id'] = $cid;
			if($id > 0) {
				$data['id'] = $cid;
				$data['outer_id'] = $row['qrcid'];
				$data['expire_seconds'] = min(array(intval($_GPC['expire-seconds']), 1800));
				$status = coupon_qr($data);
				if(!is_error($status)) {
					$update['name'] = $title;
					$update['createtime'] = TIMESTAMP;
					$update['expire'] = $status['expire_seconds'];
					$update['ticket'] = $status['ticket'];
					$update['url'] = $status['url'];
					pdo_update('qrcode', $update, array('uniacid' => $_W['uniacid'], 'id' => $id));
					message('更新二维码成功', url('wechat/card/qr', array('op' => 'list','cid' => $cid)), 'success');
				} else {
					message('更新二维码失败,' . $status['message'], referer(), 'error');
				}
			}
			if ($qrctype == 1) {
				$qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename('qrcode')." WHERE acid = :acid AND model = '1' ORDER BY qrcid DESC", array(':acid' => $acid));
				$data['outer_id'] = !empty($qrcid) ? ($qrcid+1) : 100001;
				$data['expire_seconds'] = intval($_GPC['expire-seconds']) ? min(array(intval($_GPC['expire-seconds']), 1800)) : 1800;
				$result = coupon_qr($data);
			} else if ($qrctype == 2) {
				$qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename('qrcode')." WHERE acid = :acid AND model = '2' ORDER BY qrcid DESC", array(':acid' => $acid));
				$data['outer_id'] = !empty($qrcid) ? ($qrcid+1) : 1;
				if ($data['outer_id'] > 100000) {
					message('抱歉，永久二维码已经生成最大数量，请先删除一些。');
				}
				$result = coupon_qr($data);
			} else {
				message('抱歉，此公众号暂不支持您请求的二维码类型！');
			}

			if(!is_error($result)) {
				$insert = array(
					'uniacid' => $_W['uniacid'],
					'acid' => $acid,
					'qrcid' => $data['outer_id'],
					'keyword' => '',
					'name' => $_GPC['name'],
					'model' => $qrctype,
					'ticket' => $result['ticket'],
					'expire' => $result['expire_seconds'],
					'url' => $result['url'],
					'createtime' => TIMESTAMP,
					'status' => '1',
					'type' => 'card',
					'extra' => $cid
				);
				pdo_insert('qrcode', $insert);
				message('恭喜，生成二维码成功！', url('wechat/card/qr', array('op' => 'list', 'cid' => $cid)), 'success');
			} else {
				message("公众平台返回接口错误. <br />错误代码为: {$result['errorcode']} <br />错误信息为: {$result['message']}");
			}
		}
	}

	if($op == 'list') {
		$cid = intval($_GPC['cid']);
		$title = pdo_fetchcolumn('SELECT title FROM ' . tablename('coupon') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $cid));
		$data = pdo_fetchall('SELECT * FROM ' . tablename('qrcode') . ' WHERE uniacid = :aid AND type = :type AND extra = :cid', array(':aid' => $_W['uniacid'], ':type' => 'card', ':cid' => $cid));
				pdo_query("UPDATE ".tablename('qrcode')." SET status = '0' WHERE uniacid = '{$_W['uniacid']}' AND model = '1' AND createtime < '{$_W['timestamp']}' - expire");
	}

	if($op == 'extend') {
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$qrcrow = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE uniacid = {$_W['uniacid']} AND id = '{$id}'");
			$update = array();
			if($qrcrow['model'] == 1) {
				$data['expire_seconds'] = 1800;
				$data['id'] = $qrcrow['extra'];
				$data['outer_id'] = $qrcrow['qrcid'];
				$result = coupon_qr($data);
				if(is_error($result)) {
					message($result['message'], '', 'error');
				}
				$update['ticket'] = $result['ticket'];
				$update['expire'] = $result['expire_seconds'];
				$update['url'] = $result['url'];
				$update['createtime'] = TIMESTAMP;
				pdo_update('qrcode', $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
			}
			message('恭喜，延长临时二维码时间成功！', referer(), 'success');
		}
	}

	if($op == 'del') {
		$cid = intval($_GPC['cid']);
		if ($_GPC['scgq']) {
			$list = pdo_fetchall("SELECT id FROM ".tablename('qrcode')." WHERE uniacid = '{$_W['uniacid']}' AND extra = {$cid} AND status = '0' AND type='card'", array(), 'id');
			if (!empty($list)) {
				pdo_query("DELETE FROM ".tablename('qrcode')." WHERE id IN (".implode(',', array_keys($list)).")");
							}
			message('执行成功<br />删除二维码：'.count($list), url('wechat/card/qr', array('op' => 'list', 'cid' => $cid)),'success');
		}else{
			$id = $_GPC['id'];
			pdo_delete('qrcode', array('id' =>$id, 'extra' => $cid, 'uniacid' => $_W['uniacid']));
						message('删除成功', url('wechat/card/qr', array('op' => 'list', 'cid' => $cid)), 'success');
		}
	}
	template('wechat/qrcode');
}

if($do == 'record') {
	load()->model('mc');
	if($op == 'list') {
		$condition = ' WHERE acid = :acid';
		$parma[':acid'] = $acid;
		$cid = intval($_GPC['cid']);
		$card_id = trim($_GPC['card_id']);
		if($cid > 0) {
			$coupon = pdo_fetch('SELECT title,card_id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $cid));
			$card_id = $coupon['card_id'];
		} else {
			$coupon = pdo_fetch('SELECT title FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $acid, ':card_id' => $card_id));
		}
		if(!empty($card_id)) {
			$condition .= ' AND card_id = :card_id';
			$parma[':card_id'] = $card_id;
		}

		$code = trim($_GPC['code']);
		if(!empty($code)) {
			$condition .= " AND code LIKE '%{$code}%'";
		}
		$status = intval($_GPC['status']);
		if($status > 0) {
			$condition .= " AND status = :status";
			$parma[':status'] = $status;
		}
		$outer_id = intval($_GPC['outer_id']);
		if(!empty($outer_id)) {
			$condition .= " AND outer_id = :oid";
			$parma[':oid'] = $outer_id;
		}
		$nickname = trim($_GPC['nickname']);
		if(!empty($nickname)) {
			$condition .= " AND openid IN (SELECT openid FROM " . tablename('mc_mapping_fans') ." WHERE acid = {$acid} AND nickname LIKE '%{$nickname}%')";
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('coupon_record') . $condition, $parma);
		$data = pdo_fetchall('SELECT * FROM ' . tablename('coupon_record') . $condition . ' ORDER BY id DESC LIMIT ' .($pindex - 1) * $psize.','.$psize, $parma);
		if(!empty($data)) {
			foreach($data as &$da) {
				if(!empty($da['openid'])) {
					$openids[] = $da['openid'];
				}
				if(!empty($da['friend_openid'])) {
					$openids[] = $da['friend_openid'];
				}
				if($da['outer_id'] > 0) {
					$outer_ids[] = $da['outer_id'];
				}
			}

			if(!empty($openids)) {
				$openids_str = "'" . implode("','", $openids) . "'";
				$nicknames = pdo_fetchall('SELECT nickname,openid FROM ' . tablename('mc_mapping_fans') . "WHERE acid = {$acid} AND openid IN ({$openids_str})", array(), 'openid');
			}
			if(!empty($outer_ids)) {
				$outer_str = implode(',', $outer_ids);
				$outers = pdo_fetchall('SELECT name,qrcid FROM ' . tablename('qrcode') . "WHERE acid = {$acid} AND type = 'card' AND qrcid IN ({$outer_str})", array(), 'qrcid');
			}
			$operator = mc_account_change_operator($da['clerk_type'], $da['store_id'], $da['clerk_id']);
			$da['clerk_cn'] = $operator['clerk_cn'];
			$da['store_cn'] = $operator['store_cn'];
		}
		$pager = pagination($total, $pindex, $psize);
	}

	if($op == 'unavailable') {
		$id = intval($_GPC['id']);
		$del = intval($_GPC['del']);
		$record = pdo_fetch('SELECT code,status FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $id));
		if(empty($record)) {
			message('对应code码不存在', referer(), 'error');
		}
		if($record['status'] == 1) {
			$acc = new coupon($acid);
			$status = $acc->UnavailableCode(array('code' => $record['code']));
			if(is_error($status)) {
				message($status['message'], '', 'error');
			} else {
				pdo_update('coupon_record', array('status' => 2, 'clerk_name' => $_W['user']['name'], 'clerk_id' => $_W['user']['clerk_id'], 'store_id' => $_W['user']['store_id'], 'clerk_type' => $_W['user']['clerk_type'], 'usetime' => TIMESTAMP), array('acid' => $acid, 'code' => $record['code'], 'id' => $id));
			}
		}
		if($del == 1) {
			pdo_delete('coupon_record', array('acid' => $acid, 'id' => $id));
			message('删除卡券领取状态成功', referer(), 'success');
		}
		message('更改卡券领取状态成功', referer(), 'success');
	}

	if($op == 'consume') {
		$id = intval($_GPC['id']);
		$record = pdo_fetch('SELECT code,status FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $id));
		if(empty($record)) {
			message('对应code码不存在', referer(), 'error');
		}

		if($record['status'] == 1) {
			$acc = new coupon($acid);
			$status = $acc->ConsumeCode(array('code' => $record['code']));
			if(is_error($status)) {
				message($status['message'], '', 'error');
			} else {
				pdo_update('coupon_record', array('status' => 3, 'clerk_name' => $_W['user']['name'], 'clerk_id' => $_W['user']['clerk_id'], 'store_id' => $_W['user']['store_id'], 'clerk_type' => $_W['user']['clerk_type'], 'usetime' => TIMESTAMP), array('acid' => $acid, 'code' => $record['code'], 'id' => $id));
			}
		}
		message('核销卡券成功', referer(), 'success');
	}
	template('wechat/record');
}

if($do == 'toggle') {
	$id = intval($_GPC['id']);
	if($op == 'is_display') {
		$display = pdo_fetchcolumn('SELECT is_display FROM ' . tablename('coupon') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $id));
		if($display == 1) {
			pdo_update('coupon', array('is_display' => 0), array('acid' => $acid, 'id' => $id));
		} else {
			pdo_update('coupon', array('is_display' => 1), array('acid' => $acid, 'id' => $id));
		}
		exit('success');
	}
}

if($do == 'selfconsume') {
	$id = intval($_GPC['id']);
	load()->classs('coupon');
	$coupon = new coupon($acid);
	$card_info = pdo_get('coupon', array('acid' => $acid,'id' => $id),array('is_selfconsume','card_id','location_id_list'));
	$is_selfconsume = $card_info['is_selfconsume'];
	$card_id = $card_info['card_id'];
	$location_id_list = iunserializer($card_info['location_id_list']);
	if(empty($location_id_list)) {
		exit('该卡券未设置适用门店,无法设置自助核销');
	} else {
		$is_open = ($is_selfconsume == 1) ? false : true;
		$selfconsume_value = ($is_selfconsume == 1) ? 0 : 1;
		$data = array(
			'card_id' => $card_id,
			'is_open' => $is_open
		);
		$result = $coupon->selfConsume($data);
		if(!is_error($result)) {
			pdo_update('coupon', array('is_selfconsume' => $selfconsume_value), array('acid' => $acid, 'id' => $id));
		}
		exit('success'); 
	}
}

if($do == 'discount') {
	if($op == 'post') {
		unset($url_name_type['URL_NAME_TYPE_VIP_SERVICE']);
		$id = intval($_GPC['id']);
		if($id > 0) {
			$item = coupon_fetch($id);
			if(is_error($item)) {
				message($item['message'], '', 'error');
			}
		}
		template('wechat/coupon-post');
	} elseif($op == 'post_save') {
		$post = array();
		foreach($_GPC['data'] as $da) {
			$post[$da['name']] = trim($da['value']);
		}
		$out['errno'] = 1;
		$out['error'] = '';

		$post['logo_url'] = empty($post['logo_url']) ? $setting['logourl'] : trim($post['logo_url']);
		$base = new Card('DISCOUNT', $post);
		if(is_error($base->discount->base_info)) {
			$out['errno'] = 0;
			$out['error'] = $base->discount->base_info['message'];
			exit(json_encode($out));
		}
		$base->get_card()->set_discount(100 - intval($post['discount']));
		$acc = new coupon($acid);
		$status = $acc->CreateCard($base->toJson());
		if(is_error($status)) {
			$out['errno'] = 0;
			$out['error'] = $status['message'];
			exit(json_encode($out));
		}
		$post['card_id'] = $status['card_id'];
				$post['date_info'] = array(
			'time_type' => $post['time_type'],
			'time_limit_start' =>  $post['time_limit[start]'],
			'time_limit_end' =>  $post['time_limit[end]'],
			'deadline' =>  $post['deadline'],
			'limit' =>  $post['limit'],
		);
		$post['date_info'] = iserializer($post['date_info']);
		if(!empty($post['location-select'])) {
			$location = explode('-', $post['location-select']);
			$post['location_id_list'] = iserializer($location);
		} else {
			$post['location_id_list'] = iserializer(array());
		}
		$post['uniacid'] = $_W['uniacid'];
		$post['acid'] = $acid;
		$post['type'] = 'discount';
		$post['extra'] = intval($post['discount']);
		$post['is_display'] = intval($post['is_display']);
		empty($post['code_type']) && $post['code_type'] = 1;
		$post['status'] = 1;
		$module = trim($post['module-select']);
		unset($post['module-select'],$post['discount'],$post['time_type'],$post['limit'],$post['deadline'], $post['time_limit[start]'],$post['time_limit[end]'],$post['color-value'], $post['token'], $post['is_location'], $post['location-select']);
		$is_ok = pdo_fetchcolumn('SELECT id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $acid, ':card_id' => $post['card_id']));
		if(empty($is_ok)) {
			pdo_insert('coupon', $post);
			$cid = pdo_insertid();
		} else {
			$cid = $is_ok;
			unset($post['status']);
			pdo_update('coupon', $post, array('acid' => $acid, 'id' => $is_ok));
		}
		if(!empty($module)) {
			$arr = explode('@', $module);
			foreach($arr as $li) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'acid' => $acid,
					'card_id' =>$post['card_id'],
					'cid' => $cid,
					'module' => $li
				);
				pdo_insert('coupon_modules', $data);
			}
		}
		exit(json_encode($out));
	}
}

if($do == 'cash') {
	if($op == 'post') {
		unset($url_name_type['URL_NAME_TYPE_VIP_SERVICE']);
		$id = intval($_GPC['id']);
		if($id > 0) {
			$item = coupon_fetch($id);
			if(is_error($item)) {
				message($item['message'], '', 'error');
			}
		}
		template('wechat/cash-post');
	} elseif($op == 'post_save') {
		$post = array();
		foreach($_GPC['data'] as $da) {
			$post[$da['name']] = trim($da['value']);
		}
		$out['errno'] = 1;
		$out['error'] = '';

		$post['logo_url'] = empty($post['logo_url']) ? $setting['logourl'] : trim($post['logo_url']);
		$base = new Card('CASH', $post);

		if(is_error($base->cash->base_info)) {
			$out['errno'] = 0;
			$out['error'] = $base->cash->base_info['message'];
			exit(json_encode($out));
		}
		$base->get_card()->set_least_cost($post['least_cost'] * 100);
		$base->get_card()->set_reduce_cost($post['reduce_cost'] * 100);
		$acc = new coupon($acid);
		$status = $acc->CreateCard($base->toJson());
		if(is_error($status)) {
			$out['errno'] = 0;
			$out['error'] = $status['message'];
			exit(json_encode($out));
		}
		$post['card_id'] = $status['card_id'];

				$post['date_info'] = array(
			'time_type' => $post['time_type'],
			'time_limit_start' =>  $post['time_limit[start]'],
			'time_limit_end' =>  $post['time_limit[end]'],
			'deadline' =>  $post['deadline'],
			'limit' =>  $post['limit'],
		);
		$post['date_info'] = iserializer($post['date_info']);
		if(!empty($post['location-select'])) {
			$location = explode('-', $post['location-select']);
			$post['location_id_list'] = iserializer($location);
		} else {
			$post['location_id_list'] = iserializer(array());
		}
		$post['uniacid'] = $_W['uniacid'];
		$post['acid'] = $acid;
		$post['type'] = 'cash';
		$post['extra'] = iserializer(array('least_cost' => $post['least_cost'], 'reduce_cost' => $post['reduce_cost'],));
		$post['is_display'] = intval($post['is_display']);
		empty($post['code_type']) && $post['code_type'] = 1;
		$post['status'] = 1;
		$module = trim($post['module-select']);
		unset($post['module-select'],$post['least_cost'],$post['reduce_cost'],$post['time_type'],$post['limit'],$post['deadline'], $post['time_limit[start]'],$post['time_limit[end]'],$post['color-value'], $post['token'], $post['is_location'], $post['location-select']);
		$is_ok = pdo_fetchcolumn('SELECT id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $acid, ':card_id' => $post['card_id']));
		if(empty($is_ok)) {
			pdo_insert('coupon', $post);
			$cid = pdo_insertid();
		} else {
			$cid = $is_ok;
			unset($post['status']);
			pdo_update('coupon', $post, array('acid' => $acid, 'id' => $is_ok));
		}
		if(!empty($module)) {
			$arr = explode('@', $module);
			foreach($arr as $li) {
				$data = array(
					'uniacid' => $_W['uniacid'],
					'acid' => $acid,
					'card_id' =>$post['card_id'],
					'cid' => $cid,
					'module' => $li
				);
				pdo_insert('coupon_modules', $data);
			}
		}
		exit(json_encode($out));
	}
}

if($do == 'gift') {
	if($op == 'post') {
		unset($url_name_type['URL_NAME_TYPE_VIP_SERVICE']);
		$id = intval($_GPC['id']);
		if($id > 0) {
			$item = coupon_fetch($id);
			if(is_error($item)) {
				message($item['message'], '', 'error');
			}
		}
		template('wechat/gift-post');
	} elseif($op == 'post_save') {
		$post = array();
		foreach($_GPC['data'] as $da) {
			$post[$da['name']] = trim($da['value']);
		}
		$out['errno'] = 1;
		$out['error'] = '';

		$post['logo_url'] = empty($post['logo_url']) ? $setting['logourl'] : trim($post['logo_url']);
		$base = new Card('GIFT', $post);

		if(is_error($base->gift->base_info)) {
			$out['errno'] = 0;
			$out['error'] = $base->gift->base_info['message'];
			exit(json_encode($out));
		}

		$base->get_card()->set_gift(urlencode(trim($post['gift'])));
		$acc = new coupon($acid);
		$status = $acc->CreateCard($base->toJson());
		if(is_error($status)) {
			$out['errno'] = 0;
			$out['error'] = $status['message'];
			exit(json_encode($out));
		}
		$post['card_id'] = $status['card_id'];
				$post['date_info'] = array(
			'time_type' => $post['time_type'],
			'time_limit_start' =>  $post['time_limit[start]'],
			'time_limit_end' =>  $post['time_limit[end]'],
			'deadline' =>  $post['deadline'],
			'limit' =>  $post['limit'],
		);
		$post['date_info'] = iserializer($post['date_info']);
		if(!empty($post['location-select'])) {
			$location = explode('-', $post['location-select']);
			$post['location_id_list'] = iserializer($location);
		} else {
			$post['location_id_list'] = iserializer(array());
		}
		$post['uniacid'] = $_W['uniacid'];
		$post['acid'] = $acid;
		$post['type'] = 'gift';
		$post['extra'] = trim($post['gift']);
		$post['is_display'] = intval($post['is_display']);
		empty($post['code_type']) && $post['code_type'] = 1;
		$post['status'] = 1;
		unset($post['gift'],$post['time_type'],$post['limit'],$post['deadline'], $post['time_limit[start]'],$post['time_limit[end]'],$post['color-value'], $post['token'], $post['is_location'], $post['location-select']);
		$is_ok = pdo_fetchcolumn('SELECT id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $acid, ':card_id' => $post['card_id']));
		if(empty($is_ok)) {
			pdo_insert('coupon', $post);
		} else {
			unset($post['status']);
			pdo_update('coupon', $post, array('acid' => $acid, 'id' => $is_ok));
		}
		exit(json_encode($out));
	}
}

if($do == 'groupon') {
	if($op == 'post') {
		unset($url_name_type['URL_NAME_TYPE_VIP_SERVICE']);
		$id = intval($_GPC['id']);
		if($id > 0) {
			$item = coupon_fetch($id);
			if(is_error($item)) {
				message($item['message'], '', 'error');
			}
		}
		template('wechat/groupon-post');
	} elseif($op == 'post_save') {
		$post = array();
		foreach($_GPC['data'] as $da) {
			$post[$da['name']] = trim($da['value']);
		}
		$out['errno'] = 1;
		$out['error'] = '';

		$post['logo_url'] = empty($post['logo_url']) ? $setting['logourl'] : trim($post['logo_url']);
		$base = new Card('GROUPON', $post);
		if(is_error($base->groupon->base_info)) {
			$out['errno'] = 0;
			$out['error'] = $base->groupon->base_info['message'];
			exit(json_encode($out));
		}

		$base->get_card()->set_deal_detail(urlencode(trim($post['deal_detail'])));
		$acc = new coupon($acid);
		$status = $acc->CreateCard($base->toJson());
		if(is_error($status)) {
			$out['errno'] = 0;
			$out['error'] = $status['message'];
			exit(json_encode($out));
		}
		$post['card_id'] = $status['card_id'];
				$post['date_info'] = array(
			'time_type' => $post['time_type'],
			'time_limit_start' =>  $post['time_limit[start]'],
			'time_limit_end' =>  $post['time_limit[end]'],
			'deadline' =>  $post['deadline'],
			'limit' =>  $post['limit'],
		);
		$post['date_info'] = iserializer($post['date_info']);
		if(!empty($post['location-select'])) {
			$location = explode('-', $post['location-select']);
			$post['location_id_list'] = iserializer($location);
		} else {
			$post['location_id_list'] = iserializer(array());
		}
		$post['uniacid'] = $_W['uniacid'];
		$post['acid'] = $acid;
		$post['type'] = 'groupon';
		$post['extra'] = trim($post['deal_detail']);
		$post['is_display'] = intval($post['is_display']);
		empty($post['code_type']) && $post['code_type'] = 1;
		$post['status'] = 1;
		unset($post['deal_detail'],$post['time_type'],$post['limit'],$post['deadline'], $post['time_limit[start]'],$post['time_limit[end]'],$post['color-value'], $post['token'], $post['is_location'], $post['location-select']);
		$is_ok = pdo_fetchcolumn('SELECT id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $acid, ':card_id' => $post['card_id']));
		if(empty($is_ok)) {
			pdo_insert('coupon', $post);
		} else {
			unset($post['status']);
			pdo_update('coupon', $post, array('acid' => $acid, 'id' => $is_ok));
		}
		exit(json_encode($out));
	}
}

if($do == 'general_coupon') {
	if($op == 'post') {
		unset($url_name_type['URL_NAME_TYPE_VIP_SERVICE']);
		$id = intval($_GPC['id']);
		if($id > 0) {
			$item = coupon_fetch($id);
			if(is_error($item)) {
				message($item['message'], '', 'error');
			}
		}
		template('wechat/general_coupon-post');
	} elseif($op == 'post_save') {
		$post = array();
		foreach($_GPC['data'] as $da) {
			$post[$da['name']] = trim($da['value']);
		}
		$out['errno'] = 1;
		$out['error'] = '';

		$post['logo_url'] = empty($post['logo_url']) ? $setting['logourl'] : trim($post['logo_url']);
		$base = new Card('GENERAL_COUPON', $post);
		if(is_error($base->general_coupon->base_info)) {
			$out['errno'] = 0;
			$out['error'] = $base->general_coupon->base_info['message'];
			exit(json_encode($out));
		}

		$base->get_card()->set_default_detail(urlencode(trim($post['default_detail'])));
		$acc = new coupon($acid);
		$status = $acc->CreateCard($base->toJson());
		if(is_error($status)) {
			$out['errno'] = 0;
			$out['error'] = $status['message'];
			exit(json_encode($out));
		}
		$post['card_id'] = $status['card_id'];
				$post['date_info'] = array(
			'time_type' => $post['time_type'],
			'time_limit_start' =>  $post['time_limit[start]'],
			'time_limit_end' =>  $post['time_limit[end]'],
			'deadline' =>  $post['deadline'],
			'limit' =>  $post['limit'],
		);
		$post['date_info'] = iserializer($post['date_info']);
		if(!empty($post['location-select'])) {
			$location = explode('-', $post['location-select']);
			$post['location_id_list'] = iserializer($location);
		} else {
			$post['location_id_list'] = iserializer(array());
		}
		$post['uniacid'] = $_W['uniacid'];
		$post['acid'] = $acid;
		$post['type'] = 'general_coupon';
		$post['extra'] = $post['default_detail'];
		$post['is_display'] = intval($post['is_display']);
		empty($post['code_type']) && $post['code_type'] = 1;
		$post['status'] = 1;
		unset($post['default_detail'],$post['time_type'],$post['limit'],$post['deadline'], $post['time_limit[start]'],$post['time_limit[end]'],$post['color-value'], $post['token'], $post['is_location'], $post['location-select']);
		$is_ok = pdo_fetchcolumn('SELECT id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND card_id = :card_id', array(':acid' => $acid, ':card_id' => $post['card_id']));
		if(empty($is_ok)) {
			pdo_insert('coupon', $post);
		} else {
			unset($post['status']);
			pdo_update('coupon', $post, array('acid' => $acid, 'id' => $is_ok));
		}
		exit(json_encode($out));
	}
}

if($do == 'module') {
	$module = uni_modules();
		if(!empty($module)) {
		$new = array();
		$filter = array('system', 'activity');
		foreach($module as $mou) {
			if(in_array($mou['type'], $filter)) continue;
			$new[] = $mou;
		}
	}
	unset($module);
	template('wechat/module_model');
	die;
}