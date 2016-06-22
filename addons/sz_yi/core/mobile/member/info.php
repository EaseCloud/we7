<?php


if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$openid         = m('user')->getOpenid();
$member         = m('member')->getInfo($openid);
$template_flag  = 0;
$diyform_plugin = p('diyform');
if ($diyform_plugin) {
    $set_config        = $diyform_plugin->getSet();
    $user_diyform_open = $set_config['user_diyform_open'];
    if ($user_diyform_open == 1) {
        $template_flag = 1;
        $diyform_id    = $set_config['user_diyform'];
        if (!empty($diyform_id)) {
            $formInfo     = $diyform_plugin->getDiyformInfo($diyform_id);
            $fields       = $formInfo['fields'];
            $diyform_data = iunserializer($member['diymemberdata']);
            $f_data       = $diyform_plugin->getDiyformData($diyform_data, $fields, $member);
        }
    }
}
if ($_W['isajax']) {
    if ($_W['ispost']) {
        $memberdata = $_GPC['memberdata'];
        if ($template_flag == 1) {
            $data                      = array();
            $m_data                    = array();
            $mc_data                   = array();
            $insert_data               = $diyform_plugin->getInsertData($fields, $memberdata);
            $data                      = $insert_data['data'];
            $m_data                    = $insert_data['m_data'];
            $mc_data                   = $insert_data['mc_data'];
            $m_data['diymemberid']     = $diyform_id;
            $m_data['diymemberfields'] = iserializer($fields);
            $m_data['diymemberdata']   = $data;
            pdo_update('sz_yi_member', $m_data, array(
                'openid' => $openid,
                'uniacid' => $_W['uniacid']
            ));
            if (!empty($member['uid'])) {
                load()->model('mc');
                if (!empty($mc_data)) {
                    mc_update($member['uid'], $mc_data);
                }
            }
        } else {
            pdo_update('sz_yi_member', $memberdata, array(
                'openid' => $openid,
                'uniacid' => $_W['uniacid']
            ));
            if (!empty($member['uid'])) {
                $mcdata = $_GPC['mcdata'];
                load()->model('mc');
                mc_update($member['uid'], $mcdata);
            }
        }
        show_json(1);
    }
    show_json(1, array(
        'member' => $member
    ));
}
if ($template_flag == 1) {
    include $this->template('diyform/info');
} else {
    include $this->template('member/info');
}

