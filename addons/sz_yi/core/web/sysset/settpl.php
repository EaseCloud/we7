<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_GPC;
$tpl = trim($_GPC['tpl']);
load()->func('tpl');
if ($tpl == 'setmenu') {
    $spec = array(
        "id" => random(32),
        "type" => $_GPC['type']
    );
    include $this->template('web/sysset/tpl/setmenu');
}