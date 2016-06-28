<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
load()->func('tpl');
$op     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$tempdo = empty($_GPC['tempdo']) ? "" : $_GPC['tempdo'];
$menuid = empty($_GPC['menuid']) ? "" : $_GPC['menuid'];
$apido  = empty($_GPC['apido']) ? "" : $_GPC['apido'];
if ($op == 'display') {
    ca('designer.menu.view');
    $page     = empty($_GPC['page']) ? "" : $_GPC['page'];
    $pindex   = max(1, intval($page));
    $psize    = 10;
    $kw       = empty($_GPC['keyword']) ? "" : $_GPC['keyword'];
    $menus    = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_designer_menu') . " WHERE uniacid= :uniacid and menuname LIKE :name " . "ORDER BY createtime DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(
        ':uniacid' => $_W['uniacid'],
        ':name' => "%{$kw}%"
    ));
    $menusnum = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sz_yi_designer_menu') . " WHERE uniacid= :uniacid " . "ORDER BY createtime DESC ", array(
        ':uniacid' => $_W['uniacid']
    ));
    $total    = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('sz_yi_designer_menu') . " WHERE uniacid= :uniacid and menuname LIKE :name " . "ORDER BY createtime DESC ", array(
        ':uniacid' => $_W['uniacid'],
        ':name' => "%{$kw}%"
    ));
    $pager    = pagination($total, $pindex, $psize);
} elseif ($op == 'post') {
    $menu   = pdo_fetch('select * from ' . tablename('sz_yi_designer_menu') . ' where id=:id and uniacid=:uniacid limit 1', array(
        ':id' => $menuid,
        ':uniacid' => $_W['uniacid']
    ));
    $params = array(
        "previewbg" => '#999999',
        "height" => '49px',
        "textcolor" => '#666666',
        "textcolorhigh" => '#666666',
        "iconcolor" => '#666666',
        "iconcolorhigh" => '#666666',
        "bgcolor" => '#fafafa',
        "bgcolorhigh" => '#fafafa',
        "bordercolor" => '#bfbfbf',
        "bordercolorhigh" => '#bfbfbf',
        "showtext" => 1,
        "showborder" => 1,
        "showicon" => 1,
        "textcolor2" => '#666666',
        "bgcolor2" => '#fafafa',
        "bordercolor2" => '#bfbfbf',
        "showborder2" => 1
    );
    $menus  = array(
        array(
            "id" => 1,
            "title" => '购物中心',
            "icon" => 'fa fa-list',
            "url" => '',
            "subMenus" => array(
                array(
                    'title' => '商城首页',
                    'url' => $this->createMobileUrl('shop')
                )
            )
        )
    );
    if (!empty($menu)) {
        $menus  = json_decode($menu['menus'], true);
        $params = json_decode($menu['params'], true);
    }
    foreach ($menus as $key => &$m) {
        $m['textcolor']   = empty($key) ? $params['textcolorhigh'] : $params['textcolor'];
        $m['bgcolor']     = empty($key) ? $params['bgcolorhigh'] : $params['bgcolor'];
        $m['bordercolor'] = empty($key) ? $params['bordercolorhigh'] : $params['bordercolor'];
        $m['iconcolor']   = empty($key) ? $params['iconcolorhigh'] : $params['iconcolor'];
    }
    unset($m);
    $pages     = pdo_fetchall("SELECT id,pagename,pagetype,setdefault FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid  ", array(
        ':uniacid' => $_W['uniacid']
    ));
    $categorys = pdo_fetchall("SELECT id,name,parentid FROM " . tablename('sz_yi_category') . " WHERE enabled=:enabled and uniacid= :uniacid  ", array(
        ':uniacid' => $_W['uniacid'],
        ':enabled' => '1'
    ));
    if ($_W['ispost'] && $_W['isajax']) {
        $menus  = htmlspecialchars_decode($_GPC['menus']);
        $params = htmlspecialchars_decode($_GPC['params']);
        if (empty($menus) || empty($params)) {
            die(json_encode(array(
                'result' => 0,
                'message' => '参数错误!'
            )));
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'menuname' => $_GPC['menuname'],
            'params' => $params,
            'menus' => $menus
        );
        if (empty($menu)) {
            $data['createtime'] = time();
            pdo_insert('sz_yi_designer_menu', $data);
            $menid = pdo_insertid();
        } else {
            pdo_update('sz_yi_designer_menu', $data, array(
                'id' => $menuid,
                'uniacid' => $_W['uniacid']
            ));
        }
        die(json_encode(array(
            'result' => 1,
            'menuid' => $menuid
        )));
    }
} elseif ($op == 'delete') {
    ca('designer.menu.delete');
    if (empty($menuid)) {
        die('参数错误!');
    }
    $menu = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer_menu') . " WHERE uniacid= :uniacid and id=:id", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $menuid
    ));
    if (empty($menu)) {
        die('菜单未找到!');
    }
    pdo_delete('sz_yi_designer_menu', array(
        'id' => $menuid,
        'uniacid' => $_W['uniacid']
    ));
    die('success');
} elseif ($op == 'setdefault') {
    ca('designer.menu.setdefault');
    if (empty($menuid)) {
        die('参数错误!');
    }
    $menu = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer_menu') . " WHERE uniacid= :uniacid and id=:id", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $menuid
    ));
    if (empty($menu)) {
        die('菜单未找到!');
    }
    if ($_GPC['d'] == 'on') {
        pdo_update('sz_yi_designer_menu', array(
            'isdefault' => 0
        ), array(
            'uniacid' => $_W['uniacid']
        ));
        pdo_update('sz_yi_designer_menu', array(
            'isdefault' => 1
        ), array(
            'id' => $menuid,
            'uniacid' => $_W['uniacid']
        ));
    } else {
        pdo_update('sz_yi_designer_menu', array(
            'isdefault' => 0
        ), array(
            'id' => $menuid,
            'uniacid' => $_W['uniacid']
        ));
    }
    die('success');
}
include $this->template('menu');
