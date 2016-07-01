<?php
	if (empty($_GPC['id'])) {
        message('抱歉，参数错误！', '', 'error');
    }
	$orderid = intval($_GPC['id']);
	$uniacid=$_W['uniacid'];
	$order = pdo_fetch("SELECT * FROM " . tablename('feng_record') . " WHERE id ='{$orderid}'");
	$goods = pdo_fetch("SELECT * FROM ".tablename('feng_goodslist')." WHERE id = '{$order['sid']}' ");
	if($goods['shengyurenshu']<$order['count']) {
		message('兑换码数量发生变化，请重新下单！', $this->createMobileUrl('exchange',array('id'=>$goods['id'])),'Warning');
	}
	
	$params['tid'] = $order['id'];
	$params['user'] = $_W['fans']['from_user'];
	$params['fee'] = $order['count'];
	$params['title'] = $_W['account']['name'];
	$params['ordersn'] = $order['ordersn'];

	include $this->template('pay');
?>