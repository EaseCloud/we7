<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
if (!class_exists('CouponModel')) {
	class CouponModel extends PluginModel
	{
		function get_last_count($_var_0 = 0)
		{
			global $_W;
			$_var_1 = pdo_fetch('SELECT id,total FROM ' . tablename('sz_yi_coupon') . ' WHERE id=:id and uniacid=:uniacid ', array(':id' => $_var_0, ':uniacid' => $_W['uniacid']));
			if (empty($_var_1)) {
				return 0;
			}
			if ($_var_1['total'] == -1) {
				return -1;
			}
			$_var_2 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_coupon_data') . ' where couponid=:couponid and uniacid=:uniacid limit 1', array(':couponid' => $_var_0, ':uniacid' => $_W['uniacid']));
			return $_var_1['total'] - $_var_2;
		}

		function creditshop($_var_3 = 0)
		{
			global $_W, $_GPC;
			$_var_4 = p('creditshop');
			if (!$_var_4) {
				return;
			}
			$_var_5 = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_creditshop_log') . ' WHERE `id`=:id and `uniacid`=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $_var_3));
			if (!empty($_var_5)) {
				$_var_6 = m('member')->getMember($_var_5['openid']);
				$_var_7 = $_var_4->getGoods($_var_5['couponid'], $_var_6);
				$_var_8 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_5['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $_var_5['couponid'], 'status' => 1, 'paystatus' => $_var_7['money'] > 0 ? 0 : -1, 'creditstatus' => $_var_7['credit'] > 0 ? 0 : -1, 'createtime' => time(), 'getfrom' => 2);
				pdo_insert('sz_yi_coupon_log', $_var_8);
				$_var_9 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_5['openid'], 'couponid' => $_var_5['couponid'], 'gettype' => 2, 'gettime' => time());
				pdo_insert('sz_yi_coupon_data', $_var_9);
				$_var_1 = pdo_fetch('select * from ' . tablename('sz_yi_coupon') . ' where id=:id limit 1', array(':id' => $_var_5['couponid']));
				$_var_1 = $this->setCoupon($_var_1, time());
				$_var_10 = $this->getSet();
				$this->sendMessage($_var_1, 1, $_var_6, $_var_10['templateid']);
				pdo_update('sz_yi_creditshop_log', array('status' => 3), array('id' => $_var_3));
			}
		}

		function poster($_var_6, $_var_0, $_var_11)
		{
			global $_W, $_GPC;
			$_var_12 = p('poster');
			if (!$_var_12) {
				return;
			}
			$_var_1 = $this->getCoupon($_var_0);
			if (empty($_var_1)) {
				return;
			}
			for ($_var_13 = 1; $_var_13 <= $_var_11; $_var_13++) {
				$_var_8 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_6['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $_var_0, 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => 3);
				pdo_insert('sz_yi_coupon_log', $_var_8);
				$_var_9 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_6['openid'], 'couponid' => $_var_0, 'gettype' => 3, 'gettime' => time());
				pdo_insert('sz_yi_coupon_data', $_var_9);
			}
			$_var_10 = $this->getSet();
			$this->sendMessage($_var_1, $_var_11, $_var_6, $_var_10['templateid']);
		}

		function payResult($_var_14)
		{
			global $_W;
			if (empty($_var_14)) {
				return error(-1);
			}
			$_var_5 = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_coupon_log') . ' WHERE `logno`=:logno and `uniacid`=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $_var_14));
			if (empty($_var_5)) {
				return error(-1, '服务器错误!');
			}
			if ($_var_5['status'] >= 1) {
				return true;
			}
			$_var_1 = pdo_fetch('select * from ' . tablename('sz_yi_coupon') . ' where id=:id limit 1', array(':id' => $_var_5['couponid']));
			$_var_1 = $this->setCoupon($_var_1, time());
			if (empty($_var_1['gettype'])) {
				return error(-1, '无法领取');
			}
			if ($_var_1['total'] != -1) {
				if ($_var_1['total'] <= 0) {
					return error(-1, '优惠券数量不足');
				}
			}
			if (!$_var_1['canget']) {
				return error(-1, '您已超出领取次数限制');
			}
			if (empty($_var_5['status'])) {
				$_var_15 = array();
				if ($_var_1['credit'] > 0 && empty($_var_5['creditstatus'])) {
					m('member')->setCredit($_var_5['openid'], 'credit1', -$_var_1['credit'], "购买优惠券扣除积分 {$_var_1['credit']}");
					$_var_15['creditstatus'] = 1;
				}
				if ($_var_1['money'] > 0 && empty($_var_5['paystatus'])) {
					if ($_var_1['paytype'] == 0) {
						m('member')->setCredit($_var_5['openid'], 'credit2', -$_var_1['money'], "购买优惠券扣除余额 {$_var_1['money']}");
					}
					$_var_15['paystatus'] = 1;
				}
				$_var_15['status'] = 1;
				pdo_update('sz_yi_coupon_log', $_var_15, array('id' => $_var_5['id']));
				$_var_9 = array('uniacid' => $_W['uniacid'], 'openid' => $_var_5['openid'], 'couponid' => $_var_5['couponid'], 'gettype' => $_var_5['getfrom'], 'gettime' => time());
				pdo_insert('sz_yi_coupon_data', $_var_9);
				$_var_6 = m('member')->getMember($_var_5['openid']);
				$_var_10 = $this->getSet();
				$this->sendMessage($_var_1, 1, $_var_6, $_var_10['templateid']);
			}
			$_var_16 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member';
			if ($_var_1['coupontype'] == 0) {
				$_var_16 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=shop&p=list';
			} else {
				$_var_16 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member&p=recharge';
			}
			if (strexists($_var_16, '/addons/sz_yi/plugin/coupon/core/mobile/')) {
				$_var_16 = str_replace('/addons/sz_yi/plugin/coupon/core/mobile/', '/', $_var_16);
			}
			if (strexists($_var_16, '/addons/sz_yi/')) {
				$_var_16 = str_replace('/addons/sz_yi/', '/', $_var_16);
			}
			return array('url' => $_var_16);
		}

		function sendMessage($_var_1, $_var_17, $_var_6, $_var_18 = '', $_var_19 = null)
		{
			global $_W;
			$_var_20 = array();
			$_var_21 = str_replace('[nickname]', $_var_6['nickname'], $_var_1['resptitle']);
			$_var_22 = str_replace('[nickname]', $_var_6['nickname'], $_var_1['respdesc']);
			$_var_21 = str_replace('[total]', $_var_17, $_var_21);
			$_var_22 = str_replace('[total]', $_var_17, $_var_22);
			$_var_16 = empty($_var_1['respurl']) ? $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=coupon&method=my' : $_var_1['respurl'];
			if (!empty($_var_1['resptitle'])) {
				$_var_20[] = array('title' => urlencode($_var_21), 'description' => urlencode($_var_22), 'url' => $_var_16, 'picurl' => tomedia($_var_1['respthumb']));
			}
			if (!empty($_var_20)) {
				$_var_23 = m('message')->sendNews($_var_6['openid'], $_var_20, $_var_19);
				if (is_error($_var_23)) {
					$_var_24 = array('keyword1' => array('value' => $_var_21, 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_22, 'color' => '#73a68d'));
					if (!empty($_var_18)) {
						m('message')->sendTplNotice($_var_6['openid'], $_var_18, $_var_24, $_var_16);
					}
				}
			}
		}

		function sendBackMessage($_var_25, $_var_1, $_var_26)
		{
			global $_W;
			if (empty($_var_26)) {
				return;
			}
			$_var_10 = $this->getSet();
			$_var_18 = $_var_10['templateid'];
			$_var_27 = "您的优惠券【{$_var_1['couponname']}】已返利 ";
			$_var_28 = '';
			if (isset($_var_26['credit'])) {
				$_var_28 .= " {$_var_26['credit']}个积分";
			}
			if (isset($_var_26['money'])) {
				if (!empty($_var_28)) {
					$_var_28 .= '，';
				}
				$_var_28 .= "{$_var_26['money']}元余额";
			}
			if (isset($_var_26['redpack'])) {
				if (!empty($_var_28)) {
					$_var_28 .= '，';
				}
				$_var_28 .= "{$_var_26['redpack']}元现金";
			}
			$_var_27 .= $_var_28;
			$_var_27 .= '，请查看您的账户，谢谢!';
			$_var_24 = array('keyword1' => array('value' => '优惠券返利', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_27, 'color' => '#73a68d'));
			$_var_16 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member';
			if (strexists($_var_16, '/addons/sz_yi/plugin/coupon/core/mobile/')) {
				$_var_16 = str_replace('/addons/sz_yi/plugin/coupon/core/mobile/', '/', $_var_16);
			}
			if (strexists($_var_16, '/addons/sz_yi/')) {
				$_var_16 = str_replace('/addons/sz_yi/', '/', $_var_16);
			}
			if (!empty($_var_18)) {
				m('message')->sendTplNotice($_var_25, $_var_18, $_var_24, $_var_16);
			} else {
				m('message')->sendCustomNotice($_var_25, $_var_24, $_var_16);
			}
		}

		function sendReturnMessage($_var_25, $_var_1)
		{
			global $_W;
			$_var_10 = $this->getSet();
			$_var_18 = $_var_10['templateid'];
			$_var_24 = array('keyword1' => array('value' => '优惠券退回', 'color' => '#73a68d'), 'keyword2' => array('value' => "您的优惠券【{$_var_1['couponname']}】已退回您的账户，您可以再次使用, 谢谢!", 'color' => '#73a68d'));
			$_var_16 = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=coupon&method=my';
			if (!empty($_var_18)) {
				m('message')->sendTplNotice($_var_25, $_var_18, $_var_24, $_var_16);
			} else {
				m('message')->sendCustomNotice($_var_25, $_var_24, $_var_16);
			}
		}

		function useRechargeCoupon($_var_5)
		{
			global $_W;
			if (empty($_var_5['couponid'])) {
				return;
			}
			$_var_9 = pdo_fetch('select id,openid,couponid,used from ' . tablename('sz_yi_coupon_data') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_5['couponid'], ':uniacid' => $_W['uniacid']));
			if (empty($_var_9)) {
				return;
			}
			if (!empty($_var_9['used'])) {
				return;
			}
			$_var_1 = pdo_fetch('select enough,backcredit,backmoney,backredpack from ' . tablename('sz_yi_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_9['couponid'], ':uniacid' => $_W['uniacid']));
			if (empty($_var_1)) {
				return;
			}
			if ($_var_1['enough'] > 0 && $_var_5['money'] < $_var_1['enough']) {
				return;
			}
			$_var_26 = array();
			$_var_29 = $_var_1['backcredit'];
			if (!empty($_var_29)) {
				if (strexists($_var_29, '%')) {
					$_var_29 = intval(floatval(str_replace('%', '', $_var_29)) / 100 * $_var_5['money']);
				} else {
					$_var_29 = intval($_var_29);
				}
				if ($_var_29 > 0) {
					$_var_26['credit'] = $_var_29;
					m('member')->setCredit($_var_9['openid'], 'credit1', $_var_29, array(0, '充值优惠券返积分'));
				}
			}
			$_var_30 = $_var_1['backmoney'];
			if (!empty($_var_30)) {
				if (strexists($_var_30, '%')) {
					$_var_30 = round(floatval(floatval(str_replace('%', '', $_var_30)) / 100 * $_var_5['money']), 2);
				} else {
					$_var_30 = round(floatval($_var_30), 2);
				}
				if ($_var_30 > 0) {
					$_var_26['money'] = $_var_30;
					m('member')->setCredit($_var_9['openid'], 'credit2', $_var_30, array(0, '充值优惠券返利'));
				}
			}
			$_var_31 = $_var_1['backredpack'];
			if (!empty($_var_31)) {
				if (strexists($_var_31, '%')) {
					$_var_31 = round(floatval(floatval(str_replace('%', '', $_var_31)) / 100 * $_var_5['money']), 2);
				} else {
					$_var_31 = round(floatval($_var_31), 2);
				}
				if ($_var_31 > 0) {
					$_var_26['redpack'] = $_var_31;
					$_var_31 = intval($_var_31 * 100);
					m('finance')->pay($_var_9['openid'], 1, $_var_31, '', '充值优惠券-返现金');
				}
			}
			pdo_update('sz_yi_coupon_data', array('used' => 1, 'usetime' => time(), 'ordersn' => $_var_5['logno']), array('id' => $_var_9['id']));
			$this->sendBackMessage($_var_5['openid'], $_var_1, $_var_26);
		}

		function consumeCouponCount($_var_25, $_var_32 = 0)
		{
			global $_W, $_GPC;
			$_var_33 = time();
			$_var_34 = 'select count(*) from ' . tablename('sz_yi_coupon_data') . ' d ' . '  left join ' . tablename('sz_yi_coupon') . ' c on d.couponid = c.id ' . "  where d.openid=:openid and d.uniacid=:uniacid and  c.coupontype=0 and {$_var_32}>=c.enough and d.used=0 " . " and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<={$_var_33} && c.timeend>={$_var_33}))";
			return pdo_fetchcolumn($_var_34, array(':openid' => $_var_25, ':uniacid' => $_W['uniacid']));
		}

		function useConsumeCoupon($_var_35 = 0)
		{
			global $_W, $_GPC;
			if (empty($_var_35)) {
				return;
			}
			$_var_36 = pdo_fetch('select ordersn,createtime,couponid from ' . tablename('sz_yi_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1', array(':id' => $_var_35, ':uniacid' => $_W['uniacid']));
			if (empty($_var_36)) {
				return;
			}
			$_var_1 = false;
			if (!empty($_var_36['couponid'])) {
				$_var_1 = $this->getCouponByDataID($_var_36['couponid']);
			}
			if (empty($_var_1)) {
				return;
			}
			pdo_update('sz_yi_coupon_data', array('used' => 1, 'usetime' => $_var_36['createtime'], 'ordersn' => $_var_36['ordersn']), array('id' => $_var_36['couponid']));
		}

		function returnConsumeCoupon($_var_36)
		{
			global $_W;
			if (!is_array($_var_36)) {
				$_var_36 = pdo_fetch('select id,openid,ordersn,createtime,couponid,status,finishtime from ' . tablename('sz_yi_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1', array(':id' => intval($_var_36), ':uniacid' => $_W['uniacid']));
			}
			if (empty($_var_36)) {
				return;
			}
			$_var_1 = $this->getCouponByDataID($_var_36['couponid']);
			if (empty($_var_1)) {
				return;
			}
			if (!empty($_var_1['returntype'])) {
				if (!empty($_var_1['used'])) {
					pdo_update('sz_yi_coupon_data', array('used' => 0, 'usetime' => 0, 'ordersn' => ''), array('id' => $_var_36['couponid']));
					$this->sendReturnMessage($_var_36['openid'], $_var_1);
				}
			}
		}

		function backConsumeCoupon($_var_36)
		{
			global $_W;
			if (!is_array($_var_36)) {
				$_var_36 = pdo_fetch('select id,openid,ordersn,createtime,couponid,status,finishtime,virtual from ' . tablename('sz_yi_order') . ' where id=:id and status>=0 and uniacid=:uniacid limit 1', array(':id' => intval($_var_36), ':uniacid' => $_W['uniacid']));
			}
			if (empty($_var_36)) {
				return;
			}
			$_var_0 = $_var_36['couponid'];
			if (empty($_var_0)) {
				return;
			}
			$_var_1 = $this->getCouponByDataID($_var_36['couponid']);
			if (empty($_var_1)) {
				return;
			}
			if (!empty($_var_1['back'])) {
				return;
			}
			$_var_26 = array();
			$_var_37 = false;
			if ($_var_36['status'] == 1 && $_var_1['backwhen'] == 2) {
				$_var_37 = true;
			} else if ($_var_36['status'] == 3) {
				if (!empty($_var_36['virtual'])) {
					$_var_37 = true;
				} else {
					if ($_var_1['backwhen'] == 1) {
						$_var_37 = true;
					} else if ($_var_1['backwhen'] == 0) {
						$_var_37 = true;
						$_var_38 = m('common')->getSysset('trade');
						$_var_39 = intval($_var_38['refunddays']);
						if ($_var_39 > 0) {
							$_var_40 = intval((time() - $_var_36['finishtime']) / 3600 / 24);
							if ($_var_40 <= $_var_39) {
								$_var_37 = false;
							}
						}
					}
				}
			}
			if ($_var_37) {
				$_var_41 = pdo_fetchcolumn('select ifnull( sum(og.realprice),0) from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on o.id=og.orderid ' . ' where o.id=:orderid and o.openid=:openid and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':openid' => $_var_36['openid'], ':orderid' => $_var_36['id']));
				$_var_29 = $_var_1['backcredit'];
				if (!empty($_var_29)) {
					if (strexists($_var_29, '%')) {
						$_var_29 = intval(floatval(str_replace('%', '', $_var_29)) / 100 * $_var_41);
					} else {
						$_var_29 = intval($_var_29);
					}
					if ($_var_29 > 0) {
						$_var_26['credit'] = $_var_29;
						m('member')->setCredit($_var_36['openid'], 'credit1', $_var_29, array(0, '充值优惠券返积分'));
					}
				}
				$_var_30 = $_var_1['backmoney'];
				if (!empty($_var_30)) {
					if (strexists($_var_30, '%')) {
						$_var_30 = round(floatval(floatval(str_replace('%', '', $_var_30)) / 100 * $_var_41), 2);
					} else {
						$_var_30 = round(floatval($_var_30), 2);
					}
					if ($_var_30 > 0) {
						$_var_26['money'] = $_var_30;
						m('member')->setCredit($_var_36['openid'], 'credit2', $_var_30, array(0, '购物优惠券返利'));
					}
				}
				$_var_31 = $_var_1['backredpack'];
				if (!empty($_var_31)) {
					if (strexists($_var_31, '%')) {
						$_var_31 = round(floatval(floatval(str_replace('%', '', $_var_31)) / 100 * $_var_41), 2);
					} else {
						$_var_31 = round(floatval($_var_31), 2);
					}
					if ($_var_31 > 0) {
						$_var_26['redpack'] = $_var_31;
						$_var_31 = intval($_var_31 * 100);
						m('finance')->pay($_var_36['openid'], 1, $_var_31, '', '购物优惠券-返现金');
					}
				}
				pdo_update('sz_yi_coupon_data', array('back' => 1, 'backtime' => time()), array('id' => $_var_36['couponid']));
				$this->sendBackMessage($_var_36['openid'], $_var_1, $_var_26);
			}
		}

		function getCoupon($_var_0 = 0)
		{
			global $_W;
			return pdo_fetch('select * from ' . tablename('sz_yi_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_0, ':uniacid' => $_W['uniacid']));
		}

		function getCouponByDataID($_var_42 = 0)
		{
			global $_W;
			$_var_9 = pdo_fetch('select id,openid,couponid,used,back,backtime from ' . tablename('sz_yi_coupon_data') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_42, ':uniacid' => $_W['uniacid']));
			if (empty($_var_9)) {
				return false;
			}
			$_var_1 = pdo_fetch('select * from ' . tablename('sz_yi_coupon') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_9['couponid'], ':uniacid' => $_W['uniacid']));
			if (empty($_var_1)) {
				return false;
			}
			$_var_1['back'] = $_var_9['back'];
			$_var_1['backtime'] = $_var_9['backtime'];
			$_var_1['used'] = $_var_9['used'];
			$_var_1['usetime'] = $_var_9['usetime'];
			return $_var_1;
		}

		function setCoupon($_var_43, $_var_33, $_var_44 = true)
		{
			global $_W;
			if ($_var_44) {
				$_var_25 = m('user')->getOpenid();
			}
			$_var_43['free'] = false;
			$_var_43['past'] = false;
			$_var_43['thumb'] = tomedia($_var_43['thumb']);
			if ($_var_43['money'] > 0 && $_var_43['credit'] > 0) {
				$_var_43['getstatus'] = 0;
				$_var_43['gettypestr'] = '购买';
			} else if ($_var_43['money'] > 0) {
				$_var_43['getstatus'] = 1;
				$_var_43['gettypestr'] = '购买';
			} else if ($_var_43['credit'] > 0) {
				$_var_43['getstatus'] = 2;
				$_var_43['gettypestr'] = '兑换';
			} else {
				$_var_43['getstatus'] = 3;
				$_var_43['gettypestr'] = '领取';
			}
			$_var_43['timestr'] = "0";
			if (empty($_var_43['timelimit'])) {
				if (!empty($_var_43['timedays'])) {
					$_var_43['timestr'] = 1;
				}
			} else {
				if ($_var_43['timestart'] >= $_var_33) {
					$_var_43['timestr'] = date('Y-m-d', $_var_43['timestart']) . '-' . date('Y-m-d', $_var_43['timeend']);
				} else {
					$_var_43['timestr'] = date('Y-m-d', $_var_43['timeend']);
				}
			}
			$_var_43['css'] = 'deduct';
			if ($_var_43['backtype'] == 0) {
				$_var_43['backstr'] = '立减';
				$_var_43['css'] = 'deduct';
				$_var_43['backpre'] = true;
				$_var_43['_backmoney'] = $_var_43['deduct'];
			} else if ($_var_43['backtype'] == 1) {
				$_var_43['backstr'] = '折';
				$_var_43['css'] = 'discount';
				$_var_43['_backmoney'] = $_var_43['discount'];
			} else if ($_var_43['backtype'] == 2) {
				if (!empty($_var_43['backredpack'])) {
					$_var_43['backstr'] = '返现';
					$_var_43['css'] = 'redpack';
					$_var_43['backpre'] = true;
					$_var_43['_backmoney'] = $_var_43['backredpack'];
				} else if (!empty($_var_43['backmoney'])) {
					$_var_43['backstr'] = '返利';
					$_var_43['css'] = 'money';
					$_var_43['backpre'] = true;
					$_var_43['_backmoney'] = $_var_43['backmoney'];
				} else if (!empty($_var_43['backcredit'])) {
					$_var_43['backstr'] = '返积分';
					$_var_43['css'] = 'credit';
					$_var_43['_backmoney'] = $_var_43['backcredit'];
				}
			}
			if ($_var_44) {
				$_var_43['cangetmax'] = -1;
				$_var_43['canget'] = true;
				if ($_var_43['getmax'] > 0) {
					$_var_45 = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_coupon_data') . ' where couponid=:couponid and openid=:openid and uniacid=:uniacid and gettype=1 limit 1', array(':couponid' => $_var_43['id'], ':openid' => $_var_25, ':uniacid' => $_W['uniacid']));
					$_var_43['cangetmax'] = $_var_43['getmax'] - $_var_45;
					if ($_var_43['cangetmax'] <= 0) {
						$_var_43['cangetmax'] = 0;
						$_var_43['canget'] = false;
					}
				}
			}
			return $_var_43;
		}

		function setMyCoupon($_var_43, $_var_33)
		{
			global $_W;
			$_var_43['past'] = false;
			$_var_43['thumb'] = tomedia($_var_43['thumb']);
			$_var_43['timestr'] = "";
			if (empty($_var_43['timelimit'])) {
				if (!empty($_var_43['timedays'])) {
					$_var_43['timestr'] = date('Y-m-d', $_var_43['gettime'] + $_var_43['timedays'] * 86400);
					if ($_var_43['gettime'] + $_var_43['timedays'] * 86400 < $_var_33) {
						$_var_43['past'] = true;
					}
				}
			} else {
				if ($_var_43['timestart'] >= $_var_33) {
					$_var_43['timestr'] = date('Y-m-d H:i', $_var_43['timestart']) . '-' . date('Y-m-d', $_var_43['timeend']);
				} else {
					$_var_43['timestr'] = date('Y-m-d H:i', $_var_43['timeend']);
				}
				if ($_var_43['timeend'] < $_var_33) {
					$_var_43['past'] = true;
				}
			}
			$_var_43['css'] = 'deduct';
			if ($_var_43['backtype'] == 0) {
				$_var_43['backstr'] = '立减';
				$_var_43['css'] = 'deduct';
				$_var_43['backpre'] = true;
				$_var_43['_backmoney'] = $_var_43['deduct'];
			} else if ($_var_43['backtype'] == 1) {
				$_var_43['backstr'] = '折';
				$_var_43['css'] = 'discount';
				$_var_43['_backmoney'] = $_var_43['discount'];
			} else if ($_var_43['backtype'] == 2) {
				if (!empty($_var_43['backredpack'])) {
					$_var_43['backstr'] = '返现';
					$_var_43['css'] = 'redpack';
					$_var_43['backpre'] = true;
					$_var_43['_backmoney'] = $_var_43['backredpack'];
				} else if (!empty($_var_43['backmoney'])) {
					$_var_43['backstr'] = '返利';
					$_var_43['css'] = 'money';
					$_var_43['backpre'] = true;
					$_var_43['_backmoney'] = $_var_43['backmoney'];
				} else if (!empty($_var_43['backcredit'])) {
					$_var_43['backstr'] = '返积分';
					$_var_43['css'] = 'credit';
					$_var_43['_backmoney'] = $_var_43['backcredit'];
				}
			}
			if ($_var_43['past']) {
				$_var_43['css'] = 'past';
			}
			return $_var_43;
		}

		function setShare()
		{
			global $_W, $_GPC;
			$_var_10 = $this->getSet();
			$_var_25 = m('user')->getOpenid();
			$_var_16 = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&p=coupon&m=sz_yi&do=plugin";
			$_W['shopshare'] = array('title' => $_var_10['title'], 'imgUrl' => tomedia($_var_10['icon']), 'desc' => $_var_10['desc'], 'link' => $_var_16);
			if (p('commission')) {
				$_var_46 = p('commission')->getSet();
				if (!empty($_var_46['level'])) {
					$_var_6 = m('member')->getMember($_var_25);
					if (!empty($_var_6) && $_var_6['status'] == 1 && $_var_6['isagent'] == 1) {
						$_W['shopshare']['link'] = $_var_16 . '&mid=' . $_var_6['id'];
						if (empty($_var_46['become_reg']) && (empty($_var_6['realname']) || empty($_var_6['mobile']))) {
							$_var_47 = true;
						}
					} else if (!empty($_GPC['mid'])) {
						$_W['shopshare']['link'] = $_var_16 . '&mid=' . $_GPC['id'];
					}
				}
			}
		}

		function perms()
		{
			return array('coupon' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('coupon' => array('text' => '优惠券', 'add' => '添加优惠券-log', 'edit' => '编辑优惠券-log', 'delete' => '删除优惠券-log', 'send' => '发放优惠券-log'), 'category' => array('text' => '分类', 'add' => '添加分类-log', 'edit' => '编辑分类-log', 'delete' => '删除分类-log'), 'log' => array('text' => '优惠券记录', 'view' => '查看', 'export' => '导出-log'), 'center' => array('text' => '领券中心设置', 'view' => '查看设置', 'save' => '保存设置-log'), 'set' => array('text' => '基础设置', 'view' => '查看设置', 'save' => '保存设置-log'),)));
		}
	}
}
