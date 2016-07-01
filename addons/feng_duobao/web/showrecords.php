<?php
	if (empty($_GPC['id'])) {
        message('抱歉，参数错误！', '', 'error');
    }
	$id=$_GPC['id'];
	$list = pdo_fetch("SELECT * FROM ".tablename('feng_record')." WHERE id = '{$id}'");
	if (empty($list['s_codes'])) {
		$list['s_codes']=NULL;
	}else{
		$list['s_codes']=unserialize($list['s_codes']);

		$s_codes='';
		$allcount=count($list['s_codes']);

		for ($i=0; $i < $allcount; $i++) { 
			$s_codes.='<tr><td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td>';
			$i=$i+1;
			$s_codes.='<td>'.$list['s_codes'][$i].'</td></tr>';
		}
		$list['s_codes']=$s_codes;
	}
	
	include $this->template('showrecords');
?>