<?php
if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');
global $_GPC,$_W;
$rid= intval($_GPC['rid']);
$data= $_GPC['data'];
if(empty($rid)){
    message('抱歉，传递的参数错误！','', 'error');              
}
$reply = pdo_fetch("SELECT * FROM " . tablename('stonefish_bigwheel_reply') . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
$exchange = pdo_fetch("select * FROM ".tablename("stonefish_bigwheel_exchange")." where rid = :rid", array(':rid' => $rid));
if(empty($reply)){
    message('抱歉，活动不存在！','', 'error');              
}
$isfansname = explode(',',$exchange['isfansname']);

if($data=='branch'){
    $statustitle='商家网点增送';
	if (!empty($_GPC['districtid'])) {     
        $where.=' and districtid='.$_GPC['districtid'].'';
    }elseif(!empty($_GPC['pcate'])){
		$districts = pdo_fetchall("SELECT id FROM " . tablename('stonefish_branch_business') . "  WHERE districtid=:districtid and  uniacid=:uniacid ORDER BY id DESC", array('districtid' =>$_GPC['pcate'],'uniacid' =>$_W['uniacid']), 'districtid');
		$districtid = '';
        foreach ($districts as $districtss) {
            $districtid .= $districtss['id'].',';
        }
		$districtid = substr($districtid,0,strlen($districtid)-1);
		$where.=' and districtid in('.$districtid.')';
	}
	
	$list = pdo_fetchall("SELECT * FROM ".tablename('stonefish_branch_doings')."  WHERE rid = :rid and uniacid=:uniacid and module=:module ".$where." ORDER BY id DESC" , array(':rid' => $rid,':uniacid'=>$_W['uniacid'],':module'=>'stonefish_bigwheel'));
    //查询区域以及商家 以及中奖情况
	foreach ($list as &$row) {
	    $row['shangjia'] = pdo_fetchcolumn("SELECT title FROM " . tablename('stonefish_branch_business') . "  WHERE id = :id", array(':id' => $row['districtid']));
		$districtid = pdo_fetchcolumn("SELECT districtid FROM " . tablename('stonefish_branch_business') . "  WHERE id = :id", array(':id' => $row['districtid']));
		$row['quyu'] = pdo_fetchcolumn("SELECT title FROM " . tablename('stonefish_branch_district') . "  WHERE id = :id", array(':id' => $districtid));
		$fid = pdo_fetchcolumn("SELECT from_user FROM " . tablename('stonefish_bigwheel_fans') . "  WHERE mobile = :mobile and rid=:rid and uniacid=:uniacid", array(':mobile' => $row['mobile'],':rid' => $rid,':uniacid' => $_W['uniacid']));
		$row['awardinfo']='';
		$awards = pdo_fetchall("SELECT prizeid FROM " . tablename('stonefish_bigwheel_fansaward') . " WHERE rid = :rid and from_user=:from_user", array(':rid' => $rid,':from_user' => $fid));
		if(!empty($awards)){
			foreach ($awards as &$awardid) {
				$row['prizename'] = pdo_fetchcolumn("SELECT prizename FROM " . tablename('stonefish_bigwheel_prize') . "  WHERE id = :id", array(':id' => $awardid['prizeid']));
				$row['awardinfo'] = $row['awardinfo'].$awardid['prizename'].';';
			}
		}
    }
    $tableheader = array('ID', '手机号', '机会次数', '使用个数', '商家区域', '商家', '商家ID', '中奖情况', '添加时间');
    $html = "\xEF\xBB\xBF";
    foreach ($tableheader as $value) {
	    $html .= $value . "\t ,";
    }
    $html .= "\n";
    foreach ($list as $value) {
	    $html .= $value['id'] . "\t ,";
	    $html .= $value['mobile'] . "\t ,";	
	    $html .= $value['awardcount'] . "\t ,";	
	    $html .= $value['usecount'] . "\t ,";	
	    $html .= $value['quyu'] . "\t ,";	
	    $html .= $value['shangjia'] . "\t ,";	
		$html .= $value['districtid'] . "\t ,";	
	    $html .= $value['awardinfo'] . "\t ,";	
	    $html .= date('Y-m-d H:i:s', $value['createtime']) . "\n";		
    }
}elseif($data=='fansdata'){
    $zhongjiang = $_GPC['zhongjiang'];
	if(!empty($zhongjiang)){        
	    if($zhongjiang == 1){
		    $statustitle='未中奖用户';
			$where.=' and zhongjiang=0';
	    }elseif($zhongjiang == 2){
		    $statustitle='中奖用户';
			$where.=' and zhongjiang>=1';
		}elseif($zhongjiang == 3){
		    $statustitle='虚拟中奖';
			$where.=' and zhongjiang>=1 and xuni=1';
		}
    }else{
        $statustitle='全部用户';
    }
	$list = pdo_fetchall("SELECT * FROM ".tablename('stonefish_bigwheel_fans')."  WHERE rid = :rid and uniacid=:uniacid ".$where." ORDER BY id DESC" , array(':rid' => $rid,':uniacid'=>$_W['uniacid']));
	//变换变量
	foreach ($list as &$lists) {
		$lists['status']='';
		if($lists['zhongjiang']==0){
		    $lists['status']='未中奖';
	    }elseif($lists['zhongjiang']==1){
		    $lists['status']='未兑奖';
		}elseif($lists['zhongjiang']==2){
		    $lists['status']='已兑奖';
		}
		if($lists['xuni']==0){
		    $lists['status'].='/真实';
		}else{
		    $lists['status'].='/虚拟';
		}
		$lists['status'].='/虚拟';
	}
	//变换变量
	$tableheader = array('ID', '状态');
	$ziduan = array('realname','mobile','qq','email','address','gender','telephone','idcard','company','occupation','position');
	$k = 0;
	foreach ($ziduan as $ziduans) {
		if($exchange['is'.$ziduans]){
			$tableheader[]=$isfansname[$k];
		}
		$k++;
	}
	$tableheader[]='中奖者微信码';
	//$tableheader[]='初始值';
	$tableheader[]='助力值';
	//$tableheader[]='兑换值';
	$tableheader[]='分享量';
	$tableheader[]='参与时间';
    $html = "\xEF\xBB\xBF";
    foreach ($tableheader as $value) {
	    $html .= $value . "\t ,";
    }
    $html .= "\n";
    foreach ($list as $value) {
	    $html .= $value['id'] . "\t ,";	    
	    $html .= $value['status'] . "\t ,";	
	    foreach ($ziduan as $ziduans) {
			if($exchange['is'.$ziduans]){
				if($ziduans=='gender'){
					if($value[$ziduans]==0){
						$html .= "保密\t ,";	
					}
					if($value[$ziduans]==1){
						$html .= "男\t ,";	
					}
					if($value[$ziduans]==2){
						$html .= "女\t ,";	
					}
				}else{
					$html .= $value[$ziduans] . "\t ,";	
				}
			}
		}
	    $html .= $value['from_user'] . "\t ,";	
		//$html .= $value['inpoint'] . "\t ,";
		$html .= $value['sharepoint'] . "\t ,";
		//$html .= $value['outpoint'] . "\t ,";
		$html .= $value['sharenum'] . "\t ,";
	    $html .= date('Y-m-d H:i:s', $value['createtime']) . "\n";
    }
}elseif($data=='rankdata'){
    $rank = $_GPC['rank'];
	if(!empty($rank)){        
	    if($rank == 'sharenum'){
		    $statustitle='分享值排行榜';
			$ORDER ='sharenum';
	    }elseif($rank == 'sharepoint'){
		    $statustitle='分享额排行榜';
			$ORDER ='sharepoint';
		}elseif($rank == 'award'){
		    $statustitle='中奖量排行榜';
			$ORDER ='awardnum';
		}
    }else{
        $statustitle='分享值排行榜';
		$ORDER ='sharenum';
    }
	$statustitle.='排名';
	$list = pdo_fetchall("SELECT * FROM ".tablename('stonefish_bigwheel_fans')."  WHERE rid = :rid and uniacid=:uniacid ORDER BY ".$ORDER." DESC,id asc" , array(':rid' => $rid,':uniacid'=>$_W['uniacid']));
	//变换变量
	foreach ($list as &$lists) {
		$lists['status']='';
		if($lists['zhongjiang']==0){
		    $lists['status']='未中奖';
	    }elseif($lists['zhongjiang']==1){
		    $lists['status']='未兑奖';
		}elseif($lists['zhongjiang']==2){
		    $lists['status']='已兑奖';
		}
		if($lists['xuni']==0){
		    $lists['status'].='/真实';
		}else{
		    $lists['status'].='/虚拟';
		}
		$lists['status'].='/虚拟';
	}
	//变换变量
	$tableheader = array('ID', '名次', '状态');
	$ziduan = array('realname','mobile','qq','email','address','gender','telephone','idcard','company','occupation','position');
	$k = 0;
	foreach ($ziduan as $ziduans) {
		if($exchange['is'.$ziduans]){
			$tableheader[]=$isfansname[$k];
		}
		$k++;
	}
	$tableheader[]='中奖者微信码';
	//$tableheader[]='初始值';
	$tableheader[]='助力值';
	//$tableheader[]='兑换值';
	$tableheader[]='分享量';
	$tableheader[]='中奖量';
	$tableheader[]='参与时间';
    $html = "\xEF\xBB\xBF";
    foreach ($tableheader as $value) {
	    $html .= $value . "\t ,";
    }
    $html .= "\n";
	$i = 1;
    foreach ($list as $value) {
	    $html .= $value['id'] . "\t ,";
		$html .= $i . "\t ,";	   
	    $html .= $value['status'] . "\t ,";	
	    foreach ($ziduan as $ziduans) {
			if($exchange['is'.$ziduans]){
				if($ziduans=='gender'){
					if($value[$ziduans]==0){
						$html .= "保密\t ,";	
					}
					if($value[$ziduans]==1){
						$html .= "男\t ,";	
					}
					if($value[$ziduans]==2){
						$html .= "女\t ,";	
					}
				}else{
					$html .= $value[$ziduans] . "\t ,";	
				}
			}
		}
	    $html .= $value['from_user'] . "\t ,";	
		//$html .= $value['inpoint'] . "\t ,";
		$html .= $value['sharepoint'] . "\t ,";
		//$html .= $value['outpoint'] . "\t ,";
		$html .= $value['sharenum'] . "\t ,";
		$html .= $value['awardnum'] . "\t ,";
	    $html .= date('Y-m-d H:i:s', $value['createtime']) . "\n";
		$i++;
    }
}elseif($data=='prizedata'){
    $params = '';
	//导出标题
	if ($_GPC['tickettype']>=1) {
        if($_GPC['tickettype']==1){
		    $statustitle = '后台兑奖统计';
		    $params = " and a.tickettype=1";
	    }
	    if($_GPC['tickettype']==2){
		    $statustitle = '店员兑奖统计';
		    $params = " and a.tickettype=2";
	    }
	    if($_GPC['tickettype']==3){
		    $statustitle = '商家网点兑奖统计';
		    $params = " and a.tickettype=3";
	    }    
    }else{
		$statustitle = '全部兑奖统计';
	}
	if(!empty($prizeid)){
        $statustitle .= pdo_fetchcolumn("SELECT prizerating FROM ".tablename('stonefish_bigwheel_prize')." WHERE id=:prizeid", array(':prizeid' => $_GPC['prizeid']));
		$params .= " and a.prizeid='".$prizeid."'";
    }
	if($_GPC['zhongjiang']==1){
		$statustitle .= '未兑换';
		$params.=' and a.zhongjiang=1';
	}
	if($_GPC['zhongjiang']==2){
		$statustitle .= '已兑换';
		$params.=' and a.zhongjiang>=2';
	}		
	if($_GPC['xuni']==1){
		$statustitle .= '虚拟';
		$params.=' and a.xuni=1';
	}
	if($_GPC['xuni']=='2'){
		$statustitle .= '真实';
		$params.=' and a.xuni=0';
	}
	//导出标题
    
    $list = pdo_fetchall("SELECT a.*,b.realname,b.mobile,b.qq,b.email,b.address,b.gender,b.telephone,b.idcard,b.company,b.occupation,b.position FROM ".tablename('stonefish_bigwheel_fansaward')." as a,".tablename('stonefish_bigwheel_fans')." as b WHERE a.uniacid=b.uniacid and a.rid=b.rid and a.from_user=b.from_user and a.rid = :rid and a.uniacid=:uniacid ".$params." ORDER BY a.id DESC", array(':rid' => $rid,':uniacid'=>$_W['uniacid']));
    foreach ($list as &$lists) {
	    $lists['status']='';
		if($lists['zhongjiang']==0){
		    $lists['status']='未中奖';
	    }elseif($lists['zhongjiang']==1){
		    $lists['status']='未兑奖';
		}elseif($lists['zhongjiang']==2){
		    $lists['status']='已兑奖';
		}
		if($lists['xuni']==0){
		    $lists['status'].='/真实';
		}else{
		    $lists['status'].='/虚拟';
		}
		$lists['status'].='/虚拟';
		if($lists['tickettype']==1){
			$lists['tickettype']='后台兑奖';
		}
		if($lists['tickettype']==2){
			$lists['tickettype']='店员兑奖';
			$lists['ticketname'] = pdo_fetchcolumn("SELECT name FROM " . tablename('activity_coupon_password') . " WHERE id = :id", array(':id' => $row['ticketid']));
		}
		if($lists['tickettype']==3){
			$lists['tickettype'].='商家网店兑奖';
			$lists['ticketname'] = pdo_fetchcolumn("SELECT title FROM " . tablename('stonefish_branch_business') . " WHERE id = :id", array(':id' => $row['ticketid']));
		}
		if($lists['tickettype']==4){
			$lists['tickettype']='密码兑奖';
		}
		$prize = pdo_fetch("select prizerating,prizename from " . tablename('stonefish_bigwheel_prize') . "  where id = :id", array(':id' =>$lists['prizeid']));
		$lists['prizerating'] =$prize['prizerating'];
		$lists['prizename'] =$prize['prizename'];
    }
    $tableheader = array('ID', '奖项', '奖品名称', '状态');
    $ziduan = array('realname','mobile','qq','email','address','gender','telephone','idcard','company','occupation','position');
	$k=0;
	foreach ($ziduan as $ziduans) {
		if($exchange['is'.$ziduans]){
			$tableheader[]=$isfansname[$k];
		}
		$k++;
	}
	$tableheader[]='中奖者微信码';
	$tableheader[]='中奖时间';
	$tableheader[]='兑奖时间';
	$tableheader[]='兑奖类型';
	$tableheader[]='兑奖地';
	$html = "\xEF\xBB\xBF";
    foreach ($tableheader as $value) {
	    $html .= $value . "\t ,";
    }
    $html .= "\n";
    foreach ($list as $value) {
		$html .= $value['id'] . "\t ,";
	    $html .= $value['prizerating'] . "\t ,";	
	    $html .= $value['prizename'] . "\t ,";	
	    $html .= $value['status'] . "\t ,";	
	    foreach ($ziduan as $ziduans) {
			if($exchange['is'.$ziduans]){
				if($ziduans=='gender'){
					if($value[$ziduans]==0){
						$html .= "保密\t ,";	
					}
					if($value[$ziduans]==1){
						$html .= "男\t ,";	
					}
					if($value[$ziduans]==2){
						$html .= "女\t ,";	
					}
				}else{
					$html .= $value[$ziduans] . "\t ,";	
				}				
			}
		}	
	    $html .= $value['from_user'] . "\t ,";
	    $html .= date('Y-m-d H:i:s', $value['createtime']) . "\t ,";
	    $html .= ($value['consumetime'] == 0 ? '未使用' : date('Y-m-d H:i',$value['consumetime'])) . "\t ,";
		$html .= $value['tickettype'] . "\t ,";
		$html .= $value['ticketname']  . "\n";
    }
}
header("Content-type:text/csv");
header("Content-Disposition:attachment; filename=".$statustitle.$award."数据_".$rid.".csv");
echo $html;
exit();
