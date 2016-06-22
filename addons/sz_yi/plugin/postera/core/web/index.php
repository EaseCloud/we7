<?php
global $_W, $_GPC;
//check_shop_auth
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	ca('poster.view');
	if (checksubmit('submit')) {
		ca('poster.clear');
		load()->func('file');
		@rmdirs(IA_ROOT . '/addons/sz_yi/data/postera/' . $_W['uniacid']);
		@rmdirs(IA_ROOT . '/addons/sz_yi/data/qrcode/' . $_W['uniacid']);
		$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid LIMIT 1', array(':uniacid' => $_W['uniacid']));
		pdo_update('sz_yi_postera_qr', array('mediaid' => ''), array('acid' => $acid));
		plog('poster.clear', '清除海报缓存');
		message('缓存清除成功!', referer(), 'success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$params = array(':uniacid' => $_W['uniacid']);
	$condition = ' and uniacid=:uniacid ';
	if (!empty($_GPC['keyword'])) {
		$_GPC['keyword'] = trim($_GPC['keyword']);
		$condition .= ' AND `title` LIKE :title';
		$params[':title'] = '%' . trim($_GPC['keyword']) . '%';
	}
	if (!empty($_GPC['type'])) {
		$condition .= ' AND `type` = :type';
		$params[':type'] = intval($_GPC['type']);
	}
	$list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_postera') . " WHERE 1 {$condition} ORDER BY isdefault desc,createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
	foreach ($list as &$row) {
		$row['follows'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_postera_log') . ' where posterid=:posterid and uniacid=:uniacid', array(':posterid' => $row['id'], ':uniacid' => $_W['uniacid']));
	}
	unset($row);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_postera') . " where 1 {$condition} ", $params);
	$pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'post') {
	$id = intval($_GPC['id']);
	$plugin_coupon = p('coupon');
	if (empty($id)) {
		ca('poster.add');
	} else {
		ca('poster.edit|poster.view');
	}
	$item = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_postera') . ' WHERE id =:id and uniacid=:uniacid limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));
	if (!empty($item)) {
		$data = json_decode(str_replace('&quot;', '\'', $item['data']), true);
	}
	if (checksubmit('submit')) {
		load()->model('account');
		$acid = pdo_fetchcolumn('select acid from ' . tablename('account_wechats') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
		$data = array('uniacid' => $_W['uniacid'], 'title' => trim($_GPC['title']), 'type' => intval($_GPC['type']), 'keyword' => trim($_GPC['keyword']), 'bg' => save_media($_GPC['bg']), 'data' => htmlspecialchars_decode($_GPC['data']), 'resptitle' => trim($_GPC['resptitle']), 'respthumb' => save_media($_GPC['respthumb']), 'respdesc' => trim($_GPC['respdesc']), 'respurl' => trim($_GPC['respurl']), 'createtime' => time(), 'oktext' => trim($_GPC['oktext']), 'waittext' => trim($_GPC['waittext']), 'subcredit' => intval($_GPC['subcredit']), 'submoney' => $_GPC['submoney'], 'reccredit' => intval($_GPC['reccredit']), 'recmoney' => $_GPC['recmoney'], 'subtext' => trim($_GPC['subtext']), 'bedown' => intval($_GPC['bedown']), 'beagent' => intval($_GPC['beagent']), 'isopen' => intval($_GPC['isopen']), 'opentext' => trim($_GPC['opentext']), 'openurl' => trim($_GPC['openurl']), 'paytype' => intval($_GPC['paytype']), 'subpaycontent' => trim($_GPC['subpaycontent']), 'recpaycontent' => trim($_GPC['recpaycontent']), 'templateid' => trim($_GPC['templateid']), 'entrytext' => trim($_GPC['entrytext']), 'timestart' => strtotime($_GPC['time']['start']), 'timeend' => strtotime($_GPC['time']['end']), 'status' => intval($_GPC['status']), 'goodsid' => intval($_GPC['goodsid']));
		if ($data['timeend'] - $data['timestart'] <= 15 || ($data['timeend'] - $data['timestart']) / 86400 > 30) {
			message('海报有效期最短15秒，最长30天', '', 'error');
		}
		if ($plugin_coupon) {
			$data['reccouponid'] = intval($_GPC['reccouponid']);
			$data['reccouponnum'] = intval($_GPC['reccouponnum']);
			$data['subcouponid'] = intval($_GPC['subcouponid']);
			$data['subcouponnum'] = intval($_GPC['subcouponnum']);
		}
		if ($data['isdefault'] == 1) {
			pdo_update('sz_yi_postera', array('isdefault' => 0), array('uniacid' => $_W['uniacid'], 'isdefault' => 1, 'type' => $data['type']));
		}
		if (!empty($id)) {
			pdo_update('sz_yi_postera', $data, array('id' => $id, 'uniacid' => $_W['uniacid']));
			plog('poster.edit', "修改超级海报 ID: {$id}");
		} else {
			pdo_insert('sz_yi_postera', $data);
			$id = pdo_insertid();
			plog('poster.add', "添加超级海报 ID: {$id}");
		}
		$rule = pdo_fetch('select * from ' . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'sz_yi', ':name' => 'sz_yi:postera:' . $id));
		$rule_data = array('uniacid' => $_W['uniacid'], 'name' => 'sz_yi:postera:' . $id, 'module' => 'sz_yi', 'displayorder' => 0, 'status' => $data['status']);
		$keyword_data = array('uniacid' => $_W['uniacid'], 'module' => 'sz_yi', 'content' => trim($data['keyword']), 'type' => 1, 'displayorder' => 0, 'status' => $data['status']);
		if (empty($rule)) {
			pdo_insert('rule', $rule_data);
			$keyword_data['rid'] = pdo_insertid();
			pdo_insert('rule_keyword', $keyword_data);
		} else {
			pdo_update('rule_keyword', $keyword_data, array('rid' => $rule['id']));
		}
		$ruleauto = pdo_fetch('select * from ' . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'sz_yi', ':name' => 'sz_yi:postera:auto'));
		if (empty($ruleauto)) {
			$rule_data = array('uniacid' => $_W['uniacid'], 'name' => 'sz_yi:postera:auto', 'module' => 'sz_yi', 'displayorder' => 0, 'status' => 1);
			pdo_insert('rule', $rule_data);
			$rid = pdo_insertid();
			$keyword_data = array('uniacid' => $_W['uniacid'], 'rid' => $rid, 'module' => 'sz_yi', 'content' => 'SZ_YI_POSTERA', 'type' => 1, 'displayorder' => 0, 'status' => 1);
			pdo_insert('rule_keyword', $keyword_data);
		}
		message('更新海报成功！', $this->createPluginWebUrl('postera', array('op' => 'display')), 'success');
	}
	$imgroot = $_W['attachurl'];
	if (empty($_W['setting']['remote'])) {
		setting_load('remote');
	}
	if (!empty($_W['setting']['remote']['type'])) {
		$imgroot = $_W['attachurl_remote'];
	}
	if ($plugin_coupon) {
		if (!empty($item['subcouponid'])) {
			$subcoupon = $plugin_coupon->getCoupon($item['subcouponid']);
		}
		if (!empty($item['reccouponid'])) {
			$reccoupon = $plugin_coupon->getCoupon($item['reccouponid']);
		}
	}
	if (!empty($item['goodsid'])) {
		$goods = pdo_fetch('select id,title,thumb,commission_thumb,marketprice,productprice from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $item['goodsid'], ':uniacid' => $_W['uniacid']));
	}
	if (empty($item)) {
		$starttime = time();
		$endtime = strtotime(date('Y-m-d H:i', $starttime) . '+30 days');
	} else {
		$type = $item['coupontype'];
		$starttime = $item['timestart'];
		$endtime = $item['timeend'];
	}
} elseif ($operation == 'delete') {
	ca('poster.delete');
	$id = intval($_GPC['id']);
	$poster = pdo_fetch('SELECT id,title FROM ' . tablename('sz_yi_postera') . " WHERE id = '$id'");
	if (empty($poster)) {
		message('抱歉，海报不存在或是已经被删除！', $this->createPluginWebUrl('postera', array('op' => 'display')), 'error');
	}
	pdo_delete('sz_yi_postera', array('id' => $id, 'uniacid' => $_W['uniacid']));
	pdo_delete('sz_yi_postera_log', array('posterid' => $id, 'uniacid' => $_W['uniacid']));
	plog('poster.add', "删除超级海报 ID: {$id} 海报名称: {$poster['title']}");
	message('海报删除成功！', $this->createPluginWebUrl('postera', array('op' => 'display')), 'success');
} else if ($operation == 'setdefault') {
	ca('poster.setdefault');
	$id = intval($_GPC['id']);
	$poster = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_postera') . " WHERE id = '$id'");
	if (empty($poster)) {
		message('抱歉，海报不存在或是已经被删除！', $this->createPluginWebUrl('postera', array('op' => 'display')), 'error');
	}
	pdo_update('sz_yi_postera', array('isdefault' => 0), array('uniacid' => $_W['uniacid'], 'isdefault' => 1, 'type' => $poster['type']));
	pdo_update('sz_yi_postera', array('isdefault' => 1), array('uniacid' => $_W['uniacid'], 'id' => $poster['id']));
	plog('poster.setdefault', "设置默认超级海报 ID: {$id} 海报名称: {$poster['title']}");
	message('海报设置成功！', $this->createPluginWebUrl('postera', array('op' => 'display')), 'success');
}
load()->func('tpl');
include $this->template('index');
