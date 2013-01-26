/*
 * Ajax JS
 * Copyright 2012 Christopher Natan
 */
var Ajax = {
    ready:function() {
        this.setUp.init();
        this.onClick();
    },
    /* Set means write to documents */
    setUp: {
        init: function(){
            this.error();	
        },
        error: function(){
            $.ajaxSetup({
                error:function(x,e){
                    if(x.status==0){
                        alert('You are offline!!\n Please Check Your Network.');
                    }else if(x.status==404){
                        alert('Requested URL not found.');
                    }else if(x.status==500){
                        alert('Internel Server Error.');
                    }else if(e=='parsererror'){
                        alert('Error.\nParsing JSON Request failed.');
                    }else if(e=='timeout'){
                        alert('Request Time out.');
                    }else {
                        alert('Unknow Error.\n'+x.responseText);
                    }
                },
                timeout: (2 * 1000000)
            });
        }    
    },
    currentElement:null,
    onClick: function() {
        $(".setLoader").live('click', function() {
            Ajax.currentElement = $(this).next("img.loader");
            Ajax.currentElement.show();
            Ajax.onStart();
        });
    },
    loaderHide:function(form) {
        $(form).find(".loader").hide();
    },
    onStart:function() {	
        jQuery(document).ajaxStart(function() {
					
            }).ajaxStop(function() {
            hide();
        }).ajaxComplete(function(event,request, settings){
            hide();
        }).ajaxError(function(a, b, e) {
            hide();
            throw e;
        /*@TODO: check the error if request timed out returns a message to user*/
        });	
        function hide() {
            if (Ajax.currentElement) {
                Ajax.currentElement.hide();
                Ajax.currentElement = null;
            }
        }
    }
		
				
};