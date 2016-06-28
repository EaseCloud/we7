<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;

$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'import';
ca('virtual.data.export');
$typeid = intval($_GPC['typeid']);
$type   = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_virtual_type') . ' WHERE id=:id and uniacid=:uniacid limit 1 ', array(
    ':id' => $typeid,
    ':uniacid' => $_W['uniacid']
));
if (empty($type)) {
    message('未找到虚拟物品模板!', '', 'error');
}
$type['fields'] = iunserializer($type['fields']);
$fieldstr       = "";
foreach ($type['fields'] as $key => $name) {
    $fieldstr .= $name . "(" . $key . ")/";
}
$condition = " and d.typeid=:typeid and d.uniacid=:uniacid and d.openid<>''";
$params    = array(
    ':typeid' => $typeid,
    ':uniacid' => $_W['uniacid']
);
$list      = pdo_fetchall('SELECT d.*,o.carrier,m.avatar,m.nickname FROM ' . tablename('sz_yi_virtual_data') . " d " . " left join " . tablename('sz_yi_member') . ' m on m.openid = d.openid and m.uniacid = d.uniacid ' . " left join " . tablename('sz_yi_order') . ' o on o.id = d.orderid ' . " where  1 {$condition} order by usetime desc", $params);
if (empty($list)) {
    message('没有已使用的数据!', '', 'info');
}
foreach ($list as &$row) {
    $datas    = iunserializer($row['fields']);
    $valuestr = "";
    foreach ($type['fields'] as $k => $v) {
        $valuestr .= $datas[$k] . "/";
    }
    $row['values'] = $valuestr;
    $carrier       = iunserializer($row['carrier']);
    if (is_array($carrier)) {
        $row['realname'] = $carrier['carrier_realname'];
        $row['mobile']   = $carrier['carrier_mobile'];
    }
    $row['usetime'] = date('Y-m-d H:i', $row['usetime']);
}
unset($row);
$columns = array(
    array(
        'title' => $fieldstr,
        'field' => 'values',
        'width' => 24
    ),
    array(
        'title' => '粉丝昵称',
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
        'title' => '使用时间',
        'field' => 'usetime',
        'width' => 12
    ),
    array(
        'title' => '订单号',
        'field' => 'ordersn',
        'width' => 24
    ),
    array(
        'title' => '购买价格',
        'field' => 'price',
        'width' => 12
    )
);
m('excel')->export($list, array(
    "title" => $type['title'] . "已使用数据",
    "columns" => $columns
));
exit;
