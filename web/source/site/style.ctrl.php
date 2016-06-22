<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$templateid = intval($_GPC['templateid']);
$dos = array('default', 'designer', 'module', 'createtemplate', 'template', 'copy', 'build', 'del');
$do = in_array($do, $dos) ? $do : 'display';

if ($do == 'template') {
	uni_user_permission_check('site_style_template');
	$setting = uni_setting($_W['uniacid'], array('default_site'));
	$setting['styleid'] = pdo_fetchcolumn('SELECT styleid FROM ' . tablename('site_multi') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $setting['default_site']));
	$_W['page']['title'] = '风格管理 - 网站风格设置 - 微站功能';
	$params = array();
	$params[':uniacid'] = $_W['uniacid'];
	$styles = pdo_fetchall("SELECT a.* FROM ".tablename('site_styles')." AS a LEFT JOIN ".tablename('site_templates')." AS b ON a.templateid = b.id WHERE uniacid = :uniacid ".(!empty($where) ? " AND $where" : ''), $params);
	$templates = uni_templates();
		$stylesResult = array();
	foreach($templates as $k => $v) {
		$stylesResult[$k] = array(
			'templateid' => $v['id'],
			'name' => $v['name'],
			'title' => $v['title'],
			'type' => $v['type']
		);
		foreach($styles as $vv) {
			if($v['id'] == $vv['templateid']) {
				unset($stylesResult[$k]);
			}
		}
	}
	$templates_id = array_keys($templates);
	foreach($styles as $v) {
				if(!in_array($v['templateid'], $templates_id)) {
			continue;
		}
		$stylesResult[] =  array(
			'styleid' => $v['id'], 
			'templateid' => $v['templateid'], 
			'name' => $templates[$v['templateid']]['name'], 
			'title' => $v['name'], 
			'type' => $templates[$v['templateid']]['type']
		);
	}
	if (!empty($_GPC['type']) && $_GPC['type'] != 'all') {
		$tmp = array();
		foreach($stylesResult as $k => $v) {
			if($v['type'] == $_GPC['type']) {
				$tmp[] = $v;
			}
		}
		$stylesResult = $tmp;
	}
	array_multisort($stylesResult, SORT_DESC);
	load()->model('extension');
	$temtypes = ext_template_type();
	template('site/style');
}

if($do == 'default') {
	load()->model('extension');
	$setting = uni_setting($_W['uniacid'], array('default_site'));
	$multi = pdo_fetch('SELECT id,styleid FROM ' . tablename('site_multi') . ' WHERE uniacid = :uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $setting['default_site']));
	if(empty($multi)) {
		message('您的默认微站找不到,请联系网站管理员', '', 'error');
	}

	$styleid = intval($_GPC['styleid']);
	$style = pdo_fetch("SELECT * FROM ".tablename('site_styles')." WHERE id = :styleid AND uniacid = :uniacid", array(':styleid' => $styleid, ':uniacid' => $_W['uniacid']));
	if(empty($style)) {
		message('抱歉，风格不存在或是您无权限使用！', '', 'error');
	}
	$templateid = $style['templateid'];
	$template = array();
	$templates = uni_templates();
	if(!empty($templates)) {
		foreach($templates as $row) {
			if($row['id'] == $templateid) {
				$template = $row;
				break;
			}
		}
	}
	if(empty($template)) {
		message('抱歉，模板不存在或是您无权限使用！', '', 'error');
	}
	pdo_update('site_multi', array('styleid' => $styleid), array('uniacid' => $_W['uniacid'], 'id' => $setting['default_site']));
	$styles = pdo_fetchall("SELECT variable, content FROM " . tablename('site_styles_vars') . " WHERE styleid = :styleid  AND uniacid = '{$_W['uniacid']}'", array(':styleid' => $styleid), 'variable');
	$styles_tmp = array_keys($styles);
	$templatedata = ext_template_manifest($template['name']);
	if(empty($styles)) {
		if(!empty($templatedata['settings'])) {
			foreach($templatedata['settings'] as $list) {
				pdo_insert('site_styles_vars', array('variable' => $list['key'], 'content' => $list['value'], 'description' => $list['desc'], 'templateid' => $templateid, 'styleid' => $styleid, 'uniacid' => $_W['uniacid']));
			}
		}
	} else {
		if(!empty($templatedata['settings'])) {
			foreach($templatedata['settings'] as $list) {
				if(!in_array($list['key'], $styles_tmp)) {
					pdo_insert('site_styles_vars', array(
						'content' => $list['value'],
						'templateid' => $templateid,
						'styleid' => $styleid,
						'variable' => $list['key'],
						'description' => $list['desc'],
						'uniacid' => $_W['uniacid']
					));
				}
			}
		}
	}
	message('默认模板更新成功！', url('site/style/template'), 'success');
}

if($do == 'designer') {
	$styleid = intval($_GPC['styleid']);
	$style = pdo_fetch("SELECT * FROM ".tablename('site_styles')." WHERE id = :id AND uniacid = '{$_W['uniacid']}'", array(':id' => $styleid));
	if(empty($style)) {
		message('抱歉，风格不存在或是已经被删除！', '', 'error');
	}
	$templateid = $style['templateid'];
	$template = pdo_fetch("SELECT * FROM " . tablename('site_templates') . " WHERE id = '{$templateid}'");
	if(empty($template)) {
		message('抱歉，模板不存在或是已经被删除！', '', 'error');
	}
	$styles = pdo_fetchall("SELECT variable, content, description FROM " . tablename('site_styles_vars') . " WHERE styleid = :styleid AND uniacid = '{$_W['uniacid']}'", array(':styleid' => $styleid), 'variable');
	if(checksubmit('submit')) {
		if(!empty($_GPC['style'])) {
			foreach($_GPC['style'] as $variable => $value) {
				if(!empty($styles[$variable])) {
					if($styles[$variable]['content'] != $value) {
						pdo_update('site_styles_vars', array('content' => $value), array(
							'styleid' => $styleid,
							'variable' => $variable,
						));
					}
					unset($styles[$variable]);
				} elseif (!empty($value)) {
					pdo_insert('site_styles_vars', array(
						'content' => $value,
						'templateid' => $templateid,
						'styleid' => $styleid,
						'variable' => $variable,
						'uniacid' => $_W['uniacid']
					));
				}
			}
		}
		if(!empty($_GPC['custom']['name'])) {
			foreach($_GPC['custom']['name'] as $i => $variable) {
				$value = $_GPC['custom']['value'][$i];
				$desc = $_GPC['custom']['desc'][$i];
				if(!empty($value)) {
					if(!empty($styles[$variable])) {
						if($styles[$variable] != $value) {
							pdo_update('site_styles_vars', array('content' => $value, 'description' => $desc), array(
								'templateid' => $templateid,
								'variable' => $variable,
								'uniacid' => $_W['uniacid'],
								'styleid' => $styleid
							));
						}
						unset($styles[$variable]);
					} else {
						pdo_insert('site_styles_vars', array(
							'content' => $value,
							'templateid' => $templateid,
							'styleid' => $styleid,
							'variable' => $variable,
							'description' => $desc,
							'uniacid' => $_W['uniacid']
						));
					}
				}
			}
		}
		if(!empty($styles)) {
			pdo_query("DELETE FROM " . tablename('site_styles_vars') . " WHERE variable IN ('" . implode("','", array_keys($styles)) . "') AND styleid = :styleid AND uniacid = '{$_W['uniacid']}'", array(':styleid' => $styleid));
		}
		pdo_update('site_styles', array('name' => $_GPC['name']), array('id' => $styleid));
		message('更新风格成功！', url('site/style/template'), 'success');
	}
	$systemtags = array(
		'imgdir',
		'indexbgcolor',
		'indexbgimg',
		'indexbgextra',
		'fontfamily',
		'fontsize',
		'fontcolor',
		'fontnavcolor',
		'linkcolor',
		'css'
	);
	template('site/style');
}

if($do == 'module') {
	uni_user_permission_check('site_style_module');
	$_W['page']['title'] = '模块扩展模板说明 - 网站风格设置 - 微站功能';
	if(empty($_W['isfounder'])) {
		message('您无权进行该操作！');
	}
	$setting = uni_setting($_W['uniacid'], array('default_site'));
	$styleid = pdo_fetchcolumn("SELECT styleid FROM ".tablename('site_multi')." WHERE id = :id", array(':id' => $setting['default_site']));
	$templateid = pdo_fetchcolumn("SELECT templateid FROM ".tablename('site_styles')." WHERE id = :id", array(':id' => $styleid));
	$ts = uni_templates();
	$currentTemplate = !empty($ts[$templateid]) ? $ts[$templateid]['name'] : 'default';
	$modules = uni_modules();
	$path = IA_ROOT . '/addons';
	if(is_dir($path)) {
		if($handle = opendir($path)) {
			while(false !== ($modulepath = readdir($handle))) {
				if($modulepath != '.' && $modulepath != '..' && !empty($modules[$modulepath])) {
					if(is_dir($path . '/' . $modulepath . '/template/mobile')) {
						if($handle1 = opendir($path . '/' . $modulepath . '/template/mobile')) {
							while(false !== ($mobilepath = readdir($handle1))) {
								if($mobilepath != '.' && $mobilepath != '..' && strexists($mobilepath, '.html')) {
									$templates[$modulepath][] = $mobilepath;
								}
							}
						}
					}
				}
			}
		}
	}
	template('site/style');
}

if($do == 'createtemplate') {
	if(empty($_W['isfounder'])) {
		exit('require founder');
	}
	$name = $_GPC['name'];
	load()->model('module');
	$module = module_fetch($name);
	if(empty($module)) {
		exit('invalid module');
	}

	$file = $_GPC['file'];
	$setting = uni_setting($_W['uniacid'], array('default_site'));
	$styleid = pdo_fetchcolumn("SELECT styleid FROM ".tablename('site_multi')." WHERE id = :id", array(':id' => $setting['default_site']));
	$templateid = pdo_fetchcolumn("SELECT templateid FROM ".tablename('site_styles')." WHERE id = :id", array(':id' => $styleid));

	$ts = uni_templates();
	$currentTemplate = !empty($ts[$templateid]) ? $ts[$templateid]['name'] : 'default';
	$targetfile = IA_ROOT . '/app/themes/' . $currentTemplate . '/' . $module['name'] . '/' . $file;
	if(!file_exists($targetfile)) {
		load()->func('file');
		mkdirs(dirname($targetfile));
		file_put_contents($targetfile, '<!-- 原始文件：addons/modules/' . $module['name'] . '/template/mobile/' . $file . ' -->');
		@chmod($targetfile, $_W['config']['setting']['filemode']);
	}
	message('操作成功！', '', 'success');
}

if ($do == 'build') {
	load()->model('extension');
	$template = array();
	$templates = uni_templates();
	if(!empty($templates)) {
		foreach($templates as $row) {
			if($row['id'] == $templateid) {
				$template = $row;
				break;
			}
		}
	}
	if(empty($template)) {
		message('抱歉，模板不存在或是您无权限使用！', '', 'error');
	}
	list($templatetitle) = explode('_', $template['title']);
	$newstyle = array(
			'uniacid' => $_W['uniacid'],
			'name' => $templatetitle.'_'.random(4),
			'templateid' => $template['id'],
	);
	pdo_insert('site_styles', $newstyle);
	$id = pdo_insertid();

	$templatedata = ext_template_manifest($template['name']);
	if(!empty($templatedata['settings'])) {
		foreach($templatedata['settings'] as $style_var) {
			if(!empty($style_var['key']) && !empty($style_var['desc'])) {
				pdo_insert('site_styles_vars', array(
				'content' => $style_var['value'],
				'templateid' => $templateid,
				'styleid' => $id,
				'variable' => $style_var['key'],
				'uniacid' => $_W['uniacid'],
				'description' => $style_var['desc'],
				));
			}
		}
	}
	message('风格创建成功，进入“设计风格”界面。', url('site/style/designer', array('templateid' => $template['id'], 'styleid' => $id)), 'success');
}

if($do == 'copy') {
	$styleid = intval($_GPC['styleid']);
	$style = pdo_fetch("SELECT * FROM ".tablename('site_styles')." WHERE id = :id AND uniacid = '{$_W['uniacid']}'", array(':id' => $styleid));
	if(empty($style)) {
		message('抱歉，风格不存在或是已经被删除！', '', 'error');
	}
	$templateid = $style['templateid'];
	$template = pdo_fetch("SELECT * FROM " . tablename('site_templates') . " WHERE id = '{$templateid}'");
	if(empty($template)) {
		message('抱歉，模板不存在或是已经被删除！', '', 'error');
	}

	list($name) = explode('_', $style['name']);
	$newstyle = array(
			'uniacid' => $_W['uniacid'],
			'name' => $name.'_'.random(4),
			'templateid' => $style['templateid'],
	);
	pdo_insert('site_styles', $newstyle);
	$id = pdo_insertid();

	$styles = pdo_fetchall("SELECT variable, content, templateid, uniacid FROM " . tablename('site_styles_vars') . " WHERE styleid = :styleid AND uniacid = '{$_W['uniacid']}'", array(':styleid' => $styleid));
	if(!empty($styles)) {
		foreach($styles as $data) {
			$data['styleid'] = $id;
			pdo_insert('site_styles_vars', $data);
		}
	}
	message('风格复制成功，进入“设计风格”界面。', url('site/style/designer', array('templateid' => $style['templateid'], 'styleid' => $id)), 'success');
}

if($do == 'del') {
	$styleid = intval($_GPC['styleid']);
	pdo_delete('site_styles_vars', array('uniacid' => $_W['uniacid'], 'styleid' => $styleid));
	pdo_delete('site_styles', array('uniacid' => $_W['uniacid'], 'id' => $styleid));
	message('删除风格成功。', referer(), 'success');
}