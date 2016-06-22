<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
/* 积分排行 */
global $_W, $_GPC;


$limitsum = 10; //显示多少个排行

$sql = "SELECT * FROM " . tablename('sz_yi_member')." WHERE uniacid = :uniacid ORDER BY credit1 DESC LIMIT {$limitsum}";

//$sql = "SELECT a.credit1 as jf,b.* FROM " . tablename('mc_members')." a INNER JOIN " . tablename('sz_yi_member')." b ON a.uid = b.uid WHERE a.uniacid = :uniacid ORDER BY a.credit1 DESC LIMIT {$limitsum}";
$params = array(':uniacid' => $_W['uniacid']);
$list = pdo_fetchall($sql, $params);

//print_r('<pre>');print_r($list);exit;

include $this->template('member/phb');
