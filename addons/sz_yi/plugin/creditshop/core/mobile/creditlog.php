<?php
//芸众商城 QQ:913768135
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
$credit    = intval(m('member')->getCredit($openid, 'credit1'));
if ($_W['isajax']) {
    if ($operation == 'display') {
        $pindex    = max(1, intval($_GPC['page']));
        $psize     = 10;
        $condition = ' and log.openid=:openid and log.uniacid = :uniacid';
        $params    = array(
            ':uniacid' => $_W['uniacid'],
            ':openid' => $openid
        );
        $sql       = 'SELECT COUNT(*) FROM ' . tablename('sz_yi_creditshop_log') . " log where 1 {$condition}";
        $total     = pdo_fetchcolumn($sql, $params);
        $list      = array();
        if (!empty($total)) {
            $sql  = 'SELECT log.id,log.goodsid,g.title,g.thumb,g.credit,g.type,g.money,log.createtime FROM ' . tablename('sz_yi_creditshop_log') . ' log ' . ' left join ' . tablename('sz_yi_creditshop_goods') . ' g on log.goodsid = g.id ' . ' where 1 ' . $condition . ' ORDER BY log.createtime DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $params);
            $list = set_medias($list, 'thumb');
            foreach ($list as &$row) {
                if ($row['credit'] > 0 & $row['money'] > 0) {
                    $row['acttype'] = 0;
                } else if ($row['credit'] > 0) {
                    $row['acttype'] = 1;
                } else if ($goods['money'] > 0) {
                    $row['acttype'] = 2;
                }
                $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
            }
            unset($row);
        }
        show_json(1, array(
            'total' => $total,
            'list' => $list,
            'pagesize' => $psize
        ));
    }
}
$_W['shopshare'] = array(
    'title' => $this->set['share_title'],
    'imgUrl' => tomedia($this->set['share_icon']),
    'link' => $this->createPluginMobileUrl('creditshop'),
    'desc' => $this->set['share_desc']
);
$com             = p('commission');
if ($com) {
    $cset = $com->getSet();
    if (!empty($cset)) {
        if ($member['isagent'] == 1 && $member['status'] == 1) {
            $_W['shopshare']['link'] = $this->createPluginMobileUrl('creditshop', array(
                'mid' => $member['id']
            ));
            if (empty($cset['become_reg']) && (empty($member['realname']) || empty($member['mobile']))) {
                $trigger = true;
            }
        } else if (!empty($_GPC['mid'])) {
            $_W['shopshare']['link'] = $this->createPluginMobileUrl('creditshop/detail', array(
                'mid' => $_GPC['mid']
            ));
        }
    }
}
include $this->template('creditlog');
