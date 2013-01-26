/*
 * Pages JS
 * Copyright 2012 Christopher Natan
 */

var Pages = { 
    ready:function() {	
		
    },
    Activate:{
        ready:function() {	
            App.init.poshytip();
            var formClass = $('.activateForm');	
            this.popupBox();	
            this.onLoad();
            var formClassPersonal = $('.saveNewDetailsForm');
            var formClassDatings = $('.saveNewDatingsForm');	
            Pages.Details.ready(formClassPersonal);
            Pages.Details.ready(formClassDatings);
            App.Form.bind(formClass, Pages.Activate.request, Pages.Activate.response, false);
					
        },
        onLoad:function() {
            $("a.triggerShowPopup").trigger("click");
        },
        popupBox: function() {
            $(".showPopup").fancybox({
                'titlePosition': 'inside',
                'transitionIn': 'none',
                'transitionOut': 'none',
                'padding': 0,
                'hideOnOverlayClick': false,
                'width':500,
                'height':300
            });
        },
        request:function(formData, jqForm, options) {
            var error = false;
        },
        response:function(responseText, statusText, xhr, $form) {
            var parsedJson = jQuery.parseJSON(responseText);
			
            if(typeof(parsedJson.success)!="undefined") {
                App.Notification.showSuccess($form);
                $(".step-1").show().delay(1000).hide(1, function() {
                    $(".step-2").show();	
                });
            }	
            if(typeof(parsedJson.error)!="undefined") {
                App.Notification.showError($form);
            }	
        },	
    },
    Details:{
        ready:function(formClass) {	
            App.Form.bind(formClass, Pages.Details.request, Pages.Details.response, false);		
        },
        request:function(formData, jqForm, options) {
            var error = false;
        },
        response:function(responseText, statusText, xhr, $form) {
            var parsedJson = jQuery.parseJSON(responseText);
			
            if(typeof(parsedJson.success)!="undefined") {
                App.Notification.showSuccess($form);
                $(".step-2").show().delay(1000).hide(1, function() {
                    $(".step-3").show();	
                });
            }	
            if(typeof(parsedJson.error)!="undefined") {
                App.Notification.showError($form);
            }	
        },	
    },
    Register:{
        ready:function() {	
            this.onRegister();	
            App.init.poshytip();	
        },
        onRegister:function() {
            var formClass = $('.registerForm');	
            App.Form.bind(formClass, Pages.Register.request, Pages.Register.response, false);	
        },
        request:function(formData, jqForm, options) {
            var error = false;
        },
        response:function(responseText, statusText, xhr, $form) {
            var parsedJson = jQuery.parseJSON(responseText);
            var error = 0;
            var errorPassword = "";
            var errorEmail = "";
            $($form).find(".notification").empty().hide();
            $.each(parsedJson, function(i, item){
                if(typeof(item.password)!="undefined"){
                    errorPassword = "<span class='block'>" + item.password + "</span>";
                    error = 1;
                }
                if(typeof(item.email)!="undefined"){
                    errorEmail = "<span class='block'>" + item.email + "</span>";
                    error = 1;
                }
            });	
            if (error) {
                var errors = errorEmail + errorPassword;
                $($form).find(".notification").append("" + errors + "");
                App.Notification.showError($form);
            } else {
                window.location.href = basePath + "users/activate";
            }				
        },
    },
    Login:{
        ready: function(){
            var form = $(".loginForm");
            this.bindForm(form);    
        },
        bindForm: function(form){
            var options = {
                target: $(form).find('.ajaxTarget'),
                beforeSubmit: showRequest,
                success: showResponse
            };
            $(form).ajaxForm(options);
            function showRequest(formData, jqForm, options){
                $(jqForm).find(".notification.mistake").hide();	
            }
            function showResponse(responseText, statusText, xhr, $form){
                var parsedJson = jQuery.parseJSON(responseText);
                if (typeof(parsedJson.error)!="undefined") {
                    $($form).find(".notification.mistake").text(parsedJson.error);
                    $($form).find(".notification.mistake").show();
                }
                if (typeof(parsedJson.success) != "undefined") {
                    $($form).find(".notification.mistake").hide();
                    window.location.href = parsedJson.redirect;
                }		 
            }
        },	
    }
	
	
			
};			