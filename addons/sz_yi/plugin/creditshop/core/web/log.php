<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;

$type      = intval($_GPC['type']);
$operation = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if ($operation == 'display') {
	ca('creditshop.log.view' . $type);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' and log.uniacid=:uniacid and g.type=:type and log.status>0';
	$params = array(':uniacid' => $_W['uniacid'], ':type' => $type);
	if (!empty($_GPC['keyword'])) {
		$_GPC['keyword'] = trim($_GPC['keyword']);
		$condition .= ' and ( log.logno like :keyword or log.eno like :keyword or g.title like :keyword ) ';
		$params[':keyword'] = "%{$_GPC['keyword']}%";
	}
	if ($_GPC['status'] != '') {
		$condition .= ' and log.status=' . intval($_GPC['status']);
	}
	if (!empty($_GPC['realname'])) {
		$_GPC['realname'] = trim($_GPC['realname']);
		$condition .= ' and ( m.realname like :realname or m.nickname like :realname  or m.mobile like :realname or log.realname like :realname  or log.mobile like :realname  or a.realname like :realname or a.mobile like :realname  ) ';
		$params[':realname'] = "%{$_GPC['realname']}%";
	}
	if (!empty($_GPC['storename'])) {
		$_GPC['storename'] = trim($_GPC['storename']);
		$condition .= ' and  s.storename like :storename';
		$params[':storename'] = "%{$_GPC['storename']}%";
	}
	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	}
	$searchtime = $_GPC['searchtime'];
	if (!empty($_GPC['searchtime'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']);
		if (!empty($searchtime)) {
			$condition .= ' AND log.createtime >= :starttime AND log.createtime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
	}
	$sql = 'select log.*, m.nickname,m.avatar,m.realname as mrealname,m.mobile as mmobile, g.title,g.thumb,g.thumb,g.credit,g.money,g.type as goodstype,g.isverify,g.goodstype as iscoupon,s.storename,s.address as storeaddress from ' . tablename('sz_yi_creditshop_log') . ' log ' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid = log.openid and m.uniacid=log.uniacid' . ' left join ' . tablename('sz_yi_member_address') . ' a on a.id = log.addressid' . ' left join ' . tablename('sz_yi_store') . ' s on s.id = log.storeid' . ' left join ' . tablename('sz_yi_creditshop_goods') . ' g on g.id = log.goodsid' . " where 1 {$condition} ORDER BY log.createtime desc ";
	if (empty($_GPC['export'])) {
		$sql .= ' limit ' . ($pindex - 1) * $psize . ',' . $psize;
	}
	$list = pdo_fetchall($sql, $params);
	$total = pdo_fetchcolumn('select count(log.id) from' . tablename('sz_yi_creditshop_log') . ' log ' . ' left join ' . tablename('sz_yi_member') . ' m on m.openid = log.openid and m.uniacid=log.uniacid' . ' left join ' . tablename('sz_yi_store') . ' s on s.id = log.storeid' . ' left join ' . tablename('sz_yi_member_address') . ' a on a.id = log.addressid' . ' left join ' . tablename('sz_yi_creditshop_goods') . ' g on g.id = log.goodsid' . " where 1 {$condition}", $params);
	foreach ($list as &$row) {
		$row['address'] = array();
		if (!empty($row['addressid'])) {
			$row['address'] = pdo_fetch('select realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $row['addressid'], ':uniacid' => $_W['uniacid']));
		} else {
			$row['address'] = array('carrier_realname' => $row['realname'], 'carrier_mobile' => $row['mobile'], 'carrier_storename' => $row['storename'], 'carrier_address' => $row['storeaddress'],);
		}
		$row['address']['logid'] = $row['id'];
		$row['address']['isverify'] = $row['isverify'];
		$row['address']['storeid'] = $row['storeid'];
		$row['address']['addressid'] = $row['addressid'];
		$canexchange = true;
		if ($row['status'] == 2) {
			if (empty($row['paystatus'])) {
				$canexchange = false;
			}
			if (empty($row['dispatchstatus'])) {
				$canexchange = false;
			}
		} else {
			$canexchange = false;
		}
		$row['canexchange'] = $canexchange;
	}
	unset($row);
	if ($_GPC['export'] == 1) {
		ca('creditshop.log.export' . $type);
		if (empty($type)) {
			plog('creditshop.log.export0', '导出兑换订单');
		} else {
			plog('creditshop.log.export1', '导出抽奖订单');
		}
		foreach ($list as &$row) {
			$row['typestr'] = empty($row['type']) ? '兑换' : '抽奖';
			$row['verifystr'] = empty($row['isverify']) ? '快递' : '线下';
			$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
			$row['user1'] = empty($row['realname']) ? $row['mrealname'] : $row['realname'];
			$row['user2'] = empty($row['mobile']) ? $row['mmobile'] : $row['mobile'];
			if (!empty($row['addressid'])) {
				$row['addressinfo_province'] = $row['address']['province'];
				$row['addressinfo_city'] = $row['address']['city'];
				$row['addressinfo_area'] = $row['address']['area'];
				$row['addressinfo_address'] = $row['address']['address'];
				$row['addressinfo_realname'] = $row['address']['realname'];
				$row['addressinfo_mobile'] = $row['address']['mobile'];
			} else {
				$row['storeinfo_storename'] = $row['address']['carrier_storename'];
				$row['storeinfo_address'] = $row['address']['carrier_address'];
			}
			if (empty($type)) {
				if ($row['status'] == 2) {
					$row['statusstr'] = '已兑换';
				} else if ($row['status'] == 3) {
					$row['statusstr'] = '已兑奖';
				}
			} else {
				if ($row['status'] == 1) {
					$row['statusstr'] = '未中奖';
				} else if ($row['status'] == 2) {
					$row['statusstr'] = '已中奖';
				} else if ($row['status'] == 3) {
					$row['statusstr'] = '已兑奖';
				}
			}
			if ($row['paytype'] == -1) {
				$row['paystr'] = '无需支付';
			} else {
				if ($row['paytype'] == 0) {
					if ($row['paystatus'] == 0) {
						$row['paystr'] = '余额未支付';
					} else {
						$row['paystr'] = '余额已支付';
					}
				} elseif ($row['paytype'] == 1) {
					if ($row['paystatus'] == 0) {
						$row['paystr'] = '微信未支付';
					} else {
						$row['paystr'] = '微信已支付';
					}
				}
			}
			if ($row['dispatchstatus'] == -1) {
				$row['dispatchstr'] = '无需支付';
			} else if ($row['dispatchstatus'] == 0) {
				$row['dispatchstr'] = '未支付';
			} else if ($row['dispatchstatus'] == 1) {
				$row['dispatchstr'] = '已支付';
			}
		}
		unset($row);
		$columns = array(array('title' => 'ID', 'field' => 'id', 'width' => 12), array('title' => '活动编号', 'field' => 'logno', 'width' => 24), array('title' => '商品名称', 'field' => 'title', 'width' => 12), array('title' => '活动类型', 'field' => 'typestr', 'width' => 12), array('title' => '兑换方式', 'field' => 'verifystr', 'width' => 12), array('title' => '联系人', 'field' => 'user1', 'width' => 12), array('title' => '联系电话', 'field' => 'user2', 'width' => 12), array('title' => '邮寄地址', 'field' => 'addressinfo_province', 'width' => 12), array('title' => '', 'field' => 'addressinfo_city', 'width' => 12), array('title' => '', 'field' => 'addressinfo_area', 'width' => 12), array('title' => '', 'field' => 'addressinfo_address', 'width' => 24), array('title' => '', 'field' => 'addressinfo_realname', 'width' => 12), array('title' => '', 'field' => 'addressinfo_mobile', 'width' => 12), array('title' => '兑换门店', 'field' => 'storeinfo_storename', 'width' => 12), array('title' => '', 'field' => 'storeinfo_address', 'width' => 24), array('title' => '参与状态', 'field' => 'statusstr', 'width' => 12), array('title' => '支付状态', 'field' => 'paystr', 'width' => 12), array('title' => '快递状态', 'field' => 'dispatchstr', 'width' => 12), array('title' => '参与时间', 'field' => 'createtime', 'width' => 12));
		m('excel')->export($list, array('title' => (empty($type) ? '兑换' : '抽奖') . '订单数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
	}
	$pager = pagination($total, $pindex, $psize);
} else if ($operation == 'detail') {
	$id = intval($_GPC['id']);
	$log = pdo_fetch('select * from ' . tablename('sz_yi_creditshop_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (empty($log)) {
		message('兑换记录不存在!', referer(), 'error');
	}
	$member = m('member')->getMember($log['openid']);
	$goods = $this->model->getGoods($log['goodsid'], $member);
	if (empty($goods['id'])) {
		message('商品记录不存在!', referer(), 'error');
	}
	ca('creditshop.log.view' . $goods['type']);
	$canexchange = true;
	if ($log['status'] == 2) {
		if (empty($log['paystatus'])) {
			$canexchange = false;
		}
		if (empty($log['dispatchstatus'])) {
			$canexchange = false;
		}
	} else {
		$canexchange = false;
	}
	$log['canexchange'] = $canexchange;
	$address = false;
	if (!empty($log['addressid'])) {
		$address = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $log['addressid'], ':uniacid' => $_W['uniacid']));
	}
	$address['logid'] = $id;
} else if ($operation == 'exchange') {
	ca('creditshop.log.exchange');
	$id = intval($_GPC['id']);
	$log = pdo_fetch('select * from ' . tablename('sz_yi_creditshop_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (empty($log)) {
		message('兑换记录不存在!', referer(), 'error');
	}
	if (empty($log['status'])) {
		message('无效兑换记录!', referer(), 'error');
	}
	if ($log['status'] >= 3) {
		message('此记录已兑换过了!', referer(), 'error');
	}
	$member = m('member')->getMember($log['openid']);
	$goods = $this->model->getGoods($log['goodsid'], $member);
	if (empty($goods['id'])) {
		message('商品记录不存在!', referer(), 'error');
	}
	if (!empty($goods['type'])) {
		if ($log['status'] <= 1) {
			message('未中奖，不能兑换!', referer(), 'error');
		}
	}
	if ($goods['money'] > 0 && empty($log['paystatus'])) {
		message('未支付，无法进行兑换!', referer(), 'error');
	}
	if ($goods['dispatch'] > 0 && empty($log['dispatchstatus'])) {
		message('未支付运费，无法进行兑换!', referer(), 'error');
	}
	pdo_update('sz_yi_creditshop_log', array('status' => 3, 'usetime' => time(), 'expresscom' => $_GPC['expresscom'], 'expresssn' => $_GPC['expresssn'], 'express' => $_GPC['express']), array('id' => $id));
	$this->model->sendMessage($id);
	plog('creditshop.log.exchange', "积分商城兑换 兑换记录ID: {$id}");
	message('兑换成功!', $this->createPluginWebUrl('creditshop/log', array('type' => $goods['type'])), 'success');
}
load()->func('tpl');
include $this->template('log');
