<?php


if (!pdo_fieldexists('mon_sign', 'new_title')) {
    pdo_query("ALTER TABLE " . tablename('mon_sign') . "ADD  `new_title` varchar(200);");

}

if (!pdo_fieldexists('mon_sign', 'copyright')) {
    pdo_query("ALTER TABLE " . tablename('mon_sign') . "ADD  `copyright` varchar(200) ;");

}

if (!pdo_fieldexists('mon_sign', 'new_icon')) {
    pdo_query("ALTER TABLE " . tablename('mon_sign') . "ADD  `new_icon` varchar(200);");

}

if (!pdo_fieldexists('mon_sign', 'new_content')) {
    pdo_query("ALTER TABLE " . tablename('mon_sign') . "ADD  `new_content` varchar(200);");

}


if (!pdo_fieldexists('mon_sign_award', 'serial_day')) {
    pdo_query("ALTER TABLE " . tablename('mon_sign_award') . "ADD  serial_day int(10) ;");

}





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










