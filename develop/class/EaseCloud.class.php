<?php

/**
 * 在这里定义二次开发的类
 */

/**
 * Class EaseCloud
 */
class EaseCloud {

    const EC_SZ_YI_CACHE = SZ_YI_PATH . "data/cache/";

    /**
     * 清理芸众商城缓存
     * @param string $dir
     */
    function yzCleanCache($dir = '') {
        $dir = $dir ?: self::EC_SZ_YI_CACHE;
        $cache_dir = opendir($dir);
        while (($file = readdir($cache_dir)) != false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($dir . $file)) {
                    $this->yzCleanCache($dir . $file . '/');
                    rmdir($dir . $file);
                } else {
                    unlink($dir . $file);
                }
            }
        }
        closedir($dir);
    }

}

