<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class Plugin extends Core
{
    public $pluginname;
    public $model;

    public function __construct($_var_0 = '')
    {
        parent::__construct();
        $this->modulename = 'sz_yi';
        $this->pluginname = $_var_0;
        $this->loadModel();
        if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
            cpa($this->pluginname);
        } else {
            if (strexists($_SERVER['REQUEST_URI'], '/app/')) {
                $this->setFooter();
            }
        }
        $this->module['title'] = pdo_fetchcolumn('select title from ' . tablename('modules') . ' where name=\'sz_yi\' limit 1');
    }

    private function loadModel()
    {
        $_var_1 = IA_ROOT . '/addons/' . $this->modulename . '/plugin/' . $this->pluginname . '/model.php';
        if (is_file($_var_1)) {
            $_var_2 = ucfirst($this->pluginname) . 'Model';
            require $_var_1;
            $this->model = new $_var_2($this->pluginname);
        }
    }

    public function getSet()
    {
        return $this->model->getSet();
    }

    public function updateSet($_var_3 = array())
    {
        $this->model->updateSet($_var_3);
    }

    public function template($_var_4, $_var_5 = TEMPLATE_INCLUDEPATH)
    {
        global $_W;
        $_var_6 = isMobile() ? 'mobile' : 'pc';
        if (strstr($_SERVER['REQUEST_URI'], 'app')) {
            if (!isMobile()) {
                if ($this->yzShopSet['ispc'] == 0) {
                    $_var_6 = 'mobile';
                }
            }
        }
        $_var_7 = IA_ROOT . '/addons/sz_yi/';
        if (defined('IN_SYS')) {
            $_var_8 = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . "/template/{$_var_4}.html";
            $_var_9 = IA_ROOT . "/data/tpl/web/{$_W['template']}/sz_yi/plugin/" . $this->pluginname . "/{$_var_4}.tpl.php";
            if (!is_file($_var_8)) {
                $_var_8 = IA_ROOT . "/addons/sz_yi/template/{$_var_4}.html";
                $_var_9 = IA_ROOT . "/data/tpl/web/{$_W['template']}/sz_yi/{$_var_4}.tpl.php";
            }
            if (!is_file($_var_8)) {
                $_var_8 = IA_ROOT . "/web/themes/{$_W['template']}/{$_var_4}.html";
                $_var_9 = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$_var_4}.tpl.php";
            }
            if (!is_file($_var_8)) {
                $_var_8 = IA_ROOT . "/web/themes/default/{$_var_4}.html";
                $_var_9 = IA_ROOT . "/data/tpl/web/default/{$_var_4}.tpl.php";
            }
        } else {
            $_var_10 = m('cache')->getString('template_shop');
            if (empty($_var_10)) {
                $_var_10 = 'default';
            }
            if (!is_dir(IA_ROOT . "/addons/sz_yi/template/{$_var_6}/" . $_var_10)) {
                $_var_10 = 'default';
            }
            $_var_11 = m('cache')->getString('template_' . $this->pluginname);
            if (empty($_var_11)) {
                $_var_11 = 'default';
            }
            if (!is_dir(IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . "/template/{$_var_6}/" . $_var_11)) {
                $_var_11 = 'default';
            }
            $_var_9 = IA_ROOT . '/data/app/sz_yi/plugin/' . $this->pluginname . "/{$_var_11}/{$_var_6}/{$_var_4}.tpl.php";
            $_var_8 = $_var_7 . '/plugin/' . $this->pluginname . "/template/{$_var_6}/{$_var_11}/{$_var_4}.html";
            if (!is_file($_var_8)) {
                $_var_8 = $_var_7 . '/plugin/' . $this->pluginname . "/template/{$_var_6}/default/{$_var_4}.html";
                $_var_9 = IA_ROOT . '/data/app/sz_yi/plugin/' . $this->pluginname . "/default/{$_var_6}/{$_var_4}.tpl.php";
            }
            if (!is_file($_var_8)) {
                $_var_8 = $_var_7 . "/template/{$_var_6}/{$_var_10}/{$_var_4}.html";
                $_var_9 = IA_ROOT . "/data/app/sz_yi/{$_var_10}/{$_var_4}.tpl.php";
            }
            if (!is_file($_var_8)) {
                $_var_8 = $_var_7 . "/template/{$_var_6}/default/{$_var_4}.html";
                $_var_9 = IA_ROOT . "/data/app/sz_yi/default/{$_var_4}.tpl.php";
            }
            if (!is_file($_var_8)) {
                $_var_8 = $_var_7 . "/template/{$_var_6}/{$_var_4}.html";
                $_var_9 = IA_ROOT . "/data/app/sz_yi/{$_var_4}.tpl.php";
            }
            if (!is_file($_var_8)) {
                $_var_12 = explode('/', $_var_4);
                $_var_13 = $_var_12[0];
                $_var_14 = m('cache')->getString('template_' . $_var_13);
                if (empty($_var_14)) {
                    $_var_14 = 'default';
                }
                if (!is_dir(IA_ROOT . '/addons/sz_yi/plugin/' . $_var_13 . "/template/{$_var_6}/" . $_var_14)) {
                    $_var_14 = 'default';
                }
                $_var_15 = $_var_12[1];
                $_var_8 = IA_ROOT . '/addons/sz_yi/plugin/' . $_var_13 . "/template/{$_var_6}/" . $_var_14 . "/{$_var_15}.html";
            }
        }
        if (!is_file($_var_8)) {
            die("Error: template source '{$_var_4}' is not exist!");
        }
        if (DEVELOPMENT || !is_file($_var_9) || filemtime($_var_8) > filemtime($_var_9)) {
            shop_template_compile($_var_8, $_var_9, true);
        }
        return $_var_9;
    }

    public function _exec_plugin($_var_16, $_var_17 = true)
    {
        global $_GPC;
        if ($_var_17) {
            $_var_18 = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/core/web/' . $_var_16 . '.php';
        } else {
            $_var_18 = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/core/mobile/' . $_var_16 . '.php';
        }
        if (!is_file($_var_18)) {
            message("未找到控制器文件 : {$_var_18}");
        }
        include $_var_18;
        die;
    }
}
