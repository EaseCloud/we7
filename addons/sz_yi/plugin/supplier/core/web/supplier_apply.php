<?php
global $_W, $_GPC;
$operation   = empty($_GPC['op']) ? 'display' : $_GPC['op'];
if ($operation == 'display') {
	$where = '';
	if(!empty($_GPC['uid'])){
		$where .= ' and p.uid=' . $_GPC['uid'];
	}
	if(!empty($_GPC['applysn'])){
		$where .= ' and a.applysn=' . $_GPC['applysn'];
	} 

    //修复p.*问题, 直接p.*和a.* id会有冲突,字段名也不对，没有telephone. By RainYang
	$list = pdo_fetchall('select a.*,p.accountname, mobile as telephone, accountbank, banknumber   from ' . tablename('sz_yi_supplier_apply') . ' a left join ' . tablename('sz_yi_perm_user') . ' p on p.uid=a.uid where a.status=0 and p.uniacid=' . $_W['uniacid'] . $where);
    $total = count($list);
} else if ($operation == 'detail') {
	$id = intval($_GPC['id']);
	if(!empty($id)){
		$set     = m('common')->getSysset('shop');
		$apply = pdo_fetch('select * from ' . tablename('sz_yi_supplier_apply') . ' where id = '.$id);
		$openid = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_perm_user') . ' where uid=:uid and uniacid=:uniacid',array(':uid' => $apply['uid'],':uniacid'=> $_W['uniacid']));
		if($apply['type'] == 2){
			$result = m('finance')->pay($openid, 1, $apply['apply_money'] * 100, $apply['applysn'], $set['name'] . '供应商提现');
			if (is_error($result)) {
                message('微信钱包提现失败: ' . $result['message'], '', 'error');
            }
            m('notice')->sendMemberLogMessage($apply['id']);
		}
		$data = array(
			'status' => 1,
			'finish_time' => time()
		);
		pdo_update('sz_yi_supplier_apply', $data, array(
				'id' => $id
			));
		$msg = $apply['type'] == 1 ? '手动打款成功' : '提现到微信钱包成功!';
		p('supplier')->sendMessage($openid, array('money' => $applyp['apply_money'], 'type' => $apply['type'] == 1 ? '微信' : '银行卡'), TM_SUPPLIER_PAY);
		message($msg, $this->createPluginWebUrl('supplier/supplier_apply'), 'success');
	}
}
load()->func('tpl');
include $this->template('supplier_apply');
