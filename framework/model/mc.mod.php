<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */


function mc_check($data) {
	global $_W;
	if (!empty($data['email'])) {
		$email = trim($data['email']);
		if (!preg_match(REGULAR_EMAIL, $email)) {
			return error(-1, '邮箱格式不正确');
		}
		$isexist = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND email = :email AND uid != :uid', array(':uniacid' => $_W['uniacid'], ':email' => $email, ':uid' => $_W['member']['uid']));
		if ($isexist >= 1) {
			return error(-1, '邮箱已被注册,请使用其他邮箱');
		}
	}
	if (!empty($data['mobile'])) {
		$mobile = trim($data['mobile']);
		if (!preg_match(REGULAR_MOBILE, $mobile)) {
			return error(-1, '手机号格式不正确');
		}
		$isexist = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND mobile = :mobile AND uid != :uid', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile, ':uid' => $_W['member']['uid']));
		if ($isexist >= 1) {
			return error(-1, '手机号已被注册,请使用其他手机号');
		}
	}
	return true;
}


function mc_update($uid, $fields) {
	global $_W;
	if (empty($fields)) {
		return false;
	}
		$uid_temp = $uid;

	$uid = mc_openid2uid($uid);

	$_W['weid'] && $fields['weid'] = $_W['weid'];
	$struct = array_keys(mc_fields());
	$struct[] = 'birthyear';
	$struct[] = 'birthmonth';
	$struct[] = 'birthday';
	$struct[] = 'resideprovince';
	$struct[] = 'residecity';
	$struct[] = 'residedist';
	$struct[] = 'groupid';

	if (isset($fields['birth'])) {
		$fields['birthyear'] = $fields['birth']['year'];
		$fields['birthmonth'] = $fields['birth']['month'];
		$fields['birthday'] = $fields['birth']['day'];
	}
	if (isset($fields['reside'])) {
		$fields['resideprovince'] = $fields['reside']['province'];
		$fields['residecity'] = $fields['reside']['city'];
		$fields['residedist'] = $fields['reside']['district'];
	}
	unset($fields['reside'], $fields['birth']);
	foreach ($fields as $field => $value) {
		if (!in_array($field, $struct)) {
			unset($fields[$field]);
		}
	}
	if (!empty($fields['avatar'])) {
		if (strexists($fields['avatar'], 'attachment/images/global/avatars/avatar_')) {
			$fields['avatar'] = str_replace($_W['attachurl'], '', $fields['avatar']);
		}
	}
	$isexists = pdo_fetchcolumn("SELECT uid FROM " . tablename('mc_members') . " WHERE uid = :uid", array(':uid' => $uid));
	$condition = '';
	if (!empty($isexists)) {
		$condition = ' AND uid != ' . $uid;
	}
		if (!empty($fields['email'])) {
		$emailexists = pdo_fetchcolumn("SELECT email FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND email = :email " . $condition, array(':uniacid' => $_W['uniacid'], ':email' => trim($fields['email'])));
		if ($emailexists) {
			unset($fields['email']);
		}
	}
	if (!empty($fields['mobile'])) {
		$mobilexists = pdo_fetchcolumn("SELECT mobile FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND mobile = :mobile " . $condition, array(':uniacid' => $_W['uniacid'], ':mobile' => trim($fields['mobile'])));
		if ($mobilexists) {
			unset($fields['mobile']);
		}
	}
	if (empty($isexists)) {
		if(empty($fields['mobile']) && empty($fields['email'])) {
			return false;
		}
		$fields['uniacid'] = $_W['uniacid'];
		$fields['createtime'] = TIMESTAMP;
		pdo_insert('mc_members', $fields);
		$insert_id = pdo_insertid();
		if(is_string($uid_temp)) {
			pdo_update('mc_mapping_fans', array('uid' => $insert_id), array('uniacid' => $_W['uniacid'], 'openid' => trim($uid_temp)));
		}
		return $insert_id;
	} else {
		$result = pdo_update('mc_members', $fields, array('uid' => $uid));
		return $result > 0;
	}
}


function mc_fetch($uid, $fields = array()) {
	global $_W;
	$uid = mc_openid2uid($uid);
	if (empty($uid)) {
		return array();
	}
	$struct = (array)cache_load('usersfields');
	if (empty($fields)) {
		$select = '*';
	} else {
		foreach ($fields as $field) {
			if (!in_array($field, $struct)) {
				unset($fields[$field]);
			}
			if ($field == 'birth') {
				$fields[] = 'birthyear';
				$fields[] = 'birthmonth';
				$fields[] = 'birthday';
			}
			if ($field == 'reside') {
				$fields[] = 'resideprovince';
				$fields[] = 'residecity';
				$fields[] = 'residedist';
			}
		}
		unset($fields['birth'], $fields['reside']);
		$select = '`uid`, `' . implode('`,`', $fields) . '`';
	}
	if (is_array($uid)) {
		$result = pdo_fetchall("SELECT $select FROM " . tablename('mc_members') . " WHERE uid IN ('" . implode("','", is_array($uid) ? $uid : array($uid)) . "')", array(), 'uid');
		foreach ($result as &$row) {
			if (isset($row['credit1'])) {
				$row['credit1'] = floatval($row['credit1']);
			}
			if (isset($row['credit2'])) {
				$row['credit2'] = floatval($row['credit2']);
			}
			if (isset($row['credit3'])) {
				$row['credit3'] = floatval($row['credit3']);
			}
			if (isset($row['credit4'])) {
				$row['credit4'] = floatval($row['credit4']);
			}
			if (isset($row['credit5'])) {
				$row['credit5'] = floatval($row['credit5']);
			}
			if (isset($row['credit6'])) {
				$row['credit6'] = floatval($row['credit6']);
			}
			if (isset($row['avatar']) && !empty($row['avatar'])) {
				$row['avatar'] = tomedia($row['avatar']);
			}
		}
	} else {
		$result = pdo_fetch("SELECT $select FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(':uid' => $uid));
		if (isset($result['avatar']) && !empty($result['avatar'])) {
			$result['avatar'] = tomedia($result['avatar']);
		}
		if (isset($result['credit1'])) {
			$result['credit1'] = floatval($result['credit1']);
		}
		if (isset($result['credit2'])) {
			$result['credit2'] = floatval($result['credit2']);
		}
		if (isset($result['credit3'])) {
			$result['credit3'] = floatval($result['credit3']);
		}
		if (isset($result['credit4'])) {
			$result['credit4'] = floatval($result['credit4']);
		}
		if (isset($result['credit5'])) {
			$result['credit5'] = floatval($result['credit5']);
		}
		if (isset($result['credit6'])) {
			$result['credit6'] = floatval($result['credit6']);
		}
	}
	return $result;
}


function mc_fansinfo($openidOruid, $acid = 0, $uniacid = 0){
	global $_W;
	if (empty($openidOruid)) {
		return array();
	}
	$params = array();
	if (is_numeric($openidOruid)) {
		$condition = '`uid` = :uid';
		$params[':uid'] = $openidOruid;
	} else {
		$condition = '`openid` = :openid';
		$params[':openid'] = $openidOruid;
	}

	if (!empty($acid)) {
		$params[':acid'] = $acid;
		$condition .= " AND `acid` = :acid";
	}
	if (!empty($uniacid)) {
		$params[':uniacid'] = $uniacid;
		$condition .= " AND `uniacid` = :uniacid";
	}
	$sql = 'SELECT * FROM ' . tablename('mc_mapping_fans') . " WHERE $condition";
	$fan = pdo_fetch($sql, $params);
	if (!empty($fan)) {
		if (!empty($fan['tag']) && is_string($fan['tag'])) {
			if (is_base64($fan['tag'])) {
				$fan['tag'] = @base64_decode($fan['tag']);
			}
			if (is_serialized($fan['tag'])) {
				$fan['tag'] = @iunserializer($fan['tag']);
			}
			if (is_array($fan['tag']) && !empty($fan['tag']['headimgurl'])) {
				$fan['tag']['avatar'] = tomedia($fan['tag']['headimgurl']);
				unset($fan['tag']['headimgurl']);
				$fan['nickname'] = $fan['tag']['nickname'];
				$fan['gender'] = $fan['sex'] = $fan['tag']['sex'];
				$fan['avatar'] = $fan['headimgurl'] = $fan['tag']['avatar'];
			}
		} else {
			$fan['tag'] = array();
		}
	}
	if (empty($fan) && $openidOruid == $_W['openid'] && !empty($_SESSION['userinfo'])) {
		$fan['tag'] = unserialize(base64_decode($_SESSION['userinfo']));
		$fan['uid'] = 0;
		$fan['openid'] = $fan['tag']['openid'];
		$fan['follow'] = 0;
		$fan['nickname'] = $fan['tag']['nickname'];
		$fan['gender'] = $fan['sex'] = $fan['tag']['sex'];
		$fan['avatar'] = $fan['headimgurl'] = $fan['tag']['headimgurl'];
		$mc_oauth_fan = mc_oauth_fans($fan['openid']);
		if (!empty($mc_oauth_fan)) {
			$fan['uid'] = $mc_oauth_fan['uid'];
		}
	}
	return $fan;
}


function mc_oauth_fans($openid, $acid = 0){
	$condition = array();
	$condition['oauth_openid'] = $openid;
	if (!empty($acid)) {
		$condition['acid'] = $acid;
	}
	$fan = pdo_get('mc_oauth_fans', $condition, array('openid', 'uid'));
	return $fan;
}


function mc_oauth_userinfo($acid = 0) {
	global $_W;
	if (isset($_SESSION['userinfo'])) {
		$userinfo = unserialize(base64_decode($_SESSION['userinfo']));
		if (!empty($userinfo['subscribe']) || !empty($userinfo['nickname'])) {
			return $userinfo;
		}
	}
	if ($_W['container'] != 'wechat') {
		return array();
	}
		if (!empty($_SESSION['openid']) && intval($_W['account']['level']) >= 3) {
		$oauth_account = WeAccount::create($_W['account']['oauth']);
		$userinfo = $oauth_account->fansQueryInfo($_SESSION['openid']);
		if (!is_error($userinfo) && !empty($userinfo) && is_array($userinfo) && !empty($userinfo['nickname'])) {
			$userinfo['nickname'] = stripcslashes($userinfo['nickname']);
			if (!empty($userinfo['headimgurl'])) {
				$userinfo['headimgurl'] = rtrim($userinfo['headimgurl'], '0') . 132;
			}
			$userinfo['avatar'] = $userinfo['headimgurl'];
			$_SESSION['userinfo'] = base64_encode(iserializer($userinfo));

			$fan = mc_fansinfo($_SESSION['openid']);
			if (!empty($fan)) {
				$record = array(
					'updatetime' => TIMESTAMP,
					'nickname' => stripslashes($userinfo['nickname']),
					'follow' => $userinfo['subscribe'],
					'followtime' => $userinfo['subscribe_time'],
					'tag' => base64_encode(iserializer($userinfo))
				);
				pdo_update('mc_mapping_fans', $record, array('openid' => $_SESSION['openid'], 'acid' => $_W['acid'], 'uniacid' => $_W['uniacid']));
			} else {
				$record = array();
				$record['updatetime'] = TIMESTAMP;
				$record['nickname'] = stripslashes($userinfo['nickname']);
				$record['tag'] = base64_encode(iserializer($userinfo));
				$record['openid'] = $_SESSION['openid'];
				$record['acid'] = $_W['acid'];
				$record['uniacid'] = $_W['uniacid'];
				pdo_insert('mc_mapping_fans', $record);
			}
			
			if (!empty($fan['uid']) || !empty($_SESSION['uid'])) {
				$uid = intval($fan['uid']);
				if (empty($uid)) {
					$uid = intval($_SESSION['uid']);
				}
				$member = mc_fetch($uid, array('nickname', 'gender', 'residecity', 'resideprovince', 'nationality', 'avatar'));
				$record = array();
				if (empty($member['nickname']) && !empty($userinfo['nickname'])) {
					$record['nickname'] = stripslashes($userinfo['nickname']);
				}
				if (empty($member['gender']) && !empty($userinfo['sex'])) {
					$record['gender'] = $userinfo['sex'];
				}
				if (empty($member['residecity']) && !empty($userinfo['city'])) {
					$record['residecity'] = $userinfo['city'] . '市';
				}
				if (empty($member['resideprovince']) && !empty($userinfo['province'])) {
					$record['resideprovince'] = $userinfo['province'] . '省';
				}
				if (empty($member['nationality']) && !empty($userinfo['country'])) {
					$record['nationality'] = $userinfo['country'];
				}
				if (empty($member['avatar']) && !empty($userinfo['headimgurl'])) {
					$record['avatar'] = $userinfo['headimgurl'];
				}
				if (!empty($record)) {
					pdo_update('mc_members', $record, array('uid' => intval($uid)));
				}
			}
			return $userinfo;
		}
	}

	if (empty($_W['account']['oauth'])) {
		return error(-1, '未指定网页授权公众号, 无法获取用户信息.');
	}
	if (empty($_W['account']['oauth']['key'])) {
		return error(-2, '公众号未设置 appId 或 secret.');
	}
	if (intval($_W['account']['oauth']['level']) < 4) {
		return error(-3, '公众号非认证服务号, 无法获取用户信息.');
	}

	$state = 'we7sid-' . $_W['session_id'];
	$_SESSION['dest_url'] = urlencode($_W['siteurl']);
	
	$unisetting = uni_setting($_W['uniacid']);
	$str = '';
	if(uni_is_multi_acid()) {
		$str = "&j={$_W['acid']}";
	}
	$url = (!empty($unisetting['oauth']['host']) ? ($unisetting['oauth']['host'] . '/') : $_W['siteroot']) . "app/index.php?i={$_W['uniacid']}{$str}&c=auth&a=oauth&scope=userinfo";
	$callback = urlencode($url);
	
	$oauth_account = WeAccount::create($_W['account']['oauth']);
	$forward = $oauth_account->getOauthUserInfoUrl($callback, $state);
	header('Location: ' . $forward);
	exit;
}


function mc_require($uid, $fields, $pre = '') {
	global $_W, $_GPC;
	if (empty($fields) || !is_array($fields)) {
		return false;
	}
	$flipfields = array_flip($fields);
		if (in_array('birth', $fields) || in_array('birthyear', $fields) || in_array('birthmonth', $fields) || in_array('birthday', $fields)) {
		unset($flipfields['birthyear'], $flipfields['birthmonth'], $flipfields['birthday'], $flipfields['birth']);
		$flipfields['birthyear'] = 'birthyear';
		$flipfields['birthmonth'] = 'birthmonth';
		$flipfields['birthday'] = 'birthday';
	}
	if (in_array('reside', $fields) || in_array('resideprovince', $fields) || in_array('residecity', $fields) || in_array('residedist', $fields)) {
		unset($flipfields['residedist'], $flipfields['resideprovince'], $flipfields['residecity'], $flipfields['reside']);
		$flipfields['resideprovince'] = 'resideprovince';
		$flipfields['residecity'] = 'residecity';
		$flipfields['residedist'] = 'residedist';
	}
	$fields = array_keys($flipfields);
	if (!in_array('uniacid', $fields)) {
		$fields[] = 'uniacid';
	}
	if (!empty($pre)) {
		$pre .= '<br/>';
	}
	if (empty($uid)) {
		foreach ($fields as $field) {
			$profile[$field] = '';
		}
		$uniacid = $_W['uniacid'];
	} else {
		$profile = mc_fetch($uid, $fields);
		$uniacid = $profile['uniacid'];
	}

	$sql = 'SELECT `f`.`field`, `f`.`id` AS `fid`, `mf`.* FROM ' . tablename('profile_fields') . " AS `f` LEFT JOIN " .
		tablename('mc_member_fields') . " AS `mf` ON `f`.`id` = `mf`.`fieldid` WHERE `uniacid` = :uniacid ORDER BY
			`displayorder` DESC";
	$system_fields = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']), 'field');
	if (empty($system_fields)) {
		$sql = 'SELECT `id`, `field`, `title` FROM ' . tablename('profile_fields');
		$system_fields = pdo_fetchall($sql, array(), 'field');
	}

	$titles = array();
	foreach ($system_fields as $field) {
		$titles[$field['field']] = $field['title'];
	}

	$message = '';
	$ks = array();
	foreach ($profile as $k => $v) {
		if (empty($v)) {
			$ks[] = $k;
			$message .= $system_fields[$k]['title'] . ', ';
		}
	}

	if (!empty($message)) {
		$title = '完善资料';
		if (checksubmit('submit')) {
			if (in_array('resideprovince', $fields)) {
				$_GPC['resideprovince'] = $_GPC['reside']['province'];
				$_GPC['residecity'] = $_GPC['reside']['city'];
				$_GPC['residedist'] = $_GPC['reside']['district'];
			}
			if (in_array('birthyear', $fields)) {
				$_GPC['birthyear'] = $_GPC['birth']['year'];
				$_GPC['birthmonth'] = $_GPC['birth']['month'];
				$_GPC['birthday'] = $_GPC['birth']['day'];
			}
			$record = array_elements($fields, $_GPC);
			if (isset($record['uniacid'])) {
				unset($record['uniacid']);
			}

			foreach ($record as $field => $value) {
				if ($field == 'gender') {
					continue;
				}
				if (empty($value)) {
					message('请填写完整所有资料.', referer(), 'error');
				}
			}
			if (empty($record['nickname']) && !empty($_W['fans']['nickname'])) {
				$record['nickname'] = $_W['fans']['nickname'];
			}
			if (empty($record['avatar']) && !empty($_W['fans']['tag']['avatar'])) {
				$record['avatar'] = $_W['fans']['tag']['avatar'];
			}
			$condition = " AND uid != {$uid} ";
			if (in_array('email', $fields)) {
				$emailexists = pdo_fetchcolumn("SELECT email FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND email = :email " . $condition, array(':uniacid' => $_W['uniacid'], ':email' => trim($record['email'])));
				if (!empty($emailexists)) {
					message('抱歉，您填写的手机号已经被使用，请更新。', 'refresh', 'error');
				}
			}
			if (in_array('mobile', $fields)) {
				$mobilexists = pdo_fetchcolumn("SELECT mobile FROM " . tablename('mc_members') . " WHERE uniacid = :uniacid AND mobile = :mobile " . $condition, array(':uniacid' => $_W['uniacid'], ':mobile' => trim($record['mobile'])));
				if (!empty($mobilexists)) {
					message('抱歉，您填写的手机号已经被使用，请更新。', 'refresh', 'error');
				}
			}
			$insertuid = mc_update($uid, $record);
			if (empty($uid)) {
				pdo_update('mc_oauth_fans', array('uid' => $insertuid), array('oauth_openid' => $_W['openid']));
				pdo_update('mc_mapping_fans', array('uid' => $insertuid), array('openid' => $_W['openid']));
			}
			message('资料完善成功.', 'refresh');
		}
		load()->func('tpl');
		load()->model('activity');
		$filter = array();
		$filter['status'] = 1;
		$coupons = activity_coupon_owned($_W['member']['uid'], $filter);
		$tokens = activity_token_owned($_W['member']['uid'], $filter);

		$setting = uni_setting($_W['uniacid'], array('creditnames', 'creditbehaviors', 'uc'));
		$behavior = $setting['creditbehaviors'];
		$creditnames = $setting['creditnames'];
		$credits = mc_credit_fetch($_W['member']['uid'], '*');
		include template('mc/require', TEMPLATE_INCLUDEPATH);
		exit;
	}
	return $profile;
}


function mc_credit_update($uid, $credittype, $creditval = 0, $log = array()) {
	global $_W;
	$credittype = trim($credittype);
	$credittypes = mc_credit_types();
	$clerk_types = array(
		'1' => '线上操作',
		'2' => '系统后台',
		'3' => '店员',
	);
	if (!in_array($credittype, $credittypes)) {
		return error('-1', "指定的用户积分类型 “{$credittype}”不存在.");
	}
	$creditval = floatval($creditval);
	if (empty($creditval)) {
		return true;
	}
	$value = pdo_fetchcolumn("SELECT $credittype FROM " . tablename('mc_members') . " WHERE `uid` = :uid", array(':uid' => $uid));
	if ($creditval > 0 || ($value + $creditval >= 0) || $credittype == 'credit6') {
		pdo_update('mc_members', array($credittype => $value + $creditval), array('uid' => $uid));
	} else {
		return error('-1', "积分类型为“{$credittype}”的积分不够，无法操作。");
	}
		if (empty($log) || !is_array($log)) {
		load()->func('logging');
		if (!empty($GLOBALS['site']) && $GLOBALS['site'] instanceof WeModuleSite) {
			$log = array(
				$uid, 
				$GLOBALS['site']->module['title'] . '模块内消费' . logging_implode($_GET),
				$GLOBALS['site']->module['name'],
				0,
			);
		} elseif (!empty($GLOBALS['_GPC']['m'])) {
			$modules = uni_modules();
			$log = array(
				$uid,
				$modules[$GLOBALS['_GPC']['m']]['title'] . '模块内消费' . logging_implode($_GET),
				$GLOBALS['_GPC']['m'],
				0,
			);
		} else {
			$log = array($uid, '未记录', 0, 0);
		}
	}
	if ($credittype == 'credit1') {
		$credittype_name = '积分';
	} elseif ($credittype == 'credit2') {
		$credittype_name = '元';
	}
	if (empty($log[1])) {
		if ($creditval > 0) {
			$log[1] = $clerk_types[$log[5]] . ': 添加' . $creditval . $credittype_name;
		} else {
			$log[1] = $clerk_types[$log[5]] . ': 减少' . -$creditval . $credittype_name;
		}
		
	}
	$clerk_type = intval($log[5]) ? intval($log[5]) : 1;
	$data = array(
		'uid' => $uid,
		'credittype' => $credittype,
		'uniacid' => $_W['uniacid'],
		'num' => $creditval,
		'createtime' => TIMESTAMP,
		'operator' => intval($log[0]),
		'module' => trim($log[2]),
		'clerk_id' => intval($log[3]),
		'store_id' => intval($log[4]),
		'clerk_type' => $clerk_type,
		'remark' => $log[1],
	);
	pdo_insert('mc_credits_record', $data);

	return true;
}


function mc_account_change_operator($clerk_type, $store_id, $clerk_id) {
	global $stores, $clerks, $_W;
	if(empty($stores) || empty($clerks)) {
		$clerks = pdo_getall('activity_clerks', array('uniacid' => $_W['uniacid']), array('id', 'name'), 'id');
		$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid']), array('id', 'business_name', 'branch_name'), 'id');
	}
	$data = array(
		'clerk_cn' => '',
		'store_cn' => '',
	);
	if($clerk_type == 1) {
		$data['clerk_cn'] = '系统';
	} elseif($clerk_type == 2) {
		$data['clerk_cn'] = pdo_fetchcolumn('SELECT username FROM ' . tablename('users') . ' WHERE uid = :uid', array(':uid' => $clerk_id));
	} elseif($clerk_type == 3) {
		$data['clerk_cn'] = $clerks[$clerk_id]['name'];
		$data['store_cn'] = $stores[$store_id]['business_name'] . ' ' . $stores[$store_id]['branch_name'];
	}
	return $data;
}

function mc_credit_fetch($uid, $types = array()) {
	if (empty($types) || $types == '*') {
		$select = 'credit1,credit2,credit3,credit4,credit5,credit6';
	} else {
		$struct = mc_credit_types();
		foreach ($types as $key => $type) {
			if (!in_array($type, $struct)) {
				unset($types[$key]);
			}
		}
		$select = '`' . implode('`,`', $types) . '`';
	}
	return pdo_fetch("SELECT {$select} FROM ".tablename('mc_members').' WHERE uid = :uid LIMIT 1',array(':uid' => $uid));
}


function mc_credit_types(){
	static $struct = array('credit1','credit2','credit3','credit4','credit5','credit6');
	return $struct;
}


function mc_groups($uniacid = 0) {
	global $_W;
	$uniacid = intval($uniacid);
	if (empty($uniacid)) {
		$uniacid = $_W['uniacid'];
	}
	$sql = "SELECT * FROM " . tablename('mc_groups') . ' WHERE `uniacid`=:uniacid ORDER BY credit';
	return pdo_fetchall($sql, array(':uniacid' => $uniacid), 'groupid');
}


function mc_fans_groups($force_update = false) {
	global $_W;
	$sql = "SELECT groups FROM " . tablename('mc_fans_groups') . ' WHERE `uniacid` = :uniacid AND acid = :acid';
	$results = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
	if(!empty($results) && !$force_update) {
		$results = iunserializer($results);
		return $results;
	}
	$account = WeAccount::create($_W['acid']);
	$groups = $account->fetchFansGroups();
	if(is_error($groups)) {
		return $groups;
	}
	if(!empty($groups['groups'])) {
		$groups_tmp = array();
		foreach($groups['groups'] as $da) {
			$groups_tmp[$da['id']] = $da;
		}
	}
	if(empty($results)) {
		$data = array('acid' => $_W['acid'], 'uniacid' => $_W['uniacid'], 'groups' => iserializer($groups_tmp));
		pdo_insert('mc_fans_groups', $data);
	} else {
		$data = array('groups' => iserializer($groups_tmp));
		pdo_update('mc_fans_groups', $data, array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid']));
	}
	return $groups_tmp;
}



function _mc_login($member) {
	global $_W;

	if (!empty($member) && !empty($member['uid'])) {
		$sql = 'SELECT `uid`,`realname`,`mobile`,`email`,`groupid`,`credit1`,`credit2`,`credit6` FROM ' . tablename('mc_members') . ' WHERE `uid`=:uid AND `uniacid`=:uniacid';
		$member = pdo_fetch($sql, array(':uid' => $member['uid'], ':uniacid' => $_W['uniacid']));
		if (!empty($member) && (!empty($member['mobile']) || !empty($member['email']))) {
			$_W['member'] = $member;
			$_W['member']['groupname'] = $_W['uniaccount']['groups'][$member['groupid']]['title'];
			$_SESSION['uid'] = $member['uid'];
			mc_group_update();
			if (empty($_W['openid'])) {
				$fan = mc_fansinfo($member['uid']);
				if (!empty($fan)) {
					$_SESSION['openid'] = $fan['openid'];
					$_W['openid'] = $fan['openid'];
					$_W['fans'] = $fan;
					$_W['fans']['from_user'] = $_W['openid'];
				} else {
					$_W['openid'] = $member['uid'];
					$_W['fans'] = array(
						'from_user' => $member['uid'],
						'follow' => 0
					);
				}
			}
			isetcookie('logout', '', -60000);
			return true;
		}
	}
	return false;
}


function mc_fields() {
	return array(
		'mobile' => '手机号码',
		'email' => '电子邮箱',
		'realname' => '真实姓名',
		'nickname' => '昵称',
		'avatar' => '头像',
		'qq' => 'QQ号',
		'gender' => '性别',
		'birth' => '生日',
		'constellation' => '星座',
		'zodiac' => '生肖',
		'telephone' => '固定电话',
		'idcard' => '证件号码',
		'studentid' => '学号',
		'grade' => '班级',
		'address' => '地址',
		'zipcode' => '邮编',
		'nationality' => '国籍',
		'reside' => '居住地',
		'graduateschool' => '毕业学校',
		'company' => '公司',
		'education' => '学历',
		'occupation' => '职业',
		'position' => '职位',
		'revenue' => '年收入',
		'affectivestatus' => '情感状态',
		'lookingfor' => ' 交友目的',
		'bloodtype' => '血型',
		'height' => '身高',
		'weight' => '体重',
		'alipay' => '支付宝帐号',
		'msn' => 'MSN',
		'taobao' => '阿里旺旺',
		'site' => '主页',
		'bio' => '自我介绍',
		'interest' => '兴趣爱好'
	);
}


function mc_acccount_fields($uniacid = 0, $is_available = true) {
	global $_W;
	if(!$uniacid) {
		$uniacid = $_W['uniacid'];
	}
	$condition = ' WHERE a.uniacid = :uniacid';
	$params = array(':uniacid' => $uniacid);
	if($is_available) {
		$condition . ' AND a.available = 1';
	}
	$data = pdo_fetchall('SELECT a.title, b.field FROM ' . tablename('mc_member_fields') . ' AS a LEFT JOIN ' . tablename('profile_fields') . ' as b ON a.fieldid = b.id' . $condition, $params, 'field');
	$fields = array();
	foreach($data as $row) {
		$fields[$row['field']] = $row['title'];
	}
	return $fields;
}


function mc_init_uc() {
	global $_W;
	$setting = uni_setting($_W['uniacid'], array('uc'));
	if (is_array($setting['uc']) && $setting['uc']['status'] == '1') {
		$uc = $setting['uc'];
		define('UC_CONNECT', $uc['connect'] == 'mysql' ? 'mysql' : '');

		define('UC_DBHOST', $uc['dbhost']);
		define('UC_DBUSER', $uc['dbuser']);
		define('UC_DBPW', $uc['dbpw']);
		define('UC_DBNAME', $uc['dbname']);
		define('UC_DBCHARSET', $uc['dbcharset']);
		define('UC_DBTABLEPRE', $uc['dbtablepre']);
		define('UC_DBCONNECT', $uc['dbconnect']);

		define('UC_CHARSET', $uc['charset']);
		define('UC_KEY', $uc['key']);
		define('UC_API', $uc['api']);
		define('UC_APPID', $uc['appid']);
		define('UC_IP', $uc['ip']);

		require IA_ROOT . '/framework/library/uc/client.php';
		return true;
	}
	return false;
}


function mc_handsel($touid, $fromuid, $handsel, $uniacid = '') {
	global $_W;
	$touid = intval($touid);
	$fromuid = intval($fromuid);
	if (empty($uniacid)) {
		$uniacid = $_W['uniacid'];
	}
	$touid_exist = mc_fetch($touid, array('uniacid'));
	if (empty($touid_exist)) {
		return error(-1, '赠送积分用户不存在');
	}
	if (empty($handsel['module'])) {
		return error(-1, '没有填写模块名称');
	}
	if (empty($handsel['sign'])) {
		return error(-1, '没有填写赠送积分对象信息');
	}
	if (empty($handsel['action'])) {
		return error(-1, '没有填写赠送积分动作');
	}
	$credit_value = intval($handsel['credit_value']);

	$sql = 'SELECT id FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND touid = :touid AND fromuid = :fromuid AND module = :module AND sign = :sign AND action = :action';
	$parm = array(':uniacid' => $uniacid, ':touid' => $touid, ':fromuid' => $fromuid, ':module' => $handsel['module'], ':sign' => $handsel['sign'], ':action' => $handsel['action']);
	$handsel_exists = pdo_fetch($sql, $parm);
	if (!empty($handsel_exists)) {
		return error(-1, '已经赠送过积分,每个用户只能赠送一次');
	}

	$creditbehaviors = pdo_fetchcolumn('SELECT creditbehaviors FROM ' . tablename('uni_settings') . ' WHERE uniacid = :uniacid', array(':uniacid' => $uniacid));
	$creditbehaviors = iunserializer($creditbehaviors) ? iunserializer($creditbehaviors) : array();
	if (empty($creditbehaviors['activity'])) {
		return error(-1, '公众号没有配置积分行为参数');
	} else {
		$credittype = $creditbehaviors['activity'];
	}

	$data = array(
		'uniacid' => $uniacid,
		'touid' => $touid,
		'fromuid' => $fromuid,
		'module' => $handsel['module'],
		'sign' => $handsel['sign'],
		'action' => $handsel['action'],
		'credit_value' => $credit_value,
		'createtime' => TIMESTAMP
	);
	pdo_insert('mc_handsel', $data);
	$log = array($fromuid, $handsel['credit_log']);
	mc_credit_update($touid, $credittype, $credit_value, $log);
	return true;
}


function mc_openid2uid($openid) {
	global $_W;
	if (is_numeric($openid)) {
		return $openid;
	}
	if (is_string($openid)) {
		$sql = 'SELECT uid FROM ' . tablename('mc_mapping_fans') . ' WHERE `uniacid`=:uniacid AND `openid`=:openid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		$pars[':openid'] = $openid;
		$uid = pdo_fetchcolumn($sql, $pars);
		return $uid;
	}
	if (is_array($openid)) {
		$uids = array();
		foreach ($openid as $k => $v) {
			if (is_numeric($v)) {
				$uids[] = $v;
			} elseif (is_string($v)) {
				$fans[] = $v;
			}
		}
		if (!empty($fans)) {
			$sql = 'SELECT uid, openid FROM ' . tablename('mc_mapping_fans') . " WHERE `uniacid`=:uniacid AND `openid` IN ('" . implode("','", $fans) . "')";
			$pars = array(':uniacid' => $_W['uniacid']);
			$fans = pdo_fetchall($sql, $pars, 'uid');
			$fans = array_keys($fans);
			$uids = array_merge((array)$uids, $fans);
		}
		return $uids;
	}
	return false;
}


function mc_group_update($uid = 0) {
	global $_W;
	if(!$_W['uniaccount']['grouplevel']) {
		return true;
	}
	$uid = intval($uid);
	if($uid <= 0) {
		$uid = $_W['member']['uid'];
		$user = $_W['member'];
		$user['openid'] = $_W['openid'];
	} else {
		$user = pdo_fetch('SELECT uid, realname, credit1, credit6, groupid FROM ' . tablename('mc_members') . ' WHERE uniacid = :uniacid AND uid = :uid', array(':uniacid' => $_W['uniacid'], ':uid' => $uid));
		$user['openid'] = pdo_fetchcolumn('SELECT openid FROM ' . tablename('mc_mapping_fans') . ' WHERE acid = :acid AND uid = :uid', array(':acid' => $_W['acid'], ':uid' => $uid));
	}
	if(empty($user)) {
		return false;
	}
	$groupid = $user['groupid'];
	$credit = $user['credit1'] + $user['credit6'];
	$groups = $_W['uniaccount']['groups'];
	if(empty($groups)) {
		return false;
	}
	$data = array();
	foreach($groups as $group) {
		$data[$group['groupid']] = $group['credit'];
	}
	asort($data);
	if($_W['uniaccount']['grouplevel'] == 1) {
				foreach($data as $k => $da) {
			if($credit >= $da) {
				$groupid = $k;
			}
		}
	} else {
				$now_group_credit = $data[$user['groupid']];
		if($now_group_credit < $credit) {
			foreach($data as $k => $da) {
				if($credit >= $da) {
					$groupid = $k;
				}
			}
		}
	}
	if($groupid > 0 && $groupid != $user['groupid']) {
		pdo_update('mc_members', array('groupid' => $groupid), array('uniacid' => $_W['uniacid'], 'uid' => $uid));
		mc_notice_group($user['openid'], $_W['uniaccount']['groups'][$user['groupid']]['title'], $_W['uniaccount']['groups'][$groupid]['title']);
	}
	$user['groupid'] = $groupid;
	$_W['member']['groupid'] = $groupid;
	$_W['member']['groupname'] = $_W['uniaccount']['groups'][$groupid]['title'];
	return $user['groupid'];
}

function mc_notice_init() {
	global $_W;
	if(empty($_W['account'])) {
		$_W['account'] = uni_fetch($_W['uniacid']);
	}
	if(empty($_W['account'])) {
		return error(-1, '创建公众号操作类失败');
	}
	if($_W['account']['level'] < 3) {
		return error(-1, '公众号没有经过认证，不能使用模板消息和客服消息');
	}
	$acc = WeAccount::create();
	if(is_null($acc)) {
		return error(-1, '创建公众号操作对象失败');
	}
	$setting = uni_setting();
	$noticetpl = $setting['tplnotice'];
	$acc->noticetpl = $noticetpl;
	if(!is_array($acc->noticetpl)) {
		return error(-1, '微信通知参数错误');
	}
	return $acc;
}


function mc_notice_public($openid, $title, $sender, $content, $url = '', $remark = '') {
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	$data = array(
		'first' => array(
			'value' => $title,
			'color' => '#ff510'
		),
		'keyword1' => array(
			'value' => $sender,
			'color' => '#ff510'
		),
		'keyword2' => array(
			'value' => $content,
			'color' => '#ff510'
		),
		'remark' => array(
			'value' => $remark,
			'color' => '#ff510'
		),
	);
	$status = $acc->sendTplNotice($openid, $acc->noticetpl['public'], $data, $url);
	return $status;
}


function mc_notice_recharge($openid, $uid = 0, $num = 0, $url = '', $remark = '') {
	global $_W;
	if(!$uid) {
		$uid = $_W['member']['uid'];
	}
	if(!$uid || !$num || empty($openid)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['recharge']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$credit = mc_credit_fetch($uid);
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/bond/credits', array('credittype' => 'credit2'), true, true);
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您在{$time}进行会员余额充值，充值金额{$num}元，充值后余额为{$credit['credit2']}元",
				'color' => '#ff510'
			),
			'accountType' => array(
				'value' => '会员UID',
				'color' => '#ff510'
			),
			'account' => array(
				'value' => $uid,
				'color' => '#ff510'
			),
			'amount' => array(
				'value' => $num . '元',
				'color' => '#ff510'
			),
			'result' => array(
				'value' => '充值成功',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['recharge']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】充值通知\n";
		$info .= "您在{$time}进行会员余额充值，充值金额【{$num}】元，充值后余额【{$credit['credit2']}】元。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_credit2($openid, $uid, $credit2_num, $credit1_num = 0, $store = '线下消费', $url = '', $remark = '谢谢惠顾，点击查看详情') {
	global $_W;
	if(!$uid) {
		$uid = $_W['member']['uid'];
	}
	if(!$uid || !$credit2_num || empty($openid)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['credit2']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$credit = mc_credit_fetch($uid);
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/bond/credits', array('credittype' => 'credit2'), true, true);
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您在{$time}有余额消费",
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => abs($credit2_num) . '元',
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => floatval($credit1_num) . '积分',
				'color' => '#ff510'
			),
			'keyword3' => array(
				'value' => trim($store),
				'color' => '#ff510'
			),
			'keyword4' => array(
				'value' => $credit['credit2'] . '元',
				'color' => '#ff510'
			),
			'keyword5' => array(
				'value' => $credit['credit1'] . '积分',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['credit2']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】消费通知\n";
		$info .= "您在{$time}进行会员余额消费，消费金额【{$credit2_num}】元，获得积分【{$credit1_num}】,消费后余额【{$credit['credit2']}】元，消费后积分【{$credit['credit1']}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_credit1($openid, $uid, $credit1_num, $tip, $url = '', $remark = '谢谢惠顾，点击查看详情') {
	global $_W;
	if(!$uid) {
		$uid = $_W['member']['uid'];
	}
	if(!$uid || !$credit1_num || empty($tip)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['credit1']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$credit = mc_credit_fetch($uid);
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/bond/credits', array('credittype' => 'credit1'), true, true);
	}
	$credit1_num = floatval($credit1_num);
	$type = '消费';
	if($credit1_num > 0) {
		$type = '到账';
	}
	$username = $_W['member']['realname'];
	if(empty($username)) {
		$username = $_W['member']['nickname'];
	}
	if(empty($username)) {
		$username = $uid;
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您在{$time}有积分变更",
				'color' => '#ff510'
			),
			'account' => array(
				'value' => $username,
				'color' => '#ff510'
			),
			'time' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'type' => array(
				'value' => $tip,
				'color' => '#ff510'
			),
			'creditChange' => array(
				'value' => $type,
				'color' => '#ff510'
			),
			'number' => array(
				'value' => abs($credit1_num) . '积分',
				'color' => '#ff510'
			),
			'creditName' => array(
				'value' => '账户积分',
				'color' => '#ff510'
			),
			'amount' => array(
				'value' => abs($credit['credit1']) . '积分',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['credit1']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】积分变更通知\n";
		$info .= "您在{$time}有积分{$type}，{$type}积分【{$credit1_num}】元，变更原因：【{$tip}】,消费后账户积分余额【{$credit['credit1']}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}

function mc_notice_group($openid, $old_group, $now_group, $url = '', $remark = '点击查看详情') {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['group']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$time = date('Y-m-d H:i');
	if(empty($url)) {
		$url = murl('mc/home', array(), true, true);
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的会员组变更为{$now_group}",
				'color' => '#ff510'
			),
			'grade1' => array(
				'value' => $old_group,
				'color' => '#ff510'
			),
			'grade2' => array(
				'value' => $now_group,
				'color' => '#ff510'
			),
			'time' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}",
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['group']['tpl'], $data, $url);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】会员组变更通知\n";
		$info .= "您的会员等级在{$time}由{$old_group}变更为{$now_group}。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_nums_plus($openid, $type, $num, $total_num, $remark = '感谢您的支持，祝您生活愉快！') {
	global $_W;
	if(empty($num) || empty($total_num) || empty($type)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['nums_plus']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$time = date('Y-m-d H:i');
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的{$type}已充次成功",
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => $num . '次',
				'color' => '#ff510'
			),
			'keyword3' => array(
				'value' => $total_num . '次',
				'color' => '#ff510'
			),
			'keyword4' => array(
				'value' => '用完为止',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['nums_plus']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】-【{$type}】充值通知\n";
		$info .= "您的{$type}已充值成功，本次充次【{$num}】次，总剩余【{$total_num}】次。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_nums_times($openid, $card_id, $type, $num, $remark = '感谢您对本店的支持，欢迎下次再来！') {
	global $_W;
	if(empty($num) || empty($type) || empty($card_id)) {
		return error(-1, '参数错误');
	}
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['nums_times']['tpl'])) {
		return error(-1, '未开启通知');
	}
	$time = date('Y-m-d H:i');
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的{$type}已成功使用了【1】次。",
				'color' => '#ff510'
			),
			'keyword1' => array(
				'value' => $card_id,
				'color' => '#ff510'
			),
			'keyword2' => array(
				'value' => $time,
				'color' => '#ff510'
			),
			'keyword3' => array(
				'value' => $num . '次',
				'color' => '#ff510'
			),
			'keyword4' => array(
				'value' => '用完为止',
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['nums_times']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】-【{$type}】消费通知\n";
		$info .= "您的{$type}已成功使用了一次，总剩余【{$num}】次，消费时间【{$time}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_times_plus($openid, $card_id, $type, $fee, $days, $endtime = '', $remark = '感谢您对本店的支持，欢迎下次再来！') {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['times_plus']['tpl'])) {
		return error(-1, '未开启通知');
	}
	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => "您好，您的{$type}已续费成功。",
				'color' => '#ff510'
			),
			'keynote1' => array(
				'value' => $type,
				'color' => '#ff510'
			),
			'keynote2' => array(
				'value' => $card_id,
				'color' => '#ff510'
			),
			'keynote3' => array(
				'value' => $fee . '元',
				'color' => '#ff510'
			),
			'keynote4' => array(
				'value' => $days . '天',
				'color' => '#ff510'
			),
			'keynote5' => array(
				'value' => $endtime,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['times_plus']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】-【{$type}】续费通知\n";
		$info .= "您的{$type}已成功续费，续费时长【{$days}】天，续费金额【{$fee}】元，有效期至【{$endtime}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_times_times($openid, $title, $type, $endtime = '', $remark = '请注意时间，防止服务失效！') {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if(empty($acc->noticetpl['times_times']['tpl'])) {
		return error(-1, '未开启通知');
	}

	if($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY) {
		$data = array(
			'first' => array(
				'value' => $title,
				'color' => '#ff510'
			),
			'name' => array(
				'value' => $type,
				'color' => '#ff510'
			),
			'expDate' => array(
				'value' => $endtime,
				'color' => '#ff510'
			),
			'remark' => array(
				'value' => "{$remark}" ,
				'color' => '#ff510'
			),
		);
		$status = $acc->sendTplNotice($openid, $acc->noticetpl['times_times']['tpl'], $data);
	}
	if($_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY) {
		$info = "【{$_W['account']['name']}】-【{$type}】服务到期通知\n";
		$info .= "您的{$type}即将到期，有效期至【{$endtime}】。\n";
		$info .= !empty($remark) ? "备注：{$remark}\n\n" : '';
		$custom = array(
			'msgtype' => 'text',
			'text' => array('content' => urlencode($info)),
			'touser' => $openid,
		);
		$status = $acc->sendCustomNotice($custom);
	}
	return $status;
}


function mc_notice_consume($openid, $title, $content, $url = '') {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	if($_W['account']['level'] == 4) {
		mc_notice_credit2($openid, $content['uid'], $content['credit2_num'], $content['credit1_num'], $content['store'], '', $content['remark']);
	}
	if($_W['account']['level'] == 3) {
		mc_notice_custom_text($openid, $title, $content);
	}
	return true;
}

function mc_notice_custom_text($openid, $title, $info) {
	global $_W;
	$acc = mc_notice_init();
	if(is_error($acc)) {
		return error(-1, $acc['message']);
	}
	$custom = array(
		'msgtype' => 'text',
		'text' => array('content' => urlencode($title . '\n' . $info)),
		'touser' => $openid,
	);
	$status = $acc->sendCustomNotice($custom);
	return $status;
}

function mc_plugins() {
	global $_W;
	$plugins = array(
		'card' => array(
			'title' => '会员卡',
			'name' => 'card',
			'description' => '提供粉丝可开通会员卡并可以设置充值、消费金额及积分的增减策略',
		),
		'sign' => array(
			'title' => '签到',
			'name' => 'sign',
			'description' => '提供粉丝可每天签到获取积分',
		),
		'exchange' => array(
			'title' => '兑换中心',
			'name' => 'exchange',
			'description' => '提供粉丝可通过积分进行代金劵、折扣劵或是真实物品的兑换',
		),
		'paycenter' => array(
			'title' => '收银台',
			'name' => 'paycenter',
			'description' => '提供店员可通过手机操作进行收款以及积分相关操作',
		),
		'recommend' => array(
			'title' => '每日推荐',
			'name' => 'recommend',
			'description' => '提供可对粉丝发布推荐通知信息',
		),
		'business' => array(
			'title' => '门店管理',
			'name' => 'business',
			'description' => '提供可发布门店信息',
		),
	);
	return $plugins;
}
