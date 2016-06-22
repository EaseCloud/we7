<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'query';
$openid = m('user')->getOpenid();
if ($operation == 'query') {
	$type = intval($_GPC['type']);
	$money = floatval($_GPC['money']);
	$time = time();
	$sql = 'select d.id,d.couponid,d.gettime,c.timelimit,c.timedays,c.timestart,c.timeend,c.thumb,c.couponname,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.bgcolor,c.thumb from ' . tablename('sz_yi_coupon_data') . ' d';
	$sql .= ' left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id';
	$sql .= " where d.openid=:openid and d.uniacid=:uniacid and  c.coupontype={$type} and {$money}>=c.enough and d.used=0 ";
	$sql .= " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<={$time} && c.timeend>={$time})) order by d.gettime desc";
	$list = set_medias(pdo_fetchall($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid'])), 'thumb');
	foreach ($list as &$row) {
		$row['thumb'] = tomedia($row['thumb']);
		$row['timestr'] = '永久有效';
		if (empty($row['timelimit'])) {
			if (!empty($row['timedays'])) {
				$row['timestr'] = date('Y-m-d H:i', $row['gettime'] + $row['timedays'] * 86400);
			}
		} else {
			if ($row['timestart'] >= $time) {
				$row['timestr'] = date('Y-m-d H:i', $row['timestart']) . '-' . date('Y-m-d H:i', $row['timeend']);
			} else {
				$row['timestr'] = date('Y-m-d H:i', $row['timeend']);
			}
		}
		if ($row['backtype'] == 0) {
			$row['backstr'] = '立减';
			$row['css'] = 'deduct';
			$row['backmoney'] = $row['deduct'];
			$row['backpre'] = true;
		} else if ($row['backtype'] == 1) {
			$row['backstr'] = '折';
			$row['css'] = 'discount';
			$row['backmoney'] = $row['discount'];
		} else if ($row['backtype'] == 2) {
			if ($row['backredpack'] > 0) {
				$row['backstr'] = '返现';
				$row['css'] = 'redpack';
				$row['backmoney'] = $row['backredpack'];
				$row['backpre'] = true;
			} else if ($row['backmoney'] > 0) {
				$row['backstr'] = '返利';
				$row['css'] = 'money';
				$row['backmoney'] = $row['backmoney'];
				$row['backpre'] = true;
			} else if (!empty($row['backcredit'])) {
				$row['backstr'] = '返积分';
				$row['css'] = 'credit';
				$row['backmoney'] = $row['backcredit'];
			}
		}
	}
	unset($row);
	show_json(1, array('coupons' => $list));
}
