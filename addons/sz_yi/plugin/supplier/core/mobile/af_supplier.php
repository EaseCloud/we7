<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$openid = m('user')->getOpenid();
$member = m('member')->getInfo($openid);
$af_supplier = pdo_fetch("select * from " . tablename("sz_yi_af_supplier") . " where openid='{$openid}' and uniacid={$_W['uniacid']}");
if ($_W['isajax']) {
    if ($_W['ispost']) {
        $memberdata = $_GPC['memberdata'];
		$memberdata['openid'] = $openid;
		$memberdata['uniacid'] = $_W['uniacid'];
        pdo_insert('sz_yi_af_supplier',$memberdata);
        show_json(1);
    }
	show_json(1, array(
        'member' => $member
    ));
}
include $this->template('af_supplier');
