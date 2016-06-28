<?php
global $_W, $_GPC;
$openid = m('user')->getOpenid();
if ($_W['isajax']) {
	$member = $this->model->getInfo($openid, array('total', 'ok', 'apply', 'check', 'lock', 'pay', 'myorder'));
	$cansettle = $member['commission_ok'] > 0 && $member['commission_ok'] >= floatval($this->set['withdraw']) &&  $member['myoedermoney'] >= floatval($this->set['consume_withdraw']);
	$member['commission_ok'] = number_format($member['commission_ok'], 2);
	$member['commission_total'] = number_format($member['commission_total'], 2);
	$member['commission_check'] = number_format($member['commission_check'], 2);
	$member['commission_apply'] = number_format($member['commission_apply'], 2);
	$member['commission_lock'] = number_format($member['commission_lock'], 2);
	$member['commission_pay'] = number_format($member['commission_pay'], 2);
	show_json(1, array('cansettle' => $cansettle, 'settlemoney' => number_format(floatval($this->set['withdraw']), 2), 'member' => $member, 'set' => $this->set));
}
include $this->template('withdraw');
