<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_member');
$dos = array('list', 'chart');
$do = in_array($do, $dos) ? $do : 'chart';
load()->model('mc');

$starttime = empty($_GPC['time']['start']) ? mktime(0, 0, 0, date('m') , 1, date('Y')) : strtotime($_GPC['time']['start']);
$endtime = empty($_GPC['time']['end']) ? TIMESTAMP : strtotime($_GPC['time']['end']) + 86399;
$num = ($endtime + 1 - $starttime) / 86400;

if($do == 'chart') {
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'credit2';
	$names = array('credit1' => '积分', 'credit2' => '余额', 'cash' => '现金');
	$_W['page']['title'] = "{$names[$type]}统计-会员中心";

	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'credit2';
	if($type != 'cash') {
		$today_recharge = floatval(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :uniacid AND credittype = :credittype AND num > 0 AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':credittype' => $type, ':starttime' => strtotime(date('Y-m-d')), ':endtime' => TIMESTAMP)));
		$today_consume = floatval(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :uniacid AND credittype = :credittype AND num < 0 AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':credittype' => $type, ':starttime' => strtotime(date('Y-m-d')), ':endtime' => TIMESTAMP)));
		$total_recharge = floatval(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :uniacid AND credittype = :credittype AND num > 0 AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':credittype' => $type, ':starttime' => $starttime, ':endtime' => $endtime)));
		$total_consume = floatval(pdo_fetchcolumn('SELECT SUM(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :uniacid AND credittype = :credittype AND num < 0 AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':credittype' => $type, ':starttime' => $starttime, ':endtime' => $endtime)));
	} else {
		$today_consume = floatval(pdo_fetchcolumn('SELECT SUM(final_cash) FROM ' . tablename('mc_cash_record') . ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => strtotime(date('Y-m-d')), ':endtime' => TIMESTAMP)));
		$total_consume = floatval(pdo_fetchcolumn('SELECT SUM(final_cash) FROM ' . tablename('mc_cash_record') . ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime)));
	}
	if($_W['isajax']) {
		$stat = array();
		for($i = 0; $i < $num; $i++) {
			$time = $i * 86400 + $starttime;
			$key = date('m-d', $time);
			$stat['consume'][$key] = 0;
			$stat['recharge'][$key] = 0;
		}
		if($type != 'cash') {
			$data = pdo_fetchall('SELECT id,num,credittype,createtime,uniacid FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :uniacid AND credittype = :credittype AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':credittype' => $type, ':starttime' => $starttime, ':endtime' => $endtime));

			if(!empty($data)) {
				foreach($data as $da) {
					$key = date('m-d', $da['createtime']);
					if($da['num'] > 0) {
						$stat['recharge'][$key] += $da['num'];
					} else {
						$stat['consume'][$key] += abs($da['num']);
					}
				}
			}
		} else {
			$data = pdo_fetchall('SELECT * FROM ' . tablename('mc_cash_record') . ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime', array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime));

			if(!empty($data)) {
				foreach($data as $da) {
					$key = date('m-d', $da['createtime']);
					$stat['consume'][$key] += abs($da['final_cash']);
				}
			}

		}
		$out['label'] = array_keys($stat['consume']);
		$out['datasets'] = array('recharge' => array_values($stat['recharge']), 'consume' => array_values($stat['consume']));
		exit(json_encode($out));
	}
}

if($do == 'list') {
	$type = trim($_GPC['type']) ? trim($_GPC['type']) : 'credit2';
	$names = array('credit1' => '积分', 'credit2' => '余额', 'cash' => '现金');
	$_W['page']['title'] = "{$names[$type]}查询-会员中心";

	if($type != 'cash') {
		$tablename = 'mc_credits_record';
		$condition = ' WHERE uniacid = :uniacid AND credittype = :credittype AND createtime >= :starttime AND createtime <= :endtime';
		$params = array(':uniacid' => $_W['uniacid'], ':credittype' => $type, ':starttime' => $starttime, ':endtime' => $endtime);
		$num = intval($_GPC['num']);
		if($num > 0) {
			if($num == 1) {
				$condition .= ' AND num >= 0';
			} else {
				$condition .= ' AND num <= 0';
			}
		}
		$min = intval($_GPC['min']);
		if($min > 0 ) {
			$condition .= ' AND abs(num) >= :minnum';
			$params[':minnum'] = $min;
		}

		$max = intval($_GPC['max']);
		if($max > 0 ) {
			$condition .= ' AND abs(num) <= :maxnum';
			$params[':maxnum'] = $max;
		}
	} else {
		$tablename = 'mc_cash_record';
		$condition = ' WHERE uniacid = :uniacid AND createtime >= :starttime AND createtime <= :endtime';
		$params = array(':uniacid' => $_W['uniacid'], ':starttime' => $starttime, ':endtime' => $endtime);
		$min = intval($_GPC['min']);
		if($min > 0 ) {
			$condition .= ' AND abs(final_fee) >= :minnum';
			$params[':minnum'] = $min;
		}

		$max = intval($_GPC['max']);
		if($max > 0 ) {
			$condition .= ' AND abs(final_fee) <= :maxnum';
			$params[':maxnum'] = $max;
		}
	}

	$user = trim($_GPC['user']);
	if(!empty($user)) {
		$condition .= ' AND (uid IN (SELECT uid FROM '.tablename('mc_members').' WHERE uniacid = :uniacid AND (realname LIKE :username OR uid = :uid OR mobile LIKE :mobile)))';
		$params[':username'] = "%{$user}%";
		$params[':uid'] = intval($user);
		$params[':mobile'] = "%{$user}%";
	}

	$psize = 30;
	$pindex = max(1, intval($_GPC['page']));
	$limit = " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($tablename) . $condition, $params);
	$data = pdo_fetchall('SELECT * FROM ' . tablename($tablename) . $condition . $limit, $params);

	if(!empty($data)) {
		$uids = array();
		$clerks = array();
		foreach($data as $da) {
			if(!in_array($da['uid'], $uids)) {
				$uids[] = $da['uid'];
			}
			if(!in_array($da['clerk_id'], $clerks)) {
				$clerks[] = $da['clerk_id'];
			}
		}
		$uids = implode(',', $uids);
		$users = pdo_fetchall('SELECT mobile,uid,realname FROM ' . tablename('mc_members') . " WHERE uniacid = :uniacid AND uid IN ($uids)", array(':uniacid' => $_W['uniacid']), 'uid');
		$clerks = implode(',', $clerks);
		$clerks = pdo_fetchall('SELECT name,id FROM ' . tablename('activity_coupon_password') . " WHERE uniacid = :uniacid AND id IN ($clerks)", array(':uniacid' => $_W['uniacid']), 'id');
		$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'), 'id');
	}

	$pager = pagination($total, $pindex, $psize);
}
template('mc/stat');