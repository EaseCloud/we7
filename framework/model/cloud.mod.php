<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

function cloud_client_define() {
	return array(
		'/framework/function/communication.func.php',
		'/framework/model/cloud.mod.php',
		'/web/source/cloud/upgrade.ctrl.php',
		'/web/source/cloud/process.ctrl.php',
		'/web/source/cloud/dock.ctrl.php',
		'/web/themes/default/cloud/upgrade.html',
		'/web/themes/default/cloud/process.html'
	);
}

function cloud_prepare() {
	global $_W;
	setting_load();
	if(empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
		return error('-1', "您的程序需要在微擎云服务平台注册你的站点资料, 来接入云平台服务后才能使用相应功能.");
	}
	return true;
}

function cloud_m_prepare($name) {
	$pars['method'] = 'module.check';
	$pars['module'] = $name;
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	if (is_error($dat)) {
		return $dat;
	}
	if ($dat['content'] == 'install-module-protect') {
		return error('-1', '此模块已设置版权保护，您只能通过云平台来安装。');
	}
	return true;
}

function _cloud_build_params() {
	global $_W;
	$pars = array();
	$pars['host'] = $_SERVER['HTTP_HOST'];
	$pars['family'] = IMS_FAMILY;
	$pars['version'] = IMS_VERSION;
	$pars['release'] = IMS_RELEASE_DATE;
	$pars['key'] = $_W['setting']['site']['key'];
	$pars['password'] = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
	$clients = cloud_client_define();
	$string = '';
	foreach($clients as $cli) {
		$string .= md5_file(IA_ROOT . $cli);
	}
	$pars['client'] = md5($string);
	return $pars;
}


function cloud_m_build($modulename, $type = '') {
	$type = in_array($type, array('uninstall')) ? $type : '';
	$sql = 'SELECT * FROM ' . tablename('modules') . ' WHERE `name`=:name';
	$module = pdo_fetch($sql, array(':name' => $modulename));
	$pars = _cloud_build_params();
	$pars['method'] = 'module.build';
	$pars['module'] = $modulename;
	$pars['type'] = $type;
	if (!empty($module)) {
		$pars['module_version'] = $module['version'];
	}

		$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/module.build';
	$ret = _cloud_shipping_parse($dat, $file);

	if (!is_error($ret)) {
		$dir = IA_ROOT . '/addons/' . $modulename;
		$files = array();
		if (!empty($ret['files'])) {
			foreach ($ret['files'] as $file) {
				$entry = $dir . $file['path'];
				if (!is_file($entry) || md5_file($entry) != $file['checksum']) {
					$files[] = '/' . $modulename . $file['path'];
				}
			}
		}
		$ret['files'] = $files;
		$schemas = array();
		if (!empty($ret['schemas'])) {
			load()->func('db');
			foreach ($ret['schemas'] as $remote) {
				$name = substr($remote['tablename'], 4);
				$local = db_table_schema(pdo(), $name);
				unset($remote['increment']);
				unset($local['increment']);
				if (empty($local)) {
					$schemas[] = $remote;
				} else {
					$diffs = db_table_fix_sql($local, $remote);
					if (!empty($diffs)) {
						$schemas[] = $remote;
					}
				}
			}
		}
		$ret['upgrade'] = true;
		$ret['type'] = 'module';
		$ret['schemas'] = $schemas;
				if (empty($module)) {
			$ret['install'] = 1;
		}
		cache_write('cloud:transtoken', authcode($ret['token'], 'ENCODE'));
	}
	return $ret;
}


function cloud_m_query() {
	$pars = _cloud_build_params();
	$pars['method'] = 'module.query';
	$pars['module'] = cloud_extra_module();
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/module.query';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_m_info($name) {
	$pars = _cloud_build_params();
	$pars['method'] = 'module.info';
	$pars['module'] = $name;
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/module.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}


function cloud_m_upgradeinfo($name) {
	$module = pdo_fetch("SELECT name, version FROM ".tablename('modules')." WHERE name = '{$name}'");
	$pars = _cloud_build_params();
	$pars['method'] = 'module.info';
	$pars['module'] = $name;
	$pars['curversion'] = $module['version'];
	$pars['isupgrade'] = 1;
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/module.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_t_prepare($name) {
	$pars['method'] = 'theme.check';
	$pars['theme'] = $name;
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	if (is_error($dat)) {
		return $dat;
	}
	if ($dat['content'] == 'install-theme-protect') {
		return error('-1', '此模板已设置版权保护，您只能通过云平台来安装。');
	}
	return true;
}


function cloud_t_query() {
	$pars = _cloud_build_params();
	$pars['method'] = 'theme.query';
	$pars['theme'] = cloud_extra_theme();
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/theme.query';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_t_info($name) {
	$pars = _cloud_build_params();
	$pars['method'] = 'theme.info';
	$pars['theme'] = $name;
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/theme.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_t_build($name) {
	$sql = 'SELECT * FROM ' . tablename('site_templates') . ' WHERE `name`=:name';
	$theme = pdo_fetch($sql, array(':name' => $name));
	
	$pars = _cloud_build_params();
	$pars['method'] = 'theme.build';
	$pars['theme'] = $name;
	if(!empty($theme)) {
		$pars['themeversion'] = $theme['version'];
	}
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/theme.build';
	$ret = _cloud_shipping_parse($dat, $file);
	if(!is_error($ret)) {
		$dir = IA_ROOT . '/app/themes/' . $name;
		$files = array();
		if(!empty($ret['files'])) {
			foreach($ret['files'] as $file) {
				$entry = $dir . $file['path'];
				if(!is_file($entry) || md5_file($entry) != $file['checksum']) {
					$files[] = '/'. $name . $file['path'];
				}
			}
		}
		$ret['files'] = $files;
		$ret['upgrade'] = true;
		$ret['type'] = 'theme';
				if(empty($theme)) {
			$ret['install'] = 1;
		}
		cache_write('cloud:transtoken', authcode($ret['token'], 'ENCODE'));
	}
	return $ret;
}


function cloud_t_upgradeinfo($name) {
	$sql = 'SELECT `name`, `version` FROM ' . tablename('site_templates') . ' WHERE `name` = :name';
	$theme = pdo_fetch($sql, array(':name' => $name));
	$pars = _cloud_build_params();
	$pars['method'] = 'theme.upgrade';
	$pars['theme'] = $theme['name'];
	$pars['version'] = $theme['version'];
	$pars['isupgrade'] = 1;
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/module.info';
	$ret = _cloud_shipping_parse($dat, $file);
	return $ret;
}

function cloud_sms_send($mobile, $content, $postdata = array()) {
	global $_W;
	
	if(!preg_match('/^1\d{10}$/', $mobile) || empty($content)) {
		return error(1, '发送短信失败, 原因: 手机号错误或内容为空.');
	}
	
	$row = pdo_get('uni_settings' , array('uniacid' => $_W['uniacid']), array('notify'));
	$row['notify'] = @iunserializer($row['notify']);

	$config = $row['notify']['sms'];
	$balance = intval($config['balance']);
	
	$sign = $config['signature'];
	if(empty($sign) && IMS_FAMILY == 'x') {
		$sign = $_W['setting']['copyright']['sitename'];
	}
	$pars = _cloud_build_params();
	$pars['method'] = 'sms.sendnew';
	$pars['mobile'] = $mobile;
	$pars['uniacid'] = $_W['uniacid'];
	$pars['balance'] = $balance;
	$pars['sign'] = $sign;
	if (!empty($postdata)) {
		$pars['content'] = $content;
		$pars['postdata'] = $postdata;
	} else {
		$pars['content'] = "{$content} 【{$sign}】";
	}
	
	$response = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	if (is_error($response)) {
		return error($response['errno'], '短信发送失败, 原因:'.$response['message']);
	}
	
	$result = json_decode($response['content'], true);
	
	if (is_error($result)) {
		return error($result['errno'], $result['message']);
	}
	if (intval($result['errno']) != -1) {
		$row['notify']['sms']['balance'] = $row['notify']['sms']['balance'] - 1;
		if ($row['notify']['sms']['balance'] < 0) {
			$row['notify']['sms']['balance'] = 0;
		}
		pdo_update('uni_settings', array('notify' => iserializer($row['notify'])), array('uniacid' => $_W['uniacid']));
		uni_setting_save('notify', $row['notify']);
	}
	return true;
}


function cloud_sms_info() {
	global $_W;
	
	$pars = _cloud_build_params();
	$pars['method'] = 'sms.info';
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php?', $pars);
	if ($dat['content'] == 'success') {
		$setting_key = "sms.info";
		$dat = setting_load($setting_key);
		return $dat[$setting_key];
	}
	
	return array();
}

function cloud_build() {
	$pars = _cloud_build_params();
	$pars['method'] = 'application.build2';
	$pars['extra'] = cloud_extra_account();
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/application.build';
	$ret = _cloud_shipping_parse($dat, $file);
	if(!is_error($ret)) {
		if($ret['state'] == 'warning') {
			$ret['files'] = cloud_client_define();
			unset($ret['schemas']);
			unset($ret['scripts']);
		} else {
			$files = array();
			if(!empty($ret['files'])) {
				foreach($ret['files'] as $file) {
					$entry = IA_ROOT . $file['path'];
					if(!is_file($entry) || md5_file($entry) != $file['checksum']) {
						$files[] = $file['path'];
					}
				}
			}
			$ret['files'] = $files;
			if (!empty($ret['files'])) {
				cloud_bakup_files($ret['files']);
			}
			$schemas = array();
			if(!empty($ret['schemas'])) {
				load()->func('db');
				foreach($ret['schemas'] as $remote) {
					$name = substr($remote['tablename'], 4);
					$local = db_table_schema(pdo(), $name);
					unset($remote['increment']);
					unset($local['increment']);
					if(empty($local)) {
						$schemas[] = $remote;
					} else {
						$sqls = db_table_fix_sql($local, $remote);
						if(!empty($sqls)) {
							$schemas[] = $remote;
						}
					}
				}
			}
			$ret['schemas'] = $schemas;
		}

		if($ret['family'] == 'x' && IMS_FAMILY == 'v') {
			load()->model('setting');
			setting_upgrade_version('x', IMS_VERSION, IMS_RELEASE_DATE);
			message('您已经购买了商业授权版本, 系统将转换为商业版, 并重新运行自动更新程序.', 'refresh');
		}
		$ret['upgrade'] = false;
		if(!empty($ret['files']) || !empty($ret['schemas']) || !empty($ret['scripts'])) {
			$ret['upgrade'] = true;
		}
		$upgrade = array();
		$upgrade['upgrade'] = $ret['upgrade'];
		$upgrade['lastupdate'] = TIMESTAMP;
		cache_write('upgrade', $upgrade);
		cache_write('cloud:transtoken', authcode($ret['token'], 'ENCODE'));
	}
	return $ret;
}

function cloud_schema() {
	$pars = _cloud_build_params();
	$pars['method'] = 'application.schema';
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	$file = IA_ROOT . '/data/application.schema';
	$ret = _cloud_shipping_parse($dat, $file);
	if(!is_error($ret)) {
		$schemas = array();
		if(!empty($ret['schemas'])) {
			load()->func('db');
			foreach($ret['schemas'] as $remote) {
				$name = substr($remote['tablename'], 4);
				$local = db_table_schema(pdo(), $name);
				unset($remote['increment']);
				unset($local['increment']);
				if(empty($local)) {
					$schemas[] = $remote;
				} else {
					$diffs = db_schema_compare($local, $remote);
					if(!empty($diffs)) {
						$schemas[] = $remote;
					}
				}
			}
		}
		$ret['schemas'] = $schemas;
	}
	return $ret;
}

function cloud_download($path, $type = '') {
	$pars = _cloud_build_params();
	$pars['method'] = 'application.shipping';
	$pars['path'] = $path;
	$pars['type'] = $type;
	$pars['gz'] = function_exists('gzcompress') && function_exists('gzuncompress') ? 'true' : 'false';
	$headers = array('content-type' => 'application/x-www-form-urlencoded');
	$dat = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars, $headers, 300);
	if(is_error($dat)) {
		return error(-1, '网络存在错误， 请稍后重试。' . $dat['message']);
	}
	if($dat['content'] == 'success') {
		return true;
	}
	$ret = @json_decode($dat['content'], true);
	if(is_error($ret)) {
		return $ret;
	} else {
		return error(-1, '不能下载文件， 请稍后重试。');
	}
}

function _cloud_shipping_parse($dat, $file) {
	if (is_error($dat)) {
		return error(-1, '网络传输错误, 请检查您的cURL是否可用, 或者服务器网络是否正常. ' . $dat['message']);
	}
	$tmp = unserialize($dat['content']);
	if (is_array($tmp) && is_error($tmp)) {
		if ($tmp['errno'] == '-2') {
			$data = file_get_contents(IA_ROOT . '/framework/version.inc.php');
			file_put_contents(IA_ROOT . '/framework/version.inc.php', str_replace("'x'", "'v'", $data));
		}
		return $tmp;
	}
	if ($dat['content'] == 'patching') {
		return error(-1, '补丁程序正在更新中，请稍后再试！');
	}
	if ($dat['content'] == 'blacklist') {
		return error(-1, '抱歉，您的站点已被列入云服务黑名单，云服务一切业务已被禁止，请联系微擎客服！');
	}
	if (strlen($dat['content']) != 32) {
		return error(-1, '云服务平台向您的服务器传输数据过程中出现错误, 这个错误可能是由于您的通信密钥和云服务不一致, 请尝试诊断云服务参数(重置站点ID和通信密钥). 传输原始数据:' . $dat['meta']);
	}
	$data = @file_get_contents($file);
	if (empty($data)) {
		return error(-1, '没有接收到服务器的传输的数据.');
	}
	@unlink($file);
	$ret = @iunserializer($data);
	if (empty($data) || empty($ret) || $dat['content'] != $ret['secret']) {
		return error(-1, '云服务平台向您的服务器传输的数据校验失败, 可能是因为您的网络不稳定, 或网络不安全, 请稍后重试.');
	}
	$ret = iunserializer($ret['data']);
	if (is_array($ret) && is_error($ret)) {
		if ($ret['errno'] == '-2') {
			$data = file_get_contents(IA_ROOT . '/framework/version.inc.php');
			file_put_contents(IA_ROOT . '/framework/version.inc.php', str_replace("'x'", "'v'", $data));
		}
	}
	if (!is_error($ret) && is_array($ret) && !empty($ret)) {
		if ($ret['state'] == 'fatal') {
			return error($ret['errorno'], '发生错误: ' . $ret['message']);
		}
		return $ret;
	} else {
		return error($ret['errno'], "发生错误: {$ret['message']}");
	}
}

function cloud_request($url, $post = '', $extra = array(), $timeout = 60) {
	global $_W;
	load()->func('communication');
	if (!empty($_W['setting']['cloudip']['ip']) && empty($extra['ip'])) {
		$extra['ip'] = $_W['setting']['cloudip']['ip'];
	}
	return ihttp_request($url, $post, $extra, $timeout);
}


function cloud_extra_account() {
	$data = array();
	$data['accounts'] = pdo_fetchall("SELECT name, account, original FROM ".tablename('account_wechats') . " GROUP BY account");
	return serialize($data);
}


function cloud_extra_module() {
	$sql = 'SELECT `name` FROM ' . tablename('modules') . ' WHERE `type` <> :type';
	$modules = pdo_fetchall($sql, array(':type' => 'system'), 'name');
	if (!empty($modules)) {
		return base64_encode(iserializer(array_keys($modules)));
	} else {
		return '';
	}
}


function cloud_extra_theme() {
	$sql = 'SELECT `name` FROM ' . tablename('site_templates') . ' WHERE `name` <> :name';
	$themes = pdo_fetchall($sql, array(':name' => 'default'), 'name');
	if (!empty($themes)) {
		return base64_encode(iserializer(array_keys($themes)));
	} else {
		return '';
	}
}


function cloud_cron_create($cron) {
	$pars = _cloud_build_params();
	$pars['method'] = 'cron.create';
	$pars['cron'] = base64_encode(iserializer($cron));
	$result = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	return _cloud_cron_parse($result);
}


function cloud_cron_update($cron) {
	$pars = _cloud_build_params();
	$pars['method'] = 'cron.update';
	$pars['cron'] = base64_encode(iserializer($cron));
	$result = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	return _cloud_cron_parse($result);
}


function cloud_cron_get($cron_id) {
	$pars = _cloud_build_params();
	$pars['method'] = 'cron.get';
	$pars['cron_id'] = $cron_id;
	$result = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	return _cloud_cron_parse($result);
}


function cloud_cron_change_status($cron_id, $status) {
	$pars = _cloud_build_params();
	$pars['method'] = 'cron.status';
	$pars['cron_id'] = $cron_id;
	$pars['status'] = $status;
	$result = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	return _cloud_cron_parse($result);
}


function cloud_cron_remove($cron_id) {
	$pars = _cloud_build_params();
	$pars['method'] = 'cron.remove';
	$pars['cron_id'] = $cron_id;
	$result = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
	return _cloud_cron_parse($result);
}


function _cloud_cron_parse($result) {
	if (empty($result)) {
		return error(-1, '没有接收到服务器的传输的数据');
	}
	if ($result['content'] == 'blacklist') {
		return error(-1, '抱歉，您的站点已被列入云服务黑名单，云服务一切业务已被禁止，请联系微擎客服！');
	}
	$result = json_decode($result['content'], true);
	if (null === $result) {
		return error(-1, '云服务通讯发生错误，请稍后重新尝试！');
	}
	$result = $result['message'];
	if (is_error($result)) {
		return error(-1, $result['message']);
	}
	return $result;
}


function cloud_auth_url($forward, $data = array()){
	global $_W;

	$auth = array();
	$auth['key'] = '';
	$auth['password'] = '';
	$auth['url'] = rtrim($_W['siteroot'], '/');
	$auth['referrer'] = intval($_W['config']['setting']['referrer']);
	$auth['version'] = IMS_VERSION;
	$auth['forward'] = $forward;

	if(!empty($_W['setting']['site']['key']) && !empty($_W['setting']['site']['token'])) {
		$auth['key'] = $_W['setting']['site']['key'];
		$auth['password'] = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
	}
	if ($data && is_array($data)) {
		$auth = array_merge($auth, $data);
	}
	$query = base64_encode(json_encode($auth));
	$auth_url = 'http://v2.addons.we7.cc/web/index.php?c=auth&a=passwort&__auth=' . $query;

	return $auth_url;
}


function cloud_module_setting_prepare($module, $binding) {
	global $_W;
	$auth = _cloud_build_params();
	$auth['arguments'] = array(
		'binding' => $binding,
		'acid' => $_W['uniacid'],
		'type' => 'module',
		'module' => $module,
	);
	$iframe_auth_url = cloud_auth_url('module', $auth);
	
	return $iframe_auth_url;
}


function cloud_resource_to_local($uniacid, $type, $url){
	global $_W;

	load()->func('file');

	$setting = $_W['setting']['upload'][$type];

	$pathinfo = pathinfo($url);
	$extension = !empty($pathinfo['extension']) ? $pathinfo['extension'] : 'jpg';
	$originname = $pathinfo['basename'];

	$setting['folder'] = "{$type}s/{$uniacid}/".date('Y/m/');

	$originname = pathinfo($url, PATHINFO_BASENAME);
	$filename = file_random_name(ATTACHMENT_ROOT .'/'. $setting['folder'], $extension);
	$pathname = $setting['folder'] . $filename;
	$fullname = ATTACHMENT_ROOT . $pathname;

	mkdirs(dirname($fullname));
	
	load()->func('communication');
	$response = ihttp_get($url);
	if (is_error($response)) {
		return error(1, $response['message']);
	}
	if (file_put_contents($fullname, $response['content']) == false) {
		return error(1, '提取文件失败');
	}

	if (!empty($_W['setting']['remote']['type'])) {
		$remotestatus = file_remote_upload($pathname);
		if (is_error($remotestatus)) {
			return error(1, '远程附件上传失败，请检查配置并重新上传');
		} else {
			file_delete($pathname);
		}
	}

	$data = array(
		'uniacid' => $uniacid,
		'uid' => intval($_W['uid']),
		'filename' => $originname,
		'attachment' => $pathname,
		'type' => $type == 'image' ? 1 : 2,
		'createtime' => TIMESTAMP,
	);
	pdo_insert('core_attachment', $data);

	$data['url'] = tomedia($pathname);
	$data['id'] = pdo_insertid();

	return $data;
}

function cloud_bakup_files($files) {
	global $_W;
	if (empty($files)) {
		return false;
	}
	$map = json_encode($files);
	$hash  = md5($map.$_W['config']['setting']['authkey']);
	if ($handle = opendir(IA_ROOT . '/data/patch/' . date('Ymd'))) {
		while (false !== ($patchpath = readdir($handle))) {
			if ($patchpath != '.' && $patchpath != '..') {
				if (strexists($patchpath, $hash)) {
					return false;
				}
			}
		}
	}
	
	$path = IA_ROOT . '/data/patch/' . date('Ymd') . '/' . date('Hi') . '_' . $hash;
	load()->func('file');
	if (!is_dir($path) && mkdirs($path)) {
		foreach ($files as $file) {
			mkdirs($path . '/' . dirname($file));
			file_put_contents($path . '/' . $file, file_get_contents(IA_ROOT . $file));
		}
		file_put_contents($path . '/' . 'map.json', $map);
	}
	return false;
}