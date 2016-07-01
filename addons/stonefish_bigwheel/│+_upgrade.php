<?php
$sql = "
CREATE TABLE IF NOT EXISTS `ims_stonefish_bigwheel_share` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `rid` int(11) DEFAULT '0',
  `acid` int(11) DEFAULT '0',
  `share_title` varchar(200) DEFAULT '',
  `share_desc` varchar(300) DEFAULT '',
  `share_url` varchar(100) DEFAULT '',
  `share_txt` text NOT NULL COMMENT '参与活动规则',
  `share_imgurl` varchar(255) NOT NULL COMMENT '分享朋友或朋友圈图',
  `share_picurl` varchar(255) NOT NULL COMMENT '分享图片按钮',
  `share_pic` varchar(255) NOT NULL COMMENT '分享弹出图片',
  `sharenumtype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分享赠送抽奖类型',
  `sharenum` int(11) DEFAULT '0' COMMENT '分享赠送抽奖基数',
  `sharetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分享赠送类型',
  `share_confirm` varchar(200) DEFAULT '' COMMENT '分享成功提示语',
  `share_fail` varchar(200) DEFAULT '' COMMENT '分享失败提示语',
  `share_cancel` varchar(200) DEFAULT '' COMMENT '分享中途取消提示语',
  PRIMARY KEY (`id`),
  KEY `indx_rid` (`rid`),
  KEY `indx_acid` (`acid`),
  KEY `indx_uniacid` (`uniacid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";

pdo_run($sql);

if(pdo_fieldexists('stonefish_bigwheel_reply', 'isqqhao')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." change `isqqhao` `isqq` tinyint(1) unsigned NOT NULL DEFAULT '0';");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_title')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_title`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_desc')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_desc`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_url')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_url`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_txt')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_txt`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_imgurl')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_imgurl`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_picurl')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_picurl`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'share_pic')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `share_pic`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'sharenumtype')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `sharenumtype`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'sharenum')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `sharenum`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_type_one')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_type_one`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_name_one')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_name_one`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_num_one')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_num_one`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_draw_one')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_draw_one`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_rate_one')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_rate_one`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_type_two')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_type_two`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_name_two')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_name_two`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_num_two')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_num_two`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_draw_two')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_draw_two`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_rate_two')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_rate_two`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_type_three')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_type_three`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_name_three')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_name_three`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_num_three')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_num_three`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_draw_three')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_draw_three`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_rate_three')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_rate_three`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_type_four')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_type_four`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_name_four')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_name_four`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_num_four')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_num_four`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_draw_four')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_draw_four`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_rate_four')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_rate_four`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_type_five')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_type_five`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_name_five')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_name_five`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_num_five')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_num_five`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_draw_five')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_draw_five`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_rate_five')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_rate_five`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_type_six')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_type_six`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_name_six')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_name_six`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_num_six')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_num_six`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_draw_six')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_draw_six`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'c_rate_six')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `c_rate_six`;");
}
if(pdo_fieldexists('stonefish_bigwheel_reply', 'content')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." DROP COLUMN `content`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'share_acid')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `share_acid` int(10) DEFAULT '0' AFTER `createtime`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'bigwheelimgbg')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `bigwheelimgbg` varchar(225) NOT NULL COMMENT '九宫格转动背景图' AFTER `bigwheelimgan`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'turntablenum')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `turntablenum` tinyint(1) DEFAULT '6' AFTER `turntable`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'isgender')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `isgender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要输入性别0为不需要1为需要' AFTER `isaddress`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'istelephone')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `istelephone` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要输入固定电话0为不需要1为需要' AFTER `isgender`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'isidcard')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `isidcard` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要输入证件号码0为不需要1为需要' AFTER `istelephone`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'iscompany')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `iscompany` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要输入公司名称0为不需要1为需要' AFTER `isidcard`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'isoccupation')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `isoccupation` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要输入职业0为不需要1为需要' AFTER `iscompany`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'isposition')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `isposition` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要输入职位0为不需要1为需要' AFTER `isoccupation`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'isfansname')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `isfansname` varchar(225) NOT NULL DEFAULT '真实姓名,手机号码,QQ号,邮箱,地址,性别,固定电话,证件号码,公司名称,职业,职位' COMMENT '显示字段名称' AFTER `isfans`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'ticketinfo')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `ticketinfo` varchar(50) DEFAULT '' COMMENT '兑奖参数提示词' AFTER `share_acid`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_reply', 'awardnum')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_reply')." ADD `awardnum` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '首页滚动中奖人数显示' AFTER `viewnum`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_prize', 'turntable')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_prize')." ADD `turntable` int(10) unsigned NOT NULL COMMENT '转盘类型' AFTER `rid`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_prize', 'prizetype')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_prize')." ADD `prizetype` varchar(50) NOT NULL COMMENT '奖品类别' AFTER `turntable`;");
}
if(pdo_fieldexists('stonefish_bigwheel_award', 'prizetype')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_award')." change `prizetype` `prizetype` int(11) DEFAULT '0' COMMENT '奖品ID'");
}
if(!pdo_fieldexists('stonefish_bigwheel_share', 'sharetype')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_share')." ADD `sharetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '分享赠送类型' AFTER `sharenum`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_share', 'share_confirm')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_share')." ADD `share_confirm` varchar(200) DEFAULT '' COMMENT '分享成功提示语' AFTER `sharetype`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_share', 'share_fail')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_share')." ADD `share_fail` varchar(200) DEFAULT '' COMMENT '分享失败提示语' AFTER `share_confirm`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_share', 'share_cancel')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_share')." ADD `share_cancel` varchar(200) DEFAULT '' COMMENT '分享中途取消提示语' AFTER `share_fail`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_share', 'share_txt')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_share')." ADD `share_txt` text NOT NULL COMMENT '参与活动规则' AFTER `share_url`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_fans', 'gender')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_fans')." ADD `gender` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别' AFTER `address`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_fans', 'telephone')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_fans')." ADD `telephone` varchar(15) NOT NULL DEFAULT '' COMMENT '固定电话' AFTER `gender`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_fans', 'idcard')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_fans')." ADD `idcard` varchar(30) NOT NULL DEFAULT '' COMMENT '证件号码' AFTER `telephone`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_fans', 'company')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_fans')." ADD `company` varchar(50) NOT NULL DEFAULT '' COMMENT '公司名称' AFTER `idcard`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_fans', 'occupation')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_fans')." ADD `occupation` varchar(30) NOT NULL DEFAULT '' COMMENT '职业' AFTER `company`;");
}
if(!pdo_fieldexists('stonefish_bigwheel_fans', 'position')) {
	pdo_query("ALTER TABLE ".tablename('stonefish_bigwheel_fans')." ADD `position` varchar(30) NOT NULL DEFAULT '' COMMENT '职位' AFTER `occupation`;");
}