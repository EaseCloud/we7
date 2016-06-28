<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
	die('Access Denied');
}
global $_W, $_GPC;
function upload_cert($_var_0)
{
	global $_W;
	$_var_1 = IA_ROOT . '/addons/sz_yi/cert';
	load()->func('file');
	mkdirs($_var_1, '0777');
	$_var_2 = $_var_0 . '_' . $_W['uniacid'] . '.pem';
	$_var_3 = $_var_1 . '/' . $_var_2;
	$_var_4 = $_FILES[$_var_0]['name'];
	$_var_5 = $_FILES[$_var_0]['tmp_name'];
	if (!empty($_var_4) && !empty($_var_5)) {
		$_var_6 = strtolower(substr($_var_4, strrpos($_var_4, '.')));
		if ($_var_6 != '.pem') {
			$_var_7 = "";
			if ($_var_0 == 'weixin_cert_file') {
				$_var_7 = 'CERT文件格式错误';
			} else {
				if ($_var_0 == 'weixin_key_file') {
					$_var_7 = 'KEY文件格式错误';
				} else {
					if ($_var_0 == 'weixin_root_file') {
						$_var_7 = 'ROOT文件格式错误';
					}
				}
			}
			message($_var_7 . ',请重新上传!', '', 'error');
		}
		return file_get_contents($_var_5);
	}
	return "";
}
$op = empty($_GPC['op']) ? 'shop' : trim($_GPC['op']);
if ($op == 'datamove') {
	$up = m('common')->dataMove();
	die('迁移成功');
}
$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
$set = unserialize($setdata['sets']);
$oldset = unserialize($setdata['sets']);
if ($op == 'template') {
	$styles = array();
	$dir = IA_ROOT . '/addons/sz_yi/template/mobile/';
	if ($handle = opendir($dir)) {
		while (($file = readdir($handle)) !== false) {
			if ($file != '..' && $file != '.') {
				if (is_dir($dir . '/' . $file)) {
					$styles[] = $file;
				}
			}
		}
		closedir($handle);
	}
} else {
	if ($op == 'notice') {
		$salers = array();
		if (isset($set['notice']['openid'])) {
			if (!empty($set['notice']['openid'])) {
				$openids = array();
				$strsopenids = explode(',', $set['notice']['openid']);
				foreach ($strsopenids as $openid) {
					$openids[] = '\'' . $openid . '\'';
				}
				$salers = pdo_fetchall('select id,nickname,avatar,openid from ' . tablename('sz_yi_member') . ' where openid in (' . implode(',', $openids) . ") and uniacid={$_W['uniacid']}");
			}
		}
		$newtype = explode(',', $set['notice']['newtype']);
	} else {
		if ($op == 'pay') {
			$sec = m('common')->getSec();
			$sec = iunserializer($sec['sec']);
		} else {
			if ($op == 'pcset') {
				$designer = p('designer');
				$categorys = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_article_category') . ' WHERE uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));
				if ($designer) {
					$diypages = pdo_fetchall('SELECT id,pagetype,setdefault,pagename FROM ' . tablename('sz_yi_designer') . ' WHERE uniacid=:uniacid order by setdefault desc  ', array(':uniacid' => $_W['uniacid']));
				}
				$article_sys = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article_sys') . ' WHERE uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid']));
				$article_sys['article_area'] = json_decode($article_sys['article_area'], true);
				$area_count = sizeof($article_sys['article_area']);
				if ($area_count == 0) {
					$article_sys['article_area'][0]['province'] = '';
					$article_sys['article_area'][0]['city'] = '';
					$area_count = 1;
				}
				$goodcates = pdo_fetchall('SELECT id,name,parentid FROM ' . tablename('sz_yi_category') . ' WHERE enabled=:enabled and uniacid= :uniacid  ', array(':uniacid' => $_W['uniacid'], ':enabled' => '1'));
				if (empty($set['shop']['hmenu_name'])) {
					$set['shop']['hmenu_name'] = array('首页', '全部商品', '店铺公告', '成为分销商', '会员中心');
					$set['shop']['hmenu_url'] = array($this->createMobileUrl('shop/index'), $this->createMobileUrl('shop/list', array('order' => 'sales', 'by' => 'desc')), $this->createMobileUrl('shop/notice'), $this->createPluginMobileUrl('commission'), $this->createMobileUrl('member/info'));
					$set['shop']['hmenu_id'] = array('yz01', 'yz02', 'yz03', 'yz04', 'yz05');
				}
			}
		}
	}
}
if (checksubmit()) {
	if ($op == 'shop') {
		$shop = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
		$set['shop']['name'] = trim($shop['name']);
		$set['shop']['cservice'] = trim($shop['cservice']);
		$set['shop']['img'] = save_media($shop['img']);
		$set['shop']['logo'] = save_media($shop['logo']);
		$set['shop']['signimg'] = save_media($shop['signimg']);
		$set['shop']['diycode'] = trim($shop['diycode']);
		$set['shop']['copyright'] = trim($shop['copyright']);
		plog('sysset.save.shop', '修改系统设置-商城设置');
	} elseif ($op == 'pcset') {
		$custom = is_array($_GPC['pcset']) ? $_GPC['pcset'] : array();
		$set['shop']['ispc'] = trim($custom['ispc']);
		$set['shop']['pctitle'] = trim($custom['pctitle']);
		$set['shop']['pckeywords'] = trim($custom['pckeywords']);
		$set['shop']['pcdesc'] = trim($custom['pcdesc']);
		$set['shop']['pccopyright'] = trim($custom['pccopyright']);
		$set['shop']['index'] = $custom['index'];
		$set['shop']['pclogo'] = save_media($custom['pclogo']);
		$set['shop']['reglogo'] = save_media($custom['reglogo']);
		$set['shop']['hmenu_name'] = $custom['hmenu_name'];
		$set['shop']['hmenu_url'] = $custom['hmenu_url'];
		$set['shop']['hmenu_id'] = $custom['hmenu_id'];
		$set['shop']['fmenu_name'] = $custom['fmenu_name'];
		$set['shop']['fmenu_url'] = $custom['fmenu_url'];
		$set['shop']['fmenu_id'] = $custom['fmenu_id'];
		plog('sysset.save.sms', '修改系统设置-PC设置');
	} elseif ($op == 'sms') {
		$sms = is_array($_GPC['sms']) ? $_GPC['sms'] : array();
		$set['sms']['type'] = $sms['type'];
		$set['sms']['account'] = $sms['account'];
		$set['sms']['password'] = $sms['password'];
		$set['sms']['appkey'] = $sms['appkey'];
		$set['sms']['secret'] = $sms['secret'];
		$set['sms']['signname'] = $sms['signname'];
		$set['sms']['product'] = $sms['product'];
		$set['sms']['templateCode'] = $sms['templateCode'];
		$set['sms']['templateCodeForget'] = $sms['templateCodeForget'];
		plog('sysset.save.sms', '修改系统设置-短信设置');
	} elseif ($op == 'follow') {
		$set['share'] = is_array($_GPC['share']) ? $_GPC['share'] : array();
		$set['share']['icon'] = save_media($set['share']['icon']);
		plog('sysset.save.follow', '修改系统设置-分享及关注设置');
	} else {
		if ($op == 'notice') {
			$set['notice'] = is_array($_GPC['notice']) ? $_GPC['notice'] : array();
			if (is_array($_GPC['openids'])) {
				$set['notice']['openid'] = implode(',', $_GPC['openids']);
			}
			$set['notice']['newtype'] = $_GPC['notice']['newtype'];
			if (is_array($set['notice']['newtype'])) {
				$set['notice']['newtype'] = implode(',', $set['notice']['newtype']);
			}
			plog('sysset.save.notice', '修改系统设置-模板消息通知设置');
		} elseif ($op == 'trade') {
			$set['trade'] = is_array($_GPC['trade']) ? $_GPC['trade'] : array();
			if (!$_W['isfounder']) {
				unset($set['trade']['receivetime']);
				unset($set['trade']['closordertime']);
				unset($set['trade']['paylog']);
			} else {
				m('cache')->set('receive_time', $set['trade']['receivetime'], 'global');
				m('cache')->set('closeorder_time', $set['trade']['closordertime'], 'global');
				m('cache')->set('paylog', $set['trade']['paylog'], 'global');
			}
			plog('sysset.save.trade', '修改系统设置-交易设置');
		} elseif ($op == 'pay') {
			$pluginy = p('yunpay');
			if ($pluginy) {
				$pay = $set['pay']['yunpay'];
			}
			$set['pay'] = is_array($_GPC['pay']) ? $_GPC['pay'] : array();
			if ($pluginy) {
				$set['pay']['yunpay'] = $pay;
			}
			if ($_FILES['weixin_cert_file']['name']) {
				$sec['cert'] = upload_cert('weixin_cert_file');
			}
			if ($_FILES['weixin_key_file']['name']) {
				$sec['key'] = upload_cert('weixin_key_file');
			}
			if ($_FILES['weixin_root_file']['name']) {
				$sec['root'] = upload_cert('weixin_root_file');
			}
			if (empty($sec['cert']) || empty($sec['key']) || empty($sec['root'])) {
			}
			pdo_update('sz_yi_sysset', array('sec' => iserializer($sec)), array('uniacid' => $_W['uniacid']));
			plog('sysset.save.pay', '修改系统设置-支付设置');
		} elseif ($op == 'template') {
			$shop = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
			$set['shop']['style'] = save_media($shop['style']);
			$set['shop']['theme'] = trim($shop['theme']);
			m('cache')->set('template_shop', $set['shop']['style']);
			m('cache')->set('theme_shop', $set['shop']['theme']);
			plog('sysset.save.template', '修改系统设置-模板设置');
		} elseif ($op == 'member') {
			$shop = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
			$set['shop']['levelname'] = trim($shop['levelname']);
			$set['shop']['levelurl'] = trim($shop['levelurl']);
			plog('sysset.save.member', '修改系统设置-会员设置');
			$set['shop']['isbindmobile'] = intval($shop['isbindmobile']);
		} elseif ($op == 'category') {
			$shop = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
			$set['shop']['catlevel'] = trim($shop['catlevel']);
			$set['shop']['catshow'] = intval($shop['catshow']);
			$set['shop']['catadvimg'] = save_media($shop['catadvimg']);
			$set['shop']['catadvurl'] = trim($shop['catadvurl']);
			plog('sysset.save.category', '修改系统设置-分类层级设置');
		} elseif ($op == 'contact') {
			$shop = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
			$set['shop']['qq'] = trim($shop['qq']);
			$set['shop']['address'] = trim($shop['address']);
			$set['shop']['phone'] = trim($shop['phone']);
			$set['shop']['description'] = trim($shop['description']);
			plog('sysset.save.contact', '修改系统设置-联系方式设置');
		}
	}
	$data = array('uniacid' => $_W['uniacid'], 'sets' => iserializer($set));
	if (empty($setdata)) {
		pdo_insert('sz_yi_sysset', $data);
	} else {
		pdo_update('sz_yi_sysset', $data, array('uniacid' => $_W['uniacid']));
	}
	$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
	m('cache')->set('sysset', $setdata);
	message('设置保存成功!', $this->createWebUrl('sysset', array('op' => $op)), 'success');
}
load()->func('tpl');
include $this->template('web/sysset/' . $op);
die;