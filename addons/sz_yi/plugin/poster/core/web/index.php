<?php
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    ca('poster.view');
    if (checksubmit('submit')) {
        ca('poster.clear');
        load()->func('file');
        @rmdirs(IA_ROOT . '/addons/sz_yi/data/poster/' . $_W['uniacid']);
        @rmdirs(IA_ROOT . '/addons/sz_yi/data/qrcode/' . $_W['uniacid']);
        $acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE `uniacid`=:uniacid LIMIT 1", array(
            ':uniacid' => $_W['uniacid']
        ));
        pdo_update('sz_yi_poster_qr', array(
            'mediaid' => ''
        ), array(
            'acid' => $acid
        ));
        plog('poster.clear', '清除海报缓存');
        message('缓存清除成功!', referer(), 'success');
    }
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 10;
    $params    = array(
        ':uniacid' => $_W['uniacid']
    );
    $condition = " and uniacid=:uniacid ";
    if (!empty($_GPC['keyword'])) {
        $_GPC['keyword'] = trim($_GPC['keyword']);
        $condition .= ' AND `title` LIKE :title';
        $params[':title'] = '%' . trim($_GPC['keyword']) . '%';
    }
    if (!empty($_GPC['type'])) {
        $condition .= ' AND `type` = :type';
        $params[':type'] = intval($_GPC['type']);
    }
    $list  = pdo_fetchall("SELECT * FROM " . tablename('sz_yi_poster') . " WHERE 1 {$condition} ORDER BY isdefault desc,createtime desc LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
    foreach ($list as &$row) {
    	$row['times'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_poster_scan') . ' where posterid=:posterid and uniacid=:uniacid', array(':posterid' => $row['id'], ':uniacid' => $_W['uniacid']));
    	$row['follows'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_poster_log') . ' where posterid=:posterid and uniacid=:uniacid', array(':posterid' => $row['id'], ':uniacid' => $_W['uniacid']));
    }
    unset($row);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('sz_yi_poster') . " where 1 {$condition} ", $params);
    $pager = pagination($total, $pindex, $psize);
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
	$plugin_coupon = p('coupon');
    if (empty($id)) {
        ca('poster.add');
    } else {
        ca('poster.edit|poster.view');
    }
    $item = pdo_fetch("SELECT * FROM " . tablename('sz_yi_poster') . " WHERE id =:id and uniacid=:uniacid limit 1", array(
        ':id' => $id,
        ':uniacid' => $_W['uniacid']
    ));
    if (!empty($item)) {
        $data = json_decode(str_replace('&quot;', "'", $item['data']), true);
    }
    if (checksubmit('submit')) {
        load()->model('account');
        $acid = pdo_fetchcolumn('select acid from ' . tablename('account_wechats') . ' where uniacid=:uniacid limit 1', array(
            ':uniacid' => $_W['uniacid']
        ));
        $data = array(
            'uniacid' => $_W['uniacid'],
            'title' => trim($_GPC['title']),
            'type' => intval($_GPC['type']),
            'keyword' => trim($_GPC['keyword']),
            'bg' => save_media($_GPC['bg']),
            'data' => htmlspecialchars_decode($_GPC['data']),
            'resptitle' => trim($_GPC['resptitle']),
            'respthumb' => save_media($_GPC['respthumb']),
            'respdesc' => trim($_GPC['respdesc']),
            'respurl' => trim($_GPC['respurl']),
            'isdefault' => intval($_GPC['isdefault']),
            'createtime' => time(),
            'oktext' => trim($_GPC['oktext']),
            'waittext' => trim($_GPC['waittext']),
            'subcredit' => intval($_GPC['subcredit']),
            'submoney' => $_GPC['submoney'],
            'reccredit' => intval($_GPC['reccredit']),
            'recmoney' => $_GPC['recmoney'],
            'subtext' => trim($_GPC['subtext']),
            'bedown' => intval($_GPC['bedown']),
            'beagent' => intval($_GPC['beagent']),
            'isopen' => intval($_GPC['isopen']),
            'opentext' => trim($_GPC['opentext']),
            'openurl' => trim($_GPC['openurl']),
            'paytype' => intval($_GPC['paytype']),
            'subpaycontent' => trim($_GPC['subpaycontent']),
            'recpaycontent' => trim($_GPC['recpaycontent']),
            'templateid' => trim($_GPC['templateid']),
            'entrytext' => trim($_GPC['entrytext'])
        );
		if ($plugin_coupon) {
			$data['reccouponid'] = intval($_GPC['reccouponid']);
			$data['reccouponnum'] = intval($_GPC['reccouponnum']);
			$data['subcouponid'] = intval($_GPC['subcouponid']);
			$data['subcouponnum'] = intval($_GPC['subcouponnum']);
		}
        if ($data['isdefault'] == 1) {
            pdo_update('sz_yi_poster', array(
                'isdefault' => 0
            ), array(
                'uniacid' => $_W['uniacid'],
                'isdefault' => 1,
                'type' => $data['type']
            ));
        }
        if (!empty($id)) {
            pdo_update('sz_yi_poster', $data, array(
                'id' => $id,
                'uniacid' => $_W['uniacid']
            ));
            plog('poster.edit', "修改超级海报 ID: {$id}");
        } else {
            pdo_insert('sz_yi_poster', $data);
            $id = pdo_insertid();
            plog('poster.add', "添加超级海报 ID: {$id}");
        }
        $rule = pdo_fetch("select * from " . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(
            ':uniacid' => $_W['uniacid'],
            ':module' => 'sz_yi',
            ':name' => "sz_yi:poster:" . $data['type']
        ));
        if (empty($rule)) {
            $rule_data = array(
                'uniacid' => $_W['uniacid'],
                'name' => 'sz_yi:poster:' . $data['type'],
                'module' => 'sz_yi',
                'displayorder' => 0,
                'status' => 1
            );
            pdo_insert('rule', $rule_data);
            $rid          = pdo_insertid();
            $keyword_data = array(
                'uniacid' => $_W['uniacid'],
                'rid' => $rid,
                'module' => 'sz_yi',
                'content' => $data['type'] == 3 ? ("^" . trim($data['keyword']) . "\+*[0-9]{1,}$") : trim($data['keyword']),
                'type' => $data['type'] == 3 ? 3 : 1,
                'displayorder' => 0,
                'status' => 1
            );
            pdo_insert('rule_keyword', $keyword_data);
        } else {
            $content = $data['type'] == 3 ? ("^" . trim($data['keyword']) . "\+*[0-9]{1,}$") : trim($data['keyword']);
            pdo_update('rule_keyword', array(
                'content' => $content
            ), array(
                'rid' => $rule['id']
            ));
        }
        $ruleauto = pdo_fetch("select * from " . tablename('rule') . ' where uniacid=:uniacid and module=:module and name=:name  limit 1', array(
            ':uniacid' => $_W['uniacid'],
            ':module' => 'sz_yi',
            ':name' => "sz_yi:poster:auto"
        ));
        if (empty($ruleauto)) {
            $rule_data = array(
                'uniacid' => $_W['uniacid'],
                'name' => 'sz_yi:poster:auto',
                'module' => 'sz_yi',
                'displayorder' => 0,
                'status' => 1
            );
            pdo_insert('rule', $rule_data);
            $rid          = pdo_insertid();
            $keyword_data = array(
                'uniacid' => $_W['uniacid'],
                'rid' => $rid,
                'module' => 'sz_yi',
                'content' => 'SZ_YI_POSTER',
                'type' => 1,
                'displayorder' => 0,
                'status' => 1
            );
            pdo_insert('rule_keyword', $keyword_data);
        }
        message('更新海报成功！', $this->createPluginWebUrl('poster', array(
            'op' => 'display'
        )), 'success');
    }
    $imgroot = $_W['attachurl'];
    if (empty($_W['setting']['remote'])) {
        setting_load('remote');
    }
    if (!empty($_W['setting']['remote']['type'])) {
        $imgroot = $_W['attachurl_remote'];
    }
	if ($plugin_coupon) {
		if (!empty($item['subcouponid'])) {
			$subcoupon = $plugin_coupon->getCoupon($item['subcouponid']);
		}
		if (!empty($item['reccouponid'])) {
			$reccoupon = $plugin_coupon->getCoupon($item['reccouponid']);
		}
	}
} elseif ($operation == 'delete') {
    ca('poster.delete');
    $id     = intval($_GPC['id']);
    $poster = pdo_fetch("SELECT id,title FROM " . tablename('sz_yi_poster') . " WHERE id = '$id'");
    if (empty($poster)) {
        message('抱歉，海报不存在或是已经被删除！', $this->createPluginWebUrl('poster', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_delete('sz_yi_poster', array(
        'id' => $id,
        'uniacid' => $_W['uniacid']
    ));
    pdo_delete('sz_yi_poster_log', array(
        'posterid' => $id,
        'uniacid' => $_W['uniacid']
    ));
    plog('poster.add', "删除超级海报 ID: {$id} 海报名称: {$poster['title']}");
    message('海报删除成功！', $this->createPluginWebUrl('poster', array(
        'op' => 'display'
    )), 'success');
} else if ($operation == 'setdefault') {
    ca('poster.setdefault');
    $id     = intval($_GPC['id']);
    $poster = pdo_fetch("SELECT * FROM " . tablename('sz_yi_poster') . " WHERE id = '$id'");
    if (empty($poster)) {
        message('抱歉，海报不存在或是已经被删除！', $this->createPluginWebUrl('poster', array(
            'op' => 'display'
        )), 'error');
    }
    pdo_update('sz_yi_poster', array(
        'isdefault' => 0
    ), array(
        'uniacid' => $_W['uniacid'],
        'isdefault' => 1,
        'type' => $poster['type']
    ));
    pdo_update('sz_yi_poster', array(
        'isdefault' => 1
    ), array(
        'uniacid' => $_W['uniacid'],
        'id' => $poster['id']
    ));
    plog('poster.setdefault', "设置默认超级海报 ID: {$id} 海报名称: {$poster['title']}");
    message('海报设置成功！', $this->createPluginWebUrl('poster', array(
        'op' => 'display'
    )), 'success');
}
load()->func('tpl');
include $this->template('index');
