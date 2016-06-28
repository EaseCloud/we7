<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
if (!class_exists('VirtualModel')) {
    class VirtualModel extends PluginModel
    {
        public function updateGoodsStock($id = 0)
        {
            global $_W, $_GPC;
            $goods = pdo_fetch('select virtual from ' . tablename('sz_yi_goods') . ' where id=:id and type=3 and uniacid=:uniacid limit 1', array(
                ':id' => $id,
                ':uniacid' => $_W['uniacid']
            ));
            if (empty($goods)) {
                return;
            }
            $stock = 0;
            if (!empty($goods['virtual'])) {
                $stock = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_virtual_data') . " where typeid=:typeid and uniacid=:uniacid and openid='' limit 1", array(
                    ':typeid' => $goods['virtual'],
                    ':uniacid' => $_W['uniacid']
                ));
            } else {
                $virtuals   = array();
                $alloptions = pdo_fetchall("select id, virtual from " . tablename('sz_yi_goods_option') . " where goodsid=$id");
                foreach ($alloptions as $opt) {
                    if (empty($opt['virtual'])) {
                        continue;
                    }
                    $c = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_virtual_data') . " where typeid=:typeid and uniacid=:uniacid and openid='' limit 1", array(
                        ':typeid' => $opt['virtual'],
                        ':uniacid' => $_W['uniacid']
                    ));
                    pdo_update('sz_yi_goods_option', array(
                        'stock' => $c
                    ), array(
                        'id' => $opt['id']
                    ));
                    if (!in_array($opt['virtual'], $virtuals)) {
                        $virtuals[] = $opt['virtual'];
                        $stock += $c;
                    }
                }
            }
            pdo_update('sz_yi_goods', array(
                'total' => $stock
            ), array(
                "id" => $id
            ));
        }
        public function updateStock($typeid = 0)
        {
            global $_W;
            $goodsids = array();
            $goods    = pdo_fetchall('select id from ' . tablename('sz_yi_goods') . ' where type=3 and virtual=:virtual and uniacid=:uniacid limit 1', array(
                ':virtual' => $typeid,
                ':uniacid' => $_W['uniacid']
            ));
            foreach ($goods as $g) {
                $goodsids[] = $g['id'];
            }
            $alloptions = pdo_fetchall("select id, goodsid from " . tablename('sz_yi_goods_option') . " where virtual=:virtual and uniacid=:uniacid", array(
                ':uniacid' => $_W['uniacid'],
                ':virtual' => $typeid
            ));
            foreach ($alloptions as $opt) {
                if (!in_array($opt['goodsid'], $goodsids)) {
                    $goodsids[] = $opt['goodsid'];
                }
            }
            foreach ($goodsids as $gid) {
                $this->updateGoodsStock($gid);
            }
        }
        public function pay($order)
        {
            global $_W, $_GPC;
            $goods        = pdo_fetch('select id,goodsid,total,realprice from ' . tablename('sz_yi_order_goods') . ' where  orderid=:orderid and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':orderid' => $order['id']
            ));
            $g            = pdo_fetch('select id,credit,sales,salesreal from ' . tablename('sz_yi_goods') . ' where  id=:id and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':id' => $goods['goodsid']
            ));
            $virtual_data = pdo_fetchall('SELECT id,typeid,fields FROM ' . tablename('sz_yi_virtual_data') . ' WHERE typeid=:typeid and openid=:openid and uniacid=:uniacid order by rand() limit ' . $goods['total'], array(
                ':openid' => '',
                ':typeid' => $order['virtual'],
                ':uniacid' => $_W['uniacid']
            ));
            $type         = pdo_fetch('select fields from ' . tablename('sz_yi_virtual_type') . ' where id=:id and uniacid=:uniacid limit 1 ', array(
                ':id' => $order['virtual'],
                ':uniacid' => $_W['uniacid']
            ));
            $fields       = iunserializer($type['fields'], true);
            $virtual_info = array();
            $virtual_str  = array();
            foreach ($virtual_data as $vd) {
                $virtual_info[] = $vd['fields'];
                $strs           = array();
                $vddatas        = iunserializer($vd['fields']);
                foreach ($vddatas as $vk => $vv) {
                    $strs[] = $fields[$vk] . ": " . $vv;
                }
                $virtual_str[] = implode(" ", $strs);
                pdo_update('sz_yi_virtual_data', array(
                    'openid' => $order['openid'],
                    'orderid' => $order['id'],
                    'ordersn' => $order['ordersn'],
                    'price' => round($goods['realprice'] / $goods['total'], 2),
                    'usetime' => time()
                ), array(
                    'id' => $vd['id']
                ));
                pdo_update('sz_yi_virtual_type', 'usedata=usedata+1', array(
                    'id' => $vd['typeid']
                ));
                $this->updateStock($vd['typeid']);
            }
            $virtual_str  = implode("\n", $virtual_str);
            $virtual_info = "[" . implode(",", $virtual_info) . "]";
            $time         = time();
            pdo_update('sz_yi_order', array(
                'virtual_info' => $virtual_info,
                'virtual_str' => $virtual_str,
                'status' => '3',
                'paytime' => $time,
                'sendtime' => $time,
                'finishtime' => $time
            ), array(
                'id' => $order['id']
            ));
            if ($order['deductcredit2'] > 0) {
                $shopset = m('common')->getSysset('shop');
                m('member')->setCredit($order['openid'], 'credit2', -$order['deductcredit2'], array(
                    0,
                    $shopset['name'] . "余额抵扣: {$order['deductcredit2']} 订单号: " . $order['ordersn']
                ));
            }
            $credits = $goods['total'] * $g['credit'];
            if ($credits > 0) {
                $shopset = m('common')->getSysset('shop');
                m('member')->setCredit($order['openid'], 'credit1', $credits, array(
                    0,
                    $shopset['name'] . '购物积分 订单号: ' . $order['ordersn']
                ));
            }
            $salesreal = pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where og.goodsid=:goodsid and o.status>=1 and o.uniacid=:uniacid limit 1', array(
                ':goodsid' => $g['id'],
                ':uniacid' => $_W['uniacid']
            ));
            pdo_update('sz_yi_goods', array(
                'salesreal' => $salesreal
            ), array(
                'id' => $g['id']
            ));
            m('member')->upgradeLevel($order['openid']);
            m('notice')->sendOrderMessage($order['id']);
	    if (p('coupon') && !empty($order['couponid'])) {
	    	p('coupon')->backConsumeCoupon($order['id']);
	    }
            if (p('commission')) {
                p('commission')->checkOrderPay($order['id']);
                p('commission')->checkOrderFinish($order['id']);
            }
        }
        public function perms()
        {
            return array(
                'virtual' => array(
                    'text' => $this->getName(),
                    'isplugin' => true,
                    'child' => array(
                        'temp' => array(
                            'text' => '模板',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log'
                        ),
                        'data' => array(
                            'text' => '数据',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log',
                            'import' => '导入-log',
                            'export' => '导出已使用数据-log'
                        ),
                        'category' => array(
                            'text' => '分类',
                            'view' => '浏览',
                            'add' => '添加-log',
                            'edit' => '修改-log',
                            'delete' => '删除-log'
                        )
                    )
                )
            );
        }
    }
}
