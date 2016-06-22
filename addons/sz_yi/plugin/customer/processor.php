<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'plugin/plugin_processor.php';

class CreditshopProcessor extends PluginProcessor
{
	public function __construct()
	{
		parent::__construct('creditshop');
	}

	public function respond($_var_0 = null)
	{
		global $_W;
		$_var_1 = $_var_0->message;
		$_var_2 = $_var_0->message['from'];
		$_var_3 = $_var_0->message['content'];
		$_var_4 = strtolower($_var_1['msgtype']);
		$_var_5 = strtolower($_var_1['event']);
		if ($_var_4 == 'text' || $_var_5 == 'click') {
			$_var_6 = pdo_fetch('select * from ' . tablename('sz_yi_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_2));
			if (empty($_var_6)) {
				return $this->responseEmpty();
			}
			if (!$_var_0->inContext) {
				$_var_0->beginContext();
				return $_var_0->respText('请输入兑换码:');
			} else if ($_var_0->inContext && is_numeric($_var_3)) {
				$_var_7 = pdo_fetch('select * from ' . tablename('sz_yi_creditshop_log') . ' where eno=:eno and uniacid=:uniacid  limit 1', array(':eno' => $_var_3, ':uniacid' => $_W['uniacid']));
				if (empty($_var_7)) {
					return $_var_0->respText('未找到要兑换码,请重新输入!');
				}
				$_var_8 = $_var_7['id'];
				if (empty($_var_7)) {
					return $_var_0->respText('未找到要兑换码,请重新输入!');
				}
				if (empty($_var_7['status'])) {
					return $_var_0->respText('无效兑换记录!');
				}
				if ($_var_7['status'] >= 3) {
					return $_var_0->respText('此记录已兑换过了!');
				}
				$_var_9 = m('member')->getMember($_var_7['openid']);
				$_var_10 = $this->model->getGoods($_var_7['goodsid'], $_var_9);
				if (empty($_var_10['id'])) {
					return $_var_0->respText('商品记录不存在!');
				}
				if (empty($_var_10['isverify'])) {
					$_var_0->endContext();
					return $_var_0->respText('此商品不支持线下兑换!');
				}
				if (!empty($_var_10['type'])) {
					if ($_var_7['status'] <= 1) {
						return $_var_0->respText('未中奖，不能兑换!');
					}
				}
				if ($_var_10['money'] > 0 && empty($_var_7['paystatus'])) {
					return $_var_0->respText('未支付，无法进行兑换!');
				}
				if ($_var_10['dispatch'] > 0 && empty($_var_7['dispatchstatus'])) {
					return $_var_0->respText('未支付运费，无法进行兑换!');
				}
				$_var_11 = explode(',', $_var_10['storeids']);
				if (!empty($_var_12)) {
					if (!empty($_var_6['storeid'])) {
						if (!in_array($_var_6['storeid'], $_var_12)) {
							return $_var_0->respText('您无此门店的兑换权限!');
						}
					}
				}
				$_var_13 = time();
				pdo_update('sz_yi_creditshop_log', array('status' => 3, 'usetime' => $_var_13, 'verifyopenid' => $_var_2), array('id' => $_var_7['id']));
				$this->model->sendMessage($_var_8);
				$_var_0->endContext();
				return $_var_0->respText('兑换成功!');
			}
		}
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
}
