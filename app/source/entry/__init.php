<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

$eid = intval($_GPC['eid']);
if(!empty($eid)) {
	$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `eid`=:eid';
	$entry = pdo_fetch($sql, array(':eid' => $eid));
	$_GPC['m'] = $entry['module'];
} else {
	$entry = array(
		'module' => $_GPC['m'],
		'do' => $_GPC['do'],
		'state' => $_GPC['state'],
		'direct' => 0
	);
}
$moduels = uni_modules();
if (empty($moduels[$entry['module']])) {
	message('您访问的功能模块不存在，请重新进入');
}
if(empty($entry) || empty($entry['do'])) {
	message('非法访问.');
}
$_GPC['__entry'] = $entry['title'];
$_GPC['__state'] = $entry['state'];

define('IN_MODULE', $entry['module']);

$site = WeUtility::createModuleSite($entry['module']);
if(!is_error($site)) {
	$method = 'doMobile' . ucfirst($entry['do']);
	exit($site->$method());
}
exit();
