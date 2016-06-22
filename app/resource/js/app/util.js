(function(window) {
	var module = {};

	module.querystring = function(name){
		var result = location.search.match(new RegExp("[\?\&]" + name+ "=([^\&]+)","i"));
		if (result == null || result.length < 1){
			return "";
		}
		return result[1];
	}

	module.tomedia = function(src, forcelocal){
		if(!src) {
			return '';
		}
		if(src.indexOf('./addons') == 0) {
			return window.sysinfo.siteroot + src.replace('./', '');
		}
		if(src.indexOf(window.sysinfo.siteroot) != -1 && src.indexOf('/addons/') == -1) {
			src = src.substr(src.indexOf('images/'));
		}
		if(src.indexOf('./resource') == 0) {
			src = 'app/' + src.substr(2);
		}
		var t = src.toLowerCase();
		if(t.indexOf('http://') != -1 || t.indexOf('https://') != -1 ) {
			return src;
		}
		if(forcelocal || !window.sysinfo.attachurl_remote) {
			src = window.sysinfo.attachurl_local + src;
		} else {
			src = window.sysinfo.attachurl_remote + src;
		}
		return src;
	};

	module.dialog = function(title, content, footer, options) {
		if(!options) {
			options = {};
		}
		if(!options.containerName) {
			options.containerName = 'modal-message';
		}
		var modalobj = $('#' + options.containerName);
		if(modalobj.length == 0) {
			$(document.body).append('<div id="' + options.containerName + '" class="modal animated" tabindex="-1" role="dialog" aria-hidden="true"></div>');
			modalobj = $('#' + options.containerName);
		}
		var html =
			'<div class="modal-dialog modal-sm">'+
			'	<div class="modal-content">';
		if(title) {
			html +=
			'<div class="modal-header">'+
			'	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>'+
			'	<h3>' + title + '</h3>'+
			'</div>';
		}
		if(content) {
			if(!$.isArray(content)) {
				html += '<div class="modal-body">'+ content + '</div>';
			} else {
				html += '<div class="modal-body">正在加载中</div>';
			}
		}
		if(footer) {
			html +=
			'<div class="modal-footer">'+ footer + '</div>';
		}
		html += '	</div></div>';
		modalobj.html(html);
		if(content && $.isArray(content)) {
			var embed = function(c) {
				modalobj.find('.modal-body').html(c);
			};
			if(content.length == 2) {
				$.post(content[0], content[1]).success(embed);
			} else {
				$.get(content[0]).success(embed);
			}
		}
		return modalobj;
	};
	
	module.message = function(msg, redirect, type){
		if(!redirect && !type){
			type = 'info';
		}
		if($.inArray(type, ['success', 'error', 'info', 'warning']) == -1) {
			type = '';
		}
		if(type == '') {
			type = redirect == '' ? 'error' : 'success';
		}
		
		var icons = {
			success : 'check-circle',
			error :'times-circle',
			info : 'info-circle',
			warning : 'exclamation-triangle'
		};
		var p = '';
		if(redirect && redirect.length > 0){
			if(redirect == 'back'){
				p = '<p>[<a href="javascript:;" onclick="history.go(-1)">返回上一页</a>] &nbsp; [<a href="./?refresh">回首页</a>]</p>';
			}else{
				p = '<p><a href="' + redirect + '" target="main" data-dismiss="modal" aria-hidden="true">如果你的浏览器在 <span id="timeout"></span> 秒后没有自动跳转，请点击此链接</a></p>';
			}
		}
		var content = 
			'			<i class="pull-left fa fa-4x fa-'+icons[type]+'"></i>'+
			'			<div class="pull-left"><p>'+ msg +'</p>' +
			p +
			'			</div>'+
			'			<div class="clearfix"></div>';
		var footer = 
			'			<button type="button" class="btn btn-default" data-dismiss="modal">确认</button>';
		var modalobj = module.dialog('系统提示', content, footer);
		modalobj.find('.modal-content').addClass('alert alert-'+type);
		if(redirect) {
			var timer = 0;
			var timeout = 3;
			modalobj.find("#timeout").html(timeout);
			modalobj.on('show.bs.modal', function(){doredirect();});
			modalobj.on('hide.bs.modal', function(){timeout = 0;doredirect(); });
			modalobj.on('hidden.bs.modal', function(){modalobj.remove();});
			function doredirect() {
				timer = setTimeout(function(){
					if (timeout <= 0) {
						modalobj.modal('hide');
						clearTimeout(timer);
						window.location.href = redirect;
						return;
					} else {
						timeout--;
						modalobj.find("#timeout").html(timeout);
						doredirect();
					}
				}, 1000);
			}
		}
		modalobj.on('show.bs.modal', function(e){
			$(e.target).removeClass('bounceOut');
			$(e.target).addClass('bounceIn');
		})
		modalobj.on('hide.bs.modal', function(e){
			if(!e.target.animated) {
				$(e.target).removeClass('bounceIn');
				$(e.target).addClass('bounceOut');
				e.preventDefault();
				e.target.animated = true;
				setTimeout(function(){
					$(e.target).modal('hide');
					e.target.animated = false;
				}, 1000);
			}
		})
		modalobj.modal('show');
	};

	module.map = function(val, callback){
		require(['map'], function(BMap){
			if(!val) {
				val = {};
			}
			if(!val.lng) {
				val.lng = 116.403851;
			}
			if(!val.lat) {
				val.lat = 39.915177;
			}
			var point = new BMap.Point(val.lng, val.lat);
			var geo = new BMap.Geocoder();
			
			var modalobj = $('#map-dialog');
			if(modalobj.length == 0) {
				var content =
					'<div class="form-group">' +
						'<div class="input-group">' +
							'<input type="text" class="form-control" placeholder="请输入地址来直接查找相关位置">' +
							'<div class="input-group-btn">' +
								'<button class="btn btn-default"><i class="icon-search"></i> 搜索</button>' +
							'</div>' +
						'</div>' +
					'</div>' +
					'<div id="map-container" style="height:400px;"></div>';
				var footer =
					'<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>' +
					'<button type="button" class="btn btn-primary">确认</button>';
				modalobj = module.dialog('请选择地点', content, footer, {containerName : 'map-dialog'});
				modalobj.find('.modal-dialog').css('width', '80%');
				modalobj.modal({'keyboard': false});
				
				map = module.map.instance = new BMap.Map('map-container');
				map.centerAndZoom(point, 12);
				map.enableScrollWheelZoom();
				map.enableDragging();
				map.enableContinuousZoom();
				map.addControl(new BMap.NavigationControl());
				map.addControl(new BMap.OverviewMapControl());
				marker = module.map.marker = new BMap.Marker(point);
				marker.setLabel(new BMap.Label('请您移动此标记，选择您的坐标！', {'offset': new BMap.Size(10,-20)}));
				map.addOverlay(marker);
				marker.enableDragging();
				marker.addEventListener('dragend', function(e){
					var point = marker.getPosition();
					geo.getLocation(point, function(address){
						modalobj.find('.input-group :text').val(address.address);
					});
				});
				function searchAddress(address) {
					geo.getPoint(address, function(point){
						map.panTo(point);
						marker.setPosition(point);
						marker.setAnimation(BMAP_ANIMATION_BOUNCE);
						setTimeout(function(){marker.setAnimation(null)}, 3600);
					});
				}
				modalobj.find('.input-group :text').keydown(function(e){
					if(e.keyCode == 13) {
						var kw = $(this).val();
						searchAddress(kw);
					}
				});
				modalobj.find('.input-group button').click(function(){
					var kw = $(this).parent().prev().val();
					searchAddress(kw);
				});
			}
			modalobj.off('shown.bs.modal');
			modalobj.on('shown.bs.modal', function(){
				marker.setPosition(point);
				map.panTo(marker.getPosition());
			});
			
			modalobj.find('button.btn-primary').off('click');
			modalobj.find('button.btn-primary').on('click', function(){
				if($.isFunction(callback)) {
					var point = module.map.marker.getPosition();
					geo.getLocation(point, function(address){
						var val = {lng: point.lng, lat: point.lat, label: address.address};
						callback(val);
					});
				}
				modalobj.modal('hide');
			});
			modalobj.modal('show');
		});
	}; // end of map
	
	/**
	 * 点击指定的元素, 发送验证码, 并显示倒计时, 并通知发送状态
	 * @param elm 元素节点
	 * @param no 要发送验证码的手机号
	 * @param callback 通知回调, 这个函数接受两个参数
	 * function(ret, state)
	 * ret 通知结果, success 成功, failed 失败, downcount 倒计时
	 * state 通知内容, success 时无数据, failed 时指明失败原因, downcount 时指明当前倒数
	 */
	module.sendCode = function(elm, no, callback) {
		if(!no || !elm || !$(elm).attr('uniacid')) {
			if($.isFunction(callback)) {
				callback('failed', '给定的参数有错误');
			}
			return;
		}
		$(elm).attr("disabled", true);
		var downcount = 60;
		$(elm).html(downcount + "秒后重新获取");

		var timer = setInterval(function(){
			downcount--;
			if(downcount <= 0){
				clearInterval(timer);
				$(elm).html("重新获取验证码");
				$(elm).attr("disabled", false);
				downcount = 60;
			}else{
				if($.isFunction(callback)) {
					callback('downcount', downcount);
				}
				$(elm).html(downcount + "秒后重新获取");
			}
		}, 1000);

		var params = {};
		params.receiver = no;
		params.uniacid = $(elm).attr('uniacid');
		if($(elm).attr('table')) {
			params.table = $(elm).attr('table');
		}
		$.post('../web/index.php?c=utility&a=verifycode', params).success(function(dat){
			if(dat == 'success') {
				if($.isFunction(callback)) {
					callback('success', null);
				}
			} else {
				if($.isFunction(callback)) {
					callback('failed', dat);
				}
			}
		});
	};
	
	module.image = function(obj, callback, options) {
		require(['webuploader'], function(WebUploader){
			var content = 
				'<div id="uploader" class="uploader app"> '+
				'	<div class="queueList">'+
				'		<div id="dndArea" class="placeholder">'+
				'			<div id="filePicker"></div>'+
				'		</div>'+
				'	</div>'+
				'</div>';
			var footer = '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>';
			var modalobj = module.dialog('请上传图片', content, footer, {containerName: 'image-container'});
			
			modalobj.modal({'keyboard': false});
			modalobj.find('button.btn-primary').off('click');
			modalobj.find('button.btn-primary').on('click', function(){
				modalobj.modal('hide');
			});
			var i = module.querystring('i');
			var j = module.querystring('j');
			
			defaultOptions = {
				pick: {
					id: '#filePicker',
					label: '点击选择图片',
					multiple : false
				},
				auto: true,
				swf: './resource/componets/webuploader/Uploader.swf',
				server: './index.php?i='+i+'&j='+j+'&c=utility&a=file&do=upload&type=image',
				chunked: false,
				compress: false,
				fileNumLimit: 1,
				fileSizeLimit: 4 * 1024 * 1024,
				fileSingleSizeLimit: 4 * 1024 * 1024
			};
			if (module.agent() == 'android') {
				defaultOptions.sendAsBinary = true;
			}
			options = $.extend({}, defaultOptions, options);
			
			var uploader = WebUploader.create(options);
			uploader.on( 'fileQueued', function( file ) {
				module.loading();
			});
			uploader.on('uploadSuccess', function(file, result) {
				if(result.error && result.error.message){
					require(['util'], function(u){
						module.loaded();
						u.message(result.error.message);
					});
				} else {
					if($.isFunction(callback)){
						callback(result);
					}
					uploader.reset();
					module.loaded();
					modalobj.modal('hide');
				}
			});
			uploader.onError = function( code ) {
				modalobj.modal('hide');
				uploader.reset();
				if(code == 'Q_EXCEED_SIZE_LIMIT'){
					alert('错误信息: 图片大于 4M 无法上传.');
					return
				}
				alert('错误信息: ' + code );
			};
			
			return modalobj;
		});
	}; // end of image
	
	module.loading = function() {
		var loadingid = 'modal-loading';
		var modalobj = $('#' + loadingid);
		if(modalobj.length == 0) {
			$(document.body).append('<div id="' + loadingid + '" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>');
			modalobj = $('#' + loadingid);
			html = 
				'<div class="modal-dialog">'+
				'	<div style="text-align:center; background-color: transparent;">'+
				'		<img style="width:48px; height:48px; margin-top:100px;" src="../attachment/images/global/loading.gif" title="正在努力加载...">'+
				'	</div>'+
				'</div>';
			modalobj.html(html);
		}
		modalobj.modal('show');
		modalobj.next().css('z-index', 999999);
		return modalobj;
	};
	
	module.loaded = function(){
		var loadingid = 'modal-loading';
		var modalobj = $('#' + loadingid);
		if(modalobj.length > 0){
			modalobj.modal('hide');
		}
	};
	
	module.cookie = {
		'prefix' : '',
		// 保存 Cookie
		'set' : function(name, value, seconds) {
			expires = new Date();
			expires.setTime(expires.getTime() + (1000 * seconds));
			document.cookie = this.name(name) + "=" + escape(value) + "; expires=" + expires.toGMTString() + "; path=/";
		},
		// 获取 Cookie
		'get' : function(name) {
			cookie_name = this.name(name) + "=";
			cookie_length = document.cookie.length;
			cookie_begin = 0;
			while (cookie_begin < cookie_length)
			{
				value_begin = cookie_begin + cookie_name.length;
				if (document.cookie.substring(cookie_begin, value_begin) == cookie_name)
				{
					var value_end = document.cookie.indexOf ( ";", value_begin);
					if (value_end == -1)
					{
						value_end = cookie_length;
					}
					return unescape(document.cookie.substring(value_begin, value_end));
				}
				cookie_begin = document.cookie.indexOf ( " ", cookie_begin) + 1;
				if (cookie_begin == 0)
				{
					break;
				}
			}
			return null;
		},
		// 清除 Cookie
		'del' : function(name) {
			var expireNow = new Date();
			document.cookie = this.name(name) + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" + "; path=/";
		},
		'name' : function(name) {
			return this.prefix + name;
		}
	};//end cookie

	module.agent = function() {
		var agent = navigator.userAgent;
		var isAndroid = agent.indexOf('Android') > -1 || agent.indexOf('Linux') > -1;
		var isIOS = !!agent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
		if (isAndroid) {
			return 'android';
		} else if (isIOS) {
			return 'ios';
		} else {
			return 'unknown'
		}
	};

	module.removeHTMLTag = function(str) {
		if(typeof str == 'string'){
			str = str.replace(/<script[^>]*?>[\s\S]*?<\/script>/g,'');
			str = str.replace(/<style[^>]*?>[\s\S]*?<\/style>/g,'');
			str = str.replace(/<\/?[^>]*>/g,'');
			str = str.replace(/\s+/g,'');
			str = str.replace(/&nbsp;/ig,'');
			return str;
		}
	};

	if (typeof define === "function" && define.amd) {
		define(['bootstrap', 'webuploader'], function($){
			return module;
		});
	} else {
		window.util = module;
	}
})(window);
