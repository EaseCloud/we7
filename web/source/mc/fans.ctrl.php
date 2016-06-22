<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_fans');
load()->model('mc');
$dos = array('display', 'view', 'initsync', 'updategroup');
$do = in_array($do, $dos) ? $do : 'display';
if ($do == 'display') {
	$_W['page']['title'] = '粉丝列表 - 粉丝 - 会员中心';
	if (checksubmit('submit')) {
		if (!empty($_GPC['delete'])) {
			$fanids = array();
			foreach ($_GPC['delete'] as $v) {
				$fanids[] = intval($v);
			}
			pdo_query("DELETE FROM " . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid AND fanid IN ('" . implode("','", $fanids) . "')",array(':uniacid' => $_W['uniacid']));
			message('粉丝删除成功！', url('mc/fans/', array('type' => $_GPC['type'])), 'success');
		}
	}
	$acid = $_W['acid'];
	if ($_W['isajax']) {
		$post = $_GPC['__input'];
		if ($post['method'] == 'sync') {
			if (is_array($post['fanids'])) {
				$fanids = array();
				foreach ($post['fanids'] as $fanid) {
					$fanid = intval($fanid);
					$fanids[] = $fanid;
				}
				$fanids = implode(',', $fanids);
				$sql = 'SELECT `fanid`,`uid`,`openid` FROM ' . tablename('mc_mapping_fans') . " WHERE `acid`='{$acid}' AND `fanid` IN ({$fanids})";
				$ds = pdo_fetchall($sql);
				$acc = WeAccount::create($acid);
				foreach ($ds as $row) {
					$fan = $acc->fansQueryInfo($row['openid'], true);
					if (!is_error($fan) && $fan['subscribe'] == 1) {
						$group = $acc->fetchFansGroupid($row['openid']);
						$record = array();
						if(!is_error($group)) {
							$record['groupid'] = $group['groupid'];
						}
						$record['updatetime'] = TIMESTAMP;
						$record['followtime'] = $fan['subscribe_time'];
						$fan['nickname'] = stripcslashes($fan['nickname']);
						$record['nickname'] = stripslashes($fan['nickname']);
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
					} elseif (!is_error($fan) && empty($fan['subscribe'])) {
						pdo_update('mc_mapping_fans', array('follow' => 0, 'unfollowtime' => TIMESTAMP), array('fanid' => $row['fanid']));
					}
				}
			}
			exit('success');
		}
		if ($post['method'] == 'download') {
			$acc = WeAccount::create($acid);
			if(!empty($post['next'])) {
				$_GPC['next_openid'] = $post['next'];
			} else {
				pdo_update('mc_mapping_fans', array('follow' => 0), array('uniacid' => $_W['uniacid']));
			}
			$fans = $acc->fansAll();
			if(!is_error($fans) && is_array($fans['fans'])) {
				$count = count($fans['fans']);
				$buffSize = ceil($count / 500);
				for($i = 0; $i < $buffSize; $i++) {
					$buffer = array_slice($fans['fans'], $i * 500, 500);
					$openids = implode("','", $buffer);
					$openids = "'{$openids}'";
					$sql = 'SELECT `openid`, `uniacid`, `acid` FROM ' . tablename('mc_mapping_fans') . " WHERE `openid` IN ({$openids})";
					$ds = pdo_fetchall($sql, array(), 'openid');
					$repeat_openids = array();
					$sql = '';
					foreach($buffer as $openid) {
						if(!empty($ds) && !empty($ds[$openid])) {
							if ($ds[$openid]['uniacid'] != $_W['uniacid']) {
								$repeat_openids[] = $openid;
							} else {
								unset($ds[$openid]);
								continue;
							}
						}
				
						$salt = random(8);
						$sql .= "('{$acid}', '{$_W['uniacid']}', 0, '{$openid}', '{$salt}', 1, 0, ''),";
					}
					if (!empty($repeat_openids)) {
						pdo_delete('mc_mapping_fans', array('openid' => $repeat_openids));
					}
					if(!empty($sql)) {
						$sql = rtrim($sql, ',');
						$sql = 'INSERT INTO ' . tablename('mc_mapping_fans') . ' (`acid`, `uniacid`, `uid`, `openid`, `salt`, `follow`, `followtime`, `tag`) VALUES ' . $sql;
						pdo_query($sql);
					}
					pdo_query("UPDATE " . tablename('mc_mapping_fans') . " SET follow = '1' WHERE `openid` IN ({$openids})");
				}
				$ret = array();
				$ret['total'] = $fans['total'];
				if(!empty($fans['fans'])) {
					$ret['count'] = count($fans['fans']);
				} else {
					$ret['count'] = 0;
				}
				if(!empty($fans['next'])) {
					$ret['next'] = $fans['next'];
				}
				exit(json_encode($ret));
			} else {
				exit(json_encode($fans));
			}
		}
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 50;
	$condition = " WHERE `uniacid` = :uniacid AND `acid` = :acid";
	$pars = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$pars[':acid'] = $_W['acid'];
	
	if($_GPC['type'] == 'bind') {
		$condition .= ' AND `uid` > 0';
		$type = 'bind';
	} elseif($_GPC['type'] == 'unbind') {
		$condition .= ' AND `uid` = 0';
		$type = 'unbind';
	}
	$nickname = trim($_GPC['nickname']);
	if(!empty($nickname)) {
		$condition .= " AND ((nickname LIKE '%{$nickname}%') OR (`openid` = '{$nickname}'))";
	}
	if (!empty($_GPC['time']['start'])) {
		$starttime = strtotime($_GPC['time']['start']);
		$endtime = strtotime($_GPC['time']['end']) + 86399;
		$pars[':starttime'] = $starttime;
		$pars[':endtime'] = $endtime;
	}
	$follow = intval($_GPC['follow']) ? intval($_GPC['follow']) : 1;
	if($follow == 1) {
		$orderby = ' ORDER BY followtime DESC';
		$condition .= ' AND follow = 1';
		if (!empty($starttime)) {
			$condition .= ' AND followtime >= :starttime AND followtime <= :endtime';
		}
	} elseif ($follow == 2) {
		$orderby = ' ORDER BY unfollowtime DESC';
		$condition .= ' AND follow = 0';
		if (!empty($starttime)) {
			$condition .= ' AND followtime >= :starttime AND followtime <= :endtime';
		}
	}
	$fans_group = pdo_fetch('SELECT * FROM ' . tablename('mc_fans_groups') . ' WHERE uniacid = :uniacid AND acid = :acid', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
	$fans_group = iunserializer($fans_group['groups']);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('mc_mapping_fans').$condition, $pars);
	$list = pdo_fetchall("SELECT * FROM ".tablename('mc_mapping_fans') . $condition . $orderby . ' LIMIT ' .($pindex - 1) * $psize.','.$psize, $pars);
	if(!empty($list)) {
		foreach($list as &$v) {
			if(!empty($v['uid'])) {
				$user = mc_fetch($v['uid'], array('realname', 'nickname', 'mobile', 'email', 'avatar'));
				if(!empty($user['avatar'])){
					$user['avatar'] = tomedia($user['avatar']);
				}
			}
			if (!empty($v['tag']) && is_string($v['tag'])) {
				if (is_base64($v['tag'])){
					$v['tag'] = base64_decode($v['tag']);
				}
								if (is_serialized($v['tag'])) {
					$v['tag'] = @iunserializer($v['tag']);
				}
				if(!empty($v['tag']['headimgurl'])) {
					$v['tag']['avatar'] = tomedia($v['tag']['headimgurl']);
				}
			}
			if(empty($v['tag'])) {
				$v['tag'] = array();
			}
			if(!empty($user)) {
				$niemmo = $user['realname'];
				if(empty($niemmo)) {
					$niemmo = $user['nickname'];
				}
				if(empty($niemmo)) {
					$niemmo = $user['mobile'];
				}
				if(empty($niemmo)) {
					$niemmo = $user['email'];
				}
				if(empty($niemmo) || (!empty($niemmo) && substr($niemmo, -6) == 'we7.cc' && strlen($niemmo) == 39)) {
					$niemmo_effective = 0;
				} else {
					$niemmo_effective = 1;
				}
				$v['user'] = array('niemmo_effective' => $niemmo_effective, 'niemmo' => $niemmo, 'nickname' => $user['nickname']);
			}
			if(empty($v['user']['nickname']) && !empty($v['tag']['nickname'])){
				$v['user']['nickname'] = $v['tag']['nickname'];
			}
			if(empty($v['user']['avatar']) && !empty($v['tag']['avatar'])){
				$v['user']['avatar'] = $v['tag']['avatar'];
			}
			unset($user,$niemmo,$niemmo_effective);
		}
	}
	$pager = pagination($total, $pindex, $psize);
	$fans['total'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_mapping_fans') . ' WHERE uniacid = :uniacid AND acid = :acid AND follow = 1', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']));
}

if($do == 'view') {
	$_W['page']['title'] = '粉丝详情 - 粉丝 - 会员中心';
	$fanid = intval($_GPC['id']);
	if(empty($fanid)) {
		message('访问错误.');
	}
	$row = pdo_fetch("SELECT * FROM ".tablename('mc_mapping_fans')." WHERE fanid = :fanid AND uniacid = :uniacid LIMIT 1", array(':fanid' => $fanid,':uniacid' => $_W['uniacid']));
	$account = WeAccount::create($row['acid']);
	$accountInfo = $account->fetchAccountInfo();
	$row['account'] = $accountInfo['name'];
	if(!empty($row['uid'])) {
		$user = mc_fetch($row['uid'], array('nickname', 'mobile', 'email'));
		$row['user'] = $user['nickname'];
		if(empty($row['user'])) {
			$row['user'] = $user['mobile'];
		}
		if(empty($row['user'])) {
			$row['user'] = $user['email'];
		}
		if(!empty($row['user']) && substr($row['user'], -6) == 'we7.cc' && strlen($row['user']) == 39) {
			$row['user'] = "用户uid：{$row['uid']}。昵称,手机号,邮箱尚未完善";
		}
	} else {
		$row['user'] = '还未登记为会员';
	}
}

if($do == 'initsync') {
	$acid = intval($_W['acid']);
	if(intval($_GPC['page']) == 0) {
		message('正在更新粉丝数据,请不要关闭浏览器', url('mc/fans/initsync', array('page' => 1, 'acid' => $acid)), 'success');
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 50;
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid AND acid = :acid AND follow = '1'", array(':uniacid' => $_W['uniacid'], ':acid' => $acid));
	$total_page = ceil($total / $psize);
	$ds = pdo_fetchall("SELECT * FROM ".tablename('mc_mapping_fans') ." WHERE uniacid = :uniacid AND acid = :acid AND follow = '1' ORDER BY `fanid` DESC LIMIT ".($pindex - 1) * $psize.','.$psize, array(':uniacid' => $_W['uniacid'], ':acid' => $acid));

	$acc = WeAccount::create($acid);
	if(!empty($ds)) {
		foreach($ds as $row) {
			$fan = $acc->fansQueryInfo($row['openid'], true);
			if(!is_error($fan) && $fan['subscribe'] == 1) {
				$group = $acc->fetchFansGroupid($row['openid']);
				$record = array();
				if(!is_error($group)) {
					$record['groupid'] = $group['groupid'];
				}
				$record['updatetime'] = TIMESTAMP;
				$record['followtime'] = $fan['subscribe_time'];
				$fan['nickname'] = stripcslashes($fan['nickname']);
				$record['nickname'] = stripslashes($fan['nickname']);
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
	$pindex++;
	$log = ($pindex - 1) * $psize;
	if($pindex > $total_page) {
		message('粉丝数据更新完成', url('mc/fans'), 'success');
	} else {
		message('正在更新粉丝数据,请不要关闭浏览器,已完成更新 ' . $log . ' 条数据。', url('mc/fans/initsync', array('page' => $pindex, 'acid' => $acid)));
	}
}

if($do == 'updategroup') {
	if($_W['isajax']) {
		$groupid = intval($_GPC['groupid']);
		$openid = trim($_GPC['openid']);
		if(!empty($openid)) {
			$acc = WeAccount::create($_W['acid']);
			$data = $acc->updateFansGroupid($openid, $groupid);
			if(is_error($data)) {
				exit(json_encode(array('status' => 'error', 'mess' => $data['message'])));
			} else {
				pdo_update('mc_mapping_fans', array('groupid' => $groupid), array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid'], 'openid' => $openid));
				exit(json_encode(array('status' => 'success')));
			}
		} else {
			exit(json_encode(array('status' => 'error', 'mess' => '粉丝openid错误')));
		}
	}
}

template('mc/fans');