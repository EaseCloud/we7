require.config({
	baseUrl: 'resource/js/app',
	urlArgs: "v=" +  (new Date()).getHours(),
	paths: {
		'map': 'http://api.map.baidu.com/getscript?v=2.0&ak=F51571495f717ff1194de02366bb8da9&services=&t=20140530104353',
		'css': '../../../../web/resource/js/lib/css.min',
		'jquery': '../../../../web/resource/js/lib/jquery-1.11.1.min',
		'angular': '../../../../web/resource/js/lib/angular.min',
		'bootstrap': '../../../../web/resource/js/lib/bootstrap.min',
		'underscore': '../../../../web/resource/js/lib/underscore-min',
		'moment': '../../../../web/resource/js/lib/moment',
		'filestyle': '../../../../web/resource/js/lib/bootstrap-filestyle.min',
		'daterangepicker': '../../components/daterangepicker/daterangepicker',
		'datetimepicker': '../../components/datetimepicker/bootstrap-datetimepicker.min',
		'webuploader' : '../../../../web/resource/components/webuploader/webuploader.min',
		'jquery.jplayer': '../../../../web/resource/components/jplayer/jquery.jplayer.min',
		'hammer': '../lib/hammer.min',
		'iscroll': '../lib/iscroll-lite',
		'swiper': '../../components/swiper/swiper.min',
		'calendar': '../lib/calendar',
		'jquery.qrcode': '../../../../web/resource/js/lib/jquery.qrcode.min'
	},
	shim:{
		'angular': {
			exports: 'angular',
			deps: ['jquery']
		},
		'bootstrap': {
			exports: "$",
			deps: ['jquery']
		},
		'iscroll': {
			exports: "IScroll"
		},
		'filestyle': {
			exports: '$',
			deps: ['bootstrap']
		},
		'daterangepicker': {
			exports: '$',
			deps: ['bootstrap', 'moment', 'css!../../components/daterangepicker/daterangepicker.css']
		},
		'datetimepicker': {
			exports: '$',
			deps: ['bootstrap', 'css!../../components/datetimepicker/bootstrap-datetimepicker.min.css']
		},
		'map': {
			exports: 'BMap'
		},
		'webuploader': {
			deps: ['jquery', 'css!../../../../web/resource/components/webuploader/webuploader.css', 'css!../../../../web/resource/components/webuploader/style.css']
		},
		'jquery.jplayer': {
			exports: "$",
			deps: ['jquery']
		},
		'calendar': {
			exports: "$",
			deps: ['jquery']
		},
		'jquery.qrcode': {
			exports: "$",
			deps: ['jquery']
		}
	}
});