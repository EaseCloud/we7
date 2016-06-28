<?php
global $_W, $_GPC;

ca('qiniu.admin');
$set = $this->getSet();
if (checksubmit('submit')) {
    $set['user'] = is_array($_GPC['user']) ? $_GPC['user'] : array();
    if (!empty($set['user']['upload'])) {
        $ret = $this->check($set['user']);
        if (empty($ret)) {
            message('配置有误，请仔细检查参数设置!', '', 'error');
        }
    }
    $this->updateSet($set);
    message('设置保存成功!', referer(), 'success');
}
if (checksubmit('submit_admin')) {
    $set['admin'] = is_array($_GPC['admin']) ? $_GPC['admin'] : array();
    if (!empty($set['admin']['upload'])) {
        $ret = $this->check($set['admin']);
        if (empty($ret)) {
            message('配置有误，请仔细检查参数设置!', '', 'error');
        }
    }
    m('cache')->set('qiniu', $set['admin'], 'global');
    plog('qiniu.admin', '设置七牛');
    message('设置保存成功!', referer(), 'success');
}
$set['admin'] = m('cache')->getArray('qiniu', 'global');
include $this->template('set');
