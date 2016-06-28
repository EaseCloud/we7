<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

function chmod_dir($dir,$chmod='') {
    if(is_dir($dir)) {
        if($handle = opendir($dir)) {
            while(false !== ($file = readdir($handle))) {
                if(is_dir($dir.'/'.$file)) {
                    if($file != '.' && $file != '..') {
                        $path = $dir.'/'.$file;
                        $chmod ? chmod($path,$chmod) : FALSE;
                        chmod_dir($path);
                    }
                }else{
                    $path = $dir.'/'.$file;
                    $chmod ? chmod($path,$chmod) : FALSE;
                }
            }
        }
        closedir($handle);
    }
}

function curl_download($url, $dir) {
    $ch = curl_init($url);
    $fp = fopen($dir, "wb");
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $res=curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $res;
}

function send_sms($account, $pwd, $mobile, $content) 
{		
   $smsrs = file_get_contents('http://115.29.33.155/sms.php?method=Submit&account='.$account.'&password='.$pwd.'&mobile=' . $mobile . '&content='.urldecode($content));
  
   return xml_to_array($smsrs);
}

function xml_to_array($xml)
{
    $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches)){
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++){
            $subxml= $matches[2][$i];
            $key = $matches[1][$i];
                    if(preg_match( $reg, $subxml )){
                            $arr[$key] = $this->xml_to_array( $subxml );
                    }else{
                            $arr[$key] = $subxml;
                    }
            }
    }
    return $arr;
}

function redirect($url, $sec=0){
    echo "<meta http-equiv=refresh content='{$sec}; url={$url}'>";
    exit;
}
function m($name = '')
{
    static $_modules = array();
    if (isset($_modules[$name])) {
        return $_modules[$name];
    }
    $model = SZ_YI_CORE . "model/" . strtolower($name) . '.php';
    if (!is_file($model)) {
        die(' Model ' . $name . ' Not Found!');
    }
    require $model;
    $class_name      = 'Sz_DYi_' . ucfirst($name);
    $_modules[$name] = new $class_name();
    return $_modules[$name];
}
function isEnablePlugin($name){
    $plugins = m("cache")->getArray("plugins", "global");
    foreach($plugins as $p){
        if($p['identity'] == $name){
            if($p['status']){
                return true;
            }
            else{
                return false;
            }
        }
    }
}
function p($name = '')
{
    if(!isEnablePlugin($name)){
        return false;
    }
    if ($name != 'perm' && !IN_MOBILE) {
        static $_perm_model;
        if (!$_perm_model) {
            $perm_model_file = SZ_YI_PLUGIN . 'perm/model.php';
            if (is_file($perm_model_file)) {
                require $perm_model_file;
                $perm_class_name = 'PermModel';
                $_perm_model     = new $perm_class_name('perm');
            }
        }
        if ($_perm_model) {
            if (!$_perm_model->check_plugin($name)) {
                return false;
            }
        }
    }
    static $_plugins = array();
    if (isset($_plugins[$name])) {
        return $_plugins[$name];
    }
    $model = SZ_YI_PLUGIN . strtolower($name) . '/model.php';
    if (!is_file($model)) {
        return false;
    }
    require $model;
    $class_name      = ucfirst($name) . 'Model';
    $_plugins[$name] = new $class_name($name);
    return $_plugins[$name];
}
function byte_format($input, $dec = 0)
{
    $prefix_arr = array(
        ' B',
        'K',
        'M',
        'G',
        'T'
    );
    $value      = round($input, $dec);
    $i          = 0;
    while ($value > 1024) {
        $value /= 1024;
        $i++;
    }
    $return_str = round($value, $dec) . $prefix_arr[$i];
    return $return_str;
}
function save_media($url)
{
    $config = array(
        'qiniu' => false
    );
    $plugin = p('qiniu');
    if ($plugin) {
        $config = $plugin->getConfig();
        if ($config) {
            if (strexists($url, $config['url'])) {
                return $url;
            }
            $qiniu_url = $plugin->save(tomedia($url), $config);
            if (empty($qiniu_url)) {
                return $url;
            }
            return $qiniu_url;
        }
        return $url;
    }
    return $url;
}
function is_array2($array)
{
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            return is_array($v);
        }
        return false;
    }
    return false;
}
function set_medias($list = array(), $fields = null)
{
    if (empty($fields)) {
        foreach ($list as &$row) {
            $row = tomedia($row);
        }
        return $list;
    }
    if (!is_array($fields)) {
        $fields = explode(',', $fields);
    }
    if (is_array2($list)) {
        foreach ($list as $key => &$value) {
            foreach ($fields as $field) {
                if (isset($list[$field])) {
                    $list[$field] = tomedia($list[$field]);
                }
                if (is_array($value) && isset($value[$field])) {
                    $value[$field] = tomedia($value[$field]);
                }
            }
        }
        return $list;
    } else {
        foreach ($fields as $field) {
            if (isset($list[$field])) {
                $list[$field] = tomedia($list[$field]);
            }
        }
        return $list;
    }
}
function get_last_day($year, $month)
{
    return date('t', strtotime("{$year}-{$month} -1"));
}
function show_message($msg = '', $url = '', $type = 'success')
{
    $scripts = "<script language='javascript'>require(['core'],function(core){ core.message('" . $msg . "','" . $url . "','" . $type . "')})</script>";
    die($scripts);
}
function show_json($status = 1, $return = null)
{
    $ret = array(
        'status' => $status
    );
    if ($return) {
        $ret['result'] = $return;
    }
    die(json_encode($ret));
}
function is_weixin()
{
    if (empty($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false && strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false) {
        return false;
    }
    return true;
}
function b64_encode($obj)
{
    if (is_array($obj)) {
        return urlencode(base64_encode(json_encode($obj)));
    }
    return urlencode(base64_encode($obj));
}
function b64_decode($str, $is_array = true)
{
    $str = base64_decode(urldecode($str));
    if ($is_array) {
        return json_decode($str, true);
    }
    return $str;
}
function create_image($img)
{
    $ext = strtolower(substr($img, strrpos($img, '.')));
    if ($ext == '.png') {
        $thumb = imagecreatefrompng($img);
    } else if ($ext == '.gif') {
        $thumb = imagecreatefromgif($img);
    } else {
        $thumb = imagecreatefromjpeg($img);
    }
    return $thumb;
}
function get_authcode()
{
    $auth = get_auth();
    return empty($auth['code']) ? '' : $auth['code'];
}
function get_auth()
{
    global $_W;
    $set  = pdo_fetch('select sets from ' . tablename('sz_yi_sysset') . ' order by id asc limit 1');
    $sets = iunserializer($set['sets']);
    if (is_array($sets)) {
        return is_array($sets['auth']) ? $sets['auth'] : array();
    }
    return array();
}
function check_shop_auth($url = '', $type = 's')
{
    global $_W, $_GPC;
    if ($_W['ispost'] && $_GPC['do'] != 'auth') {
        $auth = get_auth();
        load()->func('communication');
        $domain  = $_SERVER['HTTP_HOST'];
        $ip      = gethostbyname($domain);
        $setting = setting_load('site');
        $id      = isset($setting['site']['key']) ? $setting['site']['key'] : '0';
        if (empty($type) || $type == 's') {
            $post_data = array(
                'type' => $type,
                'ip' => $ip,
                'id' => $id,
                'code' => $auth['code'],
                'domain' => $domain
            );
        } else {
            $post_data = array(
                'type' => 'm',
                'm' => $type,
                'ip' => $ip,
                'id' => $id,
                'code' => $auth['code'],
                'domain' => $domain
            );
        }
        $resp   = ihttp_post($url, $post_data);
        $status = $resp['content'];
        if ($status != '1') {
            message(base64_decode('6K+35Yiw5b6u6LWe5a6Y5pa56LSt5LmwLeS6uuS6uuWVhuWfjuaooeWdly1iYnMuMDEyd3ouY29tIQ=='), '', 'error');
        }
    }
}
$my_scenfiles = array();
function my_scandir($dir)
{
    global $my_scenfiles;
    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != ".." && $file != "." && $file != ".git"  && $file != "tmp") {
                if (is_dir($dir . "/" . $file)) {
                    my_scandir($dir . "/" . $file);
                } else {
                    $my_scenfiles[] = $dir . "/" . $file;
                }
            }
        }
        closedir($handle);
    }
}
function shop_template_compile($from, $to, $inmodule = false)
{
    $path = dirname($to);
    if (!is_dir($path)) {
        load()->func('file');
        mkdirs($path);
    }
    $content = shop_template_parse(file_get_contents($from), $inmodule);
    if (IMS_FAMILY == 'x' && !preg_match('/(footer|header|account\/welcome|login|register)+/', $from)) {
        $content = str_replace('微赞', '系统', $content);
    }
    file_put_contents($to, $content);
}
function shop_template_parse($str, $inmodule = false)
{
    $str = template_parse($str, $inmodule);
    $str = preg_replace('/{ifp\s+(.+?)}/', '<?php if(cv($1)) { ?>', $str);
    $str = preg_replace('/{ifpp\s+(.+?)}/', '<?php if(cp($1)) { ?>', $str);
    $str = preg_replace('/{ife\s+(\S+)\s+(\S+)}/', '<?php if( ce($1 ,$2) ) { ?>', $str);
    return $str;
}
function ce($permtype = '', $item = null)
{
    $perm = p('perm');
    if ($perm) {
        return $perm->check_edit($permtype, $item);
    }
    return true;
}
function cv($permtypes = '')
{
    $perm = p('perm');
    if ($perm) {
        return $perm->check_perm($permtypes);
    }
    return true;
}
function ca($permtypes = '')
{
    if (!cv($permtypes)) {
        message('您没有权限操作，请联系管理员!', '', 'error');
    }
}
function cp($pluginname = '')
{
    $perm = p('perm');
    if ($perm) {
        return $perm->check_plugin($pluginname);
    }
    return true;
}
function cpa($pluginname = '')
{
    if (!cp($pluginname)) {
        message('您没有权限操作，请联系管理员!', '', 'error');
    }
}
function plog($type = '', $op = '')
{
    $perm = p('perm');
    if ($perm) {
        $perm->log($type, $op);
    }
}
function tpl_form_field_category_3level($name, $parents, $children, $parentid, $childid, $thirdid)
{
    $html = '
<script type="text/javascript">
	window._' . $name . ' = ' . json_encode($children) . ';
</script>';
    if (!defined('TPL_INIT_CATEGORY_THIRD')) {
        $html .= '	
<script type="text/javascript">
	function renderCategoryThird(obj, name){
		var index = obj.options[obj.selectedIndex].value;
		require([\'jquery\', \'util\'], function($, u){
			$selectChild = $(\'#\'+name+\'_child\');
                                                      $selectThird = $(\'#\'+name+\'_third\');
			var html = \'<option value="0">请选择二级分类</option>\';
                                                      var html1 = \'<option value="0">请选择三级分类</option>\';
			if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
				$selectChild.html(html); 
                                                                        $selectThird.html(html1);
				return false;
			}
			for(var i=0; i< window[\'_\'+name][index].length; i++){
				html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
			}
			$selectChild.html(html);
                                                    $selectThird.html(html1);
		});
	}
        function renderCategoryThird1(obj, name){
		var index = obj.options[obj.selectedIndex].value;
		require([\'jquery\', \'util\'], function($, u){
			$selectChild = $(\'#\'+name+\'_third\');
			var html = \'<option value="0">请选择三级分类</option>\';
			if (!window[\'_\'+name] || !window[\'_\'+name][index]) {
				$selectChild.html(html);
				return false;
			}
			for(var i=0; i< window[\'_\'+name][index].length; i++){
				html += \'<option value="\'+window[\'_\'+name][index][i][\'id\']+\'">\'+window[\'_\'+name][index][i][\'name\']+\'</option>\';
			}
			$selectChild.html(html);
		});
	}
</script>
			';
        define('TPL_INIT_CATEGORY_THIRD', true);
    }
    $html .= '<div class="row row-fix tpl-category-container">
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		<select class="form-control tpl-category-parent" id="' . $name . '_parent" name="' . $name . '[parentid]" onchange="renderCategoryThird(this,\'' . $name . '\')">
			<option value="0">请选择一级分类</option>';
    $ops = '';
    foreach ($parents as $row) {
        $html .= '
			<option value="' . $row['id'] . '" ' . (($row['id'] == $parentid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
    }
    $html .= '
		</select>
	</div>
	<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		<select class="form-control tpl-category-child" id="' . $name . '_child" name="' . $name . '[childid]" onchange="renderCategoryThird1(this,\'' . $name . '\')">
			<option value="0">请选择二级分类</option>';
    if (!empty($parentid) && !empty($children[$parentid])) {
        foreach ($children[$parentid] as $row) {
            $html .= '
			<option value="' . $row['id'] . '"' . (($row['id'] == $childid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
        }
    }
    $html .= '
		</select> 
	</div> 
                  <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
		<select class="form-control tpl-category-child" id="' . $name . '_third" name="' . $name . '[thirdid]">
			<option value="0">请选择三级分类</option>';
    if (!empty($childid) && !empty($children[$childid])) {
        foreach ($children[$childid] as $row) {
            $html .= '
			<option value="' . $row['id'] . '"' . (($row['id'] == $thirdid) ? 'selected="selected"' : '') . '>' . $row['name'] . '</option>';
        }
    }
    $html .= '</select>
	</div>
</div>';
    return $html;
}
