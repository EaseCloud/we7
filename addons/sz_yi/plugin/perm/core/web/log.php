<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

ca('perm.log.view');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->model('user');
if ($operation == 'display') {
    if (empty($clearstarttime) || empty($clearendtime)) {
        $clearstarttime = strtotime('-1 month');
        $clearendtime   = time();
    }
    if (checksubmit('clearlog')) {
        ca('perm.log.clear');
        $starttime = strtotime($_GPC['cleartime']['start']);
        $endtime   = strtotime($_GPC['cleartime']['end']);
        pdo_query('delete from ' . tablename('sz_yi_perm_log') . ' where  uniacid=:uniacid and  createtime >=:starttime  AND createtime <=:endtime', array(
            ':uniacid' => $_W['uniacid'],
            ':starttime' => $starttime,
            ':endtime' => $endtime
        ));
        plog('perm.log.clear', "清除日志缓存 开始时间: {$_GPC['cleartime']['start']} 结束时间: {$_GPC['cleartime']['end']}");
        message('缓存清除成功!', referer(), 'success');
    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = " and log.uniacid=:uniacid";
    $params    = array(
        ':uniacid' => $_W['uniacid']
    );
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
        $condition .= ' and ( log.op like :keyword or u.username like :keyword)';
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    if (!empty($_GPC['logtype'])) {
        $condition .= ' and log.type=:logtype';
        $params[':logtype'] = trim($_GPC['logtype']);
    }
    if (empty($starttime) || empty($endtime)) {
        $starttime = strtotime('-1 month');
        $endtime   = time();
    }
    if (!empty($_GPC['searchtime'])) {
        $starttime = strtotime($_GPC['time']['start']);
        $endtime   = strtotime($_GPC['time']['end']);
        if (!empty($timetype)) {
            $condition .= " AND log.createtime >= :starttime AND log.createtime <= :endtime ";
            $params[':starttime'] = $starttime;
            $params[':endtime']   = $endtime;
        }
    }
    $list  = pdo_fetchall("SELECT  log.* ,u.username FROM " . tablename('sz_yi_perm_log') . " log  " . " left join " . tablename('users') . " u on log.uid = u.uid  " . " WHERE 1 {$condition} ORDER BY id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    $total = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('sz_yi_perm_log') . " log  " . " left join " . tablename('users') . " u on log.uid = u.uid  " . " WHERE 1 {$condition} ", $params);
    $pager = pagination($total, $pindex, $psize);
    $types = $this->model->getLogTypes();
} elseif ($operation == 'delete') {
    ca('perm.log.delete');
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id FROM " . tablename('sz_yi_perm_log') . " WHERE id = '$id'");
    if (empty($item)) {
        message('抱歉，日志不存在或是已经被删除！', $this->createPluginWebUrl('perm/log', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_perm_log', array(
        'id' => $id
    ));
    message('删除成功！', $this->createPluginWebUrl('perm/log', array(
        'op' => 'display'
    )), 'success');
}
load()->func('tpl');
include $this->template('log');
