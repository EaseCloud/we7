<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

if (!$_W['isfounder']) {
    message('您无权操作!', '', 'error');
}
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->model('user');
if ($operation == 'display') {
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $status    = $_GPC['status'];
    $condition = "";
    $params    = array();
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
		$condition .= ' and ac.name like :keyword';
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    if ($_GPC['type'] != '') {
        $condition .= ' and p.type=' . intval($_GPC['type']);
    }
	$list = pdo_fetchall("SELECT p.*,ac.name FROM " . tablename('sz_yi_perm_plugin') . " p  " . " left join " . tablename('account_wechats') . " ac on p.acid = ac.acid  " . " WHERE 1 {$condition} ORDER BY id desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as &$row) {
        $row_plugins = explode(",", $row['plugins']);
        $aplugins    = array();
        foreach ($row_plugins as $rp) {
            $aplugins[] = "'" . $rp . "'";
        }
        if (!empty($aplugins)) {
            $row['plugins'] = pdo_fetchall('select name from ' . tablename('sz_yi_plugin') . ' where identity in (' . implode(',', $aplugins) . ')');
        } else {
            $row['plugins'] = array();
        }
    }
    unset($row);
    $total   = pdo_fetchcolumn("SELECT count(*) FROM " . tablename('sz_yi_perm_plugin') . " p  " . " left join " . tablename('users') . " u on p.uid = u.uid  " . " left join " . tablename('account_wechats') . " ac on p.acid = ac.acid  " . " WHERE 1 {$condition} ", $params);
    $pager   = pagination($total, $pindex, $psize);
    $plugins = m('plugin')->getAll();
	
} elseif ($operation == 'post') {
	
    $id           = intval($_GPC['id']);
    $item         = pdo_fetch("SELECT * FROM " . tablename('sz_yi_perm_plugin') . " WHERE id =:id limit 1", array(
        ':id' => $id
    ));
	
    $item_plugins = array();
    if (!empty($item)) {
        $item_plugins = explode(',', $item['plugins']);
        $user         = pdo_fetch('select uid,username from ' . tablename('users') . ' where uid=:uid limit 1', array(
            ':uid' => $item['uid']
        ));
        $account      = pdo_fetch('select acid,name from ' . tablename('account_wechats') . ' where acid=:acid limit 1', array(
            ':acid' => $item['acid']
        ));
    }
    if (checksubmit('submit')) {
		
        $data = array(
            'type' => 1,
            'acid' => intval($_GPC['acid']),
            'uid' => intval($_GPC['uid']),
            'plugins' => is_array($_GPC['plugins']) ? implode(',', $_GPC['plugins']) : ''
        );
		
        if (empty($data['type'])) {
            $data['acid'] = 0;
        } else {
            $data['uid'] = 0;
        }
        if (!empty($id)) {
            pdo_update('sz_yi_perm_plugin', $data, array(
                'id' => $id
            ));
        } else {
			
            if (empty($data['type'])) {
                $usercount = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_perm_plugin') . ' where uid=:uid limit 1', array(
                    ':uid' => $data['uid']
                ));
                if ($usercount > 0) {
                    message('此用户的插件权限已经设置过，不能重复设置!', '', 'error');
                }
            } else {
                $wechatcount = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_perm_plugin') . ' where acid=:acid limit 1', array(
                    ':acid' => $data['acid']
                ));
                if ($wechatcount > 0) {
                    message('此公众号的插件权限已经设置过，不能重复设置!', '', 'error');
                }
            }
			
			
            pdo_insert('sz_yi_perm_plugin', $data);
            $id = pdo_insertid();
			
        }
		
        message('保存成功!', $this->createPluginWebUrl('perm/plugins'), 'success');
    }
	
} elseif ($operation == 'delete') {
    $id   = intval($_GPC['id']);
    $item = pdo_fetch("SELECT id FROM " . tablename('sz_yi_perm_plugin') . " WHERE id = '$id'");
    if (empty($item)) {
        message('抱歉，权限设置不存在或是已经被删除！', $this->createPluginWebUrl('perm/plugins', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_perm_plugin', array(
        'id' => $id
    ));
    message('删除成功！', $this->createPluginWebUrl('perm/plugins', array(
        'op' => 'display'
    )), 'success');
} elseif ($operation == 'query_user') {
    $kwd       = trim($_GPC['keyword']);
    $params    = array();
    $condition = " and u.uid<>1";
    if (!empty($kwd)) {
        $condition .= " AND ( u.username LIKE :keyword or p.realname LIKE :keyword or p.mobile LIKE :keyword )";
        $params[':keyword'] = "%{$kwd}%";
    }
    $ds = pdo_fetchall('SELECT u.uid,u.username,p.realname,p.mobile FROM ' . tablename('users') . " u " . " left join " . tablename('users_profile') . " p on p.uid = u.uid " . " WHERE 1 {$condition} order by u.uid desc", $params);
    include $this->template('query_user');
    exit;
} elseif ($operation == 'query_wechat') {
    $kwd       = trim($_GPC['keyword']);
    $params    = array();
    $condition = " ";
    if (!empty($kwd)) {
        $condition .= " AND ( a.name LIKE :keyword or u.username like :keyword)";
        $params[':keyword'] = "%{$kwd}%";
    }
    $ds = pdo_fetchall('SELECT distinct a.acid, a.name FROM ' . tablename('account_wechats') . " a  " . " left join " . tablename('uni_account') . " ac on ac.uniacid = a.uniacid " . " left join " . tablename('uni_account_users') . " uac on uac.uniacid = ac.uniacid" . " left join " . tablename('users') . " u on u.uid = uac.uid " . " WHERE 1 {$condition} order by a.acid desc", $params);
    include $this->template('query_wechat');
    exit;
}
load()->func('tpl');
include $this->template('plugins');
