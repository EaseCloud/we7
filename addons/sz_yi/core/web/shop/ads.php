<?php
/*=============================================================================
#     FileName: ads.php
#         Desc:  
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:39:14
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $ads = pdo_fetch("SELECT * FROM " . tablename('sz_yi_ads') . " WHERE uniacid = :uniacid",
        array(':uniacid' => $_W['uniacid']
    ));
    if(empty($ads)){
        $data_center = array(
            'uniacid' => $_W['uniacid'],
            'adsname' => "中部广告位",
        );
        $data_bottom = array(
            'uniacid' => $_W['uniacid'],
            'adsname' => "底部广告位",
        );

        pdo_insert('sz_yi_ads', $data_center);
        pdo_insert('sz_yi_ads', $data_bottom);
    }
    ca('shop.ads.view');
    if (!empty($_GPC['displayorder'])) {
        ca('shop.ads.edit');
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            pdo_update('sz_yi_ads', array(
                'displayorder' => $displayorder
            ), array(
                'id' => $id
            ));
        }
        plog('shop.ads.edit', '批量修改幻灯片的排序');
        message('分类排序更新成功！', $this->createWebUrl('shop/ads', array(
            'op' => 'display'
        )), 'success');
    }
    $list = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_ads') . " WHERE uniacid = '{$_W['uniacid']}'");
    //print_r($list);exit;
} elseif ($operation == 'post') {

    $id = intval($_GPC['id']);
    if (empty($id)) {
        ca('shop.ads.add');
    } else {
        ca('shop.ads.edit|shop.ads.view');
    }
    if (checksubmit('submit')) {
        //print_R($_GPC);exit;
        $data = array(
            'uniacid' => $_W['uniacid'],
            'link_1' => trim($_GPC['link_1']),
            'link_2' => trim($_GPC['link_2']),
            'link_3' => trim($_GPC['link_3']),
            'link_4' => trim($_GPC['link_4']),
            'thumb_1' => save_media($_GPC['thumb_1']),
            'thumb_2' => save_media($_GPC['thumb_2']),
            'thumb_3' => save_media($_GPC['thumb_3']),
            'thumb_4' => save_media($_GPC['thumb_4']),
        );
        if (!empty($id)) {
            pdo_update('sz_yi_ads', $data, array(
                'id' => $id
            ));
            plog('shop.ads.edit', "修改幻灯片 ID: {$id}");
        } else {
            pdo_insert('sz_yi_ads', $data);
            $id = pdo_insertid();
            plog('shop.ads.add', "添加幻灯片 ID: {$id}");
        }
        message('更新幻灯片成功！', $this->createWebUrl('shop/ads', array(
            'op' => 'display'
        )), 'success');
    }
    $item = pdo_fetch("select * from " . tablename('sz_yi_ads') . " where id=:id and uniacid=:uniacid limit 1", array(
        ":id" => $id,
        ":uniacid" => $_W['uniacid']
    ));
}
// } elseif ($operation == 'delete') {
//     ca('shop.ads.delete');
//     $id   = intval($_GPC['id']);
//     $item = pdo_fetch("SELECT id,adsname FROM " . tablename('sz_yi_ads') . " WHERE id = '$id' AND uniacid=" . $_W['uniacid'] . "");
//     if (empty($item)) {
//         message('抱歉，幻灯片不存在或是已经被删除！', $this->createWebUrl('shop/ads', array(
//             'op' => 'display'
//         )), 'error');
//     }
//     pdo_delete('sz_yi_ads', array(
//         'id' => $id
//     ));
//     plog('shop.ads.delete', "删除幻灯片 ID: {$id} 标题: {$item['adsname']} ");
//     message('幻灯片删除成功！', $this->createWebUrl('shop/ads', array(
//         'op' => 'display'
//     )), 'success');
// }
load()->func('tpl');
include $this->template('web/shop/ads');
