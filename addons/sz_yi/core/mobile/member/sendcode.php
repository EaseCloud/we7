<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

$mc = $_GPC['memberdata'];  //'18646588292';
$op      = empty($_GPC['op']) ? 'sendcode' : trim($_GPC['op']);

session_start();
if($op == 'sendcode'){
    $mobile = $_GPC['mobile'];
    if(empty($mobile)){
        show_json(0, '请填入手机号');
    }
    $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':mobile' => $mobile
            ));
    if(!empty($info))
    {
        show_json(0, '该手机号已被注册！不能获取验证码。');
    } 
    $code = rand(1000, 9999);
    $_SESSION['codetime'] = time();
    $_SESSION['code'] = $code;
    $_SESSION['code_mobile'] = $mobile;
    $content = "您的安全码是：". $code ."。请不要把安全码泄露给其他人。如非本人操作，可不用理会！";
    $issendsms = $this->sendSms($mobile, $content);
    show_json(1);
}else if ($op == 'forgetcode'){
    $mobile = $_GPC['mobile'];
    if(empty($mobile)){
        show_json(0, '请填入手机号');
    }
    $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':mobile' => $mobile
            ));
    //print_r($info);
    if(empty($info))
    {
        show_json(0, '该手机号未注册！不能找回密码。');
    } 
    $code = rand(1000, 9999);
    $_SESSION['codetime'] = time();
    $_SESSION['code'] = $code;
    $_SESSION['code_mobile'] = $mobile;
    $content = "您的安全码是：". $code ."。请不要把安全码泄露给其他人。如非本人操作，可不用理会！";
    $issendsms = $this->sendSms($mobile, $content);
    show_json(1);
}else if ($op == 'bindmobilecode'){
    $mobile = $_GPC['mobile'];
    if(empty($mobile)){
        show_json(0, '请填入手机号');
    }
    $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':mobile' => $mobile
            ));
    //print_r($info);
    
    $code = rand(1000, 9999);
    $_SESSION['codetime'] = time();
    $_SESSION['code'] = $code;
    $_SESSION['code_mobile'] = $mobile;
    $content = "您的安全码是：". $code ."。请不要把安全码泄露给其他人。如非本人操作，可不用理会！";
    $issendsms = $this->sendSms($mobile, $content);
    show_json(1);
}else if ($op == 'checkcode'){
    $code = $_GPC['code']; 

    if(($_SESSION['codetime']+60*5) < time()){
        show_json(0, '验证码已过期,请重新获取');
    }
    if($_SESSION['code'] != $code){
        show_json(0, '验证码错误,请重新获取');
    }
    show_json(1);  
}
else if ($op == 'ismobile'){
    $mobile = $_GPC['mobile'];
    if(empty($mobile)){
        show_json(0, '请填入手机号');
    }
    $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(
                ':uniacid' => $_W['uniacid'],
                ':mobile' => $mobile
            ));
    if(!empty($info))
    {
        show_json(0, '该手机号已被注册！');
    }else{
        show_json(1); 
    }    
}
