<?php
/**


/**
 * 签到定义
 */

$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign') . " (
`id` int(10) unsigned  AUTO_INCREMENT,
 `weid` int(11) NOT NULL  ,
 `rid` int(11) NOT NULL,
 `title` varchar(200) NOT NULL,
`follow_credit` int(10) NOT NULL,
`follow_credit_allow` int (1) default 0,
`leave_credit_clear` int(1)default 0,
`sign_credit` int (11) default 0,
`sync_credit` int(1) default 0,
`rule` varchar(2000) default NULL,
`starttime` int(10) DEFAULT 0,
`endtime` int(10) DEFAULT 0,
`sin_suc_msg` varchar(200) ,
`sin_suc_fail` varchar(200),
`new_title` varchar(200),
`new_icon` varchar(200),
`new_content` varchar(200),
`copyright` varchar(200),
 `createtime` int(10) DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
pdo_query($sql);

/**
 * 签到用户
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign_user') . " (
`id` int(10) unsigned  AUTO_INCREMENT,
`sid` int(10) NOT  NULL ,
`begin_sign_time` int(10) DEFAULT NULL,
`end_sign_time` int(10) DEFAULT NULL,
`openid` varchar(200) NOT NULL ,
`nickname` varchar(20) NOT NULL,
`headimgurl` varchar(200),
`serial_id` int(10) default NULL,
`credit` int(10) default 0,
`sin_count` int(10) default 0,
`sin_serial` int(10) default 0,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
pdo_query($sql);

/**
 * 签到记录
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign_record') . " (
`id` int(10) unsigned  AUTO_INCREMENT,
`uid` int (10) NOT NULL,
`openid` varchar(200)NOT NULL,
 `sid`  int(10) default 0 ,
`sin_time` int(10) DEFAULT 0,
`credit` int(10) NOT NULL,
`sign_type` int(2) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
pdo_query($sql);


/**
 * 联系签到
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign_serial') . " (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    sid INT(11) UNSIGNED DEFAULT NULL,
    `day` int(4)  NOT NULL ,
    `credit` int(10) default 0,
 	`createtime` int(10) unsigned NOT NULL COMMENT '日期',
  	PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

pdo_query($sql);



/**
 * 积分中奖表
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign_award') . " (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    sid INT(10) UNSIGNED DEFAULT NULL,
    `uid` int(10) NOT NULL,
    `sign_type` int(2) NOT NULL,
    serial_start_time int(10) ,
    serial_end_time int(10),
    serial_day int(10),
    `credit` int(10) NOT NULL,
 	`createtime` int(10) unsigned NOT NULL COMMENT '日期',
  	PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

pdo_query($sql);

/**
 * token
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign_token') . " (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
weid INT(11) UNSIGNED DEFAULT NULL,
`access_token` varchar(1000) NOT NULL ,
expires_in INT(11),
`createtime` int(10) unsigned NOT NULL COMMENT '日期',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

pdo_query($sql);

/**
 * links
 */
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('mon_sign_link') . " (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`sid` INT(11) UNSIGNED DEFAULT NULL,
`sort` int(2) default 0,
`link_name` varchar(50) NOT NULL ,
link_url varchar(50) NOT NULL,
`createtime` int(10) unsigned NOT NULL COMMENT '日期',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

pdo_query($sql);







