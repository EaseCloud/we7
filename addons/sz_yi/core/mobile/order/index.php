<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'index';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
$designer  = p('designer');
if ($designer) {
	$pagedata = $designer->getPage();
	if ($pagedata) {
		extract($pagedata);
		$guide = $designer->getGuide($system, $pageinfo);
		$_W['shopshare'] = array('title' => $share['title'], 'imgUrl' => $share['imgUrl'], 'desc' => $share['desc'], 'link' => $this->createMobileUrl('shop'));
		if (p('commission')) {
			$set = p('commission')->getSet();
			if (!empty($set['level'])) {
				$member = m('member')->getMember($openid);
				if (!empty($member) && $member['status'] == 1 && $member['isagent'] == 1) {
					$_W['shopshare']['link'] = $this->createMobileUrl('shop', array('mid' => $member['id']));
					if (empty($set['become_reg']) && (empty($member['realname']) || empty($member['mobile']))) {
						$trigger = true;
					}
				} else if (!empty($_GPC['mid'])) {
					$_W['shopshare']['link'] = $this->createMobileUrl('shop', array('mid' => $_GPC['mid']));
				}
			}
		}
		include $this->template('shop/index_diy');
		exit;
	}
}
$set = set_medias(m('common')->getSysset('shop'), array('logo', 'img'));
$custom = m('common')->getSysset('custom');

if ($operation == 'index') {
	$advs = pdo_fetchall('select id,advname,link,thumb,thumb_pc from ' . tablename('sz_yi_adv') . ' where uniacid=:uniacid and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
	foreach($advs as $key => $adv){
		if(!empty($advs[$key]['thumb'])){
			$adv[] = $advs[$key];
		}
		if(!empty($advs[$key]['thumb_pc'])){
			$adv_pc[] = $advs[$key];
		}
	}
	$advs = set_medias($advs, 'thumb,thumb_pc');
	$advs_pc = set_medias($adv_pc, 'thumb,thumb_pc');
	$adss = pdo_fetchall('select * from ' . tablename('sz_yi_ads') . ' where uniacid=:uniacid', array(':uniacid' => $uniacid));
	$adss = set_medias($adss, 'thumb_1,thumb_2,thumb_3,thumb_4');
	$category = pdo_fetchall('select * from ' . tablename('sz_yi_category'));
	$category = set_medias($category, 'thumb');
	$goods = pdo_fetchall('select * from ' . tablename('sz_yi_goods'));
	$goods = set_medias($goods, 'thumb');
	foreach ($category as &$c) {
		$c['thumb'] = tomedia($c['thumb']);
		if ($c['level'] == 3) {
			$c['url'] = $this->createMobileUrl('shop/list', array('tcate' => $c['id']));
		} else if ($c['level'] == 2) {
			$c['url'] = $this->createMobileUrl('shop/list', array('ccate' => $c['id']));
		}
	}
	unset($c);
} else if ($operation == 'goods') {
	$type = $_GPC['type'];
	$args = array('page' => $_GPC['page'], 'pagesize' => 6, 'isrecommand' => 1, 'order' => 'displayorder desc,createtime desc', 'by' => '');
	$goods = m('goods')->getList($args);
}
if ($_W['isajax']) {	
	if ($operation == 'index') {
		show_json(1, array('set' => $set, 'advs' => $advs, 'category' => $category));
	} else if ($operation == 'goods') {
		$type = $_GPC['type'];
		show_json(1, array('goods' => $goods, 'pagesize' => $args['pagesize']));
	}
}


$this->setHeader();
include $this->template('shop/index');
