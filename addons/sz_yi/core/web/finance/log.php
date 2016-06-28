<?php
/*=============================================================================
#     FileName: log.php
#         Desc: 日志
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:36:13
#      History:
=============================================================================*/
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$op      = $operation = $_GPC['op'] ? $_GPC['op'] : 'display';
$groups  = m('member')->getGroups();
$levels  = m('member')->getLevels();
$uniacid = $_W['uniacid'];
if ($op == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize  = 20;
    $type   = intval($_GPC['type']);
    if ($type == 1) {
        ca('finance.withdraw.view');
    } else {
        ca('finance.recharge.view');
    }
    $condition = ' and log.uniacid=:uniacid and log.type=:type and log.money<>0';
    $params    = array(
        ':uniacid' => $_W['uniacid'],
        ':type' => $type
    );
    if (!empty($_GPC['realname'])) {
        $_GPC['realname'] = trim($_GPC['realname']);
        $condition .= ' and (m.realname like :realname or m.nickname like :realname or m.mobile like :realname)';
        $params[':realname'] = "%{$_GPC['realname']}%";
    }
    if (!empty($_GPC['logno'])) {
        $_GPC['logno'] = trim($_GPC['logno']);
        $condition .= ' and log.logno like :logno';
        $params[':logno'] = "%{$_GPC['logno']}%";
    }
    if (empty($starttime) || empty($endtime)) {
        $starttime = strtotime('-1 month');
        $endtime   = time();
    }
    if (!empty($_GPC['time'])) {
        $starttime = strtotime($_GPC['time']['start']);
        $endtime   = strtotime($_GPC['time']['end']);
        if ($_GPC['searchtime'] == '1') {
            $condition .= " AND log.createtime >= :starttime AND log.createtime <= :endtime ";
            $params[':starttime'] = $starttime;
            $params[':endtime']   = $endtime;
        }
    }
    if (!empty($_GPC['level'])) {
        $condition .= ' and m.level=' . intval($_GPC['level']);
    }
    if (!empty($_GPC['groupid'])) {
        $condition .= ' and m.groupid=' . intval($_GPC['groupid']);
    }
    if (!empty($_GPC['rechargetype'])) {
        $_GPC['rechargetype'] = trim($_GPC['rechargetype']);
        $condition            = " AND log.rechargetype=:rechargetype";
        if ($_GPC['rechargetype'] == 'system1') {
            $condition = " AND log.rechargetype='system' and log.money<0";
        }
        $params[':rechargetype'] = trim($_GPC['rechargetype']);
    }
    if ($_GPC['status'] != '') {
        $condition .= ' and log.status=' . intval($_GPC['status']);
    }
    $sql = "select log.id,m.id as mid, m.realname,m.avatar,m.weixin,log.logno,log.type,log.status,log.rechargetype,m.nickname,m.mobile,g.groupname,log.money,log.createtime,l.levelname from " . tablename('sz_yi_member_log') . " log " . " left join " . tablename('sz_yi_member') . " m on m.openid=log.openid" . " left join " . tablename('sz_yi_member_group') . " g on m.groupid=g.id" . " left join " . tablename('sz_yi_member_level') . " l on m.level =l.id" . " where 1 {$condition} ORDER BY log.createtime DESC ";
    if (empty($_GPC['export'])) {
        $sql .= "LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
    }
    $list = pdo_fetchall($sql, $params);
    if ($_GPC['export'] == 1) {
        if ($_GPC['type'] == 1) {
            ca('finance.withdraw.export');
            plog('finance.withdraw.export', '导出提现记录');
        } else {
            ca('finance.recharge.export');
            plog('finance.recharge.export', '导出充值记录');
        }
        foreach ($list as &$row) {
            $row['createtime'] = date('Y-m-d H:i', $row['createtime']);
            $row['groupname']  = empty($row['groupname']) ? '无分组' : $row['groupname'];
            $row['levelname']  = empty($row['levelname']) ? '普通会员' : $row['levelname'];
            if ($row['status'] == 0) {
                if ($row['type'] == 0) {
                    $row['status'] = "未充值";
                } else {
                    $row['status'] = "申请中";
                }
            } else if ($row['status'] == 1) {
                if ($row['type'] == 0) {
                    $row['status'] = "充值成功";
                } else {
                    $row['status'] = "完成";
                }
            } else if ($row['status'] == -1) {
                if ($row['type'] == 0) {
                    $row['status'] = "";
                } else {
                    $row['status'] = "失败";
                }
            }
            if ($row['rechargetype'] == 'system') {
                $row['rechargetype'] = "后台";
            } else if ($row['rechargetype'] == 'wechat') {
                $row['rechargetype'] = "微信";
            } else if ($row['rechargetype'] == 'alipay') {
                $row['rechargetype'] = "支付宝";
            }
        }
        unset($row);
        $columns = array(
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
                'title' => (empty($type) ? "充值金额" : "提现金额"),
                'field' => 'money',
                'width' => 12
            ),
            array(
                'title' => (empty($type) ? "充值时间" : "提现申请时间"),
                'field' => 'createtime',
                'width' => 12
            )
        );
        if (empty($_GPC['type'])) {
            $columns[] = array(
                'title' => "充值方式",
                'field' => 'rechargetype',
                'width' => 12
            );
        }
        m('excel')->export($list, array(
            "title" => (empty($type) ? "会员充值数据-" : "会员提现记录") . date('Y-m-d-H-i', time()),
            "columns" => $columns
        ));
    }
    $total = pdo_fetchcolumn("select count(*) from " . tablename('sz_yi_member_log') . " log " . " left join " . tablename('sz_yi_member') . " m on m.openid=log.openid and m.uniacid= log.uniacid" . " left join " . tablename('sz_yi_member_group') . " g on m.groupid=g.id" . " left join " . tablename('sz_yi_member_level') . " l on m.level =l.id" . " where 1 {$condition} ", $params);
    $pager = pagination($total, $pindex, $psize);
} else if ($op == 'pay') {
    $id      = intval($_GPC['id']);
    $paytype = $_GPC['paytype'];
    $set     = m('common')->getSysset('shop');
    $log     = pdo_fetch('select * from ' . tablename('sz_yi_member_log') . ' where id=:id and uniacid=:uniacid limit 1', array(
        ':id' => $id,
        ':uniacid' => $uniacid
    ));
    if (empty($log)) {
        message('未找到记录!', '', 'error');
    }
    $member = m('member')->getMember($log['openid']);
    if ($paytype == 'manual') {
        ca('finance.withdraw.withdraw');
        pdo_update('sz_yi_member_log', array(
            'status' => 1
        ), array(
            'id' => $id,
            'uniacid' => $uniacid
        ));
        m('notice')->sendMemberLogMessage($logid);
        plog('finance.withdraw.withdraw', "余额提现 方式: 手动 ID: {$log['id']} <br/>会员信息: ID: {$member['id']} / {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        message('手动提现完成!', referer(), 'success');
    } else if ($paytype == 'wechat') {
        ca('finance.withdraw.withdraw');
        $result = m('finance')->pay($log['openid'], 1, $log['money'] * 100, $log['logno'], $set['name'] . '余额提现');
        if (is_error($result)) {
            message('微信钱包提现失败: ' . $result['message'], '', 'error');
        }
        pdo_update('sz_yi_member_log', array(
            'status' => 1
        ), array(
            'id' => $id,
            'uniacid' => $uniacid
        ));
        m('notice')->sendMemberLogMessage($log['id']);
        plog('finance.withdraw.withdraw', "余额提现 ID: {$log['id']} 方式: 微信 金额: {$log['money']} <br/>会员信息:  ID: {$member['id']} / {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        message('微信钱包提现成功!', referer(), 'success');
    } else if ($paytype == 'refuse') {
        ca('finance.withdraw.withdraw');
        pdo_update('sz_yi_member_log', array(
            'status' => -1
        ), array(
            'id' => $id,
            'uniacid' => $uniacid
        ));
        m('member')->setCredit($log['openid'], 'credit2', $log['money'], array(
            0,
            $set['name'] . '余额提现退回'
        ));
        m('notice')->sendMemberLogMessage($log['id']);
        plog('finance.withdraw.withdraw', "拒绝余额度提现 ID: {$log['id']} 金额: {$log['money']} <br/>会员信息:  ID: {$member['id']} / {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        message('操作成功!', referer(), 'success');
    } else if ($paytype == 'refund') {
        ca('finance.recharge.refund');
        if (!empty($log['type'])) {
            message('充值退款失败: 非充值记录!', '', 'error');
        }
        if ($log['rechargetype'] == 'system') {
            message('充值退款失败: 后台充值无法退款!', '', 'error');
        }
        $current_credit = m('member')->getCredit($log['openid'], 'credit2');
        if ($log['money'] > $current_credit) {
            message('充值退款失败: 会员账户余额不足，无法进行退款!', '', 'error');
        }
        $out_refund_no = 'RR' . substr($log['logno'], 2);
        if ($log['rechargetype'] == 'wechat') {
            $result = m('finance')->refund($log['openid'], $log['logno'], $out_refund_no, $log['money'] * 100, $log['money'] * 100);
        } else {
            $result = m('finance')->pay($log['openid'], 1, $log['money'] * 100, $out_refund_no, $set['name'] . '充值退款');
        }
        if (is_error($result)) {
            message('充值退款失败: ' . $result['message'], '', 'error');
        }
        pdo_update('sz_yi_member_log', array(
            'status' => 3
        ), array(
            'id' => $id,
            'uniacid' => $uniacid
        ));
        m('member')->setCredit($log['openid'], 'credit2', -$log['money'], array(
            0,
            $set['name'] . '充值退款'
        ));
        m('notice')->sendMemberLogMessage($log['id']);
        plog('finance.withdraw.withdraw', "充值退款 ID: {$log['id']} 金额: {$log['money']} <br/>会员信息:  ID: {$member['id']} / {$member['openid']}/{$member['nickname']}/{$member['realname']}/{$member['mobile']}");
        message('充值退款成功!', referer(), 'success');
    } else {
        message('未找到提现方式!', '', 'error');
    }
}
load()->func('tpl');
include $this->template('web/finance/log');
