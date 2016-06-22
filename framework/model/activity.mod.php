<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function activity_coupon_available($uid, $pindex = 1, $psize = 10) {
	global $_W;
	$user = mc_fetch($uid, array('groupid'));
	$groupid = $user['groupid'];
	$data = pdo_fetchall("SELECT * FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `starttime` <= :starttime AND `endtime` >= :endtime AND `couponid` IN (SELECT couponid FROM " . tablename('activity_coupon_allocation') . " WHERE `groupid` = :groupid) ORDER BY `couponid` DESC", array(':groupid' => $groupid, ':starttime' => TIMESTAMP, ':endtime' => TIMESTAMP));
	foreach ($data as $da) {
		if ($da['dosage'] >= $da['amount']) {
			continue;
		}
		$pcount = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `couponid` = :couponid", array(':uid' => $uid, ':couponid' => $da['couponid']));
		if ($pcount < $da['limit']) {
			$id[] = $da['couponid'];
		}
	}
	if (!empty($id)) {
		$idstr = implode(',', $id);
		$total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `couponid` IN ({$idstr})");
		if ($psize > 0) {
			$available = pdo_fetchall("SELECT title,description,discount,starttime,endtime,`limit`,amount-dosage AS residue FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `couponid` IN ({$idstr}) ORDER BY `couponid` DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		} else {
			$available = pdo_fetchall("SELECT title,description,discount,starttime,endtime,`limit`,amount-dosage AS residue FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `couponid` IN ({$idstr}) ORDER BY `couponid` DESC");
		}
	} else {
		$available = '';
	}
	return array('total' => $total,'data' => $available);
}


function activity_coupon_owned($uid, $filter = array(), $pindex = 10, $psize = 0) {
	$condition = '';
	if (!empty($filter['used'])) {
		$condition .= ' AND r.`status` = ' . $filter['used'];
	}
	if (!empty($filter['couponid'])) {
		$condition .= ' AND r.`couponid` = ' . $filter['couponid'];
	}
	if (!empty($filter['grantmodule'])) {
		$condition .= " AND r.`grantmodule`= '{$filter['grantmodule']}' ";
	}
	if (!empty($filter['usemodule'])) {
		$condition .= " AND r.`usemodule`= '{$filter['usemodule']}' ";
	}
	$limit_sql = '';
	if ($psize > 0) {
		$limit_sql = ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
	}
	$total = pdo_fetchall("SELECT COUNT(*) AS cototal, r.couponid, r.code, r.status FROM " . tablename('activity_coupon_record') . " AS r LEFT JOIN " . tablename('activity_coupon') . " AS c ON r.couponid = c.couponid WHERE c.type = 1 AND r.uid = :uid " . $condition . ' GROUP BY r.couponid', array(':uid' => $uid));
	$data = pdo_fetchall("SELECT COUNT(*) AS cototal, r.couponid, r.code, r.recid, r.status FROM " . tablename('activity_coupon_record') . " AS r LEFT JOIN " . tablename('activity_coupon') . " AS c ON r.couponid = c.couponid WHERE c.type = 1 AND r.uid = :uid " . $condition . ' GROUP BY r.couponid ORDER BY r.couponid DESC' . $limit_sql, array(':uid' => $uid), 'couponid');
	if(!empty($data)) {
		$couponids = implode(', ', array_keys($data));
		$tokens = pdo_fetchall("SELECT couponid,thumb,couponsn,`condition`,title,discount,type,description,starttime,endtime FROM " . tablename('activity_coupon') . " WHERE couponid IN ({$couponids})", array(), 'couponid');
		foreach($tokens as &$token) {
			$token['recid'] = $data[$token['couponid']]['recid'];
			$token['code'] = $data[$token['couponid']]['code'];
			$token['status'] = $data[$token['couponid']]['status'];
			$token['cototal'] = $data[$token['couponid']]['cototal'];
			$token['thumb'] = tomedia($token['thumb']);
			$token['description'] = htmlspecialchars_decode($token['description']);
		}
	}
	unset($data);
	return array('total' => count($total), 'data' => $tokens);
}



function activity_coupon_info($couponid, $uniacid) {
	global $_W;
	$couponid = intval($couponid);
	$uniacid = intval($uniacid) ? intval($uniacid) : $_W['uniacid'];
	$coupon = pdo_fetch("SELECT couponid,credittype,credit,title,description,discount,starttime,endtime,`limit`,amount,dosage FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `couponid` = :couponid AND uniacid = :uniacid LIMIT 1", array(':couponid' => $couponid, ':uniacid' => $uniacid));
	if (!empty($coupon)) {
		$coupon['residue'] = $coupon['amount'] - $coupon['dosage'];
		if ($coupon['residue'] < 0) {
			$coupon['residue'] = 0;
		}
	}
	return $coupon;
}


function activity_coupon_grant($uid, $couponid, $module = '', $remark = '') {
	global $_W;
	$uid = intval($uid);
	$user = mc_fetch($uid, array('groupid'));
	$groupid = $user['groupid'];
	$couponid = intval($couponid);
	$code = base_convert(uniqid(), 16, 10);
	$coupon = pdo_fetch("SELECT * FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `couponid` = :couponid LIMIT 1", array(':couponid' => $couponid));
	$pcount = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `couponid` = :couponid", array(':couponid' => $couponid, ':uid' => $uid));
	$coupongroup = pdo_fetchall("SELECT * FROM " . tablename('activity_coupon_allocation') . " WHERE `couponid` = :couponid", array(':couponid' => $couponid));
	foreach ($coupongroup as $li) {
		$group[] = $li['groupid'];
	}
	if (empty($coupon)) {
		return error(-1, '未找到指定折扣券');
	} elseif (empty($coupongroup)) {
		return error(-1, '该折扣券未指定可使用的会员组');
	} elseif (!in_array($groupid, $group)) {
		return error(-1, '您所在的用户组无权限使用该折扣券');
	} elseif ($coupon['starttime'] > TIMESTAMP) {
		return error(-1, '折扣活动尚未开始');
	} elseif ($coupon['endtime'] < TIMESTAMP) {
		return error(-1, '折扣活动已经结束');
	} elseif ($coupon['dosage'] >= $coupon['amount']) {
		return error(-1, '折扣券已经发放完毕');
	} elseif ($pcount >= $coupon['limit']) {
		return error(-1, '用户领取折扣券数量已经超过限制');
	}
	$insert = array(
		'couponid' => $couponid,
		'uniacid' => $_W['uniacid'],
		'uid' => $uid,
		'code' => $code,
		'grantmodule' => $module,
		'granttime' => TIMESTAMP,
		'status' => 1,
		'remark' => $remark
	);
	pdo_insert('activity_coupon_record', $insert);
	pdo_update('activity_coupon', array('dosage' => $coupon['dosage'] + 1), array('couponid' => $couponid));
	return true;
}



function activity_coupon_use($uid, $couponid, $operator, $clerk_id = 0, $recid = '', $module = 'system', $clerk_type = 1, $store_id = 0) {
	global $_W;
	$length = strlen($couponid); 
	if ($length == '16') {
		$coupon_record = pdo_get('activity_coupon_record', array('code' => $couponid), array('couponid'));
		$code = $couponid;
		$couponid = $coupon_record['couponid'];
	}
	$coupon = pdo_fetch("SELECT * FROM " . tablename('activity_coupon') . " WHERE `type` = 1 AND `couponid` = :couponid LIMIT 1", array(':couponid' => $couponid));
	if (empty($coupon)) {
		return error(-1, '没有指定的折扣券信息');
	} elseif ($coupon['starttime'] > TIMESTAMP) {
		return error(-1, '折扣活动尚未开始');
	} elseif ($coupon['endtime'] < TIMESTAMP) {
		return error(-1, '折扣活动已经结束');
	}
	$params = array();
	$params[':couponid'] = $couponid;
	$params[':uid'] = $uid;
	$where = ' ORDER BY granttime ';
	if (!empty($recid)) {
		$where = ' AND `recid` = :recid';
		$params[':recid'] = $recid;
	}
	if ($length == '16') {
		$code_params[':uid'] = $uid;
		$code_params['code'] = $code;
		$precord = pdo_fetch("SELECT * FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `code` = :code AND `status` = 1 $where", $code_params);
	} else {
		$precord = pdo_fetch("SELECT * FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `couponid` = :couponid AND `status` = 1 $where", $params);
	}
	if (empty($precord)) {
		return error(-1, '没有可使用的折扣券');
	}
	$update = array(
		'status' => 2,
		'usemodule' => $module,
		'usetime' => TIMESTAMP,
		'operator' => $operator,
		'clerk_id' => intval($clerk_id),
		'clerk_type' => $clerk_type,
		'store_id' => $store_id
	);
	pdo_update('activity_coupon_record', $update, array('recid' => $precord['recid']));
	return true;
}


function activity_token_available($uid, $pindex = 1, $psize = 0) {
	global $_W;
	$result = array('total' => 0, 'data' => '');
	$member = mc_fetch($uid, array('groupid'));

	$sql = 'SELECT * FROM ' . tablename('activity_coupon') . ' WHERE `type` = :type AND `starttime` <= :starttime AND
			`endtime` >= :endtime AND `couponid` IN (SELECT `couponid` FROM ' . tablename('activity_coupon_allocation')
			. ' WHERE `groupid` = :groupid) ORDER BY `couponid` DESC';
	$params = array(':type' => 2, ':groupid' => $member['groupid']);
	$params[':starttime'] = $params[':endtime'] = TIMESTAMP;
	$coupons = pdo_fetchall($sql, $params);

	if (empty($coupons)) {
		return $result;
	}

	$couponIds = '';
	foreach ($coupons as $coupon) {
		$params = array(':uniacid' => $_W['uniacid'], ':uid' => $uid);
		if ($coupon['amount'] > $coupon['dosage']) {
			$sql = 'SELECT COUNT(*) FROM ' . tablename('activity_coupon_record') . ' WHERE `uniacid` = :uniacid AND
					`uid` = :uid AND `couponid` = :couponid';
			$params[':couponid'] = $coupon['couponid'];
			$receivedTotal = pdo_fetchcolumn($sql, $params);
			if ($receivedTotal < $coupon['limit']) {
				$couponIds .= $coupon['couponid'] . ',';
			}
		}
	}

	if (empty($couponIds)) {
		return $result;
	}

	$couponIds = trim($couponIds, ',');
	$sql = 'SELECT COUNT(*) FROM ' . tablename('activity_coupon') . ' WHERE `type` = :type AND `couponid` IN (' .
			$couponIds . ')';
	$params = array(':type' => 2);
	$result['total'] = pdo_fetchcolumn($sql, $params);

	if ($result['total'] > 0) {
		$sql = 'SELECT `title`, `description`, `discount`, `condition`, `starttime`, `endtime`, `limit`, `amount` -
				`dosage` AS `residue` FROM ' . tablename('activity_coupon') . ' WHERE `type` = :type AND `couponid`
				IN (' . $couponIds . ') ORDER BY `couponid` DESC';
		$sql .= $psize > 0 ? ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize : '';
		$result['data'] = pdo_fetchall($sql, $params);
	}

	return $result;
}


function activity_token_owned($uid, $filter = array(), $pindex = 1, $psize = 10) {
	$condition = '';
	if (!empty($filter['used'])) {
		$condition .= ' AND r.`status` = ' . $filter['used'];
	}
	if (!empty($filter['couponid'])) {
		$condition .= ' AND r.`couponid` = ' . $filter['couponid'];
	}
	if (!empty($filter['grantmodule'])) {
		$condition .= " AND r.`grantmodule`= '{$filter['grantmodule']}' ";
	}
	if (!empty($filter['usemodule'])) {
		$condition .= " AND r.`usemodule`= '{$filter['usemodule']}' ";
	}
	$limit_sql = '';
	if ($psize > 0) {
		$limit_sql = ' LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
	}
	$total = pdo_fetchall("SELECT COUNT(*) AS cototal, r.couponid, r.code, r.status FROM " . tablename('activity_coupon_record') . " AS r LEFT JOIN " . tablename('activity_coupon') . " AS c ON r.couponid = c.couponid WHERE c.type = 2 AND r.uid = :uid " . $condition . ' GROUP BY r.couponid', array(':uid' => $uid));
	$data = pdo_fetchall("SELECT COUNT(*) AS cototal, r.couponid, r.code, r.status,r.recid FROM " . tablename('activity_coupon_record') . " AS r LEFT JOIN " . tablename('activity_coupon') . " AS c ON r.couponid = c.couponid WHERE c.type = 2 AND r.uid = :uid " . $condition . ' GROUP BY r.couponid ORDER BY r.couponid DESC' . $limit_sql, array(':uid' => $uid), 'couponid');
	if(!empty($data)) {
		$couponids = implode(', ', array_keys($data));
		$tokens = pdo_fetchall("SELECT couponid,thumb,couponsn,`condition`,title,discount,type,description,starttime,endtime FROM " . tablename('activity_coupon') . " WHERE couponid IN ({$couponids})", array(), 'couponid');
		foreach($tokens as &$token) {
			$token['recid'] =$data[$token['couponid']]['recid'];
			$token['code'] = $data[$token['couponid']]['code'];
			$token['status'] = $data[$token['couponid']]['status'];
			$token['cototal'] = $data[$token['couponid']]['cototal'];
			$token['thumb'] = tomedia($token['thumb']);
			$token['description'] = htmlspecialchars_decode($token['description']);
		}
	}
	unset($data);
	return array('total' => count($total), 'data' => $tokens);
}


function activity_token_info($couponid, $uniacid) {
	global $_W;
	$couponid = intval($couponid);
	$uniacid = intval($uniacid) ? intval($uniacid) : $_W['uniacid'];
	$coupon = pdo_fetch("SELECT couponid,couponsn,credittype,credit,title,description,discount,`condition`,starttime,endtime,`limit`,amount,dosage FROM " . tablename('activity_coupon') . " WHERE `type` = 2 AND `couponid` = :couponid AND uniacid = :uniacid LIMIT 1", array(':couponid' => $couponid, ':uniacid' => $uniacid));
	if (!empty($coupon)) {
		$coupon['residue'] = $coupon['amount'] - $coupon['dosage'];
		if ($coupon['residue'] < 0) {
			$coupon['residue'] = 0;
		}
	}
	return $coupon;
}


function activity_token_grant($uid, $couponid, $module = '', $remark = '') {
	global $_W;
	$uid = intval($uid);
	$user = mc_fetch($uid, array('groupid'));
	$groupid = $user['groupid'];
	$couponid = intval($couponid);
	$code = base_convert(uniqid(), 16, 10);
	$coupon = pdo_fetch("SELECT * FROM " . tablename('activity_coupon') . " WHERE `type` = 2 AND `couponid` = :couponid", array(':couponid' => $couponid));
	$pcount = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `couponid` = :couponid", array(':couponid' => $couponid, ':uid' => $uid));
	$coupongroup = pdo_fetchall("SELECT * FROM " . tablename('activity_coupon_allocation') . " WHERE `couponid` = :couponid", array(':couponid' => $couponid));
	foreach ($coupongroup as $li) {
		$group[] = $li['groupid'];
	}
	if (empty($coupon)) {
		return error(-1, '未找到指定代金券');
	} elseif (empty($coupongroup)) {
		return error(-1, '该代金券未指定可使用的会员组');
	} elseif (!in_array($groupid, $group)) {
		return error(-1, '用户组无权限');
	} elseif ($coupon['starttime'] > TIMESTAMP) {
		return error(-1, '代金券活动尚未开始');
	} elseif ($coupon['endtime'] < TIMESTAMP) {
		return error(-1, '代金券活动已经结束');
	} elseif ($coupon['dosage'] >= $coupon['amount']) {
		return error(-1, '代金券已经发放完毕');
	} elseif ($pcount >= $coupon['limit']) {
		return error(-1, '用户领取代金券数量已经超过限制');
	}
	$insert = array(
		'couponid' => $couponid,
		'uniacid' => $_W['uniacid'],
		'uid' => $uid,
		'code' => $code,
		'grantmodule' => $module,
		'granttime' => TIMESTAMP,
		'status' => 1,
		'remark' => $remark
	);
	pdo_insert('activity_coupon_record', $insert);
	pdo_update('activity_coupon', array('dosage' => $coupon['dosage'] + 1), array('couponid' => $couponid));
	return true;
}


function activity_token_use($uid, $couponid, $operator, $clerk_id = 0, $recid = '', $module = 'system', $clerk_type = 1, $store_id = 0) {
	global $_W;
	$length = strlen($couponid); 
	if ($length == '16') {
		$coupon_record = pdo_get('activity_coupon_record', array('code' => $couponid), array('couponid'));
		$code = $couponid;
		$couponid = $coupon_record['couponid'];
	}
	$coupon = pdo_fetch("SELECT * FROM " . tablename('activity_coupon') . " WHERE `type` = 2 AND `couponid` = :couponid LIMIT 1", array(':couponid' => $couponid));
	if (empty($coupon)) {
		return error(-1, '没有指定的代金券信息');
	} elseif ($coupon['starttime'] > TIMESTAMP) {
		return error(-1, '代金券活动尚未开始');
	} elseif ($coupon['endtime'] < TIMESTAMP) {
		return error(-1, '代金券活动已经结束');
	}
	$params = array();
	$params[':uid'] = $uid;
	$params[':couponid'] = $couponid;
	$where = 'ORDER BY granttime';
	if (!empty($recid)) {
		$where = ' AND `recid` = :recid';
		$params[':recid'] = $recid;
	}
	if ($length == '16') {
		$code_params[':uid'] = $uid;
		$code_params['code'] = $code;
		$precord = pdo_fetch("SELECT * FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `code` = :code AND `status` = 1 $where", $code_params);
	} else {
		$precord = pdo_fetch("SELECT * FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `couponid` = :couponid AND `status` = 1 $where", $params);
	}
	
	if (empty($precord)) {
		return error(-1, '没有可使用的代金券');
	}
	$update = array(
		'status' => 2,
		'usemodule' => $module,
		'usetime' => TIMESTAMP,
		'operator' => $operator,
		'clerk_id' => $clerk_id,
		'clerk_type' => $clerk_type,
		'store_id' => $store_id,
	);
	pdo_update('activity_coupon_record', $update, array('recid' => $precord['recid']));
	return true;
}


function activity_goods_grant($uid, $exid){
	global $_W;
	$exid = intval($exid);
	$uid = intval($uid);
	$exchange = activity_exchange_info($exid, $_W['uniacid']);
	if (empty($exchange)) {
		return error(-1, '没有指定的实物兑换');
	}
	if ($exchange['starttime'] > TIMESTAMP) {
		return error(-1, '该实物兑换尚未开始');
	}
	if ($exchange['endtime'] < TIMESTAMP) {
		return error(-1, '该实物兑换已经结束');
	}
	$pnum = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_exchange_trades') . ' WHERE uniacid = :uniacid AND uid = :uid AND exid = :exid', array(':uniacid' => $_W['uniacid'], ':uid' => $uid, ':exid' => $exid));
	if ($pnum >= $exchange['pretotal']) {
		return error(-1, '该实物兑换每人只能使用' . $exchange['pretotal'] . '次');
	}
	if ($exchange['num'] >= $exchange['total']) {
		return error(-1, '该实物兑换已兑换完');
	}
	$data = array(
		'uniacid' => $_W['uniacid'],
		'uid' => $uid,
		'type' => 3,
		'exid' => $exid,
		'createtime' => TIMESTAMP,
	);
	pdo_insert('activity_exchange_trades', $data);
	$insert_id = pdo_insertid();
	if (empty($insert_id)) {
		return error(-1, '实物兑换失败');
	}
		$insert = array(
		'tid' => $insert_id,
		'uniacid' => $_W['uniacid'],
		'uid' => $uid,
		'status' => 0,
		'exid' => $exid,
		'createtime' => TIMESTAMP
	);
	pdo_insert('activity_exchange_trades_shipping', $insert);
	pdo_update('activity_exchange', array('num' => $exchange['num'] + 1), array('id' => $exid, 'uniacid' => $_W['uniacid']));
	return $insert_id;
}


function activity_module_grant($uid, $exid){
	global $_W;
	$exchange = activity_exchange_info($exid, $_W['uniacid']);
	if (empty($exchange)) {
		return error(-1, '没有指定的活动参与次数兑换');
	}
	if ($exchange['starttime'] > TIMESTAMP) {
		return error(-1, '该活动参与次数兑换尚未开始');
	}
	if ($exchange['endtime'] < TIMESTAMP) {
		return error(-1, '该活动参与次数兑换已经结束');
	}
	if ($exchange['pretotal'] > 0) {
		$activity_modules = pdo_fetch('SELECT * FROM ' . tablename('activity_modules') . ' WHERE uniacid = :uniacid AND uid = :uid AND exid = :exid AND module = :module', array(':uniacid' => $_W['uniacid'], ':uid' => $uid, 'exid' => $exid, 'module' => $exchange['extra']['name']));
		if ($activity_modules) {
			$starttime = strtotime(date('Y-m-d')) - intval($exchange['extra']['period']) * 3600 * 24;
						$pnum = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_modules_record') . ' WHERE mid = :mid AND num = 1 AND createtime > :createtime', array('mid' => $activity_modules['mid'], ':createtime' => $starttime));
			if ($pnum >= $exchange['pretotal']) {
				return error(-1, '每人每' . $exchange['extra']['period'] . '天内,只能兑换' . $exchange['pretotal'] . '次');
			}
						pdo_update('activity_modules', array('available' => $activity_modules['available'] + 1), array('mid' => $activity_modules['mid'], 'uid' => $uid));
		} else {
			$data = array(
				'uniacid' => $_W['uniacid'],
				'uid' => intval($uid),
				'exid' => $exid,
				'module' => trim($exchange['extra']['name']),
				'available' => 1
			);
			pdo_insert('activity_modules', $data);
			$activity_modules['mid'] = pdo_insertid();
		}

				$data = array('mid' => $activity_modules['mid'], 'num' => 1, 'createtime' => TIMESTAMP);
		pdo_insert('activity_modules_record', $data);
		return true;
	} else {
		return error(-1, '该兑换活动每人可兑换' . intval($exchange['pretotal']));
	}
	return true;
}


function activity_exchange_info($exchangeid, $uniacid = 0){
	global $_W;
	$uniacid = intval($uniacid) ? intval($uniacid) : $_W['uniacid'];
	$exchange = pdo_fetch('SELECT * FROM '.tablename('activity_exchange').' WHERE id=:id AND uniacid = :uniacid', array(':id'=>$exchangeid, ':uniacid' => $uniacid));
	if (!empty($exchange) && !empty($exchange['extra'])) {
		$exchange['extra'] = iunserializer($exchange['extra']);
	}
	return $exchange;
}


function activity_exchange_shipping($id){
	global $_W;
	return pdo_fetch('SELECT * FROM ' . tablename('activity_exchange_trades_shipping') . ' WHERE tid=:id AND uniacid=:uniacid', array(':id' => $id, ':uniacid' => $_W['uniacid']));
}


function activity_shipping_status_title($status){
	if ($status == 0) {
		return '正常';
	} elseif ($status == 1) {
		return '已发货';
	} elseif ($status == 2) {
		return '已完成';
	} elseif ($status == -1) {
		return '关闭';
	}
}


function activity_type_title($type){
	switch (intval($type)) {
		case 1: return '折扣券';
		case 2: return '代金券';
		case 3: return '实体物品';
		case 4: return '虚拟物品';
		case 5:
		default: return '活动参与次数';
	}
}



function activity_module_card_grant($uid, $couponid, $module = '', $remark = '') {
	global $_W;
	$uid = intval($uid);
	$user = mc_fetch($uid, array('groupid'));
	$groupid = $user['groupid'];
	$couponid = intval($couponid);
	$coupon = pdo_fetch("SELECT * FROM " . tablename('activity_coupon') . " WHERE `couponid` = :couponid LIMIT 1", array(':couponid' => $couponid));
	$pcount = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('activity_coupon_record') . " WHERE `uid` = :uid AND `couponid` = :couponid", array(':couponid' => $couponid, ':uid' => $uid));
	$coupongroup = pdo_fetchall("SELECT groupid FROM " . tablename('activity_coupon_allocation') . " WHERE `couponid` = :couponid", array(':couponid' => $couponid), 'groupid');
	$group = array_keys($coupongroup);
	$couponmodules = pdo_fetchall("SELECT module FROM " . tablename('activity_coupon_modules') . " WHERE `couponid` = :couponid", array(':couponid' => $couponid), 'module');
	$modules = array_keys($couponmodules);
	if (empty($coupon)) {
		return error(-1, '未找到指定优惠券');
	} elseif (!in_array($module, $modules)) {
		return error(-1, '该优惠券只能在特定的模块中领取');
	} elseif (empty($coupongroup)) {
		return error(-1, '该优惠券未指定可使用的会员组');
	} elseif (!in_array($groupid, $group)) {
		return error(-1, '您所在的用户组没有领取该优惠券的权限');
	} elseif ($coupon['starttime'] > TIMESTAMP) {
		return error(-1, '优惠券活动尚未开始');
	} elseif ($coupon['endtime'] < TIMESTAMP) {
		return error(-1, '优惠券活动已经结束');
	} elseif ($coupon['dosage'] >= $coupon['amount']) {
		return error(-1, '优惠券已经发放完毕');
	} elseif ($pcount >= $coupon['limit']) {
		return error(-1, '用户领取优惠券数量已经超过限制');
	}
		$creditnames = array();
	$unisettings = uni_setting($_W['uniacid'], array('creditnames'));
	if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
		foreach ($unisettings['creditnames'] as $key => $credit) {
			$creditnames[$key] = $credit['title'];
		}
	}
	$credit = mc_credit_fetch($uid, array($coupon['credittype']));
	if ($credit[$coupon['credittype']] < $coupon['credit']) {
		return error(-1, '您的' . $creditnames[$coupon['credittype']] . '数量不够,无法兑换.');
	}
	mc_credit_update($uid, $coupon['credittype'], -1 * $coupon['credit'], array($uid, '优惠券兑换:' . $coupon['title'] . ' 消耗 ' . $creditnames[$coupon['credittype']] . ':' . $coupon['credit']));
	$remark = "通过{$module}模块领取优惠券";
	$insert = array(
		'couponid' => $couponid,
		'uniacid' => $_W['uniacid'],
		'uid' => $uid,
		'grantmodule' => $module,
		'granttime' => TIMESTAMP,
		'status' => 1,
		'remark' => $remark
	);
	pdo_insert('activity_coupon_record', $insert);
	pdo_update('activity_coupon', array('dosage' => $coupon['dosage'] + 1), array('couponid' => $couponid));
	return $coupon;
}


function activity_module_card_own($uid, $filter = array('module' => '', 'used' => '', 'type' => '1'), $pindex = 1, $psize = 10) {
	global $_W;
	$condition = ' WHERE `a`.`uniacid` = :uniacid AND `a`.`uid` = :uid';
	$params = array(':uniacid' => $_W['uniacid'], ':uid' => $uid);
	if (empty($filter['module'])) {
		return error(-1, '模块标识不能为空');
	}
	if (!empty($filter['used'])) {
		$condition .= ' AND a.`status` = ' . $filter['used'];
	}
	if (!empty($filter['couponid'])) {
		$condition .= ' AND a.`couponid` = ' . $filter['couponid'];
	}
	if (!empty($filter['grantmodule'])) {
		$condition .= " AND a.`grantmodule`= '{$filter['grantmodule']}' ";
	}
	if (!empty($filter['type'])) {
		$condition .= " AND b.`type`= '{$filter['type']}' ";
	}
	$condition .= ' GROUP BY a.couponid';
	$data = pdo_fetchall('SELECT a.*, b.*,COUNT(*) as num FROM ' . tablename('activity_coupon_record') . ' AS a LEFT JOIN ' . tablename('activity_coupon') . ' AS b ON a.couponid = b.couponid' . $condition . ' ORDER BY b.endtime ASC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $params);
	return $data;
}