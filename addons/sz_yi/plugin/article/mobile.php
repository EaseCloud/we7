<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ArticleMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('article');
	}

	public function index()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function api()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function article()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function report()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}
}