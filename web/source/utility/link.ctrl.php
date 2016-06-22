<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$callback = $_GPC['callback'];
load()->model('module');
load()->model('site');
if ($do == 'modulelink') {
	$modules = uni_modules_app_binding();
	$entries = array();
	foreach ($modules as $module => $item) {
		$entries[$module] = module_entries($module, array('menu'));
		$entries[$module]['title'] = $item['title'];
	}
}
elseif ($do == 'articlelist') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$result['list'] = pdo_fetchall("SELECT id, title, thumb, description, content, author, incontent, linkurl,  createtime, uniacid FROM ".tablename('site_article')." WHERE uniacid = :uniacid ORDER BY displayorder DESC, id  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid']), 'id');
	if (!empty($result['list'])) {
		foreach ($result['list'] as $k => &$v) {
			$v['thumb_url'] = tomedia($v['thumb']);
			$v['createtime'] = date('Y-m-d H:i', $v['createtime']);
			$v['name'] = cutstr($v['name'], 10);
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('site_article').' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null'));
	}
	message($result, '', 'ajax');
}elseif ($do == 'pagelist') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('site_page')." WHERE uniacid = :uniacid AND type = '1' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid']), 'id');
	if (!empty($result['list'])) {
		foreach ($result['list'] as $k => &$v) {
			$v['createtime'] = date('Y-m-d H:i', $v['createtime']);
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('site_page'). ' WHERE uniacid = :uniacid AND type = 1', array(':uniacid' => $_W['uniacid']));
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'true'));
	}
	message($result, '', 'ajax');
} elseif ($do == 'newslist') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$sql = "SELECT n.id, n.title FROM ". tablename('rule')."AS r,". tablename('news_reply'). " AS n WHERE r.id = n.rid AND r.module = :news AND r.uniacid = :uniacid ORDER BY n.displayorder DESC LIMIT ". ($pindex - 1) * $psize . ',' . $psize;
	$result['list'] = pdo_fetchall($sql, array(':news' => 'news', ':uniacid' => $_W['uniacid']), 'id');
	if (!empty($result['list'])) {
		$sql = "SELECT COUNT(*) FROM ". tablename('rule')."AS r,". tablename('news_reply'). " AS n WHERE r.id = n.rid AND r.module = :news AND r.uniacid = :uniacid ";
		$total = pdo_fetchcolumn($sql, array(':news' => 'news', ':uniacid' => $_W['uniacid']));
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null'));
	}
	message($result, '', 'ajax');
} elseif ($do == 'catelist') {
	$condition = '';
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND name LIKE :name";
		$param = array(':uniacid' => $_W['uniacid'], ':name' => '%'.trim($_GPC['keyword']).'%');
	} else {
		$param = array(':uniacid' => $_W['uniacid']);
	}
	$category = pdo_fetchall("SELECT id, uniacid, parentid, name FROM ".tablename('site_category')." WHERE uniacid = :uniacid ". $condition." ORDER BY parentid, displayorder DESC, id", $param, 'id');
	foreach ($category as $index => $row) {
		if (!empty($row['parentid'])){
			$category[$row['parentid']]['children'][$row['id']] = $row;
			unset($category[$index]);
		}
	}
	message($category, '', 'ajax');
} elseif ($do == 'page') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('site_page')." WHERE uniacid = :uniacid AND type = '1' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid']), 'id');
	if (!empty($result['list'])) {
		foreach ($result['list'] as $k => &$v) {
			$v['createtime'] = date('Y-m-d H:i', $v['createtime']);
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('site_page'). ' WHERE uniacid = :uniacid AND type = 1', array(':uniacid' => $_W['uniacid']));
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'true'));
	}
} elseif ($do == 'news') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$sql = "SELECT n.id, n.title FROM ". tablename('rule')."AS r,". tablename('news_reply'). " AS n WHERE r.id = n.rid AND r.module = :news AND r.uniacid = :uniacid ORDER BY n.displayorder DESC LIMIT ". ($pindex - 1) * $psize . ',' . $psize;
	$result['list'] = pdo_fetchall($sql, array(':news' => 'news', ':uniacid' => $_W['uniacid']), 'id');
	if (!empty($result['list'])) {
		$sql = "SELECT COUNT(*) FROM ". tablename('rule')."AS r,". tablename('news_reply'). " AS n WHERE r.id = n.rid AND r.module = :news AND r.uniacid = :uniacid ";
		$total = pdo_fetchcolumn($sql, array(':news' => 'news', ':uniacid' => $_W['uniacid']));
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null'));
	}
} elseif ($do == 'article') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$result['list'] = pdo_fetchall("SELECT id, title, thumb, description, content, author, incontent, linkurl,  createtime, uniacid FROM ".tablename('site_article')." WHERE uniacid = :uniacid ORDER BY displayorder DESC, id  LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':uniacid' => $_W['uniacid']), 'id');
	if (!empty($result['list'])) {
		foreach ($result['list'] as $k => &$v) {
			$v['thumb_url'] = tomedia($v['thumb']);
			$v['createtime'] = date('Y-m-d H:i', $v['createtime']);
			$v['name'] = cutstr($v['name'], 10);
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('site_article').' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']));
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null'));
	}
	$category = pdo_fetchall("SELECT id, uniacid, parentid, name FROM ".tablename('site_category')." WHERE uniacid = :uniacid ORDER BY parentid, displayorder DESC, id", array(':uniacid' => $_W['uniacid']), 'id');
	foreach ($category as $index => $row) {
		if (!empty($row['parentid'])){
			$category[$row['parentid']]['children'][$row['id']] = $row;
			unset($category[$index]);
		}
	}
} elseif ($do == 'newschunk') {
	$result = array();
	$psize = 10;
	$pindex = max(1, intval($_GPC['page']));
	$rids = pdo_getall('rule', array('uniacid' => $_W['uniacid'], 'module' => 'news'), array(), 'id');
	$keys = array_keys($rids);
	$keys[] = 0;
	$keys = implode(',', $keys);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('news_reply') . " WHERE parent_id = 0 AND rid IN ({$keys})");
	$sql = "SELECT id,title,createtime FROM ". tablename('news_reply') . " WHERE parent_id = 0 AND rid IN ({$keys}) ORDER BY id DESC LIMIT ". ($pindex - 1) * $psize . ',' . $psize;
	$result['list'] = pdo_fetchall($sql, array(), 'id');
	if (!empty($result['list'])) {
		foreach($result['list'] as &$row) {
			$row['items'] = pdo_fetchall('SELECT * FROM ' . tablename('news_reply') . ' WHERE parent_id = :parent_id OR id = :id', array(':parent_id' => $row['id'], ':id' => $row['id']));
		}
		$result['pager'] = pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null'));
	}
	message($result, '', 'ajax');
} else {
		$permission = uni_user_permission_exist();
	$has_permission = array();
	if(is_error($permission)) {
		$has_permission = array(
			'system' => array(),
			'modules' => array()
		);
		$has_permission['system'] = uni_user_permission('system');
				$temp_module = pdo_fetchall('SELECT * FROM ' . tablename('users_permission') . ' WHERE uniacid = :uniacid AND uid = :uid AND type != :type', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['uid'], ':type' => 'system'), 'type');
		if(!empty($temp_module)) {
			$has_permission['modules'] = array_keys($temp_module);
			foreach($temp_module as $row) {
				if($row['permission'] == 'all') {
					$has_permission[$row['type']] = array('all');
				} else {
					$has_permission[$row['type']] = explode('|', $row['permission']);
				}
			}
		}
	}

	$modulemenus = array();
	$modules = uni_modules_app_binding();
	foreach($modules as $module) {
		$m = $module['name'];
		if(empty($has_permission) || (!empty($has_permission) && in_array($m, $has_permission['modules']))) {
			$entries = $module['entries'];
			if(!empty($has_permission[$m]) && $has_permission[$m][0] != 'all') {
				if(!in_array($m.'_home', $has_permission[$m])) {
					unset($entries['home']);
				}
				if(!in_array($m.'_profile', $has_permission[$m])) {
					unset($entries['profile']);
				}
				if(!in_array($m.'_shortcut', $has_permission[$m])) {
					unset($entries['shortcut']);
				}
				if(!empty($entries['cover'])) {
					foreach($entries['cover'] as $k => $row) {
						if(!in_array($m.'_cover_'.$row['do'], $has_permission[$m])) {
							unset($entries['cover'][$k]);
						}
					}
				}
			}

			$module['cover'] = $entries['cover'];
			$module['home'] = $entries['home'];
			$module['profile'] = $entries['profile'];
			$module['shortcut'] = $entries['shortcut'];
			$module['function'] = $entries['function'];
			$modulemenus[$module['type']][$module['name']] = $module;
		}
	}
	$modtypes = module_types();

	$sysmenus = array(
		array('title'=>'微站首页','url'=> murl('home')),
		array('title'=>'个人中心','url'=> murl('mc')),
	);

	if(empty($has_permission) || (!empty($has_permission) && in_array('mc_card', $has_permission['system']))) {
		$cardmenus = array(
			array('title'=>'我的会员卡','url'=> murl('mc/bond/mycard')),
			array('title'=>'消息','url'=> murl('mc/card/notice')),
			array('title'=>'签到','url'=> murl('mc/card/sign_display')),
			array('title'=>'推荐','url'=> murl('mc/card/recommend')),
			array('title'=>'适用门店','url'=> murl('mc/store')),
			array('title'=>'完善会员资料','url'=> murl('mc/profile')),
		);
	}
	if(empty($has_permission) || (!empty($has_permission) && in_array('site_multi_display', $has_permission['system']))) {
		$multis = pdo_fetchall('SELECT id,title FROM ' . tablename('site_multi') . ' WHERE uniacid = :uniacid AND status != 0', array(':uniacid' => $_W['uniacid']));
		if(!empty($multis)) {
			foreach($multis as $multi) {
				$multimenus[] = array('title' => $multi['title'], 'url' => murl('home', array('t' => $multi['id'])));
			}
		}
	}

	$linktypes = array(
		'cover' => '封面链接',
		'home' => '微站首页导航',
		'profile'=>'微站个人中心导航',
		'shortcut' => '微站快捷功能导航',
		'function' => '微站独立功能'
	);
}
template('utility/link');
