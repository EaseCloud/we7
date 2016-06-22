<?php
//Ü¿ÖÚÉÌ³Ç QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require_once 'model.php';
class PosterWeb extends Plugin
{
    public function __construct()
    {
        parent::__construct('poster');
    }
    public function index()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function manage()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function log()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function scan()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function set()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}