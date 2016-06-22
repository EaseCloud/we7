<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$type = $do;
$do = $_GPC['foo'];
$m = $_GPC['m'];
uni_user_permission_check('platform_nav_' . $do, true, 'nav');
$dos = array('post', 'delete', 'saves', 'display');
$do = in_array($do, $dos) ? $do : 'display';
$types = array(
	'home' => array('name' => 'home', 'title' => '首页', 'visiable' => true, 'position' => 1),
	'profile' => array('name' => 'profile', 'title' => '个人中心', 'visiable' => true, 'position' => 2),
);
$type = array_key_exists($type, $types) ? $types[$type] : $types['home'];
$titles = array('home'=>'微站首页导航图标', 'profile'=>'个人中心功能条目');
$_W['page']['title'] = $titles[$type['name']];
$setting = uni_setting($_W['uniacid'], 'default_site');
$default_site = intval($setting['default_site']);

load()->model('module');
$modules = uni_modules();
if(!empty($m)) {
	$module = module_fetch($m);
	if(empty($module)) {
		message('访问错误.');
	}
}

if($do != 'display') {
	define('FRAME', 'site');
	$frames = buildframes(array(FRAME));
	$frames = $frames[FRAME];
}

if($do == 'post') {
	$id = intval($_GPC['id']);
	$_W['page']['title'] = empty($id) ? '添加' . $title[$type['name']] : '编辑' . $title[$type['name']];
	if($_GPC['do'] != 'profile') {
		$multis = pdo_fetchall('SELECT * FROM ' . tablename('site_multi') .' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
	}
	if(!empty($id)) {
		$item = pdo_fetch("SELECT * FROM " . tablename('site_nav') . " WHERE `uniacid` = :uniacid AND `id` = :id" , array(':uniacid' => $_W['uniacid'], ':id' => $id));
		if(empty($item)) {
			message('抱歉，导航不存在或是已经删除！', '', 'error');
		}
		$item['css'] = unserialize($item['css']);
		if(strexists($item['icon'], 'images/') || strexists($item['icon'], 'http')) {
			$item['fileicon'] = $item['icon'];
			$item['icon'] = '';
		}
	}
	if(checksubmit('submit')) {
		if(empty($_GPC['title'])) {
			message('抱歉，请输入导航菜单的名称！', '', 'error');
		}
		$url = ((strexists($_GPC['url'], 'http://') || strexists($_GPC['url'], 'https://')) && !strexists($_GPC['url'], '#wechat_redirect')) ? $_GPC['url'] . '#wechat_redirect' : $_GPC['url'];
		$data = array(
			'uniacid' => $_W['uniacid'],
			'multiid' => intval($_GPC['multid']),
			'section' => intval($_GPC['section']),
			'name' => $_GPC['title'],
			'description' => $_GPC['description'],
			'displayorder' => intval($_GPC['displayorder']),
			'url' => $url,
			'status' => intval($_GPC['status']),
		);
		if(empty($id) || empty($item['module'])) {
			$data['position'] = $type['position'];
		}
		if ($data['section'] > 10) {
			$data['section'] = 10;
		}
				$icontype = $_GPC['icontype'];
		if ($icontype == 1) {
			$data['icon'] = '';
			$data['css'] = serialize(array(
					'icon' => array(
						'font-size' => $_GPC['icon']['size'],
						'color' => $_GPC['icon']['color'],
						'width' => $_GPC['icon']['size'],
						'icon' => empty($_GPC['icon']['icon']) ? 'fa fa-external-link' : $_GPC['icon']['icon'],
					),
					'name' => array(
						'color' => $_GPC['icon']['color'],
					),
				)
			);
		} else {
			$data['css'] = '';
			$data['icon'] = $_GPC['iconfile'];
		}
		if(empty($id)) {
			pdo_insert('site_nav', $data);
		} else {
			pdo_update('site_nav', $data, array('id' => $id));
		}
		message('导航更新成功！', url('site/nav', array('do'=>$type['name'], 'multiid' => $_GPC['multiid'], 'f' => $_GPC['f'])), 'success');
	}
	template('site/nav');
}

if($do == 'delete') {
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM " . tablename('site_nav') . " WHERE `uniacid` = :uniacid AND `id` = :id" , array(':uniacid' => $_W['uniacid'], ':id' => $id));
	if(empty($item)) {
		message('抱歉，导航不存在或是已经删除！', '', 'error');
	}
	load()->func('file');
	if(!empty($item['icon'])) {
		file_delete($item['icon']);
	}
	pdo_delete('site_nav', array('id' => $id));
	message('导航删除成功！', referer(), 'success');
}

if($do == 'saves') {
	$titles = $_GPC['title'];
	$urls = $_GPC['url'];
	$displayorders = $_GPC['displayorder'];
	$sections = $_GPC['section'];
	$filter = array();
	$filter['uniacid'] = $_W['uniacid'];
	foreach($titles as $key => $t) {
		$id = intval($key);
		$filter['id'] = intval($id);
		if(!empty($t)) {
			$rec = array(
				'name' => $t,
				'displayorder' => intval($displayorders[$id]),
				'section' => intval($sections[$id]),
				'url' => $urls[$id]
			);
			pdo_update('site_nav', $rec, $filter);
		}
	}
	message('批量编辑成功.', referer(), 'success');
}

if($do == 'display') {
	if(!empty($module)) {
				$types = module_types();
		define('ACTIVE_FRAME_URL', url('home/welcome/ext', array('m' => $_GPC['m'])));
		$tytitle = array('home' => '微站首页导航图标', 'profile' => '个人中心功能条目');

		$entries = module_entries($m, array($type['name']));
		if(empty($entries)) {
			message('访问错误, 当前模块不提供此功能.');
		}

		if($module['issolution']) {
			$solution = $module;
			define('FRAME', 'solution');
		} else {
			define('FRAME', 'ext');
			$types = module_types();
			define('ACTIVE_FRAME_URL', url('home/welcome/ext', array('m' => $module['name'])));
		}
	} else {
		define('FRAME', 'site');
	}
	$frames = buildframes(array(FRAME), $module['name']);
	$frames = $frames[FRAME];

	if($_W['ispost']) {
		$ret = intval($_GPC['ret']) == '1' ? 1 : 0;
		$set = @json_decode(base64_decode($_GPC['dat']), true);
		if(is_array($set)) {
			if (!empty($set['id'])) {
				$sql = $sql = 'SELECT * FROM ' . tablename('site_nav') . ' WHERE id = :id';
				$pars[':id'] = $set['id'];
			} else {
				$sql = 'SELECT * FROM ' . tablename('site_nav') . ' WHERE `position`=:position AND `uniacid`=:uniacid AND `module`=:module AND `url`=:url';
				$pars[':uniacid'] = $_W['uniacid'];
				$pars[':module'] = $set['module'];
				$pars[':position'] = $type['position'];
				$pars[':url'] = $set['url'];
			}
			if(isset($set['multiid'])) {
				$sql .= ' AND `multiid`=:multiid';
				$pars[':multiid'] = $set['multiid'];
			} 
			$nav = pdo_fetch($sql, $pars);
			if(!empty($nav)) {
				$record = array('status' => $ret);
				if(!empty($module)) {
					$record = array('status' => $ret, 'multiid' => $default_site);
				}
				if(pdo_update('site_nav', $record, array('id' => $nav['id'])) !== false) {
					exit('success');
				}
			} else {
				$nav = array();
				$nav['uniacid'] = $_W['uniacid'];
				$nav['module'] = $set['module'];
				$nav['displayorder'] = 0;
				$nav['name'] = $set['title'];
				$nav['position'] = $type['position'];
				$nav['url'] = $set['url'];
				$nav['status'] = $ret;
				if(!empty($module) && $type['name'] != 'profile') {
					$nav['multiid'] = $default_site;
				} else {
					$nav['multiid'] = 0;
				}
				if(pdo_insert('site_nav', $nav)) {
					exit('success');
				}
			}
		}
		exit();
	}

	$bindings = array();
	if(!empty($module)) {
		$modulenames = array($m);
	} else {
		$modulenames = array_keys($modules);
	}
	foreach($modulenames as $modulename) {
		$entries = module_entries($modulename, array($type['name']));
		if(!empty($entries[$type['name']])) {
			$bindings[$modulename] = $entries[$type['name']];
		}
	}
	$entries = array();
	if(!empty($bindings)) {
		foreach($bindings as $modulename => $group) {
			foreach($group as $bind) {
				$entries[] = array('module' => $modulename, 'from' => $bind['from'], 'title' => $bind['title'], 'url' => $bind['url']);
			}
		}
	}
	$multiid = intval($_GPC['multiid']);
	$multis = pdo_fetchall('SELECT * FROM ' . tablename('site_multi') .' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']), 'id');
	$site = $multis[$multiid];
	
	$pars = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$condition = '';
	if(!empty($module)) {
		$condition .= ' AND `module`=:module';
		$pars[':module'] = $m;
	}
	if(!empty($_GPC['do']) && $_GPC['do'] != 'profile') {
		if(!empty($_GPC['multiid'])) {
			if($multiid > 0) {
				$condition .= ' AND `multiid`=:multiid';
				$pars[':multiid'] = $multiid;
			}
		} else {
			$condition .= ' AND `multiid`=:multiid';
			$pars[':multiid'] = $default_site;
		}
	}

	$sql = 'SELECT * FROM ' . tablename('site_nav') . ' WHERE `uniacid`=:uniacid AND `position`=' . $type['position'] . $condition . ' ORDER BY `displayorder` DESC, id ASC';
	$navs = pdo_fetchall($sql, $pars);
	$navigations = array();
	if(!empty($navs)) {
		foreach($navs as $nav) {
			
			if (!empty($nav['icon'])) {
				$nav['icon'] = tomedia($nav['icon']);
			}
			if (is_serialized($nav['css'])) {
				$nav['css'] = iunserializer($nav['css']);
			}
			if(empty($nav['css']['icon']['icon'])) {
				$nav['css']['icon']['icon'] = 'fa fa-external-link';
			}
			$navigations[] = array(
				'id' => $nav['id'],
				'module' => $nav['module'],
				'title' => $nav['name'],
				'url' => $nav['url'],
				'from' => $nav['module'] ? 'define' : 'custom',
				'status' => $nav['status'],
				'remove' => true,
				'displayorder' => $nav['displayorder'],
				'icon' => $nav['icon'],
				'css' => $nav['css'],
				'multiid' => $nav['multiid'],
				'multi_title' => $multis[$nav['multiid']]['title'],
				'section' => $nav['section'],
			);
		}
	}
	$navigations_extend = array();
	foreach($entries as $row) {
		$match = false;
		foreach($navigations as $nav) {
			if($row['module'] == $nav['module'] && str_replace('&wxref=mp.weixin.qq.com#wechat_redirect', '', $row['url']) == str_replace('&wxref=mp.weixin.qq.com#wechat_redirect', '', $nav['url'])) {
				$match = true;
				break;
			}
		}
		if(!$match) {
			$navigations_extend[] = $row;
		}
	}
	$ds = array_merge($navigations, $navigations_extend);
	$froms = array(
		'call' => '动态数据',
				'custom' => '用户添加',
	);
	$siteid = intval($_GPC['multiid']);
	if(empty($siteid)) {
		$siteid = $default_site;
	}
	$styleid = pdo_fetchcolumn("SELECT styleid FROM ".tablename('site_multi')." WHERE id = '{$siteid}'");
	if (!empty($styleid)) {
		$style = pdo_fetch("SELECT templateid, name FROM ".tablename('site_styles')." WHERE id = '{$styleid}'");
	}
	$template = pdo_fetch("SELECT * FROM ".tablename('site_templates')." WHERE id = '{$style['templateid']}'");
		load()->model('extension');
	$manifest = ext_template_manifest($template['name']);
	if (isset($manifest['sections']) && $manifest['sections'] != $template['sections']) {
		$template['sections'] = $manifest['sections'];
		pdo_update('site_templates', array('sections' => $manifest['sections']), array('id' => $template['id']));
	}
	template('site/nav');
}
