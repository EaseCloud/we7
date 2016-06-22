<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
class CoreModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_W;
		if($this->message['event'] == 'subscribe' && !empty($this->message['ticket'])) {
			$sceneid = $this->message['scene'];
			$acid = $this->acid;
			$uniacid = $this->uniacid;
			$ticket = trim($this->message['ticket']);
			if(!empty($ticket)) {
				$qr = pdo_fetchall("SELECT `id`, `keyword`, `name`, `acid` FROM " . tablename('qrcode') . " WHERE `uniacid` = '{$uniacid}' AND ticket = '{$ticket}'");
				if(!empty($qr)) {
					if(count($qr) != 1) {
						$qr = array();
					} else {
						$qr = $qr[0];
					}
				}
			}
			if(empty($qr)) {
				$sceneid = trim($sceneid = $this->message['scene']);
				if(is_numeric($sceneid)) {
					$scene_condition = " `qrcid` = '{$sceneid}'";
				} else {
					$scene_condition = " `scene_str` = '{$sceneid}'";
				}
				$qr = pdo_fetch("SELECT `id`, `keyword`, `name`, `acid` FROM " . tablename('qrcode') . " WHERE `uniacid` = '{$uniacid}' AND {$scene_condition}");
			}
			$insert = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $qr['acid'],
				'qid' => $qr['id'],
				'openid' => $this->message['from'],
				'type' => 1,
				'qrcid' => intval($sceneid),
				'scene_str' => $sceneid,
				'name' => $qr['name'],
				'createtime' => TIMESTAMP,
			);
			pdo_insert('qrcode_stat', $insert);
		} elseif($this->message['event'] == 'SCAN') {
			$sceneid = $this->message['scene'];
			$acid = $this->acid;
			$uniacid = $this->uniacid;
			$sceneid = trim($sceneid = $this->message['scene']);
			if(is_numeric($sceneid)) {
				$scene_condition = " `qrcid` = '{$sceneid}'";
			} else {
				$scene_condition = " `scene_str` = '{$sceneid}'";
			}
			$row = pdo_fetch("SELECT `id`, `keyword`, `name`, `acid` FROM " . tablename('qrcode') . " WHERE `uniacid` = '{$uniacid}' AND {$scene_condition}");
			$insert = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $row['acid'],
				'qid' => $row['id'],
				'openid' => $this->message['from'],
				'type' => 2,
				'qrcid' => intval($sceneid),
				'scene_str' => $sceneid,
				'name' => $row['name'],
				'createtime' => TIMESTAMP,
			);
			pdo_insert('qrcode_stat', $insert);
		}
		if ($this->message['event'] == 'subscribe' && !empty($_W['account']) && ($_W['account']['level'] == ACCOUNT_SERVICE_VERIFY || $_W['account']['level'] == ACCOUNT_SUBSCRIPTION_VERIFY)) {
			$account_obj = WeAccount::create();
			$userinfo = $account_obj->fansQueryInfo($this->message['from']);
			if(!is_error($userinfo) && !empty($userinfo) && !empty($userinfo['subscribe'])) {
				$userinfo['nickname'] = stripcslashes($userinfo['nickname']);
				if (!empty($userinfo['headimgurl'])) {
					$userinfo['headimgurl'] = rtrim($userinfo['headimgurl'], '0') . 132;
				}
				$userinfo['avatar'] = $userinfo['headimgurl'];
				$fans = array(
					'unionid' => $userinfo['unionid'],
					'nickname' => $userinfo['nickname'],
					'tag' => base64_encode(iserializer($userinfo)),
				);
				pdo_update('mc_mapping_fans', $fans, array('openid' => $this->message['from']));
				
				if (!empty($_W['member']['uid'])) {
					$member = array();
					if (!empty($userinfo['nickname'])) {
						$member['nickname'] = $fans['nickname'];
					}
					if (!empty($userinfo['headimgurl'])) {
						$member['avatar'] = $userinfo['headimgurl'];
					}
					load()->model('mc');
					mc_update($_W['member']['uid'], $member);
				}
			}
		}
		$stat_setting = uni_setting($_W['uniacid'], 'stat');
		$stat_setting = $stat_setting['stat'];
		if(!is_array($stat_setting) || empty($stat_setting)) {
			$stat_setting = array();
			$stat_setting['msg_maxday'] = 0;
			$stat_setting['msg_history'] = 1;
			$stat_setting['use_ratio'] = 1;
		}

		if (!empty($stat_setting['msg_maxday']) && $stat_setting['msg_maxday'] > 0) {
			pdo_delete('stat_msg_history', " createtime < ".TIMESTAMP.' - '. $stat_setting['msg_maxday'] * 86400);
		}

		if ($stat_setting['msg_history']) {
			switch ($this->message['type']) {
				case 'text':
					$content = iserializer(array('content' => $this->message['content'], 'original' => $this->message['original'], 'redirection' => $this->message['redirection'], 'source' => $this->message['source']));
					break;
				case 'image':
					$content = $this->message['url'];
					break;
				case 'voice':
					$content = iserializer(array('media' => $this->message['media'], 'format' => $this->message['format']));
					break;
				case 'video':
					$content = iserializer(array('media' => $this->message['media'], 'thumb' => $this->message['thumb']));
					break;
				case 'location':
					$content = iserializer(array('x' => $this->message['location_x'], 'y' => $this->message['location_y']));
					break;
				case 'link':
					$content = iserializer(array('title' => $this->message['title'], 'description' => $this->message['description'], 'url' => $this->message['url']));
					break;
				case 'subscribe':
					$content = iserializer(array('scene' => $this->message['scene'], 'ticket' => $this->message['ticket']));
					break;
				case 'qr':
					$content = iserializer(array('scene' => $this->message['scene'], 'ticket' => $this->message['ticket']));
					break;
				case 'click':
					$content = $this->message['content'];
					break;
				case 'view':
					$content = $this->message['url'];
					break;
				case 'trace':
					$content = iserializer(array('location_x' => $this->message['location_x'], 'location_y' => $this->message['location_y'], 'precision' => $this->message['precision']));
					break;
				default:
					$content = $this->message['content'];
			}
				
			pdo_insert('stat_msg_history', array(
				'uniacid' => $_W['uniacid'],
				'module' => $this->params['module'],
				'from_user' => $this->message['from'],
				'rid' => intval($this->params['rule']),
				'kid' => $this->keyword['id'],
				'message' => $content,
				'type' => $this->message['type'],
				'createtime' => $this->message['time'],
			));
		}
		if (!empty($stat_setting['use_ratio'])) {
			if(!empty($this->params['rule'])) {
				$rule_stat_found = pdo_get('stat_rule', array('rid' => $this->params['rule'], 'createtime' => strtotime(date('Y-m-d'))));
				if (empty($rule_stat_found)) {
					pdo_insert('stat_rule', array(
						'uniacid' => $_W['uniacid'],
						'rid' => $this->params['rule'],
						'createtime' => strtotime(date('Y-m-d')),
						'hit' => 1,
						'lastupdate' => $this->message['time'],
					));
				} else {
					pdo_query("UPDATE ".tablename('stat_rule')." SET hit = hit + 1, lastupdate = '".TIMESTAMP."' WHERE rid = :rid AND createtime = :createtime", array(':rid' => $this->params['rule'], ':createtime' => strtotime(date('Y-m-d'))));
				}
			}
			if (!empty($this->keyword['id'])) {
				$keyword_stat_found = pdo_get('stat_keyword', array('rid' => $this->params['rule'], 'createtime' => strtotime(date('Y-m-d'))));
				if (empty($keyword_stat_found)) {
					pdo_insert('stat_keyword', array(
						'uniacid' => $_W['uniacid'],
						'rid' => $this->params['rule'],
						'kid' => $this->keyword['id'],
						'createtime' => strtotime(date('Y-m-d')),
						'hit' => 1,
						'lastupdate' => $this->message['time'],
					));
				} else {
					pdo_query("UPDATE ".tablename('stat_keyword')." SET hit = hit + 1, lastupdate = '".TIMESTAMP."' WHERE kid = :kid AND createtime = :createtime", array(':kid' => $this->keyword['id'], ':createtime' => strtotime(date('Y-m-d'))));
				}
			}
		}
	}
}
