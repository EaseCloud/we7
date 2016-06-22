<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
define('TM_SUPPLIER_PAY', 'supplier_pay');
if (!class_exists('SupplierModel')) {

	class SupplierModel extends PluginModel
	{
        public $parentAgents = "";

		public function getInfo($_var_20, $_var_21 = null)
		{
			if (empty($_var_21) || !is_array($_var_21)) {
				$_var_21 = array();
			}
			global $_W;
			$_var_0 = $this->getSet();
			$_var_8 = intval($_var_0['level']);
			$_var_22 = m('member')->getMember($_var_20);
			$_var_23 = $this->getLevel($_var_20);
			$_var_24 = time();
			$_var_25 = intval($_var_0['settledays']) * 3600 * 24;
			$_var_26 = 0;
			$_var_27 = 0;
			$_var_28 = 0;
			$_var_29 = 0;
			$_var_30 = 0;
			$_var_31 = 0;
			$_var_32 = 0;
			$_var_33 = 0;
			$_var_34 = 0;
			$_var_35 = 0;
			$_var_36 = 0;
			$_var_37 = 0;
			$_var_38 = 0;
			$_var_39 = 0;
			$_var_40 = 0;
			$_var_41 = 0;
			$_var_42 = 0;
			$_var_43 = 0;
			$_var_44 = 0;
			$_var_45 = 0;
			$_var_46 = 0;
			$_var_47 = 0;
			$_var_48 = 0;
			$_var_49 = 0;
			$_var_50 = 0;
			$_var_51 = 0;
			$_var_52 = 0;
			$_var_53 = 0;
			if ($_var_8 >= 1) {
				if (in_array('ordercount0', $_var_21)) {
					$_var_54 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=0 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					$_var_42 += $_var_54['ordercount'];
					$_var_27 += $_var_54['ordercount'];
					$_var_28 += $_var_54['ordermoney'];
				}
				if (in_array('ordercount', $_var_21)) {
					$_var_54 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=1 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					$_var_45 += $_var_54['ordercount'];
					$_var_29 += $_var_54['ordercount'];
					$_var_30 += $_var_54['ordermoney'];
				}
				if (in_array('ordercount3', $_var_21)) {
					$_var_55 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3 and og.status1>=0 and og.nocommission=0 and o.uniacid=:uniacid  limit 1', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					$_var_48 += $_var_55['ordercount'];
					$_var_31 += $_var_55['ordercount'];
					$_var_32 += $_var_55['ordermoney'];
					$_var_51 += $_var_55['ordermoney'];
				}
				if (in_array('total', $_var_21)) {
					$_var_56 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					foreach ($_var_56 as $_var_57) {
						$_var_9 = iunserializer($_var_57['commissions']);
						$_var_58 = iunserializer($_var_57['commission1']);
						if (empty($_var_9)) {
							$_var_33 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
						} else {
							$_var_33 += isset($_var_9['level1']) ? floatval($_var_9['level1']) : 0;
						}
					}
				}
				if (in_array('ok', $_var_21)) {
					$_var_56 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$_var_24} - o.createtime > {$_var_25}) and og.status1=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					foreach ($_var_56 as $_var_57) {
						$_var_9 = iunserializer($_var_57['commissions']);
						$_var_58 = iunserializer($_var_57['commission1']);
						if (empty($_var_9)) {
							$_var_34 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
						} else {
							$_var_34 += isset($_var_9['level1']) ? $_var_9['level1'] : 0;
						}
					}
				}
				if (in_array('lock', $_var_21)) {
					$_var_59 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . " where o.agentid=:agentid and o.status>=3 and og.nocommission=0 and ({$_var_24} - o.createtime <= {$_var_25})  and og.status1=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					foreach ($_var_59 as $_var_57) {
						$_var_9 = iunserializer($_var_57['commissions']);
						$_var_58 = iunserializer($_var_57['commission1']);
						if (empty($_var_9)) {
							$_var_37 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
						} else {
							$_var_37 += isset($_var_9['level1']) ? $_var_9['level1'] : 0;
						}
					}
				}
				if (in_array('apply', $_var_21)) {
					$_var_60 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					foreach ($_var_60 as $_var_57) {
						$_var_9 = iunserializer($_var_57['commissions']);
						$_var_58 = iunserializer($_var_57['commission1']);
						if (empty($_var_9)) {
							$_var_35 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
						} else {
							$_var_35 += isset($_var_9['level1']) ? $_var_9['level1'] : 0;
						}
					}
				}
				if (in_array('check', $_var_21)) {
					$_var_60 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=2 and og.nocommission=0 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					foreach ($_var_60 as $_var_57) {
						$_var_9 = iunserializer($_var_57['commissions']);
						$_var_58 = iunserializer($_var_57['commission1']);
						if (empty($_var_9)) {
							$_var_36 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
						} else {
							$_var_36 += isset($_var_9['level1']) ? $_var_9['level1'] : 0;
						}
					}
				}
				if (in_array('pay', $_var_21)) {
					$_var_60 = pdo_fetchall('select og.commission1,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid=:agentid and o.status>=3 and og.status1=3 and og.nocommission=0 and o.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']));
					foreach ($_var_60 as $_var_57) {
						$_var_9 = iunserializer($_var_57['commissions']);
						$_var_58 = iunserializer($_var_57['commission1']);
						if (empty($_var_9)) {
							$_var_38 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
						} else {
							$_var_38 += isset($_var_9['level1']) ? $_var_9['level1'] : 0;
						}
					}
				}
				$_var_61 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid=:agentid and isagent=1 and status=1 and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':agentid' => $_var_22['id']), 'id');
				$_var_39 = count($_var_61);
				$_var_26 += $_var_39;
			}
			if ($_var_8 >= 2) {
				if ($_var_39 > 0) {
					if (in_array('ordercount0', $_var_21)) {
						$_var_62 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=0 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
						$_var_43 += $_var_62['ordercount'];
						$_var_27 += $_var_62['ordercount'];
						$_var_28 += $_var_62['ordermoney'];
					}
					if (in_array('ordercount', $_var_21)) {
						$_var_62 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=1 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
						$_var_46 += $_var_62['ordercount'];
						$_var_29 += $_var_62['ordercount'];
						$_var_30 += $_var_62['ordermoney'];
					}
					if (in_array('ordercount3', $_var_21)) {
						$_var_63 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct o.id) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=3 and og.status2>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
						$_var_49 += $_var_63['ordercount'];
						$_var_31 += $_var_63['ordercount'];
						$_var_32 += $_var_63['ordermoney'];
						$_var_52 += $_var_63['ordermoney'];
					}
					if (in_array('total', $_var_21)) {
						$_var_64 = pdo_fetchall('select og.commission2,og.commissions from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_64 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission2']);
							if (empty($_var_9)) {
								$_var_33 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_33 += isset($_var_9['level2']) ? $_var_9['level2'] : 0;
							}
						}
					}
					if (in_array('ok', $_var_21)) {
						$_var_64 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ")  and ({$_var_24} - o.createtime > {$_var_25}) and o.status>=3 and og.status2=0 and og.nocommission=0  and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
						foreach ($_var_64 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission2']);
							if (empty($_var_9)) {
								$_var_34 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_34 += isset($_var_9['level2']) ? $_var_9['level2'] : 0;
							}
						}
					}
					if (in_array('lock', $_var_21)) {
						$_var_65 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ")  and ({$_var_24} - o.createtime <= {$_var_25}) and og.status2=0 and o.status>=3 and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
						foreach ($_var_65 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission2']);
							if (empty($_var_9)) {
								$_var_37 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_37 += isset($_var_9['level2']) ? $_var_9['level2'] : 0;
							}
						}
					}
					if (in_array('apply', $_var_21)) {
						$_var_66 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=3 and og.status2=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_66 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission2']);
							if (empty($_var_9)) {
								$_var_35 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_35 += isset($_var_9['level2']) ? $_var_9['level2'] : 0;
							}
						}
					}
					if (in_array('check', $_var_21)) {
						$_var_67 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=3 and og.status2=2 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_67 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission2']);
							if (empty($_var_9)) {
								$_var_36 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_36 += isset($_var_9['level2']) ? $_var_9['level2'] : 0;
							}
						}
					}
					if (in_array('pay', $_var_21)) {
						$_var_67 = pdo_fetchall('select og.commission2,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where o.agentid in( ' . implode(',', array_keys($_var_61)) . ')  and o.status>=3 and og.status2=3 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_67 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission2']);
							if (empty($_var_9)) {
								$_var_38 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_38 += isset($_var_9['level2']) ? $_var_9['level2'] : 0;
							}
						}
					}
					$_var_68 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where agentid in( ' . implode(',', array_keys($_var_61)) . ') and isagent=1 and status=1 and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']), 'id');
					$_var_40 = count($_var_68);
					$_var_26 += $_var_40;
				}
			}
			if ($_var_8 >= 3) {
				if ($_var_40 > 0) {
					if (in_array('ordercount0', $_var_21)) {
						$_var_69 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=0 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
						$_var_44 += $_var_69['ordercount'];
						$_var_27 += $_var_69['ordercount'];
						$_var_28 += $_var_69['ordermoney'];
					}
					if (in_array('ordercount', $_var_21)) {
						$_var_69 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=1 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
						$_var_47 += $_var_69['ordercount'];
						$_var_29 += $_var_69['ordercount'];
						$_var_30 += $_var_69['ordermoney'];
					}
					if (in_array('ordercount3', $_var_21)) {
						$_var_70 = pdo_fetch('select sum(og.realprice) as ordermoney,count(distinct og.orderid) as ordercount from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=3 and og.status3>=0 and og.nocommission=0 and o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
						$_var_50 += $_var_70['ordercount'];
						$_var_31 += $_var_70['ordercount'];
						$_var_32 += $_var_70['ordermoney'];
						$_var_53 += $_var_69['ordermoney'];
					}
					if (in_array('total', $_var_21)) {
						$_var_71 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_71 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission3']);
							if (empty($_var_9)) {
								$_var_33 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_33 += isset($_var_9['level3']) ? $_var_9['level3'] : 0;
							}
						}
					}
					if (in_array('ok', $_var_21)) {
						$_var_71 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ")  and ({$_var_24} - o.createtime > {$_var_25}) and o.status>=3 and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
						foreach ($_var_71 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission3']);
							if (empty($_var_9)) {
								$_var_34 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_34 += isset($_var_9['level3']) ? $_var_9['level3'] : 0;
							}
						}
					}
					if (in_array('lock', $_var_21)) {
						$_var_72 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ")  and o.status>=3 and ({$_var_24} - o.createtime > {$_var_25}) and og.status3=0  and og.nocommission=0 and o.uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
						foreach ($_var_72 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission3']);
							if (empty($_var_9)) {
								$_var_37 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_37 += isset($_var_9['level3']) ? $_var_9['level3'] : 0;
							}
						}
					}
					if (in_array('apply', $_var_21)) {
						$_var_73 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=3 and og.status3=1 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_73 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission3']);
							if (empty($_var_9)) {
								$_var_35 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_35 += isset($_var_9['level3']) ? $_var_9['level3'] : 0;
							}
						}
					}
					if (in_array('check', $_var_21)) {
						$_var_74 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=3 and og.status3=2 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_74 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission3']);
							if (empty($_var_9)) {
								$_var_36 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_36 += isset($_var_9['level3']) ? $_var_9['level3'] : 0;
							}
						}
					}
					if (in_array('pay', $_var_21)) {
						$_var_74 = pdo_fetchall('select og.commission3,og.commissions  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join  ' . tablename('sz_yi_order') . ' o on o.id = og.orderid' . ' where o.agentid in( ' . implode(',', array_keys($_var_68)) . ')  and o.status>=3 and og.status3=3 and og.nocommission=0 and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
						foreach ($_var_74 as $_var_57) {
							$_var_9 = iunserializer($_var_57['commissions']);
							$_var_58 = iunserializer($_var_57['commission3']);
							if (empty($_var_9)) {
								$_var_38 += isset($_var_58['level' . $_var_23['id']]) ? $_var_58['level' . $_var_23['id']] : $_var_58['default'];
							} else {
								$_var_38 += isset($_var_9['level3']) ? $_var_9['level3'] : 0;
							}
						}
					}
					$_var_75 = pdo_fetchall('select id from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and agentid in( ' . implode(',', array_keys($_var_68)) . ') and isagent=1 and status=1', array(':uniacid' => $_W['uniacid']), 'id');
					$_var_41 = count($_var_75);
					$_var_26 += $_var_41;
				}
			}
			$_var_22['agentcount'] = $_var_26;
			$_var_22['ordercount'] = $_var_29;
			$_var_22['ordermoney'] = $_var_30;
			$_var_22['order1'] = $_var_45;
			$_var_22['order2'] = $_var_46;
			$_var_22['order3'] = $_var_47;
			$_var_22['ordercount3'] = $_var_31;
			$_var_22['ordermoney3'] = $_var_32;
			$_var_22['order13'] = $_var_48;
			$_var_22['order23'] = $_var_49;
			$_var_22['order33'] = $_var_50;
			$_var_22['order13money'] = $_var_51;
			$_var_22['order23money'] = $_var_52;
			$_var_22['order33money'] = $_var_53;
			$_var_22['ordercount0'] = $_var_27;
			$_var_22['ordermoney0'] = $_var_28;
			$_var_22['order10'] = $_var_42;
			$_var_22['order20'] = $_var_43;
			$_var_22['order30'] = $_var_44;
			$_var_22['commission_total'] = round($_var_33, 2);
			$_var_22['commission_ok'] = round($_var_34, 2);
			$_var_22['commission_lock'] = round($_var_37, 2);
			$_var_22['commission_apply'] = round($_var_35, 2);
			$_var_22['commission_check'] = round($_var_36, 2);
			$_var_22['commission_pay'] = round($_var_38, 2);
			$_var_22['level1'] = $_var_39;
			$_var_22['level1_agentids'] = $_var_61;
			$_var_22['level2'] = $_var_40;
			$_var_22['level2_agentids'] = $_var_68;
			$_var_22['level3'] = $_var_41;
			$_var_22['level3_agentids'] = $_var_75;
			$_var_22['agenttime'] = date('Y-m-d H:i', $_var_22['agenttime']);
			return $_var_22;
		}

		
		function perms()
		{
			return array('commission' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('cover' => array('text' => '入口设置'), 'agent' => array('text' => '分销商', 'view' => '浏览', 'check' => '审核-log', 'edit' => '修改-log', 'agentblack' => '黑名单操作-log', 'delete' => '删除-log', 'user' => '查看下线', 'order' => '查看推广订单(还需有订单权限)', 'changeagent' => '设置分销商'), 'level' => array('text' => '分销商等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'apply' => array('text' => '佣金审核', 'view1' => '浏览待审核', 'view2' => '浏览已审核', 'view3' => '浏览已打款', 'view_1' => '浏览无效', 'export1' => '导出待审核-log', 'export2' => '导出已审核-log', 'export3' => '导出已打款-log', 'export_1' => '导出无效-log', 'check' => '审核-log', 'pay' => '打款-log', 'cancel' => '重新审核-log'), 'notice' => array('text' => '通知设置-log'), 'increase' => array('text' => '分销商趋势图'), 'changecommission' => array('text' => '修改佣金-log'), 'set' => array('text' => '基础设置-log'))));
		}
		public function allPerms()
		{
			$perms = array('shop' => array('text' => '商城管理', 'child' => array('goods' => array('text' => '商品', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'category' => array('text' => '商品分类', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'dispatch' => array('text' => '配送方式', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'adv' => array('text' => '幻灯片', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'notice' => array('text' => '公告', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'comment' => array('text' => '评价', 'view' => '浏览', 'add' => '添加评论-log', 'edit' => '回复-log', 'delete' => '删除-log'),)), 'member' => array('text' => '会员管理', 'child' => array('member' => array('text' => '会员', 'view' => '浏览', 'edit' => '修改-log', 'delete' => '删除-log', 'export' => '导出-log'), 'group' => array('text' => '会员组', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'level' => array('text' => '会员等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'))), 'order' => array('text' => '订单管理', 'child' => array('view' => array('text' => '浏览', 'status_1' => '浏览关闭订单', 'status0' => '浏览待付款订单', 'status1' => '浏览已付款订单', 'status2' => '浏览已发货订单', 'status3' => '浏览完成的订单', 'status4' => '浏览退货申请订单', 'status5' => '浏览已退货订单','status9' => '浏览提现申请'), 'op' => array('text' => '操作', 'pay' => '确认付款-log', 'send' => '发货-log', 'sendcancel' => '取消发货-log', 'finish' => '确认收货(快递单)-log', 'verify' => '确认核销(核销单)-log', 'fetch' => '确认取货(自提单)-log', 'close' => '关闭订单-log', 'refund' => '退货处理-log', 'export' => '导出订单-log', 'changeprice' => '订单改价-log'))), 'finance' => array('text' => '财务管理', 'child' => array('recharge' => array('text' => '充值', 'view' => '浏览', 'credit1' => '充值积分-log', 'credit2' => '充值余额-log', 'refund' => '充值退款-log', 'export' => '导出充值记录-log'), 'withdraw' => array('text' => '提现', 'view' => '浏览', 'withdraw' => '提现-log', 'export' => '导出提现记录-log'), 'downloadbill' => array('text' => '下载对账单'),)), 'statistics' => array('text' => '数据统计', 'child' => array('view' => array('text' => '浏览权限', 'sale' => '销售指标', 'sale_analysis' => '销售统计', 'order' => '订单统计', 'goods' => '商品销售统计', 'goods_rank' => '商品销售排行', 'goods_trans' => '商品销售转化率', 'member_cost' => '会员消费排行', 'member_increase' => '会员增长趋势'), 'export' => array('text' => '导出', 'sale' => '导出销售统计-log', 'order' => '导出订单统计-log', 'goods' => '导出商品销售统计-log', 'goods_rank' => '导出商品销售排行-log', 'goods_trans' => '商品销售转化率-log', 'member_cost' => '会员消费排行-log'),)), 'sysset' => array('text' => '系统设置', 'child' => array('view' => array('text' => '浏览', 'shop' => '商城设置', 'follow' => '引导及分享设置', 'notice' => '模板消息设置', 'trade' => '交易设置', 'pay' => '支付方式设置', 'template' => '模板设置', 'member' => '会员设置', 'category' => '分类层级设置', 'contact' => '联系方式设置'), 'save' => array('text' => '修改', 'shop' => '修改商城设置-log', 'follow' => '修改引导及分享设置-log', 'notice' => '修改模板消息设置-log', 'trade' => '修改交易设置-log', 'pay' => '修改支付方式设置-log', 'template' => '模板设置-log', 'member' => '会员设置-log', 'category' => '分类层级设置-log', 'contact' => '联系方式设置-log'))),);
			$plugins = m('plugin')->getAll();
			foreach ($plugins as $plugin) {
				$instance = p($plugin['identity']);
				if ($instance) {
					if (method_exists($instance, 'perms')) {
						$plugin_perms = $instance->perms();
						$perms = array_merge($perms, $plugin_perms);
					}
				}
			}
			return $perms;
		}

		public function sendMessage($openid = '', $data = array(), $_var_151 = '')
		{
			$_var_22 = m('member')->getMember($openid);
			if ($_var_151 == TM_SUPPLIER_PAY) {
				$_var_155 = '恭喜您，您的提现将通过 [提现方式] 转账提现金额为[金额]已在[时间]转账到您的账号，敬请查看';
				$_var_155 = str_replace('[时间]', date('Y-m-d H:i:s', time()), $_var_155);
				$_var_155 = str_replace('[金额]', $data['money'], $_var_155);
				$_var_155 = str_replace('[提现方式]', $data['type'], $_var_155);
				$_var_156 = array('keyword1' => array('value' => '供应商打款通知', 'color' => '#73a68d'), 'keyword2' => array('value' => $_var_155, 'color' => '#73a68d'));
				/*if (!empty($_var_153)) {
					m('message')->sendTplNotice($openid, $_var_153, $_var_156);
				} else {*/
				m('message')->sendCustomNotice($openid, $_var_156);
				//}
			}
		}
	}
}
