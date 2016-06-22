<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);
$dos = array('list');
if (!in_array($do, array('list'))) {
	exit('Access Denied');
}

if($do == 'list') {
	$type = trim($_GPC['type']);
	$condition = " WHERE uniacid = :uniacid AND type = :type AND model = :model AND media_id != ''";
	$params = array(':uniacid' => $_W['uniacid'], ':type' => $type, ':model' => 'perm');
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	if($type == 'image') {
		$psize = 50;
	}
	$limit = " ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ", {$psize}";
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('wechat_attachment') . $condition, $params);
	$lists = pdo_fetchall('SELECT * FROM ' . tablename('wechat_attachment') . $condition . $limit, $params, 'id');
	if(!empty($lists)) {
		foreach($lists as &$row) {
			if($type == 'video') {
				$row['tag'] = iunserializer($row['tag']);
				$row['attach'] = tomedia($row['attachment'], true);
			} elseif($type == 'news') {
				$row['items'] = pdo_getall('wechat_news', array('uniacid' => $_W['uniacid'], 'attach_id' => $row['id']));
				if(!empty($row['items'])) {
					foreach($row['items'] as &$li) {
						$li['thumb_url'] =  url('utility/wxcode/image', array('attach' => $li['thumb_url']));
					}
				}
			} elseif($type == 'image') {
				$row['attach'] = tomedia($row['attachment'], true);
				$row['url'] = "url({$row['attach']})";
			} elseif($type == 'voice') {
				$row['attach'] = tomedia($row['attachment'], true);
			}
			$row['createtime_cn'] = date('Y-m-d H:i', $row['createtime']);
		}
	}
	$result = array(
		'items' => $lists,
		'pager' => pagination($total, $pindex, $psize, '', array('before' => '2', 'after' => '3', 'ajaxcallback'=>'null')),
	);
	message($result, '', 'ajax');
}
