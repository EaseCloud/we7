<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class TmessageWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('tmessage');
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}