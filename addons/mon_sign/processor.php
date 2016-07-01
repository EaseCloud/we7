<?php



defined('IN_IA') or exit('Access Denied');
define("SIGN_MODULENAME", "mon_sign");

require_once IA_ROOT . "/addons/" . SIGN_MODULENAME . "/CRUD.class.php";
class Mon_SignModuleProcessor extends WeModuleProcessor {

    private $sae=false;

    public function respond() {
        $rid = $this->rule;
		
        $sign=pdo_fetch("select * from ".tablename(CRUD::$table_sign)." where rid=:rid",array(":rid"=>$rid));


        if(!empty($sign)){

            if(TIMESTAMP<$sign['starttime']){

                return   $this->respText("活动未开始");
            }elseif(TIMESTAMP>$sign['endtime']){
               return  $this->respText("活动已结束");
            }else{

                $from=$this->message['from'];
                $news = array ();
                $news [] = array ('title' => $sign['new_title'], 'description' =>$sign['new_content'], 'picurl' => $this->getpicurl ( $sign ['new_icon'] ), 'url' => $this->createMobileUrl ( 'index',array('openid'=>$from,'sid'=>$sign['id']))  );
                return $this->respNews ( $news );

            }


        }else{
          return   $this->respText("签到活动不存在或已删除");

        }

        return null;
    }




    private function getpicurl($url) {
        global $_W;

        if($this->sae){
            return $url;
        }

        return $_W ['attachurl'] . $url;

    }

}
