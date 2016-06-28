<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
if (!class_exists('TaobaoModel')) {
    
    class TaobaoModel extends PluginModel
    {
        function get_item_taobao($_var_0 = '', $_var_1 = '', $_var_2 = 0, $_var_3 = 0, $_var_4 = 0)
        {
            global $_W;
            $_var_5 = pdo_fetch('select * from ' . tablename('sz_yi_goods') . ' where uniacid=:uniacid and taobaoid=:taobaoid limit 1', array(':uniacid' => $_W['uniacid'], ':taobaoid' => $_var_0));
            if ($_var_5) {
            }
            $_var_6 = $this->get_info_url($_var_0);
            load()->func('communication');
            $_var_7 = ihttp_get($_var_6);
            if (!isset($_var_7['content'])) {
                return array('result' => '0', 'error' => '未从淘宝获取到商品信息!');
            }
            $_var_8 = $_var_7['content'];
            if (strexists($_var_7['content'], 'ERRCODE_QUERY_DETAIL_FAIL')) {
                return array('result' => '0', 'error' => '宝贝不存在!');
            }
            $_var_9 = json_decode($_var_8, true);
            $_var_10 = $_var_9['data'];
            $_var_11 = $_var_10['itemInfoModel'];
            $_var_12 = array();
            $_var_12['id'] = $_var_5['id'];
            $_var_12['pcate'] = $_var_2;
            $_var_12['ccate'] = $_var_3;
            $_var_12['tcate'] = $_var_4;
            $_var_12['itemId'] = $_var_11['itemId'];
            $_var_12['title'] = $_var_11['title'];
            $_var_12['pics'] = $_var_11['picsPath'];
            $_var_13 = array();
            if (isset($_var_10['props'])) {
                $_var_14 = $_var_10['props'];
                foreach ($_var_14 as $_var_15) {
                    $_var_13[] = array('title' => $_var_15['name'], 'value' => $_var_15['value']);
                }
            }
            $_var_12['params'] = $_var_13;
            $_var_16 = array();
            $_var_17 = array();
            if (isset($_var_10['skuModel'])) {
                $_var_18 = $_var_10['skuModel'];
                if (isset($_var_18['skuProps'])) {
                    $_var_19 = $_var_18['skuProps'];
                    foreach ($_var_19 as $_var_20) {
                        $_var_21 = array();
                        foreach ($_var_20['values'] as $_var_22) {
                            $_var_21[] = array('valueId' => $_var_22['valueId'], 'title' => $_var_22['name'], 'thumb' => !empty($_var_22['imgUrl']) ? $_var_22['imgUrl'] : '');
                        }
                        $_var_23 = array('propId' => $_var_20['propId'], 'title' => $_var_20['propName'], 'items' => $_var_21);
                        $_var_16[] = $_var_23;
                    }
                }
                if (isset($_var_18['ppathIdmap'])) {
                    $_var_24 = $_var_18['ppathIdmap'];
                    foreach ($_var_24 as $_var_25 => $_var_26) {
                        $_var_27 = array();
                        $_var_28 = explode(';', $_var_25);
                        foreach ($_var_28 as $_var_29) {
                            $_var_30 = explode(':', $_var_29);
                            $_var_27[] = array('propId' => $_var_30[0], 'valueId' => $_var_30[1]);
                        }
                        $_var_17[] = array('option_specs' => $_var_27, 'skuId' => $_var_26, 'stock' => 0, 'marketprice' => 0, 'specs' => "");
                    }
                }
            }
            $_var_12['specs'] = $_var_16;
            $_var_31 = $_var_10['apiStack'][0]['value'];
            $_var_32 = json_decode($_var_31, true);
            $_var_33 = array();
            $_var_34 = $_var_32['data'];
            $_var_35 = $_var_34['itemInfoModel'];
            $_var_12['total'] = $_var_35['quantity'];
            $_var_12['sales'] = $_var_35['totalSoldQuantity'];
            if (isset($_var_34['skuModel'])) {
                $_var_36 = $_var_34['skuModel'];
                if (isset($_var_36['skus'])) {
                    $_var_37 = $_var_36['skus'];
                    foreach ($_var_37 as $_var_25 => $_var_38) {
                        $_var_39 = $_var_25;
                        foreach ($_var_17 as &$_var_40) {
                            if ($_var_40['skuId'] == $_var_39) {
                                $_var_40['stock'] = $_var_38['quantity'];
                                foreach ($_var_38['priceUnits'] as $_var_41) {
                                    $_var_40['marketprice'] = $_var_41['price'];
                                }
                                $_var_42 = array();
                                foreach ($_var_40['option_specs'] as $_var_43) {
                                    foreach ($_var_16 as $_var_44) {
                                        if ($_var_44['propId'] == $_var_43['propId']) {
                                            foreach ($_var_44['items'] as $_var_45) {
                                                if ($_var_45['valueId'] == $_var_43['valueId']) {
                                                    $_var_42[] = $_var_45['title'];
                                                }
                                            }
                                        }
                                    }
                                }
                                $_var_40['title'] = $_var_42;
                            }
                        }
                        unset($_var_40);
                    }
                }
            } else {
                $_var_46 = 0;
                foreach ($_var_35['priceUnits'] as $_var_41) {
                    $_var_46 = $_var_41['price'];
                }
                $_var_12['marketprice'] = $_var_46;
            }
            $_var_12['options'] = $_var_17;
            $_var_12['content'] = array();
            $_var_6 = $this->get_detail_url($_var_0);
            load()->func('communication');
            $_var_7 = ihttp_get($_var_6);
            $_var_12['content'] = $_var_7;
            return $this->save_goods($_var_12, $_var_1);
        }

        function save_goods($_var_12 = array(), $_var_1 = '')
        {
            global $_W;
            $_var_47 = p('qiniu');
            $_var_48 = $_var_47 ? $_var_47->getConfig() : false;
            $_var_10 = array('uniacid' => $_W['uniacid'], 'taobaoid' => $_var_12['itemId'], 'taobaourl' => $_var_1, 'title' => $_var_12['title'], 'total' => $_var_12['total'], 'marketprice' => $_var_12['marketprice'], 'pcate' => $_var_12['pcate'], 'ccate' => $_var_12['ccate'], 'tcate' => $_var_12['tcate'], 'sales' => $_var_12['sales'], 'createtime' => time(), 'updatetime' => time(), 'hasoption' => count($_var_12['options']) > 0 ? 1 : 0, 'status' => 0, 'deleted' => 0, 'buylevels' => '', 'showlevels' => '', 'buygroups' => '', 'showgroups' => '', 'noticeopenid' => '', 'storeids' => '');
            if (p('supplier')) {
                $_var_49 = pdo_fetch('select * from ' . tablename('sz_yi_perm_user') . " where uniacid={$_W['uniacid']} and uid={$_W['uid']} and roleid=(select id from " . tablename('sz_yi_perm_role') . ' where status1=1)');
                if (empty($_var_49)) {
                    $_var_10['supplier_uid'] = 0;
                } else {
                    $_var_10['supplier_uid'] = $_W['uid'];
                }
            }
            $_var_50 = array();
            $_var_51 = $_var_12['pics'];
            $_var_52 = count($_var_51);
            if ($_var_52 > 0) {
                $_var_10['thumb'] = $this->save_image($_var_51[0], $_var_48);
                if ($_var_52 > 1) {
                    for ($_var_53 = 1; $_var_53 < $_var_52; $_var_53++) {
                        $_var_54 = $this->save_image($_var_51[$_var_53], $_var_48);
                        $_var_50[] = $_var_54;
                    }
                }
            }
            $_var_10['thumb_url'] = serialize($_var_50);
            $_var_55 = pdo_fetch('select * from ' . tablename('sz_yi_goods') . ' where  taobaoid=:taobaoid and uniacid=:uniacid', array(':taobaoid' => $_var_12['itemId'], ':uniacid' => $_W['uniacid']));
            if (empty($_var_55)) {
                pdo_insert('sz_yi_goods', $_var_10);
                $_var_56 = pdo_insertid();
            } else {
                $_var_56 = $_var_55['id'];
                unset($_var_10['createtime']);
                pdo_update('sz_yi_goods', $_var_10, array('id' => $_var_56));
            }
            $_var_57 = pdo_fetchall('select * from ' . tablename('sz_yi_goods_param') . ' where goodsid=:goodsid ', array(':goodsid' => $_var_56));
            $_var_13 = $_var_12['params'];
            $_var_58 = array();
            $_var_59 = 0;
            foreach ($_var_13 as $_var_41) {
                $_var_60 = pdo_fetch('select * from ' . tablename('sz_yi_goods_param') . ' where goodsid=:goodsid and title=:title limit 1', array(':goodsid' => $_var_56, ':title' => $_var_41['title']));
                $_var_61 = 0;
                $_var_62 = array('uniacid' => $_W['uniacid'], 'goodsid' => $_var_56, 'title' => $_var_41['title'], 'value' => $_var_41['value'], 'displayorder' => $_var_59);
                if (empty($_var_60)) {
                    pdo_insert('sz_yi_goods_param', $_var_62);
                    $_var_61 = pdo_insertid();
                } else {
                    pdo_update('sz_yi_goods_param', $_var_62, array('id' => $_var_60['id']));
                    $_var_61 = $_var_60['id'];
                }
                $_var_58[] = $_var_61;
                $_var_59++;
            }
            if (count($_var_58) > 0) {
                pdo_query('delete from ' . tablename('sz_yi_goods_param') . ' where goodsid=:goodsid and id not in (' . implode(',', $_var_58) . ')', array(':goodsid' => $_var_56));
            } else {
                pdo_query('delete from ' . tablename('sz_yi_goods_param') . ' where goodsid=:goodsid ', array(':goodsid' => $_var_56));
            }
            $_var_16 = $_var_12['specs'];
            $_var_63 = array();
            $_var_59 = 0;
            $_var_64 = array();
            foreach ($_var_16 as $_var_23) {
                $_var_65 = pdo_fetch('select * from ' . tablename('sz_yi_goods_spec') . ' where goodsid=:goodsid and propId=:propId limit 1', array(':goodsid' => $_var_56, ':propId' => $_var_23['propId']));
                $_var_66 = 0;
                $_var_67 = array('uniacid' => $_W['uniacid'], 'goodsid' => $_var_56, 'title' => $_var_23['title'], 'displayorder' => $_var_59, 'propId' => $_var_23['propId']);
                if (empty($_var_65)) {
                    pdo_insert('sz_yi_goods_spec', $_var_67);
                    $_var_66 = pdo_insertid();
                } else {
                    pdo_update('sz_yi_goods_spec', $_var_67, array('id' => $_var_65['id']));
                    $_var_66 = $_var_65['id'];
                }
                $_var_67['id'] = $_var_66;
                $_var_63[] = $_var_66;
                $_var_59++;
                $_var_21 = $_var_23['items'];
                $_var_68 = array();
                $_var_69 = 0;
                $_var_70 = array();
                foreach ($_var_21 as $_var_22) {
                    $_var_62 = array('uniacid' => $_W['uniacid'], 'specid' => $_var_66, 'title' => $_var_22['title'], 'thumb' => $this->save_image($_var_22['thumb'], $_var_48), 'valueId' => $_var_22['valueId'], 'show' => 1, 'displayorder' => $_var_69);
                    $_var_71 = pdo_fetch('select * from ' . tablename('sz_yi_goods_spec_item') . ' where specid=:specid and valueId=:valueId limit 1', array(':specid' => $_var_66, ':valueId' => $_var_22['valueId']));
                    $_var_72 = 0;
                    if (empty($_var_71)) {
                        pdo_insert('sz_yi_goods_spec_item', $_var_62);
                        $_var_72 = pdo_insertid();
                    } else {
                        pdo_update('sz_yi_goods_spec_item', $_var_62, array('id' => $_var_71['id']));
                        $_var_72 = $_var_71['id'];
                    }
                    $_var_69++;
                    $_var_68[] = $_var_72;
                    $_var_62['id'] = $_var_72;
                    $_var_70[] = $_var_62;
                }
                $_var_67['items'] = $_var_70;
                $_var_64[] = $_var_67;
                if (count($_var_68) > 0) {
                    pdo_query('delete from ' . tablename('sz_yi_goods_spec_item') . ' where specid=:specid and id not in (' . implode(',', $_var_68) . ')', array(':specid' => $_var_66));
                } else {
                    pdo_query('delete from ' . tablename('sz_yi_goods_spec_item') . ' where specid=:specid ', array(':specid' => $_var_66));
                }
                pdo_update('sz_yi_goods_spec', array('content' => serialize($_var_68)), array('id' => $_var_65['id']));
            }
            if (count($_var_63) > 0) {
                pdo_query('delete from ' . tablename('sz_yi_goods_spec') . ' where goodsid=:goodsid and id not in (' . implode(',', $_var_63) . ')', array(':goodsid' => $_var_56));
            } else {
                pdo_query('delete from ' . tablename('sz_yi_goods_spec') . ' where goodsid=:goodsid ', array(':goodsid' => $_var_56));
            }
            $_var_73 = 0;
            $_var_17 = $_var_12['options'];
            if (count($_var_17) > 0) {
                $_var_73 = $_var_17[0]['marketprice'];
            }
            $_var_74 = array();
            $_var_59 = 0;
            foreach ($_var_17 as $_var_40) {
                $_var_27 = $_var_40['option_specs'];
                $_var_75 = array();
                $_var_76 = array();
                foreach ($_var_27 as $_var_77) {
                    foreach ($_var_64 as $_var_78) {
                        foreach ($_var_78['items'] as $_var_79) {
                            if ($_var_79['valueId'] == $_var_77['valueId']) {
                                $_var_75[] = $_var_79['id'];
                                $_var_76[] = $_var_79['valueId'];
                            }
                        }
                    }
                }
                $_var_75 = implode('_', $_var_75);
                $_var_76 = implode('_', $_var_76);
                $_var_80 = array('uniacid' => $_W['uniacid'], 'displayorder' => $_var_59, 'goodsid' => $_var_56, 'title' => implode('+', $_var_40['title']), 'specs' => $_var_75, 'stock' => $_var_40['stock'], 'marketprice' => $_var_40['marketprice'], 'skuId' => $_var_40['skuId']);
                if ($_var_73 > $_var_40['marketprice']) {
                    $_var_73 = $_var_40['marketprice'];
                }
                $_var_81 = pdo_fetch('select * from ' . tablename('sz_yi_goods_option') . ' where goodsid=:goodsid and skuId=:skuId limit 1', array(':goodsid' => $_var_56, ':skuId' => $_var_40['skuId']));
                $_var_82 = 0;
                if (empty($_var_81)) {
                    pdo_insert('sz_yi_goods_option', $_var_80);
                    $_var_82 = pdo_insertid();
                } else {
                    pdo_update('sz_yi_goods_option', $_var_80, array('id' => $_var_81['id']));
                    $_var_82 = $_var_81['id'];
                }
                $_var_59++;
                $_var_74[] = $_var_82;
            }
            if (count($_var_74) > 0) {
                pdo_query('delete from ' . tablename('sz_yi_goods_option') . ' where goodsid=:goodsid and id not in (' . implode(',', $_var_74) . ')', array(':goodsid' => $_var_56));
            } else {
                pdo_query('delete from ' . tablename('sz_yi_goods_option') . ' where goodsid=:goodsid ', array(':goodsid' => $_var_56));
            }
            $_var_7 = $_var_12['content'];
            $_var_8 = $_var_7['content'];
            preg_match_all('/<img.*?src=[\\\'| "](.*?(?:[\\.gif|\\.jpg]?))[\\\'|"].*?[\\/]?>/', $_var_8, $_var_83);
            if (isset($_var_83[1])) {
                foreach ($_var_83[1] as $_var_54) {
                    $_var_84 = $_var_54;
                    if (substr($_var_84, 0, 2) == '//') {
                        $_var_54 = 'http://' . substr($_var_54, 2);
                    }
                    $_var_85 = array('taobao' => $_var_84, 'system' => $this->save_image($_var_54, $_var_48));
                    if (!strexists($_var_85['system'], 'http://') && !strexists($_var_85['system'], 'https://')) {
                        $_var_85['system'] = $_W['attachurl'] . $_var_85['system'];
                    }
                    $_var_86[] = $_var_85;
                }
            }
            preg_match('/tfsContent : \'(.*)\'/', $_var_8, $_var_87);
            $_var_87 = iconv('GBK', 'UTF-8', $_var_87[1]);
            if (isset($_var_86)) {
                foreach ($_var_86 as $_var_54) {
                    $_var_87 = str_replace($_var_54['taobao'], $_var_54['system'], $_var_87);
                }
            }
            $_var_88 = 0;
            if (count($_var_17) > 0) {
                $_var_88 = 1;
            }
            $_var_62 = array('content' => $_var_87, 'hasoption' => $_var_88);
            if ($_var_73 > 0) {
                $_var_62['marketprice'] = $_var_73;
            }
            pdo_update('sz_yi_goods', $_var_62, array('id' => $_var_56));
            return array('result' => '1', 'goodsid' => $_var_56);
        }

        function save_image($_var_6 = '', $_var_48)
        {
            global $_W;
            if ($_var_48) {
                return p('qiniu')->save($_var_6, $_var_48);
            }
            return $this->saveToLocal($_var_6);
        }

        function get_info_url($_var_0)
        {
            return 'http://hws.m.taobao.com/cache/wdetail/5.0/?id=' . $_var_0;
        }

        function get_detail_url($_var_0)
        {
            return 'http://hws.m.taobao.com/cache/wdesc/5.0/?id=' . $_var_0;
        }

        function check_remote_file_exists($_var_6)
        {
            $_var_89 = curl_init($_var_6);
            curl_setopt($_var_89, CURLOPT_NOBODY, true);
            $_var_90 = curl_exec($_var_89);
            $_var_91 = false;
            if ($_var_90 !== false) {
                $_var_92 = curl_getinfo($_var_89, CURLINFO_HTTP_CODE);
                if ($_var_92 == 200) {
                    $_var_91 = true;
                }
            }
            curl_close($_var_89);
            return $_var_91;
        }

        function saveToLocal($_var_6)
        {
            global $_W;
            if (empty($_var_6)) {
                return '';
            }
            if (!$this->check_remote_file_exists($_var_6)) {
                return "";
            }
            $_var_93 = strrchr($_var_6, '.');
            if ($_var_93 != '.jpeg' && $_var_93 != '.gif' && $_var_93 != '.jpg' && $_var_93 != '.png') {
                return '';
            }
            $_var_94 = $_W['config']['upload']['attachdir'] . '/';
            $_var_95 = 'images/sz_yi/' . $_W['uniacid'] . '/' . date('Y') . '/' . date('m') . '/';
            load()->func('file');
            mkdirs(IA_ROOT . '/' . $_var_94 . $_var_95);
            do {
                $_var_96 = random(30) . $_var_93;
            } while (file_exists(IA_ROOT . '/' . $_var_94 . $_var_95 . '/' . $_var_96));
            $_var_95 .= $_var_96;
            $_var_97 = array('http' => array('method' => 'GET', 'timeout' => 7200));
            $_var_10 = file_get_contents($_var_6, false, stream_context_create($_var_97));
            $_var_98 = @fopen(IA_ROOT . '/' . $_var_94 . $_var_95, 'w');
            fwrite($_var_98, $_var_10);
            fclose($_var_98);
            load()->func('file');
            $_var_99 = file_remote_upload($_var_95);
            if ($_var_99 == true) {
                file_delete($_var_95);
            }
            return $_var_95;
        }

        function get_pageno_url($_var_6 = '', $_var_100 = 1)
        {
            $_var_6 .= '/search.htm?pageNo=' . $_var_100;
            return $_var_6;
        }

        function get_total_page($_var_6 = '', $_var_101 = false)
        {
            if (empty($_var_6)) {
                return array('totalpage' => 0);
            }
            $_var_8 = $this->get_page_content($_var_6);
            die($_var_8);
            $_var_102 = "";
            if ($_var_101) {
                $_var_102 = '/<span class="page-info">(.*)<\\/span>/';
            } else {
                $_var_102 = '/<b class="ui-page-s-len">(.*)<\\/b>/';
            }
            preg_match($_var_102, $_var_8, $_var_41);
            if (is_array($_var_41)) {
                $_var_103 = explode('/', $_var_41[1]);
                return array('totalpage' => $_var_103[1]);
            }
            return array('totalpage' => 0);
        }

        function httpGet($_var_6)
        {
            $_var_89 = curl_init();
            curl_setopt($_var_89, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($_var_89, CURLOPT_TIMEOUT, 500);
            curl_setopt($_var_89, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($_var_89, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($_var_89, CURLOPT_URL, $_var_6);
            $_var_104 = curl_exec($_var_89);
            curl_close($_var_89);
            return $_var_104;
        }

        function get_page_content($_var_6 = '', $_var_100 = 1)
        {
            if (empty($_var_6)) {
                return array('totalpage' => 0);
            }
            $_var_6 = $this->get_pageno_url($_var_6, $_var_100);
            load()->func('communication');
            $_var_7 = ihttp_get($_var_6);
            if (!isset($_var_7['content'])) {
                return array('result' => 0);
            }
            return $_var_7['content'];
        }

        function getRealURL($_var_6)
        {
            if (function_exists('stream_context_set_default')) {
                stream_context_set_default(array('http' => array('method' => 'HEAD')));
            }
            $_var_105 = get_headers($_var_6, 1);
            if (strpos($_var_105[0], '301') || strpos($_var_105[0], '302')) {
                if (is_array($_var_105['Location'])) {
                    return $_var_105['Location'][count($_var_105['Location']) - 1];
                } else {
                    return $_var_105['Location'];
                }
            } else {
                return $_var_6;
            }
        }

        function get_pag_items($_var_106 = '')
        {
            $_var_102 = '/data-id="(.*)"/U';
            preg_match_all($_var_102, $_var_106, $_var_107);
            if (isset($_var_107[1])) {
                return $_var_107[1];
            }
            return array();
        }

        function perms()
        {
            return array('taobao' => array('text' => $this->getName(), 'isplugin' => true, 'fetch' => '抓取宝贝-log'));
        }
    }
}