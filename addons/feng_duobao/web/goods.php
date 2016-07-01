<?php
	$ops = array('display', 'edit', 'delete'); // 只支持此 3 种操作.
	$op = in_array($_GPC['op'], $ops) ? $_GPC['op'] : 'display';
	//商品列表显示
	if($op == 'display'){
		$uniacid=$_W['uniacid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		$goodses = pdo_fetchall("SELECT * FROM ".tablename('feng_goodslist')." WHERE uniacid = '{$uniacid}' and status =2 $condition ORDER BY sid DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('feng_goodslist') . " WHERE uniacid = '{$uniacid}' and status =2 $condition");
		$pager = pagination($total, $pindex, $psize);

		include $this->template('goods_display');
	}
	//商品编辑
	if ($op == 'edit') {
		
		$id = intval($_GPC['id']);
		if(!empty($id)){
			$sql = 'SELECT * FROM '.tablename('feng_goodslist').' WHERE id=:id AND uniacid=:uniacid LIMIT 1';
			$params = array(':id'=>$id, ':uniacid'=>$_W['uniacid']);
			$goods = pdo_fetch($sql, $params);
			
			if(empty($goods)){
				message('未找到指定的商品.', $this->createWebUrl('goods'));
			}
		}
		
		if (checksubmit()) {
			$data = $_GPC['goods']; // 获取打包值
			
			empty($data['title']) && message('请填写商品标题');
			empty($data['picarr']) && message('请上传商品图片');
			empty($data['maxperiods']) && message('请填写商品期数');
			empty($data['content']) && message('请填写商品详情');
			
			if(empty($goods)){
				empty($data['price']) && message('请填写商品价格');
				$data['uniacid'] = $_W['uniacid'];
				$data['zongrenshu'] = $data['price'];
				$data['shengyurenshu'] = $data['price'];
				$data['createtime'] = TIMESTAMP;
				$data['periods'] = 1;
				
				$ret = pdo_insert(feng_goodslist, $data);
				if (!empty($ret)) {
					$id = pdo_insertid();
					$sid['sid']=1+$id;
					$ret = pdo_update(feng_goodslist, $sid, array('id'=>$id));
				}
				//夺宝码计算
				if(!empty($ret)){
					$CountNum=intval($data['price']);
					$codes=array();
					for($i=1;$i<=$CountNum;$i++){
						$codes[$i]=1000000+$i;
					}shuffle($codes);$codes=serialize($codes);

					$data1['uniacid'] = $_W['uniacid'];
					$data1['s_id'] = $id;
					$data1['s_len'] = $CountNum;
					$data1['s_codes'] = $codes;
					$data1['s_codes_tmp'] = $codes;

					$ret = pdo_insert(feng_goodscodes, $data1);
					unset($codes);
				}

			} else {
				$ret = pdo_update(feng_goodslist, $data, array('id'=>$id));
			}
			
			if (!empty($ret)) {
				message('商品信息保存成功', $this->createWebUrl('goods', array('op'=>'edit', 'id'=>$id)), 'success');
			} else {
				message('商品信息保存失败');
			}
		}
		
		include $this->template('goods_edit');
	}
	
	/*if($op == 'delete') {
		$id = intval($_GPC['id']);
		if(empty($id)){
			message('未找到指定商品分类');
		}
		$result = pdo_delete(feng_goodslist, array('id'=>$id, 'uniacid'=>$_W['uniacid']));
		if(intval($result) == 1){
			message('删除商品成功.', $this->createWebUrl('goods'), 'success');
		} else {
			message('删除商品失败.');
		}
	}*/
?>