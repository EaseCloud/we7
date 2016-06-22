<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'post', 'del');
$do = in_array($do, $dos) ? $do : 'display';


$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames'));
foreach ($unisettings['creditnames'] as $key=>$credit) {
	if (!empty($credit['enabled'])) {
		$creditnames[$key] = $credit['title'];
	}
}
$activities = array();
$_W['modules'] = uni_modules();
foreach ($_W['modules'] as $key=>$value) {
	if($value['type'] == 'activity'){
		$activities[$key]= $value;
	}
}

if($do == 'post') {
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$item = pdo_fetch('SELECT * FROM '.tablename('activity_exchange').' WHERE id=:id AND uniacid=:uniacid',array(':id'=>$id, ':uniacid'=>$_W['uniacid']));
		if(empty($item)) {
			message('未找到指定兑换礼品或已删除.',url('activity/partimes'),'error');
		} else {
			$item['extra'] = iunserializer($item['extra']);
		}
	} else {
		$item['starttime'] = TIMESTAMP;
		$item['endtime'] = TIMESTAMP + 6 * 86400;
	}
	if(checksubmit('submit')) {
		$data['title'] = !empty($_GPC['title']) ? trim($_GPC['title']) : message('请输入兑换名称！');
		$_GPC['extra']['title'] = $activities[$_GPC['extra']['name']]['title'];
		if(empty($_GPC['extra']['title'])) {
			message('请选择礼品兑换内容');
		}	
		$period = intval($_GPC['extra']['period']);
		if ($period == 0) {
			$_GPC['extra']['period'] = intval($_GPC['period']);
		}
		$data['extra'] = iserializer($_GPC['extra']);
		$data['thumb'] = trim($_GPC['thumb']) ? trim($_GPC['thumb']) : message('请上传封面');
		$data['pretotal'] = intval($_GPC['pretotal']) ? intval($_GPC['pretotal']) : message('请输入每人最大兑换次数');
		$data['type'] = 5;
		$data['description'] = !empty($_GPC['description']) ? trim($_GPC['description']) : message('请输入兑换说明！');
		$data['credittype'] = !empty($_GPC['credittype']) ? trim($_GPC['credittype']) : message('请选择积分类型！');
		$data['credit'] = intval($_GPC['credit']);
		$starttime = strtotime($_GPC['datelimit']['start']);
		$endtime = strtotime($_GPC['datelimit']['end']);
		if ($endtime == $starttime) {
			$endtime = $endtime + 86399;
		}
		$data['starttime'] = $starttime;
		$data['endtime'] = $endtime;
		
		if(empty($id)) {
			$data['uniacid'] = $_W['uniacid'];
			$data['createtime'] = TIMESTAMP;
			pdo_insert('activity_exchange', $data);
			message('添加活动参与次数兑换成功',url('activity/partimes', array()),'success');
		} else {
			pdo_update('activity_exchange', $data, array('id' => $id, 'uniacid'=>$_W['uniacid']));
			message('更新活动参与次数兑换成功',url('activity/partimes', array()),'success');
		}
	}
}
if($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$where = ' WHERE type = 5 AND uniacid = :uniacid ';
	$params = array(':uniacid' => $_W['uniacid']);
	$title = trim($_GPC['keyword']);
	if (!empty($title)) {
		$where .= " AND title LIKE '%{$title}%'";
	}
	
	$list = pdo_fetchall('SELECT * FROM '.tablename('activity_exchange')." $where ORDER BY createtime DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('activity_exchange'). $where , $params);
	$pager = pagination($total, $pindex, $psize);
	foreach ($list as &$row) {
		$extra = iunserializer($row['extra']);
		$row['extra'] = $extra;
		$row['thumb'] = tomedia($row['thumb']);
	}
}
if($do == 'del') {
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$item = pdo_fetch('SELECT id FROM '.tablename('activity_exchange').' WHERE id=:id AND type = 5 AND uniacid=:uniacid',array(':id'=>$id, ':uniacid'=>$_W['uniacid']));
	}
	if(empty($item)) {
		message('删除失败,指定兑换不存在或已删除.');
	}
	pdo_delete('activity_exchange', array('id'=>$id, 'uniacid'=>$_W['uniacid']));
	message('删除成功.', referer(),'success');
}


template('activity/partimes');