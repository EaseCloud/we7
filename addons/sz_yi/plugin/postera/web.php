<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require_once 'model.php';

class PosteraWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('postera');
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

	public function set()
	{
		$this->_exec_plugin(__FUNCTION__);
	}
}
