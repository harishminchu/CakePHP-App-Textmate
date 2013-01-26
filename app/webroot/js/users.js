/*
 * Users JS
 * Copyright 2012 Christopher Natan
 */


var Users = {
    BasicInformation:{
        ready: function(){
            this.onSelectDate();
        },
        onSelectDate:function() {
            $(".dateSelect").find("select").live('change', function(){
                if ($(this).val()!="") {
                    var d = $("select.selectYear").val() + "-" + $("select.selectMonth").val() + "-" + $("select.selectDay").val();
                    $(".birthDate").val(d);
                }	
            });	
        },	
    },
    ContactInformation:{},
    Locations: {
        ready: function(){
            this.loadLocations();
            this.onSelectProvince();
        },
        provinces: null,
        loadLocations: function(){
            $.post(basePath + 'Users/loadAllLocations', function(data){
                var parsedJson = jQuery.parseJSON(data);
                Users.Locations.provinces = parsedJson;
            });
        },
        onSelectProvince: function(){
            $("select.provinceId").live('change', function(){
                var provinceId = $(this).val();
                $('select.locationId').find("option").remove();
                Users.Locations.populateLocation(provinceId);
            });
        },
        populateLocation: function(provinceId){
            var output = [];
            $.each(Users.Locations.provinces, function(i, item){
                if (item.province_id == provinceId) {
                    output.push('<option value="' + item.id + '">' + item.location + '</option>');
                }
            });
            $('select.locationId').append(output.join(''));
        }
    },
    Account:{
        ready:function() {
            this.init.poshyTip();
        },
        init:{
            poshyTip: function(){
                $('input.userPassword').poshytip({
                    className: 'tip-twitter',
                    allowTipHover: false,
                    alignTo: 'target',
                    alignX: 'right',
                    alignY: 'center',
                    offsetX: 28,
                    showTimeout: 1,
                    showOn: 'focus'
                });  
            }	
        }
    },
    PublicProfile:{
        ready:function() {
            this.loadPublics();
            this.AlbumsPhotos.ready();
        },
        loadPublics:function() {
            var userId = $("input.userId").val();
            $.post(basePath + 'UsersPublics/shows/' + userId, function(data){
                var content = $(".loadFriends").html(data);
                $(content).find(".loadedAbums").prependTo(".loadAlbums");

                var friendCount = $(".userFriends").length;
                if (friendCount) {
                    $(".friendCount").text(friendCount);
                }
                App.ready();
            });	
        },
        AlbumsPhotos: {
            ready: function(){
                this.onShowAlbumPhotos();
                this.readyComments();
                this.onShowPhotosComment();
            },
            readyComments:function() {
                Comments.deletePopupBoxClass = ".deletePhotoComment";
                Comments.commentBoxClass     = ".commentPhotoBox";
                Comments.commentForm         = ".photoCommentForm";
                Comments.clonedElement       = ".clonedPhotoCommentElement";
                Comments.appendTarget        = ".loadPhotoComments";
                Comments.onPopupButtonYesDelete();
            },
            executeOnce:true,
            onShowAlbumPhotos: function(){
                var parents = this;
                $(".showAlbumPhotos").live('click', function(){
                    $(this).parent().next().find(".loader").show();
                    var self = this;
                    var paramId = $(this).attr("param_id");
                    $.post(basePath + 'UsersPublics/albumPhotos/' + paramId, function(data){
                        $(".loadAlbumPhotos").html(data);
						
                        $("a.photos").show(1, function(){
                            var title = $(self).attr("title");
                            $(this).text(title + " Photos");
                            $(self).parent().next().find(".loader").hide();
                            $(".autoGrow").autogrow({
                                height: "16px"
                            });
                            $(this).parent().show();
                            $(this).trigger("click");
                            $(".loadAlbumPhotos").show();
                            $("div.photos").show();
                            Galleries.ready();
                            Comments.bindCommentForm();
                        });
                    });
                });
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
		
		
			
    }
	
    
};
