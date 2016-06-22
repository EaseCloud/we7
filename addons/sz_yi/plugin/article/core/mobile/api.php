<?php
global $_W, $_GPC;

$apido = $_GPC['apido'];
if ($_W['isajax'] && $_W['ispost']) {
	if ($apido == 'selectlike') {
		$aid = intval($_GPC['aid']);
		if (!empty($aid) && !empty($_W['openid'])) {
			$state = pdo_fetch("SELECT * FROM " . tablename('sz_yi_article_log') . " WHERE openid=:openid and aid=:aid and uniacid=:uniacid limit 1 ", array(':openid' => $_W['openid'], ':aid' => $aid, ':uniacid' => $_W['uniacid']));
			if (empty($state['like'])) {
				pdo_update('sz_yi_article', 'article_likenum=article_likenum+1', array('id' => $aid));
				pdo_update('sz_yi_article_log', array('like' => $state['like'] + 1), array('id' => $state['id']));
				die(json_encode(array('result' => 'success-like')));
			} else {
				pdo_update('sz_yi_article', 'article_likenum=article_likenum-1', array('id' => $aid));
				pdo_update('sz_yi_article_log', array('like' => $state['like'] - 1), array('id' => $state['id']));
				die(json_encode(array('result' => 'success-nolike')));
			}
		}
	} elseif ($apido == 'addmore') {
		$article_sys = pdo_fetch("select * from" . tablename('sz_yi_article_sys') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
		if ($article_sys['article_temp'] == 0) {
			$pindex = max(1, intval($_GPC['page']));
			$psize = empty($article_sys['article_shownum']) ? '10' : $article_sys['article_shownum'];
			$articles = pdo_fetchall("SELECT id,article_title,resp_img,article_rule_credit,article_rule_money FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid limit " . (($pindex - 1) * $psize . ',' . $psize), array(':uniacid' => $_W['uniacid']));
		} elseif ($article_sys['article_temp'] == 1) {
			$pindex = max(1, intval($_GPC['page']));
			$psize = empty($article_sys['article_shownum']) ? '10' : $article_sys['article_shownum'];
			$articles = pdo_fetchall("SELECT distinct article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid order by article_date_v desc limit " . (($pindex - 1) * $psize . ',' . $psize), array(':uniacid' => $_W['uniacid']), 'article_date_v');
			foreach ($articles as &$a) {
				$a['articles'] = pdo_fetchall("SELECT id,article_title,article_date_v,resp_img,resp_desc,article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid and article_date_v=:article_date_v order by article_date desc ", array(':uniacid' => $_W['uniacid'], ':article_date_v' => $a['article_date_v']));
			}
			unset($a);
		} elseif ($article_sys['article_temp'] == 2) {
			$cate = intval($_GPC['cate']);
			$where = '';
			if ($cate > 0) {
				$where = ' and article_category=' . $cate . ' ';
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = empty($article_sys['article_shownum']) ? '10' : $article_sys['article_shownum'];
			$articles = pdo_fetchall("SELECT id,article_title,resp_img,article_rule_credit,article_rule_money,article_author,article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid " . $where . " order by article_date_v desc limit " . (($pindex - 1) * $psize . ',' . $psize), array(':uniacid' => $_W['uniacid']));
		}
		if (!empty($articles)) {
			include $this->template('more');
		}
	} elseif ($apido == 'sendreport') {
		$aid = intval($_GPC['aid']);
		$cate = $_GPC['cate'];
		$cons = $_GPC['cons'];
		$mid = m('member')->getMid();
		$openid = m('user')->getOpenid();
		$insert = array('mid' => $mid, 'openid' => $openid, 'aid' => $aid, 'cate' => $cate, 'cons' => $cons, 'uniacid' => $_W['uniacid']);
		pdo_insert('sz_yi_article_report', $insert);
		die(json_encode(array('result' => 'success')));
	} elseif ($apido == 'selectarticle') {
		$article_sys = pdo_fetch("select * from" . tablename('sz_yi_article_sys') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
		$cid = intval($_GPC['cid']);
		$where = '';
		if ($cid > 0) {
			$where = ' and article_category=' . $cid . ' ';
		}
		$limit = empty($article_sys['article_shownum']) ? '10' : $article_sys['article_shownum'];
		$articles = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid " . $where . " order by article_date_v desc limit " . $limit, array(':uniacid' => $_W['uniacid']));
		if (!empty($articles)) {
			include $this->template('more');
		}
	}
}
