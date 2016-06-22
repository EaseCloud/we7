<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->func('communication');

class WeiXinAccount extends WeAccount {
	protected $account = null;
	
	
	public $apis = array();
	public $types = array(
		'view', 'click', 'scancode_push',
		'scancode_waitmsg', 'pic_sysphoto', 'pic_photo_or_album',
		'pic_weixin', 'location_select', 'media_id', 'view_limited'
	);
	
	public function __construct($account = array()) {
		if (empty($account)) {
			return true;
		}
		$this->account = $account;
		if(empty($this->account['acid'])) {
			trigger_error('error uniAccount id, can not construct ' . __CLASS__, E_USER_WARNING);
		}
		$this->apis = array(
			'barcode' => array(
				'post' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=%s',
				'display' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=%s',
			)
		);
	}
	
	
	public function checkSign() {
		$token = $this->account['token'];
		$signkey = array($token, $_GET['timestamp'], $_GET['nonce']);
		sort($signkey, SORT_STRING);
		$signString = implode($signkey);
		$signString = sha1($signString);
		return $signString == $_GET['signature'];
	}

	
	public function checkSignature($encrypt_msg) {
		$str = $this->buildSignature($encrypt_msg);
		return $str == $_GET['msg_signature'];
	}
	
	public function local_checkSignature($packet) {
		$token = $this->account['token'];
		$array = array($packet['Encrypt'], $token, $packet['TimeStamp'], $packet['Nonce']);
		sort($array, SORT_STRING);
		$str = implode($array);
		$str = sha1($str);
		return $str == $packet['MsgSignature'];
	}
	
	
	public function local_decryptMsg($postData) {
		$token = $this->account['token'];
		$encodingaeskey = $this->account['encodingaeskey'];
		$appid = $this->account['key'];
	
		if(strlen($encodingaeskey) != 43) {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40004 \n,错误描述为: " . $this->encrypt_error_code('40004'));
		}
		$key = base64_decode($encodingaeskey . '=');
				$packet = $this->local_xmlExtract($postData);
		if(is_error($packet)) {
			return error(-1, $packet['message']);
		}
				$istrue = $this->local_checkSignature($packet);
		if(!$istrue) {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40001 \n,错误描述为: " . $this->encrypt_error_code('40001'));
		}
				$ciphertext_dec = base64_decode($packet['Encrypt']);
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
		$xml_len = $len_list[1];
		$xml_content = substr($content, 4, $xml_len);
		$from_appid = substr($content, $xml_len + 4);
		if ($from_appid != $appid) {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40005 \n,错误描述为: " . $this->encrypt_error_code('40005'));
		}
		return $xml_content;
	}

	
	public function buildSignature($encrypt_msg) {
		$token = $this->account['token'];
		$array = array($encrypt_msg, $token, $_GET['timestamp'], $_GET['nonce']);
		sort($array, SORT_STRING);
		$str = implode($array);
		$str = sha1($str);
		return $str;
	}

	
	public function encryptMsg($text) {
		$token = $this->account['token'];
		$encodingaeskey = $this->account['encodingaeskey'];
		$appid = $this->account['key'];

		$key = base64_decode($encodingaeskey . '=');
		$text = random(16) . pack("N", strlen($text)) . $text . $appid;
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
				$signature = $this->buildSignature($encrypt_msg);
		return array($signature, $encrypt_msg);
	}
	
	
	public function decryptMsg($postData) {
		$token = $this->account['token'];
		$encodingaeskey = $this->account['encodingaeskey'];
		$appid = $this->account['key'];
		$key = base64_decode($encodingaeskey . '=');
	
		if(strlen($encodingaeskey) != 43) {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40004 \n,错误描述为: " . $this->encrypt_error_code('40004'));
		}
				$packet = $this->xmlExtract($postData);
		if(is_error($packet)) {
			return error(-1, $packet['message']);
		}
				$istrue = $this->checkSignature($packet['encrypt']);
		if(!$istrue) {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40001 \n,错误描述为: " . $this->encrypt_error_code('40001'));
		}
				$ciphertext_dec = base64_decode($packet['encrypt']);
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
		$xml_len = $len_list[1];
		$xml_content = substr($content, 4, $xml_len);
		$from_appid = substr($content, $xml_len + 4);
		if ($from_appid != $appid) {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40005 \n,错误描述为: " . $this->encrypt_error_code('40005'));
		}
		return $xml_content;
	}

	
	function xmlDetract($data) {
				$xml['Encrypt'] = $data[1];
		$xml['MsgSignature'] = $data[0];
		$xml['TimeStamp'] = $_GET['timestamp'];
		$xml['Nonce'] = $_GET['nonce'];
		return array2xml($xml);
	}
	
	
	public function xmlExtract($message) {
		$packet = array();
		if (!empty($message)){
			$obj = isimplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
			if($obj instanceof SimpleXMLElement) {
				$packet['encrypt'] = strval($obj->Encrypt);
				$packet['to'] = strval($obj->ToUserName);
			}
		}
		if(!empty($packet['encrypt'])) {
			return $packet;
		} else {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40002 \n,错误描述为: " . $this->encrypt_error_code('40002'));
		}
	}

	public function local_xmlExtract($message) {
		$packet = array();
		if (!empty($message)){
			$obj = isimplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
			if($obj instanceof SimpleXMLElement) {
				$packet['Encrypt'] = strval($obj->Encrypt);
				$packet['MsgSignature'] = strval($obj->MsgSignature);
				$packet['TimeStamp'] = strval($obj->TimeStamp);
				$packet['Nonce'] = strval($obj->Nonce);
			}
		}
		if(!empty($packet)) {
			return $packet;
		} else {
			return error(-1, "微信公众平台返回接口错误. \n错误代码为: 40002 \n,错误描述为: " . $this->encrypt_error_code('40002'));
		}
	}

	
	public function fetchAccountInfo() {
		return $this->account;
	}
	
	public function queryAvailableMessages() {
		$messages = array('text', 'image', 'voice', 'video', 'location', 'link', 'subscribe', 'unsubscribe');
		
		if(!empty($this->account['key']) && !empty($this->account['secret'])) {
			$level = intval($this->account['level']);
			if($level > 1){
				$messages[] = 'click';
				$messages[] = 'view';
			}
			if ($level > 2) {
				$messages[] = 'qr';
				$messages[] = 'trace';
			}
		}
		return $messages;
	}
	
	public function queryAvailablePackets() {
		$packets = array('text', 'music', 'news');
		if(!empty($this->account['key']) && !empty($this->account['secret'])) {
			if (intval($this->account['level']) > 1) {
				$packets[] = 'image';
				$packets[] = 'voice';
				$packets[] = 'video';
			}
		}
		return $packets;
	}

	
	public function isMenuSupported() {
		return 	!empty($this->account['key']) && 
				!empty($this->account['secret']) && 
				(intval($this->account['level']) > 1);
	}

	public function menuCreate($menu) {
		global $_W;
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}";
		if(!empty($menu['matchrule'])) {
			$url = "https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token={$token}";
		}
		$data = urldecode(json_encode($menu));
		$response = ihttp_post($url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result['menuid'];
	}
	
	public function menuDelete($menuid = 0) {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		if($menuid > 0) {
			$url = "https://api.weixin.qq.com/cgi-bin/menu/delconditional?access_token={$token}";
			$data = array(
				'menuid' => $menuid
			);
			$response = ihttp_post($url, json_encode($data));
		} else {
			$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$token}";
			$response = ihttp_get($url);
		}
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return true;
	}

	public function menuModify($menu) {
		return $this->menuCreate($menu);
	}
	
	public function menuQuery() {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$token}";
		$response = ihttp_get($url);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
				if(!empty($result['errcode']) && $result['errcode'] != '46003') {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	public function fansQueryInfo($uniid, $isOpen = true) {
		if($isOpen) {
			$openid = $uniid;
		} else {
			exit('error');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$token}&openid={$openid}&lang=zh_CN";
		$response = ihttp_get($url);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function fansBatchQueryInfo($data) {
		if(empty($data)) {
			return error(-1, '粉丝openid错误');
		}
		foreach($data as $da) {
			$post[] = array(
				'openid' => trim($da),
				'lang' => 'zh-CN'
			);
		}
		$data = array();
		$data['user_list'] = $post;
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token={$token}";
		$response = ihttp_post($url, json_encode($data));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result['user_info_list'];
	}

	public function fansAll() {
		global $_GPC;
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $token;
		if(!empty($_GPC['next_openid'])) {
			$url .= '&next_openid=' . $_GPC['next_openid'];
		}
		$response = ihttp_get($url);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		$return = array();
		$return['total'] = $result['total'];
		$return['fans'] = $result['data']['openid'];
		$return['next'] = $result['next_openid'];
		return $return;
	}

	public function queryBarCodeActions() {
		return array('barCodeCreateDisposable', 'barCodeCreateFixed');
	}

	public function barCodeCreateDisposable($barcode) {
		$barcode['expire_seconds'] = empty($barcode['expire_seconds']) ? 2592000 : $barcode['expire_seconds'];
		if (empty($barcode['action_info']['scene']['scene_id']) || empty($barcode['action_name'])) {
			return error('1', 'Invalid params');
		}
		$token = $this->getAccessToken();
		$response = ihttp_request("https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$token, json_encode($barcode));
		if (is_error($response)) {
			return $response;
		}
		$content = @json_decode($response['content'], true);
		
		if(empty($content)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		}
		if (!empty($content['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$content['errcode']}, 错误信息: {$content['errmsg']},错误详情：{$this->error_code($content['errcode'])}");
		}
		return $content;
	}
	
	public function barCodeCreateFixed($barcode) {
		if($barcode['action_name'] == 'QR_LIMIT_SCENE' && empty($barcode['action_info']['scene']['scene_id'])) {
			return error('1', '场景值错误');
		}
		if($barcode['action_name'] == 'QR_LIMIT_STR_SCENE' && empty($barcode['action_info']['scene']['scene_str'])) {
			return error('1', '场景字符串错误');
		}
		$token = $this->getAccessToken();
		$response = ihttp_request("https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$token, json_encode($barcode));
		if (is_error($response)) {
			return $response;
		}
		$content = @json_decode($response['content'], true);
		if(empty($content)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		}
		if(!empty($content['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$content['errcode']}, 错误信息: {$content['errmsg']},错误详情：{$this->error_code($content['errcode'])}");
		}
		return $content;
	}
	
		private function encrypt_error_code($code) {
		$errors = array(
			'40001' => '签名验证错误',
			'40002' => 'xml解析失败',
			'40003' => 'sha加密生成签名失败',
			'40004' => 'encodingAesKey 非法',
			'40005' => 'appid 校验错误',
			'40006' => 'aes 加密失败',
			'40007' => 'aes 解密失败',
			'40008' => '解密后得到的buffer非法',
			'40009' => 'base64加密失败',
			'40010' => 'base64解密失败',
			'40011' => '生成xml失败',
		);
		if($errors[$code]) {
			return $errors[$code];
		} else {
			return '未知错误';
		}
	}

	public function error_code($code, $errmsg = '未知错误') {
		$errors = array(
			'-1' => '系统繁忙',
			'0' => '请求成功',
			'40001' => '获取access_token时AppSecret错误，或者access_token无效',
			'40002' => '不合法的凭证类型',
			'40003' => '不合法的OpenID',
			'40004' => '不合法的媒体文件类型',
			'40005' => '不合法的文件类型',
			'40006' => '不合法的文件大小',
			'40007' => '不合法的媒体文件id',
			'40008' => '不合法的消息类型',
			'40009' => '不合法的图片文件大小',
			'40010' => '不合法的语音文件大小',
			'40011' => '不合法的视频文件大小',
			'40012' => '不合法的缩略图文件大小',
			'40013' => '不合法的APPID',
			'40014' => '不合法的access_token',
			'40015' => '不合法的菜单类型',
			'40016' => '不合法的按钮个数',
			'40017' => '不合法的按钮个数',
			'40018' => '不合法的按钮名字长度',
			'40019' => '不合法的按钮KEY长度',
			'40020' => '不合法的按钮URL长度',
			'40021' => '不合法的菜单版本号',
			'40022' => '不合法的子菜单级数',
			'40023' => '不合法的子菜单按钮个数',
			'40024' => '不合法的子菜单按钮类型',
			'40025' => '不合法的子菜单按钮名字长度',
			'40026' => '不合法的子菜单按钮KEY长度',
			'40027' => '不合法的子菜单按钮URL长度',
			'40028' => '不合法的自定义菜单使用用户',
			'40029' => '不合法的oauth_code',
			'40030' => '不合法的refresh_token',
			'40031' => '不合法的openid列表',
			'40032' => '不合法的openid列表长度',
			'40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
			'40035' => '不合法的参数',
			'40038' => '不合法的请求格式',
			'40039' => '不合法的URL长度',
			'40050' => '不合法的分组id',
			'40051' => '分组名字不合法',
			'41001' => '缺少access_token参数',
			'41002' => '缺少appid参数',
			'41003' => '缺少refresh_token参数',
			'41004' => '缺少secret参数',
			'41005' => '缺少多媒体文件数据',
			'41006' => '缺少media_id参数',
			'41007' => '缺少子菜单数据',
			'41008' => '缺少oauth code',
			'41009' => '缺少openid',
			'42001' => 'access_token超时',
			'42002' => 'refresh_token超时',
			'42003' => 'oauth_code超时',
			'43001' => '需要GET请求',
			'43002' => '需要POST请求',
			'43003' => '需要HTTPS请求',
			'43004' => '需要接收者关注',
			'43005' => '需要好友关系',
			'44001' => '多媒体文件为空',
			'44002' => 'POST的数据包为空',
			'44003' => '图文消息内容为空',
			'44004' => '文本消息内容为空',
			'45001' => '多媒体文件大小超过限制',
			'45002' => '消息内容超过限制',
			'45003' => '标题字段超过限制',
			'45004' => '描述字段超过限制',
			'45005' => '链接字段超过限制',
			'45006' => '图片链接字段超过限制',
			'45007' => '语音播放时间超过限制',
			'45008' => '图文消息超过限制',
			'45009' => '接口调用超过限制',
			'45010' => '创建菜单个数超过限制',
			'45015' => '回复时间超过限制',
			'45016' => '系统分组，不允许修改',
			'45017' => '分组名字过长',
			'45018' => '分组数量超过上限',
			'45056' => '创建的标签数过多，请注意不能超过100个',
			'45057' => '该标签下粉丝数超过10w，不允许直接删除',
			'45058' => '不能修改0/1/2这三个系统默认保留的标签',
			'45059' => '有粉丝身上的标签数已经超过限制',
			'45157' => '标签名非法，请注意不能和其他标签重名',
			'45158' => '标签名长度超过30个字节',
			'45159' => '非法的标签',
			'46001' => '不存在媒体数据',
			'46002' => '不存在的菜单版本',
			'46003' => '不存在的菜单数据',
			'46004' => '不存在的用户',
			'47001' => '解析JSON/XML内容错误',
			'48001' => 'api功能未授权',
			'50001' => '用户未授权该api',
			'40070' => '基本信息baseinfo中填写的库存信息SKU不合法。',
			'41011' => '必填字段不完整或不合法，参考相应接口。',
			'40056' => '无效code，请确认code长度在20个字符以内，且处于非异常状态（转赠、删除）。',
			'43009' => '无自定义SN权限，请参考开发者必读中的流程开通权限。',
			'43010' => '无储值权限,请参考开发者必读中的流程开通权限。',
			'43011' => '无积分权限,请参考开发者必读中的流程开通权限。',
			'40078' => '无效卡券，未通过审核，已被置为失效。',
			'40079' => '基本信息base_info中填写的date_info不合法或核销卡券未到生效时间。',
			'45021' => '文本字段超过长度限制，请参考相应字段说明。',
			'40080' => '卡券扩展信息cardext不合法。',
			'40097' => '基本信息base_info中填写的url_name_type或promotion_url_name_type不合法。',
			'49004' => '签名错误。',
			'43012' => '无自定义cell跳转外链权限，请参考开发者必读中的申请流程开通权限。',
			'40099' => '该code已被核销。',
			'61005' => '缺少接入平台关键数据，等待微信开放平台推送数据，请十分钟后再试或是检查“授权事件接收URL”是否写错（index.php?c=account&amp;a=auth&amp;do=ticket地址中的&amp;符号容易被替换成&amp;amp;）',
			'61023' => '请重新授权接入该公众号',
		);
		$code = strval($code);
		if($code == '40001' || $code == '42001') {
			$cachekey = "accesstoken:{$this->account['acid']}";
			cache_delete($cachekey);
			return '微信公众平台授权异常, 系统已修复这个错误, 请刷新页面重试.';
		}
		if($errors[$code]) {
			return $errors[$code];
		} else {
			return $errmsg;
		}
	}
	
	public function changeSend($send) {
		if (empty($send)) {
			return error(-1, 'Invalid params');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$sendapi = 'https://api.weixin.qq.com/pay/delivernotify?access_token='.$token;
		$response = ihttp_request($sendapi, json_encode($send));
		$response = json_decode($response['content'], true);
		if (empty($response)) {
			return error(-1, '发货失败，请检查您的公众号权限或是公众号AppId和公众号AppSecret！');
		}
		if (!empty($response['errcode'])) {
			return error(-1, $response['errmsg']);
		}
		return true;
	}
	
	public function getAccessToken() {
		load()->func('communication');
		$cachekey = "accesstoken:{$this->account['acid']}";
		$cache = cache_load($cachekey);
		if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > TIMESTAMP) {
			$this->account['access_token'] = $cache;
			return $cache['token'];
		}
		if (empty($this->account['key']) || empty($this->account['secret'])) {
			return error('-1', '未填写公众号的 appid 或 appsecret！');
		}
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->account['key']}&secret={$this->account['secret']}";
		$content = ihttp_get($url);
		if(is_error($content)) {
			message('获取微信公众号授权失败, 请稍后重试！错误详情: ' . $content['message']);
		}
		$token = @json_decode($content['content'], true);
		if(empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['expires_in'])) {
			$errorinfo = substr($content['meta'], strpos($content['meta'], '{'));
			$errorinfo = @json_decode($errorinfo, true);
			message('获取微信公众号授权失败, 请稍后重试！ 公众平台返回原始数据为: 错误代码-' . $errorinfo['errcode'] . '，错误信息-' . $errorinfo['errmsg']);
		}
		$record = array();
		$record['token'] = $token['access_token'];
		$record['expire'] = TIMESTAMP + $token['expires_in'] - 200;
		$this->account['access_token'] = $record;
		cache_write($cachekey, $record);
		return $record['token'];
	}
	
	public function getVailableAccessToken() {
		$accounts = pdo_fetchall("SELECT `key`, `secret`, `acid` FROM ".tablename('account_wechats')." WHERE uniacid = :uniacid ORDER BY `level` DESC ", array(':uniacid' => $GLOBALS['_W']['uniacid']));
		if (empty($accounts)) {
			return error(-1, 'no permission');
		}
		foreach ($accounts as $account) {
			if (empty($account['key']) || empty($account['secret'])) {
				continue;
			}
			$acid = $account['acid'];
			break;
		}
		$account = WeAccount::create($acid);
		return $account->getAccessToken();
	}

	public function fetch_token() {
		return $this->getAccessToken();
	}

	public function fetch_available_token() {
		return $this->getVailableAccessToken();
	}
	
	
	public function getJsApiTicket(){
		$cachekey = "jsticket:{$this->account['acid']}";
		$cache = cache_load($cachekey);
		if(!empty($cache) && !empty($cache['ticket']) && $cache['expire'] > TIMESTAMP) {
			return $cache['ticket'];
		}
		load()->func('communication');
		$access_token = $this->getAccessToken();
		if(is_error($access_token)){
			return $access_token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
		$content = ihttp_get($url);
		if(is_error($content)) {
			return error(-1, '调用接口获取微信公众号 jsapi_ticket 失败, 错误信息: ' . $content['message']);
		}
		$result = @json_decode($content['content'], true);
		if(empty($result) || intval(($result['errcode'])) != 0 || $result['errmsg'] != 'ok') {
			return error(-1, '获取微信公众号 jsapi_ticket 结果错误, 错误信息: ' . $result['errmsg']);
		}
		$record = array();
		$record['ticket'] = $result['ticket'];
		$record['expire'] = TIMESTAMP + $result['expires_in'] - 200;
		$this->account['jsapi_ticket'] = $record;
		cache_write($cachekey, $record);
		return $record['ticket'];
	}
	
	
	public function getJssdkConfig(){
		global $_W;
		$jsapiTicket = $this->getJsApiTicket();
		if(is_error($jsapiTicket)){
			$jsapiTicket = $jsapiTicket['message'];
		}
		$nonceStr = random(16);
		$timestamp = TIMESTAMP;
		$url = $_W['siteurl'];
		$string1 = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
		$signature = sha1($string1);
		$config = array(
			"appId"		=> $this->account['key'],
			"nonceStr"	=> $nonceStr,
			"timestamp" => "$timestamp",
			"signature" => $signature,
		);
		if(DEVELOPMENT) {
			$config['url'] = $url;
			$config['string1'] = $string1;
			$config['name'] = $this->account['name'];
		}
		return $config;
	}
	
	
	public function long2short($longurl) {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token={$token}";
		$send = array();
		$send['action'] = 'long2short';
		$send['long_url'] = $longurl;
		$response = ihttp_request($url, json_encode($send));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}
	
	
	public function downloadMedia($media) {
		$mediatypes = array('image', 'voice', 'thumb');
		if (empty($media) || empty($media['media_id']) || (!empty($media['type']) && !in_array($media['type'], $mediatypes))) {
			return error(-1, '微信下载媒体资源参数错误');
		}
		
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$sendapi = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$token}&media_id={$media['media_id']}";
		$response = ihttp_get($sendapi);
		if(!empty($response['headers']['Content-disposition']) && strexists($response['headers']['Content-disposition'], $media['media_id'])){
			global $_W;
			$filename =str_replace( array('attachment; filename=', '"',' '),'',$response['headers']['Content-disposition']);
			$filename = 'images/'.$_W['uniacid'].'/'.date('Y/m/').$filename;
			load()->func('file');
			file_write($filename, $response['content']);
			file_remote_upload($filename);
			return $filename;
		} else {
			$response = json_decode($response['content'], true);
			return error($response['errcode'], $response['errmsg']);
		}
	}

	
	public function fetchChatLog($params = array()) {
		if(empty($params['starttime']) || empty($params['endtime'])) {
			return error(-1, '没有要查询的时间段');
		}
		$starttmp = date('Y-m-d', $params['starttime']);
		$endtmp = date('Y-m-d', $params['endtime']);
		if($starttmp != $endtmp) {
			return error(-1, '时间范围有误，微信公众平台不支持跨日查询');
		}
		if(empty($params['openid'])) {
			return error(-1, '没有要查询的openid');
		}
		if(empty($params['pagesize'])) {
			$params['pagesize'] = 50;
		}
		if(empty($params['pageindex'])) {
			$params['pageindex'] = 1;
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/customservice/msgrecord/getrecord?access_token={$token}";
		$response = ihttp_request($url, json_encode($params));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function fetchFansGroups() {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/groups/get?access_token={$token}";
		$response = ihttp_request($url);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},信息详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function editFansGroupname($params = array()) {
		if(in_array($params['id'], array(0, 1, 2))) {
						return '';
		}
		if(empty($params['id']) || empty($params['name'])) {
			return error(-1, '分组信息错误');
		}

		$data = '{"group": {"id": ' . $params['id'] . ', "name": "' . $params['name'] . '"}}';
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/groups/update?access_token={$token}";
		$response = ihttp_request($url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function addFansGroup($name) {
		if(empty($name)) {
			return error(-1, '请填写分组名称');
		}
		$data = '{"group": {"name": "' . $name . '"}}';
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/groups/create?access_token={$token}";
		$response = ihttp_request($url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function delFansGroup($groupid) {
		$groupid = intval($groupid);
		if(empty($groupid)) {
			return error(-1, '分组id错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/groups/delete?access_token={$token}";
		$data = array(
			'group' => array('id' => $groupid)
		);
		$data = json_encode($data);
		$response = ihttp_request($url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return true;
	}

	
	public function fetchFansGroupid($openid) {
		if(empty($openid)) {
			return error(-1, '没有填写openid');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/groups/getid?access_token={$token}";
		$response = ihttp_request($url, json_encode(array('openid' => $openid)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function updateFansGroupid($openid, $groupid) {
		if(empty($openid)) {
			return error(-1, '没有填写openid');
		}
		$data = array('openid' => $openid, 'to_groupid' => intval($groupid));
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token={$token}";
		$response = ihttp_request($url, json_encode($data));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function sendCustomNotice($data) {
		if(empty($data)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$token}";
		$response = ihttp_request($url, urldecode(json_encode($data)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}
	
	public function uploadMedia($path, $type = 'image') {
		if(empty($path)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type={$type}";
		if(class_exists('CURLFile')) {
			$data = array(
				'media' => new CURLFile(ATTACHMENT_ROOT . ltrim($path, '/'))
			);
		} else {
			$data = array(
				'media' => '@' . ATTACHMENT_ROOT . ltrim($path, '/')
			);
		}
		$response = ihttp_request($url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function uploadVideo($data) {
		if(empty($data)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo?access_token={$token}";
		$response = ihttp_request($url, urldecode(json_encode($data)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']}, 错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function uploadNews($data) {
		if(empty($data)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token={$token}";
		$response = ihttp_request($url, urldecode(json_encode($data)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

		public function addMatrialNews($data) {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token={$token}";
		$response = ihttp_request($url, urldecode(json_encode($data)));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result['media_id'];
	}


	
	public function fansSendAll($group, $msgtype, $media_id) {
		$types = array('text' => 'text', 'image' => 'image', 'news' => 'mpnews', 'voice' => 'voice', 'video' => 'mpvideo', 'wxcard' => 'wxcard');
		if(empty($types[$msgtype])) {
			return error(-1, '消息类型不合法');
		}
		$is_to_all = false;
		if($group == - 1) {
			$is_to_all = true;
		}
		$data = array(
			'filter' => array(
				'is_to_all' => $is_to_all,
				'group_id' => $group
			),
			'msgtype' => $types[$msgtype],
			$types[$msgtype] => array(
				'media_id' => $media_id
			)
		);
		if($msgtype == 'wxcard') {
			unset($data['wxcard']['media_id']);
			$data['wxcard']['card_id'] = $media_id;
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token={$token}";
		$data = urldecode(json_encode($data));
		$response = ihttp_request($url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	
	public function changeOrderStatus($send) {
		if (empty($send)) {
			return error(-1, '参数错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$sendapi = 'https://api.weixin.qq.com/pay/delivernotify?access_token=' . $token;
		$response = ihttp_request($sendapi, json_encode($send));
		$response = json_decode($response['content'], true);
		if (empty($response)) {
			return error(-1, '发货失败，请检查您的公众号权限或是公众号AppId和公众号AppSecret！');
		}
		if (!empty($response['errcode'])) {
			return error(-1, $response['errmsg']);
		}
		return $response;
	}

	
	public function sendTplNotice($touser, $template_id, $postdata, $url = '', $topcolor = '#FF683F') {
		if(empty($this->account['key']) || $this->account['level'] != ACCOUNT_SERVICE_VERIFY) {
			return error(-1, '你的公众号没有发送模板消息的权限');
		}
		if(empty($touser)) {
			return error(-1, '参数错误,粉丝openid不能为空');
		}
		if(empty($template_id)) {
			return error(-1, '参数错误,模板标示不能为空');
		}
		if(empty($postdata) || !is_array($postdata)) {
			return error(-1, '参数错误,请根据模板规则完善消息内容');
		}
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}


		$data = array();
		$data['touser'] = $touser;
		$data['template_id'] = trim($template_id);
		$data['url'] = trim($url);
		$data['topcolor'] = trim($topcolor);
		$data['data'] = $postdata;
		$data = json_encode($data);
		$post_url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$token}";
		$response = ihttp_request($post_url, $data);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},信息详情：{$this->error_code($result['errcode'])}");
		}
		return true;
	}
	
	
	public function batchGetMaterial($type = 'news', $offset = 0, $count = 20) {
		global $_W;
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $token;
		$data = array(
			'type' => $type,
			'offset' => intval($offset),
			'count' => $count,
		);
		$response = ihttp_request($url, json_encode($data));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		if(!empty($response['headers']['Content-disposition'])){
			global $_W;
			$filename =str_replace(array('attachment; filename=', '"',' '),'',$response['headers']['Content-disposition']);
			load()->func('file');
			$filename = 'images/'.$_W['uniacid'].'/'.date('Y/m/').substr($filename,strripos($filename,'/')+1);
			file_write($filename, $response['content']);
			file_remote_upload($filename);
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		$return = array();
		$return['total_count'] = $result['total_count'];
		$return['item_count'] = $result['item_count'];
		$return['data'] = $result['item'];
		return $return;
	}

	
	public function getMaterial($media_id, $type = 'image') {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=' . $token;
		$data = array(
			'media_id' => trim($media_id),
		);
		$response = ihttp_request($url, json_encode($data));
		if(is_error($response)) {
			return error(-1, "访问公平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(!empty($result['errcode'])) {
			return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		if($type == 'image' || $type == 'voice') {
			$result = $response['content'];
		}
		return $result;
	}

	
	public function getMaterialCount() {
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=' . $token;
		$response = ihttp_request($url);
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	public function delMaterial($media_id) {
		$media_id = trim($media_id);
		if(empty($media_id)) {
			return error(-1, '素材media_id错误');
		}
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=' . $token;
		$data = array(
			'media_id' => trim($media_id),
		);
		$response = ihttp_request($url, json_encode($data));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}



	
	public function fansSendPreview($wxname, $content, $msgtype) {
		$types = array('text' => 'text', 'image' => 'image', 'news' => 'mpnews', 'voice' => 'voice', 'video' => 'mpvideo', 'wxcard' => 'wxcard');
		if(empty($types[$msgtype])) {
			return error(-1, '群发类型不合法');
		}
		$msgtype = $types[$msgtype];
		$token = $this->getAccessToken();
		if(is_error($token)){
			return $token;
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=' . $token;
		$send = array(
			'towxname' => $wxname,
			'msgtype' => $msgtype,
		);
		if($msgtype == 'text') {
			$send[$msgtype] = array(
				'content' => $content
			);
		} elseif($msgtype == 'wxcard') {
			$send[$msgtype] = array(
				'card_id' => $content
			);
		} else {
			$send[$msgtype] = array(
				'media_id' => $content
			);
		}

		$response = ihttp_request($url, json_encode($send));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result;
	}

	public function getOauthUserInfo($accesstoken, $openid) {
		$apiurl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken}&openid={$openid}&lang=zh_CN";
		$response = ihttp_get($apiurl);
		if (is_error($response)) {
			return $response;
		}
		return @json_decode($response['content'], true);
	}

	public function getOauthInfo($code = '') {
		global $_W, $_GPC;
		if (!empty($_GPC['code'])) {
			$code = $_GPC['code'];
		}
		if (empty($code)) {
			$unisetting = uni_setting_load();
			$url = (!empty($unisetting['oauth']['host']) ? ($unisetting['oauth']['host'] . $sitepath . '/') : $_W['siteroot'] . 'app/') . "index.php?{$_SERVER['QUERY_STRING']}";
			$forward = $this->getOauthCodeUrl(urlencode($url));
			header('Location: ' . $forward);
			exit;
		}
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->account['key']}&secret={$this->account['secret']}&code={$code}&grant_type=authorization_code";
		$response = ihttp_get($url);
		if (is_error($response)) {
			return $response;
		}
		return @json_decode($response['content'], true);
	}
	
	public function getOauthAccessToken() {
		$cachekey = "oauthaccesstoken:{$this->account['acid']}";
		$cache = cache_load($cachekey);
		if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > TIMESTAMP) {
			return $cache['token'];
		}
		$token = $this->getOauthInfo();
		if (is_error($token)) {
			return error(1);
		}
		$record = array();
		$record['token'] = $token['access_token'];
		$record['expire'] = TIMESTAMP + $token['expires_in'] - 200;
		cache_write($cachekey, $record);
		return $token['access_token'];
	}
	
	public function getShareAddressConfig() {
		global $_W;
		static $current_url;
		if (empty($current_url)) {
			$current_url = $_W['siteurl'];
		}
		$token = $this->getOauthAccessToken();
		if (is_error($token)) {
			return false;
		}
		$package = array(
			'appid' => $this->account['key'],
			'url' => $current_url,
			'timestamp' => strval(TIMESTAMP),
			'noncestr' => strval(random(8, true)),
			'accesstoken' => $token
		);
		ksort($package, SORT_STRING);
		$signstring = array();
		foreach ($package as $k => $v) {
			$signstring[] = "{$k}={$v}";
		}
		$signstring = strtolower(sha1(trim(implode('&', $signstring))));
		$shareaddress_config = array(
			'appId' => $this->account['key'],
			'scope' => 'jsapi_address',
			'signType' => 'sha1',
			'addrSign' => $signstring,
			'timeStamp' => $package['timestamp'],
			'nonceStr' => $package['noncestr']
		);
		return $shareaddress_config;
	}

	public function getOauthCodeUrl($callback, $state = '') {
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->account['key']}&redirect_uri={$callback}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
	}
	
	public function getOauthUserInfoUrl($callback, $state = '') {
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->account['key']}&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
	}
	
	public function getFansStat() {
		global $_W;
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/datacube/getusersummary?access_token={$token}";
		$response = ihttp_request($url, '{"begin_date": "'.date('Y-m-d', strtotime('-7 days')).'", "end_date": "'.date('Y-m-d', strtotime('-1 days')).'"}');
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$summary = @json_decode($response['content'], true);
		if(empty($summary)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif (!empty($summary['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$summary['errcode']}, 错误信息: {$summary['errmsg']},信息详情：{$this->error_code($summary['errcode'])}");
		}
		$url = "https://api.weixin.qq.com/datacube/getusercumulate?access_token={$token}";
		$response = ihttp_request($url, '{"begin_date": "'.date('Y-m-d', strtotime('-7 days')).'", "end_date": "'.date('Y-m-d', strtotime('-1 days')).'"}');
	
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$cumulate = @json_decode($response['content'], true);
		if(empty($cumulate)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($cumulate['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$cumulate['errcode']}, 错误信息: {$cumulate['errmsg']},信息详情：{$this->error_code($cumulate['errcode'])}");
		}
		$result = array();
		if (!empty($summary['list'])) {
			foreach ($summary['list'] as $row) {
				$key = str_replace('-', '', $row['ref_date']);
				$result[$key]['new'] = intval($result[$key]['new']) + $row['new_user'];
				$result[$key]['cancel'] = intval($result[$key]['cancel']) + $row['cancel_user'];
			}
		}
		if (!empty($cumulate['list'])) {
			foreach ($cumulate['list'] as $row) {
				$key = str_replace('-', '', $row['ref_date']);
				$result[$key]['cumulate'] = $row['cumulate_user'];
			}
		}
		return $result;
	}
}