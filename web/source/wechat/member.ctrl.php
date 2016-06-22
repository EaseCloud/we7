<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('module', 'coupon', 'location', 'discount', 'display', 'del', 'sync', 'modifystock', 'toggle', 'qr', 'record', 'cash', 'gift', 'groupon', 'general_coupon');
$do = in_array($do, $dos) ? $do : 'post';
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'post';
$acid = intval($_W['acid']);
load()->classs('coupon');
if($do == 'post') {
	$data = array(
		'card' => array(
			'card_type' => 'MEMBER_CARD',
			'member_card' => array(
				'base_info' => array(
					'logo_url' => urlencode('http://mmbiz.qpic.cn/mmbiz/qYicJhgpqsd37NCqJIqia4KF9o4fmq7NTgnQMd5vBSwn2ibBRQ4wCLr47ohUI6xicWUc7ibxoyJDubnNs3mxnfSXy2g/0'),
					'brand_name' => urlencode('商家信息'),
					'color' => 'Color080',
					'title' => urlencode('折扣券标题'),
					'sub_title' => urlencode('折扣券标题'),
					'can_share' => true,
					'can_give_friend' => true,
					'code_type' => 'CODE_TYPE_TEXT',
					'notice' => urlencode('操作提示操作提示'),
					'description' => urlencode('使用须知使用须知'),
					'service_phone' => urlencode('1000000'),
					'get_limit' => 100,
					'date_info' => array(
						'type' => 'DATE_TYPE_FIX_TIME_RANGE',
						'begin_timestamp' => '1439136000',
						'end_timestamp' => '1444838400'
					),
					'sku' => array(
						'quantity' => 10000
					),
					'custom_url_name' => urlencode('立即使用'),
					'custom_url' => urlencode('http://bbs.we7.cc'),
					'custom_url_sub_title' => urlencode('6个汉字tips'),
					'promotion_url_name' => urlencode('营销入口1'),
					'promotion_url' => urlencode('http://www.we7.cc'),
				),
				'supply_bonus' => true,
				'supply_balance' => false,
				'prerogative' => urlencode('会员卡特权说明'),
				'custom_field1' => array(
					'name_type' => 'FIELD_NAME_TYPE_LEVEL',
					'url' => urlencode('http://www.we7.cc'),
				),
				'activate_url' => urlencode('http://bbs.we7.cc'),
				'custom_cell1' => array(
					'name' => urlencode('使用入口2'),
					'tips' => urlencode('激活后显示'),
					'url' => urlencode('http://bbs.we7.cc'),
				)
			),
		),
	);
	$acc = new coupon($acid);
	$status = $acc->CreateCard(urldecode(json_encode(($data))));
	print_r($status);
}

if($do == 'update') {
	$data = array(
		'init_bonus' => 100,
		'init_balance' => 200,
		'membership_number' => 12345678,
		'code' => '',
		'card_id' => 'pTKzFjtDm_SKwh2vbiLwD2cGO0Ik ',
		'custom_field_value1' => '白金会员组',
	);
}





