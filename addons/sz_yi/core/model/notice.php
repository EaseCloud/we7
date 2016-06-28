<?php
/*=============================================================================
#     FileName: notice.php
#         Desc: 通知类
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:33:42
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Sz_DYi_Notice
{
    public function sendOrderMessage($orderid = '0', $delRefund = false)
    {
        global $_W;
        if (empty($orderid)) {
            return;
        }
        $order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(
            ':id' => $orderid
        ));
        if (empty($order)) {
            return;
        }
        $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=order&p=detail&id=' . $orderid;
        if (strexists($detailurl, '/addons/sz_yi/')) {
            $detailurl = str_replace("/addons/sz_yi/", '/', $detailurl);
        }
        if (strexists($detailurl, '/core/mobile/order/')) {
            $detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
        }
        $openid      = $order['openid'];
        $order_goods = pdo_fetchall('select g.id,g.title,og.realprice,og.total,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(
            ':uniacid' => $_W['uniacid'],
            ':orderid' => $orderid
        ));
        $goods       = '';
        foreach ($order_goods as $og) {
            $goods .= "" . $og['title'] . '( ';
            if (!empty($og['optiontitle'])) {
                $goods .= " 规格: " . $og['optiontitle'];
            }
            $goods .= ' 单价: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . "); ";
        }
        $orderpricestr = ' 订单总价: ' . $order['price'] . '(包含运费:' . $order['dispatchprice'] . ')';
        $member        = m('member')->getMember($openid);
        $usernotice    = unserialize($member['noticeset']);
        if (!is_array($usernotice)) {
            $usernotice = array();
        }
        $set  = m('common')->getSysset();
        $shop = $set['shop'];
        $tm   = $set['notice'];
        if ($delRefund) {
            if (!empty($order['refundid'])) {
                $refund = pdo_fetch('select * from ' . tablename('sz_yi_order_refund') . ' where id=:id limit 1', array(
                    ':id' => $order['refundid']
                ));
                if (empty($refund)) {
                    return;
                }
                if (empty($refund['status'])) {
                    $msg = array(
                        'first' => array(
                            'value' => "您的退款申请已经提交！",
                            "color" => "#4a5077"
                        ),
                        'orderProductPrice' => array(
                            'title' => '退款金额',
                            'value' => '￥' . $refund['price'] . '元',
                            "color" => "#4a5077"
                        ),
                        'orderProductName' => array(
                            'title' => '商品详情',
                            'value' => $goods . $orderpricestr,
                            "color" => "#4a5077"
                        ),
                        'orderName' => array(
                            'title' => '订单编号',
                            'value' => $order['ordersn'],
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => "\r\n等待商家确认退款信息！",
                            "color" => "#4a5077"
                        )
                    );
                    if (!empty($tm['refund']) && empty($usernotice['refund'])) {
                        m('message')->sendTplNotice($openid, $tm['refund'], $msg, $detailurl);
                    } else if (empty($usernotice['refund'])) {
                        m('message')->sendCustomNotice($openid, $msg, $detailurl);
                    }
                } else if ($refund['status'] == 1) {
                    $refundtype = '';
                    if (empty($refund['refundtype'])) {
                        $refundtype = ', 已经退回您的余额账户，请留意查收！';
                    } else if ($refund['refundtype'] == 1) {
                        $refundtype = ', 已经退回您的对应支付渠道（如银行卡，微信钱包等, 具体到账时间请您查看微信支付通知)，请留意查收！';
                    } else {
                        $refundtype = ', 请联系客服进行退款事项！';
                    }
                    $msg = array(
                        'first' => array(
                            'value' => "您的订单已经完成退款！",
                            "color" => "#4a5077"
                        ),
                        'orderProductPrice' => array(
                            'title' => '退款金额',
                            'value' => '￥' . $refund['price'] . '元',
                            "color" => "#4a5077"
                        ),
                        'orderProductName' => array(
                            'title' => '商品详情',
                            'value' => $goods . $orderpricestr,
                            "color" => "#4a5077"
                        ),
                        'orderName' => array(
                            'title' => '订单编号',
                            'value' => $order['ordersn'],
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => "\r\n 退款金额 ￥" . $refund['price'] . "{$refundtype}\r\n 【" . $shop['name'] . "】期待您再次购物！",
                            "color" => "#4a5077"
                        )
                    );
                    if (!empty($tm['refund1']) && empty($usernotice['refund1'])) {
                        m('message')->sendTplNotice($openid, $tm['refund1'], $msg, $detailurl);
                    } else if (empty($usernotice['refund1'])) {
                        m('message')->sendCustomNotice($openid, $msg, $detailurl);
                    }
                } elseif ($refund['status'] == -1) {
                    $remark = "\n驳回原因: " . $refund['reply'];
                    if (!empty($shop['phone'])) {
                        $remark .= "\n客服电话:  " . $shop['phone'];
                    }
                    $msg = array(
                        'first' => array(
                            'value' => "您的退款申请被商家驳回，可与商家协商沟通！",
                            "color" => "#4a5077"
                        ),
                        'orderProductPrice' => array(
                            'title' => '退款金额',
                            'value' => '￥' . $refund['price'] . '元',
                            "color" => "#4a5077"
                        ),
                        'orderProductName' => array(
                            'title' => '商品详情',
                            'value' => $goods . $orderpricestr,
                            "color" => "#4a5077"
                        ),
                        'orderName' => array(
                            'title' => '订单编号',
                            'value' => $order['ordersn'],
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => $remark,
                            "color" => "#4a5077"
                        )
                    );
                    if (!empty($tm['refund2']) && empty($usernotice['refund2'])) {
                        m('message')->sendTplNotice($openid, $tm['refund2'], $msg, $detailurl);
                    } else if (empty($usernotice['refund2'])) {
                        m('message')->sendCustomNotice($openid, $msg, $detailurl);
                    }
                }
                return;
            }
        }
        $buyerinfo = '';
        if (!empty($order['addressid'])) {
            $address = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where id=:id and uniacid=:uniacid limit 1', array(
                ':id' => $order['addressid'],
                ':uniacid' => $_W['uniacid']
            ));
            if (!empty($address)) {
                $buyerinfo = "收件人: " . $address["realname"] . "\n联系电话: " . $address["mobile"] . "\n收货地址: " . $address["province"] . $address["city"] . $address["area"] . " " . $address["address"];
            }
        } else {
            $carrier = iunserializer($order["carrier"]);
            if (is_array($carrier)) {
                $buyerinfo = "联系人: " . $carrier["carrier_realname"] . "\n联系电话: " . $carrier["carrier_mobile"];
            }
        }
        if ($order['status'] == -1) {
            if (empty($order['dispatchtype'])) {
                $address      = pdo_fetch('select * from ' . tablename('sz_yi_member_address') . ' where id=:id and uniacid=:uniacid limit 1 ', array(
                    ":uniacid" => $_W['uniacid'],
                    ":id" => $order['addressid']
                ));
                $orderAddress = array(
                    'title' => '收货信息',
                    'value' => '收货地址: ' . $address['province'] . ' ' . $address['city'] . ' ' . $address['area'] . ' ' . $address['address'] . ' 收件人: ' . $address['realname'] . ' 联系电话: ' . $address['mobile'],
                    "color" => "#4a5077"
                );
            } else {
                $carrier      = iunserializer($order['carrier']);
                $orderAddress = array(
                    'title' => '收货信息',
                    'value' => '自提地点: ' . $carrier['address'] . ' 联系人: ' . $carrier['realname'] . ' 联系电话: ' . $carrier['mobile'],
                    "color" => "#4a5077"
                );
            }
            $msg = array(
                'first' => array(
                    'value' => "您的订单已取消!",
                    "color" => "#4a5077"
                ),
                'orderProductPrice' => array(
                    'title' => '订单金额',
                    'value' => '￥' . $order['price'] . '元(含运费' . $order['dispatchprice'] . '元)',
                    "color" => "#4a5077"
                ),
                'orderProductName' => array(
                    'title' => '商品详情',
                    'value' => $goods,
                    "color" => "#4a5077"
                ),
                'orderAddress' => $orderAddress,
                'orderName' => array(
                    'title' => '订单编号',
                    'value' => $order['ordersn'],
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "\r\n【" . $shop['name'] . "】欢迎您的再次购物！",
                    "color" => "#4a5077"
                )
            );
            if (!empty($tm['cancel']) && empty($usernotice['cancel'])) {
                m('message')->sendTplNotice($openid, $tm['cancel'], $msg, $detailurl);
            } else if (empty($usernotice['cancel'])) {
                m('message')->sendCustomNotice($openid, $msg, $detailurl);
            }
        } else if ($order['status'] == 0) {
            $newtype = explode(',', $tm['newtype']);
            if (empty($tm['newtype']) || (is_array($newtype) && in_array(0, $newtype))) {
                $remark = "\n订单下单成功,请到后台查看!";
                if (!empty($buyerinfo)) {
                    $remark .= "\r\n下单者信息:\n" . $buyerinfo;
                }
                $msg     = array(
                    'first' => array(
                        'value' => "订单下单通知!",
                        "color" => "#4a5077"
                    ),
                    'keyword1' => array(
                        'title' => '时间',
                        'value' => date('Y-m-d H:i:s', $order['createtime']),
                        "color" => "#4a5077"
                    ),
                    'keyword2' => array(
                        'title' => '商品名称',
                        'value' => $goods . $orderpricestr,
                        "color" => "#4a5077"
                    ),
                    'keyword3' => array(
                        'title' => '订单号',
                        'value' => $order['ordersn'],
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'value' => $remark,
                        "color" => "#4a5077"
                    )
                );
                $account = m('common')->getAccount();
                if (!empty($tm['openid'])) {
                    $openids = explode(',', $tm['openid']);
                    foreach ($openids as $tmopenid) {
                        if (empty($tmopenid)) {
                            continue;
                        }
                        if (!empty($tm['new'])) {
                            m('message')->sendTplNotice($tmopenid, $tm['new'], $msg, '', $account);
                        } else {
                            m('message')->sendCustomNotice($tmopenid, $msg, '', $account);
                        }
                    }
                }
            }
            $remark = "\r\n商品已经下单，请及时备货，谢谢!";
            if (!empty($buyerinfo)) {
                $remark .= "\r\n下单者信息:\n" . $buyerinfo;
            }
            foreach ($order_goods as $og) {
                if (!empty($og['noticeopenid'])) {
                    $noticetype = explode(',', $og['noticetype']);
                    if (empty($og['noticetype']) || (is_array($noticetype) && in_array(0, $noticetype))) {
                        $goodstr = $og['title'] . '( ';
                        if (!empty($og['optiontitle'])) {
                            $goodstr .= " 规格: " . $og['optiontitle'];
                        }
                        $goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
                        $msg = array(
                            'first' => array(
                                'value' => "商品下单通知!",
                                "color" => "#4a5077"
                            ),
                            'keyword1' => array(
                                'title' => '时间',
                                'value' => date('Y-m-d H:i:s', $order['createtime']),
                                "color" => "#4a5077"
                            ),
                            'keyword2' => array(
                                'title' => '商品名称',
                                'value' => $goodstr,
                                "color" => "#4a5077"
                            ),
                            'keyword3' => array(
                                'title' => '订单号',
                                'value' => $order['ordersn'],
                                "color" => "#4a5077"
                            ),
                            'remark' => array(
                                'value' => $remark,
                                "color" => "#4a5077"
                            )
                        );
                        if (!empty($tm['new'])) {
                            m('message')->sendTplNotice($og['noticeopenid'], $tm['new'], $msg, '', $account);
                        } else {
                            m('message')->sendCustomNotice($og['noticeopenid'], $msg, '', $account);
                        }
                    }
                }
            }
            if (!empty($order['addressid'])) {
                $remark = "\r\n您的订单我们已经收到，支付后我们将尽快配送~~";
            } else if (!empty($order['isverify'])) {
                $remark = "\r\n您的订单我们已经收到，支付后您就可以到店使用了~~";
            } else if (!empty($order['virtual'])) {
                $remark = "\r\n您的订单我们已经收到，支付后系统将会自动发货~~";
            } else {
                $remark = "\r\n您的订单我们已经收到，支付后您就可以到自提点提货物了~~";
            }
            $msg = array(
                'first' => array(
                    'value' => "您的订单已提交成功！",
                    "color" => "#4a5077"
                ),
                'keyword1' => array(
                    'title' => '店铺',
                    'value' => $shop['name'],
                    "color" => "#4a5077"
                ),
                'keyword2' => array(
                    'title' => '下单时间',
                    'value' => date('Y-m-d H:i:s', $order['createtime']),
                    "color" => "#4a5077"
                ),
                'keyword3' => array(
                    'title' => '商品',
                    'value' => $goods,
                    "color" => "#4a5077"
                ),
                'keyword4' => array(
                    'title' => '金额',
                    'value' => '￥' . $order['price'] . '元(含运费' . $order['dispatchprice'] . '元)',
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => $remark,
                    "color" => "#4a5077"
                )
            );
            if (!empty($tm['submit']) && empty($usernotice['submit'])) {
                m('message')->sendTplNotice($openid, $tm['submit'], $msg, $detailurl);
            } else if (empty($usernotice['submit'])) {
                m('message')->sendCustomNotice($openid, $msg, $detailurl);
            }
        } else if ($order['status'] == 1) {
            $newtype = explode(',', $tm['newtype']);
            if ($tm['newtype'] == 1 || (is_array($newtype) && in_array(1, $newtype))) {
                $remark = "\n订单已经下单支付，请及时备货，谢谢!";
                if (!empty($buyerinfo)) {
                    $remark .= "\r\n购买者信息:\n" . $buyerinfo;
                }
                $msg     = array(
                    'first' => array(
                        'value' => "订单下单支付通知!",
                        "color" => "#4a5077"
                    ),
                    'keyword1' => array(
                        'title' => '时间',
                        'value' => date('Y-m-d H:i:s', $order['createtime']),
                        "color" => "#4a5077"
                    ),
                    'keyword2' => array(
                        'title' => '商品名称',
                        'value' => $goods . $orderpricestr,
                        "color" => "#4a5077"
                    ),
                    'keyword3' => array(
                        'title' => '订单号',
                        'value' => $order['ordersn'],
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'value' => $remark,
                        "color" => "#4a5077"
                    )
                );
                $account = m('common')->getAccount();
                if (!empty($tm['openid'])) {
                    $openids = explode(',', $tm['openid']);
                    foreach ($openids as $tmopenid) {
                        if (empty($tmopenid)) {
                            continue;
                        }
                        if (!empty($tm['new'])) {
                            m('message')->sendTplNotice($tmopenid, $tm['new'], $msg, '', $account);
                        } else {
                            m('message')->sendCustomNotice($tmopenid, $msg, '', $account);
                        }
                    }
                }
            }
            $remark = "\r\n商品已经下单支付，请及时备货，谢谢!";
            if (!empty($buyerinfo)) {
                $remark .= "\r\n购买者信息:\n" . $buyerinfo;
            }
            foreach ($order_goods as $og) {
                $noticetype = explode(',', $og['noticetype']);
                if ($og['noticetype'] == '1' || (is_array($noticetype) && in_array(1, $noticetype))) {
                    $goodstr = $og['title'] . '( ';
                    if (!empty($og['optiontitle'])) {
                        $goodstr .= " 规格: " . $og['optiontitle'];
                    }
                    $goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
                    $msg = array(
                        'first' => array(
                            'value' => "商品下单支付通知!",
                            "color" => "#4a5077"
                        ),
                        'keyword1' => array(
                            'title' => '时间',
                            'value' => date('Y-m-d H:i:s', $order['createtime']),
                            "color" => "#4a5077"
                        ),
                        'keyword2' => array(
                            'title' => '商品名称',
                            'value' => $goodstr,
                            "color" => "#4a5077"
                        ),
                        'keyword3' => array(
                            'title' => '订单号',
                            'value' => $order['ordersn'],
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'value' => $remark,
                            "color" => "#4a5077"
                        )
                    );
                    if (!empty($tm['new'])) {
                        m('message')->sendTplNotice($og['noticeopenid'], $tm['new'], $msg, '', $account);
                    } else {
                        m('message')->sendCustomNotice($og['noticeopenid'], $msg, '', $account);
                    }
                }
            }
            $remark = "\r\n【" . $shop['name'] . "】欢迎您的再次购物！";
            if ($order['isverify']) {
                $remark = "\r\n点击订单详情查看可消费门店, 【" . $shop['name'] . "】欢迎您的再次购物！";
            }
            $msg           = array(
                'first' => array(
                    'value' => "您已支付成功订单！",
                    "color" => "#4a5077"
                ),
                'keyword1' => array(
                    'title' => '订单',
                    'value' => $order['ordersn'],
                    "color" => "#4a5077"
                ),
                'keyword2' => array(
                    'title' => '支付状态',
                    'value' => '支付成功',
                    "color" => "#4a5077"
                ),
                'keyword3' => array(
                    'title' => '支付日期',
                    'value' => date('Y-m-d H:i:s', $order['paytime']),
                    "color" => "#4a5077"
                ),
                'keyword4' => array(
                    'title' => '商户',
                    'value' => $shop['name'],
                    "color" => "#4a5077"
                ),
                'keyword5' => array(
                    'title' => '金额',
                    'value' => '￥' . $order['price'] . '元(含运费' . $order['dispatchprice'] . '元)',
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => $remark,
                    "color" => "#4a5077"
                )
            );
            $pay_detailurl = $detailurl;
            if (strexists($pay_detailurl, '/addons/sz_yi/')) {
                $pay_detailurl = str_replace("/addons/sz_yi/", '/', $pay_detailurl);
            }
            if (strexists($pay_detailurl, '/core/mobile/order/')) {
                $pay_detailurl = str_replace("/core/mobile/order/", '/', $pay_detailurl);
            }
            if (!empty($tm['pay']) && empty($usernotice['pay'])) {
                m('message')->sendTplNotice($openid, $tm['pay'], $msg, $pay_detailurl);
            } else if (empty($usernotice['pay'])) {
                m('message')->sendCustomNotice($openid, $msg, $pay_detailurl);
            }
            if ($order['dispatchtype'] == 1 && empty($order['isverify'])) {
                $carrier = iunserializer($order['carrier']);
                if (!is_array($carrier)) {
                    return;
                }
                $msg = array(
                    'first' => array(
                        'value' => "自提订单提交成功!",
                        "color" => "#4a5077"
                    ),
                    'keyword1' => array(
                        'title' => '自提码',
                        'value' => $order['ordersn'],
                        "color" => "#4a5077"
                    ),
                    'keyword2' => array(
                        'title' => '商品详情',
                        'value' => $goods . $orderpricestr,
                        "color" => "#4a5077"
                    ),
                    'keyword3' => array(
                        'title' => '提货地址',
                        'value' => $carrier['address'],
                        "color" => "#4a5077"
                    ),
                    'keyword4' => array(
                        'title' => '提货时间',
                        'value' => $carrier['content'],
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'value' => "\r\n请您到选择的自提点进行取货, 自提联系人: " . $carrier['realname'] . ' 联系电话: ' . $carrier['mobile'],
                        "color" => "#4a5077"
                    )
                );
                if (!empty($tm['carrier']) && empty($usernotice['carrier'])) {
                    m('message')->sendTplNotice($openid, $tm['carrier'], $msg, $detailurl);
                } else if (empty($usernotice['carrier'])) {
                    m('message')->sendCustomNotice($openid, $msg, $detailurl);
                }
            }
        } else if ($order['status'] == 2) {
            if (empty($order['dispatchtype'])) {
                $address = pdo_fetch('select * from ' . tablename('sz_yi_member_address') . ' where id=:id and uniacid=:uniacid limit 1 ', array(
                    ":uniacid" => $_W['uniacid'],
                    ":id" => $order['addressid']
                ));
                if (empty($address)) {
                    return;
                }
                $msg = array(
                    'first' => array(
                        'value' => "您的宝贝已经发货！",
                        "color" => "#4a5077"
                    ),
                    'keyword1' => array(
                        'title' => '订单内容',
                        'value' => "【" . $order['ordersn'] . "】" . $goods . $orderpricestr,
                        "color" => "#4a5077"
                    ),
                    'keyword2' => array(
                        'title' => '物流服务',
                        'value' => $order['expresscom'],
                        "color" => "#4a5077"
                    ),
                    'keyword3' => array(
                        'title' => '快递单号',
                        'value' => $order['expresssn'],
                        "color" => "#4a5077"
                    ),
                    'keyword4' => array(
                        'title' => '收货信息',
                        'value' => "地址: " . $address['province'] . ' ' . $address['city'] . ' ' . $address['area'] . ' ' . $address['address'] . "收件人: " . $address['realname'] . ' (' . $address['mobile'] . ') ',
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'value' => "\r\n我们正加速送到您的手上，请您耐心等候。",
                        "color" => "#4a5077"
                    )
                );
                if (!empty($tm['send']) && empty($usernotice['send'])) {
                    m('message')->sendTplNotice($openid, $tm['send'], $msg, $detailurl);
                } else if (empty($usernotice['send'])) {
                    m('message')->sendCustomNotice($openid, $msg, $detailurl);
                }
            }
        } else if ($order['status'] == 3) {
            $pv = p('virtual');
            if ($pv && !empty($order['virtual'])) {
                $pvset       = $pv->getSet();
                $virtual_str = "\n" . $buyerinfo . "\n" . $order['virtual_str'];
                $msg         = array(
                    'first' => array(
                        'value' => "您购物的物品已自动发货!",
                        "color" => "#4a5077"
                    ),
                    'keyword1' => array(
                        'title' => '订单金额',
                        'value' => '￥' . $order['price'] . '元',
                        "color" => "#4a5077"
                    ),
                    'keyword2' => array(
                        'title' => '商品详情',
                        'value' => $goods,
                        "color" => "#4a5077"
                    ),
                    'keyword3' => array(
                        'title' => '收货信息',
                        'value' => $virtual_str,
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'title' => '',
                        'value' => "\r\n【" . $shop['name'] . '】感谢您的支持与厚爱，欢迎您的再次购物！',
                        "color" => "#4a5077"
                    )
                );
                if (!empty($pvset['tm']['send']) && empty($usernotice['finish'])) {
                    m('message')->sendTplNotice($openid, $pvset['tm']['send'], $msg, $detailurl);
                } else if (empty($usernotice['finish'])) {
                    m('message')->sendCustomNotice($openid, $msg, $detailurl);
                }
                $first   = "买家购买的商品已经自动发货!";
                $remark  = "\r\n发货信息:" . $virtual_str;
                $newtype = explode(',', $tm['newtype']);
                if ($tm['newtype'] == 2 || (is_array($newtype) && in_array(2, $newtype))) {
                    $msg     = array(
                        'first' => array(
                            'value' => $first,
                            "color" => "#4a5077"
                        ),
                        'keyword1' => array(
                            'title' => '订单号',
                            'value' => $order['ordersn'],
                            "color" => "#4a5077"
                        ),
                        'keyword2' => array(
                            'title' => '商品名称',
                            'value' => $goods . $orderpricestr,
                            "color" => "#4a5077"
                        ),
                        'keyword3' => array(
                            'title' => '下单时间',
                            'value' => date('Y-m-d H:i:s', $order['createtime']),
                            "color" => "#4a5077"
                        ),
                        'keyword4' => array(
                            'title' => '发货时间',
                            'value' => date('Y-m-d H:i:s', $order['sendtime']),
                            "color" => "#4a5077"
                        ),
                        'keyword5' => array(
                            'title' => '确认收货时间',
                            'value' => date('Y-m-d H:i:s', $order['finishtime']),
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'title' => '',
                            'value' => $remark,
                            "color" => "#4a5077"
                        )
                    );
                    $account = m('common')->getAccount();
                    if (!empty($tm['openid'])) {
                        $openids = explode(',', $tm['openid']);
                        foreach ($openids as $tmopenid) {
                            if (empty($tmopenid)) {
                                continue;
                            }
                            if (!empty($tm['finish'])) {
                                m('message')->sendTplNotice($tmopenid, $tm['finish'], $msg, '', $account);
                            } else {
                                m('message')->sendCustomNotice($tmopenid, $msg, '', $account);
                            }
                        }
                    }
                }
                foreach ($order_goods as $og) {
                    $noticetype = explode(',', $og['noticetype']);
                    if ($og['noticetype'] == '2' || (is_array($noticetype) && in_array(2, $noticetype))) {
                        $goodstr = $og['title'] . '( ';
                        if (!empty($og['optiontitle'])) {
                            $goodstr .= " 规格: " . $og['optiontitle'];
                        }
                        $goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
                        $msg = array(
                            'first' => array(
                                'value' => $first,
                                "color" => "#4a5077"
                            ),
                            'keyword1' => array(
                                'title' => '订单号',
                                'value' => $order['ordersn'],
                                "color" => "#4a5077"
                            ),
                            'keyword2' => array(
                                'title' => '商品名称',
                                'value' => $goodstr,
                                "color" => "#4a5077"
                            ),
                            'keyword3' => array(
                                'title' => '下单时间',
                                'value' => date('Y-m-d H:i:s', $order['createtime']),
                                "color" => "#4a5077"
                            ),
                            'keyword4' => array(
                                'title' => '发货时间',
                                'value' => date('Y-m-d H:i:s', $order['sendtime']),
                                "color" => "#4a5077"
                            ),
                            'keyword5' => array(
                                'title' => '确认收货时间',
                                'value' => date('Y-m-d H:i:s', $order['finishtime']),
                                "color" => "#4a5077"
                            ),
                            'remark' => array(
                                'title' => '',
                                'value' => $remark,
                                "color" => "#4a5077"
                            )
                        );
                        if (!empty($tm['finish'])) {
                            m('message')->sendTplNotice($og['noticeopenid'], $tm['finish'], $msg, '', $account);
                        } else {
                            m('message')->sendCustomNotice($og['noticeopenid'], $msg, '', $account);
                        }
                    }
                }
            } else {
                $msg = array(
                    'first' => array(
                        'value' => "亲, 您购买的宝贝已经确认收货!",
                        "color" => "#4a5077"
                    ),
                    'keyword1' => array(
                        'title' => '订单号',
                        'value' => $order['ordersn'],
                        "color" => "#4a5077"
                    ),
                    'keyword2' => array(
                        'title' => '商品名称',
                        'value' => $goods . $orderpricestr,
                        "color" => "#4a5077"
                    ),
                    'keyword3' => array(
                        'title' => '下单时间',
                        'value' => date('Y-m-d H:i:s', $order['createtime']),
                        "color" => "#4a5077"
                    ),
                    'keyword4' => array(
                        'title' => '发货时间',
                        'value' => date('Y-m-d H:i:s', $order['sendtime']),
                        "color" => "#4a5077"
                    ),
                    'keyword5' => array(
                        'title' => '确认收货时间',
                        'value' => date('Y-m-d H:i:s', $order['finishtime']),
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'title' => '',
                        'value' => "\r\n【" . $shop['name'] . '】感谢您的支持与厚爱，欢迎您的再次购物！',
                        "color" => "#4a5077"
                    )
                );
                if (!empty($tm['finish']) && empty($usernotice['finish'])) {
                    m('message')->sendTplNotice($openid, $tm['finish'], $msg, $detailurl);
                } else if (empty($usernotice['finish'])) {
                    m('message')->sendCustomNotice($openid, $msg, $detailurl);
                }
                $first = "买家购买的商品已经确认收货!";
                if ($order['isverify'] == 1) {
                    $first = "买家购买的商品已经确认核销!";
                }
                $remark = "";
                if (!empty($buyerinfo)) {
                    $remark = "\r\n购买者信息:\n" . $buyerinfo;
                }
                $newtype = explode(',', $tm['newtype']);
                if ($tm['newtype'] == 2 || (is_array($newtype) && in_array(2, $newtype))) {
                    $msg     = array(
                        'first' => array(
                            'value' => $first,
                            "color" => "#4a5077"
                        ),
                        'keyword1' => array(
                            'title' => '订单号',
                            'value' => $order['ordersn'],
                            "color" => "#4a5077"
                        ),
                        'keyword2' => array(
                            'title' => '商品名称',
                            'value' => $goods . $orderpricestr,
                            "color" => "#4a5077"
                        ),
                        'keyword3' => array(
                            'title' => '下单时间',
                            'value' => date('Y-m-d H:i:s', $order['createtime']),
                            "color" => "#4a5077"
                        ),
                        'keyword4' => array(
                            'title' => '发货时间',
                            'value' => date('Y-m-d H:i:s', $order['sendtime']),
                            "color" => "#4a5077"
                        ),
                        'keyword5' => array(
                            'title' => '确认收货时间',
                            'value' => date('Y-m-d H:i:s', $order['finishtime']),
                            "color" => "#4a5077"
                        ),
                        'remark' => array(
                            'title' => '',
                            'value' => $remark,
                            "color" => "#4a5077"
                        )
                    );
                    $account = m('common')->getAccount();
                    if (!empty($tm['openid'])) {
                        $openids = explode(',', $tm['openid']);
                        foreach ($openids as $tmopenid) {
                            if (empty($tmopenid)) {
                                continue;
                            }
                            if (!empty($tm['finish'])) {
                                m('message')->sendTplNotice($tmopenid, $tm['finish'], $msg, '', $account);
                            } else {
                                m('message')->sendCustomNotice($tmopenid, $msg, '', $account);
                            }
                        }
                    }
                }
                foreach ($order_goods as $og) {
                    $noticetype = explode(',', $og['noticetype']);
                    if ($og['noticetype'] == '2' || (is_array($noticetype) && in_array(2, $noticetype))) {
                        $goodstr = $og['title'] . '( ';
                        if (!empty($og['optiontitle'])) {
                            $goodstr .= " 规格: " . $og['optiontitle'];
                        }
                        $goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . "); ";
                        $msg = array(
                            'first' => array(
                                'value' => $first,
                                "color" => "#4a5077"
                            ),
                            'keyword1' => array(
                                'title' => '订单号',
                                'value' => $order['ordersn'],
                                "color" => "#4a5077"
                            ),
                            'keyword2' => array(
                                'title' => '商品名称',
                                'value' => $goodstr,
                                "color" => "#4a5077"
                            ),
                            'keyword3' => array(
                                'title' => '下单时间',
                                'value' => date('Y-m-d H:i:s', $order['createtime']),
                                "color" => "#4a5077"
                            ),
                            'keyword4' => array(
                                'title' => '发货时间',
                                'value' => date('Y-m-d H:i:s', $order['sendtime']),
                                "color" => "#4a5077"
                            ),
                            'keyword5' => array(
                                'title' => '确认收货时间',
                                'value' => date('Y-m-d H:i:s', $order['finishtime']),
                                "color" => "#4a5077"
                            ),
                            'remark' => array(
                                'title' => '',
                                'value' => $remark,
                                "color" => "#4a5077"
                            )
                        );
                        if (!empty($tm['finish'])) {
                            m('message')->sendTplNotice($og['noticeopenid'], $tm['finish'], $msg, '', $account);
                        } else {
                            m('message')->sendCustomNotice($og['noticeopenid'], $msg, '', $account);
                        }
                    }
                }
            }
        }
    }
    public function sendMemberUpgradeMessage($openid = '', $oldlevel = null, $level = null)
    {
        global $_W, $_GPC;
        $member     = m('member')->getMember($openid);
        $usernotice = unserialize($member['noticeset']);
        if (!is_array($usernotice)) {
            $usernotice = array();
        }
        $shop      = m('common')->getSysset('shop');
        $tm        = m('common')->getSysset('notice');
        $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member';
        if (strexists($detailurl, '/addons/sz_yi/')) {
            $detailurl = str_replace("/addons/sz_yi/", '/', $detailurl);
        }
        if (strexists($detailurl, '/core/mobile/order/')) {
            $detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
        }
        if (!$level) {
            $level = m('member')->getLevel($openid);
        }
        $defaultlevelname = empty($shop['levelname']) ? '普通会员' : $shop['levelname'];
        $msg              = array(
            'first' => array(
                'value' => "亲爱的" . $member['nickname'] . ', 恭喜您成功升级！',
                "color" => "#4a5077"
            ),
            'keyword1' => array(
                'title' => '任务名称',
                'value' => '会员升级',
                "color" => "#4a5077"
            ),
            'keyword2' => array(
                'title' => '通知类型',
                'value' => '您会员等级从 ' . $defaultlevelname . ' 升级为 ' . $level['levelname'] . ', 特此通知!',
                "color" => "#4a5077"
            ),
            'remark' => array(
                'value' => "\r\n您即可享有" . $level['levelname'] . '的专属优惠及服务！',
                "color" => "#4a5077"
            )
        );
        if (!empty($tm['upgrade']) && empty($usernotice['upgrade'])) {
            m('message')->sendTplNotice($openid, $tm['upgrade'], $msg, $detailurl);
        } else if (empty($usernotice['upgrade'])) {
            m('message')->sendCustomNotice($openid, $msg, $detailurl);
        }
    }
    public function sendMemberLogMessage($log_id = '')
    {
        global $_W, $_GPC;
        $log_info   = pdo_fetch('select * from ' . tablename('sz_yi_member_log') . ' where id=:id and uniacid=:uniacid limit 1', array(
            ':id' => $log_id,
            ':uniacid' => $_W['uniacid']
        ));
        $member     = m('member')->getMember($log_info['openid']);
        $shop       = m('common')->getSysset('shop');
        $usernotice = unserialize($member['noticeset']);
        if (!is_array($usernotice)) {
            $usernotice = array();
        }
        $account = m('common')->getAccount();
        if (!$account) {
            return;
        }
        $tm = m('common')->getSysset('notice');
        if ($log_info['type'] == 0) {
            if ($log_info['status'] == 1) {
                $product = "后台充值";
                if ($log_info['rechargetype'] == 'wechat') {
                    $product = "微信支付";
                } else if ($log_info == 'alipay') {
                    $product['rechargetype'] = "支付宝";
                }
                $money = '￥' . $log_info['money'] . '元';
                if ($log_info['gives'] > 0) {
                    $totalmoney = $log_info['money'] + $log_info['gives'];
                    $money .= "，系统赠送" . $log_info['gives'] . '元，合计:' . $totalmoney . '元';
                }
                $msg       = array(
                    'first' => array(
                        'value' => "恭喜您充值成功!",
                        "color" => "#4a5077"
                    ),
                    'money' => array(
                        'title' => '充值金额',
                        'value' => $money,
                        "color" => "#4a5077"
                    ),
                    'product' => array(
                        'title' => '充值方式',
                        'value' => $product,
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'value' => "\r\n谢谢您对我们的支持！",
                        "color" => "#4a5077"
                    )
                );
                $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member';
                if (strexists($detailurl, '/addons/sz_yi/')) {
                    $detailurl = str_replace("/addons/sz_yi/", '/', $detailurl);
                }
                if (strexists($detailurl, '/core/mobile/order/')) {
                    $detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
                }
                if (!empty($tm['recharge_ok']) && empty($usernotice['recharge_ok'])) {
                    m('message')->sendTplNotice($log_info['openid'], $tm['recharge_ok'], $msg, $detailurl);
                } else if (empty($usernotice['recharge_ok'])) {
                    m('message')->sendCustomNotice($log_info['openid'], $msg, $detailurl);
                }
            } else if ($log_info['status'] == 3) {
                $msg       = array(
                    'first' => array(
                        'value' => "充值退款成功!",
                        "color" => "#4a5077"
                    ),
                    'reason' => array(
                        'title' => '退款原因',
                        'value' => '【' . $shop['name'] . '】充值退款',
                        "color" => "#4a5077"
                    ),
                    'refund' => array(
                        'title' => '退款金额',
                        'value' => '￥' . $log_info['money'] . '元',
                        "color" => "#4a5077"
                    ),
                    'remark' => array(
                        'value' => "\r\n退款成功，请注意查收! 谢谢您对我们的支持！",
                        "color" => "#4a5077"
                    )
                );
                $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member';
                if (strexists($detailurl, '/addons/sz_yi/')) {
                    $detailurl = str_replace("/addons/sz_yi/", '/', $detailurl);
                }
                if (strexists($detailurl, '/core/mobile/order/')) {
                    $detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
                }
                if (!empty($tm['recharge_fund']) && empty($usernotice['recharge_fund'])) {
                    m('message')->sendTplNotice($log_info['openid'], $tm['recharge_fund'], $msg, $detailurl);
                } else if (empty($usernotice['recharge_fund'])) {
                    m('message')->sendCustomNotice($log_info['openid'], $msg, $detailurl);
                }
            }
        } else if ($log_info['type'] == 1 && $log_info['status'] == 0) {
            $msg       = array(
                'first' => array(
                    'value' => "提现申请已经成功提交!",
                    "color" => "#4a5077"
                ),
                'money' => array(
                    'title' => '提现金额',
                    'value' => '￥' . $log_info['money'] . '元',
                    "color" => "#4a5077"
                ),
                'timet' => array(
                    'title' => '提现时间',
                    'value' => date('Y-m-d H:i:s', $log_info['createtime']),
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "\r\n请等待我们的审核并打款！",
                    "color" => "#4a5077"
                )
            );
            $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member&p=log&type=1';
            if (strexists($detailurl, '/addons/sz_yi/')) {
                $detailurl = str_replace("/addons/sz_yi/", '/', $detailurl);
            }
            if (!empty($tm['withdraw']) && empty($usernotice['withdraw'])) {
                m('message')->sendTplNotice($log_info['openid'], $tm['withdraw'], $msg, $detailurl);
            } else if (empty($usernotice['withdraw'])) {
                m('message')->sendCustomNotice($log_info['openid'], $msg, $detailurl);
            }
        } else if ($log_info['type'] == 1 && $log_info['status'] == 1) {
            $msg       = array(
                'first' => array(
                    'value' => "恭喜您成功提现!",
                    "color" => "#4a5077"
                ),
                'money' => array(
                    'title' => '提现金额',
                    'value' => '￥' . $log_info['money'] . '元',
                    "color" => "#4a5077"
                ),
                'timet' => array(
                    'title' => '提现时间',
                    'value' => date('Y-m-d H:i:s', $log_info['createtime']),
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "\r\n感谢您的支持！",
                    "color" => "#4a5077"
                )
            );
            $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member&p=log&type=1';
            if (!empty($tm['withdraw_ok']) && empty($usernotice['withdraw_ok'])) {
                m('message')->sendTplNotice($log_info['openid'], $tm['withdraw_ok'], $msg, $detailurl);
            } else if (empty($usernotice['withdraw_ok'])) {
                m('message')->sendCustomNotice($log_info['openid'], $msg, $detailurl);
            }
        } else if ($log_info['type'] == 1 && $log_info['status'] == -1) {
            $msg       = array(
                'first' => array(
                    'value' => "抱歉，提现申请审核失败!",
                    "color" => "#4a5077"
                ),
                'money' => array(
                    'title' => '提现金额',
                    'value' => '￥' . $log_info['money'] . '元',
                    "color" => "#4a5077"
                ),
                'timet' => array(
                    'title' => '提现时间',
                    'value' => date('Y-m-d H:i:s', $log_info['createtime']),
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "\r\n有疑问请联系客服，谢谢您的支持！",
                    "color" => "#4a5077"
                )
            );
            $detailurl = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member&p=log&type=1';
            if (strexists($detailurl, '/addons/sz_yi/')) {
                $detailurl = str_replace("/addons/sz_yi/", '/', $detailurl);
            }
            if (strexists($detailurl, '/core/mobile/order/')) {
                $detailurl = str_replace("/core/mobile/order/", '/', $detailurl);
            }
            if (!empty($tm['withdraw_fail']) && empty($usernotice['withdraw_fail'])) {
                m('message')->sendTplNotice($log_info['openid'], $tm['withdraw_fail'], $msg, $detailurl);
            } else if (empty($usernotice['withdraw_fail'])) {
                m('message')->sendCustomNotice($log_info['openid'], $msg, $detailurl);
            }
        }
    }
}
