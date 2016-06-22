<?php
define('CLOUD_UPGRADE_URL', 'http://115.29.33.155/web/index.php?c=account&a=upgradetest');

if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
if (!$_W['isfounder']) {
    message('无权访问!');
}
 
$op = empty($_GPC['op']) ? 'display' : $_GPC['op'];
load()->func('communication');
load()->func('file');
if ($op == 'display') {
    //先看是否注册，没注册的要注册
    /*
    define('CLOUD_URL', 'http://115.29.33.155/web/index.php?c=account&a=register');
    $data['domain'] = $_SERVER['HTTP_HOST'];
    $data['signature'] = 'sz_cloud_register';
    $res = ihttp_request(CLOUD_URL, $data);
    if(!$res){
        exit('通讯失败,请检查网络');
    }
    $content = json_decode($res['content'], 1);
    if(!$content['status']){
        exit($content['msg']);
    }
     */
    $versionfile = IA_ROOT . '/addons/sz_yi/version.php';
    $updatedate  = date('Y-m-d H:i', filemtime($versionfile));
    $version     = SZ_YI_VERSION;
} else if ($op == 'check') {
    //文件比对形式更新
    set_time_limit(0);

    global $my_scenfiles;
    my_scandir(IA_ROOT . '/addons/sz_yi');
    $files = array();
    foreach ($my_scenfiles as $sf) {
        $files[] = array(
            'path' => str_replace(IA_ROOT . "/addons/sz_yi/", "", $sf),
            'md5' => md5_file($sf)
        );
    }
    $files   = base64_encode(json_encode($files));
    $version = defined('SZ_YI_VERSION') ? SZ_YI_VERSION : '1.0';
    $resp    = ihttp_post(CLOUD_UPGRADE_URL, array(
        'type' => 'upgrade',
        'signature' => 'sz_cloud_register',
        'domain' => $_SERVER['HTTP_HOST'],
        'version' => $version,
        'files' => $files
    ));
    $ret     = @json_decode($resp['content'], true);
    if (is_array($ret)) {
        if ($ret['result'] == 1) {
            $files = array();
            if (!empty($ret['files'])) {
                foreach ($ret['files'] as $file) {
                    $entry = IA_ROOT . "/addons/sz_yi/" . $file['path'];
                    //如果本地没有此文件或者文件与服务器不一致
                    if (!is_file($entry) || md5_file($entry) != $file['md5']) {
                        $dir = explode('/', $file['path']);
                        if(@$dir[0] == 'tmp'){
                            continue;
                        }
                        $files[] = array(
                            'path' => $file['path'],
                            'download' => 0
                        );
                        $difffile[] = $file['path'];
                    }
                    else{
                        $samefile[] = $file['path'];
                    }
                }
            }
            $tmpdir = IA_ROOT . "/addons/sz_yi/tmp/" . date('ymd');
            if (!is_dir($tmpdir)) {
                mkdirs($tmpdir);
            }

            $ret['files'] = $files;
            file_put_contents($tmpdir . "/file.txt", json_encode($ret));
            die(json_encode(array(
                'result' => 1,
                'version' => $ret['version'],
                'filecount' => count($files),
                'upgrade' => !empty($ret['upgrade']),
                'log' => str_replace("\r\n", "<br/>", base64_decode($ret['log']))
            )));
        }
    }
    die(json_encode(array(
        'result' => 0,
        'message' => $resp['content'] . ". "
    )));
} else if ($op == 'download') {
    $tmpdir  = IA_ROOT . "/addons/sz_yi/tmp/" . date('ymd');
    $f       = file_get_contents($tmpdir . "/file.txt");
    $upgrade = json_decode($f, true);
    $files   = $upgrade['files'];
    $path    = "";
    foreach ($files as $f) {
        if (empty($f['download'])) {
            $path = $f['path'];
            break;
        }
    }
    if (!empty($path)) {
        $resp = ihttp_post(CLOUD_UPGRADE_URL, array(
            'type' => 'download',
            'signature' => 'sz_cloud_register',
            'domain' => $_SERVER['HTTP_HOST'],
            'version' => $version,
            'path' => $path
        ));
        $ret  = @json_decode($resp['content'], true);
        if (is_array($ret)) {
            $path    = $ret['path'];
            $dirpath = dirname($path);
            if (!is_dir(IA_ROOT . '/addons/sz_yi/' . $dirpath)) {
                mkdirs(IA_ROOT . "/addons/sz_yi/" . $dirpath, "0777");
            }
            $content = base64_decode($ret['content']);
            file_put_contents(IA_ROOT . '/addons/sz_yi/' . $path, $content);
            if (isset($ret['path1'])) {
                $path1    = $ret['path1'];
                $dirpath1 = dirname($path1);
                if (!is_dir(IA_ROOT . '/addons/sz_yi/' . $dirpath1)) {
                    mkdirs(IA_ROOT . "/addons/sz_yi/" . $dirpath1, "0777");
                }
                $content1 = base64_decode($ret['content1']);
                file_put_contents(IA_ROOT . '/addons/sz_yi/' . $path1, $content1);
            }
            $success = 0;
            foreach ($files as &$f) {
                if ($f['path'] == $path) {
                    $f['download'] = 1;
                    break;
                }
                if ($f['download']) {
                    $success++;
                }
            }
            unset($f);
            $upgrade['files'] = $files;
            $tmpdir           = IA_ROOT . "/addons/sz_yi/tmp/" . date('ymd');
            if (!is_dir($tmpdir)) {
                mkdirs($tmpdir);
            }
            file_put_contents($tmpdir . "/file.txt", json_encode($upgrade));
            die(json_encode(array(
                'result' => 1,
                'total' => count($files),
                'success' => $success
            )));
        }
    } else {
        //数据库是否有更新，更新之后删除此文件
        if (!empty($upgrade['upgrade'])) {
            $updatefile = IA_ROOT . "/addons/sz_yi/upgradesql.php";
            file_put_contents($updatefile, base64_decode($upgrade['upgrade']));
            require $updatefile;
            @unlink($updatefile);
        }
        file_put_contents(IA_ROOT . '/addons/sz_yi/version.php', "<?php if(!defined('IN_IA')) {exit('Access Denied');}if(!defined('SZ_YI_VERSION')) {define('SZ_YI_VERSION', '" . $upgrade['version'] . "');}");
        $tmpdir = IA_ROOT . "/addons/sz_yi/tmp";
        @rmdirs($tmpdir);
        @rmdirs(IA_ROOT . "/addons/sz_yi/data/cache");
        $time = time();
        global $my_scenfiles;
        my_scandir(IA_ROOT . '/addons/sz_yi');
        foreach ($my_scenfiles as $file) {
            if (!strexists($file, '/sz_yi/data/') && !strexists($file, 'version.php')) {
                @touch($file, $time);
            }
        }
        die(json_encode(array(
            'result' => 2
        )));
    }

} else if ($op == 'download_zip') {
	//更新版本
    define('CLOUD_UPGRADE_URL', 'http://115.29.33.155/web/index.php?c=account&a=upgrade');
    $data['version'] = SZ_YI_VERSION;
    $data['method'] = 'upgrade';
    $res = ihttp_request(CLOUD_UPGRADE_URL, $data);
    //print_r($res);
    if(!$res){
        die(json_encode(array('result' => 0, 'msg' => '通讯失败,请检查网络')));
    }
    $res = json_decode($res['content'], 1);
    if($res['msg'] == 'new'){
        die(json_encode(array('result' => 0, 'msg' => '已经是最新程序')));
    }

    foreach($res as $v){
        if($v['version'] == SZ_YI_VERSION){
            continue;
        }
        $filename = 'http://115.29.33.155/data/upgrade_zip/'.$v['version'].'.zip';
        curl_download($filename, IA_ROOT. '/addons/sz_yi/upgrade.zip');

        $zip = new ZipArchive; 
        $res = $zip->open(IA_ROOT. '/addons/sz_yi/upgrade.zip'); 
        if ($res === TRUE) { 
            //chmod_dir(IA_ROOT. '/addons/sz_yi/', '0755');
            //解压缩到文件夹 
            $zip->extractTo(IA_ROOT.'/addons'); 
            $zip->close(); 
            //echo "更新版本{$v['version']}成功<br>";
            //die(json_encode(array('result' => 1, 'total' => count($files), 'success' => $success)));
            $version = file_get_contents(IA_ROOT .'/addons/sz_yi/version.php');
            $v = preg_replace('/define\(\'SZ_YI_VERSION\', \'(.+)\'\)/', 'define(\'SZ_YI_VERSION\', \''.$v['version'].'\')',$version);
            file_put_contents(IA_ROOT .'/addons/sz_yi/version.php', $v);

        } else { 
            die(json_encode(array('result' => 0, 'msg' => '解压失败')));
        } 
    }
    die(json_encode(array('result' => 2)));
} else if ($op == 'checkversion') {
	
	file_put_contents(IA_ROOT . "/addons/sz_yi/version.php", "<?php if(!defined('IN_IA')) {exit('Access Denied');}if(!defined('SZ_YI_VERSION')) {define('SZ_YI_VERSION', '1.0');}");
	header('location: '.$this->createWebUrl('upgrade'));
	exit;	 
	
}
include $this->template('web/sysset/upgrade');
