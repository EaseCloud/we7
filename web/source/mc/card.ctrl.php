<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$_W['page']['title'] = '会员卡管理 - 会员中心';

$dos = array('display', 'manage', 'delete', 'coupon', 'submit', 'modal', 'record', 'notice', 'credit', 'recommend', 'editor', 'sign','ajax');
$do = in_array($do, $dos) ? $do : 'other';
load()->model('mc');
$setting = pdo_fetch("SELECT * FROM ".tablename('mc_card')." WHERE uniacid = '{$_W['uniacid']}'");
if($do == 'ajax') {
	$op = trim($_GPC['op']);
	$sql = 'SELECT `uniacid` FROM ' . tablename('mc_card') . " WHERE `uniacid` = :uniacid";
	$setting = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
	if($op == 'status') {
		if(empty($setting)) {
			$open = array(
				'uniacid' => $_W['uniacid'],
				'title' => '我的会员卡',
				'format_type' => 1,
				'fields' => iserializer(array(
					array('title' => '姓名', 'require' => 1, 'bind' => 'realname'),
					array('title' => '手机', 'require' => 1, 'bind' => 'mobile'),
				)),
				'status' => 1,
			);
			pdo_insert('mc_card', $open);
		}
		if (false === pdo_update('mc_card', array('status' => intval($_GPC['status'])), array('uniacid' => $_W['uniacid']))) {
			exit('error');
		}
	} elseif($op == 'other') {
		if(empty($setting)) {
			exit('还没有开启会员卡,请先开启会员卡');
		}
		$field = trim($_GPC['field']);
		if(!in_array($field, array('recommend_status', 'sign_status'))) {
			exit('非法操作');
		}
		pdo_update('mc_card', array($field => intval($_GPC['status'])), array('uniacid' => $_W['uniacid']));
	}
	exit('success');
}

if($do == 'editor') {
	uni_user_permission_check('mc_card_editor');
	if (!empty($_GPC['wapeditor'])) {
		$params = $_GPC['wapeditor']['params'];
		if (empty($params)) {
			message('请您先设计手机端页面.', '', 'error');
		}
		$params = json_decode(ihtml_entity_decode($params), true);
		if (empty($params)) {
			message('请您先设计手机端页面.', '', 'error');
		}
		$html = htmlspecialchars_decode($_GPC['wapeditor']['html'], ENT_QUOTES);
		$html = str_replace(array("{\$_W['uniacid']}", "{\$_W['acid']}"), array($_W['uniacid'], $_W['acid']), $html);

		$basic = $params[0]['params'];
		$activity = $params[1]['params'];
		$nums = $params[2]['params'];
		$times = $params[3]['params'];
		$title = trim($basic['title']) ? trim($basic['title']) : message('名称不能为空');
		$format_type = 1;
		$format = trim($basic['format']);
		if(!empty($basic['fields'])) {
			foreach($basic['fields'] as $field) {
				if(!empty($field['title']) && !empty($field['bind'])) {
					$fields[] = $field;
				}
			}
		}

		if($basic['background']['type'] == 'system') {
			$image = pathinfo($basic['background']['image']);
			$basic['background']['image'] = $image['filename'];
		}
		$update = array(
			'title' => $title,
			'format_type' => $basic['format_type'],
			'format' => $format,
			'color' => iserializer($basic['color']),
			'background' => iserializer(array(
				'background' => $basic['background']['type'],
				'image' => $basic['background']['image'],
			)),
			'logo' => $basic['logo'],
			'description' => trim($basic['description']),
			'grant_rate' => intval($basic['grant_rate']),
			'offset_rate' => intval($basic['offset_rate']),
			'offset_max' => intval($basic['offset_max']),
			'fields' => iserializer($fields),
			'grant' => iserializer(
				array(
					'credit1' => intval($basic['grant']['credit1']),
					'credit2' => intval($basic['grant']['credit2']),
					'coupon' => intval($basic['grant']['coupon']),
				)
			),
			'discount_type' => intval($activity['discount_type']),
			'nums_status' => intval($nums['nums_status']),
			'nums_text' => trim($nums['nums_text']),
			'times_status' => intval($times['times_status']),
			'times_text' => trim($times['times_text']),
			'params' => json_encode($params),
			'html' => $html
		);

		if($update['discount_type'] != 0 && !empty($activity['discounts'])) {
			$update['discount'] = array();
			foreach($activity['discounts'] as $discount) {
				$groupid = intval($discount['groupid']);
				if($groupid <= 0) continue;
				$update['discount'][$groupid] = array(
					'condition_1' => trim($discount['condition_1']),
					'discount_1' => trim($discount['discount_1']),
					'condition_2' => trim($discount['condition_2']),
					'discount_2' => trim($discount['discount_2']),
				);
			}
			$update['discount'] = iserializer($update['discount']);
		}

		if($update['nums_status'] != 0 && !empty($nums['nums'])) {
			$update['nums'] = array();
			foreach($nums['nums'] as $row) {
				$num = floatval($row['num']);
				$recharge = intval($row['recharge']);
				if($num <= 0 || $recharge <= 0) continue;
				$update['nums'][$recharge] = array(
					'recharge' => $recharge,
					'num' => $num
				);
			}
			$update['nums'] = iserializer($update['nums']);
		}
		if($update['times_status'] != 0 && !empty($times['times'])) {
			$update['times'] = array();
			foreach($times['times'] as $row) {
				$time = intval($row['time']);
				$recharge = intval($row['recharge']);
				if($time <= 0 || $recharge <= 0) continue;
				$update['times'][$recharge] = array(
					'recharge' => $recharge,
					'time' => $time
				);
			}
			$update['times'] = iserializer($update['times']);
		}

		if (!empty($setting)) {
			pdo_update('mc_card', $update, array('uniacid' => $_W['uniacid']));
		} else {
			$update['status'] = '1';
			$update['uniacid'] = $_W['uniacid'];
			pdo_insert('mc_card', $update);
		}
		message('会员卡设置成功！', url('mc/card/editor'), 'success');
	}
	$unisetting = uni_setting_load('creditnames');
	$fields_temp = mc_acccount_fields();
	$fields = array();
	foreach($fields_temp as $key => $val) {
		$fields[] = array(
			'title' => $val,
			'bind' => $key
		);
	}

	$params = json_decode($setting['params'], true);
	$discounts_params = $params[1]['params']['discounts'];
	$discounts_temp = array();
	if(!empty($discounts_params)) {
		foreach($discounts_params as $row) {
			$discounts_temp[$row['groupid']] = $row;
		}
	}

	$discounts = array();
	foreach($_W['account']['groups'] as $group) {
		$discounts[$group['groupid']] = array(
			'groupid' => $group['groupid'],
			'title' => $group['title'],
			'credit' => $group['credit'],
			'condition_1' => $discounts_temp[$group['groupid']]['condition_1'],
			'discount_1' => $discounts_temp[$group['groupid']]['discount_1'],
			'condition_2' => $discounts_temp[$group['groupid']]['condition_2'],
			'discount_2' => $discounts_temp[$group['groupid']]['discount_2'],
		);
	}
	template('mc/card-editor');
	exit();
}

if ($do == 'manage') {
	uni_user_permission_check('mc_card_manage');
	if ($_W['ispost']) {
		$status = array('status' => intval($_GPC['status']));
		if (false === pdo_update('mc_card_members', $status, array('uniacid' => $_W['uniacid'], 'id' => $_GPC['cardid']))) {
			exit('error');
		}
		exit('success');
	}
	if ($setting['status'] == 0) {
		message('会员卡功能未开启', url('mc/card/editor'), 'error');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;

	$param = array(':uniacid' => $_W['uniacid']);
	$cardsn = trim($_GPC['cardsn']);
	if(!empty($cardsn)) {
		$where .= ' AND a.cardsn LIKE :cardsn';
		$param[':cardsn'] = "%{$cardsn}%";
	}
	$status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
	if ($status >= 0) {
		$where .= " AND a.status = :status";
		$param[':status'] = $status;
	}
	$num = isset($_GPC['num']) ? intval($_GPC['num']) : -1;
	if($num >= 0) {
		if(!$num) {
			$where .= " AND a.nums = 0";
		} else {
			$where .= " AND a.nums > 0";
		}
	}
	$endtime = isset($_GPC['endtime']) ? intval($_GPC['endtime']) : -1;
	if($endtime >= 0) {
		$where .= " AND a.endtime <= :endtime";
		$param[':endtime'] = strtotime($endtime . 'days');
	}

	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$where .= " AND (b.mobile LIKE '%{$keyword}%' OR b.realname LIKE '%{$keyword}%')";
	}
	$sql = 'SELECT a.*, b.realname, b.groupid, b.credit1, b.credit2, b.mobile FROM ' . tablename('mc_card_members') . " AS a LEFT JOIN " . tablename('mc_members') . " AS b ON a.uid = b.uid WHERE a.uniacid = :uniacid $where ORDER BY a.id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
	$list = pdo_fetchall($sql, $param);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_members') . " AS a LEFT JOIN " . tablename('mc_members') . " AS b ON a.uid = b.uid WHERE a.uniacid = :uniacid $where", $param);
	$pager = pagination($total, $pindex, $psize);
	template('mc/card');
}

if ($do == 'delete') {
	$cardid = intval($_GPC['cardid']);
	if (pdo_delete('mc_card_members',array('id' =>$cardid))) {
		message('删除会员卡成功',url('mc/card/manage'),'success');
	} else {
		message('删除会员卡失败',url('mc/card/manage'),'error');
	}
}

if($do == 'coupon') {
	$title = trim($_GPC['keyword']);
	$condition = ' WHERE uniacid = :uniacid AND (amount-dosage>0) AND starttime <= :time AND endtime >= :time';
	$param = array(
		':uniacid' => $_W['uniacid'],
		':time' => TIMESTAMP,
	);
	$data = pdo_fetchall('SELECT * FROM ' . tablename('activity_coupon') . $condition, $param);
	if(empty($data)) {
		exit('empty');
	}
	template('mc/coupon-model');
	exit();
}

if($do == 'modal') {
	$uid = intval($_GPC['uid']);
	$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
	$card = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	if(empty($card)) {
		exit('error');
	}
	template('mc/card-model');
	exit();
}

if($do == 'submit') {
	load()->model('mc');
	$uid = intval($_GPC['uid']);
	$setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
	$card = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	if(empty($card)) {
		message('用户会员卡信息不存在', referer(), 'error');
	}
	$type = trim($_GPC['type']);
	if($type == 'nums_plus') {
		$fee = floatval($_GPC['fee']);
		$tag = intval($_GPC['nums']);
		if(!$fee && !$tag) {
			message('请完善充值金额和充值次数', referer(), 'error');
		}
		$total_num = $card['nums'] + $tag;
		pdo_update('mc_card_members', array('nums' => $total_num), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'nums',
			'model' => 1,
			'fee' => $fee,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "充值{$fee}元，管理员手动添加{$tag}次，添加后总次数为{$total_num}次",
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('mc_card_record', $log);
		mc_notice_nums_plus($card['openid'], $setting['nums_text'], $tag, $total_num);
	}

	if($type == 'nums_times') {
		$tag = intval($_GPC['nums']);
		if(!$tag) {
			message('请填写消费次数', referer(), 'error');
		}
		if($card['nums'] < $tag) {
			message('当前用户的消费次数不够', referer(), 'error');
		}
		$total_num = $card['nums'] - $tag;
		pdo_update('mc_card_members', array('nums' => $total_num), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'nums',
			'model' => 2,
			'fee' => 0,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "消费1次，管理员手动减1次，消费后总次数为{$total_num}次",
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('mc_card_record', $log);
		mc_notice_nums_times($card['openid'], $card['cardsn'], $setting['nums_text'], $total_num);
	}

	if($type == 'times_plus') {
		$fee = floatval($_GPC['fee']);
		$endtime = strtotime($_GPC['endtime']);
		$days = intval($_GPC['days']);
		if($endtime <= $card['endtime'] && !$days) {
			message('服务到期时间不能小于会员当前的服务到期时间或未填写延长服务天数', '', 'error');
		}
		$tag = floor(($endtime - $card['endtime']) / 86400);
		if($days > 0) {
			$tag = $days;
			if($card['endtime'] > TIMESTAMP) {
				$endtime = $card['endtime'] + $days * 86400;
			} else {
				$endtime = strtotime($days . 'days');
			}
		}
		pdo_update('mc_card_members', array('endtime' => $endtime), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$endtime = date('Y-m-d', $endtime);
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'times',
			'model' => 1,
			'fee' => $fee,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "充值{$fee}元，管理员手动设置{$setting['times_text']}到期时间为{$endtime},设置之前的{$setting['times_text']}到期时间为".date('Y-m-d', $card['endtime']),
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('mc_card_record', $log);
		mc_notice_times_plus($card['openid'], $card['cardsn'], $setting['times_text'], $fee, $tag, $endtime);
	}

	if($type == 'times_times') {
		$endtime = strtotime($_GPC['endtime']);
		if($endtime > $card['endtime']) {
			message("该会员的{$setting['times_text']}到期时间为：" . date('Y-m-d', $card['endtime']) . ",您当前在进行消费操作，设置到期时间不能超过" . date('Y-m-d', $card['endtime']) , '', 'error');
		}
		$flag = intval($_GPC['flag']);
		if($flag) {
			$endtime = TIMESTAMP;
		}
		$tag = floor(($card['endtime'] - $endtime) / 86400);
		pdo_update('mc_card_members', array('endtime' => $endtime), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		$endtime = date('Y-m-d', $endtime);
		$log = array(
			'uniacid' => $_W['uniacid'],
			'uid' => $uid,
			'type' => 'times',
			'model' => 2,
			'fee' => 0,
			'tag' => $tag,
			'addtime' => TIMESTAMP,
			'note' => date('Y-m-d H:i') . "管理员手动设置{$setting['times_text']}到期时间为{$endtime},设置之前的{$setting['times_text']}到期时间为".date('Y-m-d', $card['endtime']),
			'remark' => trim($_GPC['remark']),
		);
		pdo_insert('mc_card_record', $log);
		mc_notice_times_times($card['openid'], "您好，您的{$setting['times_text']}到期时间已变更", $setting['times_text'], $endtime);
	}
	message('操作成功', referer(), 'success');
}

if($do == 'record') {
	$uid = intval($_GPC['uid']);
	$card = pdo_get('mc_card_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
	$where = ' WHERE uniacid = :uniacid AND uid = :uid';
	$param = array(':uniacid' => $_W['uniacid'], ':uid' => $uid);

	$type = trim($_GPC['type']);
	if(!empty($type)) {
		$where .= ' AND type = :type';
		$param[':type'] = $type;
	}
	if(empty($_GPC['endtime']['start'])) {
		$starttime = strtotime('-30 days');
		$endtime = TIMESTAMP;
	} else {
		$starttime = strtotime($_GPC['endtime']['start']);
		$endtime = strtotime($_GPC['endtime']['end']) + 86399;
	}
	$where .= ' AND addtime >= :starttime AND addtime <= :endtime';
	$param[':starttime'] = $starttime;
	$param[':endtime'] = $endtime;

	$pindex = max(1, intval($_GPC['page']));
	$psize = 30;
	$limit = " ORDER BY id DESC LIMIT " . ($pindex -1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_record') . " {$where}", $param);
	$list = pdo_fetchall('SELECT * FROM ' . tablename('mc_card_record') . " {$where} {$limit}", $param);
	$pager = pagination($total, $pindex, $psize);
	template('mc/card');
}

if($do == 'notice') {
	uni_user_permission_check('mc_card_other');
	$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
	if($op == 'list') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		$limit = " ORDER BY id DESC LIMIT " . ($pindex -1) * $psize . ", {$psize}";

		$addtime = intval($_GPC['addtime']);
		$where = ' WHERE uniacid = :uniacid AND type = 1';
		$param = array(':uniacid' => $_W['uniacid']);

		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_notices') . " {$where}", $param);
		$notices = pdo_fetchall('SELECT * FROM ' . tablename('mc_card_notices') . " {$where} {$limit}", $param);
		$pager = pagination($total, $pindex, $psize);
	}

	if($op == 'post') {
		$id = intval($_GPC['id']);
		if($id > 0) {
			$notice = pdo_get('mc_card_notices', array('uniacid' => $_W['uniacid'], 'id' => $id));
			if(empty($notice)) {
				message('通知不存在或已被删除', referer(), 'error');
			}
		}
		if(checksubmit()) {
			$title = trim($_GPC['title']) ? trim($_GPC['title']) : message('通知标题不能为空');
			$content = trim($_GPC['content']) ? trim($_GPC['content']) : message('通知内容不能为空');
			$data = array(
				'uniacid' => $_W['uniacid'],
				'type' => 1,
				'uid' => 0,
				'title' => $title,
				'thumb' => trim($_GPC['thumb']),
				'groupid' => intval($_GPC['groupid']),
				'content' => htmlspecialchars_decode($_GPC['content']),
				'addtime' => TIMESTAMP
			);
			if($id > 0) {
				pdo_update('mc_card_notices', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
			} else {
				pdo_insert('mc_card_notices', $data);
			}
			message('发布通知成功', url('mc/card/notice') , 'success');
		}
	}

	if($op == 'del') {
		$id = intval($_GPC['id']);
		pdo_delete('mc_card_notices', array('uniacid' => $_W['uniacid'], 'id' => $id));
		message('删除成功', referer(), 'success');
	}
	template('mc/card-notice');
}

if($do == 'credit') {
	uni_user_permission_check('mc_card_other');
	$set = pdo_get('mc_card_credit_set', array('uniacid' => $_W['uniacid']));
	if(empty($set)) {
		$set = array();
	} else {
		$set['share'] = iunserializer($set['share']);
		$set['sign'] = iunserializer($set['sign']);
	}
	if(checksubmit()) {
		$data = array(
			'uniacid' => $_W['uniacid'],
			'sign' => array(
				'everydaynum' => intval($_GPC['sign']['everydaynum']),
				'lastday' => intval($_GPC['sign']['lastday']),
				'lastnum' => intval($_GPC['sign']['lastnum']),
			),
			'share' => array(
				'times' => intval($_GPC['share']['times']),
				'num' => intval($_GPC['share']['num']),
			),
			'content' => htmlspecialchars_decode($_GPC['content']),
		);
		$data['sign'] = iserializer($data['sign']);
		$data['share'] = iserializer($data['share']);
		if(empty($set['uniacid'])) {
			pdo_insert('mc_card_credit_set', $data);
		} else {
			pdo_update('mc_card_credit_set', $data, array('uniacid' => $_W['uniacid']));
		}
		message('积分策略更新成功', referer(), 'success');
	}
	template('mc/card-credit');
}

if($do == 'recommend') {
	uni_user_permission_check('mc_card_other');
	$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
	if($op == 'list') {
		$pindex = max(1, intval($_GPC['page']));
		$psize = 30;
		$limit = " ORDER BY id DESC LIMIT " . ($pindex -1) * $psize . ", {$psize}";

		$addtime = intval($_GPC['addtime']);
		$where = ' WHERE uniacid = :uniacid';
		$param = array(':uniacid' => $_W['uniacid']);

		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_card_recommend') . " {$where}", $param);
		$recommends = pdo_fetchall('SELECT * FROM ' . tablename('mc_card_recommend') . " {$where} {$limit}", $param);
		$pager = pagination($total, $pindex, $psize);
	}

	if($op == 'post') {
		$id = intval($_GPC['id']);
		if($id > 0) {
			$recommend = pdo_get('mc_card_recommend', array('uniacid' => $_W['uniacid'], 'id' => $id));
			if(empty($recommend)) {
				message('推荐不存在或已被删除', referer(), 'error');
			}
		}
		if(checksubmit()) {
			$title = trim($_GPC['title']) ? trim($_GPC['title']) : message('推荐标题不能为空');
			$content = trim($_GPC['url']) ? trim($_GPC['url']) : message('推荐链接不能为空');
			$data = array(
				'uniacid' => $_W['uniacid'],
				'title' => $title,
				'thumb' => trim($_GPC['thumb']),
				'url' => trim($_GPC['url']),
				'displayorder' => intval($_GPC['displayorder']),
				'addtime' => TIMESTAMP
			);
			if($id > 0) {
				pdo_update('mc_card_recommend', $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
			} else {
				pdo_insert('mc_card_recommend', $data);
			}
			message('发布推荐成功', url('mc/card/recommend') , 'success');
		}
	}

	if($op == 'del') {
		$id = intval($_GPC['id']);
		pdo_delete('mc_card_recommend', array('uniacid' => $_W['uniacid'], 'id' => $id));
		message('删除成功', referer(), 'success');
	}

	template('mc/card-recommend');
}

if ($do == 'sign') {
	uni_user_permission_check('mc_card_other');
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$list = pdo_fetchall("SELECT * FROM ". tablename('mc_card_sign_record'). " WHERE uniacid = :uniacid ORDER BY id DESC LIMIT " . ($pindex - 1)*$psize. ','. $psize, array(':uniacid' => $_W['uniacid']));
	foreach ($list as $key => &$value){
		$value['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
		$value['realname'] = pdo_fetchcolumn("SELECT realname FROM ". tablename('mc_members'). ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $value['uid']));
	}
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ". tablename('mc_card_sign_record'). " WHERE uniacid = :uniacid", array(':uniacid' => $_W['uniacid']));
	$pager = pagination($total, $pindex, $psize);
	template('mc/card-sign');
}

if($do == 'other') {
	uni_user_permission_check('mc_card_other');
	template('mc/card-other');
}


