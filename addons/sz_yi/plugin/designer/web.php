<?php
//Ü¿ÖÚÉÌ³Ç QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class DesignerWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('designer');
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function api()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function menu()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}