<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'plugin/plugin_processor.php';

class CouponProcessor extends PluginProcessor
{
	public function __construct()
	{
		parent::__construct('coupon');
	}

	public function respond($_var_0 = null)
	{
		global $_W;
		$_var_1 = $_var_0->message;
		$_var_2 = $_var_0->message['content'];
		$_var_3 = strtolower($_var_1['msgtype']);
		$_var_4 = strtolower($_var_1['event']);
		if ($_var_3 == 'text' || $_var_4 == 'click') {
			return $this->respondText($_var_0);
		}
		return $this->responseEmpty();
	}

	private function responseEmpty()
	{
		ob_clean();
		ob_start();
		echo '';
		ob_flush();
		ob_end_flush();
		exit(0);
	}

	function replaceCoupon($_var_5, $_var_6, $_var_7, $_var_8)
	{
		$_var_9 = array('pwdask' => '请输入优惠券口令: ', 'pwdfail' => '很抱歉，您猜错啦，继续猜~', 'pwdsuc' => '恭喜你，猜中啦！优惠券已发到您账户了! ', 'pwdfull' => '很抱歉，您已经没有机会啦~ ', 'pwdown' => '您已经参加过啦,等待下次活动吧~', 'pwdexit' => '0', 'pwdexitstr' => '好的，等待您下次来玩!');
		foreach ($_var_9 as $_var_10 => $_var_11) {
			if (empty($_var_5[$_var_10])) {
				$_var_5[$_var_10] = $_var_11;
			} else {
				$_var_5[$_var_10] = str_replace('[nickname]', $_var_6['nickname'], $_var_5[$_var_10]);
				$_var_5[$_var_10] = str_replace('[couponname]', $_var_5['couponname'], $_var_5[$_var_10]);
				$_var_5[$_var_10] = str_replace('[times]', $_var_7, $_var_5[$_var_10]);
				$_var_5[$_var_10] = str_replace('[lasttimes]', $_var_8, $_var_5[$_var_10]);
			}
		}
		return $_var_5;
	}

	function getGuess($_var_5, $_var_12)
	{
		global $_W;
		$_var_8 = 1;
		$_var_7 = 0;
		$_var_13 = pdo_fetch('select id,times from ' . tablename('sz_yi_coupon_guess') . ' where couponid=:couponid and openid=:openid and pwdkey=:pwdkey and uniacid=:uniacid limit 1 ', array(':couponid' => $_var_5['id'], ':openid' => $_var_12, ':pwdkey' => $_var_5['pwdkey'], ':uniacid' => $_W['uniacid']));
		if ($_var_5['pwdtimes'] > 0) {
			$_var_7 = $_var_13['times'];
			$_var_8 = $_var_5['pwdtimes'] - intval($_var_7);
			if ($_var_8 <= 0) {
				$_var_8 = 0;
			}
		}
		return array('times' => $_var_7, 'lasttimes' => $_var_8);
	}

	function respondText($_var_0)
	{
		global $_W;
		@session_start();
		$_var_2 = $_var_0->message['content'];
		$_var_12 = $_var_0->message['from'];
		$_var_6 = m('member')->getMember($_var_12);
		$_var_14 = $_var_2;
		if (isset($_SESSION['sz_yi_coupon_key'])) {
			$_var_14 = $_SESSION['sz_yi_coupon_key'];
		} else {
			$_SESSION['sz_yi_coupon_key'] = $_var_2;
		}
		$_var_5 = pdo_fetch('select id,couponname,pwdkey,pwdask,pwdsuc,pwdfail,pwdfull,pwdtimes,pwdurl,pwdwords,pwdown,pwdexit,pwdexitstr from ' . tablename('sz_yi_coupon') . ' where pwdkey=:pwdkey and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':pwdkey' => $_var_14));
		$_var_15 = explode(',', $_var_5['pwdwords']);
		if (empty($_var_5)) {
			$_var_0->endContext();
			unset($_SESSION['sz_yi_coupon_key']);
			return $this->responseEmpty();
		}
		if (!$_var_0->inContext) {
			$_var_16 = pdo_fetch('select id,times from ' . tablename('sz_yi_coupon_guess') . ' where couponid=:couponid and openid=:openid and pwdkey=:pwdkey and ok=1 and uniacid=:uniacid limit 1 ', array(':couponid' => $_var_5['id'], ':openid' => $_var_12, ':pwdkey' => $_var_5['pwdkey'], ':uniacid' => $_W['uniacid']));
			if (!empty($_var_16)) {
				$_var_13 = $this->getGuess($_var_5, $_var_12);
				$_var_5 = $this->replaceCoupon($_var_5, $_var_6, $_var_13['times'], $_var_13['lasttimes']);
				$_var_0->endContext();
				unset($_SESSION['sz_yi_coupon_key']);
				return $_var_0->respText($_var_5['pwdown']);
			}
			$_var_13 = $this->getGuess($_var_5, $_var_12);
			$_var_5 = $this->replaceCoupon($_var_5, $_var_6, $_var_13['times'], $_var_13['lasttimes']);
			if ($_var_13['lasttimes'] <= 0) {
				$_var_0->endContext();
				unset($_SESSION['sz_yi_coupon_key']);
				return $_var_0->respText($_var_5['pwdfull']);
			}
			$_var_0->beginContext();
			return $_var_0->respText($_var_5['pwdask']);
		} else {
			if ($_var_2 == $_var_5['pwdexit']) {
				unset($_SESSION['sz_yi_coupon_key']);
				$_var_0->endContext();
				$_var_13 = $this->getGuess($_var_5, $_var_12);
				$_var_5 = $this->replaceCoupon($_var_5, $_var_6, $_var_13['times'], $_var_13['lasttimes']);
				return $_var_0->respText($_var_5['pwdexitstr']);
			}
			$_var_13 = pdo_fetch('select id,times from ' . tablename('sz_yi_coupon_guess') . ' where couponid=:couponid and openid=:openid and pwdkey=:pwdkey and uniacid=:uniacid limit 1 ', array(':couponid' => $_var_5['id'], ':openid' => $_var_12, ':pwdkey' => $_var_5['pwdkey'], ':uniacid' => $_W['uniacid']));
			$_var_17 = in_array($_var_2, $_var_15);
			if (empty($_var_13)) {
				$_var_13 = array('uniacid' => $_W['uniacid'], 'couponid' => $_var_5['id'], 'openid' => $_var_12, 'times' => 1, 'pwdkey' => $_var_5['pwdkey'], 'ok' => $_var_17 ? 1 : 0);
				pdo_insert('sz_yi_coupon_guess', $_var_13);
			} else {
				pdo_update('sz_yi_coupon_guess', array('times' => $_var_13['times'] + 1, 'ok' => $_var_17 ? 1 : 0), array('id' => $_var_13['id']));
			}
			$_var_18 = time();
			if ($_var_17) {
				$_var_19 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_12, 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $_var_5['id'], 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => $_var_18, 'getfrom' => 5);
				pdo_insert('sz_yi_coupon_log', $_var_19);
				$_var_20 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_12, 'couponid' => $_var_5['id'], 'gettype' => 5, 'gettime' => $_var_18);
				pdo_insert('sz_yi_coupon_data', $_var_20);
				unset($_SESSION['sz_yi_coupon_key']);
				$_var_0->endContext();
				$_var_21 = $this->model->getSet();
				$_var_22 = $this->model->getCoupon($_var_5['id']);
				$this->model->sendMessage($_var_22, 1, $_var_6, $_var_21['templateid']);
				$_var_13 = $this->getGuess($_var_5, $_var_12);
				$_var_5 = $this->replaceCoupon($_var_5, $_var_6, $_var_13['times'], $_var_13['lasttimes']);
				return $_var_0->respText($_var_5['pwdsuc']);
			} else {
				$_var_13 = $this->getGuess($_var_5, $_var_12);
				$_var_5 = $this->replaceCoupon($_var_5, $_var_6, $_var_13['times'], $_var_13['lasttimes']);
				if ($_var_13['lasttimes'] <= 0) {
					$_var_0->endContext();
					unset($_SESSION['sz_yi_coupon_key']);
					return $_var_0->respText($_var_5['pwdfull']);
				}
				return $_var_0->respText($_var_5['pwdfail']);
			}
		}
	}
}
