<?php
/*=============================================================================
#     FileName: cache.php
#         Desc: »º´æ
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:19:24
#      History:
=============================================================================*/

if (!defined('IN_IA')) {
    exit('Access Denied');
}
class Sz_DYi_Cache
{
    function get_key($key = '', $uniacid = '')
    {
        global $_W;
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        return SZ_YI_PREFIX . '_' . $GLOBALS['_W']['config']['setting']['authkey'] . '_' . $uniacid . '_' . $key;
    }
    function getArray($key = '', $uniacid = '')
    {
        return $this->get($key, true, $uniacid);
    }
    function getString($key = '', $uniacid = '')
    {
        return $this->get($key, false, $uniacid);
    }
    function get($key = '', $isArray = true, $uniacid = '')
    {
        global $_W;
        if (empty($key)) {
            return false;
        }
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        $value = false;
        if ($_W['config']['setting']['cache'] == 'memcache') {
            if (extension_loaded('memcache')) {
                $value = $this->memcache_read($key, $uniacid);
            }
        }
        if (empty($value)) {
            return $this->file_read($key, $isArray, $uniacid);
        }
        return $value;
    }
    function set($key = '', $value = null, $uniacid = '')
    {
        global $_W;
        if (empty($key)) {
            return false;
        }
        if (empty($uniacid)) {
            $uniacid = $_W['uniacid'];
        }
        $result = false;
        if ($_W['config']['setting']['cache'] == 'memcache') {
            if (extension_loaded('memcache')) {
                $result = $this->memcache_write($key, $value, $uniacid);
            }
        }
        if (empty($result)) {
            $this->file_set($key, $value, $uniacid);
        }
    }
    function file_read($key = '', $isArray = true, $uniacid = '')
    {
        global $_W;
        if (empty($key)) {
            return false;
        }
        $content = @file_get_contents(IA_ROOT . "/addons/sz_yi/data/cache/" . $uniacid . "/" . $key);
        if (empty($content)) {
            return false;
        }
        return $isArray ? iunserializer($content) : $content;
    }
    function file_set($key = '', $value = null, $uniacid = '')
    {
        global $_W;
        if (empty($key)) {
            return false;
        }
        $content = is_array($value) ? iserializer($value) : $value;
        $path    = IA_ROOT . "/addons/sz_yi/data/cache/" . $uniacid;
        if (!is_dir($path)) {
            load()->func('file');
            @mkdirs($path);
        }
        file_put_contents($path . "/" . $key, $content);
    }
    function get_memcache()
    {
        global $_W;
        static $memcacheobj;
        if (!extension_loaded('memcache')) {
            return error(1, 'Class Memcache is not found');
        }
        if (empty($memcacheobj)) {
            $config      = $_W['config']['setting']['memcache'];
            $memcacheobj = new Memcache();
            if ($config['pconnect']) {
                $connect = $memcacheobj->pconnect($config['server'], $config['port']);
            } else {
                $connect = $memcacheobj->connect($config['server'], $config['port']);
            }
            if (!$connect) {
                return error(-1, 'Memcache is not in work');
            }
        }
        return $memcacheobj;
    }
    function memcache_read($key, $uniacid)
    {
        $memcache = $this->get_memcache();
        if (is_error($memcache)) {
            return false;
        }
        return $memcache->get($this->get_key($key, $uniacid));
    }
    function memcache_write($key, $value, $uniacid = 0, $ttl = 0)
    {
        $memcache = $this->get_memcache();
        if (is_error($memcache)) {
            return false;
        }
        return $memcache->set($this->get_key($key, $uniacid), $value, MEMCACHE_COMPRESSED, $ttl);
    }
    function memcache_delete($key, $uniacid = 0)
    {
        $memcache = $this->get_memcache();
        if (is_error($memcache)) {
            return false;
        }
        return $memcache->delete($this->get_key($key, $uniacid));
    }
}
