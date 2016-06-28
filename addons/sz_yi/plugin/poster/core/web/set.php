<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
$set = $this->getSet();
if (checksubmit('submit')) {
    $set['user'] = is_array($_GPC['user']) ? $_GPC['user'] : array();
    $this->updateSet($set);
    message('设置保存成功!', referer(), 'success');
}
include $this->template('set');