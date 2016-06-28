<?php
global $_W, $_GPC;
//check_shop_auth('http://120.26.212.219/api.php', $this->pluginname);
load()->func('tpl');
// echo 'hello';exit;
$aid = intval($_GPC['aid']);
if (!empty($aid)) {
	$article = pdo_fetch("SELECT * FROM " . tablename('sz_yi_article') . " WHERE id=:aid and article_state=1 and uniacid=:uniacid limit 1 ", array(':aid' => $aid, ':uniacid' => $_W['uniacid']));
	
	if (!empty($article)) {
		
		//根据地区设置文章权限：@phpdb.net
		//读取文章的设置：
		$article_sys = pdo_fetch("select * from" . tablename('sz_yi_article_sys') . "where uniacid=:uniacid", array(':uniacid' => $_W['uniacid']));
		$article_area = $article_sys['article_area'];
		if ($article_area){
			$article_area = json_decode($article_area,true);
			if (is_array($article_area) && sizeof($article_area)>0){
				// print_r($article_area);exit;
				//获取当前客户的IP归属：
				$ip = getIP2();
				// echo $ip;exit;
				// $ip = '113.82.138.231';//@test;
				// $ip = '124.224.134.137';//@test;
				$city = getCity($ip);
				// print_r($city);exit;
				$in_area = 0;
				if (is_array($city) && sizeof($city)){
					$province = $city['region'];
					$city = $city['city'];
					//判断是否在设定的地理范围内：
						// print_r($article_area);exit;
						foreach ($article_area as $key=>$area){
							if (trim($area['province']) == trim($province)){
								if (trim($area['city'])){
									// print_r($city);exit;
									//如果有城市：
									if (trim($area['city']) == trim($city)){
										$in_area = 1;
										break;
									}
								}else{
									$in_area = 1;
									break;
								}
								
							}
						}
				}
				
				//如果不在区域，提示：
				if (!$in_area){
					message('对不起，您不在该文章允许阅读的地理区域内！','','error');
					exit;
				}
			}
		}
		
		
		//根据地区设置文章权限结束；
		
		
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
		$openid = m('user')->getOpenid();//@test：测试的时候注释，正式使用的时候启用；
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
		$myid = m('member')->getMid();//@test：测试的时候注释，正式使用的时候启用；
		
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
		
		//如果设置了最高累计奖金，计算出已经支出的金额：@phpdb.net;
		$total_money = $article['article_rule_userd_money'] ? $article['article_rule_userd_money'] : 0;
		if ($article['article_rule_money_total']>0){
			$sql = "select sum(add_money) from ".tablename('sz_yi_article_share').
					" where uniacid = '{$_W['uniacid']}' and aid='{$article['id']}' ";
					// echo $sql;exit;
			$total_money += pdo_fetchcolumn($sql);
			// echo $total_money;exit;
		}
		
		/*//这里判断是否超出了总奖励金额，如果超过，金额设为0：@phpdb.net;
		$myid = rand(1,30);//@test;
		// echo $article['article_rule_money_total'];exit;
		// print_r($article);exit;
		if ($total_money < $article['article_rule_money_total']){
			$insert = array('aid' => $article['id'], 'share_user' => $shareid, 'click_user' => $myid, 'click_date' => time(), 'add_credit' => $article['article_rule_credit'], 'add_money' => $article['article_rule_money'], 'uniacid' => $_W['uniacid']);
			// print_r($insert);exit;
			pdo_insert('sz_yi_article_share', $insert);
		}*/

	} else {
		die('没有查询到文章信息！请检查URL后重试！');
	}
} else {
	die('url参数错误！');
}
include $this->template('index');


//函数区：
/** 
 * 获取用户真实 IP 
*/ 
function getIP2(){ 
	static $realip; 
	if (isset($_SERVER)){ 
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){ 
			$realip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
		} else if (isset($_SERVER["HTTP_CLIENT_IP"])) { 
			$realip = $_SERVER["HTTP_CLIENT_IP"]; 
		} else { 
			$realip = $_SERVER["REMOTE_ADDR"]; 
		} 
	}else { 
		if (getenv("HTTP_X_FORWARDED_FOR")){ 
			$realip = getenv("HTTP_X_FORWARDED_FOR"); 
		} else if (getenv("HTTP_CLIENT_IP")) { 
			$realip = getenv("HTTP_CLIENT_IP"); 
		} else { 
			$realip = getenv("REMOTE_ADDR"); 
		} 
	}   
	return $realip; 
} 
/** 
 * 获取IP地理位置 
 * 淘宝IP接口 
 * @Return: array 
 */ 
function getCity($ip){ 
	$url="http://ip.taobao.com/service/getIpInfo.php?ip={$ip}"; 
	$ip=json_decode(file_get_contents($url)); 
	if((string)$ip->code=='1'){//失败
		return false; 
	} 
	$data = (array)$ip->data; 
	return $data; 
} 
