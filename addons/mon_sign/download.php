<?php
if (PHP_SAPI == 'cli')
    die('This example should only be run from a Web Browser');
global $_GPC,$_W;
$sid= intval($_GPC['sid']);
if(empty($sid)){
    message('抱歉，传递的参数错误！','', 'error');              
}

  $params = array(':sid' => $sid);
  $list = pdo_fetchall("SELECT * FROM " . tablename(CRUD::$table_sign_user) . " WHERE sid = :sid " . $where . " ORDER BY credit	 DESC ", $params);

iconv("UTF-8", "GB2312", $item['uname']."\t");


$tableheader = array('openid', "昵称", "积分", "连续签到天数",  "签到次数");
$html = "\xEF\xBB\xBF";
foreach ($tableheader as $value) {
	$html .= $value . "\t ,";
}
$html .= "\n";
foreach ($list as $value) {
	$html .= $value['openid'] . "\t ,";
	 $html .=  $value['nickname'] . "\t ,";
	$html .= $value['credit'] . "\t ,";
	$html .= $value['sin_serial'] . "\t ,";
    $html .= $value['sin_count'] . "\n ";

}


header("Content-type:text/csv");
header("Content-Disposition:attachment; filename=签到用户数据.csv");

echo $html;
exit();
