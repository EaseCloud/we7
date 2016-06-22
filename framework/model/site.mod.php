<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
function site_cover($coverparams = array()) {
	$where = '';
	$params = array(':uniacid' => $coverparams['uniacid'], ':module' => $coverparams['module']);
	if (!empty($coverparams['multiid'])) {
		$where .= " AND multiid = :multiid";
		$params[':multiid'] = $coverparams['multiid'];
	}
	$cover = pdo_fetch("SELECT * FROM " . tablename('cover_reply') . " WHERE `module` = :module AND uniacid = :uniacid {$where}", $params);
	if (empty($cover['rid'])) {
		$rule = array(
			'uniacid' => $coverparams['uniacid'],
			'name' => $coverparams['title'],
			'module' => 'cover',
			'status' => 1,
		);
		pdo_insert('rule', $rule);
		$rid = pdo_insertid();
	} else {
		$rule = array(
			'name' => $coverparams['title'],
		);
		pdo_update('rule', $rule, array('id' => $cover['rid']));
		$rid = $cover['rid'];
	}
	if (!empty($rid)) {
				$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `uniacid`=:uniacid';
		$pars = array();
		$pars[':rid'] = $rid;
		$pars[':uniacid'] = $coverparams['uniacid'];
		pdo_query($sql, $pars);
			
		$keywordrow = array(
			'rid' => $rid,
			'uniacid' => $coverparams['uniacid'],
			'module' => 'cover',
			'status' => 1,
			'displayorder' => 0,
			'type' => 1,
			'content' => $coverparams['keyword'],
		);
		pdo_insert('rule_keyword', $keywordrow);
	}
	$entry = array(
		'uniacid' => $coverparams['uniacid'],
		'multiid' => $coverparams['multiid'],
		'rid' => $rid,
		'title' => $coverparams['title'],
		'description' => $coverparams['description'],
		'thumb' => $coverparams['thumb'],
		'url' => $coverparams['url'],
		'do' => '',
		'module' => $coverparams['module'],
	);

	if (empty($cover['id'])) {
		pdo_insert('cover_reply', $entry);
	} else {
		pdo_update('cover_reply', $entry, array('id' => $cover['id']));
	}
	return true;
}


function site_cover_delete($page_id) {
	global $_W;
	$page_id = intval($page_id);
	$cover = pdo_fetch('SELECT * FROM ' . tablename('cover_reply') . ' WHERE uniacid = :uniacid AND module = :module AND multiid = :id', array(':uniacid' => $_W['uniacid'],':module' => 'page', ':id' => $page_id));
	if(!empty($cover)) {
		$rid = intval($cover['rid']);
		pdo_delete('rule', array('id' => $rid));
		pdo_delete('rule_keyword', array('rid' => $rid));
		pdo_delete('cover_reply', array('id' => $cover['id']));
	}
	return true;
}


