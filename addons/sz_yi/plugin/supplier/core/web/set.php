<?php

//decode by QQ:270656184 http://www.yunlu99.com/
global $_W, $_GPC;
ca('commission.set');
$set = $this->getSet();
if (checksubmit('submit')) {
    $data = is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array();
    $data['texts'] = is_array($_GPC['texts']) ? $_GPC['texts'] : array();
    $this->updateSet($data);
    m('cache')->set('template_' . $this->pluginname, $data['style']);
    plog('commission.set', '修改基本设置');
    message('设置保存成功!', referer(), 'success');
}
load()->func('tpl');
include $this->template('set');