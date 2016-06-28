<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
function sortByCreateTime($_var_0, $_var_1)
{
    if ($_var_0['createtime'] == $_var_1['createtime']) {
        return 0;
    } else {
        return $_var_0['createtime'] < $_var_1['createtime'] ? 1 : -1;
    }
}

class SupplierMobile extends Plugin
{
    protected $set = null;

    public function __construct()
    {
        parent::__construct('supplier');
        $this->set = $this->getSet();
        global $_GPC;
    }

    public function af_supplier()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
}