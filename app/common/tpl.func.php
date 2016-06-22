<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');

function _tpl_form_field_date($name, $value = '', $withtime = false) {
	$html = '';
	if ($withtime && !defined('TPL_INIT_DATA_TIME')) {
		$html = '
			<script type="text/javascript">
				require(["datetimepicker"], function($){
					$(function(){
						$(".datetimepicker.datetime").each(function(){
							var opt = {
								language: "zh-CN",
								minView: 0,
								autoclose: true,
								format : "yyyy-mm-dd",
								todayBtn: true,
								minuteStep: 5
							};
							$(this).datetimepicker(opt);
						});
					});
				});
			</script>
			';
		define('TPL_INIT_DATA_TIME', true);
	}

	if (!$withtime  && !defined('TPL_INIT_DATA') ) {
		$html = '
			<script type="text/javascript">
				require(["datetimepicker"], function($){
					$(function(){
						$(".datetimepicker.date").each(function(){
							var opt = {
								language: "zh-CN",
								minView: 2,
								format: "yyyy-mm-dd",
								autoclose: true,
								todayBtn: true
							};
							$(this).datetimepicker(opt);
						});
					});
				});
			</script>
			';
		define('TPL_INIT_DATA', true);
	}

	$class = $withtime ? 'datetime' : 'date';
	$placeholder = $withtime ? '日期时刻' : '日期';
	$value = !empty($value) ? $value : ($withtime ? date('Y-m-d H:i') : date('Y-m-d'));
	$html .= '<input type="text" name="' . $name . '" value="'.$value.'" placeholder="'.$placeholder.'"  readonly="readonly" class="datetimepicker '.$class.' form-control" style="padding:6px 12px;"/>';
	return $html;
}


function tpl_form_field_image($name, $value = ''){
	
	$thumb = empty($value) ? 'images/global/nopic.jpg' : $value;
	$thumb = tomedia($thumb);
	
	$html = <<<EOF

<div class="input-group">
	<input type="text" name="$name" value="$value" class="form-control" autocomplete="off" readonly="readonly">
	<span class="input-group-btn">
		<button class="btn btn-default" onclick="appupload(this)" type="button">上传图片</button>
	</span>
</div>
<span class="help-block">
	<img style="max-height:100px;" src="$thumb" >
</span>

<script>
window.appupload = window.appupload || function(obj){
	require(['jquery', 'util'], function($, u){
		u.image(obj, function(url){
			$(obj).parent().prev().val(url.attachment);
			$(obj).parent().parent().next().find('img').attr('src',url.url);
		});
	});
}
</script>

EOF;
	return $html;
}