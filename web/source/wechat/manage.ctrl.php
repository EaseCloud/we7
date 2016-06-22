<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('wechat_manage');
$dos = array('display', 'location_post', 'logo', 'location_list','location_view', 'location_del', 'whitelist', 'location_edit', 'export', 'location_sync');
$do = in_array($do, $dos) ? $do : 'logo';
$acid = $_W['acid'];
if($do == 'logo') {
	$coupon_setting = pdo_fetch('SELECT * FROM ' . tablename('coupon_setting') . ' WHERE uniacid = :aid AND acid = :acid', array(':aid' => $_W['uniacid'], ':acid' => $acid));
	if(checksubmit('submit')) {
		$_GPC['logo'] = trim($_GPC['logo']);
		empty($_GPC['logo']) && message('请上传商户logo', referer(), 'info');
		$data = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $acid,
			'logourl' => $_GPC['logo'],
		);
		if(empty($coupon_setting)) {
			pdo_insert('coupon_setting', $data);
		} else {
			pdo_update('coupon_setting', $data, array('uniacid' => $_W['uniacid']));
		}
		message('上传商户LOGO成功', referer(), 'success');
	}
}

if($do == 'location_post') {
	if(checksubmit('submit')) {
		$data['business_name'] = trim($_GPC['business_name']) ? urlencode(trim($_GPC['business_name'])) : message('门店名称不能为空');
		$data['branch_name'] = urlencode(trim($_GPC['branch_name']));
		$cate = array();
		$in_cate = array();
		if(!empty($_GPC['class']['cate']) && !empty($_GPC['class']['sub'])) {
			$class = array($_GPC['class']['cate'], $_GPC['class']['sub']);
			$in_cate = array('cate' => $_GPC['class']['cate'], 'sub' => $_GPC['class']['sub']);
			if(!empty($_GPC['class']['clas'])) {
				$class[] = $_GPC['class']['clas'];
				$in_cate['clas'] = $_GPC['class']['clas'];
			}
			$cate = array(urlencode(implode($class, ',')));
		}
		if(empty($cate)) {
			message('门店类目不能为空');
		}
		$data['categories'] = $cate;
		$data['province'] = trim($_GPC['reside']['province']) ? urlencode(trim($_GPC['reside']['province'])) : message('请选择门店所在省');
		$data['city'] = trim($_GPC['reside']['city']) ? urlencode(trim($_GPC['reside']['city'])) : message('请选择门店所在市');
		$data['district'] = trim($_GPC['reside']['district']) ? urlencode(trim($_GPC['reside']['district'])) : message('请选择门店所在区');
		$data['address'] = trim($_GPC['address']) ? urlencode(trim($_GPC['address'])) : message('门店详细地址不能为空');
		$data['longitude'] = trim($_GPC['baidumap']['lng']) ? trim($_GPC['baidumap']['lng']) : message('请选择门店所在地理位置经度');
		$data['latitude'] = trim($_GPC['baidumap']['lat']) ? trim($_GPC['baidumap']['lat']) : message('请选择门店所在地理位置维度');
		$data['telephone'] = trim($_GPC['telephone']) ? trim($_GPC['telephone']) : message('门店电话不能为空');
		if(empty($_GPC['photo_list'])) {
			message('门店图片不能为空');
		} else {
			foreach($_GPC['photo_list'] as $val) {
				if(empty($val)) continue;
				$data['photo_list'][] = array('photo_url' => $val);
			}
		}
		$data['avg_price'] = intval($_GPC['avg_price']);
		if(empty($_GPC['open_time_start']) || empty($_GPC['open_time_end'])) {
			message('营业时间不能为空');
		} else {
			$data['open_time'] = $_GPC['open_time_start'] . '-' . $_GPC['open_time_end'];
		}
		$data['recommend'] = urlencode(trim($_GPC['recommend']));
		$data['special'] = trim($_GPC['special']) ? urlencode(trim($_GPC['special'])) : message('特色服务不能为空');
		$data['introduction'] = urlencode(trim($_GPC['introduction']));
		$data['offset_type'] = 1;
		$data['sid'] = TIMESTAMP;
		load()->classs('coupon');
		$acc = new coupon($acid);
		$status = $acc->LocationAdd($data);

		if(is_error($status)) {
			message($status['message'], '', 'error');
		}
		$insert['uniacid'] = $_W['uniacid'];
		$insert['acid'] = $acid;
		$insert['sid'] = $data['sid'];
		$insert['business_name'] = trim($_GPC['business_name']);
		$insert['branch_name'] = trim($_GPC['branch_name']);
		$insert['category'] = iserializer($in_cate);
		$insert['province'] = trim($_GPC['reside']['province']);
		$insert['city'] = trim($_GPC['reside']['city']);
		$insert['district'] = trim($_GPC['reside']['district']);
		$insert['address'] = trim($_GPC['address']);
		$insert['longitude'] = trim($_GPC['baidumap']['lng']);
		$insert['latitude'] = trim($_GPC['baidumap']['lat']);
		$insert['telephone'] = trim($_GPC['telephone']);
		$insert['location_id'] = $status['location_id_list'][0];
		$insert['photo_list'] = iserializer($data['photo_list']);
		$insert['avg_price'] = intval($_GPC['avg_price']);
		$insert['open_time'] = $_GPC['open_time_start'] . '-' . $_GPC['open_time_end'];
		$insert['recommend'] = trim($_GPC['recommend']);
		$insert['special'] = trim($_GPC['special']);
		$insert['introduction'] = trim($_GPC['introduction']);
		$insert['offset_type'] = 1;
		$insert['status'] = 2;
				pdo_insert('coupon_location', $insert);
		$id = pdo_insertid();
		message('添加门店成功', url('wechat/manage/location_list'), 'success');
	}
}

if($do == 'location_edit') {
	$id = intval($_GPC['id']);
	$location = pdo_fetch('SELECT * FROM ' . tablename('coupon_location') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $id));
	if(empty($location)) {
		message('门店不存在或已删除', referer(), 'error');
	}
	load()->classs('coupon');
	$acc = new coupon($acid);
	$location = $acc->LocationGet($location['location_id']);
	if(is_error($location)) {
		message("从微信获取门店信息失败,错误详情:{$location['message']}", referer(), 'error');
	}
	$update_status = $location['business']['base_info']['update_status'];

	if(checksubmit('submit')) {
		if(empty($location['location_id'])) {
			message('门店正在审核中或审核未通过，不能更新门店信息', referer(), 'error');
		}
		if($update_status == 1) {
			message('服务信息正在更新中，尚未生效，不允许再次更新', referer(), 'error');
		}
		$data['telephone'] = trim($_GPC['telephone']) ? trim($_GPC['telephone']) : message('门店电话不能为空');
		if(empty($_GPC['photo_list'])) {
			message('门店图片不能为空');
		} else {
			foreach($_GPC['photo_list'] as $val) {
				if(empty($val)) continue;
				$data['photo_list'][] = array('photo_url' => $val);
			}
		}
		$data['avg_price'] = intval($_GPC['avg_price']);
		if(empty($_GPC['open_time_start']) || empty($_GPC['open_time_end'])) {
			message('营业时间不能为空');
		} else {
			$data['open_time'] = $_GPC['open_time_start'] . '-' . $_GPC['open_time_end'];
		}
		$data['recommend'] = urlencode(trim($_GPC['recommend']));
		$data['special'] = trim($_GPC['special']) ? urlencode(trim($_GPC['special'])) : message('特色服务不能为空');
		$data['introduction'] = urlencode(trim($_GPC['introduction']));
		$data['poi_id'] = $location['location_id'];
		load()->classs('coupon');
		$acc = new coupon($acid);
		$status = $acc->LocationEdit($data);
		if(is_error($status)) {
			message($status['message'], '', 'error');
		}
	}
	$location = $location['business']['base_info'];
	$status2local = array('', 3, 2, 1, 3);
	$location['status'] = $status2local[$location['available_state']];
	$location['location_id'] = $location['poi_id'];
	$category_temp = explode(',', $location['categories'][0]);
	$location['category'] = iserializer(array('cate' => $category_temp[0], 'sub' => $category_temp[1], 'clas' => $category_temp[2]));
	$location['photo_list'] = iserializer($location['photo_list']);
	unset($location['sid'], $location['categories'], $location['poi_id'], $location['update_status'], $location['available_state']);
	pdo_update('coupon_location', $location, array('acid' => $acid, 'id' => $id));

	$location = array();
	$location = pdo_fetch('SELECT * FROM ' . tablename('coupon_location') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $id));
	$location['open_time_start'] = '8:00';
	$location['open_time_end'] = '24:00';
	$open_time = explode('-', $location['open_time']);
	if(!empty($open_time)) {
		$location['open_time_start'] = $open_time[0];
		$location['open_time_end'] = $open_time[1];
	}
	$location['category'] = iunserializer($location['category']);
	$location['category'] = implode('-', $location['category']);
	$location['address'] = $location['provice'].$location['city'].$location['district'].$location['address'];
	$location['baidumap'] = array('lng' => $location['longitude'], 'lat' => $location['latitude']);
	$photo_lists = iunserializer($location['photo_list']);
	$location['photo_list'] = array();
	if(!empty($photo_lists)) {
		foreach($photo_lists as $li) {
			if(!empty($li['photo_url'])) {
				$location['photo_list'][] = $li['photo_url'];
			}
		}
	}
}

if($do == 'location_list') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' WHERE `uniacid`=:uniacid AND acid = :aid';
	$pars = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$pars[':aid'] = $acid;
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$condition .= " AND (business_name LIKE '%{$keyword}%' OR address LIKE '%{$keyword}%')";
	}

	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('coupon_location').$condition, $pars);
	$list = pdo_fetchall("SELECT * FROM ".tablename('coupon_location') . $condition ." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $pars);
	if(!empty($list)) {
		foreach($list as &$li) {
			$temp = iunserializer($li['category']);
			$li['category_'] = implode('-', $temp);
		}
	}
	$pager = pagination($total, $pindex, $psize);
}

if($do == 'location_del') {
	$id = intval($_GPC['id']);
	$location = pdo_fetch('SELECT status,location_id FROM ' . tablename('coupon_location') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $id));
	if(!empty($location['location_id'])) {
		load()->classs('coupon');
		$acc = new coupon($acid);
		$status = $acc->LocationDel($location['location_id']);
	}
	pdo_delete('coupon_location', array('uniacid' => $_W['uniacid'], 'acid' => $acid, 'id' => $id));
	if(is_error($status)) {
		message("删除本地门店数据成功<br>通过微信接口删除微信门店数据失败,请登陆微信公众平台手动删除门店<br>错误原因：{$status['message']}", url('wechat/manage/location_list'), 'error');
	}
	message('删除门店成功', url('wechat/manage/location_list'), 'success');
}

if($do == 'export') {
	load()->classs('coupon');
	$acc = new coupon($acid);
	$location = $acc->LocationBatchGet();
	if(is_error($location)) {
		message($location['message'], referer(), 'error');
	}
	$location = $location['business_list'];
	$status2local = array('', 3, 2, 1, 3);
	if(!empty($location)) {
		foreach($location as $row) {
			$li = $row['base_info'];
						if($li['available_state'] == 3 && !empty($li['poi_id']) && empty($li['sid'])) {
				$isexist = pdo_fetchcolumn('SELECT id FROM ' . tablename('coupon_location') . ' WHERE uniacid = :uniacid AND acid = :acid AND location_id = :location_id', array(':uniacid' => $_W['uniacid'], ':acid' => $acid, ':location_id' => $li['poi_id']));
				if(empty($isexist)) {
					$li['uniacid'] = $_W['uniacid'];
					$li['acid'] = $acid;
					$li['status'] = 1;
					$li['location_id'] = $li['poi_id'];
					$category_temp = explode(',', $li['categories'][0]);
					$li['category'] = iserializer(array('cate' => $category_temp[0], 'sub' => $category_temp[1], 'clas' => $category_temp[2]));
					$li['photo_list'] = iserializer($li['photo_list']);
					unset($li['sid'], $li['categories'], $li['poi_id'], $li['update_status'], $li['available_state']);
					pdo_insert('coupon_location', $li);
				}
			} else {
				$data = pdo_fetch('SELECT id,sid FROM ' . tablename('coupon_location') . ' WHERE uniacid = :uniacid AND acid = :acid AND (sid = :sid OR id = :sid)', array(':uniacid' => $_W['uniacid'], ':acid' => $acid, ':sid' => $li['sid']));
				$li['uniacid'] = $_W['uniacid'];
				$li['acid'] = $acid;
				$li['status'] = $status2local[$li['available_state']];
				$li['location_id'] = $li['poi_id'];
				$category_temp = explode(',', $li['categories'][0]);
				$li['category'] = iserializer(array('cate' => $category_temp[0], 'sub' => $category_temp[1], 'clas' => $category_temp[2]));
				$li['photo_list'] = iserializer($li['photo_list']);
				unset($li['categories'], $li['poi_id'], $li['update_status'], $li['available_state']);
				if(empty($isexist)) {
					pdo_insert('coupon_location', $li);
				} else {
					if($data['id'] == $li['sid']) {
						pdo_update('coupon_location', $li, array('acid' => $acid, 'id' => $li['sid']));
					} else {
						pdo_update('coupon_location', $li, array('acid' => $acid, 'sid' => $li['sid']));
					}
				}
			}
		}
	}
	message('导入门店成功', referer(), 'success');
}

if($do == 'location_sync') {
	$id = intval($_GPC['id']);
	$location = pdo_fetch('SELECT * FROM ' . tablename('coupon_location') . ' WHERE uniacid = :aid AND id = :id', array(':aid' => $_W['uniacid'], ':id' => $id));
	if(empty($location)) {
		message('门店不存在或已删除', referer(), 'error');
	}
	load()->classs('coupon');
	$acc = new coupon($acid);
	$location = $acc->LocationGet($location['location_id']);
	if(is_error($location)) {
		message("获取门店信息失败,错误详情:{$location['message']}", referer(), 'error');
	}
	$location = $location['business']['base_info'];

	$status2local = array('', 3, 2, 1, 3);
	$location['status'] = $status2local[$location['available_state']];
	$location['location_id'] = $location['poi_id'];
	$category_temp = explode(',', $location['categories'][0]);
	$location['category'] = iserializer(array('cate' => $category_temp[0], 'sub' => $category_temp[1], 'clas' => $category_temp[2]));
	$location['photo_list'] = iserializer($location['photo_list']);
	unset($location['sid'], $location['categories'], $location['poi_id'], $location['update_status'], $location['available_state']);
	pdo_update('coupon_location', $location, array('acid' => $acid, 'id' => $id));
	message('更新门店信息成功', referer(), 'success');
}

if($do == 'whitelist') {
	$whitelist = pdo_fetchcolumn('SELECT whitelist FROM ' . tablename('coupon_setting') . ' WHERE uniacid = :aid AND acid = :acid', array(':aid' => $_W['uniacid'], ':acid' => $acid));
	if(!empty($whitelist)) {
		$whitelist = @iunserializer($whitelist);
	}
	if(checksubmit('submit')) {
		if(!empty($_GPC['username'])) {
			$data = array();
			foreach($_GPC['username'] as $da) {
				$da = trim($da);
				if(empty($da)) {
					continue;
				}
				$i++;
				$data[] = trim($da);
				if($i >= 10) {
					break;
				}
			}
		}

		load()->classs('coupon');
		$acc = new coupon($acid);
		$post['username'] = $data;
		$status = $acc->SetTestWhiteList($post);
		if(is_error($status)) {
			message($status['message'], '', 'error');
		} else {
			$data = iserializer($data);
			pdo_update('coupon_setting', array('whitelist' => $data), array('uniacid' => $_W['uniacid'], 'acid' => $acid));
		}

		message('设置测试白名单成功', referer(), 'success');
	}
}
template('wechat/manage');