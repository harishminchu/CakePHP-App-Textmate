/*
 * AlbumsPhotos JS
 * Copyright 2012 Toper
 */

var AlbumsPhotos = {
    ready: function(){
        this.ManagePhotos.ready(); 
        App.init.popupBox();
    },
    Gallery:{
        show:function() {
            Users.PublicProfile.AlbumsAndPhotos.ready();	
        }	
    },
    ManagePhotos:{
        ready: function(){
            this.onDelete();
            this.onAlbumCover();
            this.onSaveChanges();
            this.onPopupButtonYes();
        },
        onDelete:function() {
            $('.deletePhoto').click(function(){
                if($(this).is(":checked")){
                    $(this).next("input").attr("disabled", "disabled");
                } else {
                    $(this).next("input").removeAttr("disabled");
                }	 
            });		
        },
        onAlbumCover:function() {
            $('.albumCover').click(function(){
                $(".deletePhoto").removeAttr("disabled");
                if($(this).is(":checked")){
                    $(this).prev("input").attr("disabled", "disabled");
                } 		
            });
        },
        onSaveChanges:function() {
            var formClass = $(".saveDetailsForm");
            App.Form.bind(formClass, AlbumsPhotos.ManagePhotos.showRequest, AlbumsPhotos.ManagePhotos.showResponse, false);		
        },
        showRequest:function(formData, jqForm, options) {
            var count = $(".deletePhoto:checked").length;
            if (count && $(".deleteForm").find(".paramId").val() == 0) {
                $(".triggerDelete").trigger("click");
                return false;
            }	
        },
        showResponse:function(responseText, statusText, xhr, $form) {
            AlbumsPhotos.ManagePhotos.removeElementOnDelete();
            App.Notification.showSuccess($form);
            $(".deleteForm").find(".paramId").val(0); 
            $.fancybox.close();	
            $(".loader").hide();
        },
        removeElementOnDelete:function() {
            $(".photoContainer").each(function(i, item){
                var isCheckedDelete = $(this).find(".deletePhoto").is(":checked");
                var albumId = $(this).find(".albumIdHolder").val();
                if(albumId != $(this).find(".defaultAlbumId").val()) {
                    $(this).fadeOut();
                }
                if(isCheckedDelete) {
                    $(this).fadeOut();
                }
            });	
        },
        onPopupButtonYes: function(){
            var formClass;
            $('.popupBox').find(".buttonYes").click(function(){
                $(".deleteForm").find(".paramId").val("1"); 
                $(".saveDetailsForm").submit();
            });
        }
    },
    PhotosComment: {
        ready:function() {
            this.onShowPhotosComment();
            Comments.deletePopupBoxClass = ".deletePhotoComment";
            Comments.commentBoxClass     = ".commentPhotoBox";
            Comments.commentForm         = ".photoCommentForm";
            Comments.clonedElement       = ".clonedPhotoCommentElement";
            Comments.appendTarget        = ".loadPhotoComments";
            Comments.ready();
        },
        onShowPhotosComment: function(){		
            var self = this;
            $(".photoGallery").find("a.thumb").live('click', function(){
                self.showPhotosComment(0, this);	
            });
        },
        showPhotosComment:function(paramId, self) {
            if (paramId == 0) {
                var paramId = $(self).find("img").attr("param_id");
            }
            
            $(".photoCommentForm").find(".photoId").val(paramId);
            $.post(basePath + 'UsersPublics/photoComments/' + paramId, function(data){
                $(".loadPhotoComments").html(data);
                $(".deletePhotoComment").find('.popupBox').find(".paramId").val(paramId);
                App.init.popupBox();
            });
        }
			
    }	
	
};
