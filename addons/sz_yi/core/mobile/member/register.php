<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$preUrl = $_COOKIE['preUrl'];
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
session_start();
if ($_W['isajax']) {
    if ($_W['ispost']) {
        $mobile = !empty($_GPC['mobile']) ? $_GPC['mobile'] : show_json(0, '手机号不能为空！');
        $password = !empty($_GPC['password']) ? $_GPC['password'] : show_json(0, '密码不能为空！');
        $code = !empty($_GPC['code']) ? $_GPC['code'] : show_json(0, '验证码不能为空！');
        if(($_SESSION['codetime']+60*5) < time()){
            show_json(0, '验证码已过期,请重新获取');
        }
        if($_SESSION['code'] != $code){
            show_json(0, '验证码错误,请重新获取');
        }
        if($_SESSION['code_mobile'] != $mobile){
            show_json(0, '注册手机号与验证码不匹配！');
        }
        $member = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(
                        ':uniacid' => $_W['uniacid'],
                        ':mobile' => $mobile
                    ));
        
        if(!empty($member)){
            show_json(0, '该手机号已被注册！');
        }
        $openid = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where mobile=:mobile and uniacid=:uniacid limit 1', array(
                        ':uniacid' => $_W['uniacid'],
                        ':mobile' => $mobile
                    ));
        if(empty($openid)){
            $member_data = array(
                'uniacid' => $_W['uniacid'],
                'uid' => 0,
                'openid' => 'u'.md5($mobile),
                'mobile' => $mobile,
                'pwd' => md5($password),   //md5
                'createtime' => time(),
                'status' => 0,
                'regtype' => 2,
            );
            pdo_insert('sz_yi_member', $member_data);
            $openid = $member_data['openid'];
        }else{
            $member_data = array(
                'pwd' => md5($password),   //md5
                'regtype' => 1,
                'isbindmobile' => 1
            );
            pdo_update('sz_yi_member', $member_data, array("mobile" => $mobile, "uniacid" => $_W['uniacid']));
        }

        $lifeTime = 24 * 3600 * 3;
        session_set_cookie_params($lifeTime);
        @session_start();
        $cookieid = "__cookie_sz_yi_userid_{$_W['uniacid']}";
        setcookie($cookieid, base64_encode($openid));

        show_json(1, $preUrl);
    }      
}
include $this->template('member/register');