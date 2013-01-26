/*
 * App JS
 * Copyright 2012 Christopher Natan
 */

var basePath = "/";
var App = {
    youtubeKeys:["cakephp","symfony","agile","mysql","css xhtml", 
    "codeigniter","php development","web design","html 5","css 3", "netbeans", "dreamweaver",
    "linux", "jquery", "javascript", "object oriented"],
    ready:function() {
        this.Set.init();
        App.Navigation.init();
    },
    /* Set means write to documents */
    Set: {
        init: function(){
            this.loader();	
        },
        imgLoader: jQuery(' <img src="/img/icons/loader.gif" alt="loading..." />'),
        loader:function() {
            var loader = this.imgLoader;
            loader.addClass("loader");
            $(".setLoader").after(loader);
        }
    },
    Navigation:{
        init: function(){
            this.back();	
        },
        back:function() {
            $('.back').live('click', function(){
                window.history.go(-1);
            });
        }
    },
    Image:{
        resizeImageToAspectRatio:function(image, maxWidth, maxHeight) {
				    
            var maxWidth = maxWidth;	  // Max width for the image
            var maxHeight = maxHeight;    // Max height for the image
            var ratio = 0;  			  // Used for aspect ratio
            var width = $(image).width();    // Current image width
            var height = $(image).height();  // Current image height
			
            // Check if the current width is larger than the max
            if(width > maxWidth){
                ratio = maxWidth / width;   // get ratio for scaling image
                $(image).css("width", maxWidth); // Set new width
                $(image).css("height", height * ratio);  // Scale height based on ratio
                height = height * ratio;    // Reset height to match scaled image
                width = width * ratio;    // Reset width to match scaled image
            }
			
            // Check if current height is larger than max
            if(height > maxHeight){
                ratio = maxHeight / height; // get ratio for scaling image
                $(image).css("height", maxHeight);   // Set new height
                $(image).css("width", width * ratio);    // Scale width based on ratio
                width = width * ratio;    // Reset width to match scaled image
            }
        }
    },
    Form:{
        submit: function(form, showRequest, showResponse, reset){	
            if(typeof(reset) == "undefined") {
                reset = true;
            }
            var options = {
                target: $(form).find('.ajaxTarget'),
                beforeSubmit: showRequest,
                success: showResponse,
                resetForm: reset
            };
            $(form).ajaxSubmit(options);
        },
        bind: function(form, showRequest, showResponse, reset){	
            if(typeof(reset) == "undefined") {
                reset = true;
            }
            var options = {
                target: $(form).find('.ajaxTarget'),
                beforeSubmit: showRequest,
                success: showResponse,
                resetForm: reset
            };
            $(form).ajaxForm(options);
        }
		 
    },
    Notification: {
        note:function(form, type) {
            var getText = "";
            if(typeof(type) == "undefined") {
                type = "success";
            }
            var defaulText = $.trim($(form).find(".notification").text());
            App.Notification.checkSubmits(form);
            if(defaulText.length <=0) {
                getText = $(form).find(".notification").attr(type);
                $(form).find(".notification").addClass(type);
                $(form).find(".notification").text(getText);
                $(form).find(".notification").show().delay(1000).fadeOut(function(){
                    App.Notification.reset(form);
                });
            }else{
                if (typeof($(form).find(".notification").attr("nohide")) !="undefined") {
                    $(form).find(".notification").addClass(type).show();
                }
                else {
                    $(form).find(".notification").addClass(type).show().delay(1000).fadeOut(function(){
                        App.Notification.reset(form);
                    });
                }	
            }
            $(form).find(".loader").hide();
        },
        checkSubmits:function(form) {
            var isSubmits = $(form).find(".notification").prev(".submits").length;
            if(isSubmits) {
                $(form).find(".notification").prev(".submits").hide();
            }
        },
        reset:function(form) {
            $(form).find(".notification").text("").removeClass("mistake").removeClass("success");
            $(form).find(".notification").prev(".submits").show();
        },
        showSuccess:function(form, type) {
            App.Notification.note(form, "success");
        },
        showError:function(form) {
            App.Notification.note(form, "mistake");
        }
    },
    init:{
        popupBox: function() {
            $(".showPopup").fancybox({
                'titlePosition': 'inside',
                'transitionIn': 'none',
                'transitionOut': 'none',
                'padding': 0,
                'hideOnOverlayClick': false,
                'speedOut': 0,
                'onClosed'	: function(url, load, obj,cl) {	
                    App.init.onPopupReset();
                }
            });
            this.onPopupClick();
        },
        popupDefaults:{
            head:null,
            content:null,
            type:null,
            form:null
        },
        popupElemId:null,
        onPopupClick:function() {
            var self = this;
            $('.showPopup').click(function(){
                var head = $(this).attr("head");
                var content =$(this).attr("content");
                var type = $(this).attr("type");
                var elemId = $(this).attr("href");
                var forms = $(this).attr("form");
					
                if(typeof(head)!="undefined") {
                    $(elemId).find(".head").text(head);
                }
                if(typeof(content)!="undefined"){
                    $(elemId).find(".content").text(content);
                }
                if(typeof(type)!="undefined") {
                    $(elemId).find("input.typeId").val(type);
                }
                if(typeof(forms)!="undefined") {
                    $(elemId).find("form").attr("action",  basePath + forms);
                }
					
                var val = $(this).attr("param_id");
                $(elemId).find(".paramId").val(val);
					
                if (self.popupElemId == null) {
                    self.onPopupSetDefault(elemId);
                    self.popupElemId = elemId;
                }	
            });
            $(".popupBox").find(".onCancel").click(function(){
                $.fancybox.close();
                $(".loader").hide();
            });
        },
        onPopupSetDefault:function(elemId) {
            this.popupDefaults.head = $(elemId).find(".head").text();
            this.popupDefaults.content = $(elemId).find(".content").text();
            this.popupDefaults.type = $(elemId).find("input.typeId").val();
            this.popupDefaults.form = $(elemId).find("form").attr("action");
        },
        onPopupReset:function() {
            var defaults = App.init.popupDefaults;
            var popup = App.init.popupElemId;
            if ($(popup).find(".head").text() != defaults.head) {
                $(popup).find(".head").text(defaults.head);
                $(popup).find(".content").text(defaults.content);
                $(popup).find("input.typeId").val(defaults.type);
                $(popup).find("form").attr("action", defaults.form);
            }					
        },
        lookupBox: function(){
            $(".showLookup").fancybox({
                'titlePosition': 'inside',
                'transitionIn': 'none',
                'transitionOut': 'none',
                'type' : 'iframe',
                'padding': 10,
                'width' : 580,
                'hideOnOverlayClick': false,
            });
        },
        galleryBox:function(onComplete) {
            $("a[rel=showGallery]").fancybox({
                'transitionIn'		: 'none',
                'transitionOut'		: 'none',
                'titlePosition' 	: 'over',
                'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
                    return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
                },
                "autoDimensions" : false,
                "autoScale" : false,
                "width" : "450px",
                "onComplete" : onComplete
            });
        },
        poshytip: function(){
            $('.poshytip').poshytip({
                className: 'tip-twitter',
                allowTipHover: false,
                alignTo: 'target',
                alignX: 'right',
                alignY: 'center',
                offsetX: 18,
                showTimeout: 1
            });  
        }		
    },
    checkSessions:function(parsedJson) {
        if(typeof(parsedJson.error) != "undefined") {
            window.location.href= basePath + "session-expired";
            return false;
        }
        return true;
    }
				
};