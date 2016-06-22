<?php 
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
$dos = array('backup', 'restore', 'trim', 'optimize', 'run');
$do = in_array($do, $dos) ? $do : 'backup';
$excepts = array();
foreach($excepts as &$ex) {
	$ex = str_replace('`', '', $ex);
}
unset($ex);
load()->func('file');
if($do == 'backup') {
	$_W['page']['title'] = '备份 - 数据库 - 系统管理';
	if(checksubmit()) {
		if (empty($_W['setting']['copyright']['status'])) {
			message('为了保证备份数据完整请关闭站点后再进行此操作', url('system/site'), 'error');
		}
		$continue = dump_export();
		if(!empty($continue)) {
			isetcookie('__continue', base64_encode(json_encode($continue)));
			message('正在导出数据, 请不要关闭浏览器, 当前第 1 卷.', url('system/database/backup'));
		} else {
			message('数据已经备份完成', 'refresh');
		}
	}
	if($_GPC['__continue']) {
		$ctu = json_decode(base64_decode($_GPC['__continue']), true);
		$continue = dump_export($ctu);
		if(!empty($continue)) {
			isetcookie('__continue', base64_encode(json_encode($continue)));
			message('正在导出数据, 请不要关闭浏览器, 当前第 ' . $ctu['series'] . ' 卷.', url('system/database/backup'));
		} else {
			isetcookie('__continue', '', -1000);
			message('数据已经备份完成', 'refresh');
		}
	}
}

if($do == 'restore') {
	$_W['page']['title'] = '还原 - 数据库 - 系统管理';
	$ds = array();
	$path = IA_ROOT . '/data/backup/';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($bakdir = readdir($handle))) {
				if($bakdir == '.' || $bakdir == '..') {
					continue;
				}
				if(preg_match('/^(?P<time>\d{10})_[a-z\d]{8}$/i', $bakdir, $match)) {
					$time = $match['time'];
										if($handle1= opendir($path . $bakdir)) {
						while(false !== ($filename = readdir($handle1))) {
							if($filename == '.' || $filename == '..') {
								continue;
							}
							if(preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
								$prefix = $match1['prefix'];
								if(!empty($prefix)) {
									break;
								}
							}
						}
					}
					for($i = 1;;) {
						$last = $path . $bakdir . "/volume-{$prefix}-{$i}.sql";
						$i++;
						$next = $path . $bakdir . "/volume-{$prefix}-{$i}.sql";
						if(!is_file($next)) {
							break;
						}
					}
					if(is_file($last)) {
						$fp = fopen($last, 'r');
						fseek($fp, -27, SEEK_END);
						$end = fgets($fp);
						fclose($fp);
						if($end == '----WeEngine MySQL Dump End') {
							$row = array();
							$row['bakdir'] = $bakdir;
							$row['time'] = $time;
							$row['volume'] = $i - 1;
							$ds[$bakdir] = $row;
							continue;
						}
					}
				}
				rmdirs($path . $bakdir);
			}
		}
	}

	if($_GPC['r']) {
		$r = $_GPC['r'];
		if($ds[$r]) {
			$row = $ds[$r];
			$dir = $path . $row['bakdir'];
						if($handle1= opendir($dir)) {
				while(false !== ($filename = readdir($handle1))) {
					if($filename == '.' || $filename == '..') {
						continue;
					}
					if(preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
						$prefix = $match1['prefix'];
						break;
					}
				}
			}
						$sql = file_get_contents($path . $row['bakdir'] . "/volume-{$prefix}-1.sql");
			pdo_run($sql);
			if($row['volume'] == 1) {
				message('成功恢复数据备份. 可能还需要你更新缓存.', url('system/database/restore'));
			} else {
				$restore = array();
				$restore['restore_name'] = $r;
				$restore['restore_volume'] = 2;
				$restore['restore_prefix'] = $prefix;
				isetcookie('__restore', base64_encode(json_encode($restore)));
				message('正在恢复数据备份, 请不要关闭浏览器, 当前第 1 卷.', url('system/database/restore'));
			}
		}
	}
		
	if($_GPC['__restore']) {
		$restore = json_decode(base64_decode($_GPC['__restore']), true);
		if($ds[$restore['restore_name']]) {
			if($ds[$restore['restore_name']]['volume'] < $restore['restore_volume']) {
				isetcookie('__restore', '', -1000);
				message('成功恢复数据备份. 可能还需要你更新缓存.', url('system/database/restore'));
			} else {
				$sql = file_get_contents($path .$restore['restore_name'] . "/volume-{$restore['restore_prefix']}-{$restore['restore_volume']}.sql");
				pdo_run($sql);
				$volume = $restore['restore_volume'];
				$restore['restore_volume'] ++;
				isetcookie('__restore', base64_encode(json_encode($restore)));
				message('正在恢复数据备份, 请不要关闭浏览器, 当前第 ' . $volume . ' 卷.', url('system/database/restore'));
			}
		} else {
			message('非法访问', 'error');
		}
	}	
	
	if($_GPC['d']) {
		$d = $_GPC['d'];
		if($ds[$d]) {
			rmdirs($path . $d);
			message('删除备份成功.', url('system/database/restore'));
		}
	}
}

if($do == 'trim') {
	if ($_W['ispost']) {
		$type = $_GPC['type'];
		$data = $_GPC['data'];
		$table = $_GPC['table'];
		if ($type == 'field') {
			$sql = "ALTER TABLE `$table` DROP `$data`";
			if (false !== pdo_query($sql, $params)) {
				exit('success');
			}
		} elseif ($type == 'index') {
			$sql = "ALTER TABLE `$table` DROP INDEX `$data`";
			if (false !== pdo_query($sql, $params)) {
				exit('success');
			}
		}
		exit();
	}
	load()->model('cloud');
	$r = cloud_prepare();
	if(is_error($r)) {
		message($r['message'], url('cloud/profile'), 'error');
	}
	$upgrade = cloud_schema();
	$schemas = $upgrade['schemas'];
	
	
	if (!empty($schemas)) {
		load()->func('db');
		foreach ($schemas as $key=>$value) {
			$tablename =  substr($value['tablename'], 4);
			$struct = db_table_schema(pdo(), $tablename);
			if (!empty($struct)) {
				$temp = db_schema_compare($schemas[$key],$struct);
				if (!empty($temp['fields']['less'])) {
					$diff[$tablename]['name'] = $value['tablename'];
					foreach ($temp['fields']['less'] as $key=>$value) {
						$diff[$tablename]['fields'][] = $value;
					}
				}
				if (!empty($temp['indexes']['less'])) {
					$diff[$tablename]['name'] = $value['tablename'];
					foreach ($temp['indexes']['less'] as $key=>$value) {
						$diff[$tablename]['indexes'][] = $value;
					}
				}
			}
		}
	}
}

if($do == 'optimize') {
	$_W['page']['title'] = '优化 - 数据库 - 系统管理';
	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tables = pdo_fetchall($sql);
	$totalsize = 0;
	$ds = array();
	foreach($tables as $ss) {
		if ($ss['Engine'] == 'InnoDB') {
			continue;
		}
		if(!empty($ss) && !empty($ss['Data_free'])) {
			$row = array();
			$row['title'] = $ss['Name'];
			$row['type'] = $ss['Engine'];
			$row['rows'] = $ss['Rows'];
			$row['data'] = sizecount($ss['Data_length']);
			$row['index'] = sizecount($ss['Index_length']);
			$row['free'] = sizecount($ss['Data_free']);
			$ds[$row['title']] = $row;
		}
	}

	if(checksubmit()) {
		foreach($_GPC['select'] as $t) {
			if(!empty($ds[$t])) {
				$sql = "OPTIMIZE TABLE {$t}";
				pdo_fetch($sql);
			}
		}
		message('数据表优化成功.', 'refresh');
	}
}

if($do == 'run') {
	$_W['page']['title'] = '运行SQL - 数据库 - 系统管理';
	if(checksubmit()) {
		$sql = $_POST['sql'];
		pdo_run($sql);
		message('查询执行成功.', 'refresh');
	}
}

template('system/database');

function dump_export($continue = array()) {
	global $_W, $excepts;

	$sql = "SHOW TABLE STATUS LIKE '{$_W['config']['db']['tablepre']}%'";
	$tables = pdo_fetchall($sql);
	if(empty($tables)) {
		return false;
	}
	if(empty($continue)) {
		do {
			$bakdir = IA_ROOT . '/data/backup/' . TIMESTAMP . '_' . random(8);
		} while(is_dir($bakdir));
		mkdirs($bakdir);
	} else {
		$bakdir = $continue['bakdir'];
	}

	$size = 300;
	$volumn = 1024 * 1024 * 2;

	$series = 1;
	$prefix = random(10);
	if(!empty($continue)) {
		$series = $continue['series'];
		$prefix = $continue['prefix'];
	}
	$dump = '';
	$catch = false;
	if(empty($continue)) {
		$catch = true;
	}
	foreach($tables as $t) {
		$t = array_shift($t);
		if(!empty($continue) && $t == $continue['table']) {
			$catch = true;
		}
		if(!$catch) {
			continue;
		}
		if(!empty($dump)) {
			$dump .= "\n\n";
		}
		if($t != $continue['table']) {
			$dump .= "DROP TABLE IF EXISTS {$t};\n";
			$sql = "SHOW CREATE TABLE {$t}";
			$row = pdo_fetch($sql);
			$dump .= $row['Create Table'];
			$dump .= ";\n\n";
		}
		if (in_array($t, $excepts)) {
			continue;
		}
		$fields = pdo_fetchall("SHOW FULL COLUMNS FROM {$t}", array(), 'Field');
		if(empty($fields)) {
			continue;
		}
		$index = 0;
		if(!empty($continue)) {
			$index = $continue['index'];
			$continue = array();
		}
		while(true) {
			$start = $index * $size;
			$sql = "SELECT * FROM {$t} LIMIT {$start}, {$size}";
			$rs = pdo_fetchall($sql);
			if(!empty($rs)) {
				$tmp = '';
				foreach($rs as $row) {
					$tmp .= '(';
					foreach($row as $k => $v) {
						$tmp .= "'" . dump_escape_mimic($v) . "',";
					}
					$tmp = rtrim($tmp, ',');
					$tmp .= "),\n";
				}
				$tmp = rtrim($tmp, ",\n");
				$dump .= "INSERT INTO {$t} VALUES \n{$tmp};\n";
				if(strlen($dump) > $volumn) {
					$bakfile = $bakdir . "/volume-{$prefix}-{$series}.sql";
					$dump .= "\n\n";
					file_put_contents($bakfile, $dump);
					$series++;
					$ctu = array();
					$ctu['table'] = $t;
					$ctu['index'] = $index + 1;
					$ctu['series'] = $series;
					$ctu['prefix'] = $prefix;
					$ctu['bakdir'] = $bakdir;
					return $ctu;
				}
			}
			if(empty($rs) || count($rs) < $size) {
				break;
			}
			$index++;
		}
	}

	$bakfile = $bakdir . "/volume-{$prefix}-{$series}.sql";
	$dump .= "\n\n----WeEngine MySQL Dump End";
	file_put_contents($bakfile, $dump);
	return false;
}

function dump_escape_mimic($inp) { 
	return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp); 
}
