<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Processor extends WeModuleProcessor
{
    public function respond()
    {
        $_var_0 = pdo_fetch('select * from ' . tablename('rule') . ' where id=:id limit 1', array(':id' => $this->rule));
        if (empty($_var_0)) {
            return false;
        }
        $_var_1 = explode(':', $_var_0['name']);
        $_var_2 = isset($_var_1[1]) ? $_var_1[1] : '';
        if (!empty($_var_2)) {
            $_var_3 = SZ_YI_PLUGIN . $_var_2 . '/processor.php';
            if (is_file($_var_3)) {
                require $_var_3;
                $_var_4 = ucfirst($_var_2) . 'Processor';
                $_var_5 = new $_var_4($_var_2);
                if (method_exists($_var_5, 'respond')) {
                    return $_var_5->respond($this);
                }
            }
        }
    }
}
