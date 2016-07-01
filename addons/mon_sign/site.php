<?php
/**
 * 积分签到
 */
defined('IN_IA') or exit('Access Denied');

define("SIGN_MODULENAME", "mon_sign");
define("MONSIGN_RES", "../addons/" . SIGN_MODULENAME . "/");
require_once IA_ROOT . "/addons/" . SIGN_MODULENAME . "/CRUD.class.php";
require IA_ROOT . "/addons/" . SIGN_MODULENAME . "/oauth2.class.php";


/**
 * Class MonSignModuleSite
 * SELECT sid, DAY( FROM_UNIXTIME( sin_time,  '%Y-%m-%d %H:%i:%s' ) ) AS d, COUNT( * ) AS c
FROM ims_mon_sign_record
WHERE sid =6
GROUP BY d
LIMIT 0 , 30
 */
//类型 1：关注积分  2：每日积分  3：连续积分 4、系统奖励
class Mon_SignModuleSite extends WeModuleSite
{
    public $weid;
    public $acid;
    public $oauth;

    public static $TYPE_FOLLOW = 1;//关注
    public static $TYPE_DAY = 2;//每日
    public static $TYPE_SERIAL = 3;//连续
    public static $TYPE_SYSTEM = 4;//系统


    function __construct()
    {
        global $_W;
        $this->weid = $_W['weid'];


        $wechat = pdo_fetch("select * from ".tablename('account_wechats')." where uniacid=:uniacid limit 1",array(":uniacid"=>$this->weid));
        if($wechat){
            $appid = $wechat['key'];
            $appsecret = $wechat['secret'];
        }

        $this->oauth = new Oauth2($appid, $appsecret);
    }


    /**
     * 
     * 签到管理
     */
    public function  doWebSign()
    {
        global $_GPC,$_W;


        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        if ($operation == 'display') {

            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sign) . " WHERE weid =:weid  ORDER BY createtime DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $this->weid));
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(CRUD::$table_sign) . " WHERE weid =:weid ", array(':weid' => $this->weid));
            $pager = pagination($total, $pindex, $psize);

        } else if ($operation == 'delete') {
            $id = $_GPC['id'];
            pdo_delete(CRUD::$table_sign_award, array("sid" => $id));
            pdo_delete(CRUD::$table_sign_user, array("sid" => $id));
            pdo_delete(CRUD::$table_sign_serial, array("sid" => $id));
            pdo_delete(CRUD::$table_sign_record, array("sid" => $id));
            pdo_delete(CRUD::$table_sign, array('id' => $id));

            message('删除成功！', referer(), 'success');
        }

        include $this->template("sign_manage");

    }


    /**
     * 
     * 系统奖励积分
     */
    public function  doWebSystemaward()
    {
        global $_GPC, $_W;
        $uid = $_GPC['uid'];

        $sid = $_GPC['sid'];


        $signUser = CRUD::findById(CRUD::$table_sign_user, $uid);

        $sign = CRUD::findById(CRUD::$table_sign, $sid);
        if (checksubmit()) {

            if (empty($_GPC['credit'])) {
                message("请输入积分");
            }


            $record_data = array(
                'uid' => $uid,
                'sid' => $sid,
                'sign_type' => self::$TYPE_SYSTEM,
                'credit' => $_GPC['credit'],
                'createtime' => TIMESTAMP
            );
            CRUD::create(CRUD::$table_sign_award, $record_data);
            $user_credit = $signUser['credit'];

            $user_data = array(
                'credit' => $user_credit + $_GPC['credit']

            );

            if ($sign['sync_credit'] == 1) {//同步积分
                $this->synFanscredit($signUser['openid'],$_GPC['credit']);//同步
            }

            CRUD::updateById(CRUD::$table_sign_user, $user_data, $uid);// 更新用户积分

            message('更新用户称成功！', $this->createWebUrl('SignUser', array(
                'sid' => $sid
            )), 'success');


        }


        include $this->template("system_award");


    }

    /**
     * 
     * 积分详细
     */
    public function  doWebUserAward()
    {
        global $_GPC;


        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';


        $uid = $_GPC['uid'];
        $type = !empty($_GPC['type']) ? $_GPC['type'] : 0;

        if ($operation == 'display') {

            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            switch ($type) {
                case self::$TYPE_DAY:
                case self::$TYPE_SERIAL:
                case self::$TYPE_FOLLOW:
                case self::$TYPE_SYSTEM:
                    $querySql = "select * from " . tablename(CRUD::$table_sign_award) . "  where uid=:uid and 	sign_type=:qtype order by createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
                    $countSql = "select count(*) from " . tablename(CRUD::$table_sign_award) . " where uid=:uid and 	sign_type=:qtype";
                    $q_condition = array(':uid' => $uid, ':qtype' => $type);

                    break;
                case 0:
                    $querySql = "select * from " . tablename(CRUD::$table_sign_award) . "  where uid=:uid  order by createtime desc limit " . ($pindex - 1) * $psize . ',' . $psize;
                    $countSql = "select count(*) from " . tablename(CRUD::$table_sign_award) . " where uid=:uid";
                    $q_condition = array(':uid' => $uid);
                    break;
            }
            $list = pdo_fetchall($querySql, $q_condition);
            $total = pdo_fetchcolumn($countSql, $q_condition);
            $pager = pagination($total, $pindex, $psize);

        }elseif($operation=='delete'){


            $id=$_GPC['id'];


            $award= CRUD::findById(CRUD::$table_sign_award,$id);


            if(empty($award)){
                message("奖励积分删除或已不存在");
            }

            $user= CRUD::findById(CRUD::$table_sign_user,$uid);
            CRUD::deleteByid(CRUD::$table_sign_award,$id);

            $user_data=array(
                'credit'=>$user['credit']-$award['credit']
            );

            CRUD::updateById(CRUD::$table_sign_user,$user_data,$uid);//更新用户积分

            message('删除成功！', referer(), 'success');



        }
        include $this->template("user_award");


    }


    /**
     * 
     * 签到用户
     */
    public function  doWebSignUser()
    {

        global $_GPC;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $sid = $_GPC['sid'];


        $keywords=$_GPC['keywords'];

        if(!empty($keywords)){

            $where="and nickname like '%".$keywords."%'";
        }

        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sign_user) . " WHERE sid =:sid ".$where." ORDER BY  id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':sid' => $sid));
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(CRUD::$table_sign_user) . " WHERE sid =:sid ".$where, array(':sid' => $sid));
            $pager = pagination($total, $pindex, $psize);

        } elseif ($operation == 'delete') {

            $id = $_GPC['id'];

            pdo_delete(CRUD::$table_sign_award, array('uid' => $id));
            pdo_delete(CRUD::$table_sign_record, array('uid' => $id));
            pdo_delete(CRUD::$table_sign_user, array('id' => $id));

        }


        include $this->template("sign_user");


    }


    /**
     * 
     * 用户数据下载
     */
    public function  doWebuserDownload(){
        require_once 'download.php';

    }

    /**
     * 
     *  统计分析
     */
    public function doWebAnalyse(){

        global $_GPC;
        $signs=pdo_fetchall("select * from ".tablename(CRUD::$table_sign)." where weid=:weid",array(':weid'=>$this->weid));



        $sid=$_GPC['sid'];
        if(empty($sid)){
            $sid=$signs[0]['id'];
        }
        $item=pdo_fetchall("SELECT sid,FROM_UNIXTIME( sin_time,  '%Y-%m-%d' ) as md , DAY( FROM_UNIXTIME( sin_time,  '%Y-%m-%d %H:%i:%s' ) ) AS d, COUNT( * ) AS c FROM ".tablename(CRUD::$table_sign_record)." WHERE sid =:sid   GROUP BY d  order by md asc limit 0,10 ",array(':sid'=>$sid));

      $msign=  CRUD::findById(CRUD::$table_sign,$sid);
        $date_str="[";
        $count_str="[";

       $index=0;
        foreach($item as $value){
            $date_str.="'".$value['md']."'";
           // $date_str.=$index;
            $count_str.=$value['c'];
            if($index<count($item)-1){
                $date_str.=",";
                $count_str.=",";
            }
            $index++;
        }

        $date_str.="]";
        $count_str.="]";
        include $this->template("analyse");



    }

    /*======手机*/

    /**
     * 
     *
     */
    public function  doMobileIndex()
    {

        global $_W, $_GPC;
        $is_follow = false;;
        $sid = $_GPC['sid'];
        $code = $_GPC['code'];
        $openid = $_W['fans']['from_user'];
        if (empty($openid)) {
            $openid = $_GPC['openid'];
        }
        $sign = CRUD::findById(CRUD::$table_sign, $sid);
        if (empty($sign)) {
            message("签到活动已删除或不存在");
        }

        if (!empty($openid)) {
            $userInfo = $this->setClientUserInfo($openid);
            if (!empty($userInfo)) {
                $is_follow = true;
            }

        }

        if (empty($userInfo)) {

            $userInfo = $this->getClientUserInfo();// 从cookie中取

            if (!empty($userInfo)) {
                $is_follow = true;
            }


        }


        $signUser = $this->findSignUser($userInfo['openid'], $sid);


        $serial = $this->findUserCurrentSerial($signUser, $sid);
        $serial_day = $serial['day'];//连续时间
        if (!empty($signUser)) {//用户已经签过到了

            $begintime = date('Y-m-d' . '00:00:00', $signUser['begin_sign_time']);
            $endtime = date('Y-m-d' . '23:59:59', $signUser['end_sign_time']);
            $beginSinTimeStamp = strtotime($begintime);
            $endSinTimeStamp = strtotime($endtime);
            $offsertDays = $this->getOffsetdays($beginSinTimeStamp, $endSinTimeStamp);


            $total_continuous = $offsertDays;

            $next_prize_days_length = $serial_day - $total_continuous;
            $todaySignCount = $this->findTodayUserSign($sid, $signUser['id']);

            if ($todaySignCount == 0) {//用户今天还没有签到
                $is_valid_checkin = 1;
            } else {
                $is_valid_checkin = 0;
            }

        } else {//用户从来没有签过到

            $user_serial_day = 0;
            $is_valid_checkin = 1;//
            $total_continuous = 0;
            $next_prize_days_length = $serial_day - $total_continuous;
        }

        $links = pdo_fetchall("select * from " . tablename(CRUD::$table_sign_link) . " where sid=:sid order by sort asc ", array(":sid" => $sid));
        include $this->template("index");

    }


    /**
     * 
     * 积分详细
     */
    public function  doMobileSignDetail()
    {

        global $_GPC;
        $sid = $_GPC['sid'];

        $sign = CRUD::findById(CRUD::$table_sign, $sid);

        if (empty($sign)) {
            message("签到活动不存在或已上删除!");
        }

        $userInfo = $this->getClientUserInfo();// 从cookie中取

        $is_follow = true;

        $signUser = $this->findSignUser($userInfo['openid'], $sid);

        if (empty($signUser)) {
            message("签到用户不存在");
        }


        $serialwards = pdo_fetchall("select * from " . tablename(CRUD::$table_sign_award) . " where uid=:uid and sid=:sid and sign_type=3 order by createtime  desc ", array(":uid" => $signUser['id'], ":sid" => $sid));

        $otherwards = pdo_fetchall("select * from " . tablename(CRUD::$table_sign_award) . " where uid=:uid and sid=:sid and sign_type<>3  order by createtime  desc ", array(":uid" => $signUser['id'], ":sid" => $sid));

        $links = pdo_fetchall("select * from " . tablename(CRUD::$table_sign_link) . " where sid=:sid order by sort asc ", array(":sid" => $sid));

        include $this->template("sign_detail");


    }

    /**
     * 
     * 签到
     * 类型 1：关注积分 2：每日积分 3：连续积分
     */
    public function doMobileSign()
    {
        global $_GPC;
        $sid = $_GPC['sid'];
        $res = array();
        $sign = CRUD::findById(CRUD::$table_sign, $sid);
        if (empty($sign)) {
            $res['code'] = 501;
            $res['msg'] = "积分签到活动已删除或不存在!";
            echo json_encode($res);
            exit;
        }

        $clientUser = $this->getClientUserInfo();
        if (empty($clientUser)) {
            $res['code'] = 501;
            $res['msg'] = "请关注公众账号再进行签到";
            echo json_encode($res);
            exit;
        }

        $signUser = $this->findSignUser($clientUser['openid'], $sid);

        if (!empty($signUser) && ($this->findTodayUserSign($sid, $signUser['id']) > 0)) {//今天已经签过到了
            $res['code'] = 503;
            $res['msg'] = "您今天已经签过到了，明天再来吧!";
            echo json_encode($res);
            exit;
        }

        if (empty($signUser)) {//从来没有签过到用户处理
            $now = TIMESTAMP;
            $serial = $this->findUserCurrentSerial(null, $sid);
            $user_data = array(
                "sid" => $sid,
                "begin_sign_time" => $now,
                "end_sign_time" => $now,
                "openid" => $clientUser['openid'],
                "nickname" => $clientUser['nickname'],
                "headimgurl" => $clientUser['headimgurl'],
                "serial_id" => $serial['id'],
                "credit" => $sign['sign_credit'],
                "sin_count" => 1
            );
            CRUD::create(CRUD::$table_sign_user, $user_data);
            $userid = pdo_insertid();

            $record_data = array(
                'uid' => $userid,
                'openid' => $clientUser['openid'],
                'sid' => $sid,
                'sin_time' => $now
            );

            CRUD::create(CRUD::$table_sign_record, $record_data);//插入记录

            $sign_award_day = array(
                'sid' => $sid['sid'],
                'uid' => $userid,
                'sign_type' => 2,
                'credit' => $sign['sign_credit'],
                'createtime' => TIMESTAMP
            );

            CRUD::create(CRUD::$table_sign_award, $sign_award_day);// 插入积分记录
            $res['code'] = 200;
            $res['msg'] = "恭喜您获得日签到积分" . $sign['sign_credit'] . "分!";

            if ($sign['sync_credit'] == 1) {//同步积分
                $this->synFanscredit($clientUser['openid'], $sign['sign_credit']);
            }

            echo json_encode($res);
            exit;

        }


        //已经签到过用户 签到
        if (!empty($signUser)) {

            $sin_time = TIMESTAMP;
            $user_credit = $signUser['credit'];
            $user_sin_count = $signUser['sin_count'];
            $user_sin_serial = $signUser['sin_serial'];
            $day_record_data = array(//每天签到积分
                'uid' => $signUser['id'],
                'openid' => $clientUser['openid'],
                'sid' => $sid,
                'sin_time' => $sin_time
            );
            CRUD::create(CRUD::$table_sign_record, $day_record_data);

            CRUD::updateById(CRUD::$table_sign_user, array("end_sign_time" => $sin_time), $signUser['id']);//更新最终的签到时间

            $sign_award_day = array(
                'sid' => $sid,
                'uid' => $signUser['id'],
                'sign_type' => 2,
                'credit' => $sign['sign_credit'],
                'createtime' => TIMESTAMP
            );
            CRUD::create(CRUD::$table_sign_award, $sign_award_day);// 插入日积分得奖表


            $user_credit = $user_credit + $sign['sign_credit'];
            $user_sin_count = $user_sin_count + 1;
            $user_serial_days = $signUser['sin_serial'];


            $serial = $this->findUserCurrentSerial($signUser, $sid);

            $serial_id = $serial['id'];
            $mserial_credit = 0;
            $serial_day = $serial['day'];
            $begintime = date('Y-m-d' . '00:00:00', $signUser['begin_sign_time']);
            $endtime = date('Y-m-d' . '23:59:59', $sin_time);

            $beginSinTimeStamp = strtotime($begintime);
            $endSinTimeStamp = strtotime($endtime);

            $offsertDays = $this->getOffsetdays($beginSinTimeStamp, $endSinTimeStamp);

            $recordOffsetDays = $this->findUserSerialRecordCount($beginSinTimeStamp, $endSinTimeStamp, $sid, $signUser['id']);//查询记录签到的连续时间

            if ($recordOffsetDays < $offsertDays) {// 签到时间断了，重新计算

                $this->updateUserSinTime($sin_time, $sin_time, $signUser['id']);//重置游标

            } else if (($recordOffsetDays == $recordOffsetDays) && ($recordOffsetDays == $serial_day)) {//达到了连续签到

                $sign_award_serial = array(
                    'sid' => $sid,
                    'uid' => $signUser['id'],
                    'sign_type' => 3,
                    'credit' => $serial['credit'],
                    'serial_start_time' => $signUser['begin_sign_time'],
                    'serial_end_time' => $sin_time,
                    'serial_day' => $serial_day,
                    'createtime' => TIMESTAMP
                );
                CRUD::create(CRUD::$table_sign_award, $sign_award_serial);//  插入连续积分
                //更新award 表
                $user_credit = $user_credit + $serial['credit'];

                $serial_id = $this->findNextSerialId($serial['day'], $sid);//查找下一次 连续 ID 计算
                $user_serial_days = $user_serial_days + $serial_day;
                $mserial_credit = $serial['credit'];

            }


            $m_sin_user_data = array(
                "serial_id" => $serial_id,
                "credit" => $user_credit,
                'sin_count' => $user_sin_count,
                'sin_serial' => $user_serial_days
            );

            CRUD::updateById(CRUD::$table_sign_user, $m_sin_user_data, $signUser['id']);//更新用户的积分

            if ($sign['sync_credit'] == 1) {//同步积分
                $this->synFanscredit($clientUser['openid'],$mserial_credit+$sign['sign_credit']);
            }
            $res['code'] = 200;
            $ser_msg = $mserial_credit > 0 ? "连续签到积分" . $mserial_credit . "分" : "";
            $res['msg'] = "恭喜您获日签到积分" . $sign['sign_credit'] . "分" . $ser_msg;
            echo json_encode($res);
            exit;

        }


    }


//函数==========================================================================================================

    /**
     * 
     * @param $openid
     */
    public function setClientUserInfo($openid)
    {
           global $_W;
        if (!empty($openid)) {
			
			  load()->classs('weixin.account');
            $accObj= WeixinAccount::create($_W['acid']);
            $access_token = $accObj->fetch_token();

			
           // $access_token = $this->oauth->getAccessToken();
            if (empty($access_token)) {
                message("获取accessToken失败");
            }
            $userInfo = $this->oauth->getUserInfo($access_token, $openid);
            if (!empty($userInfo)) {
                $cookie = array();
                $cookie['openid'] = $userInfo['openid'];
                $cookie['nickname'] = $userInfo['nickname'];
                $cookie['headimgurl'] = $userInfo['headimgurl'];
                $session = base64_encode(json_encode($cookie));
                isetcookie('__singnuser', $session, 24 * 3600 * 365);

            }

            return $userInfo;
        }


    }


    /**
     * 
     * 获取哟规划信息
     * @return array|mixed|stdClass
     */
    public function  getClientUserInfo()
    {
        global $_GPC;
        $session = json_decode(base64_decode($_GPC['__singnuser']), true);
        return $session;

    }


    /**
     *  更新用户时间
     * 
     * @param $begin_time
     * @param $endime
     * @param $uid
     */
    public function  updateUserSinTime($begin_time, $endime, $uid)
    {
        CRUD::updateById(CRUD::$table_sign_user, array("begin_sign_time" => $begin_time, "end_sign_time" => $endime), $uid);

    }

    /**
     * 
     * @param $currentId
     * 获取下一个id
     */
    public function  findNextSerialId($currentDay, $sid)
    {
        $serials = pdo_fetchall("select  * from " . tablename(CRUD::$table_sign_serial) . " where sid=:sid order by day asc ", array(":sid" => $sid));
        $id = $serials[0]["id"];
        foreach ($serials as $ser) {

            if ($ser['day'] > $currentDay) {
                $id = $ser['id'];
                break;
            }

        }
        return $id;

    }

    /**
     *
     * @param $openid
     * @param $sid
     * @return bool|mixed
     */
    public function  findSignUser($openid, $sid)
    {

        return CRUD::findUnique(CRUD::$table_sign_user, array(":openid" => $openid, ":sid" => $sid));
    }

    /**
     *查询用户，时间段的签到记录条数
     * 
     * @param $starttime 时间戳
     * @param $endTime 时间戳
     * @param $sid
     * @param $uid
     * @return bool|mixed
     */
    public function  findUserSerialRecordCount($starttime, $endTime, $sid, $uid)
    {

        $count = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(CRUD::$table_sign_record) . " WHERE  uid=:uid and sid=:sid and sin_time<=:endtime and  sin_time>=:starttime ", array(':uid' => $uid, ":sid" => $sid, ":endtime" => $endTime, ":starttime" => $starttime));
        return $count;
    }

    /**
     * 查找用户当前所进行的 连续签到时间
     * 
     * @param $signUser
     */
    public function  findUserCurrentSerial($signUser, $sid)
    {

        if (empty($signUser) || empty($signUser['serial_id'])) {
            $serial = pdo_fetch("select * from " . tablename(CRUD::$table_sign_serial) . " where sid=:sid order by day asc limit 0,1", array(":sid" => $sid));
        } else {
            $serial = pdo_fetch("select * from " . tablename(CRUD::$table_sign_serial) . " where sid=:sid and id=:id", array(":sid" => $sid, ":id" => $signUser['serial_id']));
        }
        return $serial;

    }


    /**
     * 查询用户今天是否已经签到了
     * 
     * @param $sid
     * @param $uid
     */

    public function  findTodayUserSign($sid, $uid)
    {

        $today_beginTime = date('Y-m-d' . '00:00:00', TIMESTAMP);
        $today_endTime = date('Y-m-d' . '23:59:59', TIMESTAMP);

        return $this->findUserSerialRecordCount(strtotime($today_beginTime), strtotime($today_endTime), $sid, $uid);


    }

    /**
     * 
     * @param $openid
     * @param $credit
     * 积分同步
     */
    public function  synFanscredit($openid, $credit)
    {
        global $_W;


        load()->model('mc');



        $sql = 'SELECT * FROM ' . tablename('mc_mapping_fans') . ' WHERE openid = :openid AND uniacid = :uniacid LIMIT 1';
        $params = array();
        $params[':openid'] = $openid;
        $params[':uniacid'] = $this->weid;
        $fan = pdo_fetch($sql, $params);


        if(!empty($fan)){

            mc_credit_update($fan['uid'],'credit1',$credit);

        }

        /*
        $fans = pdo_fetch("select * from " . tablename("fans") . " where from_user=:openid", array(":openid" => $openid));
        if (!empty($fans)) {
            $fans_credit1 = $fans['credit1'] + $credit;

            pdo_update("fans", array('credit1' => $fans_credit1), array("from_user" => $openid));

        }
        */

    }

    /**
     * 
     * 计算相隔天数
     * @param $begintime
     * @param $endtime
     * @return float
     */
    public function getOffsetdays($begintime, $endtime)
    {
        $days = ceil(abs($begintime - $endtime) / 86400);
        return $days;
    }

    public function  getTypeName($type)
    {
        $type_name = "全部";
        switch ($type) {
            case self::$TYPE_DAY:
                $type_name = "每日积分";
                break;
            case self::$TYPE_FOLLOW:
                $type_name = "关注积分";
                break;
            case self::$TYPE_SERIAL:
                $type_name = "连续签到积分";
                break;
            case self::$TYPE_SYSTEM:
                $type_name = "系统积分";
                break;

        }

        return $type_name;


    }


}