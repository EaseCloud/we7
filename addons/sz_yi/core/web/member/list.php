<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$op     = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$groups = m('member')->getGroups();
$levels = m('member')->getLevels();
$shop   = m('common')->getSysset('shop');
if ($op == 'display') {
    ca('member.member.view');
    $pindex    = max(1, intval($_GPC['page']));
    $psize     = 20;
    $condition = " and dm.uniacid=:uniacid";
    $params    = array(
        ':uniacid' => $_W['uniacid']
    );
    if (!empty($_GPC['mid'])) {
        $condition .= ' and dm.id=:mid';
        $params[':mid'] = intval($_GPC['mid']);
    }
    if (!empty($_GPC['realname'])) {
        $_GPC['realname'] = trim($_GPC['realname']);
        $condition .= ' and ( dm.realname like :realname or dm.nickname like :realname or dm.mobile like :realname)';
        $params[':realname'] = "%{$_GPC['realname']}%";
    }
    if (empty($starttime) || empty($endtime)) {
        $starttime = strtotime('-1 month');
        $endtime   = time();
    }
    if (!empty($_GPC['time'])) {
        $starttime = strtotime($_GPC['time']['start']);
        $endtime   = strtotime($_GPC['time']['end']);
        if ($_GPC['searchtime'] == '1') {
            $condition .= " AND dm.createtime >= :starttime AND dm.createtime <= :endtime ";
            $params[':starttime'] = $starttime;
            $params[':endtime']   = $endtime;
        }
    }
    if ($_GPC['level'] != '') {
        $condition .= ' and dm.level=' . intval($_GPC['level']);
    }
    if ($_GPC['groupid'] != '') {
        $condition .= ' and dm.groupid=' . intval($_GPC['groupid']);
    }
    if ($_GPC['followed'] != '') {
        if ($_GPC['followed'] == 2) {
            $condition .= ' and f.follow=0 and dm.uid<>0';
        } else {
            $condition .= ' and f.follow=' . intval($_GPC['followed']);
        }
    }
    if ($_GPC['isblack'] != '') {
        $condition .= ' and dm.isblack=' . intval($_GPC['isblack']);
    }
    $sql = "select dm.*,l.levelname,g.groupname,a.nickname as agentnickname,a.avatar as agentavatar from " . tablename('sz_yi_member') . " dm " . " left join " . tablename('sz_yi_member_group') . " g on dm.groupid=g.id" . " left join " . tablename('sz_yi_member') . " a on a.id=dm.agentid" . " left join " . tablename('sz_yi_member_level') . " l on dm.level =l.id" . " left join " . tablename('mc_mapping_fans') . "f on f.openid=dm.openid  and f.uniacid={$_W['uniacid']}" . " where 1 {$condition}  ORDER BY dm.id DESC";
    if (empty($_GPC['export'])) {
        $sql .= " limit " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql, $params);
    foreach ($list as &$row) {
        $row['levelname']  = empty($row['levelname']) ? (empty($shop['levelname']) ? '普通会员' : $shop['levelname']) : $row['levelname'];
        $row['ordercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where uniacid=:uniacid and openid=:openid and status=3', array(
            ':uniacid' => $_W['uniacid'],
            ':openid' => $row['openid']
        ));
        $row['ordermoney'] = pdo_fetchcolumn('select sum(goodsprice) from ' . tablename('sz_yi_order') . ' where uniacid=:uniacid and openid=:openid and status=3', array(
            ':uniacid' => $_W['uniacid'],
            ':openid' => $row['openid']
        ));
        $row['credit1']    = m('member')->getCredit($row['openid'], 'credit1');
        $row['credit2']    = m('member')->getCredit($row['openid'], 'credit2');
        $row['followed']   = m('user')->followed($row['openid']);
    }
    unset($row);
    if ($_GPC['export'] == '1') {
        ca('member.member.export');
        plog('member.member.export', '导出会员数据');
        foreach ($list as &$row) {
            $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
            $row['groupname']  = empty($row['groupname']) ? '无分组' : $row['groupname'];
            $row['levelname']  = empty($row['levelname']) ? '普通会员' : $row['levelname'];
        }
        unset($row);
        m('excel')->export($list, array(
            "title" => "会员数据-" . date('Y-m-d-H-i', time()),
            "columns" => array(
                array(
                    'title' => '昵称',
                    'field' => 'nickname',
                    'width' => 12
                ),
                array(
                    'title' => '姓名',
                    'field' => 'realname',
                    'width' => 12
                ),
                array(
                    'title' => '手机号',
                    'field' => 'mobile',
                    'width' => 12
                ),
                array(
                    'title' => '会员等级',
                    'field' => 'levelname',
                    'width' => 12
                ),
                array(
                    'title' => '会员分组',
                    'field' => 'groupname',
                    'width' => 12
                ),
                array(
                    'title' => '注册时间',
                    'field' => 'createtime',
                    'width' => 12
                ),
                array(
                    'title' => '积分',
                    'field' => 'credit1',
                    'width' => 12
                ),
                array(
                    'title' => '余额',
                    'field' => 'credit2',
                    'width' => 12
                ),
                array(
                    'title' => '成交订单数',
                    'field' => 'ordercount',
                    'width' => 12
                ),
                array(
                    'title' => '成交总金额',
                    'field' => 'ordermoney',
                    'width' => 12
                )
            )
        ));
    }
    $total           = pdo_fetchcolumn("select count(*) from" . tablename('sz_yi_member') . " dm " . " left join " . tablename('sz_yi_member_group') . " g on dm.groupid=g.id" . " left join " . tablename('sz_yi_member_level') . " l on dm.level =l.id" . " left join " . tablename('mc_mapping_fans') . "f on f.openid=dm.openid" . " where 1 {$condition} ", $params);
    $pager           = pagination($total, $pindex, $psize);
    $opencommission  = false;
    $plug_commission = p('commission');
    if ($plug_commission) {
        $comset = $plug_commission->getSet();
        if (!empty($comset)) {
            $opencommission = true;
        }
    }
} else if ($op == 'detail') {
    ca('member.member.view');
    $hascommission = false;
    $plugin_com    = p('commission');
    if ($plugin_com) {
        $plugin_com_set = $plugin_com->getSet();
        $hascommission  = !empty($plugin_com_set['level']);
    }
    $id = intval($_GPC['id']);
    if (checksubmit('submit')) {
        ca('member.member.edit');
        $data = is_array($_GPC['data']) ? $_GPC['data'] : array();

        $member = m('member')->getMember($id);

        if( (!empty($data['level']) || !empty($member['level'])) && $data['level'] != $member['level'])
        {

            $new_level_name = $old_level_name = '普通会员';
            foreach ($levels as $key => $value) {
                if($data['level'] == $value['id'])
                {
                    $new_level_name = $value['levelname'];
                }

                if($member['level'] == $value['id'])
                {
                    $old_level_name = $value['levelname'];
                }
            }
            $msg = array(
                'first' => array(
                    'value' => "后台修改会员等级！",
                    "color" => "#4a5077"
                ),
                'keyword1' => array(
                    'title' => '修改等级',
                    'value' => "由【". $old_level_name ."】修改为 【" . $new_level_name . "】!",
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "\r\n我们已为您修改会员等级。",
                    "color" => "#4a5077"
                )
            );

            $detailurl  = $this->createMobileUrl('member');
            m('message')->sendCustomNotice($member['openid'], $msg, $detailurl);         
        }


        pdo_update('sz_yi_member', $data, array(
            'id' => $id,
            'uniacid' => $_W['uniacid']
        ));
        $member = m('member')->getMember($id);
        plog('member.member.edit', "修改会员资料  ID: {$member['id']} <br/> 会员信息:  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        if ($hascommission) {
            if (cv('commission.agent.changeagent')) {
                $adata = is_array($_GPC['adata']) ? $_GPC['adata'] : array();
                if (!empty($adata)) {
                    if (empty($_GPC['oldstatus']) && $adata['status'] == 1) {
                        $time               = time();
                        $adata['agenttime'] = time();
                        $plugin_com->sendMessage($member['openid'], array(
                            'nickname' => $member['nickname'],
                            'agenttime' => $time
                        ), TM_COMMISSION_BECOME);
                        plog('commission.agent.check', "审核分销商 <br/>分销商信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
                    }
                    plog('commission.agent.edit', "修改分销商 <br/>分销商信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
                    pdo_update('sz_yi_member', $adata, array(
                        'id' => $id,
                        'uniacid' => $_W['uniacid']
                    ));
                    if (empty($_GPC['oldstatus']) && $adata['status'] == 1) {
                        if (!empty($member['agentid'])) {
                            $plugin_com->upgradeLevelByAgent($member['agentid']);
                        }
                    }
                }
            }
        }
        message('保存成功!', $this->createWebUrl('member/list'), 'success');
    }
    if ($hascommission) {
        $agentlevels = $plugin_com->getLevels();
    }
    $member = m('member')->getMember($id);
    if ($hascommission) {
        $member = $plugin_com->getInfo($id, array(
            'total',
            'pay'
        ));
    }
    $member['self_ordercount'] = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where uniacid=:uniacid and openid=:openid and status=3', array(
        ':uniacid' => $_W['uniacid'],
        ':openid' => $member['openid']
    ));
    $member['self_ordermoney'] = pdo_fetchcolumn('select sum(goodsprice) from ' . tablename('sz_yi_order') . ' where uniacid=:uniacid and openid=:openid and status=3', array(
        ':uniacid' => $_W['uniacid'],
        ':openid' => $member['openid']
    ));
    if (!empty($member['agentid'])) {
        $parentagent = m('member')->getMember($member['agentid']);
    }
    $diyform_flag   = 0;
    $diyform_plugin = p('diyform');
    if ($diyform_plugin) {
        if (!empty($member['diymemberdata'])) {
            $diyform_flag = 1;
            $fields       = iunserializer($member['diymemberfields']);
        }
    }
} else if ($op == 'delete') {
    ca('member.member.delete');
    $id      = intval($_GPC['id']);
    $isagent = intval($_GPC['isagent']);
    $member  = pdo_fetch("select * from " . tablename('sz_yi_member') . " where uniacid=:uniacid and id=:id limit 1 ", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $id
    ));
    if (empty($member)) {
        message('会员不存在，无法删除!', $this->createWebUrl('member/list'), 'error');
    }
    if (p('commission')) {
        $agentcount = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_member') . ' where  uniacid=:uniacid and agentid=:agentid limit 1 ', array(
            ':uniacid' => $_W['uniacid'],
            ':agentid' => $id
        ));
        if ($agentcount > 0) {
            message('此会员有下线存在，无法删除! ', '', 'error');
        }
    }
    pdo_delete('sz_yi_member', array(
        'id' => $_GPC['id']
    ));
    plog('member.member.delete', "删除会员  ID: {$member['id']} <br/>会员信息: {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
    message('删除成功！', $this->createWebUrl('member/list'), 'success');
} else if ($operation == 'setblack') {
    ca('member.member.setblack');
    $id     = intval($_GPC['id']);
    $member = pdo_fetch("select * from " . tablename('sz_yi_member') . " where uniacid=:uniacid and id=:id limit 1 ", array(
        ':uniacid' => $_W['uniacid'],
        ':id' => $id
    ));
    if (empty($member)) {
        message('会员不存在，无法设置黑名单!', $this->createWebUrl('member/list'), 'error');
    }
    $black = intval($_GPC['black']);
    if (!empty($black)) {
        pdo_update('sz_yi_member', array(
            'isblack' => 1
        ), array(
            'id' => $_GPC['id']
        ));
        plog('member.member.black', "设置黑名单 <br/>用户信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        message('设置黑名单成功！', $this->createWebUrl('member/list'), 'success');
    } else {
        pdo_update('sz_yi_member', array(
            'isblack' => 0
        ), array(
            'id' => $_GPC['id']
        ));
        plog('member.member.black', "取消黑名单 <br/>用户信息:  ID: {$member['id']} /  {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        message('取消黑名单成功！', $this->createWebUrl('member/list'), 'success');
    }
}
load()->func('tpl');
include $this->template('web/member/list');

