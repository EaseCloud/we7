<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */

defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('platform_qr');
$dos = array('display', 'post', 'list', 'del', 'delsata', 'extend', 'SubDisplay', 'check_scene_str');
$do = !empty($_GPC['do']) && in_array($do, $dos) ? $do : 'list';
load()->model('account');

if($do == 'check_scene_str') {
	$scene_str = trim($_GPC['scene_str']);
	$is_exist = pdo_fetchcolumn('SELECT id FROM ' . tablename('qrcode') . ' WHERE uniacid = :uniacid AND acid = :acid AND scene_str = :scene_str AND model = 2', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid'], ':scene_str' => $scene_str));
	if(!empty($is_exist)) {
		exit('repeat');
	}
	exit('success');
}

if($do == 'list') {
	$_W['page']['title'] = '管理二维码 - 二维码管理 - 高级功能';
	$wheresql = " WHERE uniacid = :uniacid AND acid = :acid AND type = 'scene'";
	$param = array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']);
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$wheresql .= " AND name LIKE '%{$keyword}%'";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$list = pdo_fetchall("SELECT * FROM ".tablename('qrcode'). $wheresql . ' ORDER BY `id` DESC LIMIT '.($pindex - 1) * $psize.','. $psize, $param);
	if (!empty($list)) {
		foreach ($list as $index => &$qrcode) {
			$qrcode['showurl'] = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . urlencode($qrcode['ticket']);
			$qrcode['endtime'] = $qrcode['createtime'] + $qrcode['expire'];
			if (TIMESTAMP > $qrcode['endtime']) {
				$qrcode['endtime'] = '<font color="red">已过期</font>';
			}else{
				$qrcode['endtime'] = date('Y-m-d H:i:s',$qrcode['endtime']);
			}
			if ($qrcode['model'] == 2) {
				$qrcode['modellabel']="永久";
				$qrcode['expire']="永不";
				$qrcode['endtime'] = '<font color="green">永不</font>';
			} else {
				$qrcode['modellabel']="临时";
			}
		}
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qrcode') . $wheresql, $param);
	$pager = pagination($total, $pindex, $psize);
		pdo_query("UPDATE ".tablename('qrcode')." SET status = '0' WHERE uniacid = '{$_W['uniacid']}' AND model = '1' AND createtime < '{$_W['timestamp']}' - expire");
	template('platform/qr-list');
}

if($do == 'del') {
	if ($_GPC['scgq']) {
		$list = pdo_fetchall("SELECT id FROM ".tablename('qrcode')." WHERE uniacid = :uniacid AND acid = :acid AND status = '0' AND type='scene'", array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid']), 'id');
		if (!empty($list)) {
			pdo_query("DELETE FROM ".tablename('qrcode')." WHERE id IN (".implode(',', array_keys($list)).")");
			pdo_query("DELETE FROM ".tablename('qrcode_stat')." WHERE qid IN (".implode(',', array_keys($list)).")");
		}
		message('执行成功<br />删除二维码：'.count($list), url('platform/qr/list'),'success');
	}else{
		$id = $_GPC['id'];
		pdo_delete('qrcode', array('id' =>$id, 'uniacid' => $_W['uniacid']));
		pdo_delete('qrcode_stat',array('qid' => $id, 'uniacid' => $_W['uniacid']));
		message('删除成功',url('platform/qr/list'),'success');
	}
}

if($do == 'post') {
	$_W['page']['title'] = '生成二维码 - 二维码管理 - 高级功能';
	load()->func('communication');
	if(checksubmit('submit')){
				$barcode = array(
			'expire_seconds' => '',
			'action_name' => '',
			'action_info' => array(
				'scene' => array(),
			),
		);
		$qrctype = intval($_GPC['qrc-model']);
		$acid = intval($_W['acid']);
		$uniacccount = WeAccount::create($acid);
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$update = array(
				'keyword' => trim($_GPC['keyword']),
				'name' => trim($_GPC['scene-name'])
			);
			pdo_update('qrcode', $update, array('uniacid' => $_W['uniacid'], 'id' => $id));
			message('恭喜，更新带参数二维码成功！', url('platform/qr/list'), 'success');
		}
	
		if ($qrctype == 1) {
			$qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename('qrcode')." WHERE acid = :acid AND model = '1' ORDER BY qrcid DESC LIMIT 1", array(':acid' => $acid));
			$barcode['action_info']['scene']['scene_id'] = !empty($qrcid) ? ($qrcid + 1) : 100001;
			$barcode['expire_seconds'] = intval($_GPC['expire-seconds']);
			$barcode['action_name'] = 'QR_SCENE';
			$result = $uniacccount->barCodeCreateDisposable($barcode);
		} else if ($qrctype == 2) {
			$scene_str = trim($_GPC['scene_str']) ? trim($_GPC['scene_str'])  : message('场景值不能为空');
			$is_exist = pdo_fetchcolumn('SELECT id FROM ' . tablename('qrcode') . ' WHERE uniacid = :uniacid AND acid = :acid AND scene_str = :scene_str AND model = 2', array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid'], ':scene_str' => $scene_str));
			if(!empty($is_exist)) {
				message("场景值:{$scene_str}已经存在,请更换场景值");
			}
			$barcode['action_info']['scene']['scene_str'] = $scene_str;
			$barcode['action_name'] = 'QR_LIMIT_STR_SCENE';
			$result = $uniacccount->barCodeCreateFixed($barcode);
		} else {
			message('抱歉，此公众号暂不支持您请求的二维码类型！');
		}
		
		if(!is_error($result)) {
			$insert = array(
				'uniacid' => $_W['uniacid'],
				'acid' => $acid,
				'qrcid' => $barcode['action_info']['scene']['scene_id'],
				'scene_str' => $barcode['action_info']['scene']['scene_str'],
				'keyword' => $_GPC['keyword'],
				'name' => $_GPC['scene-name'],
				'model' => $_GPC['qrc-model'],
				'ticket' => $result['ticket'],
				'url' => $result['url'],
				'expire' => $result['expire_seconds'],
				'createtime' => TIMESTAMP,
				'status' => '1',
				'type' => 'scene',
			);
			pdo_insert('qrcode', $insert);
			message('恭喜，生成带参数二维码成功！', url('platform/qr/list', array('name' => 'qrcode')), 'success');
		} else {
			message("公众平台返回接口错误. <br />错误代码为: {$result['errorcode']} <br />错误信息为: {$result['message']}");
		}
	}

	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE uniacid = {$_W['uniacid']} AND id = '{$id}'");
	template('platform/qr-post');
}

if($do == 'extend') {
	load()->func('communication');
	$id = intval($_GPC['id']);
	if (!empty($id)) {
		$qrcrow = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE uniacid = :uniacid AND id = :id LIMIT 1", array(':uniacid' => $_W['uniacid'], ':id' => $id));
		$update = array();
		if ($qrcrow['model'] == 1) {
			$uniacccount = WeAccount::create($qrcrow['acid']);
			$barcode['action_info']['scene']['scene_id'] = $qrcrow['qrcid'];
			$barcode['expire_seconds'] = 2592000;
			$barcode['action_name'] = 'QR_SCENE';
			$result = $uniacccount->barCodeCreateDisposable($barcode);
			if(is_error($result)) {
				message($result['message'], '', 'error');
			}
			$update['ticket'] = $result['ticket'];
			$update['url'] = $result['url'];
			$update['expire'] = $result['expire_seconds'];
			$update['createtime'] = TIMESTAMP;
			pdo_update('qrcode', $update, array('id' => $id, 'uniacid' => $_W['uniacid']));
		}
		message('恭喜，延长临时二维码时间成功！', referer(), 'success');
	}
}

if($do == 'display') {
	$_W['page']['title'] = '扫描统计 - 二维码管理 - 高级功能';
	$starttime = empty($_GPC['time']['start']) ? TIMESTAMP -  86399 * 30 : strtotime($_GPC['time']['start']);
	$endtime = empty($_GPC['time']['end']) ? TIMESTAMP + 6*86400: strtotime($_GPC['time']['end']) + 86399;
	$where .= " WHERE uniacid = :uniacid AND acid = :acid AND createtime >= :starttime AND createtime < :endtime";
	$param = array(':uniacid' => $_W['uniacid'], ':acid' => $_W['acid'], ':starttime' => $starttime, ':endtime' => $endtime);
	!empty($_GPC['keyword']) && $where .= " AND name LIKE '%{$_GPC['keyword']}%'";
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('qrcode_stat'). $where, $param);
	$list = pdo_fetchall("SELECT * FROM ".tablename('qrcode_stat')." $where ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','. $psize, $param);
	if (!empty($list)) {
		$openid = array();
		foreach ($list as $index => &$qrcode) {
			if ($qrcode['type'] == 1) {
				$qrcode['type']="关注";
			} else {
				$qrcode['type']="扫描";
			}
			if(!in_array($qrcode['openid'], $openid)) {
				$openid[] = $qrcode['openid'];
			}
		}
		$openids = implode("','", $openid);
		$param_temp[':uniacid'] = $_W['uniacid'];
		$param_temp[':acid'] = $_W['acid'];
		$nickname = pdo_fetchall('SELECT nickname, openid FROM ' . tablename('mc_mapping_fans') . " WHERE uniacid = :uniacid AND acid = :acid AND openid IN ('{$openids}')", $param_temp, 'openid');
	}
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qrcode_stat') . $where, $param);
	$pager = pagination($total, $pindex, $psize);
	template('platform/qr-display');
}

if($do == 'delsata') {
	$id = $_GPC['id'];
	$b = pdo_delete('qrcode_stat',array('id' =>$id, 'uniacid' => $_W['uniacid']));
	if ($b){
		message('删除成功',url('platform/qr/display'),'success');
	}else{
		message('删除失败',url('platform/qr/display'),'error');
	}
}
