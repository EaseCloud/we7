<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

ca('shop.comment.view');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->model('user');
if ($operation == 'display') {
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = " and c.uniacid=:uniacid and c.deleted=0";
    $params    = array(
        ':uniacid' => $_W['uniacid']
    );
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
        $condition .= ' and ( o.ordersn like :keyword or g.title like :keyword)';
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    if (empty($starttime) || empty($endtime)) {
        $starttime = strtotime('-1 month');
        $endtime   = time();
    }
    if (!empty($_GPC['searchtime'])) {
        $starttime = strtotime($_GPC['time']['start']);
        $endtime   = strtotime($_GPC['time']['end']);
        if (!empty($timetype)) {
            $condition .= " AND c.createtime >= :starttime AND c.createtime <= :endtime ";
            $params[':starttime'] = $starttime;
            $params[':endtime']   = $endtime;
        }
    }
    if ($_GPC['fade'] != '') {
        if (empty($_GPC['fade'])) {
            $condition .= " AND c.openid=''";
        } else {
            $condition .= " AND c.openid<>''";
        }
    }
    if ($_GPC['replystatus'] != '') {
        if (empty($_GPC['replystatus'])) {
            $condition .= " AND c.reply_content=''";
        } else {
            $condition .= " AND c.append_content='' and c.append_reply_content=''";
        }
    }
    $list  = pdo_fetchall("SELECT  c.*, o.ordersn,g.title,g.thumb FROM " . tablename('sz_yi_order_comment') . " c  " . " left join " . tablename('sz_yi_goods') . " g on c.goodsid = g.id  " . " left join " . tablename('sz_yi_order') . " o on c.orderid = o.id  " . " WHERE 1 {$condition} ORDER BY createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('sz_yi_order_comment') . " c  " . " left join " . tablename('sz_yi_goods') . " g on c.goodsid = g.id  " . " left join " . tablename('sz_yi_order') . " o on c.orderid = o.id  " . " WHERE 1 {$condition} ", $params);
    $pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'delete') {
    ca('shop.comment.delete');
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id,goodsid FROM " . tablename('sz_yi_order_comment') . " WHERE id ='$id'");
    if (empty($item)) {
        message('抱歉，评价不存在或是已经被删除！', $this->createWebUrl('shop/comment', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_update('sz_yi_order_comment', array(
        'deleted' => 1
    ), array(
        'id' => $id,
        'uniacid' => $_W['uniacid']
    ));
    $goods = pdo_fetch('select id,thumb,title from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(
        ':id' => $item['goodsid'],
        ':uniacid' => $_W['uniacid']
    ));
    plog('shop.comment.delete', "删除评价 ID: {$id} 商品ID: {$goods['id']} 商品标题: {$goods['title']}");
    message('删除成功！', $this->createWebUrl('shop/comment', array(
        'op' => 'display'
    )), 'success');
} elseif ($operation == 'add') {
    ca('shop.comment.add');
    $id      = intval($_GPC['id']);
    $item    = pdo_fetch("SELECT * FROM " . tablename('sz_yi_order_comment') . " WHERE id =:id and uniacid=:uniacid limit 1 ", array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    $goodsid = intval($_GPC['goodsid']);
    if (checksubmit()) {
        $goods = pdo_fetch('select id,thumb,title from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(
            ':id' => $goodsid,
            ':uniacid' => $_W['uniacid']
        ));
        $data  = array(
            'uniacid' => $_W['uniacid'],
            'level' => intval($_GPC['level']),
            'goodsid' => intval($_GPC['goodsid']),
            'nickname' => trim($_GPC['nickname']),
            'headimgurl' => trim($_GPC['headimgurl']),
            'content' => $_GPC['content'],
            'images' => is_array($_GPC['images']) ? iserializer($_GPC['images']) : iserializer(array()),
            'reply_content' => $_GPC['reply_content'],
            'reply_images' => is_array($_GPC['reply_images']) ? iserializer($_GPC['reply_images']) : iserializer(array()),
            'append_content' => $_GPC['append_content'],
            'append_images' => is_array($_GPC['append_images']) ? iserializer($_GPC['append_images']) : iserializer(array()),
            'append_reply_content' => $_GPC['append_reply_content'],
            'append_reply_images' => is_array($_GPC['append_reply_images']) ? iserializer($_GPC['append_reply_images']) : iserializer(array()),
            'createtime' => time()
        );
        if (empty($data['nickname'])) {
            $data['nickname'] = pdo_fetchcolumn('select nickname from ' . tablename('mc_members') . " where nickname<>'' order by rand() limit 1");
        }
        if (empty($data['headimgurl'])) {
            $data['headimgurl'] = pdo_fetchcolumn('select avatar from ' . tablename('mc_members') . " where avatar<>'' order by rand() limit 1");
        }
        if (!empty($id)) {
            pdo_update('sz_yi_order_comment', $data, array(
                'id' => $id
            ));
            plog('shop.comment.edit', "编辑商品虚拟评价 ID: {$id} 商品ID: {$goods['id']} 商品标题: {$goods['title']}");
        } else {
            pdo_insert('sz_yi_order_comment', $data);
            $id = pdo_insertid();
            plog('shop.comment.add', "添加虚拟评价 ID: {$id} 商品ID: {$goods['id']} 商品标题: {$goods['title']}");
        }
        message('更新评价成功!', $this->createWebUrl('shop/comment'), 'success');
    }
    if (empty($goodsid)) {
        $goodsid = intval($item['goodsid']);
    }
    $goods = pdo_fetch('select id,thumb,title from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(
        ':id' => $goodsid,
        ':uniacid' => $_W['uniacid']
    ));
} elseif ($operation == 'post') {
    ca('shop.comment.edit');
    $id    = intval($_GPC['id']);
    $item  = pdo_fetch("SELECT * FROM " . tablename('sz_yi_order_comment') . " WHERE id =:id and uniacid=:uniacid limit 1 ", array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    $goods = pdo_fetch('select id,thumb,title from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1', array(
        ':id' => $item['goodsid'],
        ':uniacid' => $_W['uniacid']
    ));
    $order = pdo_fetch('select id,ordersn from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid limit 1', array(
        ':id' => $item['orderid'],
        ':uniacid' => $_W['uniacid']
    ));
    if (checksubmit()) {
        $data = array(
            'uniacid' => $_W['uniacid'],
            'reply_content' => $_GPC['reply_content'],
            'reply_images' => is_array($_GPC['reply_images']) ? iserializer($_GPC['reply_images']) : iserializer(array()),
            'append_reply_content' => $_GPC['append_reply_content'],
            'append_reply_images' => is_array($_GPC['append_reply_images']) ? iserializer($_GPC['append_reply_images']) : iserializer(array())
        );
        pdo_update('sz_yi_order_comment', $data, array(
            'id' => $id
        ));
        plog('shop.comment.edit', "回复商品评价 ID: {$id} 商品ID: {$goods['id']} 商品标题: {$goods['title']}");
        message('更新评价成功!', $this->createWebUrl('shop/comment'), 'success');
    }
}
load()->func('tpl');
include $this->template('web/shop/comment');
