/*
 * Galleries JS
 * Copyright 2012 Christopher Natan
 */

var Galleries = { 
    ready:function() {
        this.placeScrollCover();
        this.onClickThumb();	
        this.onScrollTop();	
        this.onScrollBottom();
        this.setDefaultImg();
        this.onNavigations();
		
    },
    setDefaultImg:function() {
        var first =  $(".photoGallery").find("li").first().find("a").trigger("click");
    },
    showNav:function() {
        $(".photoPreview").find("img.preview").live("mouseover",function(){
            $(".navCover").show();	
        }).mouseleave(function(){
            $(".navCover").hide();	
        });	
    },
    onNavigations:function() {
        var countThumb = $(".photoGallery").find("li").length;
        var index = 0;
		
        if(countThumb<=8) {
            $(".scrollTop").hide();
            $(".scrollBottom").hide();
				
        }
		
        $(".nav").find("a").live('click', function(){
            var type = $(this).attr("class");
            var selected = $(".photoGallery").find("img.selected");
            index = $(".photoGallery").find("li").find("img").index(selected);
			
            if(type == "next"){
                $(".selected").parent().parent().next().find("a").trigger("click");
                index = index + 1;
            } else {
                $(".selected").parent().parent().prev().find("a").trigger("click");
            }
            if(index == 8 || index == 8*2 || index == 8*3 || index == 8*4 || index == 8*5 || index == 8*6 ){
                if (type == "next") {
                    $("a.scrollBottom").trigger("click");
                } else {
                    $("a.scrollTop").trigger("click");
                }	
            }
            if(index>=countThumb) {
                $(".photoGallery").find("li").first().find("a").trigger("click");
                $('.photoGallery').scrollTop(0);
            }
            if(index<=0) {
                $(".photoGallery").find("li").last().find("a").trigger("click");
                $('.photoGallery').scrollTop(10000);
            }
			
        });	
    },
    scrollBottomHeight:0,
    onClickThumb:function() {
        $(".photoGallery").find("a").live('click', function(){
            var self = this;
            var url = $(this).attr("href");
            $(".photoGallery").find("img.selected").removeClass("selected");
			
            $(".photoPreview").find("img.preview").fadeOut(function(){
                $(this).attr("src", url).fadeIn();
                var title = $(self).next().text();
                var caption = $(self).next().next().text();
                $(".contents").find(".title").text(title);
                $(".contents").find(".caption").text(caption);
            });
            $(this).find("img").addClass("selected");
            Galleries.positionNav();
            return false;
        });	
    },
    positionNav:function() {
        $(".photoPreview").find("img.preview").load(function() {
            var w = $(this).width();
            var h = $(this).height();
            var t = $(this).offset().top;
            var tops = (h/2) - 20;
            $(".navCover").css("width", w-20+"px").css("top", t+tops+"px");	
        });
		
    },
    onScrollTop:function() {
        var h = $(".photoGallery").height();
        $(".scrollTop").live('click', function(){
            var self = this;
            var current = $('.photoGallery').scrollTop();
            var currentAttr = $(self).find("img").attr("src");
				
            $('.photoGallery').animate({
                scrollTop: current - h,
            }, 500, function() {
			   
                if(Galleries.scrollBottomHeight <= h){
                    var scrollBottomAttr = $(".scrollBottom").find("img").attr("src");
                    var replaces = scrollBottomAttr.replace("_dis.png", ".png");
                    $(".scrollBottom").find("img").attr("src", replaces);
                }
				
                if(current <= h && current!=0){
                    var repl = currentAttr.replace(".png", "_dis.png");
                } else if(current > h && current!=0){
                    var repl = currentAttr.replace("_dis.png", ".png");
                }
                $(self).find("img").attr("src", repl);
            });		
        });
    },
    onScrollBottom:function() {
        var h = $(".photoGallery").height();
        $(".scrollBottom").live('click', function(){
            var current = $('.photoGallery').scrollTop();
            var currentAttr = $(this).find("img").attr("src");
            var self = this;
            $('.photoGallery').animate({
                scrollTop: h + current,
            }, 500, function() {
			
                if(current == 0){
                    var scrollTopAttr = $(".scrollTop").find("img").attr("src");
                    var repl = scrollTopAttr.replace("_dis.png", ".png");
                    $(".scrollTop").find("img").attr("src", repl);
                }
                var elem = $('.photoGallery');
                var scrollBottomHeight = elem[0].scrollHeight;
                var addUp = current + h;
                var isBottom = scrollBottomHeight  - addUp;
                Galleries.scrollBottomHeight = isBottom;
                if(isBottom <= h) {
                    var repl = currentAttr.replace(".png", "_dis.png");
                } else {
                    var repl = currentAttr.replace("_dis.png", ".png");	
                }
                $(self).find("img").attr("src", repl);
            });		
        });
    },
    placeScrollCover:function() {
        var pos = $(".scrollCover").offset();
        var l = pos.left;
        var t = pos.top;
        $(".scrollCover").css("left",l + 65 + "px");
    }
			
}			