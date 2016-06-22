<?php
error_reporting(0);
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/sz_yi/defines.php';
require '../../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
global $_W, $_GPC;
ignore_user_abort();
set_time_limit(0);
$sets = pdo_fetchall('select uniacid from ' . tablename('sz_yi_sysset'));
foreach ($sets as $set) {
	$_W['uniacid'] = $set['uniacid'];
	if (empty($_W['uniacid'])) {
		continue;
	}
	$trade = m('common')->getSysset('trade', $_W['uniacid']);
	$days = intval($trade['closeorder']);
	if ($days <= 0) {
		continue;
	}
	$daytimes = 86400 * $days;
	$orders = pdo_fetchall('select id from ' . tablename('sz_yi_order') . " where  uniacid={$_W['uniacid']} and status=0 and paytype<>3  and createtime + {$daytimes} <=unix_timestamp() ");
	$p = p('coupon');
	foreach ($orders as $o) {
		$onew = pdo_fetch('select status from ' . tablename('sz_yi_order') . " where id=:id and status=0 and paytype<>3  and createtime + {$daytimes} <=unix_timestamp()  limit 1", array(':id' => $o['id']));
		if (!empty($onew) && $onew['status'] == 0) {
			pdo_query('update ' . tablename('sz_yi_order') . ' set status=-1,canceltime=' . time() . ' where id=' . $o['id']);
			if ($p) {
				if (!empty($o['couponid'])) {
					$p->returnConsumeCoupon($o['id']);
				}
			}
		}
	}
}
