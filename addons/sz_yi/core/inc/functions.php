<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
function icheck_gpc($_var_0)
{
    if (is_array($_var_0)) {
        foreach ($_var_0 as $_var_1 => $_var_2) {
            $_var_0[stripslashes($_var_1)] = icheck_gpc($_var_2);
        }
    } else {
        $_var_0 = inject_check($_var_0);
        if ($_var_0) {
            die('非法参数');
        }
    }
    return $_var_0;
}

function inject_check($_var_3)
{
    return preg_match('/eval|select|insert|update|delete|\'|\\/\\*|\\*|\\.\\.\\/|\\.\\/|union|into|load_file|outfile/i', $_var_3);
}

function sz_tpl_form_field_date($_var_4, $_var_2 = '', $_var_5 = false)
{
    $_var_6 = '';
    if (!defined('TPL_INIT_DATA')) {
        $_var_6 = "
                    <script type=\"text/javascript\">
                        require([\"datetimepicker\"], function(){
                            \$(function(){
                                \$(\".datetimepicker\").each(function(){
                                    var option = {
                                        lang : \"zh\",
                                        step : \"10\",
                                        timepicker : " . (!empty($_var_5) ? 'true' : 'false') . ",closeOnDateSelect : true,\r\n\t\t\tformat : \"Y-m-d" . (!empty($_var_5) ? ' H:i:s"' : '"') . "};\r\n\t\t\t\$(this).datetimepicker(option);\r\n\t\t});\r\n\t});\r\n});\r\n</script>";
        define('TPL_INIT_DATA', true);
    }
    $_var_5 = empty($_var_5) ? false : true;
    if (!empty($_var_2)) {
        $_var_2 = strexists($_var_2, '-') ? strtotime($_var_2) : $_var_2;
    } else {
        $_var_2 = TIMESTAMP;
    }
    $_var_2 = $_var_5 ? date('Y-m-d H:i:s', $_var_2) : date('Y-m-d', $_var_2);
    $_var_6 .= '<input type="text" name="' . $_var_4 . '"  value="' . $_var_2 . '" placeholder="请选择日期时间" readonly="readonly" class="datetimepicker form-control" style="padding-left:12px;" />';
    return $_var_6;
}

function isMobile()
{
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    }
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $_var_7 = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'WindowsWechat');
        if (preg_match('/(' . implode('|', $_var_7) . ')/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))) {
            return true;
        }
    }
    return false;
}

function chmod_dir($_var_8, $_var_9 = '')
{
    if (is_dir($_var_8)) {
        if ($_var_10 = opendir($_var_8)) {
            while (false !== ($_var_11 = readdir($_var_10))) {
                if (is_dir($_var_8 . '/' . $_var_11)) {
                    if ($_var_11 != '.' && $_var_11 != '..') {
                        $_var_12 = $_var_8 . '/' . $_var_11;
                        $_var_9 ? chmod($_var_12, $_var_9) : FALSE;
                        chmod_dir($_var_12);
                    }
                } else {
                    $_var_12 = $_var_8 . '/' . $_var_11;
                    $_var_9 ? chmod($_var_12, $_var_9) : FALSE;
                }
            }
        }
        closedir($_var_10);
    }
}

function curl_download($_var_13, $_var_8)
{
    $_var_14 = curl_init($_var_13);
    $_var_15 = fopen($_var_8, 'wb');
    curl_setopt($_var_14, CURLOPT_FILE, $_var_15);
    curl_setopt($_var_14, CURLOPT_HEADER, 0);
    $_var_16 = curl_exec($_var_14);
    curl_close($_var_14);
    fclose($_var_15);
    return $_var_16;
}

function send_sms($account, $pwd, $mobile, $content)
{
    $msg_text = '您的验证码是：' . $content . '。请不要把验证码泄露给其他人。如非本人操作，可不用理会！';
    $smsrs = file_get_contents('http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=' . $account . '&password=' . $pwd . '&mobile=' . $mobile . '&content=' . urldecode($msg_text));
    return xml_to_array($smsrs);
}

function send_sms_alidayu($recNum, $code, $action)
{
    $sysset = m('common')->getSysset();
    include IA_ROOT . '/addons/sz_yi/alifish/TopSdk.php';
    switch ($action) {
        case 'reg':
            $template_code = $sysset['sms']['templateCode'];
            break;
        case 'forget':
            $template_code = $sysset['sms']['templateCodeForget'];
            break;
        default:
            $template_code = $sysset['sms']['templateCode'];
            break;
    }
    $client = new TopClient();
    $client->appkey = $sysset['sms']['appkey'];
    $client->secretKey = $sysset['sms']['secret'];
    $request = new AlibabaAliqinFcSmsNumSendRequest();
    $request->setExtend('123456');
    $request->setSmsType('normal');
    $request->setSmsFreeSignName($sysset['sms']['signname']);
    $request->setSmsParam("{\"code\":\"{$code}\",\"product\":\"{$sysset['sms']['product']}\"}");
    $request->setRecNum($recNum);
    $request->setSmsTemplateCode($template_code);
    $result = $client->execute($request);
    return objectArray($result);
}

function xml_to_array($xml)
{
    $arr = array();
    $regex = '/<(\\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/';
    if (preg_match_all($regex, $xml, $matches)) {
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $tagValue = $matches[2][$i];
            $tagName = $matches[1][$i];
            if (preg_match($regex, $tagValue)) {
                $arr[$tagName] = xml_to_array($tagValue);
            } else {
                $arr[$tagName] = $tagValue;
            }
        }
    }
    return $arr;
}

function redirect($_var_13, $_var_36 = 0)
{
    echo "<meta http-equiv=refresh content='{$_var_36}; url={$_var_13}'>";
    die;
}

function m($_var_4 = '')
{
    static $_var_37 = array();
    if (isset($_var_37[$_var_4])) {
        return $_var_37[$_var_4];
    }
    $_var_38 = SZ_YI_CORE . 'model/' . strtolower($_var_4) . '.php';
    if (!is_file($_var_38)) {
        die(' Model ' . $_var_4 . ' Not Found!');
    }
    require $_var_38;
    $_var_39 = 'Sz_DYi_' . ucfirst($_var_4);
    $_var_37[$_var_4] = new $_var_39();
    return $_var_37[$_var_4];
}

function isEnablePlugin($_var_4)
{
    $_var_40 = m('cache')->getArray('plugins', 'global');
    if ($_var_40) {
        foreach ($_var_40 as $_var_41) {
            if ($_var_41['identity'] == $_var_4) {
                if ($_var_41['status']) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}

function p($_var_4 = '')
{
    if (!isEnablePlugin($_var_4)) {
        return false;
    }
    if ($_var_4 != 'perm' && !IN_MOBILE) {
        static $_var_42;
        if (!$_var_42) {
            $_var_43 = SZ_YI_PLUGIN . 'perm/model.php';
            if (is_file($_var_43)) {
                require $_var_43;
                $_var_44 = 'PermModel';
                $_var_42 = new $_var_44('perm');
            }
        }
        if ($_var_42) {
            if (!$_var_42->check_plugin($_var_4)) {
                return false;
            }
        }
    }
    static $_var_45 = array();
    if (isset($_var_45[$_var_4])) {
        return $_var_45[$_var_4];
    }
    $_var_38 = SZ_YI_PLUGIN . strtolower($_var_4) . '/model.php';
    if (!is_file($_var_38)) {
        return false;
    }
    require $_var_38;
    $_var_39 = ucfirst($_var_4) . 'Model';
    $_var_45[$_var_4] = new $_var_39($_var_4);
    return $_var_45[$_var_4];
}

function byte_format($_var_46, $_var_47 = 0)
{
    $_var_48 = array(' B', 'K', 'M', 'G', 'T');
    $_var_2 = round($_var_46, $_var_47);
    $_var_33 = 0;
    while ($_var_2 > 1024) {
        $_var_2 /= 1024;
        $_var_33++;
    }
    $_var_49 = round($_var_2, $_var_47) . $_var_48[$_var_33];
    return $_var_49;
}

function save_media($_var_13)
{
    $_var_50 = array('qiniu' => false);
    $_var_51 = p('qiniu');
    if ($_var_51) {
        $_var_50 = $_var_51->getConfig();
        if ($_var_50) {
            if (strexists($_var_13, $_var_50['url'])) {
                return $_var_13;
            }
            $_var_52 = $_var_51->save(tomedia($_var_13), $_var_50);
            if (empty($_var_52)) {
                return $_var_13;
            }
            return $_var_52;
        }
        return $_var_13;
    }
    return $_var_13;
}

function is_array2($_var_53)
{
    if (is_array($_var_53)) {
        foreach ($_var_53 as $_var_54 => $_var_55) {
            return is_array($_var_55);
        }
        return false;
    }
    return false;
}

function set_medias($_var_56 = array(), $_var_57 = null)
{
    if (empty($_var_57)) {
        foreach ($_var_56 as &$_var_58) {
            $_var_58 = tomedia($_var_58);
        }
        return $_var_56;
    }
    if (!is_array($_var_57)) {
        $_var_57 = explode(',', $_var_57);
    }
    if (is_array2($_var_56)) {
        foreach ($_var_56 as $_var_1 => &$_var_2) {
            foreach ($_var_57 as $_var_59) {
                if (isset($_var_56[$_var_59])) {
                    $_var_56[$_var_59] = tomedia($_var_56[$_var_59]);
                }
                if (is_array($_var_2) && isset($_var_2[$_var_59])) {
                    $_var_2[$_var_59] = tomedia($_var_2[$_var_59]);
                }
            }
        }
        return $_var_56;
    } else {
        foreach ($_var_57 as $_var_59) {
            if (isset($_var_56[$_var_59])) {
                $_var_56[$_var_59] = tomedia($_var_56[$_var_59]);
            }
        }
        return $_var_56;
    }
}

function get_last_day($_var_60, $_var_61)
{
    return date('t', strtotime("{$_var_60}-{$_var_61} -1"));
}

function show_message($_var_62 = '', $_var_13 = '', $_var_63 = 'success')
{
    $_var_64 = '<script language=\'javascript\'>require([\'core\'],function(core){ core.message(\'' . $_var_62 . '\',\'' . $_var_13 . '\',\'' . $_var_63 . '\')})</script>';
    die($_var_64);
}

function show_json($_var_65 = 1, $_var_66 = null)
{
    $_var_67 = array('status' => $_var_65);
    if ($_var_66) {
        $_var_67['result'] = $_var_66;
    }
    die(json_encode($_var_67));
}

function is_weixin()
{
    if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
        return false;
    }
    return true;
}

function b64_encode($_var_68)
{
    if (is_array($_var_68)) {
        return urlencode(base64_encode(json_encode($_var_68)));
    }
    return urlencode(base64_encode($_var_68));
}

function b64_decode($_var_69, $_var_70 = true)
{
    $_var_69 = base64_decode(urldecode($_var_69));
    if ($_var_70) {
        return json_decode($_var_69, true);
    }
    return $_var_69;
}

function create_image($_var_71)
{
    $_var_72 = strtolower(substr($_var_71, strrpos($_var_71, '.')));
    if ($_var_72 == '.png') {
        $_var_73 = imagecreatefrompng($_var_71);
    } else {
        if ($_var_72 == '.gif') {
            $_var_73 = imagecreatefromgif($_var_71);
        } else {
            $_var_73 = imagecreatefromjpeg($_var_71);
        }
    }
    return $_var_73;
}

function get_authcode()
{
    $_var_74 = get_auth();
    return empty($_var_74['code']) ? '' : $_var_74['code'];
}

function get_auth()
{
    global $_W;
    $_var_24 = pdo_fetch('select sets from ' . tablename('sz_yi_sysset') . ' order by id asc limit 1');
    $_var_75 = iunserializer($_var_24['sets']);
    if (is_array($_var_75)) {
        return is_array($_var_75['auth']) ? $_var_75['auth'] : array();
    }
    return array();
}

function check_shop_auth($_var_13 = '', $_var_63 = 's')
{
    global $_W, $_GPC;
    if ($_W['ispost'] && $_GPC['do'] != 'auth') {
        $_var_74 = get_auth();
        load()->func('communication');
        $_var_76 = $_SERVER['HTTP_HOST'];
        $_var_77 = gethostbyname($_var_76);
        $_var_78 = setting_load('site');
        $_var_79 = isset($_var_78['site']['key']) ? $_var_78['site']['key'] : '0';
        if (empty($_var_63) || $_var_63 == 's') {
            $_var_80 = array('type' => $_var_63, 'ip' => $_var_77, 'id' => $_var_79, 'code' => $_var_74['code'], 'domain' => $_var_76);
        } else {
            $_var_80 = array('type' => 'm', 'm' => $_var_63, 'ip' => $_var_77, 'id' => $_var_79, 'code' => $_var_74['code'], 'domain' => $_var_76);
        }
        $_var_28 = ihttp_post($_var_13, $_var_80);
        $_var_65 = $_var_28['content'];
        if ($_var_65 != '1') {
            message(base64_decode('6K+35Yiw5b6u6LWe5a6Y5pa56LSt5LmwLeS6uuS6uuWVhuWfjuaooeWdly1iYnMuMDEyd3ouY29tIQ=='), '', 'error');
        }
    }
}

$my_scenfiles = array();
function my_scandir($_var_8)
{
    global $my_scenfiles;
    if ($_var_10 = opendir($_var_8)) {
        while (($_var_11 = readdir($_var_10)) !== false) {
            if ($_var_11 != '..' && $_var_11 != '.' && $_var_11 != '.git' && $_var_11 != 'tmp') {
                if (is_dir($_var_8 . '/' . $_var_11)) {
                    my_scandir($_var_8 . '/' . $_var_11);
                } else {
                    $my_scenfiles[] = $_var_8 . '/' . $_var_11;
                }
            }
        }
        closedir($_var_10);
    }
}

function shop_template_compile($_var_81, $_var_82, $_var_83 = false)
{
    $_var_12 = dirname($_var_82);
    if (!is_dir($_var_12)) {
        load()->func('file');
        mkdirs($_var_12);
    }
    $_var_21 = shop_template_parse(file_get_contents($_var_81), $_var_83);
    if (IMS_FAMILY == 'x' && !preg_match('/(footer|header|account\\/welcome|login|register)+/', $_var_81)) {
        $_var_21 = str_replace('微赞', '系统', $_var_21);
    }
    file_put_contents($_var_82, $_var_21);
}

function shop_template_parse($_var_69, $_var_83 = false)
{
    $_var_69 = template_parse($_var_69, $_var_83);
    $_var_69 = preg_replace('/{ifp\\s+(.+?)}/', '<?php if(cv($1)) { ?>', $_var_69);
    $_var_69 = preg_replace('/{ifpp\\s+(.+?)}/', '<?php if(cp($1)) { ?>', $_var_69);
    $_var_69 = preg_replace('/{ife\\s+(\\S+)\\s+(\\S+)}/', '<?php if( ce($1 ,$2) ) { ?>', $_var_69);
    return $_var_69;
}

function ce($_var_84 = '', $_var_85 = null)
{
    $_var_86 = p('perm');
    if ($_var_86) {
        return $_var_86->check_edit($_var_84, $_var_85);
    }
    return true;
}

function cv($_var_87 = '')
{
    $_var_86 = p('perm');
    if ($_var_86) {
        return $_var_86->check_perm($_var_87);
    }
    return true;
}

function ca($_var_87 = '')
{
    if (!cv($_var_87)) {
        message('您没有权限操作，请联系管理员!', '', 'error');
    }
}

function cp($_var_88 = '')
{
    $_var_86 = p('perm');
    if ($_var_86) {
        return $_var_86->check_plugin($_var_88);
    }
    return true;
}

function cpa($_var_88 = '')
{
    if (!cp($_var_88)) {
        message('您没有权限操作，请联系管理员!', '', 'error');
    }
}

function plog($_var_63 = '', $_var_89 = '')
{
    $_var_86 = p('perm');
    if ($_var_86) {
        $_var_86->log($_var_63, $_var_89);
    }
}

function objectArray($_var_53)
{
    if (is_object($_var_53)) {
        $_var_53 = (array)$_var_53;
    }
    if (is_array($_var_53)) {
        foreach ($_var_53 as $_var_1 => $_var_2) {
            $_var_53[$_var_1] = objectArray($_var_2);
        }
    }
    return $_var_53;
}

function tpl_form_field_category_3level($_var_4, $_var_90, $_var_91, $_var_92, $_var_93, $_var_94)
{
    $_var_95 = "\r\n<script type=\"text/javascript\">\r\n\twindow._" . $_var_4 . ' = ' . json_encode($_var_91) . ";\r\n</script>";
    if (!defined('TPL_INIT_CATEGORY_THIRD')) {
        $_var_95 .= "
        <script type=\"text/javascript\">
            function renderCategoryThird(obj, name){
                var index = obj.options[obj.selectedIndex].value;
                require(['jquery', 'util'], function(\$, u){
                    \$selectChild = \$('#'+name+'_child');
                                                              \$selectThird = \$('#'+name+'_third');
                    var html = '<option value=\"0\">请选择二级分类</option>';
                                                              var html1 = '<option value=\"0\">请选择三级分类</option>';
                    if (!window['_'+name] || !window['_'+name][index]) {
                        \$selectChild.html(html);
                                                                                \$selectThird.html(html1);
                        return false;
                    }
                    for(var i=0; i< window['_'+name][index].length; i++){
                        html += '<option value=\"'+window['_'+name][index][i]['id']+'\">'+window['_'+name][index][i]['name']+'</option>';
                    }
                    \$selectChild.html(html);
                                                            \$selectThird.html(html1);
                });
            }
                function renderCategoryThird1(obj, name){
                var index = obj.options[obj.selectedIndex].value;
                require(['jquery', 'util'], function(\$, u){
                    \$selectChild = \$('#'+name+'_third');
                    var html = '<option value=\"0\">请选择三级分类</option>';
                    if (!window['_'+name] || !window['_'+name][index]) {
                        \$selectChild.html(html);
                        return false;
                    }
                    for(var i=0; i< window['_'+name][index].length; i++){
                        html += '<option value=\"'+window['_'+name][index][i]['id']+'\">'+window['_'+name][index][i]['name']+'</option>';
                    }
                    \$selectChild.html(html);
                });
            }
        </script>
                    ";
        define('TPL_INIT_CATEGORY_THIRD', true);
    }
    $_var_95 .= "<div class=\"row row-fix tpl-category-container\">
        <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">
            <select class=\"form-control tpl-category-parent\" id=\"" . $_var_4 . '_parent" name="' . $_var_4 . '[parentid]" onchange="renderCategoryThird(this,\'' . $_var_4 . "')\">\r\n\t\t\t<option value=\"0\">请选择一级分类</option>";
    $_var_96 = '';
    foreach ($_var_90 as $_var_58) {
        $_var_95 .= "\r\n\t\t\t<option value=\"" . $_var_58['id'] . '" ' . ($_var_58['id'] == $_var_92 ? 'selected="selected"' : '') . '>' . $_var_58['name'] . '</option>';
    }
    $_var_95 .= "
            </select>
        </div>
        <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">
            <select class=\"form-control tpl-category-child\" id=\"" . $_var_4 . '_child" name="' . $_var_4 . '[childid]" onchange="renderCategoryThird1(this,\'' . $_var_4 . "')\">\r\n\t\t\t<option value=\"0\">请选择二级分类</option>";
    if (!empty($_var_92) && !empty($_var_91[$_var_92])) {
        foreach ($_var_91[$_var_92] as $_var_58) {
            $_var_95 .= "\r\n\t\t\t<option value=\"" . $_var_58['id'] . '"' . ($_var_58['id'] == $_var_93 ? 'selected="selected"' : '') . '>' . $_var_58['name'] . '</option>';
        }
    }
    $_var_95 .= "
            </select>
        </div>
                      <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">
            <select class=\"form-control tpl-category-child\" id=\"" . $_var_4 . '_third" name="' . $_var_4 . "[thirdid]\">\r\n\t\t\t<option value=\"0\">请选择三级分类</option>";
    if (!empty($_var_93) && !empty($_var_91[$_var_93])) {
        foreach ($_var_91[$_var_93] as $_var_58) {
            $_var_95 .= "\r\n\t\t\t<option value=\"" . $_var_58['id'] . '"' . ($_var_58['id'] == $_var_94 ? 'selected="selected"' : '') . '>' . $_var_58['name'] . '</option>';
        }
    }
    $_var_95 .= "</select>\r\n\t</div>\r\n</div>";
    return $_var_95;
}