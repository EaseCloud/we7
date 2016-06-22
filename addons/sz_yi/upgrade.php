<?php

if(!pdo_fieldexists('sz_yi_member', 'regtype')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_member')." ADD    `regtype` tinyint(3) DEFAULT '1';");
}
if(!pdo_fieldexists('sz_yi_member', 'isbindmobile')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_member')." ADD    `isbindmobile` tinyint(3) DEFAULT '0';");
}
if(!pdo_fieldexists('sz_yi_member', 'isjumpbind')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_member')." ADD    `isjumpbind` tinyint(3) DEFAULT '0';");
}
if(!pdo_fieldexists('sz_yi_creditshop_log', 'storeid')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_creditshop_log')." ADD    `storeid` int(11) DEFAULT '0';");
}
if(!pdo_fieldexists('sz_yi_creditshop_log', 'realname')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_creditshop_log')." ADD   `realname` varchar(255) DEFAULT '';");
}
 if(!pdo_fieldexists('sz_yi_creditshop_log', 'mobile')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_creditshop_log')." ADD   `mobile` varchar(255) DEFAULT '';");
}
 if(!pdo_fieldexists('sz_yi_order', 'ordersn2')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD    `ordersn2` int(11) DEFAULT '0';");
}
if(!pdo_fieldexists('sz_yi_order', 'changeprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD    `changeprice` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('sz_yi_order', 'changedispatchprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD    `changedispatchprice` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('sz_yi_order', 'oldprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD    `oldprice` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('sz_yi_order', 'olddispatchprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD    `olddispatchprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('sz_yi_order', 'isvirtual')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD   `isvirtual` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_order_goods', 'changeprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order_goods')." ADD    `changeprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('sz_yi_order_goods', 'oldprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order_goods')." ADD    `oldprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('sz_yi_order_goods', 'commissions')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order_goods')." ADD    `commissions` text;");
}
 if(!pdo_fieldexists('sz_yi_goods', 'ednum')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_goods')." ADD    `ednum` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_goods', 'edmoney')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_goods')." ADD     `edmoney` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('sz_yi_goods', 'edareas')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_goods')." ADD      `edareas` text;");
}
 if(!pdo_fieldexists('sz_yi_member', 'fixagentid')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_member')." ADD      `fixagentid` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_poster', 'reccouponid')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_poster')." ADD      `reccouponid` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_poster', 'reccouponnum')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_poster')." ADD        `reccouponnum` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_poster', 'subcouponid')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_poster')." ADD         `subcouponid` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_poster', 'subcouponnum')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_poster')." ADD         `subcouponnum` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_plugin', 'category')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_plugin')." ADD         `category` varchar(255) DEFAULT '0';");
}
/**/
 if(!pdo_fieldexists('sz_yi_goods', 'deduct2')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_goods')." ADD         `deduct2` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('sz_yi_order', 'couponid')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD         `couponid` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_order', 'couponprice')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD         `couponprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('sz_yi_creditshop_goods', 'goodstype')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_creditshop_goods')." ADD      `goodstype` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_creditshop_goods', 'couponid')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_creditshop_goods')." ADD      `couponid` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_member_log', 'gives')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_member_log')." ADD      `gives` decimal(10,2) DEFAULT NULL;");
}
 if(!pdo_fieldexists('sz_yi_commission_level', 'downcount')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_commission_level')." ADD      `downcount` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('sz_yi_commission_level', 'ordercount')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_commission_level')." ADD      `ordercount` int(11) DEFAULT '0';");
}

$sql = "
CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_coupon'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `catid` int(11) DEFAULT '0',
  `couponname` varchar(255) DEFAULT '',
  `gettype` tinyint(3) DEFAULT '0',
  `getmax` int(11) DEFAULT '0',
  `usetype` tinyint(3) DEFAULT '0' COMMENT '消费方式 0 付款使用 1 下单使用',
  `returntype` tinyint(3) DEFAULT '0' COMMENT '退回方式 0 不可退回 1 取消订单(未付款) 2.退款可以退回',
  `bgcolor` varchar(255) DEFAULT '',
  `enough` decimal(10,2) DEFAULT '0.00',
  `timelimit` tinyint(3) DEFAULT '0' COMMENT '0 领取后几天有效 1 时间范围',
  `coupontype` tinyint(3) DEFAULT '0' COMMENT '0 优惠券 1 充值券',
  `timedays` int(11) DEFAULT '0',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `discount` decimal(10,2) DEFAULT '0.00' COMMENT '折扣',
  `deduct` decimal(10,2) DEFAULT '0.00' COMMENT '抵扣',
  `backtype` tinyint(3) DEFAULT '0',
  `backmoney` varchar(50) DEFAULT '' COMMENT '返现',
  `backcredit` varchar(50) DEFAULT '' COMMENT '返积分',
  `backredpack` varchar(50) DEFAULT '',
  `backwhen` tinyint(3) DEFAULT '0',
  `thumb` varchar(255) DEFAULT '',
  `desc` text,
  `createtime` int(11) DEFAULT '0',
  `total` int(11) DEFAULT '0' COMMENT '数量 -1 不限制',
  `status` tinyint(3) DEFAULT '0' COMMENT '可用',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '购买价格',
  `respdesc` text COMMENT '推送描述',
  `respthumb` varchar(255) DEFAULT '' COMMENT '推送图片',
  `resptitle` varchar(255) DEFAULT '' COMMENT '推送标题',
  `respurl` varchar(255) DEFAULT '',
  `credit` int(11) DEFAULT '0',
  `usecredit2` tinyint(3) DEFAULT '0',
  `remark` varchar(1000) DEFAULT '',
  `descnoset` tinyint(3) DEFAULT '0',
  `pwdkey` varchar(255) DEFAULT '',
  `pwdsuc` text,
  `pwdfail` text,
  `pwdurl` varchar(255) DEFAULT '',
  `pwdask` text,
  `pwdstatus` tinyint(3) DEFAULT '0',
  `pwdtimes` int(11) DEFAULT '0',
  `pwdfull` text,
  `pwdwords` text,
  `pwdopen` tinyint(3) DEFAULT '0',
  `pwdown` text,
  `pwdexit` varchar(255) DEFAULT '',
  `pwdexitstr` text,
  `displayorder` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_coupontype` (`coupontype`),
  KEY `idx_timestart` (`timestart`),
  KEY `idx_timeend` (`timeend`),
  KEY `idx_timelimit` (`timelimit`),
  KEY `idx_status` (`status`),
  KEY `idx_givetype` (`backtype`),
  KEY `idx_catid` (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_coupon_category'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `name` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_coupon_data'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `openid` varchar(255) DEFAULT '',
  `couponid` int(11) DEFAULT '0',
  `gettype` tinyint(3) DEFAULT '0' COMMENT '获取方式 0 发放 1 领取 2 积分商城',
  `used` int(11) DEFAULT '0',
  `usetime` int(11) DEFAULT '0',
  `gettime` int(11) DEFAULT '0' COMMENT '获取时间',
  `senduid` int(11) DEFAULT '0',
  `ordersn` varchar(255) DEFAULT '',
  `back` tinyint(3) DEFAULT '0',
  `backtime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_couponid` (`couponid`),
  KEY `idx_gettype` (`gettype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_coupon_guess'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `openid` varchar(255) DEFAULT '',
  `times` int(11) DEFAULT '0',
  `pwdkey` varchar(255) DEFAULT '',
  `ok` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_couponid` (`couponid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_coupon_log'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `logno` varchar(255) DEFAULT '',
  `openid` varchar(255) DEFAULT '',
  `couponid` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `paystatus` tinyint(3) DEFAULT '0',
  `creditstatus` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `paytype` tinyint(3) DEFAULT '0',
  `getfrom` tinyint(3) DEFAULT '0' COMMENT '0 发放 1 中心 2 积分兑换',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_couponid` (`couponid`),
  KEY `idx_status` (`status`),
  KEY `idx_paystatus` (`paystatus`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_getfrom` (`getfrom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_poster'). " (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT '0' COMMENT '1 首页 2 小店 3 商城 4 自定义',
  `title` varchar(255) DEFAULT '',
  `bg` varchar(255) DEFAULT '',
  `data` text,
  `keyword` varchar(255) DEFAULT '',
  `times` int(11) DEFAULT '0',
  `follows` int(11) DEFAULT '0',
  `isdefault` tinyint(3) DEFAULT '0',
  `resptitle` varchar(255) DEFAULT '',
  `respthumb` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `respdesc` varchar(255) DEFAULT '',
  `respurl` varchar(255) DEFAULT '',
  `waittext` varchar(255) DEFAULT '',
  `oktext` varchar(255) DEFAULT '',
  `subcredit` int(11) DEFAULT '0',
  `submoney` decimal(10,2) DEFAULT '0.00',
  `reccredit` int(11) DEFAULT '0',
  `recmoney` decimal(10,2) DEFAULT '0.00',
  `paytype` tinyint(1) DEFAULT '0',
  `scantext` varchar(255) DEFAULT '',
  `subtext` varchar(255) DEFAULT '',
  `beagent` tinyint(3) DEFAULT '0',
  `bedown` tinyint(3) DEFAULT '0',
  `isopen` tinyint(3) DEFAULT '0',
  `opentext` varchar(255) DEFAULT '',
  `openurl` varchar(255) DEFAULT '',
  `templateid` varchar(255) DEFAULT '',
  `subpaycontent` text,
  `recpaycontent` text,
  `entrytext` varchar(255) DEFAULT '',
  `reccouponid` int(11) DEFAULT '0',
  `reccouponnum` int(11) DEFAULT '0',
  `subcouponid` int(11) DEFAULT '0',
  `subcouponnum` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_type` (`type`),
  KEY `idx_times` (`times`),
  KEY `idx_isdefault` (`isdefault`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_poster_log'). " (
 `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `openid` varchar(255) DEFAULT '',
  `posterid` int(11) DEFAULT '0',
  `from_openid` varchar(255) DEFAULT '',
  `subcredit` int(11) DEFAULT '0',
  `submoney` decimal(10,2) DEFAULT '0.00',
  `reccredit` int(11) DEFAULT '0',
  `recmoney` decimal(10,2) DEFAULT '0.00',
  `createtime` int(11) DEFAULT '0',
  `reccouponid` int(11) DEFAULT '0',
  `reccouponnum` int(11) DEFAULT '0',
  `subcouponid` int(11) DEFAULT '0',
  `subcouponnum` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_openid` (`openid`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_posterid` (`posterid`),
  FULLTEXT KEY `idx_from_openid` (`from_openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_poster_qr'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(10) unsigned NOT NULL,
  `openid` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(3) DEFAULT '0',
  `sceneid` int(11) DEFAULT '0',
  `mediaid` varchar(255) DEFAULT '',
  `ticket` varchar(250) NOT NULL,
  `url` varchar(80) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `goodsid` int(11) DEFAULT '0',
  `qrimg` varchar(1000) DEFAULT '',
  `scenestr` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_acid` (`acid`),
  KEY `idx_sceneid` (`sceneid`),
  KEY `idx_type` (`type`),
  FULLTEXT KEY `idx_openid` (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_poster_scan'). " (
   `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `posterid` int(11) DEFAULT '0',
  `openid` varchar(255) DEFAULT '',
  `from_openid` varchar(255) DEFAULT '',
  `scantime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_posterid` (`posterid`),
  KEY `idx_scantime` (`scantime`),
  FULLTEXT KEY `idx_openid` (`openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_postera'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT '0' COMMENT '1 首页 2 小店 3 商城 4 自定义',
  `days` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `bg` varchar(255) DEFAULT '',
  `data` text,
  `keyword` varchar(255) DEFAULT '',
  `isdefault` tinyint(3) DEFAULT '0',
  `resptitle` varchar(255) DEFAULT '',
  `respthumb` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `respdesc` varchar(255) DEFAULT '',
  `respurl` varchar(255) DEFAULT '',
  `waittext` varchar(255) DEFAULT '',
  `oktext` varchar(255) DEFAULT '',
  `subcredit` int(11) DEFAULT '0',
  `submoney` decimal(10,2) DEFAULT '0.00',
  `reccredit` int(11) DEFAULT '0',
  `recmoney` decimal(10,2) DEFAULT '0.00',
  `scantext` varchar(255) DEFAULT '',
  `subtext` varchar(255) DEFAULT '',
  `beagent` tinyint(3) DEFAULT '0',
  `bedown` tinyint(3) DEFAULT '0',
  `isopen` tinyint(3) DEFAULT '0',
  `opentext` varchar(255) DEFAULT '',
  `openurl` varchar(255) DEFAULT '',
  `paytype` tinyint(1) NOT NULL DEFAULT '0',
  `subpaycontent` text,
  `recpaycontent` varchar(255) DEFAULT '',
  `templateid` varchar(255) DEFAULT '',
  `entrytext` varchar(255) DEFAULT '',
  `reccouponid` int(11) DEFAULT '0',
  `reccouponnum` int(11) DEFAULT '0',
  `subcouponid` int(11) DEFAULT '0',
  `subcouponnum` int(11) DEFAULT '0',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_type` (`type`),
  KEY `idx_isdefault` (`isdefault`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_postera_log'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `openid` varchar(255) DEFAULT '',
  `posterid` int(11) DEFAULT '0',
  `from_openid` varchar(255) DEFAULT '',
  `subcredit` int(11) DEFAULT '0',
  `submoney` decimal(10,2) DEFAULT '0.00',
  `reccredit` int(11) DEFAULT '0',
  `recmoney` decimal(10,2) DEFAULT '0.00',
  `createtime` int(11) DEFAULT '0',
  `reccouponid` int(11) DEFAULT '0',
  `reccouponnum` int(11) DEFAULT '0',
  `subcouponid` int(11) DEFAULT '0',
  `subcouponnum` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_openid` (`openid`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_posteraid` (`posterid`),
  FULLTEXT KEY `idx_from_openid` (`from_openid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS ".tablename('sz_yi_postera_qr'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acid` int(10) unsigned NOT NULL,
  `openid` varchar(100) NOT NULL DEFAULT '',
  `posterid` int(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT '0',
  `sceneid` int(11) DEFAULT '0',
  `mediaid` varchar(255) DEFAULT '',
  `ticket` varchar(250) NOT NULL,
  `url` varchar(80) NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `goodsid` int(11) DEFAULT '0',
  `qrimg` varchar(1000) DEFAULT '',
  `expire` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_acid` (`acid`),
  KEY `idx_sceneid` (`sceneid`),
  KEY `idx_type` (`type`),
  KEY `idx_posterid` (`posterid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
pdo_query($sql);

$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_article'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_title` varchar(255) NOT NULL DEFAULT '' COMMENT '文章标题',
  `resp_desc` text NOT NULL COMMENT '回复介绍',
  `resp_img` text NOT NULL COMMENT '回复图片',
  `article_content` longtext,
  `article_category` int(11) NOT NULL DEFAULT '0' COMMENT '文章分类',
  `article_date_v` varchar(20) NOT NULL DEFAULT '' COMMENT '虚拟发布时间',
  `article_date` varchar(20) NOT NULL DEFAULT '' COMMENT '文章发布时间',
  `article_mp` varchar(50) NOT NULL DEFAULT '' COMMENT '公众号',
  `article_author` varchar(20) NOT NULL DEFAULT '' COMMENT '发布作者',
  `article_readnum_v` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟阅读量',
  `article_readnum` int(11) NOT NULL DEFAULT '0' COMMENT '真实阅读量',
  `article_likenum_v` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟点赞数',
  `article_likenum` int(11) NOT NULL DEFAULT '0' COMMENT '真实点赞数',
  `article_linkurl` varchar(300) NOT NULL DEFAULT '' COMMENT '阅读原文链接',
  `article_rule_daynum` int(11) NOT NULL DEFAULT '0' COMMENT '每人每天参与次数',
  `article_rule_allnum` int(11) NOT NULL DEFAULT '0' COMMENT '所有参与次数',
  `article_rule_credit` int(11) NOT NULL DEFAULT '0' COMMENT '增加y积分',
  `article_rule_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '增加z余额',
  `page_set_option_nocopy` int(1) NOT NULL DEFAULT '0' COMMENT '页面禁止复制url',
  `page_set_option_noshare_tl` int(1) NOT NULL DEFAULT '0' COMMENT '页面禁止分享至朋友圈',
  `page_set_option_noshare_msg` int(1) NOT NULL DEFAULT '0' COMMENT '页面禁止发送给好友',
  `article_keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '页面关键字',
  `article_report` int(1) NOT NULL DEFAULT '0' COMMENT '举报按钮',
  `product_advs_type` int(1) NOT NULL DEFAULT '0' COMMENT '营销显示产品',
  `product_advs_title` varchar(255) NOT NULL DEFAULT '' COMMENT '营销产品标题',
  `product_advs_more` varchar(255) NOT NULL DEFAULT '' COMMENT '推广产品底部标题',
  `product_advs_link` varchar(255) NOT NULL DEFAULT '' COMMENT '推广产品底部链接',
  `product_advs` text NOT NULL COMMENT '营销商品',
  `article_state` int(1) NOT NULL DEFAULT '0',
  `network_attachment` varchar(255) DEFAULT '',
  `uniacid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_article_title` (`article_title`),
  KEY `idx_article_content` (`article_content`(10)),
  KEY `idx_article_keyword` (`article_keyword`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='营销文章';

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_article_category'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL DEFAULT '' COMMENT '分类名称',
  `uniacid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_category_name` (`category_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='营销表单分类';

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_article_log'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL DEFAULT '0' COMMENT '文章id',
  `read` int(11) NOT NULL DEFAULT '0',
  `like` int(11) NOT NULL DEFAULT '0',
  `openid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户openid',
  `uniacid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_aid` (`aid`),
  KEY `idx_openid` (`openid`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='点赞/阅读记录';

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_article_report'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `openid` varchar(255) NOT NULL DEFAULT '',
  `aid` int(11) DEFAULT '0',
  `cate` varchar(255) NOT NULL DEFAULT '',
  `cons` varchar(255) NOT NULL DEFAULT '',
  `uniacid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户举报记录';

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_article_share'). " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL DEFAULT '0',
  `share_user` int(11) NOT NULL DEFAULT '0' COMMENT '分享人',
  `click_user` int(11) NOT NULL DEFAULT '0' COMMENT '点击人',
  `click_date` varchar(20) NOT NULL DEFAULT '' COMMENT '执行时间',
  `add_credit` int(11) NOT NULL DEFAULT '0' COMMENT '添加的积分',
  `add_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '添加的余额',
  `uniacid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_aid` (`aid`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户分享数据';

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_article_sys'). " (
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `article_message` varchar(255) NOT NULL DEFAULT '',
  `article_title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `article_image` varchar(300) NOT NULL DEFAULT '' COMMENT '图片',
  `article_shownum` int(11) NOT NULL DEFAULT '0' COMMENT '每页数量',
  `article_keyword` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  `article_temp` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uniacid`),
  KEY `idx_article_message` (`article_message`),
  KEY `idx_article_keyword` (`article_keyword`),
  KEY `idx_article_title` (`article_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='文章设置';

INSERT INTO " . tablename('sz_yi_plugin'). " (`id`, `displayorder`, `identity`, `name`, `version`, `author`, `status`) VALUES
(12, 12, 'article', '文章营销', '1.0', '官方', 1);";
pdo_query($sql);

pdo_query("UPDATE `ims_sz_yi_plugin` SET `name` = '芸众分销' WHERE `identity` = 'commission'");
pdo_query("UPDATE `ims_qrcode` SET `name` = 'SZ_YI_POSTER_QRCODE', `keyword`='SZ_YI_POSTER' WHERE `keyword` = 'EWEI_SHOP_POSTER'");
if(!pdo_fieldexists('sz_yi_member', 'pwd')) {
    pdo_query("ALTER TABLE  ".tablename('sz_yi_member')." CHANGE  `pwd`  `pwd` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
}

if(!pdo_fieldexists('sz_yi_goods', 'cates')) {
	pdo_query("ALTER TABLE ".tablename('sz_yi_goods')." ADD     `cates` text;");
}
