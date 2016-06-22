<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->classs('weixin.account');
class coupon extends WeiXinAccount {
	public $account = null;
	public function __construct($acid) {
		$this->account_api = self::create($acid);
		$this->account = $this->account_api->account;
	}
	
	public function getAccessToken() {
		return $this->account_api->getAccessToken();
	}

	public function getCardTicket(){
		$cachekey = "cardticket:{$this->account['acid']}";
		$cache = cache_load($cachekey);
		if (!empty($cache) && !empty($cache['ticket']) && $cache['expire'] > TIMESTAMP) {
			$this->account['card_ticket'] = $cache;
			return $cache['ticket'];
		}
		load()->func('communication');
		$access_token = $this->getAccessToken();
		if(is_error($access_token)){
			return $access_token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=wx_card";
		$content = ihttp_get($url);
		if(is_error($content)) {
			return error(-1, '调用接口获取微信公众号 card_ticket 失败, 错误信息: ' . $content['message']);
		}
		$result = @json_decode($content['content'], true);
		if(empty($result) || intval(($result['errcode'])) != 0 || $result['errmsg'] != 'ok') {
			return error(-1, '获取微信公众号 card_ticket 结果错误, 错误信息: ' . $result['errmsg']);
		}
		$record = array();
		$record['ticket'] = $result['ticket'];
		$record['expire'] = TIMESTAMP + $result['expires_in'] - 200;
		$this->account['card_ticket'] = $record;
		cache_write($cachekey, $record);
		return $record['ticket'];
	}

	
	public function LocationLogoupload($logo){
		global $_W;
		if(!strexists($logo, 'http://') && !strexists($logo, 'https://')) {
			$path = rtrim(IA_ROOT .'/'. $_W['config']['upload']['attachdir'], '/') . '/';
			if(empty($logo) || !file_exists($path . $logo)) {
				return error(-1, '商户LOGO不存在');
			}
		} else {
			return error(-1, '商户LOGO只能上传本地图片');
		}

		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token={$token}";
		$data = array(
			'buffer' => '@' . $path . $logo
		);
		load()->func('communication');
		$response = ihttp_request($url, $data);
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

	
	public function SetTestWhiteList($data){
		global $_W;
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/testwhitelist/set?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function LocationAdd($data) {
		if(empty($data)) {
			return error(-1, '门店信息错误');
		}
		$post = array(
			'business' => array(
				'base_info' => $data
			),
		);
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "http://api.weixin.qq.com/cgi-bin/poi/addpoi?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, urldecode(json_encode($post)));
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

		public function LocationEdit($data) {
		if(empty($data)) {
			return error(-1, '门店信息错误');
		}
		$post = array(
			'business' => array(
				'base_info' => $data
			),
		);
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "http://api.weixin.qq.com/cgi-bin/poi/updatepoi?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, urldecode(json_encode($post)));
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

		public function LocationDel($id) {
		if(empty($id)) {
			return error(-1, '门店信息错误');
		}
		$post = array(
			'poi_id' => $id
		);
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "http://api.weixin.qq.com/cgi-bin/poi/delpoi?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($post));
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

	public function LocationBatchGet($data = array()) {
		if(empty($data['begin'])) {
			$data['begin'] = 0;
		}
		if(empty($data['limit'])) {
			$data['limit'] = 50;
		}
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "http://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

	public function LocationGet($id) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array(
			'poi_id' => $id
		);
		$url = "http://api.weixin.qq.com/cgi-bin/poi/getpoi?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function GetColors() {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/getcolors?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url);
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

		public function CreateCard($card) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/create?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, $card);
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

		public function DeleteCard($card_id) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/delete?access_token={$token}";
		load()->func('communication');
		$card = json_encode(array('card_id' => $card_id));
		$response = ihttp_request($url, $card);
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

	
	public function ModifyStockCard($card_id, $num) {
		$data['card_id'] = trim($card_id);
		$data['increase_stock_value'] = 0;
		$data['reduce_stock_value'] = 0;
		$num = intval($num);
		($num > 0) && ($data['increase_stock_value'] = $num);
		($num < 0) && ($data['reduce_stock_value'] = abs($num));
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/modifystock?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function QrCard($data) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/qrcode/create?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function UnavailableCode($data) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/code/unavailable?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function ConsumeCode($data) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/code/consume?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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
	
		public function selfConsume($data) {
		$token = $this->getAccessToken();
		if(is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/selfconsumecell/set?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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
	
		public function DecryptCode($data) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$url = "https://api.weixin.qq.com/card/code/decrypt?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function fetchCard($card_id) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array(
			'card_id' => $card_id,
		);
		$url = "https://api.weixin.qq.com/card/get?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
		if(is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		if(empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} elseif(!empty($result['errcode'])) {
			return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},错误详情：{$this->error_code($result['errcode'])}");
		}
		return $result['card'];
	}

	public function updateCard($card_id) {
		$token = $this->getAccessToken();
		if (is_error($token)) {
			return $token;
		}
		$data = array(
			'card_id' => $card_id,
		);
		$url = "https://api.weixin.qq.com/card/membercard/activate?access_token={$token}";
		load()->func('communication');
		$response = ihttp_request($url, json_encode($data));
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

		public function PayConsumeCode($data) {
		$code_error['uniacid'] = $this->account['uniacid'];
		$code_error['acid'] = $this->account['acid'];
		$code_error['type'] = 2;
		$code_error['message'] = $data['encrypt_code'];
		$code_error['dateline'] = time();
		$code_error['module'] = $data['module'];
		$code_error['params'] = $data['card_id'];

		$code = $this->DecryptCode(array('encrypt_code' => $data['encrypt_code']));
		if(is_error($code)) {
			pdo_insert('core_queue', $code_error);
		} else {
			$sumecode = $this->ConsumeCode(array('code' => $code['code']));
			if(is_error($sumecode)) {
				pdo_insert('core_queue', $code_error);
			} else {
				pdo_update('coupon_record', array('status' => 3, 'usetime' => time()), array('acid' => $this->account['acid'], 'code' => $code['code'], 'card_id' => $data['card_id']));
			}
		}
		return true;
	}


		public function SignatureCard($data) {
		$ticket = $this->getCardTicket();
		if (is_error($ticket)) {
			return $ticket;
		}
		$data[] = $ticket;
		sort($data, SORT_STRING);
		return sha1(implode($data));
	}

	
	public function BuildCardExt($id, $openid = '') {
		$acid = $this->account['acid'];
		$card_id = pdo_fetchcolumn('SELECT card_id FROM ' . tablename('coupon') . ' WHERE acid = :acid AND id = :id', array(':acid' => $acid, ':id' => $id));
		if(empty($card_id)) {
			return error(-1, '卡券id不合法');
		}
		$time = TIMESTAMP;
		$sign = array($card_id, $time);
		$signature = $this->SignatureCard($sign);
		if(is_error($signature)) {
			return $signature;
		}
		$cardExt =  array('timestamp' => $time, 'signature' => $signature);
		$cardExt = json_encode($cardExt);
		return array('card_id' => $card_id, 'card_ext' => $cardExt);
	}

	public function AddCard($id) {
		$card = $this->BuildCardExt($id);
		if(is_error($card)) {
			return $card;
		}
		return <<<EOF
			wx.ready(function(){
				wx.addCard({
					cardList:[
						{
							cardId:'{$card['card_id']}',
							cardExt:'{$card['card_ext']}'
						}
					]
				});
			});
EOF;
	}

	public function ChooseCard($card_id) {
		$acid = $this->account['acid'];
		if(empty($card_id)) {
			return error(-1, '卡券不存在');
		}
		$time = TIMESTAMP;
		$randstr = random(8);
		$sign = array($card_id, $time, $randstr, $this->account['key']);
		$signature = $this->SignatureCard($sign);
		if(is_error($signature)) {
			return $signature;
		}
		$url = murl("wechat/pay/card");
		return <<<EOF
			wx.ready(function(){
				wx.chooseCard({
					shopId: '',
					cardType: '',
					cardId:'{$card_id}',
					timestamp:{$time},
					nonceStr:'{$randstr}',
					signType:'SHA1',
					cardSign:'{$signature}',
					success: function(res) {
						if(res.errMsg == 'chooseCard:ok') {
							eval("var rs = " + res.cardList);
							$.post('{$url}', {'card_id':rs[0].card_id}, function(data){
								var data = $.parseJSON(data);
								if(!data.errno) {
									var card = data.error;
									if(card.type == 'discount') {

									}
								} else {
									u.message('卡券不存在', '', 'error');
								}
							});
						} else {
							u.message('使用卡券失败', '', 'error');
						}
					}
				});
			});
EOF;
	}


	
	public function BatchAddCard($data) {
		$acid = $this->account['acid'];
		$condition = '';
		if(!empty($data['type'])) {
			$condition .= " AND type = '{$data['type']}'";
		} else {
			$ids = array();
			foreach($data as $da) {
				$da = intval($da);
				if($da > 0) {
					$ids[] = $da;
				}
			}
			if(empty($ids)) {
				$condition = '';
			} else {
				$ids_str = implode(', ', $ids);
				$condition .= " AND id IN ({$ids_str})";
			}
		}

		$card = array();
		if(!empty($condition)) {
			$card = pdo_fetchall('SELECT id, card_id FROM ' . tablename('coupon') . " WHERE acid = {$acid} " . $condition);
		}
				foreach($card as $ca) {
						$time = TIMESTAMP;
			$sign = array($ca['card_id'], $time);
			$signature = $this->SignatureCard($sign);
			if(is_error($signature)) {
				return $signature;
			}
			$post[] = array(
				'cardId' => trim($ca['card_id']),
				'cardExt' => array('timestamp' => $time, 'signature' => $signature),
			);
		}
		if(!empty($post)) {
			$card_json = json_encode($post);
			echo <<<EOF
			<script>
				wx.ready(function(){
					wx.addCard({
						cardList : {$card_json}, // 需要添加的卡券列表
						success: function (res) {

							 alert(JSON.stringify(res));
							var cardList = res.cardList; // 添加的卡券列表信息
						}
					});
				});

			</script>
EOF;
		} else {
			echo <<<EOF
			<script>

			</script>
EOF;
		}
	}
}

class BaseInfo{
	private	 $code_types = array('', 'CODE_TYPE_TEXT', 'CODE_TYPE_QRCODE', 'CODE_TYPE_BARCODE');
	public $base_info;
	function init($baseinfo) {
		if(empty($baseinfo['logo_url'])) {
			return error(-1, '商户logo不能为空');
		}
		if(empty($baseinfo['brand_name'])) {
			return error(-1, '商户名称不能为空');
		}
		empty($baseinfo['code_type']) && ($baseinfo['code_type'] = 1);
		$baseinfo['code_type'] = $this->code_types[$baseinfo['code_type']];
		if(empty($baseinfo['title'])) {
			return error(-1, '卡券标题不能为空');
		}
		empty($baseinfo['color']) && ($baseinfo['color'] = 'Color010');
		if(empty($baseinfo['notice'])) {
			return error(-1, '操作提示不能为空');
		}
		if(empty($baseinfo['service_phone'])) {
			return error(-1, '客服电话不能为空');
		}
		if(empty($baseinfo['description'])) {
			return error(-1, '使用须知不能为空');
		}
				if(empty($baseinfo['time_type'])) {
			return error(-1, '使用期限不能为空');
		} else {
			if($baseinfo['time_type'] == 1) {
				if(!empty($baseinfo['time_limit[start]'])) {
					$baseinfo['begin_timestamp'] = strtotime($baseinfo['time_limit[start]']);
					$baseinfo['end_timestamp'] = strtotime($baseinfo['time_limit[end]']);
				} else {
					return error(-1, '使用期限限制错误');
				}
			} else {
				if(!empty($baseinfo['limit'])) {
					$baseinfo['fixed_begin_term'] = intval($baseinfo['deadline']);
					$baseinfo['fixed_term'] = intval($baseinfo['limit']);
				} else {
					return error(-1, '使用期限限制错误');
				}
			}
		}
		$baseinfo['quantity'] = intval($baseinfo['quantity']);
		if(!$baseinfo['quantity']) {
			return error(-1, '卡券库存不能为空或无限制');
		}

		$this->base_info['logo_url'] = urlencode($baseinfo['logo_url']);
		$this->base_info['brand_name'] = urlencode($baseinfo['brand_name']);
		$this->base_info['code_type'] = $baseinfo['code_type'];
		$this->base_info['title'] = urlencode($baseinfo['title']);
		$this->base_info['sub_title'] = urlencode($baseinfo['sub_title']);
		$this->base_info['color'] = $baseinfo['color'];
		$this->base_info['notice'] = urlencode($baseinfo['notice']);
		$this->base_info['service_phone'] = urlencode($baseinfo['service_phone']);
		$this->base_info['description'] = urlencode($baseinfo['description']);
		if($baseinfo['time_type'] == 1) {
			$this->base_info['date_info'] = array(
				'type' => 1,
				'begin_timestamp' => $baseinfo['begin_timestamp'],
				'end_timestamp' => $baseinfo['end_timestamp'],
			);
		} else {
			$this->base_info['date_info'] = array(
				'type' => 2,
				'fixed_term' => $baseinfo['fixed_term'],
				'fixed_begin_term' => $baseinfo['fixed_begin_term'],
			);
		}
		$this->base_info['sku'] = array('quantity' => $baseinfo['quantity']);
		$this->base_info['get_limit'] = intval($baseinfo['get_limit']);
		$this->base_info['can_share'] = intval($baseinfo['can_share']) ? true : false;
		$this->base_info['can_give_friend'] = intval($baseinfo['can_give_friend']) ? true : false;

		if($baseinfo['is_location'] && $baseinfo['location-select']) {
			$baseinfo['location'] = explode('-', $baseinfo['location-select']);
			if(!empty($baseinfo['location'])) {
				$this->base_info['location_id_list'] = $baseinfo['location'];
			}
		}
		$this->base_info['custom_url_name'] = urlencode('立即使用');
		$this->base_info['custom_url'] = urlencode(murl('wechat/card/use', array(), true, true));
		$this->base_info['custom_url_sub_title'] = '';
		if(!empty($baseinfo['promotion_url_name']) && !empty($baseinfo['promotion_url'])) {
			$this->base_info['promotion_url_name'] = urlencode($baseinfo['promotion_url_name']);
			$this->base_info['promotion_url'] = urlencode($baseinfo['promotion_url']);
			$this->base_info['promotion_url_sub_title'] = urlencode($baseinfo['promotion_url_sub_title']);
		}
		return $this->base_info;
	}
	function set_sub_title($sub_title){
		$this->base_info['sub_title'] = urlencode($sub_title);
	}
	function set_use_limit($use_limit){
		$this->base_info['use_limit'] = intval($use_limit);
	}
	function set_get_limit($get_limit){
		$this->base_info['get_limit'] = intval($get_limit);
	}
	function set_use_custom_code($use_custom_code){
		$this->base_info['use_custom_code'] = intval($use_custom_code) ? true : false;
	}
	function set_bind_openid($bind_openid){
		$this->base_info['bind_openid'] = intval($bind_openid) ? true : false;
	}
	function set_can_share($can_share){
		$this->base_info['can_share'] = intval($can_share) ? true :false;
	}
	function set_location_id_list($location_id_list){
		$this->base_info['location_id_list'] = $location_id_list;
	}
	function set_url_name_type($url_name_type){
		$this->base_info['url_name_type'] = intval($url_name_type);
	}
	function set_custom_url($custom_url){
		$this->base_info['custom_url'] = urlencode($custom_url);
	}
}

class CardBase extends BaseInfo{
	public function __construct($base_info){
		$this->base_info = $this->init($base_info);
	}
};

class GeneralCoupon extends CardBase{
	function set_default_detail($default_detail){
		$this->default_detail = $default_detail;
	}
};
class Groupon extends CardBase{
	function set_deal_detail($deal_detail){
		$this->deal_detail = $deal_detail;
	}
};
class Discount extends CardBase{
	function set_discount($discount){
		$this->discount = $discount;
	}
};
class Gift extends CardBase{
	function set_gift($gift){
		$this->gift = $gift;
	}
};
class Cash extends CardBase{
	function set_least_cost($least_cost){
		$this->least_cost = $least_cost;
	}
	function set_reduce_cost($reduce_cost){
		$this->reduce_cost = $reduce_cost;
	}
};
class MemberCard extends CardBase{
	function set_supply_bonus($supply_bonus){
		$this->supply_bonus = $supply_bonus;
	}
	function set_supply_balance($supply_balance){
		$this->supply_balance = $supply_balance;
	}
	function set_bonus_cleared($bonus_cleared){
		$this->bonus_cleared = $bonus_cleared;
	}
	function set_bonus_rules($bonus_rules){
		$this->bonus_rules = $bonus_rules;
	}
	function set_balance_rules($balance_rules){
		$this->balance_rules = $balance_rules;
	}
	function set_prerogative($prerogative){
		$this->prerogative = $prerogative;
	}
	function set_bind_old_card_url($bind_old_card_url){
		$this->bind_old_card_url = $bind_old_card_url;
	}
	function set_activate_url($activate_url){
		$this->activate_url = $activate_url;
	}
};
class ScenicTicket extends CardBase{
	function set_ticket_class($ticket_class){
		$this->ticket_class = $ticket_class;
	}
	function set_guide_url($guide_url){
		$this->guide_url = $guide_url;
	}
};
class MovieTicket extends CardBase{
	function set_detail($detail){
		$this->detail = $detail;
	}
};



class Card{
	private	 $CARD_TYPE = Array("GENERAL_COUPON", "GROUPON", "DISCOUNT", "GIFT", "CASH", "MEMBER_CARD", "SCENIC_TICKET", "MOVIE_TICKET"	);
	function __construct($card_type, $base_info) {
		if (!in_array($card_type, $this->CARD_TYPE)) {
			return error(-1, '卡券类型错误');
		}
		$this->card_type = $card_type;
		switch ($card_type) {
			case $this->CARD_TYPE[0]:
				$this->general_coupon = new GeneralCoupon($base_info);
				break;
			case $this->CARD_TYPE[1]:
				$this->groupon = new Groupon($base_info);
				break;
			case $this->CARD_TYPE[2]:
				$this->discount = new Discount($base_info);
				break;
			case $this->CARD_TYPE[3]:
				$this->gift = new Gift($base_info);
				break;
			case $this->CARD_TYPE[4]:
				$this->cash = new cash($base_info);
				break;
			case $this->CARD_TYPE[5]:
				$this->member_card = new MemberCard($base_info);
				break;
			case $this->CARD_TYPE[6]:
				$this->scenic_ticket = new ScenicTicket($base_info);
				break;
			case $this->CARD_TYPE[8]:
				$this->movie_ticket = new MovieTicket($base_info);
				break;
			default:
				return error(-1, '卡券类型错误');
		}
		return true;
	}
	function get_card() {
		switch ($this->card_type) {
			case $this->CARD_TYPE[0]:
				return $this->general_coupon;
			case $this->CARD_TYPE[1]:
				return $this->groupon;
			case $this->CARD_TYPE[2]:
				return $this->discount;
			case $this->CARD_TYPE[3]:
				return $this->gift;
			case $this->CARD_TYPE[4]:
				return $this->cash;
			case $this->CARD_TYPE[5]:
				return $this->member_card;
			case $this->CARD_TYPE[6]:
				return $this->scenic_ticket;
			case $this->CARD_TYPE[8]:
				return $this->movie_ticket;
			default:
				return error(-1, '获取卡券出错');
		}
	}
	function toJson() {
		return "{ \"card\":" . urldecode(json_encode($this)) . "}";
	}
};
