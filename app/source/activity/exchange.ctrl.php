<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post', 'mine', 'confirm', 'shipping');
$do = in_array($_GPC['do'], $dos) ? $_GPC['do'] : 'display';
checkauth();
load()->model('activity');
load()->model('mc');
$uid = $_W['member']['uid'];
$uniacid = $_W['uniacid'];
$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames'));
if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
	foreach ($unisettings['creditnames'] as $key=>$credit) {
		$creditnames[$key] = $credit['title'];
	}
}


$sql = 'SELECT `status` FROM ' . tablename('mc_card') . " WHERE `uniacid` = :uniacid";
$cardstatus = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));

if($do == 'display') {
	$page = intval($_GPC['__input']['page']);
	$dtype = intval($_GPC['dtype']);
	$pindex = max(1, $page);
	$where = ' WHERE uniacid=:uniacid ';
	if($dtype) {
		$where .= ' AND type = ' . $dtype;
	} else {
		$where .= " AND type = '1'";
	}
	$params = array(':uniacid'=>$_W['uniacid']);
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_exchange'). $where , $params);
	$mycredits = mc_credit_fetch($uid);
		if($_W['isajax']) {
		$psize = 10;
		$list = pdo_fetchall('SELECT id,title,thumb,type,credittype,endtime,description,credit FROM '.tablename('activity_exchange')." $where ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
		if(!empty($list)) {
			foreach($list as &$li) {
				$li['credittype_cn'] = activity_type_title($li['type']);
				$li['credit_cn'] = $creditnames[$li['credittype']];
				$li['url'] = $li['type'] != 3 ? url('activity/exchange/post', array('exid' => $li['id'])) : url('activity/exchange/shipping', array('exid' => $li['id']));
			}
			$list = json_encode($list);
			exit($list);
		} else {
			exit('dataempty');
		}
	} else {
		$list = pdo_fetchall('SELECT id,title,thumb,type,credittype,endtime,description,credit FROM '.tablename('activity_exchange')." $where ORDER BY id ASC LIMIT 10", $params);
		foreach($list as &$li) {
			$li['credittype_cn'] = activity_type_title($li['type']);
			$li['credit_cn'] = $creditnames[$li['credittype']];
			$li['url'] = $li['type'] != 3 ? url('activity/exchange/post', array('exid' => $li['id'])) : url('activity/exchange/shipping', array('exid' => $li['id']));
		}
		
	}
	foreach ($list as &$value) {
		$value['endtime'] = date('Y年m月d日', $value['endtime']);
		$value['thumb'] = tomedia($value['thumb']);
		$value['description'] = htmlspecialchars_decode($value['description']);
	}
	$_W['page']['title'] = '积分兑换';
	$_W['page']['toolbar']['bottom'] = true;
	$_W['page']['toolbar']['jumps'] = array(
		array(
			'title' => '礼品兑换',
			'url' => url('activity/exchange/display', array('dtype' => 3)),
			'active' => true
		),
		array(
			'title' => '折扣券兑换',
			'url' => url('activity/exchange/display', array('dtype' => 1)),
		),
		array(
			'title' => '代金券兑换',
			'url' => url('activity/exchange/display', array('dtype' => 2)),
		),
		array(
			'title' => '抽奖机会',
			'url' => url('activity/exchange/display', array('dtype' => 5)),
		),
		array(
			'divider' => true
		),
		array(
			'title' => '所有兑换',
			'url' =>  url('activity/exchange/display', array('dtype' => '')),
		)
	);
	template('activity/exchange');
} elseif($do == 'post') {
	$exid = intval($_GPC['exid']);
	if (!empty($exid)) {
		$exchange = activity_exchange_info($exid, $_W['uniacid']);
	}
	if (empty($exchange)){
		message('没有指定的礼品兑换.');
	}
	$credit = mc_credit_fetch($uid, array($exchange['credittype']));
	if ($credit[$exchange['credittype']] < $exchange['credit']) {
		message($creditnames[$exchange['credittype']].'数量不够,无法兑换.');
	}
	$extype = intval($exchange['type']);
	if($extype == 1) {
		$ret = activity_coupon_grant($uid,$exchange['extra']['id']);
		if (is_error($ret)) {
			message($ret['message']);
		}
	} elseif($extype == 2) {
		$ret = activity_token_grant($uid,$exchange['extra']['id']);
		if (is_error($ret)) {
			message($ret['message']);
		}
	} elseif($extype == 5) {
		$ret = activity_module_grant($uid,$exid);
		if(is_error($ret)) {
			message($ret['message']);
		}
	}
	$trade = array(
			'uniacid'=>$_W['uniacid'],
			'uid'=>$uid,
			'exid'=>$exid,
			'type'=>$exchange['type'],
			'createtime'=>TIMESTAMP,
			'extra'=>iserializer($exchange)
	);
	
	$tradetype = array(
				'1' => 'coupons',
				'2' => 'tokens'
			);
	pdo_insert('activity_exchange_trades',$trade);
	mc_credit_update($uid, $exchange['credittype'], -1 * $exchange['credit'], array($uid, '礼品兑换:' . $exchange['title'] . ' 消耗 ' . $creditnames[$exchange['credittype']] . ':' . $exchange['credit']));
	message("兑换成功,您消费了 {$exchange['credit']} {$creditnames[$exchange['credittype']]}",url("mc/bond/{$tradetype[$extype]}", array('type' => $extype)));
} elseif($do == 'shipping') {
	load()->func('tpl');
	$exid = intval($_GPC['exid']);
	if (!empty($exid)) {
		$exchange = activity_exchange_info($exid);
	}
	if (empty($exchange)){
		message('没有指定的礼品兑换.');
	}
	$ret = activity_shipping_grant($uid,$exid);
	if(is_error($ret)) {
		message($ret['message']);
	}
	$member = mc_fetch($uid, array('uid','realname',$exchange['credittype'],'resideprovince','residecity','residedist','address','zipcode','mobile'));
	if ($member[$exchange['credittype']] < $exchange['credit']) {
		message($creditnames[$exchange['credittype']].'数量不够,无法兑换.');
	}
	$shipping = array(
			'exid'=>$exid,
			'uniacid'=>$uniacid,
			'uid'=>$uid,
			'realname'=>$member['realname'],
			'mobile'=>$member['mobile'],
			'province'=>$member['resideprovince'],
			'city'=>$member['residecity'],
			'district'=>$member['residedist'],
			'address'=>$member['address'],
			'zipcode'=>$member['zipcode'],
	);
	
	if(checksubmit('submit')) {
		$data = array(
			'uniacid'=>$_W['uniacid'],
			'exid'=>$exid,
			'uid'=>$uid,
			'status'=>0,
			'createtime'=>time(),
			'name'=>$_GPC['realname'],
			'mobile'=>$_GPC['mobile'],
			'province'=>$_GPC['reside']['province'],
			'city'=>$_GPC['reside']['city'],
			'district'=>$_GPC['reside']['district'],
			'address'=>$_GPC['address'],
			'zipcode'=>$_GPC['zipcode'],
		);
		$trade_log = array('uniacid' => $_W['uniacid'], 'uid' => $uid, 'exid' => $exid, 'type' => 3, 'createtime' => time(), 'extra' => iserializer($exchange));
		pdo_insert('activity_exchange_trades', $trade_log);
		pdo_insert('activity_exchange_trades_shipping', $data);
		pdo_update('activity_exchange', array('num' => $exchange['num'] + 1), array('id' => $exchange['id'], 'uniacid' => $_W['uniacid']));
		mc_credit_update($uid, $exchange['credittype'], -1*$exchange['credit'], array($uid, '礼品兑换:' . $exchange['title'] . ' 消耗 ' . $creditnames[$exchange['credittype']] . ':' . $exchange['credit']));
		message("兑换成功,您消费了 {$exchange['credit']} {$creditnames[$exchange['credittype']]}",url('activity/exchange/mine', array('type' => 3)));
	}
	template('activity/shipping');
} elseif($do == 'mine') {
	$type = intval($_GPC['type']);
	$type = empty($type) ? 1 : $type;
	$type = in_array($type, array(1,2,3,4,5)) ? $type : 1;
	$page = intval($_GPC['__input']['page']);
	switch ($type){
		case 1:
		case 2:
			break;
		case 3:
			$pindex = max(1, $page);
			$psize = 10;
			$where = ' WHERE uniacid=:uniacid AND uid=:uid';
			$params = array(':uniacid'=>$uniacid,':uid'=>$uid,);
			$sql = 'SELECT * FROM '.tablename('activity_exchange_trades_shipping')." $where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
			$list = pdo_fetchall($sql, $params);
			if(!empty($list)){
				foreach ($list as &$row) {
					$row['exchange'] = activity_exchange_info($row['exid']);
					$row['createtime_cn'] = date('Y-m-d', $row['createtime']);
					if($row['status'] == 1) {
						$row['status_cn'] = "<a onclick=\"return confirm('确认收货吗?');\" class=\"btn btn-primary\"  href=\"" . url('activity/exchange/confirm',array('id'=>$row['id'])) . "\">收货</a>";
					} else {
						$row['status_cn'] = activity_shipping_status_title($row['status']);
					}
				}
				
			}
			if($_W['isajax'] && $_W['ispost']) {
				$list = json_encode($list);
				exit($list);
			}
			break;
		case 4:
			break;
		case 5:
			$pindex = max(1, $page);
			$psize = 10;
			$where = ' WHERE uniacid=:uniacid AND uid=:uid AND available > 0';
			$params = array(':uniacid'=>$uniacid,':uid'=>$uid);
			$sql = 'SELECT * FROM '.tablename('activity_modules')." $where ORDER BY mid DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
			$list = pdo_fetchall($sql, $params);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_modules'). $where , $params);
			$pager = pagination($total, $pindex, $psize);
			break;
		default:
			break;
	}
	template('activity/mine');
} elseif($do == 'confirm') {
	$id = intval($_GPC['id']);
	$sql = 'SELECT * FROM '.tablename('activity_exchange_trades_shipping').' WHERE uid=:uid AND id=:id AND uniacid=:uniacid ';
	$params = array(
			':id'=>$id,
			':uid'=>$uid,
			':uniacid'=>$uniacid
	);
	$shipping = pdo_fetch($sql, $params);
	if (empty($shipping)) {
		message('未找到指定的实体礼品,无法确认收货.');
	}
	if (intval($shipping['status'])!=1) {
		message('指定的实体礼品未发货,无法确认收货.');
	}
	$params = array(
			'uid'=>$uid,
			'id'=>$id,
			'uniacid'=>$_W['uniacid']
	);
	pdo_update('activity_exchange_trades_shipping',array('status'=>2),$params);
	message('确认收货完成.',url('activity/exchange/mine', array('type' => 3)),'success');
}
