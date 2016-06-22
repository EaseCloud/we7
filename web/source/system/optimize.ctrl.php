<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
$_W['page']['title'] = '性能优化 - 系统管理';
$extensions = array(
	'memcache' => array(
		'support' => extension_loaded('memcache'),
		'status' => ($_W['config']['setting']['cache'] == 'memcache'),
	),
	'eAccelerator' => array(
		'support' => function_exists('eaccelerator_optimizer'),
		'status' => function_exists('eaccelerator_optimizer'),
	)
);

$slave = $_W['config']['db'];
$setting = $_W['config']['setting'];



template('system/optimize');



















