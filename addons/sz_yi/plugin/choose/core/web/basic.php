<?php
//芸众商城 QQ:913768135
global $_W, $_GPC;
load()->func('tpl');
$op     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$shopset = m('common')->getSysset('shop');
$sql = 'SELECT * FROM ' . tablename('sz_yi_category') . ' WHERE `uniacid` = :uniacid ORDER BY `parentid`, `displayorder` DESC';
$category = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']), 'id');
$parent = $children = array();
if (!empty($category)) {
    foreach ($category as $cid => $cate) {
        if (!empty($cate['parentid'])) {
            $children[$cate['parentid']][] = $cate;
        } else {
            $parent[$cate['id']] = $cate;
        }
    }
}
if ($op == 'display') {
   
} elseif($op == 'create'){
  $pcate     = !empty($_GPC['category']['parentid']) ? $_GPC['category']['parentid'] : '';
  $ccate     = !empty($_GPC['category']['childid']) ? $_GPC['category']['childid'] : '';
  $tcate     = !empty($_GPC['category']['thirdid']) ? $_GPC['category']['thirdid'] : '';
  $color     = !empty($_GPC['color']) ? $_GPC['color'] : '';
    $sql="select u.* from ".tablename('sz_yi_perm_user')." u left join " .tablename('sz_yi_perm_role'). " r on r.id = u.roleid where r.status1=1 and u.uniacid = :uniacid";
    $agent=pdo_fetchall($sql,array(':uniacid'=>$_W['uniacid']));
    if(checksubmit('submit')){
        $date=date("Y-m-d H:i:s");
        $agentname=pdo_fetch('select username from ' .tablename('sz_yi_perm_user'). ' where uid=:uid and uniacid=:uniacid',array(':uid'=>$_GPC['uid'],':uniacid'=>$_W['uniacid']));
        if($_GPC['openclose']==1 && $pcate!=''){
          message('在供应商和分类之中只能指定选择一个！', $this->createPluginWebUrl('choose/basic',array('op'=>'create')), 'error');
        }else{
          if($_GPC['openclose']==1){
            pdo_insert('sz_yi_chooseagent',array(
            'pagename'=>$_GPC['pagename'],
            'isopen'=>$_GPC['openclose'],
            'uid'=>$_GPC['uid'],
            'createtime'=>$date,
            'savetime'=>$date,
            'agentname'=>$agentname['username'],
            'uniacid'=>$_W['uniacid'],
            'color'=>$color

            ));
            message('快速选购页添加成功!', $this->createPluginWebUrl('choose'), 'success');
          }else{
            if($pcate!=''){
              pdo_insert('sz_yi_chooseagent',array(
              'pagename'=>$_GPC['pagename'],
              'isopen'=>0,
              'createtime'=>$date,
              'savetime'=>$date,
              'uniacid'=>$_W['uniacid'],
              'agentname'=>'未设置',
              'pcate'=>$pcate,
              'ccate'=>$ccate,
              'tcate'=>$tcate,
              'color'=>$color

              ));
              message('快速选购页添加成功!', $this->createPluginWebUrl('choose'), 'success');
            }else{
              pdo_insert('sz_yi_chooseagent',array(
              'pagename'=>$_GPC['pagename'],
              'isopen'=>0,
              'createtime'=>$date,
              'savetime'=>$date,
              'agentname'=>'未设置',
              'uniacid'=>$_W['uniacid'],
              'color'=>$color
              ));
              message('快速选购页添加成功!', $this->createPluginWebUrl('choose'), 'success');
            }
          }
        }
           
    }       
} elseif($op == 'change'){
  $pcate     = !empty($_GPC['category']['parentid']) ? $_GPC['category']['parentid'] : '';
  $ccate     = !empty($_GPC['category']['childid']) ? $_GPC['category']['childid'] : '';
  $tcate     = !empty($_GPC['category']['thirdid']) ? $_GPC['category']['thirdid'] : '';
  $color     = !empty($_GPC['color']) ? $_GPC['color'] : '';
  $sql="select u.* from ".tablename('sz_yi_perm_user')." u left join " .tablename('sz_yi_perm_role'). " r on r.id = u.roleid where r.status1=1 and u.uniacid = :uniacid";
  $agent=pdo_fetchall($sql,array(':uniacid'=>$_W['uniacid']));
  $open=pdo_fetch('select * from ' .tablename('sz_yi_chooseagent'). ' where id=' .$_GPC['pageid']);
  if(empty($_GPC['pageid'])){
    message('此页不存在或已删除!',$this->createPluginWebUrl('choose'), 'error');
  }
    if(checksubmit('submit')){
      $date=date("Y-m-d H:i:s");
      if($_GPC['openclose']==1 && $pcate!=''){
        message('在供应商和分类之中只能指定选择一个！', $this->createPluginWebUrl('choose/basic',array('op'=>'change','pageid'=>$_GPC['pageid'])), 'error');
      }else{
        if($_GPC['openclose']==1){
            $agentname=pdo_fetch('select username from ' .tablename('sz_yi_perm_user'). ' where uid=:uid and uniacid=:uniacid',array(':uid'=>$_GPC['uid'],':uniacid'=>$_W['uniacid']));
            pdo_update('sz_yi_chooseagent',array(
                  'pagename'=>$_GPC['pagename'],  
                  'isopen'=>$_GPC['openclose'],
                  'uid'=>$_GPC['uid'],
                  'savetime'=>$date,
                  'agentname'=>$agentname['username'],
                  'pcate'=>'',
                  'ccate'=>'',
                  'tcate'=>'',
                  'color'=>$color                 

                  ),array('id'=>$_GPC['pageid'],'uniacid'=>$_W['uniacid']));

                 message('快速选购页修改成功!',$this->createPluginWebUrl('choose'), 'success');
           
        }else{
          if($pcate!=''){
            pdo_update('sz_yi_chooseagent',array(
            'pagename'=>$_GPC['pagename'],  
                  'isopen'=>0,
                  'uid'=>'',
                  'savetime'=>$date,
                  'agentname'=>'未设置',
                  'pcate'=>$pcate,
                  'ccate'=>$ccate,
                  'tcate'=>$tcate,
                  'color'=>$color

                  ),array('id'=>$_GPC['pageid'],'uniacid'=>$_W['uniacid']));
                  message('快速选购页修改成功!', $this->createPluginWebUrl('choose'), 'success');
          }else{
            pdo_update('sz_yi_chooseagent',array(
            'pagename'=>$_GPC['pagename'],  
                  'isopen'=>0,
                  'uid'=>'',
                  'savetime'=>$date,
                  'agentname'=>'未设置',
                  'pcate'=>'',
                  'ccate'=>'',
                  'tcate'=>'',
                  'color'=>$color

                  ),array('id'=>$_GPC['pageid'],'uniacid'=>$_W['uniacid']));
                  message('快速选购页修改成功!', $this->createPluginWebUrl('choose'), 'success');
          }
            
        }
      }     
    }    

}
include $this->template('basic');
