/*
 * Videos JS
 * Copyright 2012 Christopher Natan
 */


var Videos = {
    ready: function(){
        App.init.popupBox(); 
        Videos.AddCategory.ready();
    },
    CategoryAdd: {
        ready:function() {
            App.init.popupBox();
            $("a.addCategory").trigger("click");
            Videos.AddCategory.ready();
            this.onCancel();
        },	
        onCancel:function() {
            $('.popupBox').find(".onCancel").click(function(){
                window.history.go(-1);
            });	
        }
    },
    MyVideos: {
        ready:function() {
            App.init.popupBox();
            this.onPopupButtonYes();
        },
        onPopupButtonYes: function(){
            var formClass;
            $('.popupBox').find(".buttonYes").click(function(){
                formClass = $(this).parent("form");
                App.Form.submit(formClass, Videos.MyVideos.request, Videos.MyVideos.response);
            });
        },
        request:function(formData, jqForm, options) {},
        response: function(responseText, statusText, xhr, $form){
            $.fancybox.close();
            var paramId = $($form).find(".paramId").val();
            $("li." + paramId).fadeOut();
        },
    },
    PlayVideo:{
        ready:function() {
            Comments.deletePopupBoxClass = ".deleteVideoComment";
            Comments.commentBoxClass     = ".commentVideoBox";
            Comments.commentForm         = ".videoCommentForm";
            Comments.clonedElement       = ".clonedVideoCommentElement";
            Comments.appendTarget        = ".loadVideoComments";
            Comments.ready();
            this.onHoverPhoto();
            this.windowResize();
            this.onPlay();
            this.onPlayTheHover();
            App.init.popupBox();
        },	
        onHoverPhoto:function() {
            $('a.playVideo').mouseover(function(){
                var pos = $(this).position();
                var t = pos.top;
                var l = pos.left;
                var tm = t/2;
                var lm = l/2;
                $(".playHover").css("top",t + "px").css("left",l+15 +tm + 15 + "px").show();
            }).mouseout(function(){
                //$(".playHover").hide();
                });	
        },
        windowResize:function() {
            $(window).resize(function() {
                $('a.playVideo').trigger("mouseover");	
            });
        },
        onPlay: function(){
            $('a.playVideo').click(function(){
                var src = $(this).attr("href");
                $(".playHover").hide();
                $(this).hide();
                $(".iframePlay").attr("src", src).show();
                return false;
            });
        },
        onPlayTheHover: function(){
            $('img.playHover').click(function(){
                $('a.playVideo').trigger("click");
            });	
        }
    },
    ManageVideos: {
        ready:function() {
            App.init.popupBox();
            this.onPopupButtonYes();
            App.init.lookupBox();
        },
        onPopupButtonYes: function(){
            var formClass;
            $('.popupBox').find(".buttonYes").click(function(){
                formClass = $(this).parent("form");
                App.Form.submit(formClass, Videos.ManageVideos.request, Videos.ManageVideos.response);
            });
        },
        request:function(formData, jqForm, options) {},
        response: function(responseText, statusText, xhr, $form){
            $.fancybox.close();
            var paramId = $($form).find(".paramId").val();
            $("li." + paramId).fadeOut();
        },
    },
    AddCategory:{
        ready:function() {
            this.onSubmitCreateCategory();	
        },
        onSubmitCreateCategory: function(){
            var formClass = $(".addCategoryForm");
            App.Form.bind(formClass, Videos.AddCategory.showRequest, Videos.AddCategory.showResponse, false);
			   
        },
        showRequest:function(formData, jqForm, options) {
            var val = $(".addCategoryForm").find("input.categoryName").val();
            if($.trim(val) == "") {
                App.Notification.showError(jqForm);
                return false;
            }
        },
        showResponse: function(responseText, statusText, xhr, $form){
            App.Notification.showSuccess($form);
            var keys = $($form).find(".categoryName").val();
            $($form).find(".notification").show().delay(500).fadeOut(function(){
                window.location.href= basePath + "videos/search?q=" + keys;
            });
			
        }
    },
    Edit:{
        ready:function() {
            this.onAddPhoto();
            this.onUpload();
            this.onCancelPhoto();
            Videos.AddCategory.ready();
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
                App.Form.submit(formClass, Videos.Edit.request, Videos.Edit.response);
            });
        },
        request:function(formData, jqForm, options) {},
        response: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            var photo = basePath + "img/photos/videos/" + parsedJson.imagefile;
            $(".targetPhoto").attr("src",photo);
            App.Image.resizeImageToAspectRatio($(".targetPhoto"), "177", "132");
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
        }
    },
    Youtube: {
        scriptAdd:null,
        ready: function(){
            this.addScript();
            this.onChangeCategory();
            this.searchVideo();
            this.onPressSearch();
        },	
        feedUsers: "http://gdata.youtube.com/feeds/users/",
        feedQuery:"https://gdata.youtube.com/feeds/api/videos",
        randomKeys:function(randomNumber) {
            var keys = App.youtubeKeys;
            return keys[randomNumber];			
        },
        addScript:function(q) {
            if (typeof(q) == "undefined") {	
                var randomNumber=Math.floor(Math.random()*App.youtubeKeys.length);
                q = this.randomKeys(randomNumber);
                q = this.loadSearch(q);
            }
            if (q.length >= 2) {
                $(".videoContainer").find(".lists").html("");
				
                var ytUrl = "&q=" + q;
                var params = "?alt=json-in-script&callback=Videos.Youtube.showVideos&max-results=" + Videos.Youtube.results;
                var url = this.feedQuery + params + ytUrl;
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                $(".script").html(script);
            } else {
                $(".loader").hide();
            }	
        },		
        results:25,
        playerUrl: null,
        autoplay: 0,
        addCategory:false,
        showVideos: function(data){
            var feed = data.feed;
            var entries = feed.entry || [];
			
            for (var i = 0; i < entries.length; i++) {
                var entry = entries[i];
                var title = entry.title.$t;
                title = title.replace(/\'/g, "");
                title = title.replace(/\"/g, "");
                var len = title.length;
                var subject = title;
                if(len>=50) {
                    title = title.substring(0,50) + "...";
                }
                var category = entries[i].media$group.media$category[0].label;
                var thumbnailUrl = entries[i].media$group.media$thumbnail[0].url;
                if (typeof(entries[i].media$group.media$content) != "undefined") {
                    var playerUrl = entries[i].media$group.media$content[0].url + "&amp;autoplay=1";
					
                    var url = playerUrl.replace("http://www.youtube.com/v/", "");
                    url = url.replace("https://www.youtube.com/v/", "");
                    var splits = url.split('?');
                    var ytID = splits[0];
                    var splits = ytID + "|" + category + "|" + subject + "|" + playerUrl + "|" + thumbnailUrl;
                    var datas = "<input type='hidden' value='" + splits + "'/>";
                    var options = "<span class='add'><a href='#addVideo' param_id='" + ytID + "' class='showPopup'>Add To My Videos</a></span>";
                    var spanTitle = "<span class='title'>" + title + "</span>";
                    var spanCategory = "<span class='category'>" + category + "</span>";
                    var img = "<img src='" + thumbnailUrl + "' width='136' height='102'>";
                    var a = "<a href='" + playerUrl + "' param_id='" + ytID + "' class='video showLookup'>" + img + "</a>";
					
                    var li = a + spanTitle + spanCategory + options + datas;
                    $(".videoContainer").find("ul").append("<li>" + li + "</li>");
                }	
            }
            App.init.lookupBox();
            App.init.popupBox();
            this.bindAddVideo();
            this.onClickVideo();
            $(".loader").hide();
        },
        onClickVideo:function() {
            $(".videoContainer").find('.showPopup').click(function() {
                var records = $(this).parent().next("input").val().split("|");
                $(".addVideo").find("input.title").val(records[2]);
                $(".addVideo").find("input.videoId").val(records[0]);
                $(".addVideo").find("input.videoUrl").val(records[3]);
                $(".addVideo").find("input.thumbUrl").val(records[4]);
                Videos.Youtube.checkCategory(records[1]);
                $('.categoryId').trigger("change");
            });	
        },
        loadSearch:function(q ) {
            var key = $(".searchText").val();
            if ($.trim(key) != "") {
                q = key;
            }
            return q;		
        },
        onChangeCategory:function() {
            $('.categoryId').change(function() {
                var t = $(this).find("option:selected").text();
                $(".addVideo").find("input.categoryName").val(t);
            });		
        },
        checkCategory:function(category) {
            var counter = 0;
            $("select.categoryId").find("option").each(function(key, value) { 
                if($.trim(category.toLowerCase()) == $.trim($(this).text().toLowerCase())) {
                    $(this).attr("selected","selected"); 
                    counter++;	
                }
            });
            key = Math.random(123456789);
            if(counter == 0) {
                $('select.categoryId').append($('<option>', {
                    value : key
                }).text(category).attr("selected","selected")); 
            }
            return 1;
        },
        bindAddVideo: function(){
            var formClass = $('.addVideoForm');
            App.Form.bind(formClass, Videos.Youtube.addVideoRequest, Videos.Youtube.addVideoResponse);
        },
        addVideoRequest:function(formData, jqForm, options) {
            var error = false;
        },
        addVideoResponse:function(responseText, statusText, xhr, $form) {
            App.Notification.showSuccess($form);
            $($form).find(".notification").show('fast').delay(1).fadeOut(1, function(){
                $.fancybox.close();
            });
			
        },
        searchVideo:function() {
            var self = this;
            $('.searchVideo').live("click", function() {
                var q = $.trim($(".searchText").val());
                if (q.length>=1) {
                    var key = q.replace(' ', '\u00a0');
                    self.addScript(key);
                } else {
                    $(".loader").hide();
                }
            });		
        },
        onPressSearch:function() {
            $('input.searchText').live('keypress', function(e) {
                if(e.keyCode == 13) {
                    e.preventDefault();
                    $(".searchVideo").trigger("click");
                    return false;
                }
				
            });		
        }
		
		
    }
	
};
