<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
require_once 'model.php';
class DiyformWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('diyform');
    }
    public function index()
    {
        if (cv('diyform.temp')) {
            header('location: ' . $this->createPluginWebUrl('diyform/temp'));
            exit;
        } else if (cv('diyform.category')) {
            header('location: ' . $this->createPluginWebUrl('diyform/category'));
            exit;
        } else if (cv('diyform.set')) {
            header('location: ' . $this->createPluginWebUrl('diyform/set'));
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