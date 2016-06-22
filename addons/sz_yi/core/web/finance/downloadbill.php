<?php
/*=============================================================================
#     FileName: downloadbill.php
#         Desc: 
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:36:05
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$op = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
ca('finance.downloadbill');
if ($_W['ispost']) {
    $starttime = strtotime($_GPC['time']['start']);
    $endtime   = strtotime($_GPC['time']['end']);
    $result    = m('finance')->downloadbill($starttime, $endtime, $_GPC['type']);
    if (is_error($result)) {
        message($result['message'], '', 'error');
    }
}
if (empty($starttime) || empty($endtime)) {
    $starttime = $endtime = time();
}
load()->func('tpl');
include $this->template('web/finance/downloadbill');
