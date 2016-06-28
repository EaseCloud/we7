<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'plugin/plugin_processor.php';
class PosterProcessor extends PluginProcessor
{
    public function __construct()
    {
        parent::__construct('poster');
    }
    public function respond($obj = null)
    {
        global $_W;
        $message     = $obj->message;
        $msgtype     = strtolower($message['msgtype']);
        $event       = strtolower($message['event']);
        $obj->member = $this->model->checkMember($message['from']);
        if ($msgtype == 'text' || $event == 'click') {
            return $this->responseText($obj);
        } else if ($msgtype == 'event') {
            if ($event == 'scan') {
                return $this->responseScan($obj);
            } else if ($event == 'subscribe') {
                return $this->responseSubscribe($obj);
            }
        }
    }
    /*private function responseText($obj)
    {
        global $_W;
        $timeout = 4;
        load()->func('communication');
        $resp = ihttp_post($_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=poster&method=build', array(
            "openid" => $obj->message['from'],
            "content" => urlencode($obj->message['content'])
        ), null, $timeout);
        return $this->responseEmpty();
    }*/
	private function responseText($obj)
	{
		global $_W;
		$timeout = 4;
		load()->func('communication');
		$resp = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=plugin&p=poster&method=build&timestamp=' . time();
		$_var_6 = ihttp_request($resp, array('openid' => $obj->message['from'], 'content' => urlencode($obj->message['content'])), array(), $timeout);
		return $this->responseEmpty();
	}
    private function responseEmpty()
    {
        ob_clean();
        ob_start();
        echo '';
        ob_flush();
        ob_end_flush();
        exit(0);
    }
    private function responseDefault($obj)
    {
        global $_W;
        return $obj->respText('感谢您的关注!');
    }
    private function responseScan($obj)
    {
        global $_W;
        $openid  = $obj->message['from'];
        $sceneid = $obj->message['eventkey'];
        $ticket  = $obj->message['ticket'];
        if (empty($sceneid)) {
            return $this->responseDefault($obj);
        }
		$qr = $this->model->getQRByTicket($ticket);
        if (empty($qr)) {
            return $this->responseDefault($obj);
        }
        $poster = pdo_fetch('select * from ' . tablename('sz_yi_poster') . ' where type=4 and isdefault=1 and uniacid=:uniacid limit 1', array(
            ':uniacid' => $_W['uniacid']
        ));
        if (empty($poster)) {
            return $this->responseDefault($obj);
        }
        $this->model->scanTime($openid, $qr['openid'], $poster);
        $qrmember = m('member')->getMember($qr['openid']);
        $this->commission($poster, $obj->member, $qrmember);
        $url = trim($poster['respurl']);
        if (empty($url)) {
            if ($qrmember['isagent'] == 1 && $qrmember['status'] == 1) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=plugin&p=commission&method=myshop&mid=" . $qrmember['id'];
            } else {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=shop&mid=" . $qrmember['id'];
            }
        }
       
		if (!empty($poster['resptitle'])) {
			$news = array(array('title' => $poster['resptitle'], 'description' => $poster['respdesc'], 'picurl' => tomedia($poster['respthumb']), 'url' => $url));
			return $obj->respNews($news);
		}
        return $this->responseEmpty();
    }
    private function responseSubscribe($obj)
    {
        global $_W;
        $openid  = $obj->message['from'];
        $keys    = explode('_', $obj->message['eventkey']);
        $sceneid = isset($keys[1]) ? $keys[1] : '';
        $ticket  = $obj->message['ticket'];
        $member  = $obj->member;
        if (empty($ticket)) {
            return $this->responseDefault($obj);
        }
        $qr = $this->model->getQRByTicket($ticket);
        if (empty($qr)) {
            return $this->responseDefault($obj);
        }
        $poster = pdo_fetch('select * from ' . tablename('sz_yi_poster') . ' where type=4 and isdefault=1 and uniacid=:uniacid limit 1', array(
            ':uniacid' => $_W['uniacid']
        ));
        if (empty($poster)) {
            return $this->responseDefault($obj);
        }
        if ($member['isnew']) {
            pdo_update('sz_yi_poster', array(
                'follows' => $poster['follows'] + 1
            ), array(
                'id' => $poster['id']
            ));
        }
        $qrmember = m('member')->getMember($qr['openid']);
        $log      = pdo_fetch('select * from ' . tablename('sz_yi_poster_log') . ' where openid=:openid and posterid=:posterid and uniacid=:uniacid limit 1', array(
            ':openid' => $openid,
            ':posterid' => $poster['id'],
            ':uniacid' => $_W['uniacid']
        ));
        if (empty($log) && $openid != $qr['openid']) {
            $log = array(
                'uniacid' => $_W['uniacid'],
                'posterid' => $poster['id'],
                'openid' => $openid,
                'from_openid' => $qr['openid'],
                'subcredit' => $poster['subcredit'],
                'submoney' => $poster['submoney'],
                'reccredit' => $poster['reccredit'],
                'recmoney' => $poster['recmoney'],
                'createtime' => time()
            );
            pdo_insert('sz_yi_poster_log', $log);
            $log['id']     = pdo_insertid();
            $subpaycontent = $poster['subpaycontent'];
            if (empty($subpaycontent)) {
                $subpaycontent = '您通过 [nickname] 的推广二维码扫码关注的奖励';
            }
            $subpaycontent = str_replace("[nickname]", $qrmember['nickname'], $subpaycontent);
            $recpaycontent = $poster['recpaycontent'];
            if (empty($recpaycontent)) {
                $recpaycontent = '推荐 [nickname] 扫码关注的奖励';
            }
            $recpaycontent = str_replace("[nickname]", $member['nickname'], $subpaycontent);
            if ($poster['subcredit'] > 0) {
                m('member')->setCredit($openid, 'credit1', $poster['subcredit'], array(
                    0,
                    '扫码关注积分+' . $poster['subcredit']
                ));
            }
            if ($poster['submoney'] > 0) {
                $pay = $poster['submoney'];
                if ($poster['paytype'] == 1) {
                    $pay *= 100;
                }
                m('finance')->pay($openid, $poster['paytype'], $pay, '', $subpaycontent);
            }
            if ($poster['reccredit'] > 0) {
                m('member')->setCredit($qr['openid'], 'credit1', $poster['reccredit'], array(
                    0,
                    '推荐扫码关注积分+' . $poster['reccredit']
                ));
            }
            if ($poster['recmoney'] > 0) {
                $pay = $poster['recmoney'];
                if ($poster['paytype'] == 1) {
                    $pay *= 100;
                }
                m('finance')->pay($qr['openid'], $poster['paytype'], $pay, '', $recpaycontent);
            }
			$_var_20 = false;
			$_var_21 = false;
			$_var_22 = p('coupon');
			if ($_var_22) {
				if (!empty($poster['reccouponid']) && $poster['reccouponnum'] > 0) {
					$_var_23 = $_var_22->getCoupon($poster['reccouponid']);
					if (!empty($_var_23)) {
						$_var_20 = true;
					}
				}
				if (!empty($poster['subcouponid']) && $poster['subcouponnum'] > 0) {
					$_var_24 = $_var_22->getCoupon($poster['subcouponid']);
					if (!empty($_var_24)) {
						$_var_21 = true;
					}
				}
			}
            if (!empty($poster['subtext'])) {
                $subtext = $poster['subtext'];
                $subtext = str_replace("[nickname]", $member['nickname'], $subtext);
                $subtext = str_replace("[credit]", $poster['reccredit'], $subtext);
                $subtext = str_replace("[money]", $poster['recmoney'], $subtext);
				if ($_var_23) {
					$subtext = str_replace('[couponname]', $_var_23['couponname'], $subtext);
					$subtext = str_replace('[couponnum]', $poster['reccouponnum'], $subtext);
				}
                if (!empty($poster['templateid'])) {
                    m('message')->sendTplNotice($qr['openid'], $poster['templateid'], array(
                        'first' => array(
                            'value' => "推荐关注奖励到账通知",
                            "color" => "#4a5077"
                        ),
                        'keyword1' => array(
                            'value' => '推荐奖励',
                            "color" => "#4a5077"
                        ),
                        'keyword2' => array(
                            'value' => $subtext,
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => "\r\n谢谢您对我们的支持！",
                            "color" => "#4a5077"
                        )
                    ), '');
                } else {
                    m('message')->sendCustomNotice($qr['openid'], $subtext);
                }
            }
            if (!empty($poster['entrytext'])) {
                $entrytext = $poster['entrytext'];
                $entrytext = str_replace("[nickname]", $qrmember['nickname'], $entrytext);
                $entrytext = str_replace("[credit]", $poster['subcredit'], $entrytext);
                $entrytext = str_replace("[money]", $poster['submoney'], $entrytext);
				if ($_var_24) {
					$entrytext = str_replace('[couponname]', $_var_24['couponname'], $entrytext);
					$entrytext = str_replace('[couponnum]', $poster['subcouponnum'], $entrytext);
				}
                if (!empty($poster['templateid'])) {
                    m('message')->sendTplNotice($openid, $poster['templateid'], array(
                        'first' => array(
                            'value' => "关注奖励到账通知",
                            "color" => "#4a5077"
                        ),
                        'keyword1' => array(
                            'value' => '关注奖励',
                            "color" => "#4a5077"
                        ),
                        'keyword2' => array(
                            'value' => $entrytext,
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => "\r\n谢谢您对我们的支持！",
                            "color" => "#4a5077"
                        )
                    ), '');
                } else {
                    m('message')->sendCustomNotice($openid, $entrytext);
                }
            }
			$_var_27 = array();
			if ($_var_20) {
				$_var_27['reccouponid'] = $poster['reccouponid'];
				$_var_27['reccouponnum'] = $poster['reccouponnum'];
				$_var_22->poster($qrmember, $poster['reccouponid'], $poster['reccouponnum']);
			}
			if ($_var_21) {
				$_var_27['subcouponid'] = $poster['subcouponid'];
				$_var_27['subcouponnum'] = $poster['subcouponnum'];
				$_var_22->poster($member, $poster['subcouponid'], $poster['subcouponnum']);
			}
			if (!empty($_var_27)) {
				pdo_update('sz_yi_poster_log', $_var_27, array('id' => $log['id']));
			}
		}
        $this->commission($poster, $member, $qrmember);
        $url = trim($poster['respurl']);
        if (empty($url)) {
            if ($qrmember['isagent'] == 1 && $qrmember['status'] == 1) {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=plugin&p=commission&method=myshop&mid=" . $qrmember['id'];
            } else {
                $url = $_W['siteroot'] . "app/index.php?i={$_W['uniacid']}&c=entry&m=sz_yi&do=shop&mid=" . $qrmember['id'];
            }
        }
		if (!empty($poster['resptitle'])) {
			$news = array(array('title' => $poster['resptitle'], 'description' => $poster['respdesc'], 'picurl' => tomedia($poster['respthumb']), 'url' => $url));
			return $obj->respNews($news);
		}
        return $this->responseEmpty();
    }
    private function commission($poster, $member, $qrmember)
	{
		$time = time();
		$p = p('commission');
		if ($p) {
			$cset = $p->getSet();
			if (!empty($cset)) {
				if ($member['isagent'] != 1) {
					if ($qrmember['isagent'] == 1 && $qrmember['status'] == 1) {
						if (!empty($poster['bedown'])) {
							if (empty($member['agentid'])) {
								if (empty($member['fixagentid'])) {
									pdo_update('sz_yi_member', array('agentid' => $qrmember['id'], 'childtime' => $time), array('id' => $member['id']));
									$member['agentid'] = $qrmember['id'];
									$p->sendMessage($qrmember['openid'], array('nickname' => $member['nickname'], 'childtime' => $time), TM_COMMISSION_AGENT_NEW);
									$p->upgradeLevelByAgent($qrmember['id']);
								}
							}
							if (!empty($poster['beagent'])) {
								$status = intval($cset['become_check']);
								pdo_update('sz_yi_member', array('isagent' => 1, 'status' => $status, 'agenttime' => $time), array('id' => $member['id']));
								if ($status == 1) {
									$p->sendMessage($member['openid'], array('nickname' => $member['nickname'], 'agenttime' => $time), TM_COMMISSION_BECOME);
									$p->upgradeLevelByAgent($qrmember['id']);
								}
							}
						}
					}
				}
			}
		}
	}
	
}
