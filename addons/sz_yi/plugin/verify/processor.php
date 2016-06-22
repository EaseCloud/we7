<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'plugin/plugin_processor.php';
class VerifyProcessor extends PluginProcessor
{
    public function __construct()
    {
        parent::__construct('verify');
    }
    public function respond($obj = null)
    {
        global $_W;
        $message = $obj->message;
        $openid  = $obj->message['from'];
        $content = $obj->message['content'];
        $msgtype = strtolower($message['msgtype']);
        $event   = strtolower($message['event']);
        if ($msgtype == 'text' || $event == 'click') {
            $saler = pdo_fetch('select * from ' . tablename('sz_yi_saler') . ' where openid=:openid and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':openid' => $openid
            ));
            if (empty($saler)) {
                return $this->responseEmpty();
            }
            $trade = m('common')->getSysset('trade');
            if (!$obj->inContext) {
                $obj->beginContext();
                return $obj->respText('请输入订单消费码:');
            } else if ($obj->inContext && is_numeric($content)) {
                $order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where verifycode=:verifycode and uniacid=:uniacid  limit 1', array(
                    ':verifycode' => $content,
                    ':uniacid' => $_W['uniacid']
                ));
                if (empty($order)) {
                    return $obj->respText('未找到要核销的订单,请重新输入!');
                }
                $orderid = $order['id'];
                if (empty($order['isverify'])) {
                    $obj->endContext();
                    return $obj->respText('订单无需核销!');
                }
                if (!empty($order['verified'])) {
                    $obj->endContext();
                    return $obj->respText('此订单已核销，无需重复核销!');
                }
                if ($order['status'] != 1) {
                    $obj->endContext();
                    return $obj->respText('订单未付款，无法核销!');
                }
                $storeids = array();
                $goods    = pdo_fetchall("select og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,g.isverify,g.storeids from " . tablename('sz_yi_order_goods') . " og " . " left join " . tablename('sz_yi_goods') . " g on g.id=og.goodsid " . " where og.orderid=:orderid and og.uniacid=:uniacid ", array(
                    ':uniacid' => $_W['uniacid'],
                    ':orderid' => $order['id']
                ));
                foreach ($goods as $g) {
                    if (!empty($g['storeids'])) {
                        $storeids = array_merge(explode(',', $g['storeids']), $storeids);
                    }
                }
                if (!empty($storeids)) {
                    if (!empty($saler['storeid'])) {
                        if (!in_array($saler['storeid'], $storeids)) {
                            return $obj->respText('您无此门店的核销权限!');
                        }
                    }
                }
                $time = time();
                pdo_update('sz_yi_order', array(
                    'status' => 3,
                    'sendtime' => $time,
                    'finishtime' => $time,
                    'verifytime' => $time,
                    'verified' => 1,
                    'verifyopenid' => $openid,
					'verifystoreid' => $saler['storeid']
                ), array(
                    'id' => $order['id']
                ));
                m('notice')->sendOrderMessage($orderid);
                if (p('commission')) {
                    p('commission')->checkOrderFinish($orderid);
                }
                $obj->endContext();
                return $obj->respText('核销成功!');
            }
        }
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
}
