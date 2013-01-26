/*
 * Messages JS
 * Copyright 2012 Christopher Natan
 */
var Messages = {
    ready: function(){
        this.init.autoGrow();
    },
    init: {
        autoGrow:function() {
            $(".autoGrow").autogrow({
                height:"16px"
            });	
        }
    },
    Compose:{
        ready: function(){
            Messages.AttachPhoto.ready();
            this.textAreatext = "Search your friend name here...";
            this.formClass = $(".messageForm");
            this.init.autoGrow();
            this.onSearching()
            this.onCheckboxSendTo();
            this.onClickSearching();
            this.init.autoGrow();
            App.Form.bind(this.formClass, Messages.Compose.request, Messages.Compose.response);
        },
        request:function(formData, jqForm, options) {
            var valid = true;
            var listFriends = $(".listFriends").text();
            if($.trim($(".input-message").val()) == "") {
                valid = false;	
            }
			
            if($.trim(listFriends)=="" ||  valid==false) {
                valid = false;
                var msg = $(".notifyMessage").find(".notification").attr("mistake");
                $(".notifyMessage").find(".notification").removeClass("success").addClass("mistake").text(msg)
                $(".notifyMessage").find(".notification").show().delay(3000).fadeOut();
            }
            if (!valid) {
                $(".loader").hide();
                return false;
            }
			
        },
        response: function(responseText, statusText, xhr, $form){
            var msg = $(".notifyMessage").find(".notification").attr("success");
            $(".notifyMessage").find(".notification").removeClass("mistake").addClass("success").text(msg)
            $(".notifyMessage").find(".notification").show().delay(3000).fadeOut();
            $(".listFriends").text("");
            Messages.AttachPhoto.resetUploadedPhoto();
        },
        init: {
            autoGrow:function() {
                $(".alreadyGrow").autogrow({
                    height:"150px"
                });	
            }
        },
        formClass:null,
        textAreatext:null,
        onSearching:function() {
            $('.inputSearch').keyup(function() {
                var keywords = $(this).val();
                if(keywords.length) {
                    Messages.Compose.startSearching(keywords);
                } else {
                    $(".friends").show();
                }	 
            });
        },
        onClickSearching: function(){
            var form = this.formClass;
            $(form).find('.inputSearch').click(function(){
                if (Messages.Compose.textAreatext == $(this).val()) {
                    $(this).val("").removeClass("gray");
                }
                $(form).find('.showHide').slideDown("fast");
            }).blur(function(){
                if ($(this).val().length <= 0) {
                    $(this).val(Messages.Compose.textAreatext).addClass("gray");
                }
            });
        },
        startSearching:function(keywords) {
            var keyword = keywords.toUpperCase();
            $(".friends").each(function(key) { 
                var name = $(this).find("a").text().toUpperCase();
                var isIndex = name.indexOf(keyword) >= 0;
                if(isIndex) {
                    $(this).show();	
                } else {
                    $(this).hide();		
                }		
            });
        },
        onCheckboxSendTo:function() {
            $('.sendTo').click(function() {
                var checked = $("input:checked");	
                var names = [];
                $(checked).each(function(key, item) {
                    names[key]= $(item).attr("params");
                });
                var joins = names.join(", ");	
                $(".listFriends").text("To : " + joins);
                if(checked.length <=0 ) {
                    $(".listFriends").text("");
                }  
            });	
        }
    },	
    AttachPhoto:{
        ready:function() {
            this.onAddPhoto();
            this.onUpload();
            this.onCancelPhoto();	
        },
        onAddPhoto: function(){
            $('.onAddPhoto').live('click', function(){
                $(".boxOnPost").find(".addPhoto").show();
                $(".boxOnPost").find(".showHide").hide();
                $(".fileUploadContainer").show();
                $(".onCancelPhoto").show();
            });
        },
        onUpload: function(){
            var formClass = $(".uploadForm");
            $('.fileUpload').live('change', function(){
                $(".uploadForm").find(".loader").show().find(".loader").show();
                $(".actionType").val("upload");
                $(".fileUploadContainer").hide();
                $(".onAddPhoto").text("Replace Photo");
                App.Form.submit(formClass, Messages.AttachPhoto.request, Messages.AttachPhoto.response);
            });
        },
        request:function(formData, jqForm, options) {},
        response: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            if (parsedJson.imagefile) {
                var photoPath = basePath + "img/photos/messages/" + parsedJson.imagefile;
                $("img.targetPhoto").attr("src", photoPath).hide();
                $("input.sourceImage").val(parsedJson.imagefile);
                $('img.targetPhoto').load(function(){
                    $("img.targetPhoto").show();
                    $(".boxOnPost").find(".inputPost").trigger("click").focus();
                });
            }
            else {
                $(".uploadForm").find(".notification").show();
            }
            $($form).find(".loader").hide();
        },
        onCancelPhoto: function(){
            var self = this;
            $('.onCancelPhoto').live('click', function(){
                var sourceImage = $("input.sourceImage").val();
                if (sourceImage != "") {
                    $.post(basePath + 'UsersMessages/removePhoto', {
                        sourceImage: sourceImage
                    }, function(data){});
                }
                self.resetUploadedPhoto();
                $(".boxOnPost").find(".inputPost").focus();
            });
        },
        resetUploadedPhoto: function(){
            var sourceImage = $("input.sourceImage").val();
            if (sourceImage != "") {
                $(".targetPhoto").attr("src", "").hide();
                $(".sourceImage").val("");
                $(".onAddPhoto").text("Add Photo");
            }
            $(".boxOnPost").find(".inputPost").trigger("click");
            $(".onCancelPhoto").hide();
        }	
    },
	
    Delete:{
        ready:function() {
            this.onDeleteMessage();
            this.onPopupButtonYes();		
        },
        deleteParentObject:null,
        onDeleteMessage: function(){
            var self = this;
            $('.postedBox').find(".onDeletePost").live('click', function(){
                $('.popupBox').find(".messageSubject").text("MESSAGE");
                self.deleteParentObject = $(this).parent().parent().parent().parent().parent("div");
                var paramId = $(this).attr("param_id");
                $(".popupBox").find("input.paramId").val(paramId);
				
                var action = $(".popupBox").find("form.deleteForm").attr("action");
                var path = action.charAt(0);
                var actionURL = path + "usersMessages" + "/deleteMessage";
                $(".popupBox").find("form.deleteForm").attr("action", actionURL);
            });
        },
        onPopupButtonYes: function(){
            var formClass;
            $('.popupBox').find(".buttonYes").click(function(){
                formClass = $(this).parent("form");
                App.Form.submit(formClass, Messages.Delete.request, Messages.Delete.response);
            });
        },
        request:function(formData, jqForm, options) {},
        response: function(responseText, statusText, xhr, $form){
            $.fancybox.close();
            Messages.Delete.deleteParentObject.delay(500).fadeOut(500);
        }	
    },
	
    Read: {
        formClass:null,
        clonedElement: null,
        textAreatext:"Reply...",
        ready: function(){
            App.init.popupBox();
            Messages.AttachPhoto.ready();
            Messages.Delete.ready();
            Messages.init.autoGrow();
			
            this.clonedElement = $(".clonedPostedElement").clone();
            this.formClass = $(".messageForm");
            this.bindForm();
            this.onReplying();
        },
        onReplying: function(){
            var form = this.formClass;
            $(form).find('.inputPost').click(function(){
                if (Messages.Read.textAreatext == $(this).val()) {
                    $(this).val("").removeClass("gray");
                }
                $(form).find('.showHide').slideDown("fast");
				
                if ($("input.sourceImage").val() == "") {
                    $(".addPhoto").hide();
                }
				
            }).blur(function(){
				
                });
        },
        bindForm:function() {
            App.Form.bind(this.formClass, Messages.Read.postRequest, Messages.Read.postResponse);	
        },
        postRequest:function(formData, jqForm, options) {
            var valid = true;
            $.each(formData, function(i, item){
                var val = $.trim(item.value);
                if (item.name == "data[message]") {
                    if (val.length <= 0 || val == Messages.Read.textAreatext) {
                        valid = false;
                    }
                }
            });
            if (!valid) {
                $(".loader").hide();
                return false;
            }
			
        },
        postResponse: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            var clonedElement = Messages.Read.clonedElement;
            if (App.checkSessions(parsedJson)) {
                var shown = $(clonedElement).removeClass("hide").removeClass("clonedPostedElement");
                var postId = 0;
                $.each(parsedJson, function(i, item){
                    var photo = item.photo;
                    if (photo != "") {
                        var path = basePath + "img/?w=90&r=4:3&s=photos/messages/" + photo;
                        var activityPhoto = "<img src='" + path + "' class='activity-photo'>";
                        $(shown).find("span.postedMessage").after(activityPhoto);
                    }
                    postId = item.id;
                    $(shown).find("span.postedMessage").text(item.message);
                    $(shown).find("li.dateCreated").text(item.created);
                    $(shown).find("a.onDeletePost").attr("param_id", postId);

                });
				
                var rands = Math.random(123456789);
                $(shown).find("form.commentForm").attr("id", rands);
                $(shown).find("form.commentForm").find("input.activityId").val(postId);
				
                var wrap = "<div class='posted postedBox'>" + shown.html() + "</div>";
                $(".readMessages").append(wrap);
            }
            Messages.AttachPhoto.resetUploadedPhoto();
            App.init.popupBox();
        },

		
    } 			
};
