<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class SupplierWeb extends Plugin
{
	protected $set = null;

	public function __construct()
	{
		parent::__construct('supplier');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;
		if (cv('supplier')) {
			header('location: ' . $this->createPluginWebUrl('supplier/supplier'));
			exit;
		} else if (cv('supplier')) {
			header('location: ' . $this->createPluginWebUrl('supplier/supplier_apply'));
			exit;
		} else if (cv('supplier')) {
			header('location: ' . $this->createPluginWebUrl('supplier/supplier_finish'));
			exit;
		}
	}
    public function upgrade()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function supplier()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function supplier_apply()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function supplier_finish()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function supplier_for()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
    public function supplier_add()
    {
        $this->_exec_plugin(__FUNCTION__);
    }
}