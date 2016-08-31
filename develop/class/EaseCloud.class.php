<?php

/**
 * 在这里定义二次开发的类
 */

/**
 * Class EaseCloud
 */
class EaseCloud {

    /**
     * 清理芸众商城缓存
     * @param string $dir
     */
    function yzCleanCache($dir = '') {
        $dir = $dir ?: SZ_YI_PATH . "data/cache/";
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

