<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$ms = array();
$ms['platform'][] =  array(
	'title' => '基本功能',
	'items' => array(
		array(
			'title' => '文字回复',
			'url' => url('platform/reply', array('m' => 'basic')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'basic'))
			),
			'permission_name' => 'platform_reply_basic'
		),
		array(
			'title' => '图文回复',
			'url' => url('platform/reply', array('m' => 'news')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'news')),
			),
			'permission_name' => 'platform_reply_news'
		),
		array(
			'title' => '音乐回复',
			'url' => url('platform/reply', array('m' => 'music')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'music'))
			),
			'permission_name' => 'platform_reply_music'
		),
		array(
			'title' => '图片回复',
			'url' => url('platform/reply', array('m' => 'images')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('platform/reply/post', array('m' => 'images'))
			),
			'permission_name' => 'platform_reply_images'
		),
		array(
			'title' => '语音回复',
			'url' => url('platform/reply', array('m' => 'voice')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('platform/reply/post', array('m' => 'voice'))
			),
			'permission_name' => 'platform_reply_voice'
		),
		array(
			'title' => '视频回复',
			'url' => url('platform/reply', array('m' => 'video')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('platform/reply/post', array('m' => 'video'))
			),
			'permission_name' => 'platform_reply_video'
		),
		array(
			'title' => '自定义接口回复',
			'url' => url('platform/reply', array('m' => 'userapi')),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>', 
				'url' => url('platform/reply/post', array('m' => 'userapi')),
			),
			'permission_name' => 'platform_reply_userapi'
		),
		array(
			'title' => '系统回复',
			'url' => url('platform/special/display'),
			'permission_name' => 'platform_reply_system'
		),
	)
);
$ms['platform'][] =  array(
	'title' => '高级功能',
	'items' => array(
		array(
			'title' => '常用服务接入',
			'url' => url('platform/service/switch'),
			'permission_name' => 'platform_service'
		),
		array(
			'title' => '自定义菜单',
			'url' => url('platform/menu'),
			'permission_name' => 'platform_menu'
		),
		array(
			'title' => '特殊消息回复',
			'url' => url('platform/special/message'),
			'permission_name' => 'platform_special'
		),
		array(
			'title' => '二维码管理',
			'url' => url('platform/qr'),
			'permission_name' => 'platform_qr'
		),
		array(
			'title' => '多客服接入',
			'url' => url('platform/reply', array('m' => 'custom')),
			'permission_name' => 'platform_reply_custom'
		),
		array(
			'title' => '长链接二维码',
			'url' => url('platform/url2qr'),
			'permission_name' => 'platform_url2qr'
		)
	)
);
$ms['platform'][] =  array(
	'title' => '数据统计',
	'items' => array(
		array(
			'title' => '聊天记录',
			'url' => url('platform/stat/history'),
			'permission_name' => 'platform_stat_history'
		),
		array(
			'title' => '回复规则使用情况',
			'url' => url('platform/stat/rule'),
			'permission_name' => 'platform_stat_rule'
		),
		array(
			'title' => '关键字命中情况',
			'url' => url('platform/stat/keyword'),
			'permission_name' => 'platform_stat_keyword'
		),
		array(
			'title' => '参数',
			'url' => url('platform/stat/setting'),
			'permission_name' => 'platform_stat_setting'
		)
	)
);
$ms['site'][] =  array(
	'title' => '微站管理',
	'items' => array(
		array(
			'title' => '站点管理',
			'url' => url('site/multi/display'),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('site/multi/post'),
			),
			'permission_name' => 'site_multi_display'
		),
		array(
			'title' => '站点添加/编辑',
			'is_permission' => 1,
			'permission_name' => 'site_multi_post'
		),
		array(
			'title' => '站点删除',
			'is_permission' => 1,
			'permission_name' => 'site_multi_del'
		),
		array(
			'title' => '模板管理',
			'url' => url('site/style/template'),
			'permission_name' => 'site_style_template'
		),
		array(
			'title' => '模块模板扩展',
			'url' => url('site/style/module'),
			'permission_name' => 'site_style_module'
		),
	)
);
$ms['site'][] =  array(
	'title' => '特殊页面管理',
	'items' => array(
		array(
			'title' => '会员中心',
			'url' => url('site/editor/uc'),
			'permission_name' => 'site_editor_uc'
		),
		array(
			'title' => '专题页面', 
			'url' => url('site/editor/page'),
			'append' => array(
				'title' => '<i class="fa fa-plus"></i>',
				'url' => url('site/editor/design'),
			),
			'permission_name' => 'site_editor_page'
		),
	)
);
$ms['site'][] =  array(
	'title' => '功能组件',
	'items' => array(
		array(
			'title' => '分类设置',
			'url' => url('site/category'),
			'permission_name' => 'site_category'
		),
		array(
			'title' => '文章管理',
			'url' => url('site/article'),
			'permission_name' => 'site_article'
		),
	)
);
$ms['mc'][] = array(
	'title' => '粉丝管理',
	'items' => array(
		array(
			'title' => '粉丝分组',
			'url' => url('mc/fangroup'),
			'permission_name' => 'mc_fangroup'
		),
		array(
			'title' => '粉丝',
			'url' => url('mc/fans'),
			'permission_name' => 'mc_fans'
		),
	)
);

$ms['mc'][] = array(
	'title' => '会员中心',
	'items' => array(
		array(
			'title' => '会员中心访问入口',
			'url' => url('platform/cover/mc'),
			'permission_name' => 'platform_cover_mc'
		),
		array(
			'title' => '会员',
			'url' => url('mc/member'),
			'permission_name' => 'mc_member'
		),
		array(
			'title' => '会员组',
			'url' => url('mc/group'),
			'permission_name' => 'mc_group'
		),
		array(
			'title' => '会员微信通知',
			'url' => url('mc/tplnotice'),
			'permission_name' => 'mc_tplnotice'
		),
		array(
			'title' => '会员积分管理',
			'url' => url('mc/creditmanage'),
			'permission_name' => 'mc_creditmanage'
		),
		array(
			'title' => '会员字段管理',
			'url' => url('mc/fields'),
			'permission_name' => 'mc_fields'
		)
	)
);
$ms['mc'][] = array(
	'title' => '会员卡管理',
	'items' => array(
		array(
			'title' => '会员卡访问入口',
			'url' => url('platform/cover/card'),
			'permission_name' => 'platform_cover_card'
		),
		array(
			'title' => '会员卡管理',
			'url' => url('mc/card'),
			'permission_name' => 'mc_card'
		),
		array(
			'title' => '商家设置',
			'url' =>url('mc/business'),
			'permission_name' => 'mc_business'
		),
		array(
			'title' => '店员操作访问入口',
			'url' => url('platform/cover/clerk'),
			'permission_name' => 'platform_cover_clerk'
		),
		array(
			'title' => '操作店员管理',
			'url' => url('activity/offline'),
			'permission_name' => 'activity_offline'
		)
	)
);
$ms['mc'][] = array(
	'title' => '积分兑换',
	'items' => array(
		array(
			'title' => '折扣券',
			'url' => url('activity/coupon'),
			'permission_name' => 'activity_coupon'
		),
		array(
			'title' => '代金券',
			'url' => url('activity/token'),
			'permission_name' => 'activity_token'
		),
		array(
			'title' => '真实物品',
			'url' => url('activity/goods'),
			'permission_name' => 'activity_goods',
		),
				array(
			'title' => '微信卡券',
			'url' => url('wechat/manage'),
			'permission_name' => 'wechat_manage'
		),
		array(
			'title' => '卡券核销',
			'url' => url('wechat/consume'),
			'permission_name' => 'wechat_consume',
		),
	)
);
$ms['mc'][] = array(
	'title' => '通知中心',
	'items' => array(
		array(
			'title' => '群发消息&通知',
			'url' => url('mc/broadcast'),
			'permission_name' => 'mc_broadcast',
		),
		array(
			'title' => '微信群发',
			'url' => url('mc/mass'),
			'permission_name' => 'mc_mass',
		),
		array(
			'title' => '通知参数',
			'url' => url('profile/notify'),
			'permission_name' => 'profile_notify',
		),
	)
);

$ms['setting'][] = array(
	'title' => '公众号选项',
	'items' => array(
		array(
			'title' => '支付参数',
			'url' => url('profile/payment'),
			'permission_name' => 'profile_payment',
		),
		array(
			'title' => '借用 oAuth 权限',
			'url' => url('mc/passport/oauth'),
			'permission_name' => 'mc_passport_oauth',
		),
		array(
			'title' => '借用 JS 分享权限',
			'url' => url('profile/jsauth'),
			'permission_name' => 'profile_jsauth',
		),
	)
);
$ms['setting'][] = array(
	'title' => '会员及粉丝选项',
	'items' => array(
		array(
			'title' => '积分设置',
			'url' => url('mc/credit'),
			'permission_name' => 'mc_credit',
		),
		array(
			'title' => '注册设置',
			'url' => url('mc/passport/passport'),
			'permission_name' => 'mc_passport_passport',
		),
		array(
			'title' => '粉丝同步设置',
			'url' => url('mc/passport/sync'),
			'permission_name' => 'mc_passport_sync',
		),
		array(
			'title' => 'UC站点整合',
			'url' => url('mc/uc'),
			'permission_name' => 'mc_uc',
		),
	)
);
$ms['setting'][] = array(
	'title' => '其他功能选项',
	'items' => array(
			)
);

$ms['ext'][] = array(
	'title' => '管理',
	'items' => array(
		array(
			'title' => '扩展功能管理',
			'url' => url('profile/module'),
			'permission_name' => 'profile_module',
		),
	)
);
return $ms;
