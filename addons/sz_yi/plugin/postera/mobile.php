<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class PosteraMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('postera');
	}

	public function build()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}
}
