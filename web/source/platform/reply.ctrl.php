<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

load()->model('reply');
load()->model('module');
$dos = array('display', 'post', 'delete');
$do = in_array($do, $dos) ? $do : 'display';
$m = $_GPC['m'];
if(empty($m)) {
	message('错误访问.');
}
uni_user_permission_check('platform_reply_' . $m, true, 'reply');
$module = module_fetch($m);

if(empty($module) || empty($module['isrulefields'])) {
	message('访问无权限.');
}
if(!in_array($m, $sysmods)) {
		define('FRAME', 'ext');
	$types = module_types();
	define('ACTIVE_FRAME_URL', url('home/welcome/ext', array('m' => $m)));
	$frames = buildframes(array(FRAME), $m);
	$frames = $frames[FRAME];
	}
$_W['page']['title'] = $module['title'];
load()->model('extension');
if (ext_module_checkupdate($module['name'])) {
	message('系统检测到该模块有更新，请点击“<a href="'.url('extension/module/upgrade', array('m' => $m)).'">更新模块</a>”后继续使用！', '', 'error');
}

if(in_array($m, array('custom'))) {
	$site = WeUtility::createModuleSite($m);
	$site_urls = $site->getTabUrls();
}

if($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$cids = $parentcates = $list =  array();
	$types = array('', '等价', '包含', '正则表达式匹配', '直接接管');
	
	$condition = 'uniacid = :uniacid AND `module`=:module';
	$params = array();
	$params[':uniacid'] = $_W['uniacid'];
	$params[':module'] = $m;
	$status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
	if ($status != -1){
		$condition .= " AND status = '{$status}'";
	}
	if(isset($_GPC['keyword'])) {
		$condition .= ' AND `name` LIKE :keyword';
		$params[':keyword'] = "%{$_GPC['keyword']}%";
	}

	$replies = reply_search($condition, $params, $pindex, $psize, $total);
	$pager = pagination($total, $pindex, $psize);
	if (!empty($replies)) {
		foreach($replies as &$item) {
			$condition = '`rid`=:rid';
			$params = array();
			$params[':rid'] = $item['id'];
			$item['keywords'] = reply_keywords_search($condition, $params);
			$entries = module_entries($m, array('rule'),$item['id']);
			if(!empty($entries)) {
				$item['options'] = $entries['rule'];
			}
		}
	}
	template('platform/reply');
}

if($do == 'post') {
	if ($_W['isajax'] && $_W['ispost']) {
		
		$sql = 'SELECT `rid` FROM ' . tablename('rule_keyword') . " WHERE `uniacid` = :uniacid  AND `content` = :content";
		$result = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':content' => $_GPC['keyword']));
		if (!empty($result)) {
			$keywords = array();
			foreach ($result as $reply) {
				$keywords[] = $reply['rid'];
			}
			$rids = implode($keywords, ',');
			$sql = 'SELECT `id`, `name` FROM ' . tablename('rule') . " WHERE `id` IN ($rids)";
			$rules = pdo_fetchall($sql);
			exit(@json_encode($rules));
		}
		exit('success');
	}
	$rid = intval($_GPC['rid']);
	if(!empty($rid)) {
		$reply = reply_single($rid);
		if(empty($reply) || $reply['uniacid'] != $_W['uniacid']) {
			message('抱歉，您操作的规则不在存或是已经被删除！', url('platform/reply', array('m' => $m)), 'error');
		}
		foreach($reply['keywords'] as &$kw) {
			$kw = array_elements(array('type', 'content'), $kw);
		}
	}
	if(checksubmit('submit')) {
		if(empty($_GPC['name'])) {
			message('必须填写回复规则名称.');
		}
		$keywords = @json_decode(htmlspecialchars_decode($_GPC['keywords']), true);
		if(empty($keywords)) {
			message('必须填写有效的触发关键字.');
		}
		$rule = array(
			'uniacid' => $_W['uniacid'],
			'name' => $_GPC['name'],
			'module' => $m,
			'status' => intval($_GPC['status']),
			'displayorder' => intval($_GPC['displayorder_rule']),
		);
		if(!empty($_GPC['istop'])) {
			$rule['displayorder'] = 255;
		} else {
			$rule['displayorder'] = range_limit($rule['displayorder'], 0, 254);
		}
		$module = WeUtility::createModule($m);
		
		if(empty($module)) {
			message('抱歉，模块不存在请重新其它模块！');
		}
		$msg = $module->fieldsFormValidate();
		
		if(is_string($msg) && trim($msg) != '') {
			message($msg);
		}
		if (!empty($rid)) {
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
				'module' => $rule['module'],
				'status' => $rule['status'],
				'displayorder' => $rule['displayorder'],
			);
			foreach($keywords as $kw) {
				$krow = $rowtpl;
				$krow['type'] = range_limit($kw['type'], 1, 4);
				$krow['content'] = $kw['content'];
				pdo_insert('rule_keyword', $krow);
			}
			$rowtpl['incontent'] = $_GPC['incontent'];
			$module->fieldsFormSubmit($rid);
			message('回复规则保存成功！', url('platform/reply/post', array('m' => $m, 'rid' => $rid)));
		} else {
			message('回复规则保存失败, 请联系网站管理员！');
		}
	}
	template('platform/reply-post');
}

if($do == 'delete') {
	$rids = $_GPC['rid'];
	if(!is_array($rids)) {
		$rids = array($rids);
	}
	if(empty($rids)) {
		message('非法访问.');
	}
	foreach($rids as $rid) {
		$rid = intval($rid);
		$reply = reply_single($rid);
		if(empty($reply) || $reply['uniacid'] != $_W['uniacid']) {
			message('抱歉，您操作的规则不在存或是已经被删除！', url('platform/reply', array('m' => $m)), 'error');
		}
				if (pdo_delete('rule', array('id' => $rid))) {
			pdo_delete('rule_keyword', array('rid' => $rid));
						pdo_delete('stat_rule', array('rid' => $rid));
			pdo_delete('stat_keyword', array('rid' => $rid));
						$module = WeUtility::createModule($reply['module']);
			if (method_exists($module, 'ruleDeleted')) {
				$module->ruleDeleted($rid);
			}
		}
	}
	message('规则操作成功！', referer());
}