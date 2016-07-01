<?php
global $_W, $_GPC;
$openid   = m('user')->getOpenid();
$tabwidth = "50";
if ($this->set['level'] >= 1) {
    $tabwidth = 100;
}
if ($this->set['level'] >= 2) {
    $tabwidth = 50;
}
if ($this->set['level'] >= 3) {
    $tabwidth = 33.3;
}
$member = $this->model->getInfo($openid);
$total  = $member['agentcount'];
$level  = intval($_GPC['level']);
($level > 3 || $level <= 0) && $level = 1;
$condition = '';
$level1    = $member['level1'];
$level2    = $member['level2'];
$level3    = $member['level3'];
$hasangent = false;
if ($level == 1) {
    if ($level1 > 0) {
        $condition = " and agentid={$member['id']}";
        $hasangent = true;
    }
} else if ($level == 2) {
    if ($level2 > 0) {
        $condition = " and agentid in( " . implode(',', array_keys($member['level1_agentids'])) . ")";
        $hasangent = true;
    }
} else if ($level == 3) {
    if ($level3 > 0) {
        $condition = " and agentid in( " . implode(',', array_keys($member['level2_agentids'])) . ")";
        $hasangent = true;
    }
}
if ($_W['isajax']) {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = array();
	if ($hasangent) {
		$list = pdo_fetchall("select * from " . tablename('sz_yi_member') . " where isagent =1 and status=1 and uniacid = " . $_W['uniacid'] . " {$condition}  ORDER BY agenttime desc limit " . ($pindex - 1) * $psize . ',' . $psize);
		foreach ($list as &$row) {
			$info = $this->model->getInfo($row['openid'], array('total'));
			$row['commission_total'] = $info['commission_total'];
			$row['agentcount'] = $info['agentcount'];
			$row['agenttime'] = date('Y-m-d H:i', $row['agenttime']);
		}
	}
	unset($row);
	show_json(1, array('list' => $list, 'pagesize' => $psize));
}
include $this->template('team');
