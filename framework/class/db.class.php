<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
define('PDO_DEBUG', true);

class DB {
	protected $pdo;
	protected $cfg;
	protected $tablepre;
	protected $result;
	protected $statement;
	protected $errors = array();
	protected $link = array();

	public function getPDO() {
		return $this->pdo;
	}

	public function __construct($name = 'master') {
		global $_W;
		$this->cfg = $_W['config']['db'];
		$this->connect($name);
	}

	public function connect($name = 'master') {
		if(is_array($name)) {
			$cfg = $name;
		} else {
			$cfg = $this->cfg[$name];
		}
		$this->tablepre = $cfg['tablepre'];
		if(empty($cfg)) {
			exit("The master database is not found, Please checking 'data/config.php'");
		}
		$dsn = "mysql:dbname={$cfg['database']};host={$cfg['host']};port={$cfg['port']}";
		$dbclass = '';
		$options = array();
		if (class_exists('PDO')) {
			if (extension_loaded("pdo_mysql") && in_array('mysql', PDO::getAvailableDrivers())) {
				$dbclass = 'PDO';
				$options = array(PDO::ATTR_PERSISTENT => $cfg['pconnect']);
			} else {
				if(!class_exists('_PDO')) {
					include IA_ROOT . '/framework/library/pdo/PDO.class.php';
				}
				$dbclass = '_PDO';
			}
		} else {
			include IA_ROOT . '/framework/library/pdo/PDO.class.php';
			$dbclass = 'PDO';
		}
		$this->pdo = new $dbclass($dsn, $cfg['username'], $cfg['password'], $options);
		$sql = "SET NAMES '{$cfg['charset']}';";
		$this->pdo->exec($sql);
		$this->pdo->exec("SET sql_mode='';");
		if(is_string($name)) {
			$this->link[$name] = $this->pdo;
		}
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['error'] = $this->pdo->errorInfo();
			$this->debug(false, $info);
		}
	}

	public function prepare($sql) {
		$statement = $this->pdo->prepare($sql);
		return $statement;
	}
	
	
	public function query($sql, $params = array()) {
		$starttime = microtime();
		if (empty($params)) {
			$result = $this->pdo->exec($sql);
			if(PDO_DEBUG) {
				$info = array();
				$info['sql'] = $sql;
				$info['error'] = $this->pdo->errorInfo();
				$this->debug(false, $info);
			}
			return $result;
		}
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			return $statement->rowCount();
		}
	}

	
	public function fetchcolumn($sql, $params = array(), $column = 0) {
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			return $statement->fetchColumn($column);
		}
	}
	
	
	public function fetch($sql, $params = array()) {
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			return $statement->fetch(pdo::FETCH_ASSOC);
		}
	}

	
	public function fetchall($sql, $params = array(), $keyfield = '') {
		$starttime = microtime();
		$statement = $this->prepare($sql);
		$result = $statement->execute($params);
		if(PDO_DEBUG) {
			$info = array();
			$info['sql'] = $sql;
			$info['params'] = $params;
			$info['error'] = $statement->errorInfo();
			$this->debug(false, $info);
		}
		$endtime = microtime();
		$this->performance($sql, $endtime - $starttime);
		if (!$result) {
			return false;
		} else {
			if (empty($keyfield)) {
				return $statement->fetchAll(pdo::FETCH_ASSOC);
			} else {
				$temp = $statement->fetchAll(pdo::FETCH_ASSOC);
				$rs = array();
				if (!empty($temp)) {
					foreach ($temp as $key => &$row) {
						if (isset($row[$keyfield])) {
							$rs[$row[$keyfield]] = $row;
						} else {
							$rs[] = $row;
						}
					}
				}
				return $rs;
			}
		}
	}
	
	public function get($tablename, $params = array(), $fields = array()) {
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		$condition = $this->implode($params, 'AND');
		$sql = "SELECT {$select} FROM " . $this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . " LIMIT 1";
		return $this->fetch($sql, $condition['params']);
	}
	
	public function getall($tablename, $params = array(), $fields = array(), $keyfield = '') {
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		$condition = $this->implode($params, 'AND');
		$sql = "SELECT {$select} FROM " .$this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $limitsql;
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}
	
	public function getslice($tablename, $params = array(), $limit = array(), &$total = null, $fields = array(), $keyfield = '') {
		$select = '*';
		if (!empty($fields)){
			if (is_array($fields)) {
				$select = '`'.implode('`,`', $fields).'`';
			} else {
				$select = $fields;
			}
		}
		$condition = $this->implode($params, 'AND');
		if (!empty($limit)) {
			if (is_array($limit)) {
				$limitsql = " LIMIT " . ($limit[0] - 1) * $limit[1] . ', ' . $limit[1];
			} else {
				$limitsql = strexists(strtoupper($limit), 'LIMIT') ? " $limit " : " LIMIT $limit";
			}
		}
		
		$sql = "SELECT {$select} FROM " . $this->tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : '') . $limitsql;
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($tablename) . (!empty($condition['fields']) ? " WHERE {$condition['fields']}" : ''));
		return $this->fetchall($sql, $condition['params'], $keyfield);
	}
	
	public function getcolumn($tablename, $params = array(), $field) {
		$result = $this->get($tablename, $params, array($field));
		if (!empty($result)) {
			return $result[$field];
		} else {
			return false;
		}
	}

	
	public function update($table, $data = array(), $params = array(), $glue = 'AND') {
		$fields = $this->implode($data, ',');
		$condition = $this->implode($params, $glue);
		$params = array_merge($fields['params'], $condition['params']);
		$sql = "UPDATE " . $this->tablename($table) . " SET {$fields['fields']}";
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $params);
	}

	
	public function insert($table, $data = array(), $replace = FALSE) {
		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
		$condition = $this->implode($data, ',');
		return $this->query("$cmd " . $this->tablename($table) . " SET {$condition['fields']}", $condition['params']);
	}
	
	
	public function insertid() {
		return $this->pdo->lastInsertId();
	}

	
	public function delete($table, $params = array(), $glue = 'AND') {
		$condition = $this->implode($params, $glue);
		$sql = "DELETE FROM " . $this->tablename($table);
		$sql .= $condition['fields'] ? ' WHERE '.$condition['fields'] : '';
		return $this->query($sql, $condition['params']);
	}

	
	public function begin() {
		$this->pdo->beginTransaction();
	}

	
	public function commit() {
		$this->pdo->commit();
	}

	
	public function rollback() {
		$this->pdo->rollBack();
	}

	
	private function implode($params, $glue = ',') {
		$result = array('fields' => ' 1 ', 'params' => array());
		$split = '';
		$suffix = '';
		$allow_operator = array('>', '<', '<>', '!=', '>=', '<=', '+=', '-=', 'LIKE', 'like');
		if (in_array(strtolower($glue), array('and', 'or'))) {
			$suffix = '__';
		}
		if (!is_array($params)) {
			$result['fields'] = $params;
			return $result;
		}
		if (is_array($params)) {
			$result['fields'] = '';
			foreach ($params as $fields => $value) {
				$operator = '';
				if (strpos($fields, ' ') !== FALSE) {
					list($fields, $operator) = explode(' ', $fields, 2);
					if (!in_array($operator, $allow_operator)) {
						$operator = '';
					}
				}
				if (empty($operator)) {
					$fields = trim($fields);
					if (is_array($value)) {
						$operator = 'IN';
					} else {
						$operator = '=';
					}
				} elseif ($operator == '+=') {
					$operator = " = `$fields` + ";
				} elseif ($operator == '-=') {
					$operator = " = `$fields` - ";
				}
				if (is_array($value)) {
					$result['fields'] .= $split . "`$fields` {$operator} ('".implode("','", $value)."')";
					$split = ' ' . $glue . ' ';
				} else {
					$result['fields'] .= $split . "`$fields` {$operator}  :{$suffix}$fields";
					$split = ' ' . $glue . ' ';
					$result['params'][":{$suffix}$fields"] = is_null($value) ? '' : $value;
				}
			}
		}
		return $result;
	}
	
	
	public function run($sql, $stuff = 'ims_') {
		if(!isset($sql) || empty($sql)) return;

		$sql = str_replace("\r", "\n", str_replace(' ' . $stuff, ' ' . $this->tablepre, $sql));
		$sql = str_replace("\r", "\n", str_replace(' `' . $stuff, ' `' . $this->tablepre, $sql));
		$ret = array();
		$num = 0;
		$sql = preg_replace("/\;[ \f\t\v]+/", ';', $sql);
		foreach(explode(";\n", trim($sql)) as $query) {
			$ret[$num] = '';
			$queries = explode("\n", trim($query));
			foreach($queries as $query) {
				$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
			}
			$num++;
		}
		unset($sql);
		foreach($ret as $query) {
			$query = trim($query);
			if($query) {
				$this->query($query, array());
			}
		}
	}
	
	
	public function fieldexists($tablename, $fieldname) {
		$isexists = $this->fetch("DESCRIBE " . $this->tablename($tablename) . " `{$fieldname}`", array());
		return !empty($isexists) ? true : false;
	}
	
	
	public function indexexists($tablename, $indexname) {
		if (!empty($indexname)) {
			$indexs = $this->fetchall("SHOW INDEX FROM " . $this->tablename($tablename), array(), '');
			if (!empty($indexs) && is_array($indexs)) {
				foreach ($indexs as $row) {
					if ($row['Key_name'] == $indexname) {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	
	public function tablename($table) {
		return "`{$this->tablepre}{$table}`";
	}

	
	public function debug($output = true, $append = array()) {
		if(!empty($append)) {
			$output = false;
			array_push($this->errors, $append);
		}
		if($output) {
			print_r($this->errors);
		} else {
			if (!empty($append['error'][1])) {
				$traces = debug_backtrace();
				$ts = '';
				foreach($traces as $trace) {
					$trace['file'] = str_replace('\\', '/', $trace['file']);
					$trace['file'] = str_replace(IA_ROOT, '', $trace['file']);
					$ts .= "file: {$trace['file']}; line: {$trace['line']}; <br />";
				}
				$params = var_export($append['params'], true);
				if (!function_exists('message')) {
					load()->web('common');
					load()->web('template');
				}
				message("SQL: <br/>{$append['sql']}<hr/>Params: <br/>{$params}<hr/>SQL Error: <br/>{$append['error'][2]}<hr/>Traces: <br/>{$ts}");
			}
		}
		return $this->errors;
	}

	
	public function tableexists($table) {
		if(!empty($table)) {
			$data = $this->fetch("SHOW TABLES LIKE '{$this->tablepre}{$table}'", array());
			if(!empty($data)) {
				$data = array_values($data);
				$tablename = $this->tablepre . $table;
				if(in_array($tablename, $data)) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function performance($sql, $runtime = 0) {
		global $_W;
		if ($runtime == 0) {
			return false;
		}
		if (strexists($sql, 'core_performance')) {
			return false;
		}
				if (empty($_W['config']['setting']['maxtimesql'])) {
			$_W['config']['setting']['maxtimesql'] = 5;
		}
		if ($runtime > $_W['config']['setting']['maxtimesql']) {
			$sqldata = array(
				'type' => '2',
				'runtime' => $runtime,
				'runurl' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
				'runsql' => $sql,
				'createtime' => time()
			);
			$this->insert('core_performance', $sqldata);
		}
		return true;
	}
}
