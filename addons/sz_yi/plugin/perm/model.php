<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
if (!class_exists('PermModel')) {
	class PermModel extends PluginModel
	{
		public function allPerms()
		{
			$perms = array('shop' => array('text' => '商城管理', 'child' => array('goods' => array('text' => '商品', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'category' => array('text' => '商品分类', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'dispatch' => array('text' => '配送方式', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'adv' => array('text' => '幻灯片', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'notice' => array('text' => '公告', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'comment' => array('text' => '评价', 'view' => '浏览', 'add' => '添加评论-log', 'edit' => '回复-log', 'delete' => '删除-log'),)), 'member' => array('text' => '会员管理', 'child' => array('member' => array('text' => '会员', 'view' => '浏览', 'edit' => '修改-log', 'delete' => '删除-log', 'export' => '导出-log'), 'group' => array('text' => '会员组', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'level' => array('text' => '会员等级', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'))), 'order' => array('text' => '订单管理', 'child' => array('view' => array('text' => '浏览', 'status_1' => '浏览关闭订单', 'status0' => '浏览待付款订单', 'status1' => '浏览已付款订单', 'status2' => '浏览已发货订单', 'status3' => '浏览完成的订单', 'status4' => '浏览退货申请订单', 'status5' => '浏览已退货订单',), 'op' => array('text' => '操作', 'pay' => '确认付款-log', 'send' => '发货-log', 'sendcancel' => '取消发货-log', 'finish' => '确认收货(快递单)-log', 'verify' => '确认核销(核销单)-log', 'fetch' => '确认取货(自提单)-log', 'close' => '关闭订单-log', 'refund' => '退货处理-log', 'export' => '导出订单-log', 'changeprice' => '订单改价-log'))), 'finance' => array('text' => '财务管理', 'child' => array('recharge' => array('text' => '充值', 'view' => '浏览', 'credit1' => '充值积分-log', 'credit2' => '充值余额-log', 'refund' => '充值退款-log', 'export' => '导出充值记录-log'), 'withdraw' => array('text' => '提现', 'view' => '浏览', 'withdraw' => '提现-log', 'export' => '导出提现记录-log'), 'downloadbill' => array('text' => '下载对账单'),)), 'statistics' => array('text' => '数据统计', 'child' => array('view' => array('text' => '浏览权限', 'sale' => '销售指标', 'sale_analysis' => '销售统计', 'order' => '订单统计', 'goods' => '商品销售统计', 'goods_rank' => '商品销售排行', 'goods_trans' => '商品销售转化率', 'member_cost' => '会员消费排行', 'member_increase' => '会员增长趋势'), 'export' => array('text' => '导出', 'sale' => '导出销售统计-log', 'order' => '导出订单统计-log', 'goods' => '导出商品销售统计-log', 'goods_rank' => '导出商品销售排行-log', 'goods_trans' => '商品销售转化率-log', 'member_cost' => '会员消费排行-log'),)), 'sysset' => array('text' => '系统设置', 'child' => array('view' => array('text' => '浏览', 'shop' => '商城设置', 'follow' => '引导及分享设置', 'notice' => '模板消息设置', 'trade' => '交易设置', 'pay' => '支付方式设置', 'template' => '模板设置', 'member' => '会员设置', 'category' => '分类层级设置', 'contact' => '联系方式设置'), 'save' => array('text' => '修改', 'shop' => '修改商城设置-log', 'follow' => '修改引导及分享设置-log', 'notice' => '修改模板消息设置-log', 'trade' => '修改交易设置-log', 'pay' => '修改支付方式设置-log', 'template' => '模板设置-log', 'member' => '会员设置-log', 'category' => '分类层级设置-log', 'contact' => '联系方式设置-log'))),);
			$plugins = m('plugin')->getAll();
			foreach ($plugins as $plugin) {
				$instance = p($plugin['identity']);
				if ($instance) {
					if (method_exists($instance, 'perms')) {
						$plugin_perms = $instance->perms();
						$perms = array_merge($perms, $plugin_perms);
					}
				}
			}
			return $perms;
		}

		public function isopen($pluginname = '')
		{
			if (empty($pluginname)) {
				return false;
			}
			$plugins = m('plugin')->getAll();
			foreach ($plugins as $plugin) {
				if ($plugin['identity'] == strtolower($pluginname)) {
					if (empty($plugin['status'])) {
						return false;
					}
				}
			}
			return true;
		}

		public function check_edit($permtype = '', $item = array())
		{
			if (empty($permtype)) {
				return false;
			}
			if (!$this->check_perm($permtype)) {
				return false;
			}
			if (empty($item['id'])) {
				$add_perm = $permtype . ".add";
				if (!$this->check($add_perm)) {
					return false;
				}
				return true;
			} else {
				$edit_perm = $permtype . ".edit";
				if (!$this->check($edit_perm)) {
					return false;
				}
				return true;
			}
		}

		public function check_perm($permtypes = '')
		{
			global $_W;
			$check = true;
			if (empty($permtypes)) {
				return false;
			}
			if (!strexists($permtypes, '&') && !strexists($permtypes, '|')) {
				$check = $this->check($permtypes);
			} else if (strexists($permtypes, '&')) {
				$pts = explode('&', $permtypes);
				foreach ($pts as $pt) {
					$check = $this->check($pt);
					if (!$check) {
						break;
					}
				}
			} else if (strexists($permtypes, '|')) {
				$pts = explode('|', $permtypes);
				foreach ($pts as $pt) {
					$check = $this->check($pt);
					if ($check) {
						break;
					}
				}
			}
			return $check;
		}

		private function check($permtype = '')
		{
			global $_W, $_GPC;
			if ($_W['role'] == 'manager' || $_W['role'] == 'founder') {
				return true;
			}
			$uid = $_W['uid'];
			if (empty($permtype)) {
				return false;
			}
			$user = pdo_fetch('select u.status as userstatus,r.status as rolestatus,u.perms as userperms,r.perms as roleperms from ' . tablename('sz_yi_perm_user') . ' u ' . ' left join ' . tablename('sz_yi_perm_role') . ' r on u.roleid = r.id ' . ' where uid=:uid limit 1 ', array(':uid' => $uid));
			if (empty($user) || empty($user['userstatus']) || empty($user['rolestatus'])) {
				return false;
			}
			$role_perms = explode(',', $user['roleperms']);
			$user_perms = explode(',', $user['userperms']);
			$perms = array_merge($role_perms, $user_perms);
			if (empty($perms)) {
				return false;
			}
			$permarr = explode('.', $permtype);
			if (!in_array($permarr[0], $perms)) {
				return false;
			}
			if (isset($permarr[1]) && !in_array($permarr[0] . "." . $permarr[1], $perms)) {
				return false;
			}
			if (isset($permarr[2]) && !in_array($permarr[0] . "." . $permarr[1] . "." . $permarr[2], $perms)) {
				return false;
			}
			return true;
		}

		function check_plugin($pluginname = '')
		{
			global $_W, $_GPC;
			$permset = m('cache')->getString('permset', 'global');
			if (empty($permset)) {
				return true;
			}
			if ($_W['role'] == 'founder') {
				return true;
			}
			$isopen = $this->isopen($pluginname);
			if (!$isopen) {
				return false;
			}
			$allow = true;
			$acid = pdo_fetchcolumn("SELECT acid FROM " . tablename('account_wechats') . " WHERE `uniacid`=:uniacid LIMIT 1", array(':uniacid' => $_W['uniacid']));
			$ac_perm = pdo_fetch('select  plugins from ' . tablename('sz_yi_perm_plugin') . ' where acid=:acid limit 1', array(':acid' => $acid));
			if (!empty($ac_perm)) {
				$allow_plugins = explode(',', $ac_perm['plugins']);
				if (!in_array($pluginname, $allow_plugins)) {
					$allow = false;
				}
			} else {
				$allow = false;
			}
			if (!$allow) {
				return false;
			}
			return $this->check($pluginname);
		}

		public function getLogName($type = '', $logtypes = null)
		{
			if (!$logtypes) {
				$logtypes = $this->getLogTypes();
			}
			foreach ($logtypes as $t) {
				if ($t['value'] == $type) {
					return $t['text'];
				}
			}
			return '';
		}

		public function getLogTypes()
		{
			$types = array();
			$perms = $this->allPerms();
			foreach ($perms as $pk => $p) {
				if (isset($p['child'])) {
					foreach ($p['child'] as $ck => $child) {
						foreach ($child as $k => $v) {
							if (strexists($v, '-log')) {
								$text = str_replace("-log", "", $p['text'] . "-" . $child['text'] . "-" . $v);
								if ($k == 'text') {
									$text = str_replace("-log", "", $p['text'] . "-" . $child['text']);
								}
								$types[] = array('text' => $text, 'value' => str_replace(".text", "", $pk . "." . $ck . "." . $k));
							}
						}
					}
				} else {
					foreach ($p as $k => $v) {
						if (strexists($v, '-log')) {
							$text = str_replace("-log", "", $p['text'] . "-" . $v);
							if ($k == 'text') {
								$text = str_replace("-log", "", $p['text']);
							}
							$types[] = array('text' => $text, 'value' => str_replace(".text", "", $pk . "." . $k));
						}
					}
				}
			}
			return $types;
		}

		public function log($type = '', $op = '')
		{
			global $_W;
			static $_logtypes;
			if (!$_logtypes) {
				$_logtypes = $this->getLogTypes();
			}
			$log = array('uniacid' => $_W['uniacid'], 'uid' => $_W['uid'], 'name' => $this->getLogName($type, $_logtypes), 'type' => $type, 'op' => $op, 'ip' => CLIENT_IP, 'createtime' => time());
			pdo_insert('sz_yi_perm_log', $log);
		}

		public function perms()
		{
			return array('perm' => array('text' => $this->getName(), 'isplugin' => true, 'child' => array('set' => array('text' => '基础设置'), 'role' => array('text' => '角色', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'user' => array('text' => '操作员', 'view' => '浏览', 'add' => '添加-log', 'edit' => '修改-log', 'delete' => '删除-log'), 'log' => array('text' => '操作日志', 'view' => '浏览', 'delete' => '删除-log', 'clear' => '清除-log'),)));
		}
	}
}
