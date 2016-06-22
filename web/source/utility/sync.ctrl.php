<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

if(!empty($_W['uniacid'])) {
	load()->model('account');
	load()->model('mc');
	$setting = uni_setting($_W['uniacid'], 'sync');
	$sync = $setting['sync'];
	if($sync != 1) {
		exit();
	}
	if($_W['account']['type'] == 1 && $_W['account']['level'] >= 3) {
		$data = pdo_fetchall('SELECT fanid, openid, acid, uid, uniacid FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid AND acid = :acid AND follow = 1 ORDER BY updatetime ASC, fanid DESC LIMIT 10", array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
		if(!empty($data)) {
			$acc = WeAccount::create($_W['acid']);
			foreach($data as $row) {
				$fan = $acc->fansQueryInfo($row['openid'], true);
				if(!is_error($fan) && $fan['subscribe'] == 1) {
					$group = $acc->fetchFansGroupid($row['openid']);
					$record = array();
					if(!is_error($group)) {
						$record['groupid'] = $group['groupid'];
					}
					$record['updatetime'] = time();
					$record['followtime'] = $fan['subscribe_time'];
					$record['follow'] = 1;
					$fan['nickname'] = stripcslashes($fan['nickname']);
					$record['nickname'] = stripslashes($fan['nickname']);
					if(!empty($fan['remark'])) {
						$fan['remark'] = stripslashes($fan['remark']);
					}
					$record['tag'] = iserializer($fan);
					$record['tag'] = base64_encode($record['tag']);
					pdo_update('mc_mapping_fans', $record, array('fanid' => $row['fanid']));

					if(!empty($row['uid'])) {
						$user = mc_fetch($row['uid'], array('nickname', 'gender', 'residecity', 'resideprovince', 'nationality', 'avatar'));
						$rec = array();
						if(empty($user['nickname']) && !empty($fan['nickname'])) {
														$rec['nickname'] = stripslashes($fan['nickname']);
						}
						if(empty($user['gender']) && !empty($fan['sex'])) {
							$rec['gender'] = $fan['sex'];
						}
						if(empty($user['residecity']) && !empty($fan['city'])) {
							$rec['residecity'] = $fan['city'] . '市';
						}
						if(empty($user['resideprovince']) && !empty($fan['province'])) {
							$rec['resideprovince'] = $fan['province'] . '省';
						}
						if(empty($user['nationality']) && !empty($fan['country'])) {
							$rec['nationality'] = $fan['country'];
						}
						if(empty($user['avatar']) && !empty($fan['headimgurl'])) {
							$rec['avatar'] = rtrim($fan['headimgurl'], '0') . 132;
						}
						if(!empty($rec)) {
							pdo_update('mc_members', $rec, array('uid' => $row['uid']));
						}
					}
				}
			}
		}
	}
}