<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

function upload_cert($fileinput)
{
    global $_W;
    $path = IA_ROOT . "/addons/sz_yi/cert";
    load()->func('file');
    mkdirs($path, '0777');
    $f           = $fileinput . '_' . $_W['uniacid'] . '.pem';
    $outfilename = $path . "/" . $f;
    $filename    = $_FILES[$fileinput]['name'];
    $tmp_name    = $_FILES[$fileinput]['tmp_name'];
    if (!empty($filename) && !empty($tmp_name)) {
        $ext = strtolower(substr($filename, strrpos($filename, '.')));
        if ($ext != '.pem') {
            $errinput = "";
            if ($fileinput == 'weixin_cert_file') {
                $errinput = "CERT文件格式错误";
            } else if ($fileinput == 'weixin_key_file') {
                $errinput = 'KEY文件格式错误';
            } else if ($fileinput == 'weixin_root_file') {
                $errinput = 'ROOT文件格式错误';
            }
            message($errinput . ',请重新上传!', '', 'error');
        }
        return file_get_contents($tmp_name);
    }
    return "";
}
$op      = empty($_GPC['op']) ? 'shop' : trim($_GPC['op']);
if ($op == 'datamove') {
    $up = m('common')->dataMove();
    exit('迁移成功');
}
$setdata = pdo_fetch("select * from " . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(
    ':uniacid' => $_W['uniacid']
));
$set     = unserialize($setdata['sets']);
$oldset  = unserialize($setdata['sets']);

if ($op == 'template') {
    $styles = array();
    $dir    = IA_ROOT . "/addons/sz_yi/template/mobile/";
    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != ".." && $file != ".") {
                if (is_dir($dir . "/" . $file)) {
                    $styles[] = $file;
                }
            }
        }
        closedir($handle);
    }
} else if ($op == 'notice') {
    $salers = array();
    if (isset($set['notice']['openid'])) {
        if (!empty($set['notice']['openid'])) {
            $openids     = array();
            $strsopenids = explode(",", $set['notice']['openid']);
            foreach ($strsopenids as $openid) {
                $openids[] = "'" . $openid . "'";
            }
            $salers = pdo_fetchall("select id,nickname,avatar,openid from " . tablename('sz_yi_member') . ' where openid in (' . implode(",", $openids) . ") and uniacid={$_W['uniacid']}");
        }
    }
    $newtype = explode(',', $set['notice']['newtype']);
} else if ($op == 'pay') {
    $sec = m('common')->getSec();
    $sec = iunserializer($sec['sec']);
}
if (checksubmit()) {
    if ($op == 'shop') {
        $shop                   = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
        $set['shop']['name']    = trim($shop['name']);
        $set['shop']['cservice']= trim($shop['cservice']);
        $set['shop']['img']     = save_media($shop['img']);
        $set['shop']['logo']    = save_media($shop['logo']);
        $set['shop']['signimg'] = save_media($shop['signimg']);
        $set['shop']['diycode'] = trim($shop['diycode']);
        plog('sysset.save.shop', '修改系统设置-商城设置');
    }
    elseif ($op == 'sms') {
        $sms                    = is_array($_GPC['sms']) ? $_GPC['sms'] : array();
        $set['sms']['account']  = $sms['account'];
        $set['sms']['password'] = $sms['password'];
        //print_r($set);exit;
        plog('sysset.save.sms', '修改系统设置-短信设置');
    } elseif ($op == 'follow') {
        $set['share']         = is_array($_GPC['share']) ? $_GPC['share'] : array();
        $set['share']['icon'] = save_media($set['share']['icon']);
        plog('sysset.save.follow', '修改系统设置-分享及关注设置');
    } else if ($op == 'notice') {
        $set['notice'] = is_array($_GPC['notice']) ? $_GPC['notice'] : array();
        if (is_array($_GPC['openids'])) {
            $set['notice']['openid'] = implode(",", $_GPC['openids']);
        }
        $set['notice']['newtype'] = $_GPC['notice']['newtype'];
        if (is_array($set['notice']['newtype'])) {
            $set['notice']['newtype'] = implode(",", $set['notice']['newtype']);
        }
        plog('sysset.save.notice', '修改系统设置-模板消息通知设置');
    } elseif ($op == 'trade') {
        $set['trade'] = is_array($_GPC['trade']) ? $_GPC['trade'] : array();
        if (!$_W['isfounder']) {
            unset($set['trade']['receivetime']);
            unset($set['trade']['closordertime']);
            unset($set['trade']['paylog']);
        } else {
            m('cache')->set('receive_time', $set['trade']['receivetime'], 'global');
            m('cache')->set('closeorder_time', $set['trade']['closordertime'], 'global');
            m('cache')->set('paylog', $set['trade']['paylog'], 'global');
        }
        plog('sysset.save.trade', '修改系统设置-交易设置');
    } elseif ($op == 'pay') {
	
        $pluginy = p('yunpay');
        if($pluginy){
            $pay = $set['pay']['yunpay'];
        }
        $set['pay'] = is_array($_GPC['pay']) ? $_GPC['pay'] : array();
        if($pluginy){
            $set['pay']['yunpay'] = $pay;
        }
		
        if ($_FILES['weixin_cert_file']['name']) {
            $sec['cert'] = upload_cert('weixin_cert_file');
        }
        if ($_FILES['weixin_key_file']['name']) {
            $sec['key'] = upload_cert('weixin_key_file');
        }
        if ($_FILES['weixin_root_file']['name']) {
            $sec['root'] = upload_cert('weixin_root_file');
        }
        if (empty($sec['cert']) || empty($sec['key']) || empty($sec['root'])) {
        }

        pdo_update('sz_yi_sysset', array(
            'sec' => iserializer($sec)
        ), array(
            'uniacid' => $_W['uniacid']
        ));
        plog('sysset.save.pay', '修改系统设置-支付设置');
    } elseif ($op == 'template') {
        $shop                 = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
        $set['shop']['style'] = save_media($shop['style']);
        m('cache')->set('template_shop', $set['shop']['style']);
        plog('sysset.save.pay', '修改系统设置-模板设置');
    } elseif ($op == 'member') {
        $shop                     = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
        $set['shop']['levelname'] = trim($shop['levelname']);
        $set['shop']['levelurl']  = trim($shop['levelurl']);
        plog('sysset.save.pay', '修改系统设置-会员设置');
    } elseif ($op == 'category') {
        $shop                     = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
        $set['shop']['catlevel']  = trim($shop['catlevel']);
        $set['shop']['catshow']   = intval($shop['catshow']);
        $set['shop']['catadvimg'] = save_media($shop['catadvimg']);
        $set['shop']['catadvurl'] = trim($shop['catadvurl']);
        plog('sysset.save.pay', '修改系统设置-分类层级设置');
    } elseif ($op == 'contact') {
        $shop                       = is_array($_GPC['shop']) ? $_GPC['shop'] : array();
        $set['shop']['qq']          = trim($shop['qq']);
        $set['shop']['address']     = trim($shop['address']);
        $set['shop']['phone']       = trim($shop['phone']);
        $set['shop']['description'] = trim($shop['description']);
        plog('sysset.save.pay', '修改系统设置-联系方式设置');
    }
    $data = array(
        'uniacid' => $_W['uniacid'],
        'sets' => iserializer($set)
    );
    if (empty($setdata)) {
        pdo_insert('sz_yi_sysset', $data);
    } else {
        pdo_update('sz_yi_sysset', $data, array(
            'uniacid' => $_W['uniacid']
        ));
    }
    $setdata = pdo_fetch("select * from " . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(
        ':uniacid' => $_W['uniacid']
    ));
    m('cache')->set('sysset', $setdata);
    message('设置保存成功!', $this->createWebUrl('sysset', array(
        'op' => $op
    )), 'success');
}
load()->func('tpl');
include $this->template('web/sysset/' . $op);
exit;
