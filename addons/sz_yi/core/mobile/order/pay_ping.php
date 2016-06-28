<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
$uniacid = $_W['uniacid'];
require_once '../addons/sz_yi/plugin/pingpp/init.php';
$input_data = array('channel' => $_POST['channel'], 'amount' => $_POST['amount'], 'order_no' => $_POST['ordersn'], 'openid' => $_POST['token']);
if (empty($input_data['channel'])) {
    echo 'channel is empty';
    die;
}
$channel = strtolower($input_data['channel']);
$api_key = 'sk_live_DW1Wr5TO0e940ufDqH4S08K0';
$orderNo = $input_data['order_no'];
$order_info = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where ordersn=:ordersn and uniacid=:uniacid and openid=:openid limit 1', array('ordersn' => $orderNo, ':uniacid' => $uniacid, ':openid' => $input_data['openid']));
$amount = (int)($order_info['price'] * 100);
$subject = '商品订单';
$body = '商品订单';
$app_id = 'app_unrfnH1qH8KOf14K';
$extra = array();
switch ($channel) {
    case 'alipay_wap':
        $extra = array('success_url' => 'http://www.yourdomain.com/success', 'cancel_url' => 'http://www.yourdomain.com/cancel');
        break;
    case 'upmp_wap':
        $extra = array('result_url' => 'http://www.yourdomain.com/result?code=');
        break;
    case 'bfb_wap':
        $extra = array('result_url' => 'http://www.yourdomain.com/result?code=', 'bfb_login' => true);
        break;
    case 'upacp_wap':
        $extra = array('result_url' => 'http://www.yourdomain.com/result');
        break;
    case 'wx_pub':
        $extra = array('open_id' => 'Openid');
        break;
    case 'wx_pub_qr':
        $extra = array('product_id' => 'Productid');
        break;
    case 'yeepay_wap':
        $extra = array('product_category' => '1', 'identity_id' => 'your identity_id', 'identity_type' => 1, 'terminal_type' => 1, 'terminal_id' => 'your terminal_id', 'user_ua' => 'your user_ua', 'result_url' => 'http://www.yourdomain.com/result');
        break;
    case 'jdpay_wap':
        $extra = array('success_url' => 'http://www.yourdomain.com', 'fail_url' => 'http://www.yourdomain.com', 'token' => 'dsafadsfasdfadsjuyhfnhujkijunhaf');
        break;
}
\Pingpp\Pingpp::setApiKey($api_key);
try {
    $ch = \Pingpp\Charge::create(array('subject' => $subject, 'body' => $body, 'amount' => $amount, 'order_no' => $orderNo, 'currency' => 'cny', 'extra' => $extra, 'channel' => $channel, 'client_ip' => $_SERVER['REMOTE_ADDR'], 'app' => array('id' => $app_id)));
    echo $ch;
} catch (\Pingpp\Error\Base $e) {
    if ($e->getHttpStatus() != NULL) {
        header('Status: ' . $e->getHttpStatus());
        echo $e->getHttpBody();
    } else {
        echo $e->getMessage();
    }
}