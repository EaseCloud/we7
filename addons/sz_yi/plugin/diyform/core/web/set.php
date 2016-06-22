<?php


global $_W, $_GPC;

ca('diyform.set.view');
$set       = $this->getSet();
$form_list = $this->model->getDiyformList();
if (checksubmit('submit')) {
    ca('diyform.set.save');
    $data = is_array($_GPC['setdata']) ? array_merge($set, $_GPC['setdata']) : array();
    $this->updateSet($data);
    plog('diyform.set.save', '修改基本设置');
    message('设置保存成功!', referer(), 'success');
}
load()->func('tpl');
include $this->template('set');