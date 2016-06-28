<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'category') {
	$category = m('shop')->getCategory();
	show_json(1, array('category' => $category));
} else if ($operation == 'areas') {
	$areas = m('cache')->getArray('areas', 'global');
	if (!is_array($areas)) {
		require_once SZ_YI_INC . 'json/xml2json.php';
		$file = IA_ROOT . "/addons/sz_yi/static/js/dist/area/Area.xml";
		$content = file_get_contents($file);
		$json = xml2json::transformXmlStringToJson($content);
		$areas = json_decode($json, true);
		m('cache')->set('areas', $areas, 'global');
	}
	die(json_encode($areas));
} else if ($operation == 'search') {
	$keywords = trim($_GPC['keywords']);
	$goods = m('goods')->getList(array('pagesize' => 100000, 'keywords' => trim($_GPC['keywords'])));
	show_json(1, array('list' => $goods));
} else if ($operation == 'comment') {
	$goodsid = intval($_GPC['goodsid']);
	$pindex = max(1, intval($_GPC['page']));
	$psize = 5;
	$condition = ' and uniacid = :uniacid and goodsid=:goodsid and deleted=0';
	$params = array(':uniacid' => $_W['uniacid'], ':goodsid' => $goodsid);
	$sql = 'SELECT id,nickname,headimgurl,level,content,createtime, images,append_images,append_content,reply_images,reply_content,append_reply_images,append_reply_content ' . ' FROM ' . tablename('sz_yi_order_comment') . ' where 1 ' . $condition . ' ORDER BY `id` DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
	$list = pdo_fetchall($sql, $params);
	foreach ($list as &$row) {
		$row['headimgurl'] = tomedia($row['headimgurl']);
		$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
		$images = unserialize($row['images']);
		$row['images'] = is_array($images) ? set_medias($images) : array();
		$append_images = unserialize($row['append_images']);
		$row['append_images'] = is_array($append_images) ? set_medias($append_images) : array();
		$reply_images = unserialize($row['reply_images']);
		$row['reply_images'] = is_array($reply_images) ? set_medias($reply_images) : array();
		$append_reply_images = unserialize($row['append_reply_images']);
		$row['append_reply_images'] = is_array($append_reply_images) ? set_medias($append_reply_images) : array();
	}
	unset($row);
	show_json(1, array('list' => $list, 'pagesize' => $psize));
} else if ($operation == 'recommand') {
	$goods = m('goods')->getList(array('pagesize' => 4, 'isrecommand' => true, 'random' => true));
	show_json(1, array('list' => $goods));
}
