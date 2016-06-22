<?php
if (!defined('IN_IA')) {
	exit('Access Denied');
}
if (!class_exists('PosteraModel')) {
	class PosteraModel extends PluginModel
	{
		public function getSceneTicket($_var_0, $_var_1)
		{
			global $_W, $_GPC;
			$_var_2 = m('common')->getAccount();
			$_var_3 = '{"expire_seconds":' . $_var_0 . ',"action_info":{"scene":{"scene_id":' . $_var_1 . '}},"action_name":"QR_SCENE"}';
			$_var_4 = $_var_2->fetch_token();
			$_var_5 = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $_var_4;
			$_var_6 = curl_init();
			curl_setopt($_var_6, CURLOPT_URL, $_var_5);
			curl_setopt($_var_6, CURLOPT_POST, 1);
			curl_setopt($_var_6, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($_var_6, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($_var_6, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($_var_6, CURLOPT_POSTFIELDS, $_var_3);
			$_var_7 = curl_exec($_var_6);
			$_var_8 = @json_decode($_var_7, true);
			if (!is_array($_var_8)) {
				return false;
			}
			if (!empty($_var_8['errcode'])) {
				return error(-1, $_var_8['errmsg']);
			}
			$_var_9 = $_var_8['ticket'];
			return array('barcode' => json_decode($_var_3, true), 'ticket' => $_var_9);
		}

		function getSceneID()
		{
			global $_W;
			$_var_10 = $_W['acid'];
			$_var_11 = 1;
			$_var_12 = 2147483647;
			$_var_1 = rand($_var_11, $_var_12);
			if (empty($_var_1)) {
				$_var_1 = rand($_var_11, $_var_12);
			}
			while (1) {
				$_var_13 = pdo_fetchcolumn('select count(*) from ' . tablename('qrcode') . ' where qrcid=:qrcid and acid=:acid and model=0 limit 1', array(':qrcid' => $_var_1, ':acid' => $_var_10));
				if ($_var_13 <= 0) {
					break;
				}
				$_var_1 = rand($_var_11, $_var_12);
				if (empty($_var_1)) {
					$_var_1 = rand($_var_11, $_var_12);
				}
			}
			return $_var_1;
		}

		public function getQR($_var_14, $_var_15)
		{
			global $_W, $_GPC;
			$_var_10 = $_W['acid'];
			$_var_16 = time();
			$_var_17 = $_var_14['timeend'];
			$_var_0 = $_var_17 - $_var_16;
			if ($_var_0 > 86400 * 30 - 15) {
				$_var_0 = 86400 * 30 - 15;
			}
			$_var_18 = $_var_16 + $_var_0;
			$_var_19 = pdo_fetch('select * from ' . tablename('sz_yi_postera_qr') . ' where openid=:openid and acid=:acid and posterid=:posterid limit 1', array(':openid' => $_var_15['openid'], ':acid' => $_var_10, ':posterid' => $_var_14['id']));
			if (empty($_var_19)) {
				$_var_19['current_qrimg'] = '';
				$_var_1 = $this->getSceneID();
				$_var_8 = $this->getSceneTicket($_var_0, $_var_1);
				if (is_error($_var_8)) {
					return $_var_8;
				}
				if (empty($_var_8)) {
					return error(-1, '生成二维码失败');
				}
				$_var_20 = $_var_8['barcode'];
				$_var_9 = $_var_8['ticket'];
				$_var_21 = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $_var_9;
				$_var_22 = array('uniacid' => $_W['uniacid'], 'acid' => $_W['acid'], 'qrcid' => $_var_1, 'model' => 0, 'name' => 'SZ_YI_POSTERA_QRCODE', 'keyword' => 'SZ_YI_POSTERA', 'expire' => $_var_0, 'createtime' => time(), 'status' => 1, 'url' => $_var_8['url'], 'ticket' => $_var_8['ticket']);
				pdo_insert('qrcode', $_var_22);
				$_var_19 = array('acid' => $_var_10, 'openid' => $_var_15['openid'], 'sceneid' => $_var_1, 'type' => $_var_14['type'], 'ticket' => $_var_8['ticket'], 'qrimg' => $_var_21, 'posterid' => $_var_14['id'], 'expire' => $_var_0, 'url' => $_var_8['url'], 'goodsid' => $_var_14['goodsid'], 'endtime' => $_var_18);
				pdo_insert('sz_yi_postera_qr', $_var_19);
				$_var_19['id'] = pdo_insertid();
			} else {
				$_var_19['current_qrimg'] = $_var_19['qrimg'];
				if (time() > $_var_19['endtime']) {
					$_var_1 = $_var_19['sceneid'];
					$_var_8 = $this->getSceneTicket($_var_0, $_var_1);
					if (is_error($_var_8)) {
						return $_var_8;
					}
					if (empty($_var_8)) {
						return error(-1, '生成二维码失败');
					}
					$_var_20 = $_var_8['barcode'];
					$_var_9 = $_var_8['ticket'];
					$_var_21 = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $_var_9;
					pdo_update('qrcode', array('ticket' => $_var_8['ticket'], 'url' => $_var_8['url']), array('acid' => $_W['acid'], 'qrcid' => $_var_1));
					pdo_update('sz_yi_postera_qr', array('ticket' => $_var_9, 'qrimg' => $_var_21, 'url' => $_var_8['url'], 'endtime' => $_var_18), array('id' => $_var_19['id']));
					$_var_19['ticket'] = $_var_9;
					$_var_19['qrimg'] = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $_var_19['ticket'];
				}
			}
			return $_var_19;
		}

		public function getRealData($_var_23)
		{
			$_var_23['left'] = intval(str_replace('px', '', $_var_23['left'])) * 2;
			$_var_23['top'] = intval(str_replace('px', '', $_var_23['top'])) * 2;
			$_var_23['width'] = intval(str_replace('px', '', $_var_23['width'])) * 2;
			$_var_23['height'] = intval(str_replace('px', '', $_var_23['height'])) * 2;
			$_var_23['size'] = intval(str_replace('px', '', $_var_23['size'])) * 2;
			$_var_23['src'] = tomedia($_var_23['src']);
			return $_var_23;
		}

		public function createImage($_var_24)
		{
			load()->func('communication');
			$_var_25 = ihttp_request($_var_24);
			return imagecreatefromstring($_var_25['content']);
		}

		public function mergeImage($_var_26, $_var_23, $_var_24)
		{
			$_var_27 = $this->createImage($_var_24);
			$_var_28 = imagesx($_var_27);
			$_var_29 = imagesy($_var_27);
			imagecopyresized($_var_26, $_var_27, $_var_23['left'], $_var_23['top'], 0, 0, $_var_23['width'], $_var_23['height'], $_var_28, $_var_29);
			imagedestroy($_var_27);
			return $_var_26;
		}

		public function mergeText($_var_26, $_var_23, $_var_30)
		{
			$_var_31 = IA_ROOT . '/addons/sz_yi/static/fonts/msyh.ttf';
			$_var_32 = $this->hex2rgb($_var_23['color']);
			$_var_33 = imagecolorallocate($_var_26, $_var_32['red'], $_var_32['green'], $_var_32['blue']);
			imagettftext($_var_26, $_var_23['size'], 0, $_var_23['left'], $_var_23['top'] + $_var_23['size'], $_var_33, $_var_31, $_var_30);
			return $_var_26;
		}

		function hex2rgb($_var_34)
		{
			if ($_var_34[0] == '#') {
				$_var_34 = substr($_var_34, 1);
			}
			if (strlen($_var_34) == 6) {
				list($_var_35, $_var_36, $_var_37) = array($_var_34[0] . $_var_34[1], $_var_34[2] . $_var_34[3], $_var_34[4] . $_var_34[5]);
			} elseif (strlen($_var_34) == 3) {
				list($_var_35, $_var_36, $_var_37) = array($_var_34[0] . $_var_34[0], $_var_34[1] . $_var_34[1], $_var_34[2] . $_var_34[2]);
			} else {
				return false;
			}
			$_var_35 = hexdec($_var_35);
			$_var_36 = hexdec($_var_36);
			$_var_37 = hexdec($_var_37);
			return array('red' => $_var_35, 'green' => $_var_36, 'blue' => $_var_37);
		}

		public function createPoster($_var_14, $_var_15, $_var_19, $_var_38 = true)
		{
			global $_W;
			$_var_39 = IA_ROOT . '/addons/sz_yi/data/postera/' . $_W['uniacid'] . '/';
			if (!is_dir($_var_39)) {
				load()->func('file');
				mkdirs($_var_39);
			}
			if (!empty($_var_19['goodsid'])) {
				$_var_40 = pdo_fetch('select id,title,thumb,commission_thumb,marketprice,productprice from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $_var_19['goodsid'], ':uniacid' => $_W['uniacid']));
				if (empty($_var_40)) {
					m('message')->sendCustomNotice($_var_15['openid'], '未找到商品，无法生成海报');
					exit;
				}
			}
			$_var_41 = md5(json_encode(array('openid' => $_var_15['openid'], 'goodsid' => $_var_19['goodsid'], 'bg' => $_var_14['bg'], 'data' => $_var_14['data'], 'version' => 1)));
			$_var_42 = $_var_41 . '.png';
			if (!is_file($_var_39 . $_var_42) || $_var_19['qrimg'] != $_var_19['current_qrimg']) {
				set_time_limit(0);
				@ini_set('memory_limit', '256M');
				$_var_26 = imagecreatetruecolor(640, 1008);
				$_var_43 = $this->createImage(tomedia($_var_14['bg']));
				imagecopy($_var_26, $_var_43, 0, 0, 0, 0, 640, 1008);
				imagedestroy($_var_43);
				$_var_23 = json_decode(str_replace('&quot;', '\'', $_var_14['data']), true);
				foreach ($_var_23 as $_var_44) {
					$_var_44 = $this->getRealData($_var_44);
					if ($_var_44['type'] == 'head') {
						$_var_45 = preg_replace('/\\/0$/i', '/96', $_var_15['avatar']);
						$_var_26 = $this->mergeImage($_var_26, $_var_44, $_var_45);
					} else if ($_var_44['type'] == 'time') {
						$_var_16 = date('Y-m-d H:i', $_var_19['endtime']);
						$_var_26 = $this->mergeText($_var_26, $_var_44, $_var_16);
					} else if ($_var_44['type'] == 'img') {
						$_var_26 = $this->mergeImage($_var_26, $_var_44, $_var_44['src']);
					} else if ($_var_44['type'] == 'qr') {
						$_var_26 = $this->mergeImage($_var_26, $_var_44, tomedia($_var_19['qrimg']));
					} else if ($_var_44['type'] == 'nickname') {
						$_var_26 = $this->mergeText($_var_26, $_var_44, $_var_15['nickname']);
					} else {
						if (!empty($_var_40)) {
							if ($_var_44['type'] == 'title') {
								$_var_26 = $this->mergeText($_var_26, $_var_44, $_var_40['title']);
							} else if ($_var_44['type'] == 'thumb') {
								$_var_46 = !empty($_var_40['commission_thumb']) ? tomedia($_var_40['commission_thumb']) : tomedia($_var_40['thumb']);
								$_var_26 = $this->mergeImage($_var_26, $_var_44, $_var_46);
							} else if ($_var_44['type'] == 'marketprice') {
								$_var_26 = $this->mergeText($_var_26, $_var_44, $_var_40['marketprice']);
							} else if ($_var_44['type'] == 'productprice') {
								$_var_26 = $this->mergeText($_var_26, $_var_44, $_var_40['productprice']);
							}
						}
					}
				}
				imagepng($_var_26, $_var_39 . $_var_42);
				imagedestroy($_var_26);
			}
			$_var_27 = $_W['siteroot'] . 'addons/sz_yi/data/poster/' . $_W['uniacid'] . '/' . $_var_42;
			if (!$_var_38) {
				return $_var_27;
			}
			if ($_var_19['qrimg'] != $_var_19['current_qrimg'] || empty($_var_19['mediaid']) || empty($_var_19['createtime']) || $_var_19['createtime'] + 3600 * 24 * 3 - 7200 < time()) {
				$_var_47 = $this->uploadImage($_var_39 . $_var_42);
				$_var_19['mediaid'] = $_var_47;
				pdo_update('sz_yi_postera_qr', array('mediaid' => $_var_47, 'createtime' => time()), array('id' => $_var_19['id']));
			}
			return array('img' => $_var_27, 'mediaid' => $_var_19['mediaid']);
		}

		public function uploadImage($_var_27)
		{
			load()->func('communication');
			$_var_2 = m('common')->getAccount();
			$_var_48 = $_var_2->fetch_token();
			$_var_5 = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token={$_var_48}&type=image";
			$_var_6 = curl_init();
			$_var_23 = array('media' => '@' . $_var_27);
			if (version_compare(PHP_VERSION, '5.5.0', '>')) {
				$_var_23 = array('media' => curl_file_create($_var_27));
			}
			curl_setopt($_var_6, CURLOPT_URL, $_var_5);
			curl_setopt($_var_6, CURLOPT_POST, 1);
			curl_setopt($_var_6, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($_var_6, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($_var_6, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($_var_6, CURLOPT_POSTFIELDS, $_var_23);
			$_var_49 = @json_decode(curl_exec($_var_6), true);
			if (!is_array($_var_49)) {
				$_var_49 = array('media_id' => '');
			}
			curl_close($_var_6);
			return $_var_49['media_id'];
		}

		public function getQRByTicket($_var_9 = '')
		{
			global $_W;
			if (empty($_var_9)) {
				return false;
			}
			$_var_50 = pdo_fetchall('select * from ' . tablename('sz_yi_postera_qr') . ' where ticket=:ticket and acid=:acid limit 1', array(':ticket' => $_var_9, ':acid' => $_W['acid']));
			$_var_13 = count($_var_50);
			if ($_var_13 <= 0) {
				return false;
			}
			if ($_var_13 == 1) {
				return $_var_50[0];
			}
			return false;
		}

		public function checkMember($_var_51 = '')
		{
			global $_W;
			$_var_52 = WeiXinAccount::create($_W['acid']);
			$_var_53 = $_var_52->fansQueryInfo($_var_51);
			$_var_53['avatar'] = $_var_53['headimgurl'];
			load()->model('mc');
			$_var_54 = mc_openid2uid($_var_51);
			if (!empty($_var_54)) {
				pdo_update('mc_members', array('nickname' => $_var_53['nickname'], 'gender' => $_var_53['sex'], 'nationality' => $_var_53['country'], 'resideprovince' => $_var_53['province'], 'residecity' => $_var_53['city'], 'avatar' => $_var_53['headimgurl']), array('uid' => $_var_54));
			}
			pdo_update('mc_mapping_fans', array('nickname' => $_var_53['nickname']), array('uniacid' => $_W['uniacid'], 'openid' => $_var_51));
			$_var_55 = m('member');
			$_var_15 = $_var_55->getMember($_var_51);
			if (empty($_var_15)) {
				$_var_56 = mc_fetch($_var_54, array('realname', 'nickname', 'mobile', 'avatar', 'resideprovince', 'residecity', 'residedist'));
				$_var_15 = array('uniacid' => $_W['uniacid'], 'uid' => $_var_54, 'openid' => $_var_51, 'realname' => $_var_56['realname'], 'mobile' => $_var_56['mobile'], 'nickname' => !empty($_var_56['nickname']) ? $_var_56['nickname'] : $_var_53['nickname'], 'avatar' => !empty($_var_56['avatar']) ? $_var_56['avatar'] : $_var_53['avatar'], 'gender' => !empty($_var_56['gender']) ? $_var_56['gender'] : $_var_53['sex'], 'province' => !empty($_var_56['resideprovince']) ? $_var_56['resideprovince'] : $_var_53['province'], 'city' => !empty($_var_56['residecity']) ? $_var_56['residecity'] : $_var_53['city'], 'area' => $_var_56['residedist'], 'createtime' => time(), 'status' => 0);
				pdo_insert('sz_yi_member', $_var_15);
				$_var_15['id'] = pdo_insertid();
				$_var_15['isnew'] = true;
			} else {
				$_var_15['nickname'] = $_var_53['nickname'];
				$_var_15['avatar'] = $_var_53['headimgurl'];
				$_var_15['province'] = $_var_53['province'];
				$_var_15['city'] = $_var_53['city'];
				pdo_update('sz_yi_member', $_var_15, array('id' => $_var_15['id']));
				$_var_15['isnew'] = false;
			}
			return $_var_15;
		}

		function perms()
		{
			return array('postera' => array('text' => $this->getName(), 'isplugin' => true, 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log', 'log' => '扫描记录', 'clear' => '清除缓存-log', 'setdefault' => '设置默认海报-log'));
		}
	}
}
