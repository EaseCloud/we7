<?php
//Ü¿ÖÚÉÌ³Ç QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class VirtualMobile extends Plugin
{
    public function __construct()
    {
        parent::__construct('virtual');
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
}