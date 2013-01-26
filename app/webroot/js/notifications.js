/*
 * Notifications JS
 * Copyright 2012 Christopher Natan
 */

var Notifications = {
    ready: function(){
       
    },
    FriendRequests: {
        ready: function(){
            App.init.popupBox();  
            this.onAddAndIgnoreFriend();
            this.onConfirmRequest();
        },
        onAddAndIgnoreFriend: function(){
            this.onPopupButtonYes();
        },
        onPopupButtonYes: function(){
            $('.ignorePopup').find(".buttonYes").click(function(){
                var typeId = $(".typeId").val();
                formClass = $(".cancelFriendForm");
                var action = $(formClass).attr("action");
                if (typeId == 1) {
                    $.fancybox.close();
                    action = action.replace("cancelFriend", "addFriend");
                    $(formClass).attr("action", action);
                    var paramId = $(formClass).find(".paramId").val();
                    $(".userId-" + paramId).find(".onAddFriend").hide();
                    $(".userId-" + paramId).find(".onCancelFriend").show();
                    $(".userId-" + paramId).find(".poshytip").poshytip("refresh");
                    $(".userId-" + paramId).find(".poshytip").poshytip('show');
                }
                if (typeId == 2) {
                    $.fancybox.close();
                    action = action.replace("addFriend", "cancelFriend");
                    $(formClass).attr("action", action);
                    var paramId = $(formClass).find(".paramId").val();
                    $(".userId-" + paramId).find(".onAddFriend").show();
                    $(".userId-" + paramId).find(".onCancelFriend").hide();
                    $(".userId-" + paramId).find(".onConfirmRequest").hide();
                    $(".userId-" + paramId).find(".poshytip").poshytip('hide');
                }
                App.Form.submit(formClass, Notifications.FriendRequests.showRequest, Notifications.FriendRequests.showResponse);
            });
        },
        onConfirmRequest: function(){
            $('.onConfirmRequest').live('click', function(){
                var paramId = $(this).attr("param_id");
                var self = this;
                $.post(basePath + 'UsersFriends/confirmedRequest', {
                    friendId: paramId
                }, function(data){
                    $(self).parent().html("<img src='" + basePath + "img/icons/yes.png'/> <a>Confirmed</a>");
                });
                return false;
            });
        },
        showsPoshytipRequestingAdd: function(){
            $(".requestingAdd").poshytip("show");
        },
        showRequest: function(formData, jqForm, options){
		
        },
        showResponse: function(responseText, statusText, xhr, $form){
            var paramId = $(".paramId").val();
            $("li." + paramId).fadeOut();
        }
    }
	
};
