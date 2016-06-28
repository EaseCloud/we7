<?php
global $_W, $_GPC;
$goods   = array();
$openid  = trim($_GPC['openid']);
$content = trim(urldecode($_GPC['content']));
if (empty($openid)) {
    exit;
}
$member = m('member')->getMember($openid);
if (empty($member)) {
    exit;
}
if (strexists($content, '+')) {
    $msg    = explode('+', $content);
    $poster = pdo_fetch('select * from ' . tablename('sz_yi_poster') . ' where keyword=:keyword and type=3 and isdefault=1 and uniacid=:uniacid limit 1', array(
        ':keyword' => $msg[0],
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($poster)) {
        m('message')->sendCustomNotice($openid, '未找到商品海报类型!');
        exit;
    }
    $goodsid = intval($msg[1]);
    if (empty($goodsid)) {
        m('message')->sendCustomNotice($openid, '未找到商品, 无法生成海报 !');
        exit;
    }
} else {
    $poster = pdo_fetch('select * from ' . tablename('sz_yi_poster') . ' where keyword=:keyword and isdefault=1 and uniacid=:uniacid limit 1', array(
        ':keyword' => $content,
        ':uniacid' => $_W['uniacid']
    ));
    if (empty($poster)) {
        m('message')->sendCustomNotice($openid, '未找到海报类型!');
        exit;
    }
}
if ($member['isagent'] != 1 || $member['status'] != 1) {
    if (empty($poster['isopen'])) {
        $opentext = !empty($poster['opentext']) ? $poster['opentext'] : '您还不是我们分销商，去努力成为分销商，拥有你的专属海报吧!';
        m('message')->sendCustomNotice($openid, $opentext, trim($poster['openurl']));
        exit;
    }
}
$waittext = !empty($poster['waittext']) ? $poster['waittext'] : '您的专属海报正在拼命生成中，请等待片刻...';
m('message')->sendCustomNotice($openid, $waittext);
$qr = $this->model->getQR($poster, $member, $goodsid);
if (is_error($qr)) {
    m('message')->sendCustomNotice($openid, '生成二维码出错: ' . $qr['message']);
    exit;
}
$img     = $this->model->createPoster($poster, $member, $qr);
$mediaid = $img['mediaid'];
if (!empty($mediaid)) {
	m('message')->sendImage($openid, $mediaid);
} else {
	$oktext = '<a href=\'' . $img['img'] . '\'>点击查看您的专属海报</a>';
	m('message')->sendCustomNotice($openid, $oktext);
}
exit;
