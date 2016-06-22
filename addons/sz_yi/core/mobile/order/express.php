<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
function sortByTime($a, $b)
{
    if ($a['ts'] == $b['ts']) {
        return 0;
    } else {
        return $a['ts'] > $b['ts'] ? 1 : -1;
    }
}
function getList($express, $expresssn)
{
	$url = "http://wap.kuaidi100.com/wap_result.jsp?rand=" . time() . "&id={$express}&fromWeb=null&postid={$expresssn}";
	load()->func('communication');
	$resp = ihttp_request($url);
	$content = $resp['content'];
	if (empty($content)) {
		return array();
	}
	preg_match_all('/\\<p\\>&middot;(.*)\\<\\/p\\>/U', $content, $arr);
	if (!isset($arr[1])) {
		return false;
	}
	return $arr[1];
}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
$orderid   = intval($_GPC['id']);
if ($_W['isajax']) {
	if ($operation == 'display') {
		$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
		if (empty($order)) {
			show_json(0);
		}
		$goods = pdo_fetchall("select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids  from " . tablename('sz_yi_order_goods') . " og " . " left join " . tablename('sz_yi_goods') . " g on g.id=og.goodsid " . " where og.orderid=:orderid and og.uniacid=:uniacid ", array(':uniacid' => $uniacid, ':orderid' => $orderid));
		$goods = set_medias($goods, 'thumb');
		$order['goodstotal'] = count($goods);
		$set = set_medias(m('common')->getSysset('shop'), 'logo');
		show_json(1, array('order' => $order, 'goods' => $goods, 'set' => $set));
	} else if ($operation == 'step') {
		$express = trim($_GPC['express']);
		$expresssn = trim($_GPC['expresssn']);
		$arr = getList($express, $expresssn);
		if (!$arr) {
			$arr = getList($express, $expresssn);
			if (!$arr) {
				show_json(1, array('list' => array()));
			}
		}
		$len = count($arr);
		$step1 = explode("<br />", str_replace("&middot;", "", $arr[0]));
		$step2 = explode("<br />", str_replace("&middot;", "", $arr[$len - 1]));
		for ($i = 0; $i < $len; $i++) {
			if (strtotime(trim($step1[0])) > strtotime(trim($step2[0]))) {
				$row = $arr[$i];
			} else {
				$row = $arr[$len - $i - 1];
			}
			$step = explode("<br />", str_replace("&middot;", "", $row));
			$list[] = array('time' => trim($step[0]), 'step' => trim($step[1]), 'ts' => strtotime(trim($step[0])));
		}
		show_json(1, array('list' => $list));
	}
}
include $this->template('order/express');
