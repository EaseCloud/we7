<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
if ($_W['isajax']) {
    if ($operation == 'display') {
        $condition  = ' and f.uniacid= :uniacid and f.openid=:openid and f.deleted=0';
        $params     = array(
            ':uniacid' => $uniacid,
            ':openid' => $openid
        );
        $list       = array();
        $total      = 0;
        $totalprice = 0;
        $sql        = 'SELECT f.id,f.total,f.goodsid,g.total as stock, o.stock as optionstock, g.maxbuy,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,g.productprice,o.title as optiontitle,f.optionid,o.specs FROM ' . tablename('sz_yi_member_cart') . ' f ' . ' left join ' . tablename('sz_yi_goods') . ' g on f.goodsid = g.id ' . ' left join ' . tablename('sz_yi_goods_option') . ' o on f.optionid = o.id ' . ' where 1 ' . $condition . ' ORDER BY `id` DESC ';
        $list       = pdo_fetchall($sql, $params);
        foreach ($list as &$r) {
            if (!empty($r['optionid'])) {
                $r['stock'] = $r['optionstock'];
            }
            $totalprice += $r['marketprice'] * $r['total'];
            $total += $r['total'];
        }
        unset($r);
        $list       = set_medias($list, 'thumb');
        $totalprice = number_format($totalprice, 2);
        show_json(1, array(
            'total' => $total,
            'list' => $list,
            'totalprice' => $totalprice
        ));
    } else if ($operation == 'add' && $_W['ispost']) {
        $id    = intval($_GPC['id']);
        $total = intval($_GPC['total']);
        empty($total) && $total = 1;
        $optionid = intval($_GPC['optionid']);
        $goods    = pdo_fetch('select id,marketprice from ' . tablename('sz_yi_goods') . ' where uniacid=:uniacid and id=:id limit 1', array(
            ':uniacid' => $uniacid,
            ':id' => $id
        ));
        if (empty($goods)) {
            show_json(0, '商品未找到');
        }
        $diyform_plugin = p('diyform');
        $datafields     = "id,total";
        if ($diyform_plugin) {
            $datafields .= ",diyformdataid";
        }
        $data          = pdo_fetch("select {$datafields} from " . tablename('sz_yi_member_cart') . ' where openid=:openid and goodsid=:id and  optionid=:optionid and deleted=0 and  uniacid=:uniacid   limit 1', array(
            ':uniacid' => $uniacid,
            ':openid' => $openid,
            ':optionid' => $optionid,
            ':id' => $id
        ));
        $diyformdataid = 0;
        $diyformfields = iserializer(array());
        $diyformdata   = iserializer(array());
        if ($diyform_plugin) {
            $diyformdata = $_GPC['diyformdata'];
            if (!empty($diyformdata) && is_array($diyformdata)) {
                $diyformid = intval($diyformdata['diyformid']);
                $diydata   = $diyformdata['diydata'];
                if (!empty($diyformid) && is_array($diydata)) {
                    $formInfo = $diyform_plugin->getDiyformInfo($diyformid);
                    if (!empty($formInfo)) {
                        $diyformfields = $formInfo['fields'];
                        $insert_data   = $diyform_plugin->getInsertData($diyformfields, $diydata);
                        $idata         = $insert_data['data'];
                        $diyformdata   = $idata;
                        $diyformfields = iserializer($diyformfields);
                    }
                }
            }
        }
        $cartcount = pdo_fetchcolumn('select sum(total) from ' . tablename('sz_yi_member_cart') . ' where openid=:openid and deleted=0 and uniacid=:uniacid limit 1', array(
            ':uniacid' => $uniacid,
            ':openid' => $openid
        ));
        if (empty($data)) {
            $data = array(
                'uniacid' => $uniacid,
                'openid' => $openid,
                'goodsid' => $id,
                'optionid' => $optionid,
                'marketprice' => $goods['marketprice'],
                'total' => $total,
                'diyformid' => $diyformid,
                'diyformdata' => $diyformdata,
                'diyformfields' => $diyformfields,
                'createtime' => time()
            );
            pdo_insert('sz_yi_member_cart', $data);
            $cartcount += $total;
            show_json(1, array(
                'message' => '添加成功',
                'cartcount' => $cartcount
            ));
        } else {
            $data['diyformdataid'] = $diyformdataid;
            $data['diyformdata']   = $diyformdata;
            $data['diyformfields'] = $diyformfields;
            pdo_update('sz_yi_member_cart', $data, array(
                'id' => $data['id']
            ));
        }
        show_json(1, array(
            'message' => '已在购物车',
            'cartcount' => $cartcount
        ));
    } else if ($operation == 'selectoption') {
        $id         = intval($_GPC['id']);
        $goodsid    = intval($_GPC['goodsid']);
        $cartdata   = pdo_fetch("SELECT id,optionid,total FROM " . tablename('sz_yi_member_cart') . " WHERE id = :id and uniacid=:uniacid and openid=:openid limit 1", array(
            ':id' => $id,
            ':uniacid' => $uniacid,
            ':openid' => $openid
        ));
        $cartoption = pdo_fetch("select id,title,thumb,marketprice,productprice,costprice, stock,weight,specs from " . tablename('sz_yi_goods_option') . " " . " where uniacid=:uniacid and goodsid=:goodsid and id=:id limit 1 ", array(
            ':id' => $cartdata['optionid'],
            ':uniacid' => $uniacid,
            ':goodsid' => $goodsid
        ));
        $cartoption = set_medias($cartoption, 'thumb');
        $cartspecs  = explode('_', $cartoption['specs']);
        $goods      = pdo_fetch("SELECT id,title,thumb,total,marketprice FROM " . tablename('sz_yi_goods') . " WHERE id = :id", array(
            ':id' => $goodsid
        ));
        $goods      = set_medias($goods, 'thumb');
        $allspecs   = pdo_fetchall("select * from " . tablename('sz_yi_goods_spec') . " where goodsid=:id order by displayorder asc", array(
            ':id' => $goodsid
        ));
        foreach ($allspecs as &$s) {
            $s['items'] = pdo_fetchall("select * from " . tablename('sz_yi_goods_spec_item') . " where  `show`=1 and specid=:specid order by displayorder asc", array(
                ":specid" => $s['id']
            ));
        }
        unset($s);
        $options = pdo_fetchall("select id,title,thumb,marketprice,productprice,costprice, stock,weight,specs from " . tablename('sz_yi_goods_option') . " where goodsid=:id order by id asc", array(
            ':id' => $goodsid
        ));
        $options = set_medias($options, 'thumb');
        $specs   = array();
        if (count($options) > 0) {
            $specitemids = explode("_", $options[0]['specs']);
            foreach ($specitemids as $itemid) {
                foreach ($allspecs as $ss) {
                    $items = $ss['items'];
                    foreach ($items as $it) {
                        if ($it['id'] == $itemid) {
                            $specs[] = $ss;
                            break;
                        }
                    }
                }
            }
        }
        show_json(1, array(
            'cartdata' => $cartdata,
            'cartoption' => $cartoption,
            'cartspecs' => $cartspecs,
            'goods' => $goods,
            'options' => $options,
            'specs' => $specs
        ));
    } else if ($operation == 'setoption' && $_W['ispost']) {
        $id       = intval($_GPC['id']);
        $goodsid  = intval($_GPC['goodsid']);
        $optionid = intval($_GPC['optionid']);
        $option   = pdo_fetch("select id,title,thumb,marketprice,productprice,costprice, stock,weight,specs from " . tablename('sz_yi_goods_option') . " " . " where uniacid=:uniacid and goodsid=:goodsid and id=:id limit 1 ", array(
            ':id' => $optionid,
            ':uniacid' => $uniacid,
            ':goodsid' => $goodsid
        ));
        $option   = set_medias($option, 'thumb');
        if (empty($option)) {
            show_json(0, '规格未找到');
        }
        pdo_update('sz_yi_member_cart', array(
            'optionid' => $optionid
        ), array(
            'id' => $id,
            'uniacid' => $uniacid,
            'goodsid' => $goodsid
        ));
        show_json(1, array(
            'optionid' => $optionid,
            'optiontitle' => $option['title']
        ));
    } else if ($operation == 'updatenum' && $_W['ispost']) {
        $id      = intval($_GPC['id']);
        $goodsid = intval($_GPC['goodsid']);
        $total   = intval($_GPC['total']);
        empty($total) && $total = 1;
        $data = pdo_fetchall("select id,total from " . tablename('sz_yi_member_cart') . " " . " where id=:id and uniacid=:uniacid and goodsid=:goodsid  and openid=:openid limit 1 ", array(
            ':id' => $id,
            ':uniacid' => $uniacid,
            ':goodsid' => $goodsid,
            ':openid' => $openid
        ));
        if (empty($data)) {
            show_json(0, '购物车数据未找到');
        }
        pdo_update('sz_yi_member_cart', array(
            'total' => $total
        ), array(
            'id' => $id,
            'uniacid' => $uniacid,
            'goodsid' => $goodsid
        ));
        show_json(1);
    } else if ($operation == 'tofavorite' && $_W['ispost']) {
        $ids = $_GPC['ids'];
        if (empty($ids) || !is_array($ids)) {
            show_json(0, '参数错误');
        }
        foreach ($ids as $id) {
            $goodsid = pdo_fetchcolumn('select goodsid from ' . tablename('sz_yi_member_cart') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1 ', array(
                ':id' => $id,
                ':uniacid' => $uniacid,
                ':openid' => $openid
            ));
            if (!empty($goodsid)) {
                $fav = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member_favorite') . ' where goodsid=:goodsid and uniacid=:uniacid and openid=:openid and deleted=0 limit 1 ', array(
                    ':goodsid' => $goodsid,
                    ':uniacid' => $uniacid,
                    ':openid' => $openid
                ));
                if ($fav <= 0) {
                    $fav = array(
                        'uniacid' => $uniacid,
                        'goodsid' => $goodsid,
                        'openid' => $openid,
                        'deleted' => 0,
                        'createtime' => time()
                    );
                    pdo_insert('sz_yi_member_favorite', $fav);
                }
            }
        }
        $sql = "update " . tablename('sz_yi_member_cart') . ' set deleted=1 where uniacid=:uniacid and openid=:openid and id in (' . implode(',', $ids) . ')';
        pdo_query($sql, array(
            ':uniacid' => $uniacid,
            ':openid' => $openid
        ));
        show_json(1);
    } else if ($operation == 'remove' && $_W['ispost']) {
        $ids = $_GPC['ids'];
        if (empty($ids) || !is_array($ids)) {
            show_json(0, '参数错误');
        }
        $sql = "update " . tablename('sz_yi_member_cart') . ' set deleted=1 where uniacid=:uniacid and openid=:openid and id in (' . implode(',', $ids) . ')';
        pdo_query($sql, array(
            ':uniacid' => $uniacid,
            ':openid' => $openid
        ));
        show_json(1);
    }
}
include $this->template('shop/cart');

