<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$mid     = intval($_GPC['mid']);
$openid  = m('user')->getOpenid();
$member  = m('member')->getMember($openid);
$set     = $this->set;
$uniacid = $_W['uniacid'];
if (!empty($mid)) {
	if (!$this->model->isAgent($mid)) {
		header('location: ' . $this->createMobileUrl('shop'));
		exit;
	}
	if ($mid != $member['id']) {
		if ($member['isagent'] == 1 && $member['status'] == 1) {
			if (!empty($set['closemyshop'])) {
				$shopurl = $this->createMobileUrl('shop', array('mid' => $member['id']));
			} else {
				$shopurl = $this->createPluginMobileUrl('commission/myshop', array('mid' => $member['id']));
			}
			header('location: ' . $shopurl);
			exit;
		} else {
			if (!empty($set['closemyshop'])) {
				$shopurl = $this->createMobileUrl('shop', array('mid' => $mid));
				header('location: ' . $shopurl);
				exit;
			}
		}
	} else {
		if (!empty($set['closemyshop'])) {
			$shopurl = $this->createMobileUrl('shop', array('mid' => $member['id']));
			header('location: ' . $shopurl);
			exit;
		}
	}
} else {
	if ($member['isagent'] == 1 && $member['status'] == 1) {
		$mid = $member['id'];
		if (!empty($set['closemyshop'])) {
			$shopurl = $this->createMobileUrl('shop');
			header('location: ' . $shopurl);
			exit;
		}
	} else {
		header('location: ' . $this->createMobileUrl('shop'));
		exit;
	}
}
$shop = set_medias($this->model->getShop($mid), array('img', 'logo'));
$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if ($op == 'display') {
	if ($_W['isajax']) {
		if (empty($shop['selectgoods'])) {
			$goodscount = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_goods') . ' where uniacid=:uniacid and status=1 and deleted=0', array(':uniacid' => $_W['uniacid']));
		} else {
			$goodscount = count(explode(",", $shop['goodsids']));
		}
		$advs = pdo_fetchall("select id,advname,link,thumb from " . tablename('sz_yi_adv') . ' where uniacid=:uniacid and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
		$advs = set_medias($advs, 'thumb');
		$ret = array('shop' => $shop, 'goodscount' => number_format($goodscount, 0), 'set' => m('common')->getSysset('shop'), 'advs' => $advs);
		$ret['isme'] = $mid == $member['id'];
		show_json(1, $ret);
	}
	$_W['shopshare'] = array('title' => $shop['name'], 'imgUrl' => $shop['logo'], 'desc' => $shop['desc'], 'link' => $this->createMobileUrl('shop'));
	if ($member['isagent'] == 1 && $member['status'] == 1) {
		$_W['shopshare']['link'] = $this->createPluginMobileUrl('commission/myshop', array('mid' => $member['id']));
		if (empty($this->set['become_reg']) && (empty($member['realname']) || empty($member['mobile']))) {
			$trigger = true;
		}
	} else if (!empty($mid)) {
		$_W['shopshare']['link'] = $this->createPluginMobileUrl('commission/myshop', array('mid' => $_GPC['mid']));
	}
	$this->setHeader();
	include $this->template('myshop');
} else if ($op == 'goods') {
	if ($_W['isajax']) {
		$args = array('page' => $_GPC['page'], 'pagesize' => 6, 'order' => 'displayorder desc,createtime desc', 'by' => '');
		if (!empty($shop['selectgoods'])) {
			$goodsids = explode(',', $shop['goodsids']);
			if (!empty($goodsids)) {
				$args['ids'] = trim($shop['goodsids']);
			}
		}
		$goods = m('goods')->getList($args);
		show_json(1, array('goods' => $goods, 'pagesize' => $args['pagesize']));
	}
} else if ($op == 'set') {
	if ($_W['isajax']) {
		if ($_W['ispost']) {
			$shopdata = is_array($_GPC['shopdata']) ? $_GPC['shopdata'] : array();
			$shopdata['uniacid'] = $_W['uniacid'];
			$shopdata['mid'] = $member['id'];
			if (empty($shop['id'])) {
				pdo_insert('sz_yi_commission_shop', $shopdata);
			} else {
				pdo_update('sz_yi_commission_shop', $shopdata, array('id' => $shop['id']));
			}
			show_json(1);
		}
		$shop = pdo_fetch('select * from ' . tablename('sz_yi_commission_shop') . ' where uniacid=:uniacid and mid=:mid limit 1', array(':uniacid' => $_W['uniacid'], ':mid' => $member['id']));
		$shop = set_medias($shop, array('img', 'logo'));
		$openselect = false;
		if ($this->set['select_goods'] == '1') {
			if (empty($member['agentselectgoods']) || $member['agentselectgoods'] == 2) {
				$openselect = true;
			}
		} else {
			if ($member['agentselectgoods'] == 2) {
				$openselect = true;
			}
		}
		$shop['openselect'] = $openselect;
		show_json(1, array('shop' => $shop));
	}
	include $this->template('myshop_set');
} else if ($op == 'select') {
	if ($_W['isajax']) {
		if ($member['agentselectgoods'] == 1) {
			show_json(-1, '您无权自选商品，请和运营商联系!');
		}
		if (empty($this->set['select_goods'])) {
			if ($member['agentselectgoods'] != 2) {
				show_json(-1, '系统未开启自选商品!');
			}
		}
		$shop = pdo_fetch('select * from ' . tablename('sz_yi_commission_shop') . ' where uniacid=:uniacid and mid=:mid limit 1', array(':uniacid' => $_W['uniacid'], ':mid' => $member['id']));
		if ($_W['ispost']) {
			$shopdata['selectgoods'] = intval($_GPC['selectgoods']);
			$shopdata['selectcategory'] = intval($_GPC['selectcategory']);
			$shopdata['uniacid'] = $_W['uniacid'];
			$shopdata['mid'] = $member['id'];
			if (is_array($_GPC['goodsids'])) {
				$shopdata['goodsids'] = implode(",", $_GPC['goodsids']);
			}
			if (!empty($shopdata['selectgoods']) && !is_array($_GPC['goodsids'])) {
				show_json(0, '请选择商品!');
			}
			if (empty($shop['id'])) {
				pdo_insert('sz_yi_commission_shop', $shopdata);
			} else {
				pdo_update('sz_yi_commission_shop', $shopdata, array('id' => $shop['id']));
			}
			show_json(1);
		}
		$goods = array();
		if (!empty($shop['selectgoods'])) {
			$goodsids = explode(',', $shop['goodsids']);
			if (!empty($goodsids)) {
				$goods = pdo_fetchall('select id,title,marketprice,thumb from ' . tablename('sz_yi_goods') . ' where uniacid=:uniacid and id in ( ' . trim($shop['goodsids']) . ')', array(':uniacid' => $_W['uniacid']));
				$goods = set_medias($goods, 'thumb');
			}
		}
		show_json(1, array('shop' => $shop, 'goods' => $goods));
	}
	$set = m('common')->getSysset('shop');
	include $this->template('myshop_select');
}
