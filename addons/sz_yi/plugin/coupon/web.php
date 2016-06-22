<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require_once 'model.php';

class CouponWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('coupon');
	}

	public function index()
	{
		if (cv('coupon.coupon.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/coupon'));
			exit;
		} else if (cv('coupon.category.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/category'));
			exit;
		} else if (cv('coupon.center.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/center'));
			exit;
		} else if (cv('coupon.set.view')) {
			header('location: ' . $this->createPluginWebUrl('coupon/set'));
			exit;
		}
	}

	public function coupon()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function center()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function category()
	{
		$this->_exec_plugin(__FUNCTION__);
	}

	public function send()
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