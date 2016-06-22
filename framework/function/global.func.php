<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


function ver_compare($version1, $version2) {
	$version1 = str_replace('.', '', $version1);
	$version2 = str_replace('.', '', $version2);
	$oldLength = istrlen($version1);
	$newLength = istrlen($version2);
	if(is_numeric($version1) && is_numeric($version2)) {
		if ($oldLength > $newLength) {
			$version2 .= str_repeat('0', $oldLength - $newLength);
		}
		if ($newLength > $oldLength) {
			$version1 .= str_repeat('0', $newLength - $oldLength);
		}
		$version1 = intval($version1);
		$version2 = intval($version2);
	}
	return version_compare($version1, $version2);
}


function istripslashes($var) {
	if (is_array($var)) {
		foreach ($var as $key => $value) {
			$var[stripslashes($key)] = istripslashes($value);
		}
	} else {
		$var = stripslashes($var);
	}
	return $var;
}


function ihtmlspecialchars($var) {
	if (is_array($var)) {
		foreach ($var as $key => $value) {
			$var[htmlspecialchars($key)] = ihtmlspecialchars($value);
		}
	} else {
		$var = str_replace('&amp;', '&', htmlspecialchars($var, ENT_QUOTES));
	}
	return $var;
}


function isetcookie($key, $value, $expire = 0, $httponly = false) {
	global $_W;
	$expire = $expire != 0 ? (TIMESTAMP + $expire) : 0;
	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	return setcookie($_W['config']['cookie']['pre'] . $key, $value, $expire, $_W['config']['cookie']['path'], $_W['config']['cookie']['domain'], $secure, $httponly);
}


function getip() {
	static $ip = '';
	$ip = $_SERVER['REMOTE_ADDR'];
	if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
		$ip = $_SERVER['HTTP_CDN_SRC_IP'];
	} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
			if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
				$ip = $xip;
				break;
			}
		}
	}
	return $ip;
}


function token($specialadd = '') {
	global $_W;
	if(!defined('IN_MOBILE')) {
		return substr(md5($_W['config']['setting']['authkey'] . $specialadd), 8, 8);
	} else {
		if(!empty($_SESSION['token'])) {
			$count  = count($_SESSION['token']) - 5;
			asort($_SESSION['token']);
			foreach($_SESSION['token'] as $k => $v) {
				if(TIMESTAMP - $v > 300 || $count > 0) {
					unset($_SESSION['token'][$k]);
					$count--;
				}
			}
		}
		$key = substr(random(20), 0, 4);
		$_SESSION['token'][$key] = TIMESTAMP;
		return $key;
	}
}


function random($length, $numeric = FALSE) {
	$seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
	if ($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for ($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}


function checksubmit($var = 'submit', $allowget = false) {
	global $_W, $_GPC;
	if (empty($_GPC[$var])) {
		return FALSE;
	}
	if(defined('IN_SYS')) {
		if ($allowget || (($_W['ispost'] && !empty($_W['token']) && $_W['token'] == $_GPC['token']) && (empty($_SERVER['HTTP_REFERER']) || preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
			return TRUE;
		}
	} else {
		if(empty($_W['isajax']) && empty($_SESSION['token'][$_GPC['token']])) {
			message('抱歉，表单已经失效请您重新进入提交数据', referer(), 'error');
		} else {
			unset($_SESSION['token'][$_GPC['token']]);
		}
		return TRUE;
	}
	return FALSE;
}

function checkcaptcha($code) {
	global $_W, $_GPC;
	session_start();
	$codehash = md5(strtolower($code) . $_W['config']['setting']['authkey']);
	if (!empty($_GPC['__code']) && $codehash == $_SESSION['__code']) {
		$return = true;
	} else {
		$return = false;
	}
	$_SESSION['__code'] = '';
	isetcookie('__code', '');
	return $return;
}

function tablename($table) {
	if(empty($GLOBALS['_W']['config']['db']['master'])) {
		return "`{$GLOBALS['_W']['config']['db']['tablepre']}{$table}`";
	}
	return "`{$GLOBALS['_W']['config']['db']['master']['tablepre']}{$table}`";
}


function array_elements($keys, $src, $default = FALSE) {
	$return = array();
	if(!is_array($keys)) {
		$keys = array($keys);
	}
	foreach($keys as $key) {
		if(isset($src[$key])) {
			$return[$key] = $src[$key];
		} else {
			$return[$key] = $default;
		}
	}
	return $return;
}


function range_limit($num, $downline, $upline, $returnNear = true) {
	$num = intval($num);
	$downline = intval($downline);
	$upline = intval($upline);
	if($num < $downline){
		return empty($returnNear) ? false : $downline;
	} elseif ($num > $upline) {
		return empty($returnNear) ? false : $upline;
	} else {
		return empty($returnNear) ? true : $num;
	}
}


function ijson_encode($value) {
	if (empty($value)) {
		return false;
	}
	return addcslashes(json_encode($value), "\\\'\"");
}


function iserializer($value) {
	return serialize($value);
}


function iunserializer($value) {
	if (empty($value)) {
		return '';
	}
	if (!is_serialized($value)) {
		return $value;
	}
	$result = unserialize($value);
	if ($result === false) {
		$temp = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $value);
		return unserialize($temp);
	}
	return $result;
}


function is_base64($str){
	if(!is_string($str)){
		return false;
	}
	return $str == base64_encode(base64_decode($str));
}


function is_serialized($data, $strict = true) {
	if (!is_string($data)) {
		return false;
	}
	$data = trim($data);
	if ('N;' == $data) {
		return true;
	}
	if (strlen($data) < 4) {
		return false;
	}
	if (':' !== $data[1]) {
		return false;
	}
	if ($strict) {
		$lastc = substr($data, -1);
		if (';' !== $lastc && '}' !== $lastc) {
			return false;
		}
	} else {
		$semicolon = strpos($data, ';');
		$brace = strpos($data, '}');
				if (false === $semicolon && false === $brace)
			return false;
				if (false !== $semicolon && $semicolon < 3)
			return false;
		if (false !== $brace && $brace < 4)
			return false;
	}
	$token = $data[0];
	switch ($token) {
		case 's' :
			if ($strict) {
				if ('"' !== substr($data, -2, 1)) {
					return false;
				}
			} elseif (false === strpos($data, '"')) {
				return false;
			}
				case 'a' :
		case 'O' :
			return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
		case 'b' :
		case 'i' :
		case 'd' :
			$end = $strict ? '$' : '';
			return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
	}
	return false;
}


function wurl($segment, $params = array()) {
	list($controller, $action, $do) = explode('/', $segment);
	$url = './index.php?';
	if (!empty($controller)) {
		$url .= "c={$controller}&";
	}
	if (!empty($action)) {
		$url .= "a={$action}&";
	}
	if (!empty($do)) {
		$url .= "do={$do}&";
	}
	if (!empty($params)) {
		$queryString = http_build_query($params, '', '&');
		$url .= $queryString;
	}
	return $url;
}


function murl($segment, $params = array(), $noredirect = true, $addhost = false) {
	global $_W;
	list($controller, $action, $do) = explode('/', $segment);
	if (!empty($addhost)) {
		$url = $_W['siteroot'] . 'app/';
	} else {
		$url = './';
	}
	$str = '';
	if(uni_is_multi_acid()) {
		$str = "&j={$_W['acid']}";
	}
	$url .= "index.php?i={$_W['uniacid']}{$str}&";
	if (!empty($controller)) {
		$url .= "c={$controller}&";
	}
	if (!empty($action)) {
		$url .= "a={$action}&";
	}
	if (!empty($do)) {
		$url .= "do={$do}&";
	}
	if (!empty($params)) {
		$queryString = http_build_query($params, '', '&');
		$url .= $queryString;
		if ($noredirect === false) {
						$url .= '&wxref=mp.weixin.qq.com#wechat_redirect';
		}
	}
	return $url;
}


function pagination($total, $pageIndex, $pageSize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '')) {
	global $_W;
	$pdata = array(
		'tcount' => 0,
		'tpage' => 0,
		'cindex' => 0,
		'findex' => 0,
		'pindex' => 0,
		'nindex' => 0,
		'lindex' => 0,
		'options' => ''
	);
	if ($context['ajaxcallback']) {
		$context['isajax'] = true;
	}

	$pdata['tcount'] = $total;
	$pdata['tpage'] = ceil($total / $pageSize);
	if ($pdata['tpage'] <= 1) {
		return '';
	}
	$cindex = $pageIndex;
	$cindex = min($cindex, $pdata['tpage']);
	$cindex = max($cindex, 1);
	$pdata['cindex'] = $cindex;
	$pdata['findex'] = 1;
	$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
	$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
	$pdata['lindex'] = $pdata['tpage'];

	if ($context['isajax']) {
		if (!$url) {
			$url = $_W['script_name'] . '?' . http_build_query($_GET);
		}
		$pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['findex'] . '\', this);return false;"' : '');
		$pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['pindex'] . '\', this);return false;"' : '');
		$pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['nindex'] . '\', this);return false;"' : '');
		$pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $_W['script_name'] . $url . '\', \'' . $pdata['lindex'] . '\', this);return false;"' : '');
	} else {
		if ($url) {
			$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
			$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
			$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
			$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
		} else {
			$_GET['page'] = $pdata['findex'];
			$pdata['faa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['pindex'];
			$pdata['paa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['nindex'];
			$pdata['naa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['lindex'];
			$pdata['laa'] = 'href="' . $_W['script_name'] . '?' . http_build_query($_GET) . '"';
		}
	}

	$html = '<div><ul class="pagination pagination-centered">';
	if ($pdata['cindex'] > 1) {
		$html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
		$html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
	}
		if (!$context['before'] && $context['before'] != 0) {
		$context['before'] = 5;
	}
	if (!$context['after'] && $context['after'] != 0) {
		$context['after'] = 4;
	}

	if ($context['after'] != 0 && $context['before'] != 0) {
		$range = array();
		$range['start'] = max(1, $pdata['cindex'] - $context['before']);
		$range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
		if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
			$range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
			$range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
		}
		for ($i = $range['start']; $i <= $range['end']; $i++) {
			if ($context['isajax']) {
				$aa = 'href="javascript:;" page="' . $i . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $_W['script_name'] . $url . '\', \'' . $i . '\', this);return false;"' : '');
			} else {
				if ($url) {
					$aa = 'href="?' . str_replace('*', $i, $url) . '"';
				} else {
					$_GET['page'] = $i;
					$aa = 'href="?' . http_build_query($_GET) . '"';
				}
			}
			$html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
		}
	}

	if ($pdata['cindex'] < $pdata['tpage']) {
		$html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
		$html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
	}
	$html .= '</ul></div>';
	return $html;
}


function tomedia($src, $local_path = false){
	global $_W;
	if (empty($src)) {
		return '';
	}
	if (strexists($src, 'addons/')) {
		return $_W['siteroot'] . substr($src, strpos($src, 'addons/'));
	}
		if (strexists($src, $_W['siteroot']) && !strexists($src, '/addons/')) {
		$urls = parse_url($src);
		$src = $t = substr($urls['path'], strpos($urls['path'], 'images'));
	}
	$t = strtolower($src);
	if (strexists($t, 'https://mmbiz.qlogo.cn') || strexists($t, 'http://mmbiz.qpic.cn')) {
		return url('utility/wxcode/image', array('attach' => $src));
	}
	if (strexists($t, 'http://') || strexists($t, 'https://')) {
		return $src;
	}
	if ($local_path || empty($_W['setting']['remote']['type']) || file_exists(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/' . $src)) {
		$src = $_W['siteroot'] . $_W['config']['upload']['attachdir'] . '/' . $src;
	} else {
		$src = $_W['attachurl_remote'] . $src;
	}
	return $src;
}


function error($errno, $message = '') {
	return array(
		'errno' => $errno,
		'message' => $message,
	);
}


function is_error($data) {
	if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && $data['errno'] == 0)) {
		return false;
	} else {
		return true;
	}
}


function referer($default = '') {
	global $_GPC, $_W;
	$_W['referer'] = !empty($_GPC['referer']) ? $_GPC['referer'] : $_SERVER['HTTP_REFERER'];;
	$_W['referer'] = substr($_W['referer'], -1) == '?' ? substr($_W['referer'], 0, -1) : $_W['referer'];

	if (strpos($_W['referer'], 'member.php?act=login')) {
		$_W['referer'] = $default;
	}
	$_W['referer'] = $_W['referer'];
	$_W['referer'] = str_replace('&amp;', '&', $_W['referer']);
	$reurl = parse_url($_W['referer']);

	if (!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
		$_W['referer'] = $_W['siteroot'];
	} elseif (empty($reurl['host'])) {
		$_W['referer'] = $_W['siteroot'] . './' . $_W['referer'];
	}
	return strip_tags($_W['referer']);
}


function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}


function cutstr($string, $length, $havedot = false, $charset = '') {
	global $_W;
	if (empty($charset)) {
		$charset = $_W['charset'];
	}
	if (strtolower($charset) == 'gbk') {
		$charset = 'gbk';
	} else {
		$charset = 'utf8';
	}
	if (istrlen($string, $charset) <= $length) {
		return $string;
	}
	if (function_exists('mb_strcut')) {
		$string = mb_substr($string, 0, $length, $charset);
	} else {
		$pre = '{%';
		$end = '%}';
		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), $string);

		$strcut = '';
		$strlen = strlen($string);

		if ($charset == 'utf8') {
			$n = $tn = $noc = 0;
			while ($n < $strlen) {
				$t = ord($string[$n]);
				if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1;
					$n++;
					$noc++;
				} elseif (194 <= $t && $t <= 223) {
					$tn = 2;
					$n += 2;
					$noc++;
				} elseif (224 <= $t && $t <= 239) {
					$tn = 3;
					$n += 3;
					$noc++;
				} elseif (240 <= $t && $t <= 247) {
					$tn = 4;
					$n += 4;
					$noc++;
				} elseif (248 <= $t && $t <= 251) {
					$tn = 5;
					$n += 5;
					$noc++;
				} elseif ($t == 252 || $t == 253) {
					$tn = 6;
					$n += 6;
					$noc++;
				} else {
					$n++;
				}
				if ($noc >= $length) {
					break;
				}
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		} else {
			while ($n < $strlen) {
				$t = ord($string[$n]);
				if ($t > 127) {
					$tn = 2;
					$n += 2;
					$noc++;
				} else {
					$tn = 1;
					$n++;
					$noc++;
				}
				if ($noc >= $length) {
					break;
				}
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		}
		$string = str_replace(array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
	}

	if ($havedot) {
		$string = $string . "...";
	}

	return $string;
}


function istrlen($string, $charset = '') {
	global $_W;
	if (empty($charset)) {
		$charset = $_W['charset'];
	}
	if (strtolower($charset) == 'gbk') {
		$charset = 'gbk';
	} else {
		$charset = 'utf8';
	}
	if (function_exists('mb_strlen')) {
		return mb_strlen($string, $charset);
	} else {
		$n = $noc = 0;
		$strlen = strlen($string);

		if ($charset == 'utf8') {

			while ($n < $strlen) {
				$t = ord($string[$n]);
				if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$n++;
					$noc++;
				} elseif (194 <= $t && $t <= 223) {
					$n += 2;
					$noc++;
				} elseif (224 <= $t && $t <= 239) {
					$n += 3;
					$noc++;
				} elseif (240 <= $t && $t <= 247) {
					$n += 4;
					$noc++;
				} elseif (248 <= $t && $t <= 251) {
					$n += 5;
					$noc++;
				} elseif ($t == 252 || $t == 253) {
					$n += 6;
					$noc++;
				} else {
					$n++;
				}
			}

		} else {

			while ($n < $strlen) {
				$t = ord($string[$n]);
				if ($t > 127) {
					$n += 2;
					$noc++;
				} else {
					$n++;
					$noc++;
				}
			}

		}

		return $noc;
	}
}


function emotion($message = '', $size = '24px') {
	$emotions = array(
		"/::)","/::~","/::B","/::|","/:8-)","/::<","/::$","/::X","/::Z","/::'(",
		"/::-|","/::@","/::P","/::D","/::O","/::(","/::+","/:--b","/::Q","/::T",
		"/:,@P","/:,@-D","/::d","/:,@o","/::g","/:|-)","/::!","/::L","/::>","/::,@",
		"/:,@f","/::-S","/:?","/:,@x","/:,@@","/::8","/:,@!","/:!!!","/:xx","/:bye",
		"/:wipe","/:dig","/:handclap","/:&-(","/:B-)","/:<@","/:@>","/::-O","/:>-|",
		"/:P-(","/::'|","/:X-)","/::*","/:@x","/:8*","/:pd","/:<W>","/:beer","/:basketb",
		"/:oo","/:coffee","/:eat","/:pig","/:rose","/:fade","/:showlove","/:heart",
		"/:break","/:cake","/:li","/:bome","/:kn","/:footb","/:ladybug","/:shit","/:moon",
		"/:sun","/:gift","/:hug","/:strong","/:weak","/:share","/:v","/:@)","/:jj","/:@@",
		"/:bad","/:lvu","/:no","/:ok","/:love","/:<L>","/:jump","/:shake","/:<O>","/:circle",
		"/:kotow","/:turn","/:skip","/:oY","/:#-0","/:hiphot","/:kiss","/:<&","/:&>"
	);
	foreach ($emotions as $index => $emotion) {
		$message = str_replace($emotion, '<img style="width:'.$size.';vertical-align:middle;" src="http://res.mail.qq.com/zh_CN/images/mo/DEFAULT2/'.$index.'.gif" />', $message);
	}
	return $message;
}


function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : $GLOBALS['_W']['config']['setting']['authkey']);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya . md5($keya . $keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for ($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for ($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for ($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if ($operation == 'DECODE') {
		if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc . str_replace('=', '', base64_encode($result));
	}

}


function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}


function array2xml($arr, $level = 1) {
	$s = $level == 1 ? "<xml>" : '';
	foreach ($arr as $tagname => $value) {
		if (is_numeric($tagname)) {
			$tagname = $value['TagName'];
			unset($value['TagName']);
		}
		if (!is_array($value)) {
			$s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
		} else {
			$s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
		}
	}
	$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
	return $level == 1 ? $s . "</xml>" : $s;
}

function xml2array($xml) {
	if (empty($xml)) {
		return array();
	}
	$result = array();
	$xmlobj = isimplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if($xmlobj instanceof SimpleXMLElement) {
		$result = json_decode(json_encode($xmlobj), true);
		if (is_array($result)) {
			return $result;
		} else {
			return array();
		}
	} else {
		return $result;
	}
}

function scriptname() {
	global $_W;
	$_W['script_name'] = basename($_SERVER['SCRIPT_FILENAME']);
	if(basename($_SERVER['SCRIPT_NAME']) === $_W['script_name']) {
		$_W['script_name'] = $_SERVER['SCRIPT_NAME'];
	} else {
		if(basename($_SERVER['PHP_SELF']) === $_W['script_name']) {
			$_W['script_name'] = $_SERVER['PHP_SELF'];
		} else {
			if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $_W['script_name']) {
				$_W['script_name'] = $_SERVER['ORIG_SCRIPT_NAME'];
			} else {
				if(($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
					$_W['script_name'] = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $_W['script_name'];
				} else {
					if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
						$_W['script_name'] = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
					} else {
						$_W['script_name'] = 'unknown';
					}
				}
			}
		}
	}
	return $_W['script_name'];
}


function utf8_bytes($cp) {
	if ($cp > 0x10000){
				return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
		chr(0x80 | (($cp & 0x3F000) >> 12)).
		chr(0x80 | (($cp & 0xFC0) >> 6)).
		chr(0x80 | ($cp & 0x3F));
	}else if ($cp > 0x800){
				return	chr(0xE0 | (($cp & 0xF000) >> 12)).
		chr(0x80 | (($cp & 0xFC0) >> 6)).
		chr(0x80 | ($cp & 0x3F));
	}else if ($cp > 0x80){
				return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
		chr(0x80 | ($cp & 0x3F));
	}else{
				return chr($cp);
	}
}

function media2local($media_id, $all = false){
	global $_W;
	if (empty($media_id)) {
		return '';
	}
	$data = pdo_fetch('SELECT * FROM ' . tablename('wechat_attachment') . ' WHERE uniacid = :uniacid AND media_id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $media_id));
	if (!empty($data)) {
		$data['attachment'] = tomedia($data['attachment'], true);
		if (!$all) {
			return $data['attachment'];
		}
		return $data;
	}
	return '';
}

function aes_decode($message, $encodingaeskey = '', $appid = '') {
	$key = base64_decode($encodingaeskey . '=');

	$ciphertext_dec = base64_decode($message);
	$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
	$iv = substr($key, 0, 16);

	mcrypt_generic_init($module, $key, $iv);
	$decrypted = mdecrypt_generic($module, $ciphertext_dec);
	mcrypt_generic_deinit($module);
	mcrypt_module_close($module);
	$block_size = 32;

	$pad = ord(substr($decrypted, -1));
	if ($pad < 1 || $pad > 32) {
		$pad = 0;
	}
	$result = substr($decrypted, 0, (strlen($decrypted) - $pad));
	if (strlen($result) < 16) {
		return '';
	}
	$content = substr($result, 16, strlen($result));
	$len_list = unpack("N", substr($content, 0, 4));
	$contentlen = $len_list[1];
	$content = substr($content, 4, $contentlen);
	$from_appid = substr($content, $xml_len + 4);
	if (!empty($appid) && $appid != $from_appid) {
		return '';
	}
	return $content;
}

function aes_encode($message, $encodingaeskey = '', $appid = '') {
	$key = base64_decode($encodingaeskey . '=');
	$text = random(16) . pack("N", strlen($message)) . $message . $appid;

	$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
	$iv = substr($key, 0, 16);

	$block_size = 32;
	$text_length = strlen($text);
		$amount_to_pad = $block_size - ($text_length % $block_size);
	if ($amount_to_pad == 0) {
		$amount_to_pad = $block_size;
	}
		$pad_chr = chr($amount_to_pad);
	$tmp = '';
	for ($index = 0; $index < $amount_to_pad; $index++) {
		$tmp .= $pad_chr;
	}
	$text = $text . $tmp;
	mcrypt_generic_init($module, $key, $iv);
		$encrypted = mcrypt_generic($module, $text);
	mcrypt_generic_deinit($module);
	mcrypt_module_close($module);
		$encrypt_msg = base64_encode($encrypted);
	return $encrypt_msg;
}


function isimplexml_load_string($string, $class_name = 'SimpleXMLElement', $options = 0, $ns = '', $is_prefix = false) {
	libxml_disable_entity_loader(true);
	if (preg_match('/(\<\!DOCTYPE|\<\!ENTITY)/i', $string)) {
		return false;
	}
	return simplexml_load_string($string, $class_name, $options, $ns, $is_prefix);
}

function ihtml_entity_decode($str) {
	$str = str_replace('&nbsp;', '#nbsp;', $str);
	return str_replace('#nbsp;', '&nbsp;', html_entity_decode(urldecode($str)));
}

function iarray_change_key_case($array, $case = CASE_LOWER){
	if (!is_array($array) || empty($array)){
		return array();
	}
	$array = array_change_key_case($array, $case);
	foreach ($array as $key => $value){
		if (is_array($value)) {
			$array[$key] = iarray_change_key_case($value, $case);
		}
	}
	return $array;
}

function strip_gpc($values, $type = 'g') {
	$filter = array(
		'g' => "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
		'p' => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
		'c' => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
	);
	if (!isset($values)) {
		return '';
	}
	if(is_array($values)) {
		foreach($values as $key => $val) {
			$values[addslashes($key)] = strip_gpc($val, $type);
		}
	} else {
		if (preg_match("/".$filter[$type]."/is", $values, $match) == 1) {
			$values = '';
		}
	}
	return $values;
}