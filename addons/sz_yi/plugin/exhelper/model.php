<?php

if (!defined('IN_IA')) {
    die('Access Denied');
}
if (!class_exists('ExhelperModel')) {
    class ExhelperModel extends PluginModel
    {
        function perms()
        {
            return array('exhelper' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('print' => array('text' => '打印', 'single' => '单个打印-log', 'more' => '批量打印-log'), 'exptemp1' => array('text' => '快递单模版管理', 'view' => '查看', 'add' => '添加-log', 'edit' => '编辑-log', 'delete' => '删除-log', 'setdefault' => '设为默认-log'), 'exptemp2' => array('text' => '发货单模版管理', 'view' => '查看', 'add' => '添加-log', 'edit' => '编辑-log', 'delete' => '删除-log', 'setdefault' => '设为默认-log'), 'senduser' => array('text' => '发货人信息管理', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log', 'setdefault' => '设为默认-log'), 'short' => array('text' => '商品简称', 'view' => '浏览', 'save' => '修改-log'), 'printset' => array('text' => '打印设置', 'view' => '查看', 'save' => '修改-log'), 'dosend' => array('text' => '一键发货'))));
        }
    }
}