<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
if (!$_W['isfounder']) {
    message('无权访问!');
}
$domain  = $_SERVER['HTTP_HOST'];
$ip      = gethostbyname($domain);
$setting = setting_load('site');
$id      = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
$auth    = $this->getAuthSet();
load()->func('communication');
if ($_W['ispost']) {
    if (empty($_GPC['domain'])) {
        message('域名不能为空!', '', 'error');
    }
    if (empty($_GPC['code'])) {
        message('请填写授权码!', '', 'error');
    }
    if (empty($_GPC['id'])) {
        message('您还没未注册站点!', '', 'error');
    }
    $resp = ihttp_post("http://www.baidu.com/api.php", array(
        'type' => 's',
        'ip' => $ip,
        'id' => $id,
        'code' => $_GPC['code'],
        'domain' => $domain
    ));
    if ($resp['content'] == '1') {
        $set  = pdo_fetch('select id, sets from ' . tablename('sz_yi_sysset') . ' order by id asc limit 1');
        $sets = iunserializer($set['sets']);
        if (!is_array($sets)) {
            $sets = array();
        }
        $sets['auth'] = array(
            'ip' => $ip,
            'id' => $id,
            'code' => $_GPC['code'],
            'domain' => $_GPC['domain']
        );
        if (empty($set)) {
            pdo_insert('sz_yi_sysset', array(
                'sets' => iserializer($sets),
                'uniacid' => $_W['uniacid']
            ));
        } else {
            pdo_update('sz_yi_sysset', array(
                'sets' => iserializer($sets)
            ), array(
                'id' => $set['id']
            ));
        }
        message('系统授权成功！', referer(), 'success');
    }
    message('授权失败，请联系客服!错误信息:' . $resp['content']);
}
$status = 0;
if (!empty($ip) && !empty($id) && !empty($auth['code'])) {
    load()->func('communication');
    $resp = ihttp_post("http://www.baidu.com/api.php", array(
        'type' => 's',
        'ip' => $ip,
        'id' => $id,
        'code' => $auth['code'],
        'domain' => $domain
    ));
    if ($resp['content'] == '1') {
        $status = 1;
    }
}
include $this->template('web/sysset/auth');
