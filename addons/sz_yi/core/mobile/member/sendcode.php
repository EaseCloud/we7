<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
global $_W, $_GPC;
$mc = $_GPC['memberdata'];
$op = empty($_GPC['op']) ? 'sendcode' : trim($_GPC['op']);
session_start();
if ($op == 'sendcode') {
    $mobile = $_GPC['mobile'];
    if (empty($mobile)) {
        show_json(0, '请填入手机号');
    }
    $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
    if (!empty($info)) {
        show_json(0, '该手机号已被注册！不能获取验证码。');
    }
    $code = rand(1000, 9999);
    $_SESSION['codetime'] = time();
    $_SESSION['code'] = $code;
    $_SESSION['code_mobile'] = $mobile;
    $issendsms = $this->sendSms($mobile, $code);
    $set = m('common')->getSysset();
    if ($set['sms']['type'] == 1) {
        if ($issendsms['SubmitResult']['code'] == 2) {
            show_json(1);
        } else {
            show_json(0, $issendsms['SubmitResult']['msg']);
        }
    } else {
        if (isset($issendsms['result']['success'])) {
            show_json(1);
        } else {
            show_json(0, $issendsms['msg']);
        }
    }
} else {
    if ($op == 'forgetcode') {
        $mobile = $_GPC['mobile'];
        if (empty($mobile)) {
            show_json(0, '请填入手机号');
        }
        $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
        if (empty($info)) {
            show_json(0, '该手机号未注册！不能找回密码。');
        }
        $code = rand(1000, 9999);
        $_SESSION['codetime'] = time();
        $_SESSION['code'] = $code;
        $_SESSION['code_mobile'] = $mobile;
        $issendsms = $this->sendSms($mobile, $code, 'forget');
        $set = m('common')->getSysset();
        if ($set['sms']['type'] == 1) {
            if ($issendsms['SubmitResult']['code'] == 2) {
                show_json(1);
            } else {
                show_json(0, $issendsms['SubmitResult']['msg']);
            }
        } else {
            if (isset($issendsms['result']['success'])) {
                show_json(1);
            } else {
                show_json(0, $issendsms['msg']);
            }
        }
    } else {
        if ($op == 'bindmobilecode') {
            $mobile = $_GPC['mobile'];
            if (empty($mobile)) {
                show_json(0, '请填入手机号');
            }
            $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid and isbindmobile=1 limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
            if (!empty($info)) {
                show_json(0, '该手机号已绑定过');
            }
            $code = rand(1000, 9999);
            $_SESSION['codetime'] = time();
            $_SESSION['code'] = $code;
            $_SESSION['code_mobile'] = $mobile;
            $issendsms = $this->sendSms($mobile, $code);
            $set = m('common')->getSysset();
            if ($set['sms']['type'] == 1) {
                if ($issendsms['SubmitResult']['code'] == 2) {
                    show_json(1);
                } else {
                    show_json(0, $issendsms['SubmitResult']['msg']);
                }
            } else {
                if (isset($issendsms['result']['success'])) {
                    show_json(1);
                } else {
                    show_json(0, $issendsms['msg']);
                }
            }
        } else {
            if ($op == 'checkcode') {
                $code = $_GPC['code'];
                if ($_SESSION['codetime'] + 60 * 5 < time()) {
                    show_json(0, '验证码已过期,请重新获取');
                }
                if ($_SESSION['code'] != $code) {
                    show_json(0, '验证码错误,请重新获取');
                }
                show_json(1);
            } else {
                if ($op == 'ismobile') {
                    $mobile = $_GPC['mobile'];
                    if (empty($mobile)) {
                        show_json(0, '请填入手机号');
                    }
                    $info = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
                    if (!empty($info)) {
                        show_json(0, '该手机号已被注册！');
                    } else {
                        show_json(1);
                    }
                }
            }
        }
    }
}