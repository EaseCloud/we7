<?php
global $_W, $_GPC;

ca('commission.set');
$set = $this->getSet();
if (checksubmit('submit')) {
    $data          = is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array();
    $data['texts'] = is_array($_GPC['texts']) ? $_GPC['texts'] : array();
    $this->updateSet($data);
    m('cache')->set('template_' . $this->pluginname, $data['style']);
    plog('commission.set', '修改基本设置');
    message('设置保存成功!', referer(), 'success');
}
$styles = array();
$dir    = IA_ROOT . "/addons/sz_yi/plugin/" . $this->pluginname . "/template/mobile/";
if ($handle = opendir($dir)) {
    while (($file = readdir($handle)) !== false) {
        if ($file != ".." && $file != ".") {
            if (is_dir($dir . "/" . $file)) {
                $styles[] = $file;
            }
        }
    }
    closedir($handle);
}
//Author:Y.yang Date:2016-04-08 Content:成为分销商条件（购买条件）
$goods = false;
if (!empty($set['become_goodsid'])) {
    $goods = pdo_fetch('select id,title from ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid limit 1 ', array(
        ':id' => $set['become_goodsid'],
        ':uniacid' => $_W['uniacid']
    ));
}
// END
load()->func('tpl');
include $this->template('set');
