<?php
/**
 * 积分签到
 */
defined('IN_IA') or exit('Access Denied');

define("SIGN_MODULENAME", "mon_sign");

require_once IA_ROOT . "/addons/" . SIGN_MODULENAME . "/CRUD.class.php";

class Mon_SignModule extends WeModule {

	public $weid;
	public function __construct() {
		global $_W;
		$this->weid = IMS_VERSION<0.6?$_W['weid']:$_W['uniacid'];
	}
	

	public function fieldsFormDisplay($rid = 0) {
		global $_W;

		if(!empty($rid)){
			$reply=CRUD::findUnique(CRUD::$table_sign,array(":rid"=>$rid));

			$reply['starttime'] = date("Y-m-d  H:i", $reply['starttime']);
			$reply['endtime'] = date("Y-m-d  H:i", $reply['endtime']);


			$sin_serials=pdo_fetchall("select * from ".tablename(CRUD::$table_sign_serial)." where sid=:sid order by day asc ",array(":sid"=>$reply['id']));
			$links=pdo_fetchall("select * from ".tablename(CRUD::$table_sign_link)." where sid=:sid order by sort asc ",array(":sid"=>$reply['id']));

		}


		load()->func('tpl');


		include $this->template('form');


	}
	public function fieldsFormValidate($rid = 0) {



		return '';
	}
	public function fieldsFormSubmit($rid) {
		global $_GPC, $_W;


		$sid=$_GPC['sin_id'];

		$data=array(
			'title'=>$_GPC['title'],
			'rid'=>$rid,
			'starttime'=>strtotime($_GPC['starttime']),
			'endtime'=>strtotime($_GPC['endtime']),
			'follow_credit_allow'=>$_GPC['follow_credit_allow'],
			'follow_credit'=>$_GPC['follow_credit'],
			'leave_credit_clear'=>$_GPC['leave_credit_clear'],
			'sign_credit'=>$_GPC['sign_credit'],
			'sync_credit'=>$_GPC['sync_credit'],
			'sin_suc_msg'=>$_GPC['sin_suc_msg'],
			'sin_suc_fail'=>$_GPC['sin_suc_fail'],
			'rule'=>htmlspecialchars_decode($_GPC['rule']),
			'weid'=>$this->weid,
			'copyright'=>$_GPC['copyright'],
			'new_icon'=>$_GPC['new_icon'],
			'new_title'=>$_GPC['new_title'],
			'new_content'=>$_GPC['new_content'],
			'createtime'=>TIMESTAMP
		);

		if(empty($sid)){

			CRUD::create(CRUD::$table_sign,$data);
			$sid=pdo_insertid();

		}else{

			CRUD::updateById(CRUD::$table_sign,$data,$sid);
		}

		//连续 签到处理
		$serialids=array();

		$serial_ids=$_GPC['serial_ids'];
		$serial_days=$_GPC['serial_day'];
		$serial_credits=$_GPC['serial_credit'];

		if(is_array($serial_ids)){

			foreach($serial_ids as $key=>$value){
				$value=intval($value);
				$d=array(
					'sid'=>$sid,
					'day'=>$serial_days[$key],
					'credit'=>$serial_credits[$key],
					'createtime'=>TIMESTAMP

				);

				if(empty($value)){
					CRUD::create(CRUD::$table_sign_serial,$d);
					$serialids[]=pdo_insertid();
				}else{
					CRUD::updateById(CRUD::$table_sign_serial,$d,$value);
					$serialids[]=$value;
				}

			}


			if(count($serialids)>0){

				pdo_query("delete from ".tablename(CRUD::$table_sign_serial)." where sid='{$sid}' and id not in (".implode(",",$serialids).")");
			}else{
				pdo_query("delete from ".tablename(CRUD::$table_sign_serial)." where sid='{$sid}' ");
			}


		}



		//快捷菜单处理

		$link_ids = $_GPC ['link_ids'];
		$link_urls = $_GPC ['link_url'];
		$link_names = $_GPC ['link_name'];
		$link_sorts=$_GPC['link_sort'];


		pdo_query ( "delete from " . tablename ( CRUD::$table_sign_link ) . " where sid=:sid",array(":sid"=>$sid) );
		if (is_array ( $link_ids )) {
			foreach ( $link_ids as $key => $value ) {
				$value = intval ( $value );
				$d = array (
					"sid"=>$sid,
					"sort" => $link_sorts [$key],
					"link_name" => $link_names[$key],
					"link_url"=>$link_urls[$key],
					'createtime'=>TIMESTAMP
				);

				CRUD::create(CRUD::$table_sign_link,$d);
			}
		}





		return true;
	}
	public function ruleDeleted($rid) {

		$sin=CRUD::findUnique(CRUD::$table_sign,array(":rid"=>$rid));

		pdo_delete(CRUD::$table_sign_award,array("sid"=>$sin['id']));
		pdo_delete(CRUD::$table_sign_user,array("sid"=>$sin['id']));
		pdo_delete(CRUD::$table_sign_serial,array("sid"=>$sin['id']));
		pdo_delete(CRUD::$table_sign_record,array("sid"=>$sin['id']));

		pdo_delete(CRUD::$table_sign, array('rid' => $rid));

	}
    
    
    
   

}