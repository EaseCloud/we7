<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
require_once 'model.php';
class SystemWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('system');
    }
    public function index()
    {
        header('location: ' . $this->createPluginWebUrl('system/clear'));
        exit;
    }
    public function clear()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function transfer()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function copyright()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function backup()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function commission()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}