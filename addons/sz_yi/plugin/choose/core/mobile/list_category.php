<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$openid    = m('user')->getOpenid();
$uniacid   = $_W['uniacid'];
$pageid=$_GPC['pageid'];
$page=pdo_fetch('select * from '.tablename('sz_yi_chooseagent') . ' where uniacid=:uniacid and id=:id ',array(':id'=>$pageid,':uniacid'=>$uniacid));
if($page['isopen']==1){
	$sup_uid=$page['uid'];	
}else{
	$sup_uid='';
	if($page['pcate']!=''){
		$pcate=$page['pcate'];	
		if($page['ccate']!=''){
			$ccate=$page['ccate'];
		}
		if($page['tcate']!=''){
			$tcate=$page['tcate'];
		}
	}
	
}

// $pageid=$_GPC['pageid'];
// $page=pdo_fetch('select * from '.tablename('sz_yi_chooseagent'). ' where id=:id and uniacid=:uniacid',array(':uniacid'=>$_W['uniacid'],':id'=>$pageid));
if ($operation == 'category') {
	if (!empty($_GPC['level'])) {

		
		
		if($page['isopen']==1){//判断是否开启供应商


		    $parent_category = pdo_fetchall("select a.id,a.parentid,a.name,a.level from " . tablename('sz_yi_category') . " a left join  " .tablename('sz_yi_goods'). " b on (a.id = b.pcate )  where a.parentid=0 and a.uniacid=:uniacid and b.supplier_uid = '".$sup_uid."' group by a.id ", array(
			    ':uniacid' => $_W['uniacid'] 
			));
			
			foreach ($parent_category as $v){
				$ids[] = $v['id'];
			}
			$sql = 'select a.id,a.parentid,a.name,a.level from ' . tablename('sz_yi_category') . ' a left join  ' .tablename('sz_yi_goods'). ' b on a.id = b.ccate where a.parentid in('.implode(',',$ids).') and a.uniacid=:uniacid and  b.uniacid=:uniacid and b.supplier_uid = "'.$sup_uid.'" group by a.id ';
			$children_category = pdo_fetchall($sql, array(
			    ':uniacid' => $_W['uniacid']
			));

			foreach ($children_category as $v1){
				$ids1[] = $v1['id'];
			}
			$sql1 = 'select a.id,a.parentid,a.name,a.level from ' . tablename('sz_yi_category') . ' a left join  ' .tablename('sz_yi_goods'). ' b on a.id = b.tcate where a.parentid in('.implode(',',$ids1).') and a.uniacid=:uniacid and  b.uniacid=:uniacid and b.supplier_uid = "'.$sup_uid.'" group by a.id ';
			$third_category = pdo_fetchall($sql1, array(
			    ':uniacid' => $_W['uniacid']
			));

		}else{
			if(!empty($page['tcate'])){

			    $parent_category = pdo_fetchall('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and id=:id and parentid=0  ', array(':uniacid'=>$uniacid,':id'=>$page['pcate']));
				
				$sql = 'select id,parentid,name,level from ' . tablename('sz_yi_category') .' where uniacid=:uniacid and id=:id and parentid=:parentid ' ;
				$children_category = pdo_fetchall($sql, array(':uniacid'=>$uniacid,':id'=>$page['ccate'],':parentid'=>$page['pcate']));

				$sql1 = 'select id,parentid,name,level from ' . tablename('sz_yi_category') .' where uniacid=:uniacid and id=:id and parentid=:parentid ' ;
				$third_category = pdo_fetchall($sql1, array(':uniacid'=>$uniacid,':id'=>$page['tcate'],':parentid'=>$page['ccate']));
				
			}else if(!empty($page['ccate'])){

			    $parent_category = pdo_fetchall('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and id=:id and parentid=0  ',array(':uniacid'=>$uniacid,':id'=>$page['pcate']));
				
				$sql = 'select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and id=:id and parentid = :parentid ';
				$children_category = pdo_fetchall($sql, array(':uniacid'=>$uniacid,':id'=>$page['ccate'],':parentid'=>$page['pcate']));

				$sql1 = 'select id,parentid,name,level from ' . tablename('sz_yi_category') .' where uniacid=:uniacid  and parentid=:parentid ' ;
				$third_category = pdo_fetchall($sql1, array(':uniacid'=>$uniacid,':parentid'=>$page['ccate']));					

			}else if(!empty($page['pcate'])){

			    $parent_category = pdo_fetchall('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and id=:id and parentid=0  ',array(':uniacid'=>$uniacid,':id'=>$page['pcate']));

				$sql = 'select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and parentid = :parentid ' ;
				$children_category = pdo_fetchall($sql, array(':uniacid'=>$uniacid,':parentid'=>$page['pcate']));

				foreach ($children_category as $v){
					$ids[] = $v['id'];
				}
				$sql1 = 'select id,parentid,name,level from ' . tablename('sz_yi_category') .' where uniacid=:uniacid  and parentid in('.implode(',',$ids).') ' ;
				$third_category = pdo_fetchall($sql1, array(':uniacid'=>$uniacid));					


			}else{
			    $parent_category = pdo_fetchall('select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and parentid=0  ',array(':uniacid'=>$uniacid));
				
				foreach ($parent_category as $v){
					$ids[] = $v['id'];
				}
				$sql = 'select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and parentid in ('.implode(',',$ids).') ' ;
				$children_category = pdo_fetchall($sql, array(':uniacid'=>$uniacid));	

				foreach ($children_category as $v1){
					$ids1[] = $v1['id'];
				}
				$sql1 = 'select id,parentid,name,level from ' . tablename('sz_yi_category') . ' where uniacid=:uniacid and parentid in ('.implode(',',$ids1).') ' ;
				$third_category = pdo_fetchall($sql1, array(':uniacid'=>$uniacid));								
			}
		}
		
		
		// foreach ($parent_category as $k => $v) {
		// 	foreach ($children_category as $k1 => $v1) {
		// 		if($v['id']==$v1['parentid']){
		// 			$parent_category[$k]['sub'][] = $v1;
		// 		}
		// 	}
		// }
		
	/*    $children_category = pdo_fetchall('select a.id,a.parentid,a.name,a.level from ' . tablename('sz_yi_category') . ' a left join  ' .tablename('sz_yi_goods'). ' b on a.id = b.tcate where a.parentid in('.implode(',',$ids).') and a.uniacid=:uniacid and b.supplier_uid = :supplier_uid and b.uniacid=:uniacid', array(
		    ':uniacid' => $_W['uniacid'],
		    ':supplier_uid'=> $sup_uid
		));*/

		foreach ($parent_category as $key => $category) {
			foreach ($children_category as $k1 => $v1) {

				if($category['id']==$v1['parentid']){
					
						$parent_category[$key]['sub'][$k1] = $v1;
						foreach($third_category as $k2 => $v2){
							if($v1['id']==$v2['parentid']){
								$parent_category[$key]['sub'][$k1]['sub1'][$k2] = $v2;
							}
						}
					
				}	
			}

			$args = array(           
            'ccate' => $category['id'],
            'supplier_uid'=>$sup_uid
	        );
	        $goods    = m('goods')->getList($args);

	        $conut = 0;
	        foreach ($goods as $key => $good) {
	        	$cartcount = pdo_fetchcolumn('select sum(total) from ' . tablename('sz_yi_member_cart') . ' where openid=:openid and deleted=0 and uniacid=:uniacid and goodsid = :goodsid limit 1', array(
		            ':uniacid' => $_W['uniacid'],
		            'goodsid' => $good['id'],
		            ':openid' => $openid
		        ));

		        $conut = $cartcount + $conut;
	        }

	        $parent_category[$key]['count'] = $conut;

		}

		show_json(1, array('category' => $parent_category,'current_category' => $current_category));

	}else{
		$category = m('shop')->getCategory();
		show_json(1, array('category' => $category));
	}
} 