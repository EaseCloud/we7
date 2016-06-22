<?php
//芸众商城 QQ:913768135
global $_GPC;
set_time_limit(0);
$ret   = array();
$url   = $_GPC['url'];
$pcate = intval($_GPC['pcate']);
$ccate = intval($_GPC['ccate']);
$tcate = intval($_GPC['tcate']);
if (is_numeric($url)) {
    $itemid = $url;
} else {
    preg_match('/id\=(\d+)/i', $url, $matches);
    if (isset($matches[1])) {
        $itemid = $matches[1];
    }
}
if (empty($itemid)) {
    die(json_encode(array(
        "result" => 0,
        "error" => "未获取到 itemid!"
    )));
}
$ret = $this->model->get_item_taobao($itemid, $_GPC['url'], $pcate, $ccate, $tcate);
plog('taobao.fetch', '淘宝抓取宝贝 淘宝id:' . $itemid);
die(json_encode($ret));