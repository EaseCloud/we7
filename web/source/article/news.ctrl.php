<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('category_post', 'category', 'category_del', 'list', 'post', 'batch_post', 'del');
$do = in_array($do, $dos) ? $do : 'list';

if($do == 'category_post') {
	$_W['page']['title'] = '编辑分类-新闻分类';
	if(checksubmit('submit')) {
		$i = 0;
		if(!empty($_GPC['title'])) {
			foreach($_GPC['title'] as $k => $v) {
				$title = trim($v);
				if(empty($title)) {
					continue;
				}
				$data = array(
					'title' => $title,
					'displayorder' => intval($_GPC['displayorder'][$k]),
					'type' => 'news',
				);
				pdo_insert('article_category', $data);
				$i++;
			}
		}
		message('修改文章分类成功', url('article/news/category'), 'success');
	}
	template('article/news-category');
}

if($do == 'category') {
	$_W['page']['title'] = '分类列表-新闻分类';
	if(checksubmit('submit')) {
		if(!empty($_GPC['ids'])) {
			foreach($_GPC['ids'] as $k => $v) {
				$data = array(
					'title' => trim($_GPC['title'][$k]),
					'displayorder' => intval($_GPC['displayorder'][$k])
				);
				pdo_update('article_category', $data, array('id' => intval($v)));
			}
			message('修改新闻分类成功', referer(), 'success');
		}
	}
	$data = pdo_fetchall('SELECT * FROM ' . tablename('article_category') . ' WHERE type = :type ORDER BY displayorder DESC', array(':type' => 'news'));
	template('article/news-category');
}

if($do == 'category_del') {
	$id = intval($_GPC['id']);
	pdo_delete('article_category', array('id' => $id, 'type' => 'news'));
	pdo_delete('article_news', array('cateid' => $id));
	message('删除分类成功', referer(), 'success');
}

if($do == 'post') {
	$_W['page']['title'] = '编辑新闻-新闻列表';
	$id = intval($_GPC['id']);
	$new = pdo_fetch('SELECT * FROM ' . tablename('article_news') . ' WHERE id = :id', array(':id' => $id));
	if(empty($new)) {
		$new = array(
			'is_display' => 1,
			'is_show_home' => 1,
		);
	}
	if(checksubmit()) {
		$title = trim($_GPC['title']) ? trim($_GPC['title']) : message('新闻标题不能为空', '', 'error');
		$cateid = intval($_GPC['cateid']) ? intval($_GPC['cateid']) : message('新闻分类不能为空', '', 'error');
		$content = trim($_GPC['content']) ? trim($_GPC['content']) : message('新闻内容不能为空', '', 'error');
		$data = array(
			'title' => $title,
			'cateid' => $cateid,
			'content' => htmlspecialchars_decode($content),
			'source' => trim($_GPC['source']),
			'author' => trim($_GPC['author']),
			'displayorder' => intval($_GPC['displayorder']),
			'click' => intval($_GPC['click']),
			'is_display' => intval($_GPC['is_display']),
			'is_show_home' => intval($_GPC['is_show_home']),
			'createtime' => TIMESTAMP,
		);
		if (!empty($_GPC['thumb'])) {
			$data['thumb'] = $_GPC['thumb'];
		} elseif (!empty($_GPC['autolitpic'])) {
			$match = array();
			preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $data['content'], $match);
			if (!empty($match[1])) {
				$data['thumb'] = $match[1].$match[2];
			}
		} else {
			$data['thumb'] = '';
		}

		if(!empty($new['id'])) {
			pdo_update('article_news', $data, array('id' => $id));
		} else {
			pdo_insert('article_news', $data);
		}
		message('编辑文章成功', url('article/news/list'), 'success');
	}
	$categorys = pdo_fetchall('SELECT * FROM ' . tablename('article_category') . ' WHERE type = :type ORDER BY displayorder DESC', array(':type' => 'news'));
	template('article/news');
}

if($do == 'list') {
	$_W['page']['title'] = '所有新闻-新闻列表';
	$condition = ' WHERE 1';
	$cateid = intval($_GPC['cateid']);
	$createtime = intval($_GPC['createtime']);
	$title = trim($_GPC['title']);

	$params = array();
	if($cateid > 0) {
		$condition .= ' AND cateid = :cateid';
		$params[':cateid'] = $cateid;
	}
	if($createtime > 0) {
		$condition .= ' AND createtime >= :createtime';
		$params[':createtime'] = strtotime("-{$createtime} days");
	}
	if(!empty($title)) {
		$condition .= " AND title LIKE :title";
		$params[':title'] = "%{$title}%";
	}

	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$sql = 'SELECT * FROM ' . tablename('article_news') . $condition . " ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize .',' .$psize;
	$news = pdo_fetchall($sql, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('article_news') . $condition, $params);
	$pager = pagination($total, $pindex, $psize);

	$categorys = pdo_fetchall('SELECT * FROM ' . tablename('article_category') . ' WHERE type = :type ORDER BY displayorder DESC', array(':type' => 'news'), 'id');
	template('article/news');
}

if($do == 'batch_post') {
	if(checksubmit()) {
		if(!empty($_GPC['ids'])) {
			foreach($_GPC['ids'] as $k => $v) {
				$data = array(
					'title' => trim($_GPC['title'][$k]),
					'displayorder' => intval($_GPC['displayorder'][$k]),
					'click' => intval($_GPC['click'][$k]),
				);
				pdo_update('article_news', $data, array('id' => intval($v)));
			}
			message('编辑新闻列表成功', referer(), 'success');
		}
	}
}

if($do == 'del') {
	$id = intval($_GPC['id']);
	pdo_delete('article_news', array('id' => $id));
	message('删除文章成功', referer(), 'success');
}





