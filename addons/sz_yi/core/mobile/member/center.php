<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$openid = m('user')->getOpenid();
$set = m('common')->getSysset(array('trade'));
$member = m('member')->getMember($openid);
$member['nickname'] = empty($member['nickname']) ? $member['mobile'] : $member['nickname'];
$uniacid = $_W['uniacid'];
$trade['withdraw'] = $set['trade']['withdraw'];
$trade['closerecharge'] = $set['trade']['closerecharge'];
$hascom = false;
$shopset = array();
$supplier_switch = false;
if (p('supplier')) {
	$supplier_set = p('supplier')->getSet();
	if(!empty($supplier_set['switch'])){
		$supplier_switch = true;
	}
}
$shopset['supplier_switch'] = $supplier_switch;
$plugc = p('commission');
if ($plugc) {
	$pset = $plugc->getSet();
	if (!empty($pset['level'])) {
		if ($member['isagent'] == 1 && $member['status'] == 1) {
			$hascom = true;
		}
	}
}
$shopset['commission_text'] = $pset['texts']['center'];
$shopset['hascom'] = $hascom;
$hascoupon = false;
$hascouponcenter = false;
$plugin_coupon = p('coupon');
if ($plugin_coupon) {
	$pcset = $plugin_coupon->getSet();
	if (empty($pcset['closemember'])) {
		$hascoupon = true;
		$hascouponcenter = true;
	}
}
$shopset['hascoupon'] = $hascoupon;
$shopset['hascouponcenter'] = $hascouponcenter;
$pluginbonus = p("bonus");
$bonus_start = false;
$bonus_text = "";
if(!empty($pluginbonus)){
	$bonus_set = $pluginbonus->getSet();
	if(!empty($bonus_set['start'])){
		$bonus_start = true;
		$bonus_text = $bonus_set['detail_text'] ? $bonus_set['detail_text'] : "分红明细";
	}
}
$shopset['bonus_start'] = $bonus_start;
$shopset['bonus_text'] = $bonus_text;
$shopset['is_weixin'] = is_weixin();
if ($_W['isajax']) {
	$level = array('levelname' => empty($set['shop']['levelname']) ? '普通会员' : $set['shop']['levelname']);
	if (!empty($member['level'])) {
		$level = m('member')->getLevel($openid);
	}
	$orderparams = array(':uniacid' => $_W['uniacid'], ':openid' => $openid);
	$order = array('status0' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=0  and uniacid=:uniacid limit 1', $orderparams), 'status1' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=1 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status2' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and status=2 and refundid=0 and uniacid=:uniacid limit 1', $orderparams), 'status4' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where openid=:openid and refundid<>0 and uniacid=:uniacid limit 1', $orderparams),);
	if (mb_strlen($member['nickname'], 'utf-8') > 6) {
		$member['nickname'] = mb_substr($member['nickname'], 0, 6, 'utf-8');
	}
	$open_creditshop = false;
	$creditshop = p('creditshop');
	if ($creditshop) {
		$creditshop_set = $creditshop->getSet();
		if (!empty($creditshop_set['centeropen'])) {
			$open_creditshop = true;
		}
	}
	$counts = array('cartcount' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('sz_yi_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0 ', array(':uniacid' => $uniacid, ':openid' => $openid)), 'favcount' => pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0 ', array(':uniacid' => $uniacid, ':openid' => $openid)));
	if ($plugin_coupon) {
		$time = time();
		$sql = 'select count(*) from ' . tablename('sz_yi_coupon_data') . ' d';
		$sql .= ' left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id';
		$sql .= ' where d.openid=:openid and d.uniacid=:uniacid and  d.used=0 ';
		$sql .= " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<={$time} && c.timeend>={$time})) order by d.gettime desc";
		$counts['couponcount'] = pdo_fetchcolumn($sql, array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
	}
	show_json(1, array('member' => $member, 'order' => $order, 'level' => $level, 'open_creditshop' => $open_creditshop, 'counts' => $counts, 'shopset' => $shopset, 'trade' => $trade));
}
include $this->template('member/center');
