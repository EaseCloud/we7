<?php
global $_W, $_GPC;

$agentlevels = $this->model->getLevels();
$status      = intval($_GPC['status']);
empty($status) && $status = 1;
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if ($operation == 'display') {
	if ($status == -1) {
		ca('commission.apply.view_1');
	} else {
		ca('commission.apply.view' . $status);
	}
	$level = $this->set['level'];
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and a.uniacid=:uniacid and a.status=:status';
	$params = array(':uniacid' => $_W['uniacid'], ':status' => $status);
	if (!empty($_GPC['applyno'])) {
		$_GPC['applyno'] = trim($_GPC['applyno']);
		$condition .= ' and a.applyno like :applyno';
		$params[':applyno'] = "%{$_GPC['applyno']}%";
	}
	if (!empty($_GPC['realname'])) {
		$_GPC['realname'] = trim($_GPC['realname']);
		$condition .= ' and (m.realname like :realname or m.nickname like :realname or m.mobile like :realname)';
		$params[':realname'] = "%{$_GPC['realname']}%";
	}
	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	}
	$timetype = $_GPC['timetype'];
	if (!empty($_GPC['timetype'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']);
		if (!empty($timetype)) {
			$condition .= " AND a.{$timetype} >= :starttime AND a.{$timetype}  <= :endtime ";
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
	}
	if (!empty($_GPC['agentlevel'])) {
		$condition .= ' and m.agentlevel=' . intval($_GPC['agentlevel']);
	}
	if ($status >= 3) {
		$orderby = 'paytime';
	} else if ($status >= 2) {
		$orderby = ' checktime';
	} else {
		$orderby = 'applytime';
	}
	$sql = 'select a.*, m.nickname,m.avatar,m.realname,m.mobile,l.levelname from ' . tablename('sz_yi_commission_apply') . ' a ' . ' left join ' . tablename('sz_yi_member') . ' m on m.id = a.mid' . ' left join ' . tablename('sz_yi_commission_level') . ' l on l.id = m.agentlevel' . " where 1 {$condition} ORDER BY {$orderby} desc ";
	if (empty($_GPC['export'])) {
		$sql .= '  limit ' . ($pindex - 1) * $psize . ',' . $psize;
	}
	$list = pdo_fetchall($sql, $params);
	foreach ($list as &$row) {
		$row['levelname'] = empty($row['levelname']) ? (empty($this->set['levelname']) ? '普通等级' : $this->set['levelname']) : $row['levelname'];
		$row['applytime'] = ($status >= 1 || $status == -1) ? date('Y-m-d H:i', $row['applytime']) : '--';
		$row['checktime'] = $status >= 2 ? date('Y-m-d H:i', $row['checktime']) : '--';
		$row['paytime'] = $status >= 3 ? date('Y-m-d H:i', $row['paytime']) : '--';
		$row['invalidtime'] = $status == -1 ? date('Y-m-d H:i', $row['invalidtime']) : '--';
		$row['typestr'] = empty($row['type']) ? '余额' : '微信';
	}
	unset($row);
	if ($_GPC['export'] == '1') {
		ca('commission.apply.export' . $status);
		plog('commission.apply.export' . $status, '导出数据');
		$title = "";
		if ($status == 1) {
			$title = '待审核佣金';
		} else if ($status == 2) {
			$title = '待打款佣金';
		} else if ($status == 3) {
			$title = '已打款佣金';
		} else if ($status == -1) {
			$title = '已无效佣金';
		}
		m('excel')->export($list, array('title' => $title . '数据-' . date('Y-m-d-H-i', time()), 'columns' => array(array('title' => 'ID', 'field' => 'id', 'width' => 12), array('title' => '提现单号', 'field' => 'applyno', 'width' => 24), array('title' => '粉丝', 'field' => 'nickname', 'width' => 12), array('title' => '姓名', 'field' => 'realname', 'width' => 12), array('title' => '手机号码', 'field' => 'mobile', 'width' => 12), array('title' => '提现方式', 'field' => 'typestr', 'width' => 12), array('title' => '申请时间', 'field' => 'applytime', 'width' => 24), array('title' => '审核时间', 'field' => 'checktime', 'width' => 24), array('title' => '打款时间', 'field' => 'paytime', 'width' => 24), array('title' => '设置无效时间', 'field' => 'invalidtime', 'width' => 24))));
	}
	$total = pdo_fetchcolumn('select count(a.id) from' . tablename('sz_yi_commission_apply') . ' a ' . ' left join ' . tablename('sz_yi_member') . ' m on m.uid = a.mid' . ' left join ' . tablename('sz_yi_commission_level') . ' l on l.id = m.agentlevel' . " where 1 {$condition}", $params);
	$pager = pagination($total, $pindex, $psize);
} else if ($operation == 'detail') {
	$id = intval($_GPC['id']);
	$apply = pdo_fetch('select * from ' . tablename('sz_yi_commission_apply') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if (empty($apply)) {
		message('提现申请不存在!', '', 'error');
	}
	if ($apply['status'] == -1) {
		ca('commission.apply.view_1');
	} else {
		ca('commission.apply.view' . $apply['status']);
	}
	$agentid = $apply['mid'];
	$member = $this->model->getInfo($agentid, array('total', 'ok', 'apply', 'lock', 'check'));
	$hasagent = $member['agentcount'] > 0;
	$agentLevel = $this->model->getLevel($apply['mid']);
	if (empty($agentLevel['id'])) {
		$agentLevel = array('levelname' => empty($this->set['levelname']) ? '普通等级' : $this->set['levelname'], 'commission1' => $this->set['commission1'], 'commission2' => $this->set['commission2'], 'commission3' => $this->set['commission3'],);
	}
	$orderids = iunserializer($apply['orderids']);
	if (!is_array($orderids) || count($orderids) <= 0) {
		message('无任何订单，无法查看!', '', 'error');
	}
	$ids = array();
	foreach ($orderids as $o) {
		$ids[] = $o['orderid'];
	}
	$list = pdo_fetchall('select id,agentid, ordersn,price,goodsprice, dispatchprice,createtime, paytype from ' . tablename('sz_yi_order') . ' where  id in ( ' . implode(',', $ids) . ' );');
	$totalcommission = 0;
	$totalpay = 0;
	foreach ($list as &$row) {
		foreach ($orderids as $o) {
			if ($o['orderid'] == $row['id']) {
				$row['level'] = $o['level'];
				break;
			}
		}
		$goods = pdo_fetchall('SELECT og.id,g.thumb,og.price,og.realprice, og.total,g.title,o.paytype,og.optionname,og.commission1,og.commission2,og.commission3,og.commissions,og.status1,og.status2,og.status3,og.content1,og.content2,og.content3 from ' . tablename('sz_yi_order_goods') . ' og' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid  ' . ' left join ' . tablename('sz_yi_order') . ' o on o.id=og.orderid  ' . ' where og.uniacid = :uniacid and og.orderid=:orderid and og.nocommission=0 order by og.createtime  desc ', array(':uniacid' => $_W['uniacid'], ':orderid' => $row['id']));
		foreach ($goods as &$g) {
			$commissions = iunserializer($g['commissions']);
			if ($this->set['level'] >= 1) {
				$commission = iunserializer($g['commission1']);
				if (empty($commissions)) {
					$g['commission1'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
				} else {
					$g['commission1'] = isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
				}
				if ($row['level'] == 1) {
					$totalcommission += $g['commission1'];
					if ($g['status1'] >= 2) {
						$totalpay += $g['commission1'];
					}
				}
			}
			if ($this->set['level'] >= 2) {
				$commission = iunserializer($g['commission2']);
				if (empty($commissions)) {
					$g['commission2'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
				} else {
					$g['commission2'] = isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
				}
				if ($row['level'] == 2) {
					$totalcommission += $g['commission2'];
					if ($g['status2'] >= 2) {
						$totalpay += $g['commission2'];
					}
				}
			}
			if ($this->set['level'] >= 3) {
				$commission = iunserializer($g['commission3']);
				if (empty($commissions)) {
					$g['commission3'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
				} else {
					$g['commission3'] = isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
				}
				if ($row['level'] == 3) {
					$totalcommission += $g['commission3'];
					if ($g['status3'] >= 2) {
						$totalpay += $g['commission3'];
					}
				}
			}
			$g['level'] = $row['level'];
		}
		unset($g);
		$row['goods'] = $goods;
		$totalmoney += $row['price'];
	}
	unset($row);
	$totalcount = $total = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' o ' . ' left join ' . tablename('sz_yi_member') . ' m on o.openid = m.openid ' . ' left join ' . tablename('sz_yi_member_address') . ' a on a.id = o.addressid ' . ' where o.id in ( ' . implode(',', $ids) . ' );');
	if (checksubmit('submit_check') && $apply['status'] == 1) {
		ca('commission.apply.check');
		$paycommission = 0;
		$ogids = array();
		foreach ($list as $row) {
			$goods = pdo_fetchall('SELECT id from ' . tablename('sz_yi_order_goods') . ' where uniacid = :uniacid and orderid=:orderid and nocommission=0', array(':uniacid' => $_W['uniacid'], ':orderid' => $row['id']));
			foreach ($goods as $g) {
				$ogids[] = $g['id'];
			}
		}
		if (!is_array($ogids)) {
			message('数据出错，请重新设置!', '', 'error');
		}
		$time = time();
		$isAllUncheck = true;
		foreach ($ogids as $ogid) {
			$g = pdo_fetch('SELECT total, commission1,commission2,commission3,commissions from ' . tablename('sz_yi_order_goods') . '  ' . 'where id=:id and uniacid = :uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $ogid));
			if (empty($g)) {
				continue;
			}
			$commissions = iunserializer($g['commissions']);
			if ($this->set['level'] >= 1) {
				$commission = iunserializer($g['commission1']);
				if (empty($commissions)) {
					$g['commission1'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
				} else {
					$g['commission1'] = isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
				}
			}
			if ($this->set['level'] >= 2) {
				$commission = iunserializer($g['commission2']);
				if (empty($commissions)) {
					$g['commission2'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
				} else {
					$g['commission2'] = isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
				}
			}
			if ($this->set['level'] >= 3) {
				$commission = iunserializer($g['commission3']);
				if (empty($commissions)) {
					$g['commission3'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
				} else {
					$g['commission3'] = isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
				}
			}
			$update = array();
			if (isset($_GPC['status1'][$ogid])) {
				if (intval($_GPC['status1'][$ogid]) == 2) {
					$paycommission += $g['commission1'];
					$isAllUncheck = false;
				}
				$update = array('checktime1' => $time, 'status1' => intval($_GPC['status1'][$ogid]), 'content1' => $_GPC['content1'][$ogid]);
			} else if (isset($_GPC['status2'][$ogid])) {
				if (intval($_GPC['status2'][$ogid]) == 2) {
					$paycommission += $g['commission2'];
					$isAllUncheck = false;
				}
				$update = array('checktime2' => $time, 'status2' => intval($_GPC['status2'][$ogid]), 'content2' => $_GPC['content2'][$ogid]);
			} else if (isset($_GPC['status3'][$ogid])) {
				if (intval($_GPC['status3'][$ogid]) == 2) {
					$paycommission += $g['commission3'];
					$isAllUncheck = false;
				}
				$update = array('checktime3' => $time, 'status3' => intval($_GPC['status3'][$ogid]), 'content3' => $_GPC['content3'][$ogid]);
			}
			if (!empty($update)) {
				pdo_update('sz_yi_order_goods', $update, array('id' => $ogid));
			}
		}
		if ($isAllUncheck) {
			pdo_update('sz_yi_commission_apply', array('status' => -1, 'invalidtime' => $time), array('id' => $id, 'uniacid' => $_W['uniacid']));
		} else {
			pdo_update('sz_yi_commission_apply', array('status' => 2, 'checktime' => $time), array('id' => $id, 'uniacid' => $_W['uniacid']));
			$this->model->sendMessage($member['openid'], array('commission' => $paycommission, 'type' => $apply['type'] == 1 ? '微信' : '余额'), TM_COMMISSION_CHECK);
		}
		plog('commission.apply.check', "佣金审核 ID: {$id} 申请编号: {$apply['applyno']} 总佣金: {$totalmoney} 审核通过佣金: {$paycommission} ");
		message('申请处理成功!', $this->createPluginWebUrl('commission/apply', array('status' => $apply['status'])), 'success');
	}
}
if (checksubmit('submit_cancel') && ($apply['status'] == 2 || $apply['status'] == -1)) {
	ca('commission.apply.cancel');
	$time = time();
	foreach ($list as $row) {
		$update = array();
		foreach ($row['goods'] as $g) {
			$update = array();
			if ($row['level'] == 1) {
				$update = array('checktime1' => 0, 'status1' => 1);
			} else if ($row['level'] == 2) {
				$update = array('checktime2' => 0, 'status2' => 1);
			} else if ($row['level'] == 3) {
				$update = array('checktime3' => 0, 'status3' => 1);
			}
			if (!empty($update)) {
				pdo_update('sz_yi_order_goods', $update, array('id' => $g['id']));
			}
		}
	}
	pdo_update('sz_yi_commission_apply', array('status' => 1, 'checktime' => 0, 'invalidtime' => 0), array('id' => $id, 'uniacid' => $_W['uniacid']));
	plog('commission.apply.cancel', "重新审核申请 ID: {$id} 申请编号: {$apply['applyno']} ");
	message('撤销审核处理成功!', $this->createPluginWebUrl('commission/apply', array('status' => 1)), 'success');
}
if (checksubmit('submit_pay') && $apply['status'] == 2) {
	ca('commission.apply.pay');
	$time = time();
	$pay = $totalpay;
	if ($apply['type'] == 1) {
		$pay *= 100;
	}
	$result = m('finance')->pay($member['openid'], $apply['type'], $pay, $apply['applyno']);
	if (is_error($result)) {
		if (strexists($result['message'], '系统繁忙')) {
			$updateno['applyno'] = $apply['applyno'] = m('common')->createNO('commission_apply', 'applyno', 'CA');
			pdo_update('sz_yi_commission_apply', $updateno, array('id' => $apply['id']));
			$result = m('finance')->pay($member['openid'], $apply['type'], $pay, $apply['applyno']);
			if (is_error($result)) {
				message($result['message'], '', 'error');
			}
		}
		message($result['message'], '', 'error');
	}
	foreach ($list as $row) {
		$update = array();
		foreach ($row['goods'] as $g) {
			$update = array();
			if ($row['level'] == 1 && $g['status1'] == 2) {
				$update = array('paytime1' => $time, 'status1' => 3);
			} else if ($row['level'] == 2 && $g['status2'] == 2) {
				$update = array('paytime2' => $time, 'status2' => 3);
			} else if ($row['level'] == 3 && $g['status3'] == 2) {
				$update = array('paytime3' => $time, 'status3' => 3);
			}
			if (!empty($update)) {
				pdo_update('sz_yi_order_goods', $update, array('id' => $g['id']));
			}
		}
	}
	pdo_update('sz_yi_commission_apply', array('status' => 3, 'paytime' => $time, 'commission_pay' => $totalpay), array('id' => $id, 'uniacid' => $_W['uniacid']));
	$log = array('uniacid' => $_W['uniacid'], 'applyid' => $apply['id'], 'mid' => $member['id'], 'commission' => $totalcommission, 'commission_pay' => $totalpay, 'createtime' => $time);
	pdo_insert('sz_yi_commission_log', $log);
	$this->model->sendMessage($member['openid'], array('commission' => $totalpay, 'type' => $apply['type'] == 1 ? '微信' : '余额'), TM_COMMISSION_PAY);
	$this->model->upgradeLevelByCommissionOK($member['openid']);
	plog('commission.apply.pay', "佣金打款 ID: {$id} 申请编号: {$apply['applyno']} 总佣金: {$totalcommission} 审核通过佣金: {$totalpay} ");
	message('佣金打款处理成功!', $this->createPluginWebUrl('commission/apply', array('status' => $apply['status'])), 'success');
} elseif ($operation == 'changecommissionmodal') {
	$id = intval($_GPC['id']);
	$set = $this->getSet();
	$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (empty($order)) {
		exit('fail');
	}
	$member = m('member')->getMember($order['openid']);
	$agentid = $order['agentid'];
	$agentLevel = $this->model->getLevel($agentid);
	$ogid = intval($_GPC['ogid']);
	$order_goods_change = pdo_fetchall('select og.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.oldprice,og.commission1,og.commission2,og.commission3,og.commissions,og.status1,og.status2,og.status3 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $id));
	if (empty($order_goods_change)) {
		exit('fail');
	}
	$cm1 = m('member')->getMember($agentid);
	if (!empty($cm1['agentid'])) {
		$cm2 = m('member')->getMember($cm1['agentid']);
		if (!empty($cm2['agentid'])) {
			$cm3 = m('member')->getMember($cm2['agentid']);
		}
	}
	foreach ($order_goods_change as &$og) {
		$commissions = iunserializer($og['commissions']);
		if ($set['level'] >= 1) {
			$commission = iunserializer($og['commission1']);
			if (empty($commissions)) {
				$og['c1'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
			} else {
				$og['c1'] = isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
			}
		}
		if ($set['level'] >= 2) {
			$commission = iunserializer($og['commission2']);
			if (empty($commissions)) {
				$og['c2'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
			} else {
				$og['c2'] = isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
			}
		}
		if ($set['level'] >= 3) {
			$commission = iunserializer($og['commission3']);
			if (empty($commissions)) {
				$og['c3'] = isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
			} else {
				$og['c3'] = isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
			}
		}
		$og['co'] = $this->model->getOrderCommissions($id, $og['id']);
	}
	unset($og);
	include $this->template('changecommission_modal');
	exit;
} else if ($operation == 'confirmchangecommission') {
	ca('commission.changecommission');
	$id = intval($_GPC['id']);
	$set = $this->getSet();
	$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (empty($order)) {
		message('未找到订单，无法修改佣金!', '', 'error');
	}
	$member = m('member')->getMember($order['openid']);
	$agentid = $order['agentid'];
	$agentLevel = $this->model->getLevel($agentid);
	$ogid = intval($_GPC['ogid']);
	$order_goods_change = pdo_fetchall('select og.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.oldprice,og.commission1,og.commission2,og.commission3,og.commissions,og.status1,og.status2,og.status3 from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid and og.nocommission=0 ', array(':uniacid' => $_W['uniacid'], ':orderid' => $id));
	if (empty($order_goods_change)) {
		message('未找到订单商品，无法修改佣金!', '', 'error');
	}
	$cm1 = $_GPC['cm1'];
	$cm2 = $_GPC['cm2'];
	$cm3 = $_GPC['cm3'];
	if (!is_array($cm1) && !is_array($cm2) && !is_array($cm3)) {
		message('未找到修改数据!', '', 'error');
	}
	foreach ($order_goods_change as $og) {
		$commissions = iunserializer($og['commissions']);
		$commissions['level1'] = isset($cm1[$og['id']]) ? round($cm1[$og['id']], 2) : $commissions['level1'];
		$commissions['level2'] = isset($cm2[$og['id']]) ? round($cm2[$og['id']], 2) : $commissions['level3'];
		$commissions['level3'] = isset($cm3[$og['id']]) ? round($cm3[$og['id']], 2) : $commissions['level2'];
		pdo_update('sz_yi_order_goods', array('commissions' => iserializer($commissions)), array('id' => $og['id']));
	}
	plog('commission.changecommission', "修改佣金 订单号: {$order['ordersn']}");
	message('佣金修改成功!', referer(), 'success');
}
load()->func('tpl');
include $this->template('apply');
