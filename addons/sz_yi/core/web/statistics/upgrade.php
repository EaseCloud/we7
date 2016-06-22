<?php

if (!defined('IN_IA')) {
	die('Access Denied');
}
global $_W, $_GPC;
if (!$_W['isfounder']) {
	message('无权访问!');
}
$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
load()->func('communication');
load()->func('file');
if ($op == 'display') {
	$auth = $this->getAuthSet();
	$versionfile = IA_ROOT . '/addons/sz_yi/version.php';
	$updatedate = date('Y-m-d H:i', filemtime($versionfile));
	$version = SZ_YI_VERSION;
} else {
	if ($op == 'check') {
		set_time_limit(0);
		$auth = $this->getAuthSet();
		global $my_scenfiles;
		my_scandir(IA_ROOT . '/addons/sz_yi');
		$files = array();
		foreach ($my_scenfiles as $sf) {
			$files[] = array('path' => str_replace(IA_ROOT . '/addons/sz_yi/', "", $sf), 'md5' => md5_file($sf));
		}
		$files = base64_encode(json_encode($files));
		$version = defined('SZ_YI_VERSION') ? SZ_YI_VERSION : '1.0';
		$resp = ihttp_post(SZ_YI_AUTH_URL, array('type' => 'check', 'ip' => $auth['ip'], 'id' => $auth['id'], 'code' => $auth['code'], 'domain' => $auth['domain'], 'version' => $version, 'files' => $files));
		$ret = @json_decode($resp['content'], true);
		if (is_array($ret)) {
			if ($ret['result'] == 1) {
				$files = array();
				if (!empty($ret['files'])) {
					foreach ($ret['files'] as $file) {
						$entry = IA_ROOT . '/sz_yi/' . $file['path'];
						if (!is_file($entry) || md5_file($entry) != $file['md5']) {
							$files[] = array('path' => $file['path'], 'download' => 0);
						}
					}
				}
				$tmpdir = IA_ROOT . '/addons/sz_yi/' . date('ymd');
				if (!is_dir($tmpdir)) {
					mkdirs($tmpdir);
				}
				file_put_contents($tmpdir . '/file.txt', json_encode($ret));
				die(json_encode(array('result' => 1, 'version' => $ret['version'], 'filecount' => count($files), 'upgrade' => !empty($ret['upgrade']), 'log' => str_replace('
				', '<br/>', base64_decode($ret['log'])))));
			}
		}
		die(json_encode(array('result' => 0, 'message' => '服务器错误:' . $resp['content'] . '. ')));
	} else {
		if ($op == 'download') {
			$tmpdir = IA_ROOT . '/addons/sz_yi/' . date('ymd');
			$f = file_get_contents($tmpdir . '/file.txt');
			$upgrade = json_decode($f, true);
			$files = $upgrade['files'];
			$auth = $this->getAuthSet();
			$path = "";
			foreach ($files as $f) {
				if (empty($f['download'])) {
					$path = $f['path'];
					break;
				}
			}
			if (!empty($path)) {
				$resp = ihttp_post(SZ_YI_AUTH_URL, array('type' => 'download', 'ip' => $auth['ip'], 'id' => $auth['id'], 'code' => $auth['code'], 'domain' => $auth['domain'], 'path' => $path));
				$ret = @json_decode($resp['content'], true);
				if (is_array($ret)) {
					$path = $ret['path'];
					$dirpath = dirname($path);
					if (!is_dir(IA_ROOT . '/addons/sz_yi/' . $dirpath)) {
						mkdirs(IA_ROOT . '/addons/sz_yi/' . $dirpath, '0777');
					}
					$content = base64_decode($ret['content']);
					file_put_contents(IA_ROOT . '/addons/sz_yi/' . $path, $content);
					$success = 0;
					foreach ($files as &$f) {
						if ($f['path'] == $path) {
							$f['download'] = 1;
							break;
						}
						if ($f['download']) {
							$success++;
						}
					}
					unset($f);
					$upgrade['files'] = $files;
					$tmpdir = IA_ROOT . '/addons/sz_yi/' . date('ymd');
					if (!is_dir($tmpdir)) {
						mkdirs($tmpdir);
					}
					file_put_contents($tmpdir . '/file.txt', json_encode($upgrade));
					die(json_encode(array('result' => 1, 'total' => count($files), 'success' => $success . '(' . $path . ')')));
				}
			} else {
				if (!empty($upgrade['upgrade'])) {
					$updatefile = IA_ROOT . '/addons/sz_yi/upgrade.php';
					file_put_contents($updatefile, base64_decode($upgrade['upgrade']));
					require $updatefile;
					@unlink($updatefile);
				}
				file_put_contents(IA_ROOT . '/addons/sz_yi/version.php', '<?php if(!defined(\'IN_IA\')) {exit(\'Access Denied\');}if(!defined(\'SZ_YI_VERSION\')) {define(\'SZ_YI_VERSION\', \'' . $upgrade['version'] . '\');}');
				$tmpdir = IA_ROOT . '/addons/sz_yi/' . date('ymd');
				@rmdirs($tmpdir);
				die(json_encode(array('result' => 2)));
			}
		}
	}
}
include $this->template('web/sysset/upgrade');
