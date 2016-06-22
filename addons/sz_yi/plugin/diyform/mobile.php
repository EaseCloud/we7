<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
class DiyformMobile extends Plugin
{
    public function __construct()
    {
        parent::__construct('diyform');
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__, false);
    }
}