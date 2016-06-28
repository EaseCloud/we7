<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class CommissionWeb extends Plugin
{
	protected $set = null;

	public function __construct()
	{
		parent::__construct('commission');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;
		if (cv('commission.agent')) {
			header('location: ' . $this->createPluginWebUrl('commission/agent'));
			exit;
		} else if (cv('commission.level')) {
			header('location: ' . $this->createPluginWebUrl('commission/level'));
			exit;
		} else if (cv('commission.apply.view1')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => 1)));
			exit;
		} else if (cv('commission.apply.view2')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => 2)));
			exit;
		} else if (cv('commission.apply.view3')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => 3)));
			exit;
		} else if (cv('commission.apply.view_1')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => -1)));
			exit;
		} else if (cv('commission.increase')) {
			header('location: ' . $this->createPluginWebUrl('commission/increase'));
			exit;
		} else if (cv('commission.notice')) {
			header('location: ' . $this->createPluginWebUrl('commission/notice'));
			exit;
		} else if (cv('commission.cover')) {
			header('location: ' . $this->createPluginWebUrl('commission/cover'));
			exit;
		} else if (cv('commission.set')) {
			header('location: ' . $this->createPluginWebUrl('commission/set'));
			exit;
		}
	}

	public function cover()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function agent()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function level()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function notice()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function increase()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function apply()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function set()
	{
		$this->_exec_plugin(__FUNCTION__);
	}
}
