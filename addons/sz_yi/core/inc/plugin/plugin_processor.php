<?php
/*=============================================================================
#     FileName: plugin_processor.php
#         Desc: 
#       Author: Yunzhong - http://www.yunzshop.com
#        Email: 913768135@qq.com
#     HomePage: http://www.yunzshop.com
#      Version: 0.0.1
#   LastChange: 2016-02-05 02:10:19
#      History:
=============================================================================*/

if (!defined('IN_IA')) {
    exit('Access Denied');
}
require IA_ROOT . '/addons/sz_yi/defines.php';
class PluginProcessor extends WeModuleProcessor
{
    public $model;
    public $modulename;
    public $message;
    public function __construct($name = '')
    {
        $this->modulename = 'sz_yi';
        $this->pluginname = $name;
        $this->loadModel();
    }
    private function loadModel()
    {
        $modelfile = IA_ROOT . '/addons/' . $this->modulename . "/plugin/" . $this->pluginname . "/model.php";
        if (is_file($modelfile)) {
            $classname = ucfirst($this->pluginname) . "Model";
            require $modelfile;
            $this->model = new $classname($this->pluginname);
        }
    }
    public function respond()
    {
        $this->message = $this->message;
    }
}
