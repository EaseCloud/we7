<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CouponMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('coupon');
	}

	public function index()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function detail()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function my()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function mydetail()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}

	public function util()
	{
		$this->_exec_plugin(__FUNCTION__, false);
	}
}