<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

load()->model('cloud');
load()->func('communication');

if (!defined('MODULE_ROOT')) {
	trigger_error('CloudAPI 仅限模块中使用!');
}

class CloudApi {
	
	private $url = 'http://api.we7.cc/index.php?c=%s&a=%s&access_token=%s&';
	private $development = false;
	private $module = null;
	
	const ACCESS_TOKEN_EXPIRE_IN = 7200;
	
	
	public function __construct($development = false) {
		$this->development = $development;
		$this->module = pathinfo(MODULE_ROOT, PATHINFO_BASENAME);
	}
	
	private function getCerContent($file) {
		$cer_filepath = $this->cer_filepath($file);
		if (is_file($cer_filepath)) {
			$cer = file_get_contents($cer_filepath);
			if (!empty($cer)) {
				return $cer;
			}
		}
		return error(1, '获取访问云API的授权数字证书失败.');
	}
	
	private function developerCerContent(){
		$cer = $this->getCerContent('developer.cer');
		if (is_error($cer)) {
			return error(1, '访问云API获取授权失败,模块中没有开发者数字证书,请到 <a href="http://s.we7.cc/index.php?c=develop&a=auth" target="_blank">开发者中心</a> 下载数字证书!');;
		}
		
		return $cer;
	}
	
	private function cer_filepath($file) {
		return MODULE_ROOT.'/'.$file;
	}
	
	private function moduleCerContent(){
		$cer_filename = 'module.cer';
		$cer_filepath = $this->cer_filepath($cer_filename);
		
		if (is_file($cer_filepath)) {
			$expire_time = filemtime($cer_filepath) + CloudApi::ACCESS_TOKEN_EXPIRE_IN - 200;
			if (TIMESTAMP > $expire_time) {
				unlink($cer_filepath);
			}
		}
		
		if (!is_file($cer_filepath)) {
			$pars = _cloud_build_params();
			$pars['method'] = 'api.oauth';
			$pars['module'] = $this->module;
			$data = cloud_request('http://v2.addons.we7.cc/gateway.php', $pars);
			if (is_error($data)) {
				return $data;
			}
			$data = json_decode($data['content'], true);
			if (is_error($data)) {
				return $data;
			}
		}
		
		$cer = $this->getCerContent($cer_filename);
		if (is_error($cer)) {
			return error(1, '访问云API获取授权失败,模块中未发现数字证书(module.cer).');;
		}
		
		return $cer;
	}
	
	private function deleteModuleCer() {
		$cer_filename = 'module.cer';
		$cer_filepath = $this->cer_filepath($cer_filename);
		if (is_file($cer_filepath)) {
			unlink($cer_filepath);
		}
	}
	
	private function getAccessToken(){
		global $_W;
		if ($this->development) {
			$token = $this->developerCerContent();
		} else {
			$token = $this->moduleCerContent();
		}
		if (empty($token)) {
			return error(1, '错误的数字证书内容.');
		}
		if (is_error($token)) {
			return $token;
		}
		
		$access_token = array(
			'token' => $token,
			'module' => $this->module,
		);
		return base64_encode(json_encode($access_token));
	}
	
	public function url($api, $method, $params = array(), $dataType = 'json') {
		$access_token = $this->getAccessToken();
		if (is_error($access_token)) {
			return $access_token;
		}
		if (empty($params) || !is_array($params)) {
			$params = array();
		}
		
		$url = sprintf($this->url, $api, $method, $access_token);
		if (!empty($dataType)) {
			$url .= "&dataType={$dataType}";
		}
		if (!empty($params)) {
			$querystring = base64_encode(json_encode($params));
			$url .= "&api_qs={$querystring}";
		}
		
		if (strlen($url) > 4000) {
			return error(1, 'url query string too long');
		}
		
		return $url;
	}
	
	private function actionResult($result, $dataType = 'json') {
		if ($dataType == 'html') {
			return $result;
		}
		
		if ($dataType == 'json') {
			$result = strval($result);
			$json_result = json_decode($result, true);
			if (is_error($json_result)) {
				if ($json_result['errno'] == 10000) {
					$this->deleteModuleCer();
				};
				return $json_result;
			}
			return $json_result;
		}
		
		return $result;
	}
	
	public function get($api, $method, $url_params = array(), $dataType = 'json') {
		$url = $this->url($api, $method, $url_params, $dataType);
		if (is_error($url)) {
			return $url;
		}
		
		$response = ihttp_get($url);
		if (is_error($response)) {
			return $response;
		}
		
		$ihttp_options = array();
		$cookiejar = $response['headers']['Set-Cookie'];
		if (!empty($cookiejar)) {
			$ihttp_options['CURLOPT_COOKIE'] = implode('; ', $cookiejar);
		}
		
		$response = ihttp_request($url, array(), $ihttp_options);
		if (is_error($response)) {
			return $response;
		}
		
		$result = $this->actionResult($response['content'], $dataType);

		return $result;
	}
	
	public function post($api, $method, $post_params = array(), $dataType = 'json') {
		$url = $this->url($api, $method, array(), $dataType);
		if (is_error($url)) {
			return $url;
		}
		
		$response = ihttp_get($url);
		if (is_error($response)) {
			return $response;
		}
		
		$ihttp_options = array();
		$cookiejar = $response['headers']['Set-Cookie'];
		if (!empty($cookiejar)) {
			$ihttp_options['CURLOPT_COOKIE'] = implode('; ', $cookiejar);
		}
		
		$response = ihttp_request($url, $post_params, $ihttp_options);
		if (is_error($response)) {
			return $response;
		}
		
		return $this->actionResult($response['content'], $dataType);
	}
}
