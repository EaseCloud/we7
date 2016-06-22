<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$member    = m("member")->getMember($openid);
$uniacid   = $_W['uniacid'];
$fromcart  = 0;
$trade     = m('common')->getSysset('trade');
if (!empty($trade['shareaddress'])  && is_weixin()) {
    if (!$_W['isajax']) {
        $shareAddress = m('common')->shareAddress();
        if (empty($shareAddress)) {
            exit;
        }
    }
}
$pv = p('virtual');
$hascouponplugin = false;
$plugc           = p("coupon");
if ($plugc) {
    $hascouponplugin = true;
}
$diyform_plugin = p("diyform");
$order_formInfo = false;
if ($diyform_plugin) {
    $diyform_set = $diyform_plugin->getSet();
    if (!empty($diyform_set["order_diyform_open"])) {
        $orderdiyformid = intval($diyform_set["order_diyform"]);
        if (!empty($orderdiyformid)) {
            $order_formInfo = $diyform_plugin->getDiyformInfo($orderdiyformid);
            $fields         = $order_formInfo["fields"];
            $f_data         = $diyform_plugin->getLastOrderData($orderdiyformid, $member);
        }
    }
}
if ($operation == "display" || $operation == "create") {
    $id   = intval($_GPC["id"]);
    $show = 1;
    if ($diyform_plugin) {
        if (!empty($id)) {
            $sql         = "SELECT id as goodsid,type,diyformtype,diyformid,diymode FROM " . tablename("sz_yi_goods") . " where id=:id and uniacid=:uniacid  limit 1";
            $goods_data  = pdo_fetch($sql, array(
                ":uniacid" => $uniacid,
                ":id" => $id
            ));
            $diyformtype = $goods_data["diyformtype"];
            $diyformid   = $goods_data["diyformid"];
            $diymode     = $goods_data["diymode"];
            if (!empty($diyformtype) && !empty($diyformid)) {
                $formInfo      = $diyform_plugin->getDiyformInfo($diyformid);
                $goods_data_id = intval($_GPC["gdid"]);
            }
        }
    }
}
if ($_W['isajax']) {
    if ($operation == 'display') {
        $id       = intval($_GPC['id']);
        $optionid = intval($_GPC['optionid']);
        $total    = intval($_GPC['total']);
        $ids      = '';
        if ($total < 1) {
            $total = 1;
        }
        $buytotal  = $total;
        $isverify  = false;
        $isvirtual = false;
        $changenum = false;
        $goods     = array();
        if (empty($id)) {
            $condition = '';
            $cartids   = $_GPC['cartids'];
            if (!empty($cartids)) {
                $condition = ' and c.id in (' . $cartids . ')';
            }
            $sql   = 'SELECT c.goodsid,c.total,g.maxbuy,g.type,g.issendfree,g.isnodiscount,g.weight,o.weight as optionweight,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,o.title as optiontitle,c.optionid,g.storeids,g.isverify,g.deduct,g.virtual,o.virtual as optionvirtual,discounts FROM ' . tablename('sz_yi_member_cart') . ' c ' . ' left join ' . tablename('sz_yi_goods') . ' g on c.goodsid = g.id ' . ' left join ' . tablename('sz_yi_goods_option') . ' o on c.optionid = o.id ' . " where c.openid=:openid and  c.deleted=0 and c.uniacid=:uniacid {$condition} order by c.id desc";
            $goods = pdo_fetchall($sql, array(
                ':uniacid' => $uniacid,
                ':openid' => $openid
            ));
            if (empty($goods)) {
                show_json(-1, array(
                    'url' => $this->createMobileUrl('shop/cart')
                ));
            } else {
                foreach ($goods as $k => $v) {
                    if (!empty($v["optionvirtual"])) {
                        $goods[$k]["virtual"] = $v["optionvirtual"];
                    }
                    if (!empty($v["optionweight"])) {
                        $goods[$k]["weight"] = $v["optionweight"];
                    }
                }
            }
            $fromcart = 1;
        } else {
            $sql              = "SELECT id as goodsid,type,title,weight,issendfree,isnodiscount, thumb,marketprice,storeids,isverify,deduct, manydeduct, virtual,maxbuy,usermaxbuy,discounts,total as stock, deduct2, ednum, edmoney, edareas, diyformtype, diyformid, diymode, dispatchtype, dispatchid, dispatchprice FROM " . tablename("sz_yi_goods") . " where id=:id and uniacid=:uniacid  limit 1";
            $data             = pdo_fetch($sql, array(
                ':uniacid' => $uniacid,
                ':id' => $id
            ));
            $data['total']    = $total;
            $data['optionid'] = $optionid;
            if (!empty($optionid)) {
                $option = pdo_fetch('select id,title,marketprice,goodssn,productsn,virtual,stock,weight from ' . tablename('sz_yi_goods_option') . ' where id=:id and goodsid=:goodsid and uniacid=:uniacid  limit 1', array(
                    ':uniacid' => $uniacid,
                    ':goodsid' => $id,
                    ':id' => $optionid
                ));
                if (!empty($option)) {
                    $data['optionid']    = $optionid;
                    $data['optiontitle'] = $option['title'];
                    $data['marketprice'] = $option['marketprice'];
                    $data['virtual']     = $option['virtual'];
                    $data['stock']       = $option['stock'];
                    if (!empty($option['weight'])) {
                        $data['weight'] = $option['weight'];
                    }
                }
            }
            $changenum   = true;
            $totalmaxbuy = $data['stock'];
            if ($data['maxbuy'] > 0) {
                if ($totalmaxbuy != -1) {
                    if ($totalmaxbuy > $data['maxbuy']) {
                        $totalmaxbuy = $data['maxbuy'];
                    }
                } else {
                    $totalmaxbuy = $data['maxbuy'];
                }
            }
            if ($data['usermaxbuy'] > 0) {
                $order_goodscount = pdo_fetchcolumn('select ifnull(sum(og.total),0)  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id ' . ' where og.goodsid=:goodsid and  o.status>=1 and o.openid=:openid  and og.uniacid=:uniacid ', array(
                    ':goodsid' => $data['goodsid'],
                    ':uniacid' => $uniacid,
                    ':openid' => $openid
                ));
                $last             = $data['usermaxbuy'] - $order_goodscount;
                if ($last <= 0) {
                    $last = 0;
                }
                if ($totalmaxbuy != -1) {
                    if ($totalmaxbuy > $last) {
                        $totalmaxbuy = $last;
                    }
                } else {
                    $totalmaxbuy = $last;
                }
            }
            $data['totalmaxbuy'] = $totalmaxbuy;
            $goods[]             = $data;
        }
        $goods = set_medias($goods, 'thumb');
        foreach ($goods as $g) {
            if ($g['isverify'] == 2) {
                $isverify = true;
            }
            if (!empty($g['virtual']) || $g['type'] == 2) {
                $isvirtual = true;
            }
        }
        $member        = m('member')->getMember($openid);
        $level          = m("member")->getLevel($openid);
        $weight         = 0;
        $total          = 0;
        $goodsprice     = 0;
        $realprice      = 0;
        $deductprice    = 0;
        $discountprice  = 0;
        $deductprice2   = 0;
        $stores        = array();
        $address       = false;
        $carrier       = false;
        $carrier_list  = array();
        $dispatch_list = false;
        $dispatch_price = 0;
        $dispatch_array = array();
        $sale_plugin   = p('sale');
        $saleset       = false;
        if ($sale_plugin) {
            $saleset = $sale_plugin->getSet();
            $saleset["enoughs"] = $sale_plugin->getEnoughs();
        }
        //$carrier_list = pdo_fetchall("select * from " . tablename("sz_yi_store") . " where  uniacid=:uniacid and status=1 and type in(1,3)", array(
        $carrier_list = pdo_fetchall("select * from " . tablename("sz_yi_store") . " where  uniacid=:uniacid and status=1", array(
            ":uniacid" => $_W["uniacid"]
        ));
        if (!empty($carrier_list)) {
            $carrier = $carrier_list[0];
        }
        foreach ($goods as &$g) {
            if (empty($g["total"]) || intval($g["total"]) == "-1") {
                $g["total"] = 1;
            }
            $gprice    = $g["marketprice"] * $g["total"];
            $discounts = json_decode($g["discounts"], true);
            if (is_array($discounts)) {
                if (!empty($level["id"])) {
                    if (floatval($discounts["level" . $level["id"]]) > 0 && floatval($discounts["level" . $level["id"]]) < 10) {
                        $level["discount"] = floatval($discounts["level" . $level["id"]]);
                    } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                        $level["discount"] = floatval($level["discount"]);
                    } else {
                        $level["discount"] = 0;
                    }
                } else {
                    if (floatval($discounts["default"]) > 0 && floatval($discounts["default"]) < 10) {
                        $level["discount"] = floatval($discounts["default"]);
                    } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                        $level["discount"] = floatval($level["discount"]);
                    } else {
                        $level["discount"] = 0;
                    }
                }
            }
            if (empty($g["isnodiscount"]) && floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                $price = round(floatval($level["discount"]) / 10 * $gprice, 2);
                $discountprice += $gprice - $price;
            } else {
                $price = $gprice;
            }
            $g["ggprice"] = $price;
            $realprice += $price;
            $goodsprice += $gprice;
            $total += $g["total"];
            if ($g["manydeduct"]) {
                $deductprice += $g["deduct"] * $g["total"];
            } else {
                $deductprice += $g["deduct"];
            }
            if ($g["deduct2"] == 0) {
                $deductprice2 += $price;
            } else if ($g["deduct2"] > 0) {
                if ($g["deduct2"] > $price) {
                    $deductprice2 += $price;
                } else {
                    $deductprice2 += $g["deduct2"];
                }
            }
        }
        unset($g);
        if ($isverify) {
            $storeids = array();
            foreach ($goods as $g) {
                if (!empty($g['storeids'])) {
                    $storeids = array_merge(explode(',', $g['storeids']), $storeids);
                }
            }
            if (empty($storeids)) {
                $stores = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where  uniacid=:uniacid and status=1 and type in(2,3)', array(
                    ':uniacid' => $_W['uniacid']
                ));
            } else {
                $stores = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where id in (' . implode(',', $storeids) . ') and uniacid=:uniacid and status=1 and type in(2,3)', array(
                    ':uniacid' => $_W['uniacid']
                ));
            }
        } else {
            $address      = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and isdefault=1  and uniacid=:uniacid limit 1', array(
                ':uniacid' => $uniacid,
                ':openid' => $openid
            ));
            if (!empty($carrier_list)) {
                $carrier = $carrier_list[0];
            }
            if (!$isvirtual) {
                foreach ($goods as $g) {
                    $sendfree = false;
                    if (!empty($g["issendfree"])) {
                        $sendfree = true;
                    } else {
                        if ($g["total"] >= $g["ednum"] && $g["ednum"] > 0) {
                            $gareas = explode(";", $g["edareas"]);
                            if (empty($gareas)) {
                                $sendfree = true;
                            } else {
                                if (!empty($address)) {
                                    if (!in_array($address["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else if (!empty($member["city"])) {
                                    if (!in_array($member["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else {
                                    $sendfree = true;
                                }
                            }
                        }
                        if ($g["ggprice"] >= floatval($g["edmoney"]) && floatval($g["edmoney"]) > 0) {
                            $gareas = unserialize($g["edareas"]);
                            if (empty($gareas)) {
                                $sendfree = true;
                            } else {
                                if (!empty($address)) {
                                    if (!in_array($address["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else if (!empty($member["city"])) {
                                    if (!in_array($member["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else {
                                    $sendfree = true;
                                }
                            }
                        }
                    }
                    if (!$sendfree) {
                        if ($g["dispatchtype"] == 1) {
                            if ($g["dispatchprice"] > 0) {
                                $dispatch_price += $g["dispatchprice"] * $g["total"];
                            }
                        } else if ($g["dispatchtype"] == 0) {
                            if (empty($g["dispatchid"])) {
                                $dispatch_data = m("order")->getDefaultDispatch();
                            } else {
                                $dispatch_data = m("order")->getOneDispatch($g["dispatchid"]);
                            }
                            if (empty($dispatch_data)) {
                                $dispatch_data = m("order")->getNewDispatch();
                            }
                            if (!empty($dispatch_data)) {
                                $areas = unserialize($dispatch_data["areas"]);
                                if ($dispatch_data["calculatetype"] == 1) {
                                    $param = $g["total"];
                                } else {
                                    $param = $g["weight"] * $g["total"];
                                }
                                $dkey = $dispatch_data["id"];
                                if (array_key_exists($dkey, $dispatch_array)) {
                                    $dispatch_array[$dkey]["param"] += $param;
                                } else {
                                    $dispatch_array[$dkey]["data"]  = $dispatch_data;
                                    $dispatch_array[$dkey]["param"] = $param;
                                }
                            }
                        }
                    }
                }
                if (!empty($dispatch_array)) {
                    foreach ($dispatch_array as $k => $v) {
                        $dispatch_data = $dispatch_array[$k]["data"];
                        $param         = $dispatch_array[$k]["param"];
                        $areas         = unserialize($dispatch_data["areas"]);
                        if (!empty($address)) {
                            $dispatch_price += m("order")->getCityDispatchPrice($areas, $address["city"], $param, $dispatch_data);
                        } else if (!empty($member["city"])) {
                            $dispatch_price += m("order")->getCityDispatchPrice($areas, $member["city"], $param, $dispatch_data);
                        } else {
                            $dispatch_price += m("order")->getDispatchPrice($param, $dispatch_data);
                        }
                    }
                }
            }
        }
        if ($saleset) {
            if (!empty($saleset["enoughfree"])) {
                if (floatval($saleset["enoughorder"]) <= 0) {
                    $dispatch_price = 0;
                } else {
                    if ($realprice >= floatval($saleset["enoughorder"])) {
                        if (empty($saleset["enoughareas"])) {
                            $dispatch_price = 0;
                        } else {
                            $areas = explode(",", $saleset["enoughareas"]);
                            if (!empty($address)) {
                                if (!in_array($address["city"], $areas)) {
                                    $dispatch_price = 0;
                                }
                            } else if (!empty($member["city"])) {
                                if (!in_array($member["city"], $areas)) {
                                    $dispatch_price = 0;
                                }
                            } else if (empty($member["city"])) {
                                $dispatch_price = 0;
                            }
                        }
                    }
                }
            }
            foreach ($saleset["enoughs"] as $e) {
                if ($realprice >= floatval($e["enough"]) && floatval($e["money"]) > 0) {
                    $saleset["showenough"]   = true;
                    $saleset["enoughmoney"]  = $e["enough"];
                    $saleset["enoughdeduct"] = $e["money"];
                    $realprice -= floatval($e["money"]);
                    break;
                }
            }
            if (empty($saleset["dispatchnodeduct"])) {
                $deductprice2 += $dispatch_price;
            }
        }
        $hascoupon = false;
        if ($hascouponplugin) {
            $couponcount = $plugc->consumeCouponCount($openid, $realprice);
            $hascoupon   = $couponcount > 0;
        }
        $realprice += $dispatch_price;
        $deductcredit  = 0;
        $deductmoney   = 0;
        $deductcredit2 = 0;
        if ($sale_plugin) {
            $credit = m('member')->getCredit($openid, 'credit1');
            if (!empty($saleset['creditdeduct'])) {
                $pcredit = intval($saleset['credit']);
                $pmoney  = round(floatval($saleset['money']), 2);
                if ($pcredit > 0 && $pmoney > 0) {
                    if ($credit % $pcredit == 0) {
                        $deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
                    } else {
                        $deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
                    }
                }
                if ($deductmoney > $deductprice) {
                    $deductmoney = $deductprice;
                }
                if ($deductmoney > $realprice) {
                    $deductmoney = $realprice;
                }
                $deductcredit = $deductmoney / $pmoney * $pcredit;
            }
            if (!empty($saleset['moneydeduct'])) {
                $deductcredit2 = m('member')->getCredit($openid, 'credit2');
                if ($deductcredit2 > $realprice) {
                    $deductcredit2 = $realprice;
                }
                if ($deductcredit2 > $deductprice2) {
                    $deductcredit2 = $deductprice2;
                }
            }
        }
        show_json(1, array(
            'member' => $member,
            'deductcredit' => $deductcredit,
            'deductmoney' => $deductmoney,
            'deductcredit2' => $deductcredit2,
            'saleset' => $saleset,
            'goods' => $goods,
            'weight' => $weight / $buytotal,
            'set' => m('common')->getSysset('shop'),
            'fromcart' => $fromcart,
            'haslevel' => !empty($level['id']) && $level['discount'] > 0 && $level['discount'] < 10,
            'total' => $total,
            "dispatchprice" => number_format($dispatch_price, 2),
            'totalprice' => number_format($totalprice, 2),
            'goodsprice' => number_format($goodsprice, 2),
            'discountprice' => number_format($discountprice, 2),
            'discount' => $level['discount'],
            'realprice' => number_format($realprice, 2),
            'address' => $address,
            'carrier' => $carrier,
            'carrier_list' => $carrier_list,
            'dispatch_list' => $dispatch_list,
            'isverify' => $isverify,
            'stores' => $stores,
            'isvirtual' => $isvirtual,
            'changenum' => $changenum,
            'hascoupon' => $hascoupon,
            'couponcount' => $couponcount
        ));
    } else if ($operation == 'getdispatchprice') {
        $isverify       = false;
        $isvirtual      = false;
        $deductprice    = 0;
        $deductprice2   = 0;
        $deductcredit2  = 0;
        $dispatch_array = array();
        $totalprice = floatval($_GPC['totalprice']);
        $dflag          = $_GPC["dflag"];
        $hascoupon      = false;
        $couponcount    = 0;
        $pc             = p("coupon");
        if ($pc) {
            $pset = $pc->getSet();
            if (empty($pset["closemember"])) {
                $couponcount = $pc->consumeCouponCount($openid, $totalprice);
                $hascoupon   = $couponcount > 0;
            }
        }
        $addressid           = intval($_GPC["addressid"]);
        $address     = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where  id=:id and openid=:openid and uniacid=:uniacid limit 1', array(
            ':uniacid' => $uniacid,
            ':openid' => $openid,
            ':id' => $addressid
        ));
        $member              = m("member")->getMember($openid);
        $level               = m("member")->getLevel($openid);
        $weight              = $_GPC["weight"];
        $dispatch_price      = 0;
        $deductenough_money  = 0;
        $deductenough_enough = 0;
        $sale_plugin = p('sale');
        $saleset     = false;
        if ($sale_plugin) {
            $saleset = $sale_plugin->getSet();
            $saleset["enoughs"] = $sale_plugin->getEnoughs();
        }
        if ($sale_plugin) {
            if ($saleset) {
                foreach ($saleset["enoughs"] as $e) {
                    if ($totalprice >= floatval($e["enough"]) && floatval($e["money"]) > 0) {
                        $deductenough_money  = floatval($e["money"]);
                        $deductenough_enough = floatval($e["enough"]);
                        break;
                    }
                }
                if (!empty($saleset['enoughfree'])) {
                    if (floatval($saleset['enoughorder']) <= 0) {
                        show_json(1, array(
                            'price' => 0,
                            "hascoupon" => $hascoupon,
                            "couponcount" => $couponcount,
                            "deductenough_money" => $deductenough_money,
                            "deductenough_enough" => $deductenough_enough
                        ));
                    }
                }
                if (!empty($saleset['enoughfree']) && $totalprice >= floatval($saleset['enoughorder'])) {
                    if (!empty($saleset['enoughareas'])) {
                        $areas = explode(";", $saleset['enoughareas']);
                        if (!in_array($address['city'], $areas)) {
                            show_json(1, array(
                                "price" => 0,
                                "hascoupon" => $hascoupon,
                                "couponcount" => $couponcount,
                                "deductenough_money" => $deductenough_money,
                                "deductenough_enough" => $deductenough_enough
                            ));
                        }
                    } else {
                        show_json(1, array(
                            "price" => 0,
                            "hascoupon" => $hascoupon,
                            "couponcount" => $couponcoun,
                            "deductenough_money" => $deductenough_money,
                            "deductenough_enough" => $deductenough_enough
                        ));
                    }
                }
            }
        }
        $goods = trim($_GPC["goods"]);
        if (!empty($goods)) {
            $weight   = 0;
            $allgoods = array();
            $goodsarr = explode("|", $goods);
            foreach ($goodsarr as &$g) {
                if (empty($g)) {
                    continue;
                }
                $goodsinfo  = explode(",", $g);
                $goodsid    = !empty($goodsinfo[0]) ? intval($goodsinfo[0]) : '';
                $optionid   = !empty($goodsinfo[1]) ? intval($goodsinfo[1]) : 0;
                $goodstotal = !empty($goodsinfo[2]) ? intval($goodsinfo[2]) : "1";
                if ($goodstotal < 1) {
                    $goodstotal = 1;
                }
                if (empty($goodsid)) {
                    show_json(1, array(
                        "price" => 0
                    ));
                }
                $sql  = "SELECT id as goodsid,title,type, weight,total,issendfree,isnodiscount, thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,manydeduct,virtual,discounts,deduct2,ednum,edmoney,edareas,diyformid,diyformtype,diymode,dispatchtype,dispatchid,dispatchprice FROM " . tablename("sz_yi_goods") . " where id=:id and uniacid=:uniacid  limit 1";
                $data = pdo_fetch($sql, array(
                    ":uniacid" => $uniacid,
                    ":id" => $goodsid
                ));
                if (empty($data)) {
                    show_json(1, array(
                        "price" => 0
                    ));
                }
                $data["stock"] = $data["total"];
                $data["total"] = $goodstotal;
                if (!empty($optionid)) {
                    $option = pdo_fetch("select id,title,marketprice,goodssn,productsn,stock,virtual,weight from " . tablename("sz_yi_goods_option") . " where id=:id and goodsid=:goodsid and uniacid=:uniacid  limit 1", array(
                        ":uniacid" => $uniacid,
                        ":goodsid" => $goodsid,
                        ":id" => $optionid
                    ));
                    if (!empty($option)) {
                        $data["optionid"]    = $optionid;
                        $data["optiontitle"] = $option["title"];
                        $data["marketprice"] = $option["marketprice"];
                        if (!empty($option["weight"])) {
                            $data["weight"] = $option["weight"];
                        }
                    }
                }
                $discounts = json_decode($data["discounts"], true);
                if (is_array($discounts)) {
                    if (!empty($level["id"])) {
                        if ($discounts["level" . $level["id"]] > 0 && $discounts["level" . $level["id"]] < 10) {
                            $level["discount"] = $discounts["level" . $level["id"]];
                        } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                            $level["discount"] = floatval($level["discount"]);
                        } else {
                            $level["discount"] = 0;
                        }
                    } else {
                        if ($discounts["default"] > 0 && $discounts["default"] < 10) {
                            $level["discount"] = $discounts["default"];
                        } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                            $level["discount"] = floatval($level["discount"]);
                        } else {
                            $level["discount"] = 0;
                        }
                    }
                }
                $gprice  = $data["marketprice"] * $goodstotal;
                $ggprice = 0;
                if (empty($data["isnodiscount"]) && $level["discount"] > 0 && $level["discount"] < 10) {
                    $dprice = round($gprice * $level["discount"] / 10, 2);
                    $discountprice += $gprice - $dprice;
                    $ggprice = $dprice;
                } else {
                    $ggprice = $gprice;
                }
                $data["ggprice"] = $ggprice;
                $allgoods[]      = $data;
            }
            unset($g);
            foreach ($allgoods as $g) {
                if ($g["isverify"] == 2) {
                    $isverify = true;
                }
                if (!empty($g["virtual"]) || $g["type"] == 2) {
                    $isvirtual = true;
                }
                if ($g["manydeduct"]) {
                    $deductprice += $g["deduct"] * $g["total"];
                } else {
                    $deductprice += $g["deduct"];
                }
                if ($g["deduct2"] == 0) {
                    $deductprice2 += $g["ggprice"];
                } else if ($g["deduct2"] > 0) {
                    if ($g["deduct2"] > $g["ggprice"]) {
                        $deductprice2 += $g["ggprice"];
                    } else {
                        $deductprice2 += $g["deduct2"];
                    }
                }
            }
            if ($isverify) {
                show_json(1, array(
                    "price" => 0,
                    "hascoupon" => $hascoupon,
                    "couponcount" => $couponcount
                ));
            }
            if (!empty($allgoods)) {
                foreach ($allgoods as $g) {
                    $sendfree = false;
                    if (!empty($g["issendfree"])) {
                        $sendfree = true;
                    }
                    if ($g["type"] == 2 || $g["type"] == 3) {
                        $sendfree = true;
                    } else {
                        if ($g["total"] >= $g["ednum"] && $g["ednum"] > 0) {
                            $gareas = explode(";", $g["edareas"]);
                            if (empty($gareas)) {
                                $sendfree = true;
                            } else {
                                if (!empty($address)) {
                                    if (!in_array($address["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else if (!empty($member["city"])) {
                                    if (!in_array($member["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else {
                                    $sendfree = true;
                                }
                            }
                        }
                        if ($g["ggprice"] >= floatval($g["edmoney"]) && floatval($g["edmoney"]) > 0) {
                            $gareas = unserialize($g["edareas"]);
                            if (empty($gareas)) {
                                $sendfree = true;
                            } else {
                                if (!empty($address)) {
                                    if (!in_array($address["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else if (!empty($member["city"])) {
                                    if (!in_array($member["city"], $gareas)) {
                                        $sendfree = true;
                                    }
                                } else {
                                    $sendfree = true;
                                }
                            }
                        }
                    }
                    if (!$sendfree) {
                        if ($g["dispatchtype"] == 1) {
                            if ($g["dispatchprice"] > 0) {
                                $dispatch_price += $g["dispatchprice"] * $g["total"];
                            }
                        } else if ($g["dispatchtype"] == 0) {
                            if (empty($g["dispatchid"])) {
                                $dispatch_data = m("order")->getDefaultDispatch();
                            } else {
                                $dispatch_data = m("order")->getOneDispatch($g["dispatchid"]);
                            }
                            if (empty($dispatch_data)) {
                                $dispatch_data = m("order")->getNewDispatch();
                            }
                            if (!empty($dispatch_data)) {
                                $areas = unserialize($dispatch_data["areas"]);
                                if ($dispatch_data["calculatetype"] == 1) {
                                    $param = $g["total"];
                                } else {
                                    $param = $g["weight"] * $g["total"];
                                }
                                $dkey = $dispatch_data["id"];
                                if (array_key_exists($dkey, $dispatch_array)) {
                                    $dispatch_array[$dkey]["param"] += $param;
                                } else {
                                    $dispatch_array[$dkey]["data"]  = $dispatch_data;
                                    $dispatch_array[$dkey]["param"] = $param;
                                }
                            }
                        }
                    }
                }
                if (!empty($dispatch_array)) {
                    foreach ($dispatch_array as $k => $v) {
                        $dispatch_data = $dispatch_array[$k]["data"];
                        $param         = $dispatch_array[$k]["param"];
                        $areas         = unserialize($dispatch_data["areas"]);
                        if (!empty($address)) {
                            $dispatch_price += m("order")->getCityDispatchPrice($areas, $address["city"], $param, $dispatch_data);
                        } else if (!empty($member["city"])) {
                            $dispatch_price += m("order")->getCityDispatchPrice($areas, $member["city"], $param, $dispatch_data);
                        } else {
                            $dispatch_price += m("order")->getDispatchPrice($param, $dispatch_data);
                        }
                    }
                }
            }
            if ($dflag != "true") {
                if (empty($saleset["dispatchnodeduct"])) {
                    $deductprice2 += $dispatch_price;
                }
            }
            $deductcredit = 0;
            $deductmoney  = 0;
            if ($sale_plugin) {
                $credit = m("member")->getCredit($openid, "credit1");
                if (!empty($saleset["creditdeduct"])) {
                    $pcredit = intval($saleset["credit"]);
                    $pmoney  = round(floatval($saleset["money"]), 2);
                    if ($pcredit > 0 && $pmoney > 0) {
                        if ($credit % $pcredit == 0) {
                            $deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
                        } else {
                            $deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
                        }
                    }
                    if ($deductmoney > $deductprice) {
                        $deductmoney = $deductprice;
                    }
                    if ($deductmoney > $totalprice) {
                        $deductmoney = $totalprice;
                    }
                    $deductcredit = $deductmoney / $pmoney * $pcredit;
                }
                if (!empty($saleset["moneydeduct"])) {
                    $deductcredit2 = m("member")->getCredit($openid, "credit2");
                    if ($deductcredit2 > $totalprice) {
                        $deductcredit2 = $totalprice;
                    }
                    if ($deductcredit2 > $deductprice2) {
                        $deductcredit2 = $deductprice2;
                    }
                }
            }
        }
        show_json(1, array(
            "price" => $dispatch_price,
            "hascoupon" => $hascoupon,
            "couponcount" => $couponcount,
            "deductenough_money" => $deductenough_money,
            "deductenough_enough" => $deductenough_enough,
            "deductcredit2" => $deductcredit2,
            "deductcredit" => $deductcredit,
            "deductmoney" => $deductmoney
        ));
    } else if ($operation == 'create' && $_W['ispost']) {
        $member       = m('member')->getMember($openid);
        $dispatchtype = intval($_GPC['dispatchtype']);
        $addressid    = intval($_GPC['addressid']);
        $address      = false;
        if (!empty($addressid) && $dispatchtype == 0) {
            $address = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where id=:id and openid=:openid and uniacid=:uniacid   limit 1', array(
                ':uniacid' => $uniacid,
                ':openid' => $openid,
                ':id' => $addressid
            ));
            if (empty($address)) {
                show_json(0, '未找到地址');
            }
        }
        $carrierid = intval($_GPC["carrierid"]);
        $goods = $_GPC['goods'];
        if (empty($goods)) {
            show_json(0, '未找到任何商品');
        }
        $allgoods      = array();
        $totalprice    = 0;
        $goodsprice    = 0;
        $weight        = 0;
        $discountprice = 0;
        $goodsarr      = explode('|', $goods);
        $cash          = 1;
        $level         = m('member')->getLevel($openid);
        $deductprice   = 0;
        $deductprice2   = 0;
        $virtualsales  = 0;
        $dispatch_price = 0;
        $dispatch_array = array();
        $sale_plugin   = p('sale');
        $saleset       = false;
        if ($sale_plugin) {
            $saleset = $sale_plugin->getSet();
            $saleset["enoughs"] = $sale_plugin->getEnoughs();
        }
        $isvirtual = false;
        $isverify  = false;
        foreach ($goodsarr as $g) {
            if (empty($g)) {
                continue;
            }
            $goodsinfo  = explode(',', $g);
            $goodsid    = !empty($goodsinfo[0]) ? intval($goodsinfo[0]) : '';
            $optionid   = !empty($goodsinfo[1]) ? intval($goodsinfo[1]) : 0;
            $goodstotal = !empty($goodsinfo[2]) ? intval($goodsinfo[2]) : '1';
            if ($goodstotal < 1) {
                $goodstotal = 1;
            }
            if (empty($goodsid)) {
                show_json(0, '参数错误，请刷新重试');
            }
            if(p('supplier')){
                $sql  = 'SELECT id as goodsid,supplier_uid,title,type, weight,total,issendfree,isnodiscount, thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,manydeduct,virtual,discounts,deduct2,ednum,edmoney,edareas,diyformtype,diyformid,diymode,dispatchtype,dispatchid,dispatchprice FROM ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid  limit 1';
			}else{
                $sql  = 'SELECT id as goodsid,title,type, weight,total,issendfree,isnodiscount, thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,manydeduct,virtual,discounts,deduct2,ednum,edmoney,edareas,diyformtype,diyformid,diymode,dispatchtype,dispatchid,dispatchprice FROM ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid  limit 1';
            }
            $data = pdo_fetch($sql, array(
                ':uniacid' => $uniacid,
                ':id' => $goodsid
            ));
            if (empty($data['status']) || !empty($data['deleted'])) {
                show_json(-1, $data['title'] . '<br/> 已下架!');
            }
            $virtualid     = $data['virtual'];
            $data['stock'] = $data['total'];
            $data['total'] = $goodstotal;
            if ($data['cash'] != 2) {
                $cash = 0;
            }
            $unit = empty($data['unit']) ? '件' : $data['unit'];
            if ($data['maxbuy'] > 0) {
                if ($goodstotal > $data['maxbuy']) {
                    show_json(-1, $data['title'] . '<br/> 一次限购 ' . $data['maxbuy'] . $unit . "!");
                }
            }
            if ($data['usermaxbuy'] > 0) {
                $order_goodscount = pdo_fetchcolumn('select ifnull(sum(og.total),0)  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id ' . ' where og.goodsid=:goodsid and  o.status>=1 and o.openid=:openid  and og.uniacid=:uniacid ', array(
                    ':goodsid' => $data['goodsid'],
                    ':uniacid' => $uniacid,
                    ':openid' => $openid
                ));
                if ($order_goodscount >= $data['usermaxbuy']) {
                    show_json(-1, $data['title'] . '<br/> 最多限购 ' . $data['usermaxbuy'] . $unit . "!");
                }
            }
            if ($data['istime'] == 1) {
                if (time() < $data['timestart']) {
                    show_json(-1, $data['title'] . '<br/> 限购时间未到!');
                }
                if (time() > $data['timeend']) {
                    show_json(-1, $data['title'] . '<br/> 限购时间已过!');
                }
            }
            $levelid = intval($member['level']);
            $groupid = intval($member['groupid']);
            if ($data['buylevels'] != '') {
                $buylevels = explode(',', $data['buylevels']);
                if (!in_array($levelid, $buylevels)) {
                    show_json(-1, '您的会员等级无法购买<br/>' . $data['title'] . '!');
                }
            }
            if ($data['buygroups'] != '') {
                $buygroups = explode(',', $data['buygroups']);
                if (!in_array($groupid, $buygroups)) {
                    show_json(-1, '您所在会员组无法购买<br/>' . $data['title'] . '!');
                }
            }
            if (!empty($optionid)) {
                $option = pdo_fetch('select id,title,marketprice,goodssn,productsn,stock,virtual,weight from ' . tablename('sz_yi_goods_option') . ' where id=:id and goodsid=:goodsid and uniacid=:uniacid  limit 1', array(
                    ':uniacid' => $uniacid,
                    ':goodsid' => $goodsid,
                    ':id' => $optionid
                ));
                if (!empty($option)) {
                    if ($option['stock'] != -1) {
                        if (empty($option['stock'])) {
                            show_json(-1, $data['title'] . "<br/>" . $option['title'] . " 库存不足!");
                        }
                    }
                    $data['optionid']    = $optionid;
                    $data['optiontitle'] = $option['title'];
                    $data['marketprice'] = $option['marketprice'];
                    $virtualid           = $option['virtual'];
                    if (!empty($option['goodssn'])) {
                        $data['goodssn'] = $option['goodssn'];
                    }
                    if (!empty($option['productsn'])) {
                        $data['productsn'] = $option['productsn'];
                    }
                    if (!empty($option['weight'])) {
                        $data['weight'] = $option['weight'];
                    }
                }
            } else {
                if ($data['stock'] != -1) {
                    if (empty($data['stock'])) {
                        show_json(-1, $data['title'] . "<br/>库存不足!");
                    }
                }
            }
            $data["diyformdataid"] = 0;
            $data["diyformdata"]   = iserializer(array());
            $data["diyformfields"] = iserializer(array());
            if ($_GPC["fromcart"] == 1) {
                if ($diyform_plugin) {
                    $cartdata = pdo_fetch("select id,diyformdataid,diyformfields,diyformdata from " . tablename("sz_yi_member_cart") . " " . " where goodsid=:goodsid and optionid=:optionid and openid=:openid and deleted=0 order by id desc limit 1", array(
                        ":goodsid" => $data["goodsid"],
                        ":optionid" => $data["optionid"],
                        ":openid" => $openid
                    ));
                    if (!empty($cartdata)) {
                        $data["diyformdataid"] = $cartdata["diyformdataid"];
                        $data["diyformdata"]   = $cartdata["diyformdata"];
                        $data["diyformfields"] = $cartdata["diyformfields"];
                    }
                }
            } else {
                if (!empty($diyformtype) && !empty($data["diyformid"])) {
                    $temp_data             = $diyform_plugin->getOneDiyformTemp($goods_data_id, 0);
                    $data["diyformfields"] = $temp_data["diyformfields"];
                    $data["diyformdata"]   = $temp_data["diyformdata"];
                    $data["diyformid"]     = $formInfo["id"];
                }
            }
            $gprice = $data['marketprice'] * $goodstotal;
            $goodsprice += $gprice;
            $discounts = json_decode($data['discounts'], true);
            if (is_array($discounts)) {
                if (!empty($level["id"])) {
                    if (floatval($discounts["level" . $level["id"]]) > 0 && floatval($discounts["level" . $level["id"]]) < 10) {
                        $level["discount"] = floatval($discounts["level" . $level["id"]]);
                    } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                        $level["discount"] = floatval($level["discount"]);
                    } else {
                        $level["discount"] = 0;
                    }
                } else {
                    if (floatval($discounts["default"]) > 0 && floatval($discounts["default"]) < 10) {
                        $level["discount"] = floatval($discounts["default"]);
                    } else if (floatval($level["discount"]) > 0 && floatval($level["discount"]) < 10) {
                        $level["discount"] = floatval($level["discount"]);
                    } else {
                        $level["discount"] = 0;
                    }
                }
            }
            $ggprice = 0;
            if (empty($data['isnodiscount']) && $level['discount'] > 0 && $level['discount'] < 10) {
                $dprice = round($gprice * $level['discount'] / 10, 2);
                $discountprice += $gprice - $dprice;
                $ggprice = $dprice;
            } else {
                $ggprice = $gprice;
            }
            $data["realprice"] = $ggprice;
            $totalprice += $ggprice;
            if ($data['isverify'] == 2) {
                $isverify = true;
            }
            if (!empty($data["virtual"]) || $data["type"] == 2) {
                $isvirtual = true;
            }
            if ($data["manydeduct"]) {
                $deductprice += $data["deduct"] * $data["total"];
            } else {
                $deductprice += $data["deduct"];
            }
            $virtualsales += $data["sales"];
            if ($data["deduct2"] == 0) {
                $deductprice2 += $ggprice;
            } else if ($data["deduct2"] > 0) {
                if ($data["deduct2"] > $ggprice) {
                    $deductprice2 += $ggprice;
                } else {
                    $deductprice2 += $data["deduct2"];
                }
            }
            $allgoods[] = $data;
        }
        if (empty($allgoods)) {
            show_json(0, '未找到任何商品');
        }
        $deductenough = 0;
        if ($saleset) {
            foreach ($saleset["enoughs"] as $e) {
                if ($totalprice >= floatval($e["enough"]) && floatval($e["money"]) > 0) {
                    $deductenough = floatval($e["money"]);
            if ($deductenough > $totalprice) {
                $deductenough = $totalprice;
                    }
                    break;
                }
            }
        }
        if (!$isvirtual && !$isverify && $dispatchtype == 0) {
            foreach ($allgoods as $g) {
                $sendfree = false;
                if (!empty($g["issendfree"])) {
                    $sendfree = true;
                } else {
                    if ($g["total"] >= $g["ednum"] && $g["ednum"] > 0) {
                        $gareas = explode(";", $g["edareas"]);
                        if (empty($gareas)) {
                            $sendfree = true;
                        } else {
                            if (!empty($address)) {
                                if (!in_array($address["city"], $gareas)) {
                                    $sendfree = true;
                                }
                            } else if (!empty($member["city"])) {
                                if (!in_array($member["city"], $gareas)) {
                                    $sendfree = true;
                                }
                            } else {
                                $sendfree = true;
                            }
                        }
                    }
                    if ($g["ggprice"] >= floatval($g["edmoney"]) && floatval($g["edmoney"]) > 0) {
                        $gareas = unserialize($g["edareas"]);
                        if (empty($gareas)) {
                            $sendfree = true;
                        } else {
                            if (!empty($address)) {
                                if (!in_array($address["city"], $gareas)) {
                                    $sendfree = true;
                                }
                            } else if (!empty($member["city"])) {
                                if (!in_array($member["city"], $gareas)) {
                                    $sendfree = true;
                                }
                            } else {
                                $sendfree = true;
                            }
                        }
                    }
                }
                if (!$sendfree) {
                    if ($g["dispatchtype"] == 1) {
                        if ($g["dispatchprice"] > 0) {
                            $dispatch_price += $g["dispatchprice"] * $g["total"];
                        }
                    } else if ($g["dispatchtype"] == 0) {
                        if (empty($g["dispatchid"])) {
                            $dispatch_data = m("order")->getDefaultDispatch();
                        } else {
                            $dispatch_data = m("order")->getOneDispatch($g["dispatchid"]);
                        }
                        if (empty($dispatch_data)) {
                            $dispatch_data = m("order")->getNewDispatch();
                        }
                        if (!empty($dispatch_data)) {
                            $areas = unserialize($dispatch_data["areas"]);
                            if ($dispatch_data["calculatetype"] == 1) {
                                $param = $g["total"];
                            } else {
                                $param = $g["weight"] * $g["total"];
                            }
                            $dkey = $dispatch_data["id"];
                            if (array_key_exists($dkey, $dispatch_array)) {
                                $dispatch_array[$dkey]["param"] += $param;
                            } else {
                                $dispatch_array[$dkey]["data"]  = $dispatch_data;
                                $dispatch_array[$dkey]["param"] = $param;
                            }
                        }
                    }
                }
            }
            if (!empty($dispatch_array)) {
                foreach ($dispatch_array as $k => $v) {
                    $dispatch_data = $dispatch_array[$k]["data"];
                    $param         = $dispatch_array[$k]["param"];
                    $areas         = unserialize($dispatch_data["areas"]);
                    if (!empty($address)) {
                        $dispatch_price += m("order")->getCityDispatchPrice($areas, $address["city"], $param, $dispatch_data);
                    } else if (!empty($member["city"])) {
                        $dispatch_price += m("order")->getCityDispatchPrice($areas, $member["city"], $param, $dispatch_data);
                    } else {
                        $dispatch_price += m("order")->getDispatchPrice($param, $dispatch_data);
                    }
                }
            }
        }
        if ($saleset) {
            if (!empty($saleset["enoughfree"])) {
                if (floatval($saleset["enoughorder"]) <= 0) {
                    $dispatch_price = 0;
                } else {
                    if ($totalprice >= floatval($saleset["enoughorder"])) {
                        if (empty($saleset["enoughareas"])) {
                            $dispatch_price = 0;
                        } else {
                            $areas = explode(",", $saleset["enoughareas"]);
                            if (!empty($address)) {
                                if (!in_array($address["city"], $areas)) {
                                    $dispatch_price = 0;
                                }
                            } else if (!empty($member["city"])) {
                                if (!in_array($member["city"], $areas)) {
                                    $dispatch_price = 0;
                                }
                            } else if (empty($member["city"])) {
                                $dispatch_price = 0;
                            }
                        }
                    }
                }
            }
        }
        $couponprice = 0;
        $couponid    = intval($_GPC["couponid"]);
        if ($plugc) {
            $coupon = $plugc->getCouponByDataID($couponid);
            if (!empty($coupon)) {
                if ($totalprice >= $coupon["enough"] && empty($coupon["used"])) {
                    if ($coupon["backtype"] == 0) {
                        if ($coupon["deduct"] > 0) {
                            $couponprice = $coupon["deduct"];
                        }
                    } else if ($coupon["backtype"] == 1) {
                        if ($coupon["discount"] > 0) {
                            $couponprice = $totalprice * (1 - $coupon["discount"] / 10);
                        }
                    }
                    if ($couponprice > 0) {
                        $totalprice -= $couponprice;
                    }
                }
            }
        }
        $totalprice -= $deductenough;
        $totalprice += $dispatch_price;
        if ($saleset && empty($saleset["dispatchnodeduct"])) {
            $deductprice2 += $dispatch_price;
        }
        $deductcredit  = 0;
        $deductmoney   = 0;
        $deductcredit2 = 0;
        if ($sale_plugin) {
            if (!empty($_GPC['deduct'])) {
                $credit  = m('member')->getCredit($openid, 'credit1');
                $saleset = $sale_plugin->getSet();
                if (!empty($saleset['creditdeduct'])) {
                    $pcredit = intval($saleset['credit']);
                    $pmoney  = round(floatval($saleset['money']), 2);
                    if ($pcredit > 0 && $pmoney > 0) {
                        if ($credit % $pcredit == 0) {
                            $deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
                        } else {
                            $deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
                        }
                    }
                    if ($deductmoney > $deductprice) {
                        $deductmoney = $deductprice;
                    }
                    if ($deductmoney > $totalprice) {
                        $deductmoney = $totalprice;
                    }
                    $deductcredit = round($deductmoney / $pmoney * $pcredit, 2);
                }
            }
            $totalprice -= $deductmoney;
            if (!empty($_GPC['deduct2'])) {
                $deductcredit2 = m('member')->getCredit($openid, 'credit2');
                if ($deductcredit2 > $totalprice) {
                    $deductcredit2 = $totalprice;
                }
                if ($deductcredit2 > $deductprice2) {
                    $deductcredit2 = $deductprice2;
                }
            }
            $totalprice -= $deductcredit2;
        }
        $ordersn    = m('common')->createNO('order', 'ordersn', 'SH');
        $verifycode = "";
        if ($isverify) {
            $verifycode = random(8, true);
            while (1) {
                $count = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where verifycode=:verifycode and uniacid=:uniacid limit 1', array(
                    ':verifycode' => $verifycode,
                    ':uniacid' => $_W['uniacid']
                ));
                if ($count <= 0) {
                    break;
                }
                $verifycode = random(8, true);
            }
        }
        $carrier  = $_GPC['carrier'];
        $carriers = is_array($carrier) ? iserializer($carrier) : iserializer(array());
        if ($totalprice <= 0) {
            $totalprice = 0;
        }
        $order    = array(
            'uniacid' => $uniacid,
            'openid' => $openid,
            'ordersn' => $ordersn,
            'price' => $totalprice,
            'cash' => $cash,
            'discountprice' => $discountprice,
            'deductprice' => $deductmoney,
            'deductcredit' => $deductcredit,
            'deductcredit2' => $deductcredit2,
            'deductenough' => $deductenough,
            'status' => 0,
            'paytype' => 0,
            'transid' => '',
            'remark' => $_GPC['remark'],
            'addressid' => empty($dispatchtype) ? $addressid : 0,
            'goodsprice' => $goodsprice,
            'dispatchprice' => $dispatch_price,
            'dispatchtype' => $dispatchtype,
            'dispatchid' => $dispatchid,
            "storeid" => $carrierid,
            'carrier' => $carriers,
            'createtime' => time(),
            'isverify' => $isverify ? 1 : 0,
            'verifycode' => $verifycode,
            'virtual' => $virtualid,
            'isvirtual' => $isvirtual ? 1 : 0,
            'oldprice' => $totalprice,
            'olddispatchprice' => $dispatch_price,
            "couponid" => $couponid,
            "couponprice" => $couponprice
        );
        if ($diyform_plugin) {
            if (is_array($_GPC["diydata"]) && !empty($order_formInfo)) {
                $diyform_data           = $diyform_plugin->getInsertData($fields, $_GPC["diydata"]);
                $idata                  = $diyform_data["data"];
                $order["diyformfields"] = iserializer($fields);
                $order["diyformdata"]   = $idata;
                $order["diyformid"]     = $order_formInfo["id"];
            }
        }
        if (!empty($address)) {
            $order['address'] = iserializer($address);
        }
        pdo_insert('sz_yi_order', $order);
        $orderid = pdo_insertid();
        if (is_array($carrier)) {
            $up = array(
                'realname' => $carrier['carrier_realname'],
                'mobile' => $carrier['carrier_mobile']
            );
            pdo_update('sz_yi_member', $up, array(
                'id' => $member['id'],
                'uniacid' => $_W['uniacid']
            ));
            if (!empty($member['uid'])) {
                load()->model('mc');
                mc_update($member['uid'], $up);
            }
        }
        if ($_GPC['fromcart'] == 1) {
            $cartids = $_GPC['cartids'];
            if (!empty($cartids)) {
                pdo_query('update ' . tablename('sz_yi_member_cart') . ' set deleted=1 where id in (' . $cartids . ') and openid=:openid and uniacid=:uniacid ', array(
                    ':uniacid' => $uniacid,
                    ':openid' => $openid
                ));
            } else {
                pdo_query('update ' . tablename('sz_yi_member_cart') . ' set deleted=1 where openid=:openid and uniacid=:uniacid ', array(
                    ':uniacid' => $uniacid,
                    ':openid' => $openid
                ));
            }
        }
        foreach ($allgoods as $goods) {
            $order_goods = array(
                'uniacid' => $uniacid,
                'orderid' => $orderid,
                'goodsid' => $goods['goodsid'],
                'price' => $goods['marketprice'] * $goods['total'],
                'total' => $goods['total'],
                'optionid' => $goods['optionid'],
                'createtime' => time(),
                'optionname' => $goods['optiontitle'],
                'goodssn' => $goods['goodssn'],
                'productsn' => $goods['productsn'],
                "realprice" => $goods["realprice"],
                "oldprice" => $goods["realprice"],
                "openid" => $openid
            );
            if ($diyform_plugin) {
                $order_goods["diyformid"]     = $goods["diyformid"];
                $order_goods["diyformdata"]   = $goods["diyformdata"];
                $order_goods["diyformfields"] = $goods["diyformfields"];
            }
            if(p('supplier')){
				$order_goods['supplier_uid'] = $goods['supplier_uid'];
			}
            pdo_insert('sz_yi_order_goods', $order_goods);
        }
        if ($deductcredit > 0) {
            $shop = m('common')->getSysset('shop');
            m('member')->setCredit($openid, 'credit1', -$deductcredit, array(
                '0',
                $shop['name'] . "购物积分抵扣 消费积分: {$deductcredit} 抵扣金额: {$deductmoney} 订单号: {$ordersn}"
            ));
        }
        if (empty($virtualid)) {
            m('order')->setStocksAndCredits($orderid, 0);
        } else {
            if (isset($allgoods[0])) {
                $vgoods = $allgoods[0];
                pdo_update('sz_yi_goods', array(
                    'sales' => $vgoods['sales'] + $vgoods['total']
                ), array(
                    'id' => $vgoods['goodsid']
                ));
            }
        }
        $plugincoupon = p("coupon");
        if ($plugincoupon) {
            $plugincoupon->useConsumeCoupon($orderid);
        }
        m('notice')->sendOrderMessage($orderid);
        $pluginc = p('commission');
        if ($pluginc) {
            $pluginc->checkOrderConfirm($orderid);
        }
        show_json(1, array(
            'orderid' => $orderid
        ));
    }
}
include $this->template('order/confirm');

