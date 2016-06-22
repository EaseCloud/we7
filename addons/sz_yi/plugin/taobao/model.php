<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
if (!class_exists('TaobaoModel')) {
    class TaobaoModel extends PluginModel
    {
        function get_item_taobao($itemid = '', $taobaourl = '', $pcate = 0, $ccate = 0, $tcate = 0)
        {
            global $_W;
            $g = pdo_fetch("select * from " . tablename('sz_yi_goods') . " where uniacid=:uniacid and taobaoid=:taobaoid limit 1", array(
                ':uniacid' => $_W['uniacid'],
                ":taobaoid" => $itemid
            ));
            if ($g) {
            }
            $url = $this->get_info_url($itemid);
            load()->func('communication');
            $response = ihttp_get($url);
            if (!isset($response['content'])) {
                return array(
                    "result" => '0',
                    "error" => '未从淘宝获取到商品信息!'
                );
            }
            $content = $response['content'];
            if (strexists($response['content'], "ERRCODE_QUERY_DETAIL_FAIL")) {
                return array(
                    "result" => '0',
                    "error" => '宝贝不存在!'
                );
            }
            $arr            = json_decode($content, true);
            $data           = $arr['data'];
            $itemInfoModel  = $data['itemInfoModel'];
            $item           = array();
            $item['id']     = $g['id'];
            $item['pcate']  = $pcate;
            $item['ccate']  = $ccate;
            $item['tcate']  = $tcate;
            $item['itemId'] = $itemInfoModel['itemId'];
            $item['title']  = $itemInfoModel['title'];
            $item['pics']   = $itemInfoModel['picsPath'];
            $params         = array();
            if (isset($data['props'])) {
                $props = $data['props'];
                foreach ($props as $pp) {
                    $params[] = array(
                        "title" => $pp['name'],
                        "value" => $pp['value']
                    );
                }
            }
            $item['params'] = $params;
            $specs          = array();
            $options        = array();
            if (isset($data['skuModel'])) {
                $skuModel = $data['skuModel'];
                if (isset($skuModel['skuProps'])) {
                    $skuProps = $skuModel['skuProps'];
                    foreach ($skuProps as $prop) {
                        $spec_items = array();
                        foreach ($prop['values'] as $spec_item) {
                            $spec_items[] = array(
                                'valueId' => $spec_item['valueId'],
                                'title' => $spec_item['name'],
                                "thumb" => !empty($spec_item['imgUrl']) ? $spec_item['imgUrl'] : ''
                            );
                        }
                        $spec    = array(
                            "propId" => $prop['propId'],
                            "title" => $prop['propName'],
                            "items" => $spec_items
                        );
                        $specs[] = $spec;
                    }
                }
                if (isset($skuModel['ppathIdmap'])) {
                    $ppathIdmap = $skuModel['ppathIdmap'];
                    foreach ($ppathIdmap as $key => $skuId) {
                        $option_specs = array();
                        $m            = explode(";", $key);
                        foreach ($m as $v) {
                            $mm             = explode(":", $v);
                            $option_specs[] = array(
                                "propId" => $mm[0],
                                "valueId" => $mm[1]
                            );
                        }
                        $options[] = array(
                            "option_specs" => $option_specs,
                            "skuId" => $skuId,
                            "stock" => 0,
                            "marketprice" => 0,
                            "specs" => ""
                        );
                    }
                }
            }
            $item['specs']  = $specs;
            $stack          = $data['apiStack'][0]['value'];
            $value          = json_decode($stack, true);
            $item1          = array();
            $data1          = $value['data'];
            $itemInfoModel1 = $data1['itemInfoModel'];
            $item['total']  = $itemInfoModel1['quantity'];
            $item['sales']  = $itemInfoModel1['totalSoldQuantity'];
            if (isset($data1['skuModel'])) {
                $skuModel1 = $data1['skuModel'];
                if (isset($skuModel1['skus'])) {
                    $skus = $skuModel1['skus'];
                    foreach ($skus as $key => $val) {
                        $sku_id = $key;
                        foreach ($options as &$o) {
                            if ($o['skuId'] == $sku_id) {
                                $o['stock'] = $val['quantity'];
                                foreach ($val['priceUnits'] as $p) {
                                    $o['marketprice'] = $p['price'];
                                }
                                $titles = array();
                                foreach ($o['option_specs'] as $osp) {
                                    foreach ($specs as $sp) {
                                        if ($sp['propId'] == $osp['propId']) {
                                            foreach ($sp['items'] as $spitem) {
                                                if ($spitem['valueId'] == $osp['valueId']) {
                                                    $titles[] = $spitem['title'];
                                                }
                                            }
                                        }
                                    }
                                }
                                $o['title'] = $titles;
                            }
                        }
                        unset($o);
                    }
                }
            } else {
                $mprice = 0;
                foreach ($itemInfoModel1['priceUnits'] as $p) {
                    $mprice = $p['price'];
                }
                $item['marketprice'] = $mprice;
            }
            $item['options'] = $options;
            $item['content'] = array();
            $url             = $this->get_detail_url($itemid);
            load()->func('communication');
            $response        = ihttp_get($url);
            $item['content'] = $response;
            return $this->save_goods($item, $taobaourl);
        }
        function save_goods($item = array(), $taobaourl = '')
        {
            global $_W;
            $qiniu     = p('qiniu');
            $config    = $qiniu ? $qiniu->getConfig() : false;
            $data      = array(
                "uniacid" => $_W['uniacid'],
                "taobaoid" => $item['itemId'],
                "taobaourl" => $taobaourl,
                "title" => $item['title'],
                "total" => $item['total'],
                "marketprice" => $item['marketprice'],
                "pcate" => $item['pcate'],
                "ccate" => $item['ccate'],
                "tcate" => $item['tcate'],
                "sales" => $item['sales'],
                "createtime" => time(),
                "updatetime" => time(),
                'hasoption' => count($item['options']) > 0 ? 1 : 0,
                'status' => 0,
                'deleted' => 0,
                'buylevels' => '',
                'showlevels' => '',
                'buygroups' => '',
                'showgroups' => '',
                'noticeopenid' => '',
                'storeids' => ''
            );
            $thumb_url = array();
            $pics      = $item['pics'];
            $piclen    = count($pics);
            if ($piclen > 0) {
                $data['thumb'] = $this->save_image($pics[0], $config);
                if ($piclen > 1) {
                    for ($i = 1; $i < $piclen; $i++) {
                        $img         = $this->save_image($pics[$i], $config);
                        $thumb_url[] = $img;
                    }
                }
            }
            $data['thumb_url'] = serialize($thumb_url);
            $goods             = pdo_fetch("select * from " . tablename('sz_yi_goods') . " where  taobaoid=:taobaoid and uniacid=:uniacid", array(
                ":taobaoid" => $item['itemId'],
                ":uniacid" => $_W['uniacid']
            ));
            if (empty($goods)) {
                pdo_insert("sz_yi_goods", $data);
                $goodsid = pdo_insertid();
            } else {
                $goodsid = $goods['id'];
                unset($data['createtime']);
                pdo_update('sz_yi_goods', $data, array(
                    "id" => $goodsid
                ));
            }
            $goods_params = pdo_fetchall("select * from " . tablename('sz_yi_goods_param') . " where goodsid=:goodsid ", array(
                ":goodsid" => $goodsid
            ));
            $params       = $item['params'];
            $paramids     = array();
            $displayorder = 0;
            foreach ($params as $p) {
                $oldp    = pdo_fetch("select * from " . tablename('sz_yi_goods_param') . " where goodsid=:goodsid and title=:title limit 1", array(
                    ":goodsid" => $goodsid,
                    ":title" => $p['title']
                ));
                $paramid = 0;
                $d       = array(
                    "uniacid" => $_W['uniacid'],
                    "goodsid" => $goodsid,
                    "title" => $p['title'],
                    "value" => $p['value'],
                    "displayorder" => $displayorder
                );
                if (empty($oldp)) {
                    pdo_insert("sz_yi_goods_param", $d);
                    $paramid = pdo_insertid();
                } else {
                    pdo_update('sz_yi_goods_param', $d, array(
                        "id" => $oldp['id']
                    ));
                    $paramid = $oldp['id'];
                }
                $paramids[] = $paramid;
                $displayorder++;
            }
            if (count($paramids) > 0) {
                pdo_query("delete from " . tablename('sz_yi_goods_param') . " where goodsid=:goodsid and id not in (" . implode(",", $paramids) . ")", array(
                    ":goodsid" => $goodsid
                ));
            } else {
                pdo_query('delete from ' . tablename('sz_yi_goods_param') . ' where goodsid=:goodsid ', array(
                    ':goodsid' => $goodsid
                ));
            }
            $specs        = $item['specs'];
            $specids      = array();
            $displayorder = 0;
            $newspecs     = array();
            foreach ($specs as $spec) {
                $oldspec = pdo_fetch("select * from " . tablename('sz_yi_goods_spec') . " where goodsid=:goodsid and propId=:propId limit 1", array(
                    ":goodsid" => $goodsid,
                    ":propId" => $spec['propId']
                ));
                $specid  = 0;
                $d_spec  = array(
                    "uniacid" => $_W['uniacid'],
                    "goodsid" => $goodsid,
                    "title" => $spec['title'],
                    "displayorder" => $displayorder,
                    "propId" => $spec['propId']
                );
                if (empty($oldspec)) {
                    pdo_insert("sz_yi_goods_spec", $d_spec);
                    $specid = pdo_insertid();
                } else {
                    pdo_update('sz_yi_goods_spec', $d_spec, array(
                        "id" => $oldspec['id']
                    ));
                    $specid = $oldspec['id'];
                }
                $d_spec['id'] = $specid;
                $specids[]    = $specid;
                $displayorder++;
                $spec_items        = $spec['items'];
                $spec_itemids      = array();
                $displayorder_item = 0;
                $newspecitems      = array();
                foreach ($spec_items as $spec_item) {
                    $d            = array(
                        "uniacid" => $_W['uniacid'],
                        "specid" => $specid,
                        "title" => $spec_item['title'],
                        "thumb" => $this->save_image($spec_item['thumb'], $config),
                        "valueId" => $spec_item['valueId'],
                        "show" => 1,
                        "displayorder" => $displayorder_item
                    );
                    $oldspecitem  = pdo_fetch("select * from " . tablename('sz_yi_goods_spec_item') . " where specid=:specid and valueId=:valueId limit 1", array(
                        ":specid" => $specid,
                        ":valueId" => $spec_item['valueId']
                    ));
                    $spec_item_id = 0;
                    if (empty($oldspecitem)) {
                        pdo_insert("sz_yi_goods_spec_item", $d);
                        $spec_item_id = pdo_insertid();
                    } else {
                        pdo_update('sz_yi_goods_spec_item', $d, array(
                            "id" => $oldspecitem['id']
                        ));
                        $spec_item_id = $oldspecitem['id'];
                    }
                    $displayorder_item++;
                    $spec_itemids[] = $spec_item_id;
                    $d['id']        = $spec_item_id;
                    $newspecitems[] = $d;
                }
                $d_spec['items'] = $newspecitems;
                $newspecs[]      = $d_spec;
                if (count($spec_itemids) > 0) {
                    pdo_query("delete from " . tablename('sz_yi_goods_spec_item') . " where specid=:specid and id not in (" . implode(",", $spec_itemids) . ")", array(
                        ":specid" => $specid
                    ));
                } else {
                    pdo_query('delete from ' . tablename('sz_yi_goods_spec_item') . ' where specid=:specid ', array(
                        ':specid' => $specid
                    ));
                }
                pdo_update('sz_yi_goods_spec', array(
                    'content' => serialize($spec_itemids)
                ), array(
                    "id" => $oldspec['id']
                ));
            }
            if (count($specids) > 0) {
                pdo_query("delete from " . tablename('sz_yi_goods_spec') . " where goodsid=:goodsid and id not in (" . implode(",", $specids) . ")", array(
                    ":goodsid" => $goodsid
                ));
            } else {
                pdo_query('delete from ' . tablename('sz_yi_goods_spec') . ' where goodsid=:goodsid ', array(
                    ':goodsid' => $goodsid
                ));
            }
            $minprice = 0;
            $options  = $item['options'];
            if (count($options) > 0) {
                $minprice = $options[0]['marketprice'];
            }
            $optionids    = array();
            $displayorder = 0;
            foreach ($options as $o) {
                $option_specs = $o['option_specs'];
                $ids          = array();
                $valueIds     = array();
                foreach ($option_specs as $os) {
                    foreach ($newspecs as $nsp) {
                        foreach ($nsp['items'] as $nspitem) {
                            if ($nspitem['valueId'] == $os['valueId']) {
                                $ids[]      = $nspitem['id'];
                                $valueIds[] = $nspitem['valueId'];
                            }
                        }
                    }
                }
                $ids      = implode("_", $ids);
                $valueIds = implode("_", $valueIds);
                $do       = array(
                    'uniacid' => $_W['uniacid'],
                    "displayorder" => $displayorder,
                    "goodsid" => $goodsid,
                    "title" => implode('+', $o['title']),
                    "specs" => $ids,
                    "stock" => $o['stock'],
                    "marketprice" => $o['marketprice'],
                    "skuId" => $o['skuId']
                );
                if ($minprice > $o['marketprice']) {
                    $minprice = $o['marketprice'];
                }
                $oldoption = pdo_fetch("select * from " . tablename('sz_yi_goods_option') . " where goodsid=:goodsid and skuId=:skuId limit 1", array(
                    ":goodsid" => $goodsid,
                    ":skuId" => $o['skuId']
                ));
                $option_id = 0;
                if (empty($oldoption)) {
                    pdo_insert("sz_yi_goods_option", $do);
                    $option_id = pdo_insertid();
                } else {
                    pdo_update('sz_yi_goods_option', $do, array(
                        "id" => $oldoption['id']
                    ));
                    $option_id = $oldoption['id'];
                }
                $displayorder++;
                $optionids[] = $option_id;
            }
            if (count($optionids) > 0) {
                pdo_query("delete from " . tablename('sz_yi_goods_option') . " where goodsid=:goodsid and id not in (" . implode(",", $optionids) . ")", array(
                    ":goodsid" => $goodsid
                ));
            } else {
                pdo_query('delete from ' . tablename('sz_yi_goods_option') . ' where goodsid=:goodsid ', array(
                    ':goodsid' => $goodsid
                ));
            }
            $response = $item['content'];
            $content  = $response['content'];
            preg_match_all("/<img.*?src=[\'| \"](.*?(?:[\.gif|\.jpg]?))[\'|\"].*?[\/]?>/", $content, $imgs);
            if (isset($imgs[1])) {
                foreach ($imgs[1] as $img) {
                    $taobaoimg = $img;
                    if (substr($taobaoimg, 0, 2) == "//") {
                        $img = "http://" . substr($img, 2);
                    }
                    $im = array(
                        "taobao" => $taobaoimg,
                        "system" => $this->save_image($img, $config)
                    );
                    if (!strexists($im['system'], 'http://') && !strexists($im['system'], 'https://')) {
                        $im['system'] = $_W['attachurl'] . $im['system'];
                    }
                    $images[] = $im;
                }
            }
            preg_match("/tfsContent : '(.*)'/", $content, $html);
            $html = iconv("GBK", "UTF-8", $html[1]);
            if (isset($images)) {
                foreach ($images as $img) {
                    $html = str_replace($img['taobao'], $img['system'], $html);
                }
            }
            $hasoption = 0;
            if (count($options) > 0) {
                $hasoption = 1;
            }
            $d = array(
                "content" => $html,
                "hasoption" => $hasoption
            );
            if ($minprice > 0) {
                $d["marketprice"] = $minprice;
            }
            pdo_update('sz_yi_goods', $d, array(
                "id" => $goodsid
            ));
            return array(
                'result' => '1',
                'goodsid' => $goodsid
            );
        }
        function save_image($url = '', $config)
        {
            global $_W;
            if ($config) {
                return p('qiniu')->save($url, $config);
            }
            return $this->saveToLocal($url);
        }
        function get_info_url($itemid)
        {
            return "http://hws.m.taobao.com/cache/wdetail/5.0/?id=" . $itemid;
        }
        function get_detail_url($itemid)
        {
            return 'http://hws.m.taobao.com/cache/wdesc/5.0/?id=' . $itemid;
        }
        function check_remote_file_exists($url)
        {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            $result = curl_exec($curl);
            $found  = false;
            if ($result !== false) {
                $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($statusCode == 200) {
                    $found = true;
                }
            }
            curl_close($curl);
            return $found;
        }
        function saveToLocal($url)
        {
            global $_W;
            if (empty($url)) {
                return '';
            }
            if (!$this->check_remote_file_exists($url)) {
                return "";
            }
            $ext = strrchr($url, ".");
            if ($ext != ".jpeg" && $ext != ".gif" && $ext != ".jpg" && $ext != ".png") {
                return '';
            }
            $apath = $_W['config']['upload']['attachdir'] . "/";
            $path  = "images/sz_yi/" . $_W['uniacid'] . "/" . date('Y') . "/" . date('m') . "/";
            load()->func('file');
            mkdirs(IA_ROOT . "/" . $apath . $path);
            do {
                $filename = random(30) . $ext;
            } while (file_exists(IA_ROOT . "/" . $apath . $path . '/' . $filename));
            $path .= $filename;
            $opts = array(
                'http' => array(
                    'method' => "GET",
                    'timeout' => 7200
                )
            );
            $data = file_get_contents($url, false, stream_context_create($opts));
            $fp2  = @fopen(IA_ROOT . "/" . $apath . $path, "w");
            fwrite($fp2, $data);
            fclose($fp2);
            load()->func('file');
            $remote = file_remote_upload($path);
            if ($remote == true) {
                file_delete($path);
            }
            return $path;
        }
        function get_pageno_url($url = '', $pageNo = 1)
        {
            $url .= "/search.htm?pageNo=" . $pageNo;
            return $url;
        }
        function get_total_page($url = '', $taobao = false)
        {
            if (empty($url)) {
                return array(
                    "totalpage" => 0
                );
            }
            $content = $this->get_page_content($url);
            die($content);
            $str = "";
            if ($taobao) {
                $str = "/<span class=\"page-info\">(.*)<\/span>/";
            } else {
                $str = "/<b class=\"ui-page-s-len\">(.*)<\/b>/";
            }
            preg_match($str, $content, $p);
            if (is_array($p)) {
                $pages = explode("/", $p[1]);
                return array(
                    'totalpage' => $pages[1]
                );
            }
            return array(
                'totalpage' => 0
            );
        }
        function httpGet($url)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 500);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_URL, $url);
            $res = curl_exec($curl);
            curl_close($curl);
            return $res;
        }
        function get_page_content($url = '', $pageNo = 1)
        {
            if (empty($url)) {
                return array(
                    "totalpage" => 0
                );
            }
            $url = $this->get_pageno_url($url, $pageNo);
            load()->func('communication');
            $response = ihttp_get($url);
            if (!isset($response['content'])) {
                return array(
                    "result" => 0
                );
            }
            return $response['content'];
        }
        function getRealURL($url)
        {
            if (function_exists("stream_context_set_default")) {
                stream_context_set_default(array(
                    'http' => array(
                        'method' => 'HEAD'
                    )
                ));
            }
            $header = get_headers($url, 1);
            if (strpos($header[0], '301') || strpos($header[0], '302')) {
                if (is_array($header['Location'])) {
                    return $header['Location'][count($header['Location']) - 1];
                } else {
                    return $header['Location'];
                }
            } else {
                return $url;
            }
        }
        function get_pag_items($pageContent = '')
        {
            $str = '/data-id="(.*)"/U';
            preg_match_all($str, $pageContent, $items);
            if (isset($items[1])) {
                return $items[1];
            }
            return array();
        }
        function perms()
        {
            return array(
                'taobao' => array(
                    'text' => $this->getName(),
                    'isplugin' => true,
                    'fetch' => '抓取宝贝-log'
                )
            );
        }
    }
}
