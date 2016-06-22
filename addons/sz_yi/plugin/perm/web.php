<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class PermWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('perm');
    }
    public function index()
    {
        if (cv('perm.role')) {
            header('location: ' . $this->createPluginWebUrl('perm/role'));
            exit;
        } else if (cv('perm.user')) {
            header('location: ' . $this->createPluginWebUrl('perm/user'));
            exit;
        } else if (cv('perm.log')) {
            header('location: ' . $this->createPluginWebUrl('perm/log'));
            exit;
        } else if (cv('perm.set')) {
            header('location: ' . $this->createPluginWebUrl('perm/set'));
            exit;
        }
    }
    public function set()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function role()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function user()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function log()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function plugins()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function setting()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}