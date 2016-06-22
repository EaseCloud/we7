<?php
global $_W, $_GPC;

load()->func('tpl');
$aid = intval($_GPC['aid']);
if (!empty($aid)) {
	$article = pdo_fetch("SELECT * FROM " . tablename('sz_yi_article') . " WHERE id=:aid and article_state=1 and uniacid=:uniacid limit 1 ", array(':aid' => $aid, ':uniacid' => $_W['uniacid']));
	if (!empty($article)) {
		$article['article_content'] = $this->model->mid_replace(htmlspecialchars_decode($article['article_content']));
		$readnum = $article['article_readnum'] + $article['article_readnum_v'];
		$readnum = $readnum > 100000 ? '100000+' : $readnum;
		$likenum = $article['article_likenum'] + $article['article_likenum_v'];
		$likenum = $likenum > 100000 ? '100000+' : $likenum;
		if (empty($article['article_mp'])) {
			$mp = pdo_fetch("SELECT acid,uniacid,name FROM " . tablename('account_wechats') . " WHERE uniacid=:uniacid limit 1 ", array(':uniacid' => $_W['uniacid']));
			$article['article_mp'] = $mp['name'];
		}
		$shop = m('common')->getSysset(array('shop', 'share'));
		$openid = m('user')->getOpenid();
		if (!empty($openid)) {
			$state = pdo_fetch("SELECT * FROM " . tablename('sz_yi_article_log') . " WHERE openid=:openid and aid=:aid and uniacid=:uniacid limit 1 ", array(':openid' => $openid, ':aid' => $article['id'], ':uniacid' => $_W['uniacid']));
			if (empty($state['id'])) {
				$insert = array('aid' => $aid, 'read' => 1, 'uniacid' => $_W['uniacid'], 'openid' => $openid);
				pdo_insert('sz_yi_article_log', $insert);
				$sid = pdo_insertid();
				pdo_update('sz_yi_article', array('article_readnum' => $article['article_readnum'] + 1), array('id' => $article['id']));
			} else {
				if ($state['read'] < 4) {
					pdo_update('sz_yi_article_log', array('read' => $state['read'] + 1), array('id' => $state['id']));
					pdo_update('sz_yi_article', array('article_readnum' => $article['article_readnum'] + 1), array('id' => $article['id']));
				}
			}
		}
		$article['product_advs'] = htmlspecialchars_decode($article['product_advs']);
		$advs = json_decode($article['product_advs'], true);
		foreach ($advs as $i => &$v) {
			$v['link'] = $this->model->href_replace($v['link']);
		}
		unset($v);
		$article['product_advs_link'] = $this->model->href_replace($article['product_advs_link']);
		$article['article_linkurl'] = $this->model->href_replace($article['article_linkurl']);
		if (!empty($advs)) {
			$advnum = count($advs);
			if ($article['product_advs_type'] == 1) {
				$advrand = 0;
			} elseif ($article['product_advs_type'] == 2) {
				$advrand = rand(0, $advnum - 1);
			} elseif ($article['product_advs_type'] == 3 && $advnum >= 1) {
				$advrand = -1;
			}
		}
		$myid = m('member')->getMid();
		$shareid = intval($_GPC['shareid']);
		echo $doShare = $this->model->doShare($article, $shareid, $myid);
		$_W['shopshare'] = array('title' => $article['article_title'], 'imgUrl' => $article['resp_img'], 'desc' => $article['resp_desc'], 'link' => $this->createPluginMobileUrl('article', array('aid' => $article['id'], 'directopenid' => 1, 'shareid' => $myid)));
		if (p('commission')) {
			$set = p('commission')->getSet();
			if (!empty($set['level'])) {
				$member = m('member')->getMember($openid);
				if (!empty($member) && $member['status'] == 1 && $member['isagent'] == 1) {
					$_W['shopshare']['link'] = $this->createPluginMobileUrl('article', array('directopenid' => 1, 'aid' => $article['id'], 'shareid' => $myid, 'mid' => $member['id']));
				} else if (!empty($_GPC['mid'])) {
					$_W['shopshare']['link'] = $this->createPluginMobileUrl('article', array('directopenid' => 1, 'aid' => $article['id'], 'shareid' => $myid, 'mid' => $_GPC['mid']));
				}
			}
		}
	} else {
		die('没有查询到文章信息！请检查URL后重试！');
	}
} else {
	die('url参数错误！');
}
include $this->template('index');
