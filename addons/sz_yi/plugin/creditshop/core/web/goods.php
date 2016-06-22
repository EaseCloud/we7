<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$pcoupon = p('coupon');
if ($operation == 'display') {
	ca('creditshop.goods.view');
	if (!empty($_GPC['displayorder'])) {
		ca('creditshop.goods.edit');
		foreach ($_GPC['displayorder'] as $id => $displayorder) {
			pdo_update('sz_yi_creditshop_goods', array('displayorder' => $displayorder), array('id' => $id));
		}
		plog('creditshop.goods.edit', '批量修改积分商城商品排序');
		message('商品排序更新成功！', $this->createPluginWebUrl('creditshop/goods', array('op' => 'display')), 'success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' uniacid = :uniacid AND deleted = :deleted';
	$params = array(':uniacid' => $_W['uniacid'], ':deleted' => '0');
	if (!empty($_GPC['keyword'])) {
		$_GPC['keyword'] = trim($_GPC['keyword']);
		$condition .= ' AND title LIKE :title';
		$params[':title'] = '%' . trim($_GPC['keyword']) . '%';
	}
	if ($_GPC['status'] != '') {
		$condition .= ' AND status = :status';
		$params[':status'] = intval($_GPC['status']);
	}
	if ($_GPC['cate'] != '') {
		$condition .= ' AND cate = :cate';
		$params[':cate'] = intval($_GPC['cate']);
	}
	$sql = 'SELECT * FROM ' . tablename('sz_yi_creditshop_goods') . " where  1 and {$condition} ORDER BY displayorder DESC,id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
	$list = pdo_fetchall($sql, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_creditshop_goods') . " where 1 and {$condition}", $params);
	$pager = pagination($total, $pindex, $psize);
	$category = pdo_fetchall('select id,name,thumb from ' . tablename('sz_yi_creditshop_category') . ' where uniacid=:uniacid order by displayorder desc', array(':uniacid' => $_W['uniacid']), 'id');
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	if (empty($id)) {
		ca('creditshop.goods.add');
	} else {
		ca('creditshop.goods.view|creditshop.goods.edit');
	}
	if (checksubmit('submit')) {
		$data = array('uniacid' => $_W['uniacid'], 'displayorder' => intval($_GPC['displayorder']), 'title' => trim($_GPC['title']), 'cate' => intval($_GPC['cate']), 'thumb' => save_media($_GPC['thumb']), 'price' => $_GPC['price'], 'type' => intval($_GPC['type']), 'chance' => intval($_GPC['chance']), 'chanceday' => intval($_GPC['chanceday']), 'total' => intval($_GPC['total']), 'totalday' => intval($_GPC['totalday']), 'credit' => intval($_GPC['credit']), 'money' => trim($_GPC['money']), 'rate1' => trim($_GPC['rate1']), 'rate2' => trim($_GPC['rate2']), 'status' => intval($_GPC['status']), 'usecredit2' => intval($_GPC['usecredit2']), 'showlevels' => is_array($_GPC['showlevels']) ? implode(',', $_GPC['showlevels']) : '', 'buylevels' => is_array($_GPC['buylevels']) ? implode(',', $_GPC['buylevels']) : '', 'showgroups' => is_array($_GPC['showgroups']) ? implode(',', $_GPC['showgroups']) : '', 'buygroups' => is_array($_GPC['buygroups']) ? implode(',', $_GPC['buygroups']) : '', 'istime' => intval($_GPC['istime']), 'istop' => intval($_GPC['istop']), 'isrecommand' => intval($_GPC['isrecommand']), 'isendtime' => intval($_GPC['isendtime']), 'endtime' => strtotime($_GPC['endtime']), 'timestart' => strtotime($_GPC['timestart']), 'timeend' => strtotime($_GPC['timeend']), 'share_title' => trim($_GPC['share_title']), 'share_icon' => save_media($_GPC['share_icon']), 'share_desc' => trim($_GPC['share_desc']), 'followneed' => intval($_GPC['followneed']), 'followtext' => trim($_GPC['followtext']), 'detail' => m('common')->html_images($_GPC['detail']), 'subtitle' => trim($_GPC['subtitle']), 'subdetail' => m('common')->html_images($_GPC['subdetail']), 'usedetail' => m('common')->html_images($_GPC['usedetail']), 'goodsdetail' => m('common')->html_images($_GPC['goodsdetail']), 'noticedetail' => m('common')->html_images($_GPC['noticedetail']), 'area' => trim($_GPC['area']), 'dispatch' => trim($_GPC['dispatch']), 'isverify' => intval($_GPC['isverify']), 'storeids' => is_array($_GPC['storeids']) ? implode(',', $_GPC['storeids']) : '', 'noticeopenid' => trim($_GPC['noticeopenid']), 'goodstype' => intval($_GPC['goodstype']), 'couponid' => intval($_GPC['couponid']));
		if (!empty($data['isverify'])) {
			$data['dispatch'] = 0;
		}
		$data['vip'] = (!empty($data['showlevels']) || !empty($data['showgroups'])) ? 1 : 0;
		if (!empty($id)) {
			pdo_update('sz_yi_creditshop_goods', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
			plog('creditshop.goods.edit', "编辑积分商城商品 ID: {$id} <br/>商品名称: {$data['title']}");
		} else {
			pdo_insert('sz_yi_creditshop_goods', $data);
			$id = pdo_insertid();
			plog('creditshop.goods.add', "添加积分商城商品 ID: {$id}  <br/>商品名称: {$data['title']}");
		}
		message('更新商品成功！', $this->createPluginWebUrl('creditshop/goods', array('op' => 'post', 'id' => $id)), 'success');
	}
	$item = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_creditshop_goods') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if ($item['showlevels'] != '') {
		$item['showlevels'] = explode(',', $item['showlevels']);
	}
	if ($item['buylevels'] != '') {
		$item['buylevels'] = explode(',', $item['buylevels']);
	}
	if ($item['showgroups'] != '') {
		$item['showgroups'] = explode(',', $item['showgroups']);
	}
	if ($item['buygroups'] != '') {
		$item['buygroups'] = explode(',', $item['buygroups']);
	}
	$stores = array();
	if (!empty($item['storeids'])) {
		$stores = pdo_fetchall('select id,storename from ' . tablename('sz_yi_store') . ' where id in (' . $item['storeids'] . ' ) and uniacid=' . $_W['uniacid']);
	}
	if (!empty($item['noticeopenid'])) {
		$saler = m('member')->getMember($item['noticeopenid']);
	}
	$endtime = empty($item['endtime']) ? date('Y-m-d H:i', time()) : date('Y-m-d H:i', $item['endtime']);
	$levels = m('member')->getLevels();
	$groups = m('member')->getGroups();
	$category = pdo_fetchall('select id,name,thumb from ' . tablename('sz_yi_creditshop_category') . ' where uniacid=:uniacid order by displayorder desc', array(':uniacid' => $_W['uniacid']));
} elseif ($operation == 'delete') {
	ca('creditshop.goods.delete');
	$id = intval($_GPC['id']);
	$item = pdo_fetch('SELECT id,title FROM ' . tablename('sz_yi_creditshop_goods') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (empty($item)) {
		message('抱歉，商品不存在或是已经被删除！', $this->createPluginWebUrl('creditshop/goods', array('op' => 'display')), 'error');
	}
	pdo_update('sz_yi_creditshop_goods', array('deleted' => 1), array('id' => $id, 'uniacid' => $_W['uniacid']));
	plog('creditshop.goods.delete', "删除积分商城商品 ID: {$id}  <br/>商品名称: {$item['title']} ");
	message('商品删除成功！', $this->createPluginWebUrl('creditshop/goods', array('op' => 'display')), 'success');
} elseif ($operation == 'setgoodsproperty') {
	ca('creditshop.goods.edit');
	$id = intval($_GPC['id']);
	$type = $_GPC['type'];
	$data = intval($_GPC['data']);
	if (in_array($type, array('istop', 'isrecommand', 'istime', 'status'))) {
		$data = ($data == 1 ? '0' : '1');
		pdo_update('sz_yi_creditshop_goods', array($type => $data), array('id' => $id, 'uniacid' => $_W['uniacid']));
		if ($type == 'istop') {
			$typestr = '置顶';
		} else if ($type == 'isrecommand') {
			$typestr = '推荐';
		} else if ($type == 'istime') {
			$typestr = '限时';
		} else if ($type == 'status') {
			$typestr = '上下架';
		}
		plog('creditshop.goods.edit', "修改积分商城商品{$typestr}状态   ID: {$id}");
		die(json_encode(array('result' => 1, 'data' => $data)));
	}
	die(json_encode(array('result' => 0)));
}
load()->func('tpl');
include $this->template('goods');
