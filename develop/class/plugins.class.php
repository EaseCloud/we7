<?php

/**
 * 芸众商城插件管理
 * Class EcPluginsManage
 * $plugins：ims_sz_yi_plugin 表中的插件信息
 * $plugins_copy：ims_sz_yi_plugin_copy 表中的插件信息
 * 与之芸众商城现有插件：
 * + qiniu - 七牛存储
 * + taobao - 淘宝助手
 * + commission - 芸众分销
 * + poster - 超级海报
 * + verify - O2O 核销
 * + perm - 分权系统
 * + sale - 营销宝
 * + tmessage - 会员群发
 * + designer - 店铺装修
 * + creditshop - 积分商城
 * + virtual - 虚拟物品
 * + article - 文章营销
 * + coupon - 超级劵
 * + postera - 活动海报
 * + exhelper - 快递助手
 * + yunpay - 云支付
 * + supplier - 供应商
 * + diyform - 自定义表单
 * + system - 系统工具
 * + bonus - 芸众分红
 */
class EcPluginsManage extends EaseCloud
{
    // ims_sz_yi_plugin 表中的信息
    public $plugins;
    // ims_sz_yi_plugin_copy 表中的信息
    public $plugins_copy;

    /**
     * 获取 $plugins、$plugins_copy
     * EcPluginsManage constructor.
     */
    function __construct()
    {
        $this->plugins = pdo_fetchall(
            "SELECT * FROM " . tablename('sz_yi_plugin')
        );
        $this->plugins_copy = pdo_fetchall(
            "SELECT * FROM " . tablename('sz_yi_plugin_copy')
        );
    }

    /**
     * 获取 ims_sz_yi_plugin 表中最大的 displayorder 的值
     * @return int
     */
    function getMaxDisplayOrder()
    {
        // 获取 ims_sz_yi_plugin 表中最大的 displayorder
        $max_displayorder = 1;
        foreach ($this->plugins as $item) {
            $max_displayorder = $item['displayorder'] > $max_displayorder
                ? $item['displayorder'] : $max_displayorder;
        }
        return $max_displayorder;
    }

    /**
     * 获取 ims_sz_yi_plugin / ims_sz_yi_plugin_copy 表中对应插件的信息
     * @param $identity string|array 插件的身份标识，表中 identity 字段的值
     * @param string $type on|string on，返回 ims_sz_yi_plugin 中对应插件的信息
     * @return array
     */
    function getPlugin($identity, $type = 'on')
    {
        $plugins = $type == 'on' ? $this->plugins : $this->plugins_copy;
        $identity = is_array($identity) ? $identity : array($identity);
        $plugin = array();
        foreach ($plugins as $item) {
            if (in_array($item['identity'], $identity)) {
                $plugin[$item['identity']] = $item;
                // 删除已经找到的 identity
                unset($identity[array_search($item['identity'], $identity)]);
            }
        }
        if (empty($plugin)) {
            message("请输入正确的插件身份标识！", '', 'error');
        }
        // plugin：查找到的插件信息；identity：没有查找到的插件标识
        $result = array(
            'plugin' => $plugin,
            'identity' => $identity,
        );
        return $result;
    }

    /**
     * 开启插件
     * 通过在 ims_sz_yi_plugin 中插入一条插件的信息来开启插件
     * @param $identity string|array 插件的身份标识，表中的 identity 字段的值
     */
    function pluginOn($identity)
    {
        // 先查看 $identity 中有哪些插件没有开启的
        $plugin_message = $this->getPlugin($identity);
        // 获取需要开启的插件的信息
        $plugin_off = $this->getPlugin($plugin_message['identity'], 'off');
        // 获取当前 ims_sz_yi_plugin 表中 displayorder 字段的最大值
        $max_displayorder = $this->getMaxDisplayOrder();
        // 开启插件
        foreach ($plugin_off['plugin'] as $item) {
            ++$max_displayorder;
            pdo_insert('sz_yi_plugin', array(
                'displayorder' => $max_displayorder,
                'identity' => $item['identity'],
                'name' => $item['name'],
                'version' => $item['version'],
                'author' => $item['author'],
                'status' => $item['status'],
                'category' => $item['category'],
            ));
        }
        // 检查输入的插件身份标识是否有无法开启的
        if ($plugin_off['identity']) {
            $error_identity = implode('、', $plugin_off['identity']);
            message("开启失败的插件标识：{$error_identity}", '', 'error');
        }
        $this->yzCleanCache();
    }

    /**
     * 关闭插件
     * 通过删除 ims_sz_yi_plugin 中该插件的信息记录来关闭插件，
     * 并且将它保存到 ims_sz_yi_plugin_copy 表中，
     * 再次开启插件的时候可以从该表中获取插件信息
     * @param $identity
     */
    function pluginOff($identity)
    {
        // 先查看有哪些 $identity 的插件是开启的
        $plugin_on = $this->getPlugin($identity);
        // 查看 ims_sz_yi_plugin_copy 表中是否记录了这些要关闭的插件的信息
        $plugin_copy = $this->getPlugin($identity, 'off');
        foreach ($plugin_on['plugin'] as $item) {
            $plugin_data = array(
                'displayorder' => $item['displayorder'],
                'identity' => $item['identity'],
                'name' => $item['name'],
                'version' => $item['version'],
                'author' => $item['author'],
                'status' => $item['status'],
                'category' => $item['category'],
            );
            // 将要关闭的插件的信息插入或者更新到 ims_sz_yi_plugin_copy 表中
            if (in_array($item['identity'], $plugin_copy['identity'])) {
                pdo_insert('sz_yi_plugin_copy', $plugin_data);
            } else {
                pdo_update('sz_yi_plugin_copy', $plugin_data, array(
                    'identity' => $item['identity'],
                ));
            }
            // 关闭插件，即删除 ims_sz_yi_plugin 表中该插件的信息
            pdo_delete('sz_yi_plugin', array(
                'identity' => $item['identity'],
            ));
        }
        // 检查输入的插件身份标识是否有无法关闭的
        if ($plugin_on['identity']) {
            $error_identity = implode('、', $plugin_on['identity']);
            message("关闭失败的插件标识：{$error_identity}", '', 'error');
        }
        $this->yzCleanCache();
    }

}

