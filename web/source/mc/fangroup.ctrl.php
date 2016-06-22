<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_fangroup');
load()->model('mc');
$dos = array('post', 'display', 'del');
$do = !empty($_GPC['do']) && in_array($do, $dos) ? $do : 'display';

if($do == 'display') {
	$groups = mc_fans_groups(true);
	if(empty($groups)) {
		message($groups['message'], '', 'error');
	}
}

if($do == 'post') {
	$account = WeAccount::create($_W['acid']);
	if(!empty($_GPC['groupname'])) {
		foreach($_GPC['groupname'] as $key => $value) {
			if(empty($value)) {
				continue;
			} else {
				$data = array('id' => $_GPC['groupid'][$key], 'name' => $value);
				$state = $account->editFansGroupname($data);
				if(is_error($state)) {
					message($state['message'], url('mc/fangroup/'), 'error');
				}
			}
		}
	}
	if(!empty($_GPC['group_add'])) {
		foreach($_GPC['group_add'] as $value) {
			if(empty($value)) {
				continue;
			} else {
				$value = trim($value);
				$state = $account->addFansGroup($value);
				if(is_error($state)) {
					message($state['message'], url('mc/fangroup/'), 'error');
				}
			}
		}
	}
	message('保存分组名称成功', url('mc/fangroup/'), 'success');
}

if($do == 'del') {
	$groupid = intval($_GPC['id']);
	$account = WeAccount::create($_W['acid']);
	$groups = $account->delFansGroup($groupid);
	if(!is_error($groups)) {
				pdo_update('mc_mapping_fans', array('groupid' => 0), array('acid' => $_W['acid'], 'groupid' => $groupid));
		message(array('errno' => 0), '', 'ajax');
	} else {
		message($groups, '', 'ajax');
	}
}
template('mc/fansgroup');