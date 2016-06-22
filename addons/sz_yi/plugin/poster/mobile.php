<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class PosterMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('poster');
	}
	public function build()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}
}