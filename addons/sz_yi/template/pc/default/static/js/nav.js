$(function(){
	var slideMenu=function(o){
		var f=$("."+o.f),s=f.children("."+o.s),h=s.outerHeight();
		f.css({position:"relative"});
		s.css({height:0,opacity:0});
		f.hover(function(){
		s.show().stop(true,false).animate({height:h,opacity:1},350,function(){
			s.css({overflow:"visible"});
		});
		},function(){
			s.stop(true,false).animate({height:0,opacity:0},350,function(){
				s.hide();
			});
		});
		
	}
	//缃戠珯瀵艰埅涓嬫媺妗�
	slideMenu({
		f:"site_map",
		s:"j-categorys"
	});
	// 瀵艰埅鍙充晶鐨勮喘鐗╄溅寮瑰嚭妗�
	slideMenu({
		f:"cart",
		s:"j-car"

	});
	//鐧诲綍杩囧悗鐨勫脊鍑烘
	slideMenu({
		f:"j-user-img",
		s:"j-logined"
	});
	//涓诲鑸笅鎷夎彍鍗�
	var navMenu = function(){
		var h=$(".sublistbox").outerHeight();
		$(".nav_head li").hover(function(){
			$(this).toggleClass('active');
//			$(this).children().children("i").removeClass().addClass("fontello-icon-angle-up");
			$(this).children(".sublistbox").show().stop(true,false).animate({height:h,opacity:1},350,function(){
			$(this).children(".sublistbox").css({overflow:"visible"});
			
			
		});
		},function(){
			$(this).toggleClass('active');
//			$(this).children().children("i").removeClass().addClass("fontello-icon-angle-down");
			$(this).children(".sublistbox").stop(true,false).animate({height:0,opacity:0},350,function(){
				$(this).children(".sublistbox").hide();
			});
		});
	}
	navMenu();
	
	/*$(".nav_head li").hover(function(){
		$(this).children(".sublistbox").show();
    },function(){
    	$(this).children(".sublistbox").hide();
    });*/
//鏁欑▼js	
	$(".j-user-teach").hover(function(){
		$(".j-teach div").height(90);
		$(".j-teach div").stop();
        $(".j-teach div").animate({
           height:"toggle",
           opacity: "show",
       },"fast");
    },function(){
        $(".j-teach div").animate({
           height:"toggle",
           opacity: "hide",
       });
    },"fast");
	
	//head-fix
	$(window).scroll(function(){
		if($(window).scrollTop()>40){
			if($("#head-fix").hasClass("nav-wrap")){
				$("#head-fix").removeClass("nav-wrap");
				$("#head-fix").addClass("nav-wrap-fix");
				$(".nav-wrap-fix").css({height:"80px"});
//				$(".nav-wrap-fix").animate({height:80},350);
//				$(".nav-wrap-fix").css({overflow:"inherit"});
			}
		}else{
			$("#head-fix").addClass("nav-wrap");
			$("#head-fix").removeClass("nav-wrap-fix");
			$(".nav-wrap").css("height",'108px');
		}
	});
	if($(window).scrollTop()>40){
		if($("#head-fix").hasClass("nav-wrap")){
			$("#head-fix").removeClass("nav-wrap");
			$("#head-fix").addClass("nav-wrap-fix");
			$(".nav-wrap-fix").css({height:"80px"});
//			$(".nav-wrap-fix").animate({height:80},350);
//			$(".nav-wrap-fix").css({overflow:"inherit"});
		}
	}else{
		$("#head-fix").addClass("nav-wrap");
		$("#head-fix").removeClass("nav-wrap-fix");
		$(".nav-wrap").css("height",'108px');
	}
});


$(function(){	
		// 瀵艰埅宸︿晶鏍廽s鏁堟灉 start
		$(".pullDownList li").hover(function(){
			//$(this).stop(true,false);
			/*$(".yMenuListCon").stop(true);*/
			$(".yMenuListConin").stop(true);
			$(".yMenuListCon").show();
			var index=$(this).index(".pullDownList li");
			
			$(this).css("background","rgba(255,255,255,0.3)").siblings().css("background","");
			$(this).addClass("menulihover").siblings().removeClass("menulihover");
			$(this).addClass("menuliselected").siblings().removeClass("menuliselected");
			$($(".yMenuListConin")[index]).show().siblings().hide();
		},function(){
			
		})
		$(".nav_banner").mouseleave(function(){
			$(".pullDownList li").css("background","");
			$(".yMenuListCon").hide();
			$(".yMenuListConin").hide();
			$(".pullDownList li").removeClass("menulihover");

		})
		// 瀵艰埅宸︿晶鏍廽s鏁堟灉  end
		
	})
	