<?php


global $_W, $_GPC;

if (!$_W['isfounder']) {
    message('您无权操作!', '', 'error');
}
function table2sql($table)
{
    global $db;
    $tabledump   = "DROP TABLE IF EXISTS $table;\r\n";
    $createtable = pdo_fetch("SHOW CREATE TABLE $table");
    $tabledump .= $createtable["Create Table"] . ";\r\n";
    $rows = pdo_fetchall("SELECT * FROM $table");
    foreach ($rows as $row) {
        $comma = "";
        $tabledump .= "INSERT INTO $table VALUES(";
        foreach ($row as $k => $v) {
            $tabledump .= $comma . "'" . addslashes($v) . "'";
            $comma = ",";
        }
        $tabledump .= ");\r\n";
    }
    return $tabledump;
}
if (checksubmit('submit')) {
    $sqls   = "";
    $sql    = "SHOW TABLES LIKE '%sz_yi_%'";
    $tables = pdo_fetchall($sql);
    foreach ($tables as $k => $t) {
        $table     = array_values($t);
        $tablename = $table[0];
        $sqls .= table2sql($tablename) . "\r\n\r\n";
    }
    $filename = "sz_yi_data_" . date('Y_m_d_H_i_s') . ".sql";
    header('Pragma: public');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: pre-check=0, post-check=0, max-age=0');
    header('Content-Encoding: UTF8');
    header('Content-type: application/force-download');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    m('cache')->set('systembackuptime', date('Y-m-d H:i:s'), 'global');
    exit($sqls);
}
$lasttime = m('cache')->getString('systembackuptime', 'global');
load()->func('tpl');
include $this->template('backup');
