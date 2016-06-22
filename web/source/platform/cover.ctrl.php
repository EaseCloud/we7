<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('reply');
$dos = array('mc', 'card', 'module', 'clerk');
$do = in_array($do, $dos) ? $do : 'module';

uni_user_permission_check('platform_cover_' . $do, true, 'cover');
$entries['mc']['title'] = '个人中心入口设置';
$entries['mc']['module'] = 'mc';
$entries['mc']['do'] = '';
$entries['mc']['url'] = url('mc/home', array('i' => $_W['uniacid']));
$entries['mc']['url_show'] = murl('mc/home', array(), true, true); 
$entries['card']['title'] = '会员卡入口设置';
$entries['card']['module'] = 'card';
$entries['card']['do'] = '';
$entries['card']['url'] = url('mc/bond/card', array('i' => $_W['uniacid']));
$entries['card']['url_show'] = murl('mc/bond/card', array(), true, true);

$entries['clerk']['title'] = '收银台关键字设置';
$entries['clerk']['module'] = 'clerk';
$entries['clerk']['do'] = '';
$entries['clerk']['url'] = url('entry', array('i' => $_W['uniacid'],'do' => 'home', 'm' => 'paycenter'));
$entries['clerk']['url_show'] = murl('entry', array('m' => 'paycenter', 'do' => 'home'), true, true);

if($do != 'module') {
	$entry = $entries[$do];
	if($do == 'mc') {
		$_W['page']['title'] = '个人中心入口设置 - 会员中心访问入口- 会员中心';
	}
	if($do == 'clerk') {
		$_W['page']['title'] = '店员操作入口设置 - 店员操作';
	}
	if($do == 'card') {
		
		$sql = 'SELECT `status` FROM ' . tablename('mc_card') . " WHERE `uniacid` = :uniacid";
		$list = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
		if ($list['status'] == 0) {
			message('会员卡功能未开启', url('mc/card'), 'error');
		}
		$_W['page']['title'] = '会员卡入口设置 - 会员中心访问入口- 会员中心';
	}
} else {
	$eid = intval($_GPC['eid']);
	if(empty($eid)) {
		message('访问错误');
	}
	$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `eid`=:eid';
	$pars = array();
	$pars[':eid'] = $eid;
	$entry = pdo_fetch($sql, $pars);
	if(empty($entry) || $entry['entry'] != 'cover') {
		message('访问错误');
	}
	load()->model('module');
	$module = module_fetch($entry['module']);
	if(empty($module)) {
		message('访问错误');
	}
	$entry['url'] = murl('entry', array('do' => $entry['do'], 'm' => $entry['module']));
	$entry['url_show'] = murl('entry', array('do' => $entry['do'], 'm' => $entry['module']), true, true);
	$cover['title'] = $entry['title'];
		define('FRAME', 'ext');
	$types = module_types();

	if(!$GLOBALS['ext_type']) {
		define('ACTIVE_FRAME_URL', url('platform/cover', array('eid' => $entry['eid'])));
	} else {
		echo 8;
		define('ACTIVE_FRAME_URL', url('home/welcome/ext', array('m' => $entry['module'])));
	}
	$frames = buildframes(array(FRAME));
	$frames = $frames[FRAME];
	}

$sql = "SELECT * FROM " . tablename('cover_reply') . ' WHERE `module` = :module AND `do` = :do AND uniacid = :uniacid';
$pars = array();
$pars[':module'] = $entry['module'];
$pars[':do'] = $entry['do'];
$pars[':uniacid'] = $_W['uniacid'];
$cover = pdo_fetch($sql, $pars);

if(!empty($cover)) {
	$cover['saved'] = true;
	if(!empty($cover['thumb'])) {
		$cover['src'] = tomedia($cover['thumb']);
	}
	$cover['url_show'] = $entry['url_show'];
	$reply = reply_single($cover['rid']);
	$entry['title'] = $cover['title'];
} else {
	$cover['title'] = $entry['title'];
	$cover['url_show'] = $entry['url_show'];
}
if(empty($reply)) {
	$reply = array();
}

if (checksubmit('submit')) {
	if(trim($_GPC['keywords']) == '') {
		message('必须输入触发关键字.');
	}
	
	$keywords = @json_decode(htmlspecialchars_decode($_GPC['keywords']), true);
	if(empty($keywords)) {
		message('必须填写有效的触发关键字.');
	}
	$rule = array(
		'uniacid' => $_W['uniacid'],
		'name' => $entry['title'],
		'module' => 'cover', 
		'status' => intval($_GPC['status']),
	);
	if(!empty($_GPC['istop'])) {
		$rule['displayorder'] = 255;
	} else {
		$rule['displayorder'] = range_limit($_GPC['displayorder'], 0, 254);
	}
	if (!empty($reply)) {
		$rid = $reply['id'];
		$result = pdo_update('rule', $rule, array('id' => $rid));
	} else {
		$result = pdo_insert('rule', $rule);
		$rid = pdo_insertid();
	}
	
	if (!empty($rid)) {
				$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `uniacid`=:uniacid';
		$pars = array();
		$pars[':rid'] = $rid;
		$pars[':uniacid'] = $_W['uniacid'];
		pdo_query($sql, $pars);

		$rowtpl = array(
			'rid' => $rid,
			'uniacid' => $_W['uniacid'],
			'module' => 'cover',
			'status' => $rule['status'],
			'displayorder' => $rule['displayorder'],
		);
		foreach($keywords as $kw) {
			$krow = $rowtpl;
			$krow['type'] = range_limit($kw['type'], 1, 4);
			$krow['content'] = $kw['content'];
			pdo_insert('rule_keyword', $krow);
		}
		
		$entry = array(
			'uniacid' => $_W['uniacid'],
			'multiid' => 0,
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'thumb' => $_GPC['thumb'],
			'url' => $entry['url'],
			'do' => $entry['do'],
			'module' => $entry['module'],
		);
		if (empty($cover['id'])) {
			pdo_insert('cover_reply', $entry);
		} else {
			pdo_update('cover_reply', $entry, array('id' => $cover['id']));
		}
		message('封面保存成功！', 'refresh', 'success');
	} else {
		message('封面保存失败, 请联系网站管理员！');
	}
}

template('platform/cover');