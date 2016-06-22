<?php
/*=============================================================================
#     FileName: processor.php
#         Desc: 
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:09:44
#      History:
=============================================================================*/

if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Processor extends WeModuleProcessor
{
    public function respond()
    {
        $rule = pdo_fetch('select * from ' . tablename('rule') . ' where id=:id limit 1', array(
            ':id' => $this->rule
        ));
        if (empty($rule)) {
            return false;
        }
        $names  = explode(':', $rule['name']);
        $plugin = isset($names[1]) ? $names[1] : '';
        if (!empty($plugin)) {
            $processor_file = SZ_YI_PLUGIN . $plugin . "/processor.php";
            if (is_file($processor_file)) {
                require $processor_file;
                $processor_class = ucfirst($plugin) . "Processor";
                $proc            = new $processor_class($plugin);
                if (method_exists($proc, "respond")) {
                    return $proc->respond($this);
                }
            }
        }
    }
}
