<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation  = !empty($_GPC['op']) ? $_GPC['op'] : 'index';
$openid     = m('user')->getOpenid();
$uniacid    = $_W['uniacid'];
$set        = m('common')->getSysset('shop');
$commission = p('commission');
if ($commission) {
    $shopid = intval($_GPC['shopid']);
    if (!empty($shopid)) {
        $myshop = set_medias($commission->getShop($shopid), array(
            'img',
            'logo'
        ));
    }
}
$current_category = false;
if (!empty($_GPC['tcate'])) {
    $current_category = pdo_fetch('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where id=:id and uniacid=:uniacid order by displayorder DESC', array(
        ':id' => intval($_GPC['tcate']),
        ':uniacid' => $_W['uniacid']
    ));
} else if (!empty($_GPC['ccate'])) {
    $current_category = pdo_fetch('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where id=:id and uniacid=:uniacid order by displayorder DESC', array(
        ':id' => intval($_GPC['ccate']),
        ':uniacid' => $_W['uniacid']
    ));
} else if (!empty($_GPC['pcate'])) {
    $current_category = pdo_fetch('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where id=:id and uniacid=:uniacid order by displayorder DESC', array(
        ':id' => intval($_GPC['pcate']),
        ':uniacid' => $_W['uniacid']
    ));
}
$parent_category = pdo_fetch('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where id=:id  and uniacid=:uniacid limit 1', array(
    ':id' => $current_category['parentid'],
    ':uniacid' => $_W['uniacid']
));
$args = array(
    'pagesize' => 20,
    'page' => $_GPC['page'],
    'isnew' => $_GPC['isnew'],
    'ishot' => $_GPC['ishot'],
    'isrecommand' => $_GPC['isrecommand'],
    'isdiscount' => $_GPC['isdiscount'],
    'istime' => $_GPC['istime'],
    'keywords' => $_GPC['keywords'],
    'pcate' => $_GPC['pcate'],
    'ccate' => $_GPC['ccate'],
    'tcate' => $_GPC['tcate'],
    'order' => $_GPC['order'],
    'by' => $_GPC['by']
);
if (!empty($myshop['selectgoods']) && !empty($myshop['goodsids'])) {
    $args['ids'] = $myshop['goodsids'];
}


$condition = ' and `uniacid` = :uniacid AND `deleted` = 0 and status=1';
$params    = array(
     ':uniacid' => $_W['uniacid']
);
if (!empty( $args['ids'] )) {
    $condition .= " and id in ( " .  $args['ids'] . ")";
}
$isnew = !empty($args['isnew']) ? 1 : 0;
if (!empty($isnew)) {
    $condition .= " and isnew=1";
}
$ishot = !empty($args['ishot']) ? 1 : 0;
if (!empty($ishot)) {
   $condition .= " and ishot=1";
}
$isrecommand = !empty($args['isrecommand']) ? 1 : 0;
if (!empty($isrecommand)) {
   $condition .= " and isrecommand=1";
}
$isdiscount = !empty($args['isdiscount']) ? 1 : 0;
if (!empty($isdiscount)) {
    $condition .= " and isdiscount=1";
}
$istime = !empty($args['istime']) ? 1 : 0;
if (!empty($istime)) {
   $condition .= " and istime=1 and " . time() . ">=timestart and " . time() . "<=timeend";
}
$keywords = !empty($args['keywords']) ? $args['keywords'] : '';
if (!empty($keywords)) {
    $condition .= ' AND `title` LIKE :title';
    $params[':title'] = '%' . trim($keywords) . '%';
}
$tcate = !empty($args['tcate']) ? intval($args['tcate']) : 0;
if (!empty($tcate)) {
$condition .= " AND ( `tcate` = :tcate or  FIND_IN_SET({$tcate},cates)<>0 )";
   $params[':tcate'] = intval($tcate);
}
$ccate = !empty($args['ccate']) ? intval($args['ccate']) : 0;
if (!empty($ccate)) {
   $condition .= " AND ( `ccate` = :ccate or  FIND_IN_SET({$ccate},cates)<>0 )";
   $params[':ccate'] = intval($ccate);
}
$pcate = !empty($args['pcate']) ? intval($args['pcate']) : 0;
if (!empty($pcate)) {
    $condition .= ' AND `pcate` = :pcate';
    $params[':pcate'] = intval($pcate);
}
$total=pdo_fetchcolumn("SELECT count(*) FROM " . tablename('sz_yi_goods') . " where 1 {$condition}",$params);

$pindex = max(1, intval($_GPC['page']));
$pager = pagination($total, $pindex, $args['pagesize']);


$goods    = m('goods')->getList($args);
$category = false;
if (intval($_GPC['page']) <= 1) {
    if (!empty($_GPC['tcate'])) {
        $parent_category1 = pdo_fetch('select id,parentid,name,level,thumb from ' . tablename('sz_yi_category') . ' where id=:id  and uniacid=:uniacid limit 1', array(
            ':id' => $parent_category['parentid'],
            ':uniacid' => $_W['uniacid']
        ));
        $category         = pdo_fetchall('select id,name,level,thumb from ' . tablename('sz_yi_category') . ' where parentid=:parentid and enabled=1 and uniacid=:uniacid order by level asc, isrecommand desc, displayorder DESC', array(
            ':parentid' => $parent_category['id'],
            ':uniacid' => $_W['uniacid']
        ));
        $category         = array_merge(array(
            array(
                'id' => 0,
                'name' => '全部分类',
                'level' => 0
            ),
            $parent_category1,
            $parent_category
        ), $category);
    } else if (!empty($_GPC['ccate'])) {
        if (intval($set['catlevel']) == 3) {
            $category = pdo_fetchall('select id,name,level,thumb from ' . tablename('sz_yi_category') . ' where (parentid=:parentid or id=:parentid) and enabled=1  and uniacid=:uniacid order by level asc, isrecommand desc, displayorder DESC', array(
                ':parentid' => intval($_GPC['ccate']),
                ':uniacid' => $_W['uniacid']
            ));
        } else {
            $category = pdo_fetchall('select id,name,level,thumb from ' . tablename('sz_yi_category') . ' where parentid=:parentid and enabled=1 and uniacid=:uniacid order by level asc, isrecommand desc, displayorder DESC', array(
                ':parentid' => $parent_category['id'],
                ':uniacid' => $_W['uniacid']
            ));
        }
        $category = array_merge(array(
            array(
                'id' => 0,
                'name' => '全部分类',
                'level' => 0
            ),
            $parent_category
        ), $category);
    } else if (!empty($_GPC['pcate'])) {
        $category = pdo_fetchall('select id,name,level,thumb from ' . tablename('sz_yi_category') . ' where (parentid=:parentid or id=:parentid) and enabled=1 and uniacid=:uniacid order by level asc, isrecommand desc, displayorder DESC', array(
            ':parentid' => intval($_GPC['pcate']),
            ':uniacid' => $_W['uniacid']
        ));
        $category = array_merge(array(
            array(
                'id' => 0,
                'name' => '全部分类',
                'level' => 0
            )
        ), $category);
    } else {
        $category = pdo_fetchall('select id,name,level,thumb from ' . tablename('sz_yi_category') . ' where parentid=0 and enabled=1 and uniacid=:uniacid order by displayorder DESC', array(
            ':uniacid' => $_W['uniacid']
        ));
    }
    foreach ($category as &$c) {
        $c['thumb'] = tomedia($c['thumb']);
        if ($current_category['id'] == $c['id']) {
            $c['on'] = true;
        }
    }
    unset($c);
}
if ($_W['isajax']) {
    show_json(1, array(
        'goods' => $goods,
        'pagesize' => $args['pagesize'],
        'category' => $category,
        'current_category' => $current_category
    ));
}
include $this->template('shop/list');

