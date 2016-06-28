<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'plugin/plugin_processor.php';

class PosteraProcessor extends PluginProcessor
{
	public function __construct()
	{
		parent::__construct('postera');
	}

	public function respond($_var_0 = null)
	{
		global $_W;
		$_var_1 = $_var_0->message;
		$_var_2 = strtolower($_var_1['msgtype']);
		$_var_3 = strtolower($_var_1['event']);
		$_var_0->member = $this->model->checkMember($_var_1['from']);
        file_put_contents(IA_ROOT.'/ares.log', $_var_2);
        file_put_contents(IA_ROOT.'/ares1.log', $_var_3);
		if ($_var_2 == 'text' || $_var_3 == 'click') {
			return $this->responseText($_var_0);
		} else if ($_var_2 == 'event') {
			if ($_var_3 == 'scan') {
				return $this->responseScan($_var_0);
			} else if ($_var_3 == 'subscribe') {
				return $this->responseSubscribe($_var_0);
			}
		}
	}

	private function responseText($_var_0)
	{
		global $_W;
		$_var_4 = 4;
		load()->func('communication');
		$_var_5 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=postera&method=build&timestamp=' . time();
		$_var_6 = ihttp_request($_var_5, array('openid' => $_var_0->message['from'], 'content' => urlencode($_var_0->message['content'])), array(), $_var_4);
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

	private function responseDefault($_var_0)
	{
		global $_W;
		return $_var_0->respText('感谢您的关注!');
	}

	private function responseScan($_var_0)
	{
		global $_W;
		$_var_7 = $_var_0->message['from'];
		$_var_8 = $_var_0->message['eventkey'];
		$_var_9 = $_var_0->message['ticket'];
		if (empty($_var_9)) {
			return $this->responseDefault($_var_0);
		}
		$_var_10 = $this->model->getQRByTicket($_var_9);
		if (empty($_var_10)) {
			return $this->responseDefault($_var_0);
		}
		$_var_11 = pdo_fetch('select * from ' . tablename('sz_yi_postera') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_10['posterid'], ':uniacid' => $_W['uniacid']));
		if (empty($_var_11)) {
			return $this->responseDefault($_var_0);
		}
		$_var_12 = m('member')->getMember($_var_10['openid']);
		$this->commission($_var_11, $_var_0->member, $_var_12);
		$_var_5 = trim($_var_11['respurl']);
		if (empty($_var_5)) {
			if ($_var_12['isagent'] == 1 && $_var_12['status'] == 1) {
				$_var_5 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=plugin&p=commission&method=myshop&mid=" . $_var_12['id'];
			} else {
				$_var_5 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=shop&mid=" . $_var_12['id'];
			}
		}
		if (!empty($_var_11['resptitle'])) {
			$_var_13 = array(array('title' => $_var_11['resptitle'], 'description' => $_var_11['respdesc'], 'picurl' => tomedia($_var_11['respthumb']), 'url' => $_var_5));
			return $_var_0->respNews($_var_13);
		}
		return $this->responseEmpty();
	}

	private function responseSubscribe($_var_0)
	{
		global $_W;
		$_var_7 = $_var_0->message['from'];
		$_var_14 = explode('_', $_var_0->message['eventkey']);
		$_var_8 = isset($_var_14[1]) ? $_var_14[1] : '';
		$_var_9 = $_var_0->message['ticket'];
		$_var_15 = $_var_0->member;
		if (empty($_var_9)) {
			return $this->responseDefault($_var_0);
		}
		$_var_10 = $this->model->getQRByTicket($_var_9);
		if (empty($_var_10)) {
			return $this->responseDefault($_var_0);
		}
		$_var_11 = pdo_fetch('select * from ' . tablename('sz_yi_postera') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_10['posterid'], ':uniacid' => $_W['uniacid']));
		if (empty($_var_11)) {
			return $this->responseDefault($_var_0);
		}
		$_var_12 = m('member')->getMember($_var_10['openid']);
		$_var_16 = pdo_fetch('select * from ' . tablename('sz_yi_postera_log') . ' where openid=:openid and posterid=:posterid and uniacid=:uniacid limit 1', array(':openid' => $_var_7, ':posterid' => $_var_11['id'], ':uniacid' => $_W['uniacid']));
		if (empty($_var_16) && $_var_7 != $_var_10['openid']) {
			$_var_16 = array('uniacid' => $_W['uniacid'], 'posterid' => $_var_11['id'], 'openid' => $_var_7, 'from_openid' => $_var_10['openid'], 'subcredit' => $_var_11['subcredit'], 'submoney' => $_var_11['submoney'], 'reccredit' => $_var_11['reccredit'], 'recmoney' => $_var_11['recmoney'], 'createtime' => time());
			pdo_insert('sz_yi_postera_log', $_var_16);
			$_var_16['id'] = pdo_insertid();
			$_var_17 = $_var_11['subpaycontent'];
			if (empty($_var_17)) {
				$_var_17 = '您通过 [nickname] 的推广二维码扫码关注的奖励';
			}
			$_var_17 = str_replace('[nickname]', $_var_12['nickname'], $_var_17);
			$_var_18 = $_var_11['recpaycontent'];
			if (empty($_var_18)) {
				$_var_18 = '推荐 [nickname] 扫码关注的奖励';
			}
			$_var_18 = str_replace('[nickname]', $_var_15['nickname'], $_var_17);
			if ($_var_11['subcredit'] > 0) {
				m('member')->setCredit($_var_7, 'credit1', $_var_11['subcredit'], array(0, '扫码关注积分+' . $_var_11['subcredit']));
			}
			if ($_var_11['submoney'] > 0) {
				$_var_19 = $_var_11['submoney'];
				if ($_var_11['paytype'] == 1) {
					$_var_19 *= 100;
				}
				m('finance')->pay($_var_7, $_var_11['paytype'], $_var_19, '', $_var_17);
			}
			if ($_var_11['reccredit'] > 0) {
				m('member')->setCredit($_var_10['openid'], 'credit1', $_var_11['reccredit'], array(0, '推荐扫码关注积分+' . $_var_11['reccredit']));
			}
			if ($_var_11['recmoney'] > 0) {
				$_var_19 = $_var_11['recmoney'];
				if ($_var_11['paytype'] == 1) {
					$_var_19 *= 100;
				}
				m('finance')->pay($_var_10['openid'], $_var_11['paytype'], $_var_19, '', $_var_18);
			}
			$_var_20 = false;
			$_var_21 = false;
			$_var_22 = p('coupon');
			if ($_var_22) {
				if (!empty($_var_11['reccouponid']) && $_var_11['reccouponnum'] > 0) {
					$_var_23 = $_var_22->getCoupon($_var_11['reccouponid']);
					if (!empty($_var_23)) {
						$_var_20 = true;
					}
				}
				if (!empty($_var_11['subcouponid']) && $_var_11['subcouponnum'] > 0) {
					$_var_24 = $_var_22->getCoupon($_var_11['subcouponid']);
					if (!empty($_var_24)) {
						$_var_21 = true;
					}
				}
			}
			if (!empty($_var_11['subtext'])) {
				$_var_25 = $_var_11['subtext'];
				$_var_25 = str_replace('[nickname]', $_var_15['nickname'], $_var_25);
				$_var_25 = str_replace('[credit]', $_var_11['reccredit'], $_var_25);
				$_var_25 = str_replace('[money]', $_var_11['recmoney'], $_var_25);
				if ($_var_23) {
					$_var_25 = str_replace('[couponname]', $_var_23['couponname'], $_var_25);
					$_var_25 = str_replace('[couponnum]', $_var_11['reccouponnum'], $_var_25);
				}
				if (!empty($_var_11['templateid'])) {
					m('message')->sendTplNotice($_var_10['openid'], $_var_11['templateid'], array('first' => array('value' => '推荐关注奖励到账通知', 'color' => '#4a5077'), 'keyword1' => array('value' => '推荐奖励', 'color' => '#4a5077'), 'keyword2' => array('value' => $_var_25, 'color' => '#4a5077'), 'remark' => array('value' => '
谢谢您对我们的支持！', 'color' => '#4a5077'),), '');
				} else {
					m('message')->sendCustomNotice($_var_10['openid'], $_var_25);
				}
			}
			if (!empty($_var_11['entrytext'])) {
				$_var_26 = $_var_11['entrytext'];
				$_var_26 = str_replace('[nickname]', $_var_12['nickname'], $_var_26);
				$_var_26 = str_replace('[credit]', $_var_11['subcredit'], $_var_26);
				$_var_26 = str_replace('[money]', $_var_11['submoney'], $_var_26);
				if ($_var_24) {
					$_var_26 = str_replace('[couponname]', $_var_24['couponname'], $_var_26);
					$_var_26 = str_replace('[couponnum]', $_var_11['subcouponnum'], $_var_26);
				}
				if (!empty($_var_11['templateid'])) {
					m('message')->sendTplNotice($_var_7, $_var_11['templateid'], array('first' => array('value' => '关注奖励到账通知', 'color' => '#4a5077'), 'keyword1' => array('value' => '关注奖励', 'color' => '#4a5077'), 'keyword2' => array('value' => $_var_26, 'color' => '#4a5077'), 'remark' => array('value' => '
谢谢您对我们的支持！', 'color' => '#4a5077'),), '');
				} else {
					m('message')->sendCustomNotice($_var_7, $_var_26);
				}
			}
			$_var_27 = array();
			if ($_var_20) {
				$_var_27['reccouponid'] = $_var_11['reccouponid'];
				$_var_27['reccouponnum'] = $_var_11['reccouponnum'];
				$_var_22->poster($_var_12, $_var_11['reccouponid'], $_var_11['reccouponnum']);
			}
			if ($_var_21) {
				$_var_27['subcouponid'] = $_var_11['subcouponid'];
				$_var_27['subcouponnum'] = $_var_11['subcouponnum'];
				$_var_22->poster($_var_15, $_var_11['subcouponid'], $_var_11['subcouponnum']);
			}
			if (!empty($_var_27)) {
				pdo_update('sz_yi_postera_log', $_var_27, array('id' => $_var_16['id']));
			}
		}
		$this->commission($_var_11, $_var_15, $_var_12);
		$_var_5 = trim($_var_11['respurl']);
		if (empty($_var_5)) {
			if ($_var_12['isagent'] == 1 && $_var_12['status'] == 1) {
				$_var_5 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=plugin&p=commission&method=myshop&mid=" . $_var_12['id'];
			} else {
				$_var_5 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=shop&mid=" . $_var_12['id'];
			}
		}
		if (!empty($_var_11['resptitle'])) {
			$_var_13 = array(array('title' => $_var_11['resptitle'], 'description' => $_var_11['respdesc'], 'picurl' => tomedia($_var_11['respthumb']), 'url' => $_var_5));
			return $_var_0->respNews($_var_13);
		}
		return $this->responseEmpty();
	}

	private function commission($_var_11, $_var_15, $_var_12)
	{
		$_var_28 = time();
		$_var_29 = p('commission');
		if ($_var_29) {
			$_var_30 = $_var_29->getSet();
			if (!empty($_var_30)) {
				if ($_var_15['isagent'] != 1) {
					if ($_var_12['isagent'] == 1 && $_var_12['status'] == 1) {
						if (!empty($_var_11['bedown'])) {
							if (empty($_var_15['agentid'])) {
								if (empty($_var_15['fixagentid'])) {
									pdo_update('sz_yi_member', array('agentid' => $_var_12['id'], 'childtime' => $_var_28), array('id' => $_var_15['id']));
									$_var_15['agentid'] = $_var_12['id'];
									$_var_29->sendMessage($_var_12['openid'], array('nickname' => $_var_15['nickname'], 'childtime' => $_var_28), TM_COMMISSION_AGENT_NEW);
									$_var_29->upgradeLevelByAgent($_var_12['id']);
								}
							}
							if (!empty($_var_11['beagent'])) {
								$_var_31 = intval($_var_30['become_check']);
								pdo_update('sz_yi_member', array('isagent' => 1, 'status' => $_var_31, 'agenttime' => $_var_28), array('id' => $_var_15['id']));
								if ($_var_31 == 1) {
									$_var_29->sendMessage($_var_15['openid'], array('nickname' => $_var_15['nickname'], 'agenttime' => $_var_28), TM_COMMISSION_BECOME);
									$_var_29->upgradeLevelByAgent($_var_12['id']);
								}
							}
						}
					}
				}
			}
		}
	}
}
