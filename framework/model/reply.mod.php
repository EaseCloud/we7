<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function reply_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	if (!empty($condition)) {
		$where = "WHERE {$condition}";
	}
	$sql = 'SELECT * FROM ' . tablename('rule') . $where . " ORDER BY status DESC, displayorder DESC, id ASC";
	if ($pindex > 0) {
				$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule') . $where, $params);
	}
	return pdo_fetchall($sql, $params);
}


function reply_single($id) {
	$result = array();
	$id = intval($id);
	$result = pdo_fetch("SELECT * FROM " . tablename('rule') . " WHERE id = :id", array(':id' => $id));
	if (empty($result)) {
		return $result;
	}
	$result['keywords'] = pdo_fetchall("SELECT * FROM " . tablename('rule_keyword') . " WHERE rid = :rid", array(':rid' => $id));
	return $result;
}


function reply_keywords_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	if (!empty($condition)) {
		$where = " WHERE {$condition} ";
	}
	$sql = 'SELECT * FROM ' . tablename('rule_keyword') . $where . ' ORDER BY displayorder DESC, `type` ASC, id DESC LIMIT 3';
	if ($pindex > 0) {
				$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule_keyword') . $where, $params);
	}
	return pdo_fetchall($sql, $params);
}

