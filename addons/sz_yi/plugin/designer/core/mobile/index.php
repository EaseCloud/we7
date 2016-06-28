<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$pageid = $_GPC['pageid'];
if (!empty($pageid)) {
    $page     = pdo_fetch("SELECT * FROM " . tablename('sz_yi_designer') . " WHERE uniacid= :uniacid and id=:id", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $pageid
    ));
    $pagedata = $this->model->getData($page);
    extract($pagedata);
}
$guide     = $this->model->getGuide($system, $pageinfo);
$sharelink = $this->createPluginMobileUrl('designer', array(
    'pageid' => $page['id']
));
if ($page['pagetype'] == 1 && $page['setdefault'] == 1) {
    $sharelink = $this->createMobileUrl('shop');
}
$_W['shopshare'] = array(
    'title' => $share['title'],
    'imgUrl' => $share['imgUrl'],
    'desc' => $share['desc'],
    'link' => $sharelink
);
if (p('commission')) {
    $set = p('commission')->getSet();
    if (!empty($set['level'])) {
        if (!empty($_GPC['preview'])) {
            $openid                 = 'fromUser';
            $this->footer['first']  = array(
                'text' => '首页',
                'ico' => 'home',
                'url' => $this->createMobileUrl('shop')
            );
            $this->footer['second'] = array(
                'text' => '分类',
                'ico' => 'list',
                'url' => $this->createMobileUrl('shop/category')
            );
        } else {
            $openid = m('user')->getOpenid();
        }
        $member = m('member')->getMember($openid);
        if (!empty($member) && $member['status'] == 1 && $member['isagent'] == 1) {
            $_W['shopshare']['link'] = $this->createPluginMobileUrl('designer', array(
                'pageid' => $page['id'],
                'mid' => $member['id']
            ));
            if ($page['pagetype'] == 1 && $page['setdefault'] == 1) {
                $_W['shopshare']['link'] = $this->createMobileUrl('shop', array(
                    'mid' => $member['id']
                ));
            }
            if (empty($set['become_reg']) && (empty($member['realname']) || empty($member['mobile']))) {
                $trigger = true;
            }
        } else if (!empty($_GPC['mid'])) {
            $_W['shopshare']['link'] = $this->createPluginMobileUrl('designer', array(
                'pageid' => $page['id'],
                'mid' => $_GPC['mid']
            ));
            if ($page['pagetype'] == 1 && $page['setdefault'] == 1) {
                $_W['shopshare']['link'] = $this->createMobileUrl('shop', array(
                    'mid' => $_GPC['mid']
                ));
            }
        }
    }
}
include $this->template('index');
