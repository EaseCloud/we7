<?php
global $_W;
if (!defined('IN_IA')) {
    exit('Access Denied');
}
$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'bonus'));
if(empty($result)){
    $displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
    $displayorder = $displayorder_max + 1;
    $sql = "INSERT INTO " . tablename('sz_yi_plugin') . " (`displayorder`,`identity`,`name`,`version`,`author`,`status`) VALUES(". $displayorder .",'bonus','芸众分红','1.0','官方','1');";
  pdo_fetchall($sql);
}
$sql = "CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus_goods') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `ordergoodid` int(11) DEFAULT '0',
  `orderid` int(11) DEFAULT '0',
  `total` int(11) DEFAULT '0',
  `optionname` varchar(100) DEFAULT '',
  `mid` int(11) DEFAULT '0' COMMENT '所有人，分佣者',
  `levelid` int(11) DEFAULT '0' COMMENT '级别id',
  `level` int(11) DEFAULT '0' COMMENT '1/2/3哪一级',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '应得佣金',
  `status` tinyint(3) DEFAULT '0' COMMENT '申请状态，-2删除，-1无效，0未申请，1申请，2审核通过 3已打款',
  `content` text,
  `applytime` int(11) DEFAULT '0',
  `checktime` int(11) DEFAULT '0',
  `paytime` int(11) DEFAULT '0',
  `invalidtime` int(11) DEFAULT '0',
  `deletetime` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分红单商品表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus_level') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `levelname` varchar(50) DEFAULT '',
  `agent_money` decimal(10,2) DEFAULT '0.00',
  `pcommission` decimal(10,2) DEFAULT '0.00',
  `commissionmoney` decimal(10,2) DEFAULT '0.00',
  `ordermoney` decimal(10,2) DEFAULT '0.00',
  `downcount` int(10) DEFAULT '0',
  `ordercount` int(10) DEFAULT '0',
  `downcountlevel1` int(10) DEFAULT '0',
  `type` int(11) DEFAULT '0' COMMENT '1为区域代理',
  `level` int(10) DEFAULT '0' COMMENT '等级权重',
  `premier` tinyint(1) DEFAULT '0' COMMENT '0 普通级别 1 最高级别',
  `content` text DEFAULT '' COMMENT '微信消息提醒追加内容',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='分红代理等级表' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `send_bonus_sn` int(11) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `total` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `type` tinyint(1) DEFAULT '0' COMMENT '0 手动 1 自动',
  `paymethod` tinyint(1) DEFAULT '0',
  `isglobal` tinyint(1) DEFAULT '0',
  `sendpay_error` tinyint(1) DEFAULT '0',
  `utime` int(11) DEFAULT '0',
  `ctime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分红明细';

CREATE TABLE IF NOT EXISTS " . tablename('sz_yi_bonus_log') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `openid` varchar(255) DEFAULT '',
  `uid` int(11) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `logno` varchar(255) DEFAULT '',
  `send_bonus_sn` int(11) DEFAULT '0',
  `paymethod` tinyint(1) DEFAULT '0',
  `isglobal` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `sendpay` tinyint(1) DEFAULT '0',
  `ctime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='分红日志';
";
pdo_fetchall($sql);
if(!pdo_fieldexists('sz_yi_member', 'bonuslevel')) {
  pdo_fetchall("ALTER TABLE ".tablename('sz_yi_member')." ADD `bonuslevel` INT DEFAULT '0' AFTER `agentlevel`, ADD `bonus_status` TINYINT(1) DEFAULT '0' AFTER `bonuslevel`;");
}

if(!pdo_fieldexists('sz_yi_member', 'bonus_area')) {
  pdo_fetchall("ALTER TABLE ".tablename('sz_yi_member')." ADD `bonus_area` TINYINT(1) DEFAULT '0' AFTER `bonuslevel`, ADD `bonus_province` varchar(50) DEFAULT '' AFTER `bonus_area`, ADD `bonus_city` varchar(50) DEFAULT '' AFTER `bonus_province`, ADD `bonus_district` varchar(50) DEFAULT '' AFTER `bonus_city`, ADD `bonus_area_commission` decimal(10,2) DEFAULT '0.00' AFTER `bonus_district`;");
}

if(!pdo_fieldexists('sz_yi_goods', 'bonusmoney')) {
  pdo_fetchall("ALTER TABLE ".tablename('sz_yi_goods')." ADD `bonusmoney` DECIMAL(10,2) DEFAULT '0.00' AFTER `costprice`;");
}

if(!pdo_fieldexists('sz_yi_bonus_goods', 'bonus_area')) {
  pdo_fetchall("ALTER TABLE ".tablename('sz_yi_bonus_goods')." ADD `bonus_area` TINYINT(1) DEFAULT '0' AFTER `levelid`;");
}
if(!pdo_fieldexists('sz_yi_bonus_level', 'msgtitle')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_bonus_level')." ADD `msgtitle` varchar(100) DEFAULT '';");
}

if(!pdo_fieldexists('sz_yi_bonus_level', 'msgcontent')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_bonus_level')." ADD `msgcontent` varchar(255) DEFAULT '';");
}
message('芸众分红插件安装成功', $this->createPluginWebUrl('bonus/agent'), 'success');