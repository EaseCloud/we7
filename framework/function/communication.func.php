<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function ihttp_request($url, $post = '', $extra = array(), $timeout = 60) {
	$urlset = parse_url($url);
	if (empty($urlset['path'])) {
		$urlset['path'] = '/';
	}
	if (!empty($urlset['query'])) {
		$urlset['query'] = "?{$urlset['query']}";
	}
	if (empty($urlset['port'])) {
		$urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
	}
	if (strexists($url, 'https://') && !extension_loaded('openssl')) {
		if (!extension_loaded("openssl")) {
			message('请开启您PHP环境的openssl');
		}
	}
	if (function_exists('curl_init') && function_exists('curl_exec')) {
		$ch = curl_init();
				if (ver_compare(phpversion(), '5.6') >= 0) {
			curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
		}
		if (!empty($extra['ip'])) {
			$extra['Host'] = $urlset['host'];
			$urlset['host'] = $extra['ip'];
			unset($extra['ip']);
		}
		curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		@curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		if ($post) {
			if (is_array($post)) {
				$filepost = false;
				foreach ($post as $name => $value) {
					if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof CURLFile)) {
						$filepost = true;
						break;
					}
				}
				if (!$filepost) {
					$post = http_build_query($post);
				}
			}
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if (!empty($GLOBALS['_W']['config']['setting']['proxy'])) {
			$urls = parse_url($GLOBALS['_W']['config']['setting']['proxy']['host']);
			if (!empty($urls['host'])) {
				curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
				$proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
				if (!empty($urls['scheme']) && defined($proxytype)) {
					curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
				} else {
					curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
					curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
				}
				if (!empty($GLOBALS['_W']['config']['setting']['proxy']['auth'])) {
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['_W']['config']['setting']['proxy']['auth']);
				}
			}
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION, 1);
		if (defined('CURL_SSLVERSION_TLSv1')) {
			curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
		}
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
		if (!empty($extra) && is_array($extra)) {
			$headers = array();
			foreach ($extra as $opt => $value) {
				if (strexists($opt, 'CURLOPT_')) {
					curl_setopt($ch, constant($opt), $value);
				} elseif (is_numeric($opt)) {
					curl_setopt($ch, $opt, $value);
				} else {
					$headers[] = "{$opt}: {$value}";
				}
			}
			if (!empty($headers)) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			}
		}
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if ($errno || empty($data)) {
			return error(1, $error);
		} else {
			return ihttp_response_parse($data);
		}
	}
	$method = empty($post) ? 'GET' : 'POST';
	$fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
	$fdata .= "Host: {$urlset['host']}\r\n";
	if (function_exists('gzdecode')) {
		$fdata .= "Accept-Encoding: gzip, deflate\r\n";
	}
	$fdata .= "Connection: close\r\n";
	if (!empty($extra) && is_array($extra)) {
		foreach ($extra as $opt => $value) {
			if (!strexists($opt, 'CURLOPT_')) {
				$fdata .= "{$opt}: {$value}\r\n";
			}
		}
	}
	$body = '';
	if ($post) {
		if (is_array($post)) {
			$body = http_build_query($post);
		} else {
			$body = urlencode($post);
		}
		$fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
	} else {
		$fdata .= "\r\n";
	}
	if ($urlset['scheme'] == 'https') {
		$fp = fsockopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
	} else {
		$fp = fsockopen($urlset['host'], $urlset['port'], $errno, $error);
	}
	stream_set_blocking($fp, true);
	stream_set_timeout($fp, $timeout);
	if (!$fp) {
		return error(1, $error);
	} else {
		fwrite($fp, $fdata);
		$content = '';
		while (!feof($fp))
			$content .= fgets($fp, 512);
		fclose($fp);
		return ihttp_response_parse($content, true);
	}
}


function ihttp_response_parse($data, $chunked = false) {
	$rlt = array();
	$headermeta = explode('HTTP/', $data);
	if (count($headermeta) > 2) {
		$data = 'HTTP/' . array_pop($headermeta);
	}
	$pos = strpos($data, "\r\n\r\n");
	$split1[0] = substr($data, 0, $pos);
	$split1[1] = substr($data, $pos + 4, strlen($data));
	
	$split2 = explode("\r\n", $split1[0], 2);
	preg_match('/^(\S+) (\S+) (\S+)$/', $split2[0], $matches);
	$rlt['code'] = $matches[2];
	$rlt['status'] = $matches[3];
	$rlt['responseline'] = $split2[0];
	$header = explode("\r\n", $split2[1]);
	$isgzip = false;
	$ischunk = false;
	foreach ($header as $v) {
		$pos = strpos($v, ':');
		$key = substr($v, 0, $pos);
		$value = trim(substr($v, $pos + 1));
		if (is_array($rlt['headers'][$key])) {
			$rlt['headers'][$key][] = $value;
		} elseif (!empty($rlt['headers'][$key])) {
			$temp = $rlt['headers'][$key];
			unset($rlt['headers'][$key]);
			$rlt['headers'][$key][] = $temp;
			$rlt['headers'][$key][] = $value;
		} else {
			$rlt['headers'][$key] = $value;
		}
		if(!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
			$isgzip = true;
		}
		if(!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
			$ischunk = true;
		}
	}
	if($chunked && $ischunk) {
		$rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
	} else {
		$rlt['content'] = $split1[1];
	}
	if($isgzip && function_exists('gzdecode')) {
		$rlt['content'] = gzdecode($rlt['content']);
	}

	$rlt['meta'] = $data;
	if($rlt['code'] == '100') {
		return ihttp_response_parse($rlt['content']);
	}
	return $rlt;
}

function ihttp_response_parse_unchunk($str = null) {
	if(!is_string($str) or strlen($str) < 1) {
		return false; 
	}
	$eol = "\r\n";
	$add = strlen($eol);
	$tmp = $str;
	$str = '';
	do {
		$tmp = ltrim($tmp);
		$pos = strpos($tmp, $eol);
		if($pos === false) {
			return false;
		}
		$len = hexdec(substr($tmp, 0, $pos));
		if(!is_numeric($len) or $len < 0) {
			return false;
		}
		$str .= substr($tmp, ($pos + $add), $len);
		$tmp  = substr($tmp, ($len + $pos + $add));
		$check = trim($tmp);
	} while(!empty($check));
	unset($tmp);
	return $str;
}


function ihttp_get($url) {
	return ihttp_request($url);
}


function ihttp_post($url, $data) {
	$headers = array('Content-Type' => 'application/x-www-form-urlencoded');
	return ihttp_request($url, $data, $headers);
}


function ihttp_email($to, $subject, $body, $global = false) {
	static $mailer;
	set_time_limit(0);

	if (empty($mailer)) {
		if (!class_exists('PHPMailer')) {
			require IA_ROOT . '/framework/library/phpmailer/PHPMailerAutoload.php';
		}
		$mailer = new PHPMailer();
		global $_W;
		$config = $GLOBALS['_W']['setting']['mail'];
		if (!$global) {
			$row = pdo_get("uni_settings", array('uniacid' => $_W['uniacid']), array('notify'));
			$row['notify'] = @iunserializer($row['notify']);
			if (!empty($row['notify']) && !empty($row['notify']['mail'])) {
				$config = $row['notify']['mail'];
			}
		}

		$config['charset'] = 'utf-8';
		if ($config['smtp']['type'] == '163') {
			$config['smtp']['server'] = 'smtp.163.com';
			$config['smtp']['port'] = 25;
		} elseif ($config['smtp']['type'] == 'qq') {
			$config['smtp']['server'] = 'ssl://smtp.qq.com';
			$config['smtp']['port'] = 465;
		} else {
			if (!empty($config['smtp']['authmode'])) {
				$config['smtp']['server'] = 'ssl://' . $config['smtp']['server'];
			}
		}

		if (!empty($config['smtp']['authmode'])) {
			if (!extension_loaded('openssl')) {
				return error(1, '请开启 php_openssl 扩展！');
			}
		}
		$mailer->signature = $config['signature'];
		$mailer->isSMTP();
		$mailer->CharSet = $config['charset'];
		$mailer->Host = $config['smtp']['server'];
		$mailer->Port = $config['smtp']['port'];
		$mailer->SMTPAuth = true;
		$mailer->Username = $config['username'];
		$mailer->Password = $config['password'];
		!empty($config['smtp']['authmode']) && $mailer->SMTPSecure = 'ssl';

		$mailer->From = $config['username'];
		$mailer->FromName = $config['sender'];
		$mailer->isHTML(true);
	}
	if (!empty($mailer->signature)) {
		$body .= htmlspecialchars_decode($mailer->signature);
	}
	$mailer->Subject = $subject;
	$mailer->Body = $body;
	$mailer->addAddress($to);
	if ($mailer->send()) {
		return true;
	} else {
		return error(1, $mailer->ErrorInfo);
	}
}
