/*
 * Albums JS
 * Copyright 2012 Christopher Natan
 */

var Albums = {
    ready:function() {
        App.init.popupBox();
        this.AddNewPhotos.ready();
    },
    AddNewPhotos:{
        uploadFormClass:null,
        ready:function() {
            this.uploadFormClass = $(".uploadForm");
            this.onUpload();
            this.onSavingDetail();
            this.onDeletingDetail();
            this.onAddAlbum();
        },
        triggerAdd:function() {
            $(".onAddAlbum").trigger("click");
        },
        onAddAlbum:function() {
            var formClass = $('.addNewAlbumForm');	
            App.Form.bind(formClass, Albums.AddNewPhotos.addAlbumRequest, Albums.AddNewPhotos.addAlbumResponse);	
        },
        addAlbumRequest:function(formData, jqForm, options) {
            var error = false;
            $.each(formData, function(i,item ){
                if(item.name == "data[album_name]")	 {
                    if($.trim(item.value)== "") {
                        error = true;
                        return false;
                    }
                }
            });	
            if(error) {
                App.Notification.showError(jqForm);
                return false;
            }
		
        },
        addAlbumResponse:function(responseText, statusText, xhr, $form) {
            var parsedJson = jQuery.parseJSON(responseText);
            var albumId = parsedJson.id
            var albumName= parsedJson.album_name
            $(".uploadForm").find("select.albumId").append($("<option>", {
                value:albumId
            }).text(albumName).attr("selected","selected"));
            $("select.albumIdHolder").append($("<option>", {
                value:albumId
            }).text(albumName));
            $.fancybox.close();		
        },
        onUpload: function(){
            $('.fileUpload').live('change', function(){
                $(Albums.AddNewPhotos.uploadFormClass).find(".loader").show().find(".loader").show();
                $("input.actionType").val("upload");
                App.Form.submit(Albums.AddNewPhotos.uploadFormClass, Albums.AddNewPhotos.showRequest, Albums.AddNewPhotos.showResponse,false);
            });
        },
        showRequest:function(formData, jqForm, options) {
			
        },
        showResponse:function(responseText, statusText, xhr, $form) {
            Ajax.loaderHide(Albums.AddNewPhotos.uploadFormClass);
            var parsedJson = jQuery.parseJSON(responseText);
            var clonedElement = $(".photoDetailsCloned").clone();
            var shown = $(clonedElement).removeClass("hide").removeClass("photoDetailsCloned");
            var photoId;
            var item = parsedJson;
            var photo = item.photo;
            var photoPath = basePath + "img/?w=130&r=4:5&s=photos/albums/" + photo;
            $(shown).find(".photoHolder").attr("src", photoPath).attr("source", photo);
            $(shown).find("input.titleHolder").attr("value", item.title);
            $(shown).find("input.photoId").attr("value", item.id);
            $(shown).find("a.paramId").attr("param_id", item.id);
            $(shown).find(".albumIdHolder").children("option[value='"+item.album_id+"']").attr("selected","selected");
            photoId = item.id;
            var wrap = "<div class='uploaded cleared' id="+photoId+">" + shown.html() + "</div>";
            $(".uploaderContainer").before(wrap);	
            return false;	
        },
        onSavingDetail:function() {
            $('.saveDetail').live('click', function(){
                var formClass = $(this).parent().parent().parent();
                App.Form.submit(formClass, Albums.AddNewPhotos.saveDetailRequest, Albums.AddNewPhotos.saveDetailResponse,false);	
            });	
        },
        onDeletingDetail:function() {
            $('.deleteDetail').live('click', function() {
                var  paramId = $(this).attr("param_id");
                var self = this;
                var sourceImage = $(this).parent().parent().find(".photoHolder").attr("source");
                $.post(basePath + 'UsersAlbumsPhotos/removePhoto', {
                    photoId:paramId, 
                    sourceImage:sourceImage
                }, function(data) {
                    $("#" + paramId).fadeOut();
                });	
                return false;	
            });	
        },
        saveDetailResponse:function(responseText, statusText, xhr, $form) {
            App.Notification.showSuccess($form);
            return false;	
        },	
        saveDetailRequest:function(formData, jqForm, options) {
				
        }
    },
    EditSettings:{
        ready:function() {
            var formClass = $('.editAlbumSettingsForm');	
            App.Form.bind(formClass, Albums.EditSettings.editRequest, Albums.EditSettings.editResponse);	
        },
        editResponse:function(responseText, statusText, xhr, $form) {
            App.Notification.showSuccess($form);
            return false;	
        },	
        editRequest:function(formData, jqForm, options) {
				
        }	
    },	
    MyAlbums: {
        ready:function() {
            App.init.popupBox();
            this.onYesDeleteAlbum();
        },
        deleteResponse:function(responseText, statusText, xhr, $form) {
            var paramId = $(".popupBox").find(".paramId").val();
            $("div." + paramId).fadeOut();
            $.fancybox.close();
            return false;	
        },	
        deleteRequest:function(formData, jqForm, options) {
				
        },	
        onYesDeleteAlbum:function() {
            $('.buttonYes').live('click', function() {
                var formClass = $(".deleteForm");
                App.Form.submit(formClass, Albums.MyAlbums.deleteRequest, Albums.MyAlbums.deleteResponse);
            });		
        }
    }		
};