<?php
/*=============================================================================
#     FileName: query.php
#         Desc:  
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:25:08
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$kwd = trim($_GPC['keyword']);
$params = array();
$params[':uniacid'] = $_W['uniacid'];
$condition = " and uniacid=:uniacid";
if (!empty($kwd)) {
	$condition .= " AND ( `nickname` LIKE :keyword or `realname` LIKE :keyword or `mobile` LIKE :keyword )";
	$params[':keyword'] = "%{$kwd}%";
}
$ds = pdo_fetchall('SELECT id,avatar,nickname,openid,realname,mobile FROM ' . tablename('sz_yi_member') . " WHERE 1 {$condition} order by createtime desc", $params);
include $this->template('web/member/query');
