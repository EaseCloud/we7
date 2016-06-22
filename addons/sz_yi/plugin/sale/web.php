<?php
//Ü¿ÖÚÉÌ³Ç QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class SaleWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('sale');
    }
    public function index()
    {
        global $_W;
        if (cv('sale.deduct.view')) {
            header('location: ' . $this->createPluginWebUrl('sale/deduct'));
            exit;
        } else if (cv('sale.enough.view')) {
            header('location: ' . $this->createPluginWebUrl('sale/enough'));
            exit;
        } else if (cv('sale.recharge.view')) {
            header('location: ' . $this->createPluginWebUrl('sale/enough'));
            exit;
        }
    }
    public function deduct()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function enough()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function recharge()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}