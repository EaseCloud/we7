<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
function sortByCreateTime($a, $b)
{
    if ($a['createtime'] == $b['createtime']) {
        return 0;
    } else {
        return ($a['createtime'] < $b['createtime']) ? 1 : -1;
    }
}
class CommissionMobile extends Plugin
{
    protected $set = null;
    public function __construct()
    {
        parent::__construct('commission');
        $this->set = $this->getSet();
        global $_GPC;
        if ($_GPC['method'] != 'register' && $_GPC['method'] != 'myshop') {
            $openid = m('user')->getOpenid();
            $member = m('member')->getMember($openid);
            if ($member['isagent'] != 1 || $member['status'] != 1) {
                header('location:' . $this->createPluginMobileUrl('commission/register'));
                exit;
            }
        }
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function team()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function customer()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function order()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function withdraw()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function apply()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function shares()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function register()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function myshop()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
    public function log()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
}
