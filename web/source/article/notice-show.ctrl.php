<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array( 'detail');
$do = in_array($do, $dos) ? $do : 'list';
load()->model('article');
load()->model('user');

if($do == 'detail') {
	$id = intval($_GPC['id']);
	$notice = article_notice_info($id);
	if(is_error($notice)) {
		message('公告不存在或已删除', referer(), 'error');
	}
	$_W['page']['title'] = $notice['title'] . '-公告列表';
	pdo_query('UPDATE ' . tablename('article_notice') . ' SET click = click + 1 WHERE id = :id', array(':id' => $id));
	if(!empty($_W['uid'])) {
		pdo_update('article_unread_notice', array('is_new' => 0), array('notice_id' => $id, 'uid' => $_W['uid']));
	}
	$title = $notice['title'];
}

if($do == 'list') {
	$_W['page']['title'] = '-新闻列表';
	$categroys = article_categorys('notice');
	$categroys[0] = array('title' => '所有公告');

	$cateid = intval($_GPC['cateid']);
	$_W['page']['title'] = $categroys[$cateid]['title'] . '-公告列表';

	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$filter = array('cateid' => $cateid);
	$notices = article_notice_all($filter, $pindex, $psize);
	$total = intval($notices['total']);
	$data = $notices['notice'];
	$pager = pagination($total, $pindex, $psize);
}

template('article/notice-show');
