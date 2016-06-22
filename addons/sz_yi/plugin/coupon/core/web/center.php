<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
//check_shop_auth
ca('coupon.center.view');
$set = $this->getSet();
if (checksubmit('submit')) {
	ca('coupon.center.save');
	$data = is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array();
	$coverkeyword = $data['keyword'];
	if (!empty($coverkeyword)) {
		$rule = pdo_fetch('select * from ' . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'cover', ':name' => 'sz_yi优惠券入口设置'));
		if (!empty($rule)) {
			$keyword = pdo_fetch('select * from ' . tablename('rule_keyword') . ' where uniacid=:uniacid and rid=:rid limit 1', array(':uniacid' => $_W['uniacid'], ':rid' => $rule['id']));
			$cover = pdo_fetch('select * from ' . tablename('cover_reply') . ' where uniacid=:uniacid and rid=:rid limit 1', array(':uniacid' => $_W['uniacid'], ':rid' => $rule['id']));
		}
		$kw = pdo_fetch('select * from ' . tablename('rule_keyword') . ' where uniacid=:uniacid and content=:content and id<>:id limit 1', array(':uniacid' => $_W['uniacid'], ':content' => trim($coverkeyword), ':id' => $keyword['id']));
		if (!empty($kw)) {
			message("关键词 {$coverkeyword} 已经存在!", '', 'error');
		}
		$status = empty($data['closecenter']) ? 1 : 0;
		$rule_data = array('uniacid' => $_W['uniacid'], 'name' => 'sz_yi优惠券入口设置', 'module' => 'cover', 'displayorder' => 0, 'status' => $status);
		if (empty($rule)) {
			pdo_insert('rule', $rule_data);
			$rid = pdo_insertid();
		} else {
			pdo_update('rule', $rule_data, array('id' => $rule['id']));
			$rid = $rule['id'];
		}
		$keyword_data = array('uniacid' => $_W['uniacid'], 'rid' => $rid, 'module' => 'cover', 'content' => trim($coverkeyword), 'type' => 1, 'displayorder' => 0, 'status' => $status);
		if (empty($keyword)) {
			pdo_insert('rule_keyword', $keyword_data);
		} else {
			pdo_update('rule_keyword', $keyword_data, array('id' => $keyword['id']));
		}
		$cover_data = array('uniacid' => $_W['uniacid'], 'rid' => $rid, 'module' => $this->modulename, 'title' => trim($data['title']), 'description' => trim($data['desc']), 'thumb' => tomedia($data['icon']), 'url' => $this->createPluginMobileUrl('coupon'));
		if (empty($cover)) {
			pdo_insert('cover_reply', $cover_data);
		} else {
			pdo_update('cover_reply', $cover_data, array('id' => $cover['id']));
		}
	}
	$imgs = $_GPC['adv_img'];
	$urls = $_GPC['adv_url'];
	$advs = array();
	if (is_array($imgs)) {
		foreach ($imgs as $key => $img) {
			$advs[] = array('img' => trim($img), 'url' => trim($urls[$key]));
		}
	}
	$data['advs'] = $advs;
	m('cache')->set('template_' . $this->pluginname, $data['style']);
	$this->updateSet($data);
	plog('coupon.center.save', '修改领券中心设置');
	message('设置领券中心成功!', referer(), 'success');
}
$advs = is_array($set['advs']) ? $set['advs'] : array();
$shop = m('common')->getSysset('shop');
$styles = array();
$dir = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/template/mobile/';
if ($handle = opendir($dir)) {
	while (($file = readdir($handle)) !== false) {
		if ($file != '..' && $file != '.') {
			if (is_dir($dir . '/' . $file)) {
				$styles[] = $file;
			}
		}
	}
	closedir($handle);
}
load()->func('tpl');
include $this->template('center');
