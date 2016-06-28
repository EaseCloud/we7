<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$mid     = intval($_GPC['mid']);
$openid  = m('user')->getOpenid();
$member  = m('member')->getMember($openid);
$set     = $this->set;
$uniacid = $_W['uniacid'];
$shop = set_medias($this->model->getShop($member['id']), array('img', 'logo'));
$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if ($op == 'display') {
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
}