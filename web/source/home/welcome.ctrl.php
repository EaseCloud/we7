<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('platform', 'site', 'mc', 'setting', 'ext', 'solution', 'replystatistics');
$do = in_array($do, $dos) ? $do : $do;
$title = array('platform'=>'公众平台','site'=>'微站功能','mc'=>'会员及会员营销','setting'=>'功能选项','ext'=>'扩展功能','solution'=>'行业功能');
$_W['page']['title'] = $title[$do];
define('FRAME', $do);
$frames = buildframes(array(FRAME), $_GPC['m']);
$frames = $frames[FRAME];
if (!empty($_W['setting']['permurls']['sections']) && !in_array($do, $_W['setting']['permurls']['sections'])) {
	header('Location: '.url('home/welcome/'.$_W['setting']['permurls']['sections'][0]));
	exit;
}

if($do != 'solution') {
	$modules = uni_modules();
	$settings = uni_setting($_W['uniacid'], array('shortcuts'));
	$shorts = $settings['shortcuts'];
	if(!is_array($shorts)) {
		$shorts = array();
	}
	$shortcuts = array();
	foreach($shorts as $i => $shortcut) {
		if (!empty($_W['setting']['permurls']['modules']) && !in_array($shortcut['name'], $_W['setting']['permurls']['modules'])) {
			continue;
		}
		$module = $modules[$shortcut['name']];
		if(!empty($module)) {
			$shortcut['title'] = $module['title'];
			if(file_exists('../addons/' . $module['name'] . '/icon-custom.jpg')) {
				$shortcut['image'] = '../addons/' . $module['name'] . '/icon-custom.jpg';
			} elseif(file_exists('../addons/' . $module['name'] . '/icon.jpg')) {
				$shortcut['image'] = '../addons/' . $module['name'] . '/icon.jpg';
			} else {
				$shortcut['image'] = '../web/resource/images/nopic-small.jpg';
			}
			$shortcut['link'] = wurl('home/welcome/ext', array('m'=>$shortcut['name']));
			$shortcuts[] = $shortcut;
		}
	}
	unset($shortcut);
}

if($do == 'platform') {
	uni_update_week_stat();
	$title = '平台相关数据';
	$yesterday = date('Ymd', strtotime('-1 days'));
	$yesterday_stat = pdo_get('stat_fans', array('date' => $yesterday, 'uniacid' => $_W['uniacid']));
	$today_stat = pdo_get('stat_fans', array('date' => date('Ymd'), 'uniacid' => $_W['uniacid']));
		$today_add_num = intval($today_stat['new']);
	$today_cancel_num = intval($today_stat['cancel']);
	$today_jing_num = $today_add_num - $today_cancel_num;
	$today_total_num = intval($today_jing_num) + intval($yesterday_stat['cumulate']);
	if($today_total_num < 0) {
		$today_total_num = 0;
	}	
		load()->model('reply');
	$cfg = $modules['userapi']['config'];
	$ds = reply_search("`uniacid` = 0 AND module = 'userapi' AND `status`=1");
	$apis = array();
	foreach($ds as $row) {
		$apis[$row['id']] = $row; 
	}
	
	$ds = array();
	foreach($apis as $row) {
		$reply = pdo_fetch('SELECT * FROM ' . tablename('userapi_reply') . ' WHERE `rid`=:rid', array(':rid' => $row['id']));
		$r = array();
		$r['title'] = $row['name'];
		$r['rid'] = $row['id'];
		$r['description'] = $reply['description'];
		$r['switch'] = $cfg[$r['rid']] ? true : false;
		$ds[] = $r;
	}
	$apis = $ds;
	
		$accounts = uni_accounts();
	$accounttypes = account_types();
		$mtypes = array();
	$mtypes['image'] = '图片消息';
	$mtypes['voice'] = '语音消息';
	$mtypes['video'] = '视频消息';
	$mtypes['location'] = '位置消息';
	$mtypes['link'] = '链接消息';
	$mtypes['subscribe'] = '粉丝开始关注';

	$setting = uni_setting($_W['uniacid'], array('default_message'));
	$ds = array();
	foreach($mtypes as $k => $v) {
		$row = array();
		$row['type'] = $k;
		$row['title'] = $v;
		$row['handles'] = array();
		foreach($modules as $m) {
			if(is_array($m['handles']) && in_array($k, $m['handles'])) {
				$row['handles'][] = array('name' => $m['name'], 'title' => $m['title']);
			}
		}
		$row['empty'] = empty($row['handles']);
		$row['current'] = is_array($setting['default_message']) ? $setting['default_message'][$k] : '';
		$ds[] = $row;
	}
		$qrs = pdo_fetchall("SELECT acid, COUNT(*) as num, model FROM ".tablename('qrcode')." WHERE uniacid=:uniacid GROUP BY acid, model", array(':uniacid'=>$_W['uniacid']));
	
	$tyqr = array('qr1num'=>0,'qr2num'=>0);
	foreach ($qrs as $qr) {
		$acid = intval($qr['acid']);
		if(intval($accounts[$acid]['level']) < 4){
			continue;
		}
		if(intval($qr['model']) == 1){
			$accounts[$acid]['qr1num'] = $qr['num'];
			$tyqr['qr1num'] += $qr['num'];
		} else {
			$accounts[$acid]['qr2num'] = $qr['num'];
			$tyqr['qr2num'] += $qr['num'];
		}
	}
}

if($do == 'site') {
	$title = '微站功能概况';
		$setting = uni_setting($_W['uniacid'], array('default_site'));
	$default_site = intval($setting['default_site']);
	$setting = pdo_fetch('SELECT styleid,id FROM ' . tablename('site_multi') . ' WHERE uniacid =:uniacid AND id = :id ', array(':uniacid' => $_W['uniacid'], ':id' => $setting['default_site']));
	$templates_id = pdo_fetchcolumn('SELECT templateid FROM ' . tablename('site_styles') . ' WHERE id = :id', array(':id' => $setting['styleid']));
	$template = pdo_fetch('SELECT * FROM ' . tablename('site_templates') . ' WHERE id = :id ', array(':id' => $templates_id));
	
		$sql = "SELECT rid FROM " . tablename('cover_reply') . ' WHERE `module` = :module AND uniacid = :uniacid AND multiid = :multiid';
	$pars = array();
	$pars[':module'] = 'site';
	$pars[':uniacid'] = $_W['uniacid'];
	$pars[':multiid'] = $setting['id'];
	$cover = pdo_fetch($sql, $pars);
	if(!empty($cover['rid'])) {
		$keywords = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = :rid", array(':rid' => $cover['rid']));
	}
	
		load()->model('app');
	$home_navs = app_navs('home', $setting['id']);
	$profile_navs = app_navs('profile');
		$slides = pdo_fetchall("SELECT * FROM ".tablename('site_slide')." WHERE uniacid = '{$_W['uniacid']}' AND multiid = {$default_site}  ORDER BY displayorder DESC, id DESC ");
	foreach ($slides as $key=>$value) {
		$slides[$key]['thumb'] = tomedia($value['thumb']);
	}
}

if($do == 'mc') {
	$title = '会员功能概况';
}

if($do == 'setting') {
	$title = '功能参数概况';
	
	
}

if($do == 'ext') {
	$title = '扩展功能概况';
	$installedmodulelist = uni_modules(false);
	foreach ($installedmodulelist as $k => &$value) {
		$value['official'] = empty($value['issystem']) && (strexists($value['author'], 'WeEngine Team') || strexists($value['author'], '微擎团队'));
	}
	$m = $_GPC['m'];
	if(empty($m)) {
		foreach($installedmodulelist as $name => $module) {
			if (!empty($_W['setting']['permurls']['modules']) && !in_array($name, $_W['setting']['permurls']['modules'])) {
				continue;
			}

			if($module['issystem']) {
				$path = '../framework/builtin/' . $module['name'];
			} else {
				$path = '../addons/' . $module['name'];
			}
			$cion = $path . '/icon-custom.jpg';
			if(!file_exists($cion)) {
				$cion = $path . '/icon.jpg';
				if(!file_exists($cion)) {
					$cion = './resource/images/nopic-small.jpg';
				}
			}
			$module['icon'] = $cion;

			if($module['enabled'] == 1) {
				$enable_modules[$name] = $module;
			} else {
				$unenable_modules[$name] = $module;
			}
		}
		$moudles = true;
	} else {
		$module = $installedmodulelist[$m];
		$title .= ' - ' . $module['title'];
		$entries = module_entries($m, array('menu', 'home', 'profile', 'shortcut', 'cover', 'mine'));
		$status = uni_user_permission_exist();
		if(is_error($status)) {
			$permission = uni_user_permission($m);
			if($permission[0] != 'all') {
				if(!in_array($m.'_rule', $permission)) {
					unset($module['isrulefields']);
				}
				if(!in_array($m.'_settings', $permission)) {
					unset($module['settings']);
				}
				if(!in_array($m.'_home', $permission)) {
					unset($entries['home']);
				}
				if(!in_array($m.'_profile', $permission)) {
					unset($entries['profile']);
				}
				if(!in_array($m.'_shortcut', $permission)) {
					unset($entries['shortcut']);
				}
				if(!empty($entries['cover'])) {
					foreach($entries['cover'] as $k => $row) {
						if(!in_array($m.'_cover_'.$row['do'], $permission)) {
							unset($entries['cover'][$k]);
						}
					}
				}
				if(!empty($entries['menu'])) {
					foreach($entries['menu'] as $k => $row) {
						if(!in_array($m.'_menu_'.$row['do'], $permission)) {
							unset($entries['menu'][$k]);
						}
					}
				}
			}
		} else {
			$site = WeUtility::createModule($m);
			if (!is_error($site)) {
				$method = 'welcomeDisplay';
				if(method_exists($site, $method)){
					$frames = array();
					$site->$method($entries);
					exit;
				}
			}
		}
	}
}

if(!in_array($do, $dos)) {
	$title = '';
}
if($do == 'replystatistics') {
	if($_W['ispost'] && $_W['isajax']) {
		$settings = uni_setting($_W['uniacid'], array('stat'));
		$day_num = @$settings['stat']['msg_maxday'] ? $settings['stat']['msg_maxday'] : 30;
		$m_name = trim($_GPC['m_name']);
		$starttime = strtotime("-{$day_num} day");
		$endtime = time();
		$data_hit = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE uniacid = :uniacid AND module = :module AND createtime >= :starttime AND createtime <= :endtime", array(':uniacid' => $_W['uniacid'], ':module' => $m_name, ':starttime' => $starttime, ':endtime' => $endtime));
		for($i = $day_num; $i >= 0; $i--){
			$key = date('m-d', strtotime('-' . $i . 'day'));
			$days[] = $key;
			$datasets[$key] = 0;
		}
		foreach($data_hit as $da) {
			$key1 = date('m-d', $da['createtime']);
			if(in_array($key1, $days)) {
					$datasets[$key1]++;
			}
		}
		$todaytimestamp = strtotime(date('Y-m-d'));
		$monthtimestamp = strtotime(date('Y-m'));
		$stat['month'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('stat_msg_history')." WHERE uniacid = :uniacid AND module = :module AND createtime >= '$monthtimestamp'", array(':uniacid' => $_W['uniacid'], ':module' => $m_name));
		$stat['today'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('stat_msg_history')." WHERE uniacid = :uniacid AND module = :module AND createtime >= '$todaytimestamp'", array(':uniacid' => $_W['uniacid'], ':module' => $m_name));
		$stat['rule'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('rule')." WHERE uniacid = :uniacid AND module = :module", array(':uniacid' => $_W['uniacid'], ':module' => $m_name));
		$stat['m_name'] = $m_name;
		exit(json_encode(array('key' => $days, 'value' => array_values($datasets), 'stat' => $stat)));
	}
}
template('home/welcome');
