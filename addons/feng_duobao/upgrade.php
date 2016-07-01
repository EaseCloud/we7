<?php
/*
*/
if(!pdo_fieldexists('feng_wechat', 'win_mess')) {  
	pdo_query("ALTER TABLE ".tablename('feng_wechat')." ADD `win_mess` varchar(200) DEFAULT NULL;");
}

?>