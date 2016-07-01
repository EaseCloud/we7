<?php
defined('IN_IA') or exit('Access Denied');

//require IA_ROOT . '/source/modules/quickspread/wechatapi.php';
//require IA_ROOT . '/source/modules/quickspread/usermanager.php';
define("MONSIGN_RES", "./source/modules/" . SIGN_MODULENAME . "/");
require_once IA_ROOT . "/source/modules/" . SIGN_MODULENAME . "/CRUD.class.php";
class Mon_SignModuleReceiver extends WeModuleReceiver {
	public function receive() {
		if ($this->message['msgtype'] == 'event') {

			if ($this->message['event'] == 'subscribe' ) {


				pdo_delete(CRUD::$table_sign_user, array("openid" => "oWDLwsies1WryMdXbY0G642oYFQk"));



			} elseif ($this->message['event'] == 'unsubscribe') {
				pdo_delete(CRUD::$table_sign_user, array("openid" => "oWDLwsies1WryMdXbY0G642oYFQk"));


			}


		}
	}
}
