<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
$result = pdo_fetchcolumn('select id from ' . tablename('sz_yi_plugin') . ' where identity=:identity', array(':identity' => 'bonus'));
if(empty($result)){
      $displayorder_max = pdo_fetchcolumn('select max(displayorder) from ' . tablename('sz_yi_plugin'));
      $displayorder = $displayorder_max + 1;
      $sql = "INSERT INTO " . tablename('sz_yi_plugin') . " (`displayorder`,`identity`,`name`,`version`,`author`,`status`,`category`) VALUES(". $displayorder .",'bonus','亚美分红','1.0','官方','1','biz');";
    pdo_query($sql);
}
