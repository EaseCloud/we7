<?php

class duobao extends Feng_duobaoModuleSite{

public function payResult($params){
		global $_W, $_GPC;

		$uniacid=$_W['uniacid'];
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => '1', 'wechat' => '3', 'alipay' => '2');
		$data['paytype'] = $paytype[$params['type']];
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		
		if ($params['from'] == 'return') {
			$order = pdo_fetch("SELECT * FROM " . tablename('feng_record') . " WHERE id ='{$params['tid']}'");//获取商品ID
			if ($order['status'] != 1) {
				if ($params['result'] == 'success') {
					$data['status'] = 1;
					$codes = pdo_fetch("SELECT * FROM " . tablename('feng_goodscodes') . " WHERE s_id ='{$order['sid']}'");//获取商品code
					$sidm = pdo_fetch("SELECT * FROM " . tablename('feng_goodslist') . " WHERE id ='{$order['sid']}'");//获取商品详情
					$s_codes=unserialize($codes['s_codes']);//转换商品code
					$c_number=intval($codes['s_len']);;
					if ($fee<$c_number) {
						//计算购买的夺宝码
						$data['s_codes']=array_slice($s_codes,0,$fee);
						$data['s_codes']=serialize($data['s_codes']);
						$r_codes['s_len']=$c_number-$fee;
						$r_codes['s_codes']=array_slice($s_codes,$fee,$r_codes['s_len']);
						$r_codes['s_codes']=serialize($r_codes['s_codes']);
						$sid_mess['canyurenshu']=$sidm['canyurenshu']+$fee;
						$sid_mess['shengyurenshu']=$sidm['shengyurenshu']-$fee;
						$sid_mess['scale']=round(($sid_mess['canyurenshu'] / $sidm['zongrenshu'])*100);

						//执行数据库更新
						pdo_update('feng_goodscodes', $r_codes, array('id' => $codes['id']));
						pdo_update('feng_goodslist', $sid_mess, array('id' => $sidm['id']));
					}elseif ($fee==$c_number) {
						$data['s_codes']=$codes['s_codes'];
						/*$data['s_codes']=serialize($data['s_codes']);*/
						$r_codes['s_len']=0;
						$r_codes['s_codes']=NULL;

						//计算获奖的code和获奖人
						$s_record = pdo_fetchall("SELECT * FROM " . tablename('feng_record') . " WHERE uniacid = '{$_W['uniacid']}' and sid ='{$order['sid']}'");//获取商品所有交易记录
						$wincode=mt_rand(1,$sidm['zongrenshu']);
						$wincode=$wincode+1000000;
						foreach ($s_record as $value) {
							$ss_codes=unserialize($value['s_codes']);//转换商品code
							for ($i=0; $i < count($ss_codes) ; $i++) { 
								if ($ss_codes[$i]==$wincode) {
									$sid_mess['q_user']=$value['from_user'];
								}
							}
						}
						$sid_mess['canyurenshu']=$sidm['zongrenshu'];
						$sid_mess['shengyurenshu']=0;
						$sid_mess['q_user_code']=$wincode;
						$pro_m = pdo_fetch("SELECT * FROM " . tablename('feng_member') . " WHERE uniacid = '{$_W['uniacid']}' and from_user ='{$_W['fans']['from_user']}'");//用户信息
						$sid_mess['q_uid']=$pro_m['nickname'];
						$sid_mess['q_user']=$_W['fans']['from_user'];
						$sid_mess['status']=1;
						$sid_mess['q_end_time']=TIMESTAMP;
						$sid_mess['scale']=100;

						//模板消息推送
						if ($_W['account']['level']==2) {
						$template_mess='{
							           "touser":"oU5agjqc9O9srg-xC4uruYy_MKIQ",
							           "template_id":"bkf4QYcnsqLTG7Y5FgmbGQ66ZyYUVCTPN84cBdY1Xgg",
							           "url":"",
							           "topcolor":"#FF0000",
							           "data":{
							                   "title": {
							                       "value":"尊敬的客户",
							                       "color":"#173177"
							                   },
							                   "headinfo":{
							                       "value":"恭喜您，中奖啦！",
							                       "color":"#FF0000"
							                   },
							                   "program": {
							                       "value":"一元夺宝",
							                       "color":"#FF0000"
							                   },
							                   "result": {
							                       "value":"获得了我们的大奖",
							                       "color":"#FF0000"
							                   },
							                   "remark":{
							                       "value":"请进入个人中心查看中奖详情，祝你生活愉快！",
							                       "color":"#173177"
							                   }
							           }
						       		}';

						$this->send_template_message($template_mess);
						}


						//生成新一期商品
						if ($sidm['periods']<=$sidm['maxperiods']) {
							$new_sid=array(
								'uniacid'=>$_W['uniacid'],
								'sid'=>$sidm['sid'],
								'title'=>$sidm['title'],
								'price'=>$sidm['price'],
								'zongrenshu'=>$sidm['zongrenshu'],
								'canyurenshu'=>0,
								'shengyurenshu'=>$sidm['zongrenshu'],
								'periods'=>$sidm['periods']+1,
								'maxperiods'=>$sidm['maxperiods'],
								'picarr'=>$sidm['picarr'],
								'content'=>$sidm['content'],
								'createtime'=>TIMESTAMP,
								'pos'=>$sidm['pos'],
								'status'=>$sidm['status'],
							);
							pdo_insert(feng_goodslist,$new_sid);
							$id = pdo_insertid();

							$CountNum=intval($sidm['price']);
							$new_codes=array();
							for($i=1;$i<=$CountNum;$i++){
								$new_codes[$i]=1000000+$i;
							}shuffle($new_codes);$new_codes=serialize($new_codes);

							$data1['uniacid'] = $_W['uniacid'];
							$data1['s_id'] = $id;
							$data1['s_len'] = $CountNum;
							$data1['s_codes'] = $new_codes;
							$data1['s_codes_tmp'] = $new_codes;

							$ret = pdo_insert(feng_goodscodes, $data1);
							unset($new_codes);
						
						}

						//执行数据库操作
						pdo_update('feng_goodscodes', $r_codes, array('id' => $codes['id']));
						pdo_update('feng_goodslist', $sid_mess, array('id' => $sidm['id']));
					}else{
						message('错误！');
					}
				}

				pdo_update('feng_record', $data, array('id' => $params['tid']));
			}
			
			if ($params['type'] == $credit) {
				message('支付成功！', $this->createMobileUrl('myorder'), 'success');
			} else {
				message('支付成功！', '../../app/' . $this->createMobileUrl('myorder'), 'success');
			}
		}
	}
}
?>