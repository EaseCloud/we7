<?php

//decode by QQ:270656184 http://www.yunlu99.com/
if (!defined('IN_IA')) {
    die('Access Denied');
}

class PluginModel
{
    private $pluginname;

    public function __construct($_var_0 = '')
    {
        $this->pluginname = $_var_0;
    }

    public function getSet()
    {
        global $_W, $_GPC;
        $_var_1 = m('common')->getSetData();
        $_var_2 = iunserializer($_var_1['plugins']);
        if (is_array($_var_2) && isset($_var_2[$this->pluginname])) {
            return $_var_2[$this->pluginname];
        }
        return array();
    }

    public function updateSet($_var_3 = array())
    {
        global $_W;
        $_var_4 = $_W['uniacid'];
        $_var_1 = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_var_4));
        if (empty($_var_1)) {
            pdo_insert('sz_yi_sysset', array('uniacid' => $_var_4, 'sets' => iserializer(array()), 'plugins' => iserializer(array($this->pluginname => $_var_3))));
        } else {
            $_var_5 = unserialize($_var_1['plugins']);
            $_var_5[$this->pluginname] = $_var_3;
            pdo_update('sz_yi_sysset', array('plugins' => iserializer($_var_5)), array('uniacid' => $_var_4));
        }
        $_var_1 = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_var_4));
        m('cache')->set('sysset', $_var_1);
    }

    function getName()
    {
        return pdo_fetchcolumn('select name from ' . tablename('sz_yi_plugin') . ' where identity=:identity limit 1', array(':identity' => $this->pluginname));
    }
}

