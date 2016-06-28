<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';

class PluginProcessor extends WeModuleProcessor
{
    public $model;
    public $modulename;
    public $message;

    public function __construct($_var_0 = '')
    {
        $this->modulename = 'sz_yi';
        $this->pluginname = $_var_0;
        $this->loadModel();
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

    public function respond()
    {
        $this->message = $this->message;
    }
}