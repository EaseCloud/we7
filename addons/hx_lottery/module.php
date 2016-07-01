<?php
/**
 * 幸运大抽奖模块定义
 *
 * @author 华轩科技
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Hx_lotteryModule extends WeModule {
	public $table_reply = 'hx_lottery_reply';
	public function fieldsFormDisplay($rid = 0) {
		load()->model('mc');
		global $_W, $_GPC;
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		$creditnames = uni_setting($_W['uniacid'], array('creditnames'));
		if($creditnames) {
			foreach($creditnames['creditnames'] as $index=>$creditname) {
				if($creditname['enabled'] == 0) {
					unset($creditnames['creditnames'][$index]);
				}
			}
			$scredit = implode(', ', array_keys($creditnames['creditnames']));
		} else {
			$scredit = '';
		}
		$groups = mc_groups($_W['uniacid']);
		$couponlists = pdo_fetchall('SELECT couponid,title,type,credittype,credit,endtime,amount,dosage FROM ' . tablename('activity_coupon') . ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime ORDER BY endtime ASC ', array(':uniacid' => $_W['uniacid'], ':type' => 1, ':endtime' => TIMESTAMP));
		$tokenlists = pdo_fetchall('SELECT couponid,title,type,credittype,credit,endtime,amount,dosage FROM ' . tablename('activity_coupon') . ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime ORDER BY endtime ASC ', array(':uniacid' => $_W['uniacid'], ':type' => 2, ':endtime' => TIMESTAMP));
		$goodslists = pdo_fetchall('SELECT id,title,type,credittype,endtime,total,num,credit FROM ' . tablename('activity_exchange') . ' WHERE uniacid = :uniacid AND type = :type AND endtime > :endtime ORDER BY endtime ASC' , array(':uniacid' => $_W['uniacid'], ':type' => 3, ':endtime' => TIMESTAMP));
		//print_r($couponlists);
		load()->func('tpl');
		if($rid==0){
			$reply = array(
				'title'=> '幸运大抽奖活动开始了!',
				'description' => '幸运大抽奖活动开始啦！',
				'tips'	=>	'每次抽奖需要花费50积分，一等奖为39元的现金抵扣券，二等奖为100积分，三等奖为50积分，四等奖为30积分。每人每天限抽2次。',
				'remark'	=>	'中奖积分请到会员主页查看',
				'starttime' => time(),
				'endtime' => time() + 10 * 84400,
				'reg' => '0',
				'status' => '1',
				'awardnum'	=>	'1',
				'playnum'	=>	'5',
				'dayplaynum'=>	'1',
				'daytotalnum' => '5',
				'zfcs'		=>  '1',
				'zjcs'		=>  '1',
				'rate' => '10',
				'rate1' => '10',
				'rate2' => '10',
				'rate3' => '10',
				'rate4' => '10',
				'need_type' => 'credit1',
				'need_num' => '0',
				'give_type' => 'credit1',
				'give_num' => '0',
				'onlynone' => '1',
				'share_title'=> '欢迎参加幸运大抽奖活动',
				'share_content'=> '亲，欢迎参加幸运大抽奖活动，祝您好运哦！！ 亲，需要绑定账号才可以参加哦',
			);
			$prizes = array(
				'p1_type' => 'credit1',
				);
		}else{
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$prizes = iunserializer($reply['prizes']);
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_W,$_GPC;
		//print_r($_GPC);exit();
		$id = intval($_GPC['reply_id']);
		$p1_key = 'p1_'.$_GPC['p1_type'];
		$p2_key = 'p2_'.$_GPC['p2_type'];
		$p3_key = 'p3_'.$_GPC['p3_type'];
		$p4_key = 'p4_'.$_GPC['p4_type'];
		$data = array(
			'p1_type' => $_GPC['p1_type'],
			'p1_score' => intval($_GPC[$p1_key]),
			'p1_num' => intval($_GPC['p1_num']),
			'p1_thumb' => $_GPC['p1_thumb'],
			'p2_type' => $_GPC['p2_type'],
			'p2_score' => intval($_GPC[$p2_key]),
			'p2_num' => intval($_GPC['p2_num']),
			'p2_thumb' => $_GPC['p2_thumb'],
			'p3_type' => $_GPC['p3_type'],
			'p3_score' => intval($_GPC[$p3_key]),
			'p3_num' => intval($_GPC['p3_num']),
			'p3_thumb' => $_GPC['p3_thumb'],
			'p4_type' => $_GPC['p4_type'],
			'p4_score' => intval($_GPC[$p4_key]),
			'p4_num' => intval($_GPC['p4_num']),
			'p4_thumb' => $_GPC['p4_thumb'],
			);
		$insert = array(
				'rid' => $rid,
				'uniacid' => $_W['uniacid'],
				'title' => $_GPC['title'],
				'thumb' => $_GPC['thumb'],
				'description' => $_GPC['description'],
				'groupid' => intval($_GPC['groupid']),
				'starttime' => strtotime($_GPC['time'][start]),
				'endtime' => strtotime($_GPC['time'][end]),
				'status' => intval($_GPC['status']),
				'reg' => intval($_GPC['reg']),
				'need_type' => $_GPC['need_type'],
				'need_num' => intval($_GPC['need_num']),
				'give_type' => $_GPC['give_type'],
				'give_num' => intval($_GPC['give_num']),
				'onlynone' => intval($_GPC['onlynone']),
				'awardnum' => intval($_GPC['awardnum']),
				'playnum' => intval($_GPC['playnum']),
				'dayplaynum' => intval($_GPC['dayplaynum']),
				'daytotalnum' => intval($_GPC['daytotalnum']),
				'zfcs' => intval($_GPC['zfcs']),
				'zjcs' => intval($_GPC['zjcs']),
				'tips' => $_GPC['tips'],
				'prizeinfo' => $_GPC['prizeinfo'],
				'remark' => $_GPC['remark'],
				'share_title' => $_GPC['share_title'],
				'share_img' => $_GPC['share_img'],
				'share_url' => $_GPC['share_url'],
				'share_content'=>$_GPC['share_content'],
				'rate' =>  intval($_GPC['rate']),
				'rate1' =>  intval($_GPC['rate1']),
				'rate2' =>  intval($_GPC['rate2']),
				'rate3' =>  intval($_GPC['rate3']),
				'rate4' =>  intval($_GPC['rate4']),
				'prizes' => iserializer($data),
				'createtime' => time(),		
			);
		if (empty($id)) {
			pdo_insert($this->table_reply, $insert);
		} else {
			unset($insert['createtime']);
			pdo_update($this->table_reply, $insert, array('id' => $id));
		}
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		$replies = pdo_fetchall("SELECT id  FROM ".tablename($this->table_reply)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->table_reply, "id IN ('".implode("','", $deleteid)."')");
	}

	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		//点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
		//在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
		if(checksubmit()) {
			//字段验证, 并获得正确的数据$dat
			$this->saveSettings($dat);
		}
		//这里来展示设置项表单
		include $this->template('settings');
	}

}