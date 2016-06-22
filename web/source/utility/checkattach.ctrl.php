<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
set_time_limit(0);
if($do == 'ftp') {
	require(IA_ROOT . '/framework/library/ftp/ftp.php');
	$ftp_config = array(
		'hostname' => trim($_GPC['host']),
		'username' => trim($_GPC['username']),
		'password' => trim($_GPC['password']),
		'port' => intval($_GPC['port']),
		'ssl' => trim($_GPC['ssl']),
		'passive' => trim($_GPC['pasv']),
		'timeout' => intval($_GPC['overtime']),
		'rootdir' => trim($_GPC['dir']),
	);
	$url = trim($_GPC['url']);
	$filename = 'MicroEngine.ico';
	$ftp = new Ftp($ftp_config);
	if (true === $ftp->connect()) {
				if ($ftp->upload(ATTACHMENT_ROOT .'images/global/'. $filename, $filename)) {
			load()->func('communication');
			$response = ihttp_get($url. '/'. $filename);
			if (is_error($response)) {
				message(error(-1, '配置失败，FTP远程访问url错误'),'','ajax');
			}
			if (intval($response['code']) != 200) {
				message(error(-1, '配置失败，FTP远程访问url错误'),'','ajax');
			}
			$image = getimagesizefromstring($response['content']);
			if (!empty($image) && strexists($image['mime'], 'image')) {
				message(error(0,'配置成功'),'','ajax');
			} else {
				message(error(-1, '配置失败，FTP远程访问url错误'),'','ajax');
			}
		} else {
			message(error(-1, '配置失败，FTP远程访问url错误'),'','ajax');
		}
	} else {
		message(error(-1, 'FTP服务器连接失败，请检查配置'),'','ajax');
	}
}
if ($do == 'oss') {
	load()->model('attachment');
	$buckets = attachment_alioss_buctkets(trim($_GPC['key']), trim($_GPC['secret']));
	if (is_error($buckets)) {
		message(error(-1, 'OSS-Access Key ID 或 OSS-Access Key Secret错误，请重新填写'),'','ajax');
	}
	if (empty($_GPC['bucket'])) {
		$bucket = reset($buckets);
		$bucket = $bucket['name'];
	} else {
		if (strexists($_GPC['bucket'], '@@')) {
			list($bucket, $url) = explode('@@', $_GPC['bucket']);
		} else {
			$bucket = trim($_GPC['bucket']);
		}
		if (empty($buckets[$bucket])) {
			message(error(-1, '填写的bucket错误，请重新填写'),'','ajax');
		}
	}
	$ossurl = $buckets[$bucket]['location'].'.aliyuncs.com';
	if (!empty($_GPC['url'])) {
		if (!strexists($_GPC['url'], 'http://') && !strexists($_GPC['url'],'https://')) {
			$url = 'http://'. trim($_GPC['url']);
		} else {
			$url = trim($_GPC['url']);
		}
		$url = trim($url, '/').'/';
	} else {
		$url = 'http://'.$bucket.'.'.$buckets[$bucket]['location'].'.aliyuncs.com/';
	}
	$oss = new ALIOSS($_GPC['key'], $_GPC['secret'],$ossurl);
	$filename = 'MicroEngine.ico';
	$options = array(
		ALIOSS::OSS_FILE_UPLOAD => ATTACHMENT_ROOT . 'images/global/' . $filename,
		ALIOSS::OSS_PART_SIZE => 5242880,
	);
	$response = $oss->create_mpu_object($bucket, $filename, $options);
	if ($response->status == 200) {
		load()->func('communication');
		$response = ihttp_get($url.$filename);
		if (is_error($response)) {
			message(error(-1, '配置失败，阿里云访问url错误'),'','ajax');
		}
		if (intval($response['code']) != 200) {
			message(error(-1, '配置失败，阿里云访问url错误,请保证bucket为公共读取的'),'','ajax');
		}
		$image = getimagesizefromstring($response['content']);
		if (!empty($image) && strexists($image['mime'], 'image')) {
			message(error(0,'配置成功'),'','ajax');
		} else {
			message(error(-1, '配置失败，阿里云访问url错误'),'','ajax');
		}
	} else {
		message(error(-1, '配置失败，请检查bucket是否填写正确'),'','ajax');
	}
}
if ($do == 'qiniu') {
	load()->model('attachment');
	$auth= attachment_qiniu_auth(trim($_GPC['accesskey']), trim($_GPC['secretkey']), trim($_GPC['bucket']));
	if (is_error($auth)) {
		message(error(-1, '配置失败，请检查配置'), '', 'ajax');
	}
	load()->func('communication');
	$url = $_GPC['url'];
	$url = strexists($url, 'http') ? trim($url, '/') : 'http://'.trim($url, '/');
	$filename = 'MicroEngine.ico';
	$response = ihttp_get($url. '/'.$filename);
	if (is_error($response)) {
		message(error(-1, '配置失败，七牛访问url错误'),'','ajax');
	}
	if (intval($response['code']) != 200) {
		message(error(-1, '配置失败，七牛访问url错误,请保证bucket为公共读取的'),'','ajax');
	}
	$image = getimagesizefromstring($response['content']);
	if (!empty($image) && strexists($image['mime'], 'image')) {
		message(error(0,'配置成功'),'','ajax');
	} else {
		message(error(-1, '配置失败，七牛访问url错误'),'','ajax');
	}
}
