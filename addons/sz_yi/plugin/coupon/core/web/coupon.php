<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
	exit('Access Denied');
}
global $_W, $_GPC;
//check_shop_auth
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$type = intval($_GPC['type']);
if ($operation == 'display') {
	ca('coupon.coupon.view');
	if (!empty($_GPC['displayorder'])) {
		ca('coupon.coupon.edit');
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('sz_yi_coupon', array('displayorder' => $displayorder), array('id' => $id));
		}
		plog('coupon.coupon.edit', '批量修改排序');
		message('分类排序更新成功！', $this->createPluginWebUrl('coupon', array('op' => 'display')), 'success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' uniacid = :uniacid';
	$params = array(':uniacid' => $_W['uniacid']);
	if (!empty($_GPC['keyword'])) {
		$_GPC['keyword'] = trim($_GPC['keyword']);
		$condition .= ' AND couponname LIKE :couponname';
		$params[':couponname'] = '%' . trim($_GPC['keyword']) . '%';
	}
	if (empty($starttime) || empty($endtime)) {
		$starttime = strtotime('-1 month');
		$endtime = time();
	}
	if (!empty($_GPC['searchtime'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']);
		if ($_GPC['searchtime'] == '1') {
			$condition .= ' AND createtime >= :starttime AND createtime <= :endtime ';
			$params[':starttime'] = $starttime;
			$params[':endtime'] = $endtime;
		}
	}
	if ($_GPC['type'] != '') {
		$condition .= ' AND coupontype = :coupontype';
		$params[':coupontype'] = intval($_GPC['type']);
	}
	$sql = 'SELECT * FROM ' . tablename('sz_yi_coupon') . ' ' . " where  1 and {$condition} ORDER BY displayorder DESC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
	$list = pdo_fetchall($sql, $params);
	foreach ($list as &$row) {
		$row['gettotal'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_coupon_data') . ' where couponid=:couponid and uniacid=:uniacid limit 1', array(':couponid' => $row['id'], ':uniacid' => $_W['uniacid']));
		$row['usetotal'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_coupon_data') . ' where used = 1 and couponid=:couponid and uniacid=:uniacid limit 1', array(':couponid' => $row['id'], ':uniacid' => $_W['uniacid']));
		$row['pwdjoins'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_coupon_guess') . ' where couponid=:couponid and uniacid=:uniacid limit 1', array(':couponid' => $row['id'], ':uniacid' => $_W['uniacid']));
		$row['pwdoks'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_coupon_guess') . ' where couponid=:couponid and uniacid=:uniacid and ok=1 limit 1', array(':couponid' => $row['id'], ':uniacid' => $_W['uniacid']));
	}
	unset($row);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_coupon') . " where 1 and {$condition}", $params);
	$pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		ca('coupon.coupon.add');
	} else {
		ca('coupon.coupon.view|coupon.coupon.edit');
	}
	if (checksubmit('submit')) {
		$data = array('uniacid' => $_W['uniacid'], 'couponname' => trim($_GPC['couponname']), 'coupontype' => intval($_GPC['coupontype']), 'catid' => intval($_GPC['catid']), 'timelimit' => intval($_GPC['timelimit']), 'usetype' => intval($_GPC['usetype']), 'returntype' => intval($_GPC['returntype']), 'enough' => trim($_GPC['enough']), 'timedays' => intval($_GPC['timedays']), 'timestart' => strtotime($_GPC['time']['start']), 'timeend' => strtotime($_GPC['time']['end']), 'backtype' => intval($_GPC['backtype']), 'deduct' => trim($_GPC['deduct']), 'discount' => trim($_GPC['discount']), 'backmoney' => trim($_GPC['backmoney']), 'backcredit' => trim($_GPC['backcredit']), 'backredpack' => trim($_GPC['backredpack']), 'backwhen' => intval($_GPC['backwhen']), 'gettype' => intval($_GPC['gettype']), 'getmax' => intval($_GPC['getmax']), 'credit' => intval($_GPC['credit']), 'money' => trim($_GPC['money']), 'usecredit2' => intval($_GPC['usecredit2']), 'total' => intval($_GPC['total']), 'bgcolor' => trim($_GPC['bgcolor']), 'thumb' => save_media($_GPC['thumb']), 'remark' => trim($_GPC['remark']), 'desc' => htmlspecialchars_decode($_GPC['desc']), 'descnoset' => intval($_GPC['descnoset']), 'status' => intval($_GPC['status']), 'resptitle' => trim($_GPC['resptitle']), 'respthumb' => save_media($_GPC['respthumb']), 'respdesc' => trim($_GPC['respdesc']), 'respurl' => trim($_GPC['respurl']), 'pwdkey' => trim($_GPC['pwdkey']), 'pwdwords' => trim($_GPC['pwdwords']), 'pwdask' => trim($_GPC['pwdask']), 'pwdsuc' => trim($_GPC['pwdsuc']), 'pwdfail' => trim($_GPC['pwdfail']), 'pwdfull' => trim($_GPC['pwdfull']), 'pwdurl' => trim($_GPC['pwdurl']), 'pwdtimes' => intval($_GPC['pwdtimes']), 'pwdopen' => intval($_GPC['pwdopen']), 'pwdown' => trim($_GPC['pwdown']), 'pwdexit' => trim($_GPC['pwdexit']), 'pwdexitstr' => trim($_GPC['pwdexitstr']));
		if (!empty($id)) {
			if (!empty($data['pwdkey'])) {
				$pwdkey = pdo_fetchcolumn('SELECT pwdkey FROM ' . tablename('sz_yi_coupon') . ' WHERE id=:id and uniacid=:uniacid limit 1 ', array(':id' => $id, ':uniacid' => $_W['uniacid']));
				if ($pwdkey != $data['pwdkey']) {
					$keyword = pdo_fetch('SELECT * FROM ' . tablename('rule_keyword') . ' WHERE content=:content and uniacid=:uniacid and id<>:id  limit 1 ', array(':content' => $data['pwdkey'], ':uniacid' => $_W['uniacid'], ':id' => $id));
					if (!empty($keyword)) {
						message('口令关键词已存在!', '', 'error');
					}
				}
			}
			pdo_update('sz_yi_coupon', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
			plog('coupon.coupon.edit', "编辑优惠券 ID: {$id} <br/>优惠券名称: {$data['couponname']}");
		} else {
			if (!empty($data['pwdkey'])) {
				$keyword = pdo_fetch('SELECT * FROM ' . tablename('rule_keyword') . ' WHERE content=:content and uniacid=:uniacid limit 1 ', array(':content' => $data['pwdkey'], ':uniacid' => $_W['uniacid']));
				if (!empty($keyword)) {
					message('口令关键词已存在!', '', 'error');
				}
			}
			$data['createtime'] = time();
			pdo_insert('sz_yi_coupon', $data);
			$id = pdo_insertid();
			plog('coupon.coupon.add', "添加优惠券 ID: {$id}  <br/>优惠券名称: {$data['couponname']}");
		}
		$key = 'sz_yi:coupon:' . $id;
		$rule = pdo_fetch('select * from ' . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'sz_yi', ':name' => $key));
		if (!empty($data['pwdkey'])) {
			if (empty($rule)) {
				$rule_data = array('uniacid' => $_W['uniacid'], 'name' => $key, 'module' => 'sz_yi', 'displayorder' => 0, 'status' => $data['pwdopen']);
				pdo_insert('rule', $rule_data);
				$rid = pdo_insertid();
				$keyword_data = array('uniacid' => $_W['uniacid'], 'rid' => $rid, 'module' => 'sz_yi', 'content' => $data['pwdkey'], 'type' => 1, 'displayorder' => 0, 'status' => $data['pwdopen']);
				pdo_insert('rule_keyword', $keyword_data);
			} else {
				pdo_update('rule_keyword', array('content' => $data['pwdkey'], 'status' => $data['pwdopen']), array('rid' => $rule['id']));
			}
		} else {
			if (!empty($rule)) {
				pdo_delete('rule_keyword', array('rid' => $rule['id']));
				pdo_delete('rule', array('id' => $rule['id']));
			}
		}
		message('更新优惠券成功！', $this->createPluginWebUrl('coupon/coupon'), 'success');
	}
	$item = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_coupon') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if (empty($item)) {
		$starttime = time();
		$endtime = strtotime(date('Y-m-d H:i:s', $starttime) . '+7 days');
	} else {
		$type = $item['coupontype'];
		$starttime = $item['timestart'];
		$endtime = $item['timeend'];
	}
} elseif ($operation == 'delete') {
	ca('coupon.coupon.delete');
	$id = intval($_GPC['id']);
	$item = pdo_fetch('SELECT id,couponname FROM ' . tablename('sz_yi_coupon') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (empty($item)) {
		message('抱歉，优惠券不存在或是已经被删除！', $this->createPluginWebUrl('coupon/coupon', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_coupon', array('id' => $id, 'uniacid' => $_W['uniacid']));
	$couponids = pdo_fetchall('select id from ' . tablename('sz_yi_coupon') . ' where uniacid=:uniacid', array(':uniacid' => $_W['uniacid']), 'id');
	if (!empty($couponids)) {
		pdo_query('delete from ' . tablename('sz_yi_coupon_data') . ' where couponid not in (' . implode(',', array_keys($couponids)) . ') and uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
	}
	pdo_delete('sz_yi_coupon_data', array('couponid' => $id, 'uniacid' => $_W['uniacid']));
	plog('coupon.coupon.delete', "删除优惠券 ID: {$id}  <br/>优惠券名称: {$item['couponname']} ");
	message('优惠券删除成功！', $this->createPluginWebUrl('coupon/coupon', array('op' => 'display')), 'success');
} else if ($operation == 'query') {
	$kwd = trim($_GPC['keyword']);
	$params = array();
	$params[':uniacid'] = $_W['uniacid'];
	$condition = ' and uniacid=:uniacid';
	if (!empty($kwd)) {
		$condition .= ' AND couponname like :couponname';
		$params[':couponname'] = "%{$kwd}%";
	}
	$time = time();
	$ds = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_coupon') . "  WHERE 1 {$condition} ORDER BY id asc", $params);
	foreach ($ds as &$d) {
		$d = $this->model->setCoupon($d, $time, false);
		$d['last'] = $this->model->get_last_count($d['id']);
		if ($d['last'] == -1) {
			$d['last'] = '不限';
		}
	}
	unset($d);
	include $this->template('coupon/query');
	exit;
}
$category = pdo_fetchall('select * from ' . tablename('sz_yi_coupon_category') . ' where uniacid=:uniacid order by id desc', array(':uniacid' => $_W['uniacid']), 'id');
load()->func('tpl');
include $this->template('coupon');
