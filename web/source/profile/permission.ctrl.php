<?php

$modulename = $_GPC['m'];
$check = module_solution_check($modulename);
if(is_error($check)) {
	message($check['message'], '', 'error');
}

$module_types = module_types();
$module = module_fetch($modulename);
define('ACTIVE_FRAME_URL', url('home/welcome/ext', array('m' => $modulename)));

$username = pdo_fetchcolumn('SELECT username FROM' . tablename('users') . ' WHERE uid = :uid', array(':uid' => intval($_GPC['uid'])));
$entries = module_entries($modulename, array('menu', 'rule', 'function'));
if(!empty($entries)) {
	foreach($entries as $index1 => &$entry1) {
		foreach($entry1 as $index2 => &$entry2) {
			$url_arr = parse_url($entry2['url']);
			$url_query = $url_arr['query'];
			parse_str($url_query, $query_arr);
			$eid = intval($query_arr['eid']);
			$data = pdo_fetch('SELECT * FROM ' . tablename('modules_bindings') . ' WHERE eid = :eid', array(':eid' => $eid));
			$entry2['eid'] = $eid;
			if($entry2['from'] == 'call') {
				$entry2['eid'] = 0;
			}
			$entry2['do'] = $data['do'];
			$entry2['state'] = $data['state'];
			
			$entry2['dostate'] = $data['do'] . $data['state'];
			$shuju[$index1][$i] = $entry2;
			$i ++;
		}
	}
}
unset($entries);

$uid = intval($_GPC['uid']);
if(checksubmit('submit')) {
	pdo_delete('solution_acl', array('uid' => $uid, 'module' => $modulename));
	if(!empty($_GPC['enable'])) {
		foreach($_GPC['enable'] as $index => $value) {
			$data = array(
					'eid' => intval($_GPC['eid'][$index]),
					'uid' => $uid,
					'do' => $_GPC['do'][$index],
					'state' => $_GPC['state'][$index],
					'title' => $_GPC['title'][$index],
					'module' => $modulename,
					'enable' => intval($_GPC['enable'][$index])
			);
			pdo_insert('solution_acl', $data);
			unset($data);
		}
	}
	message('设置用户权限成功.',url('profile/permission/', array('m' => $modulename, 'uid' => $uid)));
}

$userdata = pdo_fetchall('SELECT * FROM ' . tablename('solution_acl') . ' WHERE uid = :uid AND module = :module', array(':uid' => $uid, ':module' => $modulename), 'do');
foreach($userdata as $udata) {
	$index = $udata['do'] .  $udata['state'];
	$usdata[$index] =  $udata['do'] .  $udata['state'];
}
template('profile/permission');