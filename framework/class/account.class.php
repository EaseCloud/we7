<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');


abstract class WeAccount {
	
	const TYPE_WEIXIN = '1';
	const TYPE_YIXIN = '2';
	const TYPE_WEIXIN_PLATFORM = '3';
	
	
	public static function create($acidOrAccount = '') {
		global $_W;
		if(empty($acidOrAccount)) {
			$acidOrAccount = $_W['account'];
		}
		if (is_array($acidOrAccount)) {
			$account = $acidOrAccount;
		} else {
			$account = account_fetch($acidOrAccount);
		}
		if (is_error($account)) {
			$account = $_W['account'];
		}
		if(!empty($account) && isset($account['type'])) {
			if($account['type'] == self::TYPE_WEIXIN) {
				load()->classs('weixin.account');
				return new WeiXinAccount($account);
			}
			if($account['type'] == self::TYPE_YIXIN) {
				load()->classs('yixin.account');
				return new YiXinAccount($account);
			}
			if($account['type'] == self::TYPE_WEIXIN_PLATFORM) {
				load()->classs('weixin.platform');
				return new WeiXinPlatform($account);
			}
		}
		return null;
	}
	
	static public function token($type = 1) {
		$classname = self::includes($type);
		$obj = new $classname();
		return $obj->fetch_available_token();
	}
	
	static public function includes($type = 1) {
		if($type == '1') {
			load()->classs('weixin.account');
			return 'WeiXinAccount';
		}
		if($type == '2') {
			load()->classs('yixin.account');
			return 'YiXinAccount';
		}
	}
	
	
	abstract public function __construct($account);

	
	public function checkSign() {
		trigger_error('not supported.', E_USER_WARNING);
	}

	
	public function fetchAccountInfo() {
		trigger_error('not supported.', E_USER_WARNING);
	}

	
	public function queryAvailableMessages() {
		return array();
	}
	
	
	public function queryAvailablePackets() {
		return array();
	}

	
	public function parse($message) {
		global $_W;
		if (!empty($message)){
			$message = xml2array($message);
			$packet = iarray_change_key_case($message, CASE_LOWER);
			$packet['from'] = $message['FromUserName'];
			$packet['to'] = $message['ToUserName'];
			$packet['time'] = $message['CreateTime'];
			$packet['type'] = $message['MsgType'];
			$packet['event'] = $message['Event'];
			switch ($packet['type']) {
				case 'text':
					$packet['redirection'] = false;
					$packet['source'] = null;
					break;
				case 'image':
					$packet['url'] = $message['PicUrl'];
					break;
				case 'video':
				case 'shortvideo':
					$packet['thumb'] = $message['ThumbMediaId'];
					break;
			}
	
			switch ($packet['event']) {
				case 'SCAN':
					$packet['type'] = 'qr';
				case 'subscribe':
					$packet['type'] = 'subscribe';
				case 'SCAN':
					if(!empty($packet['eventkey'])) {
						$packet['scene'] = str_replace('qrscene_', '', $packet['eventkey']);
						if(strexists($packet['scene'], '\u')) {
							$packet['scene'] = '"' . str_replace('\\u', '\u', $packet['scene']) . '"';
							$packet['scene'] = json_decode($packet['scene']);
						}
	
					}
					break;
				case 'unsubscribe':
					$packet['type'] = 'unsubscribe';
					break;
				case 'LOCATION':
					$packet['type'] = 'trace';
					$packet['location_x'] = $message['Latitude'];
					$packet['location_y'] = $message['Longitude'];
					break;
				case 'pic_photo_or_album':
				case 'pic_weixin':
				case 'pic_sysphoto':
					$packet['sendpicsinfo']['count'] = $message['SendPicsInfo']['Count'];
					if (!empty($message['SendPicsInfo']['PicList'])) {
						foreach ($message['SendPicsInfo']['PicList']['item'] as $item) {
							if (!empty($item)) {
								$packet['sendpicsinfo']['piclist'][] = $item['PicMd5Sum'];
							}
						}
					}
					break;
				case 'card_pass_check':
				case 'card_not_pass_check':
				case 'user_get_card':
				case 'user_del_card':
				case 'user_consume_card':
				case 'poi_check_notify':
					$packet['type'] = 'coupon';
					break;
			}
		}
		return $packet;
	}
	
	
	public function response($packet) {
		if (is_error($packet)) {
			return '';
		}
		if (!is_array($packet)) {
			return $packet;
		}
		if(empty($packet['CreateTime'])) {
			$packet['CreateTime'] = TIMESTAMP;
		}
		if(empty($packet['MsgType'])) {
			$packet['MsgType'] = 'text';
		}
		if(empty($packet['FuncFlag'])) {
			$packet['FuncFlag'] = 0;
		} else {
			$packet['FuncFlag'] = 1;
		}
		return array2xml($packet);
	}

	
	public function isPushSupported() {
		return false;
	}
	
	
	public function push($uniid, $packet) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function isBroadcastSupported() {
		return false;
	}
	
	
	public function broadcast($packet, $targets = array()) {
		trigger_error('not supported.', E_USER_WARNING);
	}

	
	public function isMenuSupported() {
		return false;
	}
	
	
	public function menuCreate($menu) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function menuDelete() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function menuModify($menu) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function menuQuery() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function queryFansActions() {
		return array();
	}
	
	
	public function fansGroupAll() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function fansGroupCreate($group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function fansGroupModify($group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function fansMoveGroup($uniid, $group) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function fansQueryGroup($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function fansQueryInfo($uniid, $isPlatform) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function fansAll() {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function queryTraceActions() {
		return array();
	}
	
	
	public function traceCurrent($uniid) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function traceHistory($uniid, $time) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function queryBarCodeActions() {
		return array();
	}
	
	
	public function barCodeCreateDisposable($barcode) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	
	public function barCodeCreateFixed($barcode) {
		trigger_error('not supported.', E_USER_WARNING);
	}
	
	public function downloadMedia($media){
		trigger_error('not supported.', E_USER_WARNING);
	}
}


class WeUtility {
	
	private static function defineConst($obj){
		global $_W;
		
		if ($obj instanceof WeBase) {
			if (!defined('MODULE_ROOT')) {
				define('MODULE_ROOT', dirname($obj->__define));
			}
			if (!defined('MODULE_URL')) {
				define('MODULE_URL', $_W['siteroot'].'addons/'.$obj->modulename.'/');
			}
		}
	}
	
	
	public static function createModule($name) {
		global $_W;
		static $file;
		$classname = ucfirst($name) . 'Module';
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/module.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/module.php";
			}
			if(!is_file($file)) {
				trigger_error('Module Definition File Not Found', E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('Module Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModule) {
			return $o;
		} else {
			trigger_error('Module Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	
	public static function createModuleProcessor($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleProcessor";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/processor.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/processor.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleProcessor Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleProcessor Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleProcessor) {
			return $o;
		} else {
			trigger_error('ModuleProcessor Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	
	public static function createModuleReceiver($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleReceiver";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/receiver.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/receiver.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleReceiver Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleReceiver Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleReceiver) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	
	public static function createModuleSite($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleSite";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/site.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/site.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleSite Definition File Not Found '.$file, E_USER_WARNING);
				return null;
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleSite Definition Class Not Found', E_USER_WARNING);
			return null;
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		$o->inMobile = defined('IN_MOBILE');
		if($o instanceof WeModuleSite) {
			return $o;
		} else {
			trigger_error('ModuleReceiver Class Definition Error', E_USER_WARNING);
			return null;
		}
	}

	
	public static function createModuleCron($name) {
		global $_W;
		static $file;
		$classname = "{$name}ModuleCron";
		if(!class_exists($classname)) {
			$file = IA_ROOT . "/addons/{$name}/cron.php";
			if(!is_file($file)) {
				$file = IA_ROOT . "/framework/builtin/{$name}/cron.php";
			}
			if(!is_file($file)) {
				trigger_error('ModuleCron Definition File Not Found '.$file, E_USER_WARNING);
				return error(-1006, 'ModuleCron Definition File Not Found');
			}
			require $file;
		}
		if(!class_exists($classname)) {
			trigger_error('ModuleCron Definition Class Not Found', E_USER_WARNING);
			return error(-1007, 'ModuleCron Definition Class Not Found');
		}
		$o = new $classname();
		$o->uniacid = $o->weid = $_W['uniacid'];
		$o->modulename = $name;
		load()->model('module');
		$o->module = module_fetch($name);
		$o->__define = $file;
		self::defineConst($o);
		if($o instanceof WeModuleCron) {
			return $o;
		} else {
			trigger_error('ModuleCron Class Definition Error', E_USER_WARNING);
			return error(-1008, 'ModuleCron Class Definition Error');
		}
	}

	
	public static function logging($level = 'info', $message = '') {
		$filename = IA_ROOT . '/data/logs/' . date('Ymd') . '.log';
		load()->func('file');
		mkdirs(dirname($filename));
		$content = date('Y-m-d H:i:s') . " {$level} :\n------------\n";
		if(is_string($message) && !in_array($message, array('post', 'get'))) {
			$content .= "String:\n{$message}\n";
		}
		if(is_array($message)) {
			$content .= "Array:\n";
			foreach($message as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'get') {
			$content .= "GET:\n";
			foreach($_GET as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		if($message === 'post') {
			$content .= "POST:\n";
			foreach($_POST as $key => $value) {
				$content .= sprintf("%s : %s ;\n", $key, $value);
			}
		}
		$content .= "\n";

		$fp = fopen($filename, 'a+');
		fwrite($fp, $content);
		fclose($fp);
	}
}

abstract class WeBase {
	
	public $modulename;
	
	public $module;
	
	public $weid;
	
	public $uniacid;
	
	public $__define;

	
	public function saveSettings($settings) {
		global $_W;
		$pars = array('module' => $this->modulename, 'uniacid' => $_W['uniacid']);
		$row = array();
		$row['settings'] = iserializer($settings);
		cache_build_account_modules();
		if (pdo_fetchcolumn("SELECT module FROM ".tablename('uni_account_modules')." WHERE module = :module AND uniacid = :uniacid", array(':module' => $this->modulename, ':uniacid' => $_W['uniacid']))) {
			return pdo_update('uni_account_modules', $row, $pars) !== false;
		} else {
			return pdo_insert('uni_account_modules', array('settings' => iserializer($settings), 'module' => $this->modulename ,'uniacid' => $_W['uniacid'], 'enabled' => 1)) !== false;
		}
	}

	
	protected function createMobileUrl($do, $query = array(), $noredirect = true) {
		global $_W;
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		return murl('entry', $query, $noredirect);
	}

	
	protected function createWebUrl($do, $query = array()) {
		$query['do'] = $do;
		$query['m'] = strtolower($this->modulename);
		return wurl('site/entry', $query);
	}

	
	protected function template($filename) {
		global $_W;
		$name = strtolower($this->modulename);
		$defineDir = dirname($this->__define);
		if(defined('IN_SYS')) {
			$source = IA_ROOT . "/web/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$name}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = $defineDir . "/template/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/{$_W['template']}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$filename}.html";
			}
		} else {
			$source = IA_ROOT . "/app/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/app/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if(!is_file($source)) {
				$source = IA_ROOT . "/app/themes/default/{$name}/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = $defineDir . "/template/mobile/{$filename}.html";
			}
			if(!is_file($source)) {
				$source = IA_ROOT . "/app/themes/{$_W['template']}/{$filename}.html";
			}
			if(!is_file($source)) {
				if (in_array($filename, array('header', 'footer', 'slide', 'toolbar', 'message'))) {
					$source = IA_ROOT . "/app/themes/default/common/{$filename}.html";
				} else {
					$source = IA_ROOT . "/app/themes/default/{$filename}.html";
				}
			}
		}
		if(!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		}
		$paths = pathinfo($compile);
		$compile = str_replace($paths['filename'], $_W['uniacid'] . '_' . $paths['filename'], $compile);
		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		}
		return $compile;
	}
}


abstract class WeModule extends WeBase {
	
	public function fieldsFormDisplay($rid = 0) {
		return '';
	}
	
	public function fieldsFormValidate($rid = 0) {
		return '';
	}
	
	public function fieldsFormSubmit($rid) {
			}
	
	public function ruleDeleted($rid) {
		return true;
	}
	
	public function settingsDisplay($settings) {
			}
}


abstract class WeModuleProcessor extends WeBase {
	
	public $priority;
	
	public $message;
	
	public $inContext;
	
	public $rule;

	public function __construct(){
		global $_W;
		
		$_W['member'] = array();
		if(!empty($_W['openid'])){
			load()->model('mc');
			$_W['member'] = mc_fetch($_W['openid']);
		}
	}
	
	
	protected function beginContext($expire = 1800) {
		if($this->inContext) {
			return true;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextmodule'] = $this->module['name'];
		$_SESSION['__contextrule'] = $this->rule;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		$_SESSION['__contextpriority'] = $this->priority;
		$this->inContext = true;
		
		return true;
	}
	
	protected function refreshContext($expire = 1800) {
		if(!$this->inContext) {
			return false;
		}
		$expire = intval($expire);
		WeSession::$expire = $expire;
		$_SESSION['__contextexpire'] = TIMESTAMP + $expire;
		
		return true;
	}
	
	protected function endContext() {
		unset($_SESSION['__contextmodule']);
		unset($_SESSION['__contextrule']);
		unset($_SESSION['__contextexpire']);
		unset($_SESSION['__contextpriority']);
		unset($_SESSION);
		session_destroy();
	}
	
	abstract function respond();
	
	protected function respText($content) {
		if (empty($content)) {
			return error(-1, 'Invaild value');
		}
		if(stripos($content,'./') !== false) {
			preg_match_all('/<a .*?href="(.*?)".*?>/is',$content,$urls);
			if (!empty($urls[1])) {
				foreach ($urls[1] as $url) {
					$content = str_replace($url, $this->buildSiteUrl($url), $content);
				}
			}
		}
		$content = str_replace("\r\n", "\n", $content);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'text';
		$response['Content'] = htmlspecialchars_decode($content);
		preg_match_all('/\[U\+(\\w{4,})\]/i', $response['Content'], $matchArray);
		if(!empty($matchArray[1])) {
			foreach ($matchArray[1] as $emojiUSB) {
				$response['Content'] = str_ireplace("[U+{$emojiUSB}]", utf8_bytes(hexdec($emojiUSB)), $response['Content']);
			}
		}
		return $response;
	}
	
	protected function respImage($mid) {
		if (empty($mid)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'image';
		$response['Image']['MediaId'] = $mid;
		return $response;
	}
	
	protected function respVoice($mid) {
		if (empty($mid)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'voice';
		$response['Voice']['MediaId'] = $mid;
		return $response;
	}
	
	protected function respVideo(array $video) {
		if (empty($video)) {
			return error(-1, 'Invaild value');
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'video';
		$response['Video']['MediaId'] = $video['MediaId'];
		$response['Video']['Title'] = $video['Title'];
		$response['Video']['Description'] = $video['Description'];
		return $response;
	}
	
	protected function respMusic(array $music) {
		if (empty($music)) {
			return error(-1, 'Invaild value');
		}
		global $_W;
		$music = array_change_key_case($music);
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'music';
		$response['Music'] = array(
			'Title' => $music['title'],
			'Description' => $music['description'],
			'MusicUrl' => tomedia($music['musicurl'])
		);
		if (empty($music['hqmusicurl'])) {
			$response['Music']['HQMusicUrl'] = $response['Music']['MusicUrl'];
		} else {
			$response['Music']['HQMusicUrl'] = tomedia($music['hqmusicurl']);
		}
		if($music['thumb']) {
			$response['Music']['ThumbMediaId'] = $music['thumb'];
		}
		return $response;
	}
	
	protected function respNews(array $news) {
		if (empty($news) || count($news) > 10) {
			return error(-1, 'Invaild value');
		}
		$news = array_change_key_case($news);
		if (!empty($news['title'])) {
			$news = array($news);
		}
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($news);
		$response['Articles'] = array();
		foreach ($news as $row) {
			$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => ($response['ArticleCount'] > 1) ? '' : $row['description'],
				'PicUrl' => tomedia($row['picurl']),
				'Url' => $this->buildSiteUrl($row['url']),
				'TagName' => 'item'
			);
		}
		return $response;
	}

	
	protected function respCustom(array $message = array()) {
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'transfer_customer_service';
		if (!empty($message['TransInfo']['KfAccount'])) {
			$response['TransInfo']['KfAccount'] = $message['TransInfo']['KfAccount'];
		}
		return $response;
	}

	
	protected function buildSiteUrl($url) {
		global $_W;
		$mapping = array(
			'[from]' => $this->message['from'],
			'[to]' => $this->message['to'],
			'[rule]' => $this->rule,
			'[uniacid]' => $_W['uniacid'],
		);
		$url = str_replace(array_keys($mapping), array_values($mapping), $url);
		if(strexists($url, 'http://') || strexists($url, 'https://')) {
			return $url;
		}
		if (uni_is_multi_acid() && strexists($url, './index.php?i=') && !strexists($url, '&j=') && !empty($_W['acid'])) {
			$url = str_replace("?i={$_W['uniacid']}&", "?i={$_W['uniacid']}&j={$_W['acid']}&", $url);
		}
		static $auth;
		if(empty($auth)){
			$pass = array();
			$pass['openid'] = $this->message['from'];
			$pass['acid'] = $_W['acid'];
			
			$sql = 'SELECT `fanid`,`salt`,`uid` FROM ' . tablename('mc_mapping_fans') . ' WHERE `acid`=:acid AND `openid`=:openid';
			$pars = array();
			$pars[':acid'] = $_W['acid'];
			$pars[':openid'] = $pass['openid'];
			$fan = pdo_fetch($sql, $pars);
			if(empty($fan) || !is_array($fan) || empty($fan['salt'])) {
				$fan = array('salt' => ''); 
			}
			$pass['time'] = TIMESTAMP;
			$pass['hash'] = md5("{$pass['openid']}{$pass['time']}{$fan['salt']}{$_W['config']['setting']['authkey']}");
			$auth = base64_encode(json_encode($pass));
		}
		
		$vars = array();
		$vars['uniacid'] = $_W['uniacid'];
		$vars['__auth'] = $auth;
		$vars['forward'] = base64_encode($url);

		return $_W['siteroot'] . 'app/' . str_replace('./', '', url('auth/forward', $vars));
	}

	
	protected function extend_W(){
		global $_W;
		
		if(!empty($_W['openid'])){
			load()->model('mc');
			$_W['member'] = mc_fetch($_W['openid']);
		}
		if(empty($_W['member'])){
			$_W['member'] = array();
		}
		
		if(!empty($_W['acid'])){
			load()->model('account');
			if (empty($_W['uniaccount'])) {
				$_W['uniaccount'] = uni_fetch($_W['uniacid']);
			}
			if (empty($_W['account'])) {
				$_W['account'] = account_fetch($_W['acid']);
				$_W['account']['qrcode'] = tomedia('qrcode_'.$_W['acid'].'.jpg').'?time='.$_W['timestamp'];
				$_W['account']['avatar'] = tomedia('headimg_'.$_W['acid'].'.jpg').'?time='.$_W['timestamp'];
				$_W['account']['groupid'] = $_W['uniaccount']['groupid'];
			}
		}
	}
}


abstract class WeModuleReceiver extends WeBase {
	
	public $params;
	
	public $response;
	
	public $keyword;
	
	public $message;
	
	abstract function receive();
}


abstract class WeModuleSite extends WeBase {
	
	public $inMobile;

	public function __call($name, $arguments) {
		$isWeb = stripos($name, 'doWeb') === 0;
		$isMobile = stripos($name, 'doMobile') === 0;
		if($isWeb || $isMobile) {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/inc/';
			if($isWeb) {
				$dir .= 'web/';
				$fun = strtolower(substr($name, 5));
			}
			if($isMobile) {
				$dir .= 'mobile/';
				$fun = strtolower(substr($name, 8));
			}
			$file = $dir . $fun . '.inc.php';
			if(file_exists($file)) {
				require $file;
				exit;
			} else {
				$dir = str_replace("addons", "framework/builtin", $dir);
				$file = $dir . $fun . '.inc.php';
				if(file_exists($file)) {
					require $file;
					exit;
				}
			}
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return null;
	}

	
	protected function pay($params = array(), $mine = array()) {
		global $_W;
		if(!$this->inMobile) {
			message('支付功能只能在手机上使用');
		}
		$params['module'] = $this->module['name'];
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		$pars[':module'] = $params['module'];
		$pars[':tid'] = $params['tid'];
		if($params['fee'] <= 0) {
			$pars['from'] = 'return';
			$pars['result'] = 'success';
			$pars['type'] = '';
			$pars['tid'] = $params['tid'];
			$site = WeUtility::createModuleSite($pars[':module']);
			$method = 'payResult';
			if (method_exists($site, $method)) {
				exit($site->$method($pars));
			}
		}

		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
		$log = pdo_fetch($sql, $pars);
		if (empty($log)) {
			$log = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $_W['acid'],
				'openid' => $_W['member']['uid'],
				'module' => $this->module['name'],
				'tid' => $params['tid'],
				'fee' => $params['fee'],
				'card_fee' => $params['fee'],
				'status' => '0',
				'is_usecard' => '0',
			);
			pdo_insert('core_paylog', $log);
		}
		if($log['status'] == '1') {
			message('这个订单已经支付成功, 不需要重复支付.');
		}
		$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
		if(!is_array($setting['payment'])) {
			message('没有有效的支付方式, 请联系网站管理员.');
		}
		$pay = $setting['payment'];
		if (empty($_W['member']['uid'])) {
			$pay['credit']['switch'] = false;
		}
		if (!empty($pay['credit']['switch'])) {
			$credtis = mc_credit_fetch($_W['member']['uid']);
		}
		$you = 0;
		if($pay['card']['switch'] == 2 && !empty($_W['openid'])) {
						if($_W['card_permission'] == 1 && !empty($params['module'])) {
				$cards = pdo_fetchall('SELECT a.id,a.card_id,a.cid,b.type,b.title,b.extra,b.is_display,b.status,b.date_info FROM ' . tablename('coupon_modules') . ' AS a LEFT JOIN ' . tablename('coupon') . ' AS b ON a.cid = b.id WHERE a.acid = :acid AND a.module = :modu AND b.is_display = 1 AND b.status = 3 ORDER BY a.id DESC', array(':acid' => $_W['acid'], ':modu' => $params['module']));
				$flag = 0;
				if(!empty($cards)) {
					foreach($cards as $temp) {
						$temp['date_info'] = iunserializer($temp['date_info']);
						if($temp['date_info']['time_type'] == 1) {
							$starttime = strtotime($temp['date_info']['time_limit_start']);
							$endtime = strtotime($temp['date_info']['time_limit_end']);
							if(TIMESTAMP < $starttime || TIMESTAMP > $endtime) {
								continue;
							} else {
																$param = array(':acid' => $_W['acid'], ':openid' => $_W['openid'], ':card_id' => $temp['card_id']);
								$num = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND openid = :openid AND card_id = :card_id AND status = 1', $param);
								if($num <= 0) {
									continue;
								} else {
									$flag = 1;
									$card = $temp;
									break;
								}
							}
						} else {
							$deadline = intval($temp['date_info']['deadline']);
							$limit = intval($temp['date_info']['limit']);
														$param = array(':acid' => $_W['acid'], ':openid' => $_W['openid'], ':card_id' => $temp['card_id']);
							$record = pdo_fetchall('SELECT addtime,id,code FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND openid = :openid AND card_id = :card_id AND status = 1', $param);
							if(!empty($record)) {
								foreach($record as $li) {
									$time = strtotime(date('Y-m-d', $li['addtime']));
									$starttime = $time + $deadline * 86400;
									$endtime = $time + $deadline * 86400 + $limit * 86400;
									if(TIMESTAMP < $starttime || TIMESTAMP > $endtime) {
										continue;
									} else {
										$flag = 1;
										$card = $temp;
										break;
									}
								}
							}
							if($flag) {
								break;
							}
						}
					}
				}
				if($flag) {
					if($card['type'] == 'discount') {
						$you = 1;
						$card['fee'] = sprintf("%.2f", ($params['fee'] * ($card['extra'] / 100)));
					} elseif($card['type'] == 'cash') {
						$cash = iunserializer($card['extra']);
						if($params['fee'] >= $cash['least_cost']) {
														$you = 1;
							$card['fee'] = sprintf("%.2f", ($params['fee'] -  $cash['reduce_cost']));
						}
					}
					load()->classs('coupon');
					$acc = new coupon($_W['acid']);
					$card_id = $card['card_id'];
					$time = TIMESTAMP;
					$randstr = random(8);
					$sign = array($card_id, $time, $randstr, $acc->account['key']);
					$signature = $acc->SignatureCard($sign);
					if(is_error($signature)) {
						$you = 0;
					}
				}
			}
		}

		if($pay['card']['switch'] == 3 && $_W['member']['uid']) {
						$cards = array();
			if(!empty($params['module'])) {
				$cards = pdo_fetchall('SELECT a.id,a.couponid,b.type,b.title,b.discount,b.condition,b.starttime,b.endtime FROM ' . tablename('activity_coupon_modules') . ' AS a LEFT JOIN ' . tablename('activity_coupon') . ' AS b ON a.couponid = b.couponid WHERE a.uniacid = :uniacid AND a.module = :modu AND b.condition <= :condition AND b.starttime <= :time AND b.endtime >= :time  ORDER BY a.id DESC', array(':uniacid' => $_W['uniacid'], ':modu' => $params['module'], ':time' => TIMESTAMP, ':condition' => $params['fee']), 'couponid');
				if(!empty($cards)) {
					foreach($cards as $key => &$card) {
						$has = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon_record') . ' WHERE uid = :uid AND uniacid = :aid AND couponid = :cid AND status = 1' . $condition, array(':uid' => $_W['member']['uid'], ':aid' => $_W['uniacid'], ':cid' => $card['couponid']));
						if($has > 0){
							if($card['type'] == '1') {
								$card['fee'] = sprintf("%.2f", ($params['fee'] * $card['discount']));
								$card['discount_cn'] = sprintf("%.2f", $params['fee'] * (1 - $card['discount']));
							} elseif($card['type'] == '2') {
								$card['fee'] = sprintf("%.2f", ($params['fee'] -  $card['discount']));
								$card['discount_cn'] = $card['discount'];
							}
						} else {
							unset($cards[$key]);
						}
					}
				}
			}
			if(!empty($cards)) {
				$cards_str = json_encode($cards);
			}
		}
		include $this->template('common/paycenter');
	}

	
	public function payResult($ret) {
		global $_W;
		if($ret['from'] == 'return') {
			if ($ret['type'] == 'credit2') {
				message('已经成功支付', url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])));
			} else {
				message('已经成功支付', '../../' . url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])));
			}
		}
	}

	
	protected function payResultQuery($tid) {
		$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid';
		$params = array();
		$params[':module'] = $this->module['name'];
		$params[':tid'] = $tid;
		$log = pdo_fetch($sql, $params);
		$ret = array();
		if(!empty($log)) {
			$ret['uniacid'] = $log['uniacid'];
			$ret['result'] = $log['status'] == '1' ? 'success' : 'failed';
			$ret['type'] = $log['type'];
			$ret['from'] = 'query';
			$ret['tid'] = $log['tid'];
			$ret['user'] = $log['openid'];
			$ret['fee'] = $log['fee'];
		}
		return $ret;
	}

	
	protected function grant($params = array()) {
		global $_W, $_GPC;
		if (empty($_W['member']['uid'])) {
			checkauth();
		}
		load()->model('activity');
		$params['module'] = $this->module['name'];
		if(empty($params['module'])) {
			message('模块信息错误', referer(), 'error');
		}
		$iscard = pdo_fetchcolumn('SELECT iscard FROM ' . tablename('modules') . ' WHERE name = :name', array(':name' => $params['module']));
		if(!$iscard) {
			message('模块不支持领取优惠券', referer(), 'error');
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 5;
		$user = mc_fetch($_W['member']['uid'], array('groupid'));
		$groupid = $user['groupid'];

		$modules_limit = pdo_fetchall("SELECT couponid FROM ".tablename('activity_coupon_modules')." WHERE uniacid = :uniacid AND module = :module", array(':uniacid' => $_W['uniacid'], ':module' => $params['module']), 'couponid');
		$groups_limit = pdo_fetchall("SELECT couponid FROM ".tablename('activity_coupon_allocation')." WHERE uniacid = :uniacid AND groupid = :groupid", array(':uniacid' => $_W['uniacid'], ':groupid' => $groupid), 'couponid');
		$modules_limit = array_keys($modules_limit);
		$groups_limit = array_keys($groups_limit);
		$intersect = array_intersect($modules_limit, $groups_limit);
		if(empty($intersect)) {
			message('没有该模块适用的优惠券', referer(), 'error');
		}
		$intersect = implode(',', array_values($intersect));
		$par = array(':uniacid' => $_W['uniacid'], ':time' => TIMESTAMP);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon') . " WHERE uniacid = :uniacid AND dosage < amount AND endtime >= :time AND couponid IN ({$intersect})", $par);
		$cards = pdo_fetchall('SELECT * FROM ' . tablename('activity_coupon') . " WHERE uniacid = :uniacid AND dosage < amount AND endtime >= :time AND couponid IN ({$intersect}) ORDER BY endtime ASC LIMIT " . ($pindex - 1) * $psize . ', ' . $psize, $par);
		if(!empty($cards)) {
			foreach($cards as $key => &$card) {
				$has = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon_record') . ' WHERE uid = :uid AND uniacid = :aid AND couponid = :cid AND status > 0', array(':uid' => $_W['member']['uid'], ':aid' => $_W['uniacid'], ':cid' => $card['couponid']));
				$card['is_grant'] = 1;
				if($card['limit'] <= $has) {
					$card['is_grant'] = 0;
				}
				$card['grant_num'] = $has;
				$card['grant_url'] = base64_encode(json_encode(array('id' => $card['couponid'], 'm' => $params['module'])));
			}

						$creditnames = array();
			$unisettings = uni_setting($_W['uniacid'], array('creditnames'));
			if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
				foreach ($unisettings['creditnames'] as $key=>$credit) {
					$creditnames[$key] = $credit['title'];
				}
			}
		}
		$pager = pagination($total, $pindex, $psize, '', array('before' => 0, 'after' => 0));
		include $this->template('common/grant');
	}

	
	public function grantResult($ret) {
		global $_W;
		if($ret['result'] == 'success') {
			$types = array('', 'coupon', 'token');
			message('领取优惠券成功', url('activity/' . $types[$ret['type']] . '/mine'), 'success');
		}
	}

	
	public function grantCherk($ret) {
		global $_W;
		return true;
	}

	
	protected function share($params = array()) {
		global $_W;
		$url = murl('utility/share', array('module' => $params['module'], 'action' => $params['action'], 'sign' => $params['sign'], 'uid' => $params['uid']));
		echo <<<EOF
		<script>
			//转发成功后事件
			window.onshared = function(){
				var url = "{$url}";
				$.post(url);
			}
		</script>
EOF;
	}

	
	protected function click($params = array()) {
		global $_W;
		$url = murl('utility/click', array('module' => $params['module'], 'action' => $params['action'], 'sign' => $params['sign'], 'tuid' => $params['tuid'], 'fuid' => $params['fuid']));
		echo <<<EOF
		<script>
			var url = "{$url}";
			$.post(url);
		</script>
EOF;
	}

}


abstract class WeModuleCron extends WeBase {
	public function __call($name, $arguments) {
		if($this->modulename == 'task') {
			$dir = IA_ROOT . '/framework/builtin/task/cron/';
		} else {
			$dir = IA_ROOT . '/addons/' . $this->modulename . '/cron/';
		}
		$fun = strtolower(substr($name, 6));
		$file = $dir . $fun . '.inc.php';
		if(file_exists($file)) {
			require $file;
			exit;
		}
		trigger_error("访问的方法 {$name} 不存在.", E_USER_WARNING);
		return error(-1009, "访问的方法 {$name} 不存在.");
	}

		public function addCronLog($tid, $errno, $note, $tag = array()) {
		global $_W;
		if(!$tid) {
			message(error(-1, 'tid参数错误'), '', 'ajax');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'module' => $this->modulename,
			'type' => $_W['cron']['filename'],
			'tid' => $tid,
			'note' => $note,
			'tag' => iserializer($tag),
			'createtime' => TIMESTAMP
		);
		pdo_insert('core_cron_record', $data);
		message(error($errno, $note), '', 'ajax');
	}
}
