<?php
/*=============================================================================
#     FileName: processor.php
#         Desc: 
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:08:51
#      History:
=============================================================================*/

if (!defined('IN_IA')) {
    exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/version.php';
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'functions.php';
require SZ_YI_INC . 'processor.php';
require SZ_YI_INC . 'plugin/plugin_model.php';
class Sz_yiModuleProcessor extends Processor
{
    public function respond()
    {
        return parent::respond();
    }
}
