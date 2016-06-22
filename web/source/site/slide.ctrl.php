<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

$do = !empty($do) ? $do : 'display';
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';
$setting = uni_setting($_W['uniacid'], 'default_site');
$default_site = intval($setting['default_site']);
$multis = pdo_fetchall('SELECT * FROM ' . tablename('site_multi') .' WHERE uniacid = :uniacid', array(':uniacid' => $_W['uniacid']), 'id');

if ($do == 'display') {
	$_W['page']['title'] = '幻灯片管理- 幻灯片设置 - 功能组件';
	if (checksubmit('submit')) {
		if (!empty($_GPC['displayorder'])) {
			foreach ($_GPC['displayorder'] as $id => $displayorder) {
				pdo_update('site_slide', array('displayorder' => $displayorder), array('id' => $id));
			}
		}
		message('更新排序成功！', referer(), 'success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$params = array();
	$multiid = intval($_GPC['multiid']);
	if($multiid > 0) {
		$condition .= " AND multiid = {$multiid}";
	}
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('site_slide')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, uniacid DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('site_slide') . " WHERE uniacid = '{$_W['uniacid']}' $condition");
	$pager = pagination($total, $pindex, $psize);
}

if ($do == 'post') {
	$_W['page']['title'] = '幻灯片添加- 幻灯片设置 - 功能组件';
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM ".tablename('site_slide')." WHERE id = :id" , array(':id' => $id));
		if (empty($item)) {
			message('抱歉，幻灯片不存在或是已经删除！', '', 'error');
		}
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('标题不能为空，请输入标题！');
		}
				$multiid = intval($_GPC['multiid']) ? intval($_GPC['multiid']) : $default_site;
		$data = array(
			'uniacid' => $_W['uniacid'],
			'multiid' => $multiid,
			'title' => $_GPC['title'],
			'url' => $_GPC['url'],
			'displayorder' => intval($_GPC['displayorder']),
		);
		if (!empty($_GPC['thumb'])) {
			$data['thumb'] = $_GPC['thumb'];
		}
		if (empty($id)) {
			pdo_insert('site_slide', $data);
		} else {
			pdo_update('site_slide', $data, array('id' => $id));
		}
		message('幻灯片更新成功！', url('site/slide/display', array('multiid' => $multiid, 'f' => $_GPC['f'])), 'success');
	}
}

if ($do == 'delete') {
	$_W['page']['title'] = '幻灯片删除- 幻灯片设置 - 功能组件';
	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT id, thumb FROM ".tablename('site_slide')." WHERE id = :id", array(':id' => $id));
	if (empty($row)) {
		message('抱歉，幻灯片不存在或是已经被删除！');
	}
	pdo_delete('site_slide', array('id' => $id));
	message('删除成功！', url('site/slide/display', array('multiid' => $_GPC['multiid'] , 'f' => $_GPC['f'])), 'success');
}

template('site/slide');