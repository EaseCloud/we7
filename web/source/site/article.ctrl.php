<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('site_article');
$do = in_array($do, array('display', 'post', 'delete')) ? $do : 'display';

$category = pdo_fetchall("SELECT id,parentid,name FROM ".tablename('site_category')." WHERE uniacid = '{$_W['uniacid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
$parent = array();
$children = array();

if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['parentid'])) {
			$children[$cate['parentid']][] = $cate;
		} else {
			$parent[$cate['id']] = $cate;
		}
	}
}

if($do == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
	$params = array();
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND title LIKE :keyword";
		$params[':keyword'] = "%{$_GPC['keyword']}%";
	}
	
	if (!empty($_GPC['category']['childid'])) {
		$cid = intval($_GPC['category']['childid']);
		$condition .= " AND ccate = '{$cid}'";
	} elseif (!empty($_GPC['category']['parentid'])) {
		$cid = intval($_GPC['category']['parentid']);
		$condition .= " AND pcate = '{$cid}'";
	}
	
	$list = pdo_fetchall("SELECT * FROM ".tablename('site_article')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('site_article') . " WHERE uniacid = '{$_W['uniacid']}'".$condition);
	$pager = pagination($total, $pindex, $psize);
	template('site/article');
} elseif($do == 'post') {
	load()->func('file');
	$id = intval($_GPC['id']);
		$template = uni_templates();
	$pcate = $_GPC['pcate'];
	$ccate = $_GPC['ccate'];
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM ".tablename('site_article')." WHERE id = :id" , array(':id' => $id));
		$item['type'] = explode(',', $item['type']);
		$pcate = $item['pcate'];
		$ccate = $item['ccate'];
		if (empty($item)) {
			message('抱歉，文章不存在或是已经删除！', '', 'error');
		}
		$key = pdo_fetchall('SELECT content FROM ' . tablename('rule_keyword') . ' WHERE rid = :rid AND uniacid = :uniacid', array(':rid' => $item['rid'], ':uniacid' => $_W['uniacid']));
		if(!empty($key)) {
			$keywords = array();
			foreach($key as $row) {
				$keywords[] = $row['content'];
			}
			$keywords = implode(',', array_values($keywords));
		}
		$item['credit'] = iunserializer($item['credit']) ? iunserializer($item['credit']) : array();
		if(!empty($item['credit']['limit'])) {
						$credit_num = pdo_fetchcolumn('SELECT SUM(credit_value) FROM ' . tablename('mc_handsel') . ' WHERE uniacid = :uniacid AND module = :module AND sign = :sign', array(':uniacid' => $_W['uniacid'], ':module' => 'article', ':sign' => md5(iserializer(array('id' => $id)))));
			if(is_null($credit_num)) $credit_num = 0;
			$credit_yu = (($item['credit']['limit'] - $credit_num) < 0) ? 0 : $item['credit']['limit'] - $credit_num;
		}
	} else {
		$item['credit'] = array();
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('标题不能为空，请输入标题！');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'iscommend' => intval($_GPC['option']['commend']),
			'ishot' => intval($_GPC['option']['hot']),
			'pcate' => intval($_GPC['category']['parentid']),
			'ccate' => intval($_GPC['category']['childid']),
			'template' => addslashes($_GPC['template']),
			'title' => addslashes($_GPC['title']),
			'description' => addslashes($_GPC['description']),
			'content' => htmlspecialchars_decode($_GPC['content'], ENT_QUOTES),
			'incontent' => intval($_GPC['incontent']),
			'source' => addslashes($_GPC['source']),
			'author' => addslashes($_GPC['author']),
			'displayorder' => intval($_GPC['displayorder']),
			'linkurl' => addslashes($_GPC['linkurl']),
			'createtime' => TIMESTAMP,
			'click' => intval($_GPC['click'])
		);
		if (!empty($_GPC['thumb'])) {
			$data['thumb'] = $_GPC['thumb'];
		} elseif (!empty($_GPC['autolitpic'])) {
			$match = array();
			preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
			if (!empty($match[1])) {
				$data['thumb'] = $match[1].$match[2];
			}
		} else {
			$data['thumb'] = '';
		}
		$keyword = str_replace('，', ',', trim($_GPC['keyword']));
		$keyword = explode(',', $keyword);
		if(!empty($keyword)) {
			$rule['uniacid'] = $_W['uniacid'];
			$rule['name'] = '文章：' . $_GPC['title'] . ' 触发规则';
			$rule['module'] = 'news';
			$rule['status'] = 1;
			$keywords = array();
			foreach($keyword as $key) {
				$key = trim($key);
				if(empty($key)) continue;
				$keywords[] = array(
					'uniacid' => $_W['uniacid'],
					'module' => 'news',
					'content' => $key,
					'status' => 1,
					'type' => 1,
					'displayorder' => 1,
				);
			}
			$reply['title'] = $_GPC['title'];
			$reply['description'] = $_GPC['description'];
			$reply['thumb'] = $_GPC['thumb'];
			$reply['url'] = murl('site/site/detail', array('id' => $id));
		}
				if(!empty($_GPC['credit']['status'])) {
			$credit['status'] = intval($_GPC['credit']['status']);
			$credit['limit'] = intval($_GPC['credit']['limit']) ? intval($_GPC['credit']['limit']) : message('请设置积分上限');
			$credit['share'] = intval($_GPC['credit']['share']) ? intval($_GPC['credit']['share']) : message('请设置分享时赠送积分多少');
			$credit['click'] = intval($_GPC['credit']['click']) ? intval($_GPC['credit']['click']) : message('请设置阅读时赠送积分多少');
			$data['credit'] = iserializer($credit);
		} else {
			$data['credit'] = iserializer(array('status' => 0, 'limit' => 0, 'share' => 0, 'click' => 0));
		}	
		if (empty($id)) {
			if(!empty($keywords)) {
				pdo_insert('rule', $rule);
				$rid = pdo_insertid();
				foreach($keywords as $li) {
					$li['rid'] = $rid;
					pdo_insert('rule_keyword', $li);
				}
				$reply['rid'] = $rid;
				pdo_insert('news_reply', $reply);
				$data['rid'] = $rid;
			}
			pdo_insert('site_article', $data);
			$aid = pdo_insertid();
			pdo_update('news_reply', array('url' => murl('site/site/detail', array('id' => $aid))), array('rid' => $rid));
		} else {
			unset($data['createtime']);
			pdo_delete('rule', array('id' => $item['rid'], 'uniacid' => $_W['uniacid']));
			pdo_delete('rule_keyword', array('rid' => $item['rid'], 'uniacid' => $_W['uniacid']));
			pdo_delete('news_reply', array('rid' => $item['rid']));
			if(!empty($keywords)) {
				pdo_insert('rule', $rule);
				$rid = pdo_insertid();

				foreach($keywords as $li) {
					$li['rid'] = $rid;
					pdo_insert('rule_keyword', $li);
				}

				$reply['rid'] = $rid;
				pdo_insert('news_reply', $reply);
				$data['rid'] = $rid;
			} else {
				$data['rid'] = 0;
				$data['kid'] = 0;
			}
			pdo_update('site_article', $data, array('id' => $id));
		}
		message('文章更新成功！', url('site/article/display'), 'success');
	} else {
		template('site/article');
	}
} elseif($do == 'delete') {
	load()->func('file');
	$id = intval($_GPC['id']);
	$row = pdo_fetch("SELECT id,rid,kid,thumb FROM ".tablename('site_article')." WHERE id = :id", array(':id' => $id));
	if (empty($row)) {
		message('抱歉，文章不存在或是已经被删除！');
	}
	if (!empty($row['thumb'])) {
		file_delete($row['thumb']);
	}
	if(!empty($row['rid'])) {
		pdo_delete('rule', array('id' => $row['rid'], 'uniacid' => $_W['uniacid']));
		pdo_delete('rule_keyword', array('rid' => $row['rid'], 'uniacid' => $_W['uniacid']));
		pdo_delete('news_reply', array('rid' => $row['rid']));
	}
	pdo_delete('site_article', array('id' => $id));
	message('删除成功！', referer(), 'success');
}


