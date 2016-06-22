<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
load()->func('tpl');

$op     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tempdo = empty($_GPC['tempdo']) ? "" : $_GPC['tempdo'];
$pageid = empty($_GPC['pageid']) ? "" : $_GPC['pageid'];
$apido  = empty($_GPC['apido']) ? "" : $_GPC['apido'];
if ($op == 'display') {
    ca('designer.page.view');
    $page     = empty($_GPC['page']) ? "" : $_GPC['page'];
    $pindex   = max(1, intval($page));
    $psize    = 10;
    $kw       = empty($_GPC['keyword']) ? "" : $_GPC['keyword'];
    $pages    = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid and pagename LIKE :name " . "ORDER BY savetime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(
        ':uniacid' => $_W['uniacid'],
        ':name' => "%{$kw}%"
    ));
    $pagesnum = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid " . "ORDER BY savetime DESC ", array(
        ':uniacid' => $_W['uniacid']
    ));
    $total    = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid and pagename LIKE :name " . "ORDER BY savetime DESC ", array(
        ':uniacid' => $_W['uniacid'],
        ':name' => "%{$kw}%"
    ));
    $pager    = pagination($total, $pindex, $psize);
} elseif ($op == 'post') {
    $menus     = pdo_fetchall("SELECT id,menuname,isdefault FROM " . tablename('sz_yi_designer_menu') . " WHERE uniacid= :uniacid  ", array(
        ':uniacid' => $_W['uniacid']
    ));
    $pages     = pdo_fetchall("SELECT id,pagename,pagetype,setdefault FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid  ", array(
        ':uniacid' => $_W['uniacid']
    ));
    $categorys = pdo_fetchall("SELECT id,name,parentid FROM " . tablename('sz_yi_category') . " WHERE enabled=:enabled and uniacid= :uniacid  ", array(
        ':uniacid' => $_W['uniacid'],
        ':enabled' => '1'
    ));
    if (!empty($pageid)) {
        ca('designer.page.edit');
        $datas = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid and id=:id", array(
            ':uniacid' => $_W['uniacid'],
            ':id' => $pageid
        ));
        $data  = htmlspecialchars_decode($datas['datas']);
        $data  = json_decode($data, true);
        if (!empty($data)) {
            foreach ($data as $i1 => &$dd) {
                if ($dd['temp'] == 'goods') {
                    foreach ($dd['data'] as $i2 => &$ddd) {
                        $goodinfo = pdo_fetchall("SELECT id,title,productprice,marketprice,thumb FROM " . tablename('sz_yi_goods') . " WHERE uniacid= :uniacid and id=:goodid", array(
                            ':uniacid' => $_W['uniacid'],
                            ':goodid' => $ddd['goodid']
                        ));
                        $goodinfo = set_medias($goodinfo, 'thumb');
                        if (!empty($goodinfo)) {
                            $data[$i1]['data'][$i2]['name']     = $goodinfo[0]['title'];
                            $data[$i1]['data'][$i2]['priceold'] = $goodinfo[0]['productprice'];
                            $data[$i1]['data'][$i2]['pricenow'] = $goodinfo[0]['marketprice'];
                            $data[$i1]['data'][$i2]['img']      = $goodinfo[0]['thumb'];
                        }
                    }
                    unset($ddd);
                } elseif ($dd['temp'] == 'richtext') {
                    $dd['content'] = $this->model->unescape($dd['content']);
                } elseif ($dd['temp'] == 'cube') {
                    $dd['params']['currentLayout']['isempty'] = true;
                    $dd['params']['selection']                = null;
                    $dd['params']['currentPos']               = null;
                    $has                                      = false;
                    $newarr                                   = new stdClass();
                    foreach ($dd['params']['layout'] as $k => $v) {
                        $arr = new stdClass();
                        foreach ($v as $kk => $vv) {
                            $arr->$kk = $vv;
                        }
                        $newarr->$k = $arr;
                    }
                    $dd['params']['layout'] = $newarr;
                }
            }
            $data = json_encode($data);
        }
        $data     = rtrim($data, "]");
        $data     = ltrim($data, "[");
        $pageinfo = htmlspecialchars_decode($datas['pageinfo']);
        $pageinfo = rtrim($pageinfo, "]");
        $pageinfo = ltrim($pageinfo, "[");
        $shopset  = m('common')->getSysset('shop');
        $system   = array(
            'shop' => array(
                'name' => $shopset['name'],
                'logo' => tomedia($shopset['logo'])
            )
        );
        $system   = json_encode($system);
    } else {
        ca('designer.page.edit');
        $defaultmenuid = $this->model->getDefaultMenuID();
        $pageinfo      = "{id:'M0000000000000',temp:'topbar',params:{title:'',desc:'',img:'',kw:'',footer:'1',footermenu:'{$defaultmenuid}', floatico:'0',floatstyle:'right',floatwidth:'40px',floattop:'100px',floatimg:'',floatlink:''}}";
    }
} elseif ($op == 'api') {
    if ($_W['ispost']) {
        if ($apido == 'savepage') {
            $id                    = $_GPC['pageid'];
            $datas                 = json_decode(htmlspecialchars_decode($_GPC['datas']), true);
            $date                  = date("Y-m-d H:i:s");
            $pagename              = $_GPC['pagename'];
            $pagetype              = $_GPC['pagetype'];
            $pageinfo              = $_GPC['pageinfo'];
            $p                     = htmlspecialchars_decode($pageinfo);
            $p                     = json_decode($p, true);
            $keyword               = empty($p[0]['params']['kw']) ? "" : $p[0]['params']['kw'];
            $p[0]['params']['img'] = save_media($p[0]['params']['img']);
            foreach ($datas as &$data) {
                if ($data['temp'] == 'banner' || $data['temp'] == 'menu' || $data['temp'] == 'picture') {
                    foreach ($data['data'] as &$d) {
                        $d['imgurl'] = save_media($d['imgurl']);
                    }
                    unset($d);
                } else if ($data['temp'] == 'shop') {
                    $data['params']['bgimg'] = save_media($data['params']['bgimg']);
                } else if ($data['temp'] == 'goods') {
                    foreach ($data['data'] as &$d) {
                        $d['img'] = save_media($d['img']);
                    }
                    unset($d);
                } else if ($data['temp'] == 'richtext') {
                    $content         = m('common')->html_images($this->model->unescape($data['content']));
                    $data['content'] = $this->model->escape($content);
                } else if ($data['temp'] == 'cube') {
                    foreach ($data['params']['layout'] as &$row) {
                        foreach ($row as &$col) {
                            $col['imgurl'] = save_media($col['imgurl']);
                        }
                        unset($col);
                    }
                    unset($row);
                }
            }
            unset($data);
            $insert = array(
                'pagename' => $pagename,
                'pagetype' => $pagetype,
                'pageinfo' => json_encode($p),
                'savetime' => $date,
                'datas' => json_encode($datas),
                'uniacid' => $_W['uniacid'],
                'keyword' => $keyword
            );
            if (empty($id)) {
                ca('designer.page.edit');
                $insert['createtime'] = $date;
                pdo_insert('sz_yi_designer', $insert);
                $id = pdo_insertid();
                plog('designer.page.edit', "店铺装修-添加修改页面 ID: {$id}");
            } else {
                ca('designer.page.edit');
                if ($pagetype == '4') {
                    $insert['setdefault'] = '0';
                }
                pdo_update('sz_yi_designer', $insert, array(
                    'id' => $id
                ));
                plog('designer.page.edit', "店铺装修-修改修改页面 ID: {$id}");
            }
            $rule = pdo_fetch("select * from " . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':module' => 'sz_yi',
                ':name' => "sz_yi:designer:" . $id
            ));
            if (empty($rule)) {
                $rule_data = array(
                    'uniacid' => $_W['uniacid'],
                    'name' => 'sz_yi:designer:' . $id,
                    'module' => 'sz_yi',
                    'displayorder' => 0,
                    'status' => 1
                );
                pdo_insert('rule', $rule_data);
                $rid          = pdo_insertid();
                $keyword_data = array(
                    'uniacid' => $_W['uniacid'],
                    'rid' => $rid,
                    'module' => 'sz_yi',
                    'content' => trim($keyword),
                    'type' => 1,
                    'displayorder' => 0,
                    'status' => 1
                );
                pdo_insert('rule_keyword', $keyword_data);
            } else {
                pdo_update('rule_keyword', array(
                    'content' => trim($keyword)
                ), array(
                    'rid' => $rule['id']
                ));
            }
            echo $id;
            exit;
        } elseif ($apido == 'delpage') {
            ca('designer.page.delete');
            if (empty($pageid)) {
                message('删除失败！Url参数错误', $this->createPluginWebUrl('designer'), 'error');
            } else {
                $page = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid and id=:id", array(
                    ':uniacid' => $_W['uniacid'],
                    ':id' => $pageid
                ));
                if (empty($page)) {
                    echo '删除失败！目标页面不存在！';
                    exit();
                } else {
                    $do = pdo_delete('sz_yi_designer', array(
                        'id' => $pageid
                    ));
                    if ($do) {
                        $rule = pdo_fetch("select * from " . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(
                            ':uniacid' => $_W['uniacid'],
                            ':module' => 'sz_yi',
                            ':name' => "sz_yi:designer:" . $pageid
                        ));
                        if (!empty($rule)) {
                            pdo_delete('rule_keyword', array(
                                'rid' => $rule['id']
                            ));
                            pdo_delete('rule', array(
                                'id' => $rule['id']
                            ));
                        }
                        plog('designer.page.edit', "店铺装修-修改修改页面 ID: {$pageid} 页面名称: {$page['pagename']}");
                        echo 'success';
                    } else {
                        echo '删除失败！';
                    }
                }
            }
        } elseif ($apido == 'selectgood') {
            $kw    = $_GPC['kw'];
            $goods = pdo_fetchall("SELECT id,title,productprice,marketprice,thumb,sales,unit FROM " . tablename('sz_yi_goods') . " WHERE uniacid= :uniacid and status=:status and deleted=0 AND title LIKE :title ", array(
                ':title' => "%{$kw}%",
                ':uniacid' => $_W['uniacid'],
                ':status' => '1'
            ));
            $goods = set_medias($goods, 'thumb');
            echo json_encode($goods);
        } elseif ($apido == 'setdefault') {
            ca('designer.page.setdefault');
            $do   = $_GPC['d'];
            $id   = $_GPC['id'];
            $type = $_GPC['type'];
            if ($do == 'on') {
                $pages = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer') . " WHERE pagetype=:pagetype and setdefault=:setdefault and uniacid=:uniacid ", array(
                    ':pagetype' => $type,
                    ':setdefault' => '1',
                    ':uniacid' => $_W['uniacid']
                ));
                if (!empty($pages)) {
                    $array = array(
                        'setdefault' => '0'
                    );
                    pdo_update('sz_yi_designer', $array, array(
                        'id' => $pages['id']
                    ));
                }
                $array  = array(
                    'setdefault' => '1'
                );
                $action = pdo_update('sz_yi_designer', $array, array(
                    'id' => $id
                ));
                if ($action) {
                    $json = array(
                        'result' => 'on',
                        'id' => $id,
                        'closeid' => $pages['id']
                    );
                    plog('designer.page.edit', "店铺装修-设置默认页面 ID: {$id} 页面名称: {$pages['pagename']}");
                    echo json_encode($json);
                }
            } else {
                $pages = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer') . " WHERE  id=:id and uniacid=:uniacid ", array(
                    ':id' => $id,
                    ':uniacid' => $_W['uniacid']
                ));
                if ($pages['setdefault'] == 1) {
                    $array  = array(
                        'setdefault' => '0'
                    );
                    $action = pdo_update('sz_yi_designer', $array, array(
                        'id' => $pages['id']
                    ));
                    if ($action) {
                        $json = array(
                            'result' => 'off',
                            'id' => $pages['id']
                        );
                        plog('designer.page.edit', "店铺装修-关闭默认页面 ID: {$id} 页面名称: {$pages['pagename']}");
                        echo json_encode($json);
                    }
                }
            }
        } elseif ($apido == 'selectkeyword') {
            $kw   = $_GPC['kw'];
            $pid  = $_GPC['pid'];
            $rule = pdo_fetch("select * from " . tablename('rule_keyword') . ' where content=:content and uniacid=:uniacid and module=:module limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':module' => 'sz_yi',
                ':content' => $kw
            ));
            if (empty($rule)) {
                echo 'ok';
            } else {
                $rule2 = pdo_fetch("select * from " . tablename('rule') . ' where id=:id and uniacid=:uniacid limit 1', array(
                    ':uniacid' => $_W['uniacid'],
                    ':id' => $rule['rid']
                ));
                if ($rule2['name'] == 'sz_yi:designer:' . $pid) {
                    echo 'ok';
                }
            }
        } elseif ($apido == 'selectlink') {
            $type = $_GPC['type'];
            $kw   = $_GPC['kw'];
            if ($type == 'notice') {
                $notices = pdo_fetchall("select * from " . tablename('sz_yi_notice') . ' where title LIKE :title and status=:status and uniacid=:uniacid ', array(
                    ':uniacid' => $_W['uniacid'],
                    ':status' => '1',
                    ':title' => "%{$kw}%"
                ));
                echo json_encode($notices);
            } elseif ($type == 'good') {
                $goods = pdo_fetchall("select title,id,thumb,marketprice,productprice from " . tablename('sz_yi_goods') . ' where title LIKE :title and status=1 and deleted=0 and uniacid=:uniacid ', array(
                    ':uniacid' => $_W['uniacid'],
                    ':title' => "%{$kw}%"
                ));
                $goods = set_medias($goods, 'thumb');
                echo json_encode($goods);
            } elseif ($type == 'article') {
                $articles = pdo_fetchall("select id,article_title from " . tablename('sz_yi_article') . ' where article_title LIKE :title and article_state=1 and uniacid=:uniacid ', array(
                    ':uniacid' => $_W['uniacid'],
                    ':title' => "%{$kw}%"
                ));
                echo json_encode($articles);
	    	} elseif ($type == 'coupon') {
	    		$articles = pdo_fetchall('select id,couponname,coupontype from ' . tablename('sz_yi_coupon') . ' where couponname LIKE :title and uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':title' => "%{$kw}%"));
	    		echo json_encode($articles);
            } else {
                exit();
            }
        }
    }
    exit();
}
include $this->template('index');
