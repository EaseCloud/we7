<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'index';
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$designer = p('designer');
if (empty($this->yzShopSet['ispc']) or is_weixin()) {
    if ($designer) {
        $pagedata = $designer->getPage();
        if ($pagedata) {
            extract($pagedata);
            $guide = $designer->getGuide($system, $pageinfo);
            $_W['shopshare'] = array('title' => $share['title'], 'imgUrl' => $share['imgUrl'], 'desc' => $share['desc'], 'link' => $this->createMobileUrl('shop'));
            if (p('commission')) {
                $set = p('commission')->getSet();
                if (!empty($set['level'])) {
                    $member = m('member')->getMember($openid);
                    if (!empty($member) && $member['status'] == 1 && $member['isagent'] == 1) {
                        $_W['shopshare']['link'] = $this->createMobileUrl('shop', array('mid' => $member['id']));
                        if (empty($set['become_reg']) && (empty($member['realname']) || empty($member['mobile']))) {
                            $trigger = true;
                        }
                    } else {
                        if (!empty($_GPC['mid'])) {
                            $_W['shopshare']['link'] = $this->createMobileUrl('shop', array('mid' => $_GPC['mid']));
                        }
                    }
                }
            }
            include $this->template('shop/index_diy');
            die;
        }
    }
}
if ($operation == 'index') {
    $advs = pdo_fetchall('select id,advname,link,thumb,thumb_pc from ' . tablename('sz_yi_adv') . ' where uniacid=:uniacid and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
    foreach ($advs as $key => $adv) {
        if (!empty($advs[$key]['thumb'])) {
            $adv[] = $advs[$key];
        }
        if (!empty($advs[$key]['thumb_pc'])) {
            $adv_pc[] = $advs[$key];
        }
    }
    $advs = set_medias($advs, 'thumb,thumb_pc');
    $advs_pc = set_medias($adv_pc, 'thumb,thumb_pc');
    $category = pdo_fetchall('select id,name,thumb,parentid,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and ishome=1 and enabled=1 order by displayorder desc', array(':uniacid' => $uniacid));
    $category = set_medias($category, 'thumb');
    $index_name = array('isrecommand' => '精品推荐', 'isnew' => '新上商品', 'ishot' => '热卖商品', 'isdiscount' => '促销商品', 'issendfree' => '包邮商品', 'istime' => '限时特价');
    foreach ($category as &$c) {
        $c['thumb'] = tomedia($c['thumb']);
        if ($c['level'] == 3) {
            $c['url'] = $this->createMobileUrl('shop/list', array('tcate' => $c['id']));
        } else {
            if ($c['level'] == 2) {
                $c['url'] = $this->createMobileUrl('shop/list', array('ccate' => $c['id']));
            }
        }
    }
    $ads_pc = array();
    $goods_pc = array();
    if (!empty($this->yzShopSet['index']['isrecommand']) && !empty($this->yzShopSet['ispc'])) {
        $ads_pc['isrecommand'] = pdo_fetchall('select * from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'isrecommand\'', array(':uniacid' => $uniacid));
        $goods_pc['isrecommand'] = pdo_fetchall('select * from ' . tablename('sz_yi_goods') . ' where uniacid = :uniacid and status = 1 and deleted = 0 and isrecommand=1 order by displayorder desc limit 4', array(':uniacid' => $uniacid));
        $ads_pc['isrecommand'] = set_medias($ads_pc['isrecommand'], 'thumb');
        $goods_pc['isrecommand'] = set_medias($goods_pc['isrecommand'], 'thumb');
    }
    if (!empty($this->yzShopSet['index']['isnew']) && !empty($this->yzShopSet['ispc'])) {
        $ads_pc['isnew'] = pdo_fetchall('select * from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'isnew\'', array(':uniacid' => $uniacid));
        $goods_pc['isnew'] = pdo_fetchall('select * from ' . tablename('sz_yi_goods') . ' where uniacid = :uniacid and status = 1 and deleted = 0 and isnew=1 order by displayorder desc limit 4', array(':uniacid' => $uniacid));
        $ads_pc['isnew'] = set_medias($ads_pc['isnew'], 'thumb');
        $goods_pc['isnew'] = set_medias($goods_pc['isnew'], 'thumb');
    }
    if (!empty($this->yzShopSet['index']['ishot']) && !empty($this->yzShopSet['ispc'])) {
        $ads_pc['ishot'] = pdo_fetchall('select * from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'ishot\'', array(':uniacid' => $uniacid));
        $goods_pc['ishot'] = pdo_fetchall('select * from ' . tablename('sz_yi_goods') . ' where uniacid = :uniacid and status = 1 and deleted = 0 and ishot=1 order by displayorder desc limit 4', array(':uniacid' => $uniacid));
        $ads_pc['ishot'] = set_medias($ads_pc['ishot'], 'thumb');
        $goods_pc['ishot'] = set_medias($goods_pc['ishot'], 'thumb');
    }
    if (!empty($this->yzShopSet['index']['isdiscount']) && !empty($this->yzShopSet['ispc'])) {
        $ads_pc['isdiscount'] = pdo_fetchall('select * from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'isdiscount\'', array(':uniacid' => $uniacid));
        $goods_pc['isdiscount'] = pdo_fetchall('select * from ' . tablename('sz_yi_goods') . ' where uniacid = :uniacid and status = 1 and deleted = 0 and isdiscount=1 order by displayorder desc limit 4', array(':uniacid' => $uniacid));
        $ads_pc['isdiscount'] = set_medias($ads_pc['isdiscount'], 'thumb');
        $goods_pc['isdiscount'] = set_medias($goods_pc['isdiscount'], 'thumb');
    }
    if (!empty($this->yzShopSet['index']['issendfree']) && !empty($this->yzShopSet['ispc'])) {
        $ads_pc['issendfree'] = pdo_fetchall('select * from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'issendfree\'', array(':uniacid' => $uniacid));
        $goods_pc['issendfree'] = pdo_fetchall('select * from ' . tablename('sz_yi_goods') . ' where uniacid = :uniacid and status = 1 and deleted = 0 and issendfree=1 order by displayorder desc limit 4', array(':uniacid' => $uniacid));
        $ads_pc['issendfree'] = set_medias($ads_pc['issendfree'], 'thumb');
        $goods_pc['issendfree'] = set_medias($goods_pc['issendfree'], 'thumb');
    }
    if (!empty($this->yzShopSet['index']['istime']) && !empty($this->yzShopSet['ispc'])) {
        $ads_pc['istime'] = pdo_fetchall('select * from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'istime\'', array(':uniacid' => $uniacid));
        $goods_pc['istime'] = pdo_fetchall('select * from ' . tablename('sz_yi_goods') . ' where uniacid = :uniacid and status = 1 and deleted = 0 and istime=1 order by displayorder desc limit 4', array(':uniacid' => $uniacid));
        $ads_pc['istime'] = set_medias($ads_pc['istime'], 'thumb');
        $goods_pc['istime'] = set_medias($goods_pc['istime'], 'thumb');
    }
    $ads_pc['bottom_ad'] = pdo_fetch('select link,thumb from ' . tablename('sz_yi_adpc') . ' where uniacid=:uniacid and location=\'bottom_ad\'', array(':uniacid' => $uniacid));
    if (!empty($ads_pc['bottom_ad'])) {
        $ads_pc['bottom_ad'] = set_medias($ads_pc['bottom_ad'], 'thumb');
    }
    unset($c);
} else {
    if ($operation == 'goods') {
        $type = $_GPC['type'];
        $args = array('page' => $_GPC['page'], 'pagesize' => 6, 'isrecommand' => 1, 'order' => 'displayorder desc,createtime desc', 'by' => '');
        $goods = m('goods')->getList($args);
    }
}
if ($_W['isajax']) {
    if ($operation == 'index') {
        show_json(1, array('set' => $set, 'advs' => $advs, 'category' => $category));
    } else {
        if ($operation == 'goods') {
            $type = $_GPC['type'];
            show_json(1, array('goods' => $goods, 'pagesize' => $args['pagesize']));
        }
    }
}
$this->setHeader();
include $this->template('shop/index');