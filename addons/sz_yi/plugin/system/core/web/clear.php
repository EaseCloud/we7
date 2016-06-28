<?php


global $_W, $_GPC;

if (!$_W['isfounder']) {
    message('您无权操作!', '', 'error');
}
if (checksubmit('submit')) {
    $wechatid  = intval($_GPC['wechatid']);
    $condition = "";
    $acid      = 0;
    $where     = array();
    if ($wechatid != -1) {
        $condition = ' and uniacid=' . $wechatid;
        $where     = array(
            'uniacid' => $wechatid
        );
        $acid      = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE `uniacid`=:uniacid LIMIT 1", array(
            ':uniacid' => $wechatid
        ));
    }
    load()->func('file');
    if (is_array($_GPC['shop'])) {
        foreach ($_GPC['shop'] as $data) {
            if ($data == 'goods') {
                pdo_query('delete from  ' . tablename('sz_yi_goods') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_goods_option') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_goods_param') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_goods_spec') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_goods_spec_item') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_goods_comment') . " where 1 {$condition}");
                if (table_exists('sz_yi_goods_comment')) {
                    pdo_query('delete from  ' . tablename('sz_yi_goods_comment') . " where 1 {$condition}");
                }
            } else if ($data == 'category') {
                pdo_query('delete from  ' . tablename('sz_yi_category') . " where 1 {$condition}");
            } else if ($data == 'dispatch') {
                pdo_query('delete from  ' . tablename('sz_yi_dispatch') . " where 1 {$condition}");
            } else if ($data == 'adv') {
                pdo_query('delete from  ' . tablename('sz_yi_adv') . " where 1 {$condition}");
            } else if ($data == 'notice') {
                pdo_query('delete from   ' . tablename('sz_yi_notice') . " where 1 {$condition}");
            } else if ($data == 'level') {
                pdo_query('delete from   ' . tablename('sz_yi_member_level') . " where 1 {$condition}");
            } else if ($data == 'group') {
                pdo_query('delete from   ' . tablename('sz_yi_member_group') . " where 1 {$condition}");
            } else if ($data == 'member') {
                pdo_query('delete from  ' . tablename('sz_yi_member') . " where 1 {$condition}");
                pdo_query('delete from   ' . tablename('sz_yi_member_address') . " where 1 {$condition}");
                pdo_query('delete from   ' . tablename('sz_yi_member_cart') . " where 1 {$condition}");
                pdo_query('delete from   ' . tablename('sz_yi_member_history') . " where 1 {$condition}");
                pdo_query('delete from   ' . tablename('sz_yi_member_favorite') . " where 1 {$condition}");
                pdo_query('delete from   ' . tablename('sz_yi_member_log') . " where 1 {$condition}");
            } else if ($data == 'order') {
                pdo_query('delete from  ' . tablename('sz_yi_order') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_order_goods') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_order_refund') . " where 1 {$condition}");
                if (table_exists('sz_yi_order_comment')) {
                    pdo_query('delete from  ' . tablename('sz_yi_order_comment') . " where 1 {$condition}");
                }
            } else if ($data == 'memberlevel') {
                pdo_query('update ' . tablename('sz_yi_member') . " set level=0 where 1 {$condition}");
            } else if ($data == 'membergroup') {
                pdo_query('update ' . tablename('sz_yi_member') . " set groupid=0 where 1 {$condition}");
            } else if ($data == 'membercredit1') {
                if ($wechatid != -1) {
                    $members = pdo_fetchall('select id,uid,openid,uniacid from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid ', array(
                        ':uniacid' => $wechatid
                    ));
                } else {
                    $members = pdo_fetchall('select id,uid,openid,uniacid from ' . tablename('sz_yi_member'));
                }
                foreach ($members as $m) {
                    $mm = m('member')->getMember($m['openid']);
                    if (empty($mm['uid'])) {
                        pdo_update('sz_yi_member', array(
                            'credit1' => 0
                        ), array(
                            'id' => $mm['id']
                        ));
                    } else {
                        pdo_update('mc_members', array(
                            'credit1' => 0
                        ), array(
                            'uid' => $mm['uid']
                        ));
                    }
                }
            } else if ($data == 'membercredit2') {
                if ($wechatid != -1) {
                    $members = pdo_fetchall('select id,uid,openid,uniacid from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid ', array(
                        ':uniacid' => $wechatid
                    ));
                } else {
                    $members = pdo_fetchall('select id,uid,openid,uniacid from ' . tablename('sz_yi_member'));
                }
                foreach ($members as $m) {
                    $mm = m('member')->getMember($m['openid']);
                    if (empty($mm['uid'])) {
                        pdo_update('sz_yi_member', array(
                            'credit2' => 0
                        ), array(
                            'id' => $mm['id']
                        ));
                    } else {
                        pdo_update('mc_members', array(
                            'credit2' => 0
                        ), array(
                            'uid' => $mm['uid']
                        ));
                    }
                }
            }
        }
    }
    if (is_array($_GPC['commission'])) {
        foreach ($_GPC['commission'] as $data) {
            if ($data == 'agent') {
                pdo_query('update ' . tablename('sz_yi_member') . " set isagent=0,status=0,agenttime=0 where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_commission_shop') . " where 1 {$condition}");
            } else if ($data == 'relation') {
                pdo_query('update ' . tablename('sz_yi_member') . " set agentid=0 where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_commission_clickcount') . " where 1 {$condition}");
            } else if ($data == 'dispatch') {
                pdo_query('delete from  ' . tablename('sz_yi_dispatch') . " where 1 {$condition}");
            } else if ($data == 'agentlevel') {
                pdo_query('update ' . tablename('sz_yi_member') . " set agentlevel=0 where 1 {$condition}");
            } else if ($data == 'level') {
                pdo_query('delete from  ' . tablename('sz_yi_commission_level') . " where 1 {$condition}");
            } else if ($data == 'apply') {
                pdo_query('delete from  ' . tablename('sz_yi_commission_apply') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_commission_log') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['poster'])) {
        foreach ($_GPC['poster'] as $data) {
            if ($data == 'cache' || $data == 'poster') {
                if ($wechatid == -1) {
                    @rmdirs(IA_ROOT . "/addons/sz_yi/data/poster");
                    @rmdirs(IA_ROOT . '/addons/sz_yi/data/qrcode');
                    pdo_update('sz_yi_poster_qr', array(
                        'mediaid' => ''
                    ));
                } else {
                    @rmdirs(IA_ROOT . '/addons/sz_yi/data/poster/' . $wechatid);
                    @rmdirs(IA_ROOT . '/addons/sz_yi/data/qrcode/' . $wechatid);
                    pdo_update('sz_yi_poster_qr', array(
                        'mediaid' => ''
                    ), array(
                        'acid' => $acid
                    ));
                }
            } else if ($data == 'poster') {
                pdo_query('delete from  ' . tablename('sz_yi_poster') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_poster_qr') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_poster_log') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_poster_scan') . " where 1 {$condition}");
            } else if ($data == 'log') {
                pdo_query('delete from  ' . tablename('sz_yi_poster_log') . " where 1 {$condition}");
            } else if ($data == 'scan') {
                pdo_query('delete from  ' . tablename('sz_yi_poster_scan') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['verify'])) {
        foreach ($_GPC['verify'] as $data) {
            if ($data == 'store') {
                pdo_query('delete from  ' . tablename('sz_yi_store') . " where 1 {$condition}");
            } else if ($data == 'saler') {
                pdo_query('delete from  ' . tablename('sz_yi_saler') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['perm'])) {
        foreach ($_GPC['perm'] as $data) {
            if ($data == 'role') {
                pdo_query('delete from  ' . tablename('sz_yi_perm_role') . " where 1 {$condition}");
            } else if ($data == 'user') {
                pdo_query('delete from  ' . tablename('sz_yi_perm_user') . " where 1 {$condition}");
            } else if ($data == 'log') {
                pdo_query('delete from  ' . tablename('sz_yi_perm_log') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['creditshop'])) {
        foreach ($_GPC['creditshop'] as $data) {
            if ($data == 'goods') {
                pdo_query('delete from  ' . tablename('sz_yi_creditshop_goods') . " where 1 {$condition}");
            } else if ($data == 'category') {
                pdo_query('delete from  ' . tablename('sz_yi_creditshop_category') . " where 1 {$condition}");
            } else if ($data == 'adv') {
                pdo_query('delete from  ' . tablename('sz_yi_creditshop_adv') . " where 1 {$condition}");
            } else if ($data == 'log') {
                pdo_query('delete from  ' . tablename('sz_yi_creditshop_log') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['virtual'])) {
        foreach ($_GPC['virtual'] as $data) {
            if ($data == 'template') {
                pdo_query('delete from  ' . tablename('sz_yi_virtual_type') . " where 1 {$condition}");
            } else if ($data == 'category') {
                pdo_query('delete from  ' . tablename('sz_yi_virtual_category') . " where 1 {$condition}");
            } else if ($data == 'data') {
                pdo_query('delete from  ' . tablename('sz_yi_virtual_data') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['designer'])) {
        foreach ($_GPC['designer'] as $data) {
            if ($data == 'page') {
                pdo_query('delete from  ' . tablename('sz_yi_designer') . " where 1 {$condition}");
            } else if ($data == 'menu') {
                pdo_query('delete from  ' . tablename('sz_yi_designer_menu') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['article'])) {
        foreach ($_GPC['article'] as $data) {
            if ($data == 'article') {
                $articles = pdo_fetchall('select * from ' . tablename('sz_yi_article') . " where uniacid=:uniacid and id not in (" . implode(',', $catearticles) . ")", array(
                    ':uniacid' => $wechatid
                ));
                foreach ($articles as $article) {
                    $keyword = pdo_fetch("SELECT * FROM " . tablename('rule_keyword') . " WHERE content=:content and module=:module and uniacid=:uniacid limit 1 ", array(
                        ':content' => $article['article_keyword'],
                        ':module' => 'sz_yi',
                        ':uniacid' => $wechatid
                    ));
                    if (!empty($keyword)) {
                        pdo_delete('rule_keyword', array(
                            'id' => $keyword['id']
                        ));
                        pdo_delete('rule', array(
                            'id' => $keyword['rid']
                        ));
                    }
                }
                pdo_query('delete from  ' . tablename('sz_yi_article') . " where 1 {$condition}");
            } else if ($data == 'category') {
                pdo_query('delete from  ' . tablename('sz_yi_article_category') . " where 1 {$condition}");
            } else if ($data == 'share') {
                pdo_query('delete from  ' . tablename('sz_yi_article_share') . " where 1 {$condition}");
            } else if ($data == 'log') {
                pdo_query('update ' . tablename('sz_yi_article') . " set article_readnum=0,article_likenum=0 where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_article_log') . " where 1 {$condition}");
            } else if ($data == 'report') {
                pdo_query('delete from  ' . tablename('sz_yi_article_report') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['coupon'])) {
        foreach ($_GPC['coupon'] as $data) {
            if ($data == 'coupon') {
                pdo_query('delete from  ' . tablename('sz_yi_coupon') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_coupon_data') . " where 1 {$condition}");
            } else if ($data == 'category') {
                pdo_query('delete from  ' . tablename('sz_yi_coupon_category') . " where 1 {$condition}");
            } else if ($data == 'data') {
                pdo_query('delete from  ' . tablename('sz_yi_coupon_data') . " where 1 {$condition}");
            }
        }
    }
    if (is_array($_GPC['postera'])) {
        foreach ($_GPC['postera'] as $data) {
            if ($data == 'cache' || $data == 'poster') {
                if ($wechatid == -1) {
                    @rmdirs(IA_ROOT . "/addons/sz_yi/data/postera");
                    @rmdirs(IA_ROOT . '/addons/sz_yi/data/qrcode');
                    pdo_update('sz_yi_postera_qr', array(
                        'mediaid' => ''
                    ));
                } else {
                    @rmdirs(IA_ROOT . '/addons/sz_yi/data/postera/' . $wechatid);
                    @rmdirs(IA_ROOT . '/addons/sz_yi/data/qrcode/' . $wechatid);
                    pdo_update('sz_yi_postera_qr', array(
                        'mediaid' => ''
                    ), array(
                        'acid' => $acid
                    ));
                }
            } else if ($data == 'poster') {
                pdo_query('delete from  ' . tablename('sz_yi_postera') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_postera_qr') . " where 1 {$condition}");
                pdo_query('delete from  ' . tablename('sz_yi_postera_log') . " where 1 {$condition}");
            } else if ($data == 'log') {
                pdo_query('delete from  ' . tablename('sz_yi_poster_log') . " where 1 {$condition}");
            }
        }
    }
    message('数据清理成功!', $this->createPluginWebUrl('system/clear'), 'success');
}
$wechats = $this->model->get_wechats();
load()->func('tpl');
include $this->template('clear');
