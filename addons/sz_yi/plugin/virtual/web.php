<?php
//Ü¿ÖÚÉÌ³Ç QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require_once 'model.php';
class VirtualWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('virtual');
    }
    public function index()
    {
        if (cv('virtual.temp')) {
            header('location: ' . $this->createPluginWebUrl('virtual/temp'));
            exit;
        } else if (cv('virtual.category')) {
            header('location: ' . $this->createPluginWebUrl('virtual/category'));
            exit;
        }
    }
    public function temp()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function data()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function category()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function import()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function export()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function set()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}