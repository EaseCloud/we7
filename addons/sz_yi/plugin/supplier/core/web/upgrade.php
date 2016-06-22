<?php
//金额不能用int, apply表少uniacid字段
global $_W;
$sql = "
CREATE TABLE IF NOT EXISTS `ims_sz_yi_af_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(255) CHARACTER SET utf8 NOT NULL,
  `uniacid` int(11) NOT NULL,
  `realname` varchar(55) CHARACTER SET utf8 NOT NULL,
  `mobile` varchar(255) CHARACTER SET utf8 NOT NULL,
  `weixin` varchar(255) CHARACTER SET utf8 NOT NULL,
  `productname` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `ims_sz_yi_supplier_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '供应商id',
  `uniacid` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '1手动2微信',
  `applysn` varchar(255) NOT NULL COMMENT '提现单号',
  `apply_money` int(11) NOT NULL COMMENT '申请金额',
  `apply_time` int(11) NOT NULL COMMENT '申请时间',
  `status` tinyint(3) NOT NULL COMMENT '0为申请状态1为完成状态',
  `finish_time` int(11) NOT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;";
pdo_query($sql);
if(!pdo_fieldexists('sz_yi_perm_user', 'banknumber')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_perm_user')." ADD `banknumber` varchar(255) NOT NULL COMMENT '银行卡号';");
}
if(!pdo_fieldexists('sz_yi_perm_user', 'accountname')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_perm_user')." ADD `accountname` varchar(255) NOT NULL COMMENT '开户名';");
}
if(!pdo_fieldexists('sz_yi_perm_user', 'accountbank')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_perm_user')." ADD `accountbank` varchar(255) NOT NULL COMMENT '开户行';");
}

if(!pdo_fieldexists('sz_yi_goods', 'supplier_uid')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_goods')." ADD `supplier_uid` INT NOT NULL COMMENT '供应商ID';");
}
if(!pdo_fieldexists('sz_yi_order', 'supplier_uid')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_order')." ADD `supplier_uid` INT NOT NULL COMMENT '供应商ID';");
}
if(!pdo_fieldexists('sz_yi_order_goods', 'supplier_uid')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_order_goods')." ADD `supplier_uid` INT NOT NULL COMMENT '供应商ID';");
}
if(!pdo_fieldexists('sz_yi_order_goods', 'supplier_apply_status')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_order_goods')." ADD `supplier_apply_status` tinyint(4) NOT NULL COMMENT '1为供应商已提现';");
}
if(!pdo_fieldexists('sz_yi_af_supplier', 'id')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_af_supplier')." ADD PRIMARY KEY (`id`);");
}
if(!pdo_fieldexists('sz_yi_supplier_apply', 'id')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_supplier_apply')." ADD PRIMARY KEY (`id`);");
}
if(!pdo_fieldexists('sz_yi_af_supplier', 'id')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_af_supplier')." MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;");
}
if(!pdo_fieldexists('sz_yi_supplier_apply', 'id')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_supplier_apply')." MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('sz_yi_perm_role', 'status1')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_perm_role')." ADD `status1` tinyint(3) NOT NULL COMMENT '1：供应商开启';");
}
if(!pdo_fieldexists('sz_yi_perm_user', 'openid')) {
  pdo_query("ALTER TABLE ".tablename('sz_yi_perm_user')." ADD `openid` VARCHAR( 255 ) NOT NULL;");
}


$info = pdo_fetch('select * from ' . tablename('sz_yi_plugin') . ' where identity= "supplier"  order by id desc limit 1');

if(!$info){
    $sql = "INSERT INTO " . tablename('sz_yi_plugin'). " (`displayorder`, `identity`, `name`, `version`, `author`, `status`, `category`) VALUES(0, 'supplier', '供应商', '1.0', '官方', 1, 'biz');";
    pdo_query($sql);
}

//todo,这里缺少uniacid，我没加，要测试$_W['uniacid']是否可用
$result = pdo_fetch('select * from ' . tablename('sz_yi_perm_role') . ' where status1=1');
if(empty($result)){
  $sql = "
INSERT INTO " . tablename('sz_yi_perm_role') . " (`rolename`, `status`, `status1`, `perms`, `deleted`) VALUES
('供应商', 1, 1, 'shop,shop.goods,shop.goods.view,shop.goods.add,shop.goods.edit,shop.goods.delete,order,order.view,order.view.status_1,order.view.status0,order.view.status1,order.view.status2,order.view.status3,order.view.status4,order.view.status5,order.view.status9,order.op,order.op.send,order.op.sendcancel,order.op.verify,order.op.fetch,order.op.close,order.op.refund,order.op.export,order.op.changeprice', 0);";
pdo_query($sql);
}else{
  $gysdata = array("perms" => 'shop,shop.goods,shop.goods.view,shop.goods.add,shop.goods.edit,shop.goods.delete,order,order.view,order.view.status_1,order.view.status0,order.view.status1,order.view.status2,order.view.status3,order.view.status4,order.view.status5,order.view.status9,order.op,order.op.send,order.op.sendcancel,order.op.verify,order.op.fetch,order.op.close,order.op.refund,order.op.export,order.op.changeprice');
  pdo_update('sz_yi_perm_role', $gysdata, array('rolename' => "供应商"));
}

message('供应商插件安装成功', $this->createPluginWebUrl('supplier/supplier'), 'success');
