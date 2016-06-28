<?php
global $_W, $_GPC;
load()->func('tpl');
$article_sys = pdo_fetch("select * from" . tablename('sz_yi_article_sys') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
$article_sys['article_image'] = tomedia($article_sys['article_image']);
if ($article_sys['article_temp'] == 0) {
	$limit = empty($article_sys['article_shownum']) ? '10' : $article_sys['article_shownum'];
	$articles = pdo_fetchall("SELECT id,article_title,resp_img,article_rule_credit,article_rule_money,article_date FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid order by article_date_v desc limit " . $limit, array(':uniacid' => $_W['uniacid']));
} elseif ($article_sys['article_temp'] == 1) {
	$limit = empty($article_sys['article_shownum']) ? '7' : $article_sys['article_shownum'];
	$articles = pdo_fetchall("SELECT distinct article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid order by article_date_v desc limit " . $limit, array(':uniacid' => $_W['uniacid']), 'article_date_v');
	foreach ($articles as &$a) {
		$a['articles'] = pdo_fetchall("SELECT id,article_title,article_date_v,resp_img,resp_desc,article_date_v FROM " . tablename('sz_yi_article') . " WHERE article_state=1 and uniacid=:uniacid and article_date_v=:article_date_v order by article_date desc ", array(':uniacid' => $_W['uniacid'], ':article_date_v' => $a['article_date_v']));
	}
	unset($a);
} elseif ($article_sys['article_temp'] == 2) {
	$categorys = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_article_category') . " WHERE uniacid=:uniacid ", array(':uniacid' => $_W['uniacid']));
}
include $this->template('list');
