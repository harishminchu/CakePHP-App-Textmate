/*
 * Activities JS
 * Copyright 2012 Christopher Natan
 */
var Activities = {
    ready: function(){
        this.UsersPost.init();
        this.UsersPostsComment.init();
        this.PostAndCommentContent.init();
        this.PostAndCommentLike.init();
        this.onPopupButtonYes();
        App.init.lookupBox();
        App.init.popupBox();
        this.init.autoGrow();
        this.onTabMenu();
        this.UsersDatings.ready();
        this.Youtube.ready();
    },
    init: {
        autoGrow:function() {
            $(".autoGrow").autogrow({
                height:"16px"
            });	
        }
    },
    Youtube: {
        scriptAdd:null,
        ready: function(){
            this.addScript();
            this.searchVideo();
            this.onRemoveVideo();
        },	
        feedUsers: "http://gdata.youtube.com/feeds/users/",
        feedQuery:"http://gdata.youtube.com/feeds/api/videos",
        randomKeys:function(randomNumber) {
            var keys = App.youtubeKeys;
            return keys[randomNumber];			
        },
        addScript:function(q) {
            if (typeof(q) == "undefined") {	
                var randomNumber=Math.floor(Math.random()*App.youtubeKeys.length);
                q = this.randomKeys(randomNumber);
            }
            if (q.length >= 2) {
                $(".videoContainer").find(".lists").html("");
				
                var ytUrl = "&q=" + q;
                var params = "?alt=json-in-script&callback=Activities.Youtube.showVideos&max-results=" + Activities.Youtube.results;
                var url = this.feedQuery + params + ytUrl;
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                $(".script").html(script);
            } else {
                $(".loader").hide();
            }	
        },		
        results:12,
        playerUrl: null,
        autoplay: 0,
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
                if(len>=30) {
                    title = title.substring(0,30) + "...";
                }
				
                var category = entries[i].media$group.media$category[0].label;
                var thumbnailUrl = entries[i].media$group.media$thumbnail[0].url;
                if (typeof(entries[i].media$group.media$content) != "undefined") {
                    var playerUrl = entries[i].media$group.media$content[0].url + "&amp;autoplay=1";
					
                    var url = playerUrl.replace("http://www.youtube.com/v/", "");
                    var splits = url.split('?');
                    var ytID = splits[0];
                    var splits = ytID + "{}" + category + "{}" + subject + "{}" + playerUrl + "{}" + thumbnailUrl;
                    var datas = "<input type='hidden' value='" + splits + "'/>";
                    var options = "<span class='add'><a href='javascript:void(1)' param_id='" + ytID + "' class='addThisVideo'>Add</a></span>";
                    var spanTitle = "<span class='title'>" + title + "</span>";
                    var spanCategory = "<span class='category'>" + category + "</span>";
                    var img = "<img src='" + thumbnailUrl + "' width='90' height='68'>";
                    var a = "<a href='javascript:void(1)' param_id='" + ytID + "' class='addThisVideo'>" + img + "</a>";
					
                    var li = a + spanTitle + spanCategory + options + datas;
                    $(".videoContainer").find("ul").append("<li>" + li + "</li>");
                }	
            }
            this.onClickVideo();
            $(".loader").hide();
        },
        onClickVideo:function() {
            $('a.addThisVideo').click(function() {
                var records = $(this).closest("li").find("input").val();
                var splits = records.split("{}");
                var img = "<img src='"+splits[4]+"' width='100' height='75'>";
                $('input.sourceVideo').val(records);
                $(".imageContainer").html(img);
                $(".addVideoContainer").show();	
                $.fancybox.close();
            });	
        },
        onRemoveVideo:function() {
            $('.removeVideo').click(function() {
                $('input.videoId').val("");
                $(".imageContainer").html("");
                $(".addVideoContainer").hide();
                $('input.sourceVideo').val("");
            });		
        },
        searchVideo:function() {
            var self = this;
            $('.searchVideo').click(function() {
                var q = $(".searchText").val();
                var key = q.replace(' ','\u00a0');
                self.addScript(key);
            });		
        }
		
    },
    UsersDatings:{
        ready:function() {
            this.bindForm();
            this.onUpdatingPreferences();	
        },
        bindForm:function() {
            var formClass = $(".datingForm");
            var options = {
                target: $(formClass).find('.ajaxTarget'),
                beforeSubmit: showRequest,
                success: showResponse
            };
            $(formClass).ajaxForm(options);
            function showRequest(formData, jqForm, options){	
                if($(formClass).find(".inputDatings").val() == Activities.textDatingText
                    || $.trim((formClass).find(".inputDatings").val()) == "") {
                    App.Notification.showError(jqForm);
                    return false;	
                }	
            }
            function showResponse(responseText, statusText, xhr, $form){
                App.Notification.showSuccess(formClass);	
            }
        },
        onUpdatingPreferences: function(){
            $(".datingForm").find('.inputDatings').click(function(){
                if (Activities.textDatingText == $(this).val()) {
                    $(this).val("").removeClass("gray");
                }	
            }).blur(function(){
                if ($(this).val().length <= 0) {
                    $(this).val(Activities.textDatingText).addClass("gray");
                }
            });
        },
    },
    onTabMenu:function() {
        Activities.windowResize();
        Activities.onTabMenuDefault();
        $(".tabMenu").find("a").live('click', function(){
            $(".tabMenu").find(".fixed").hide();
            var cls = $(this).attr("class");
            var parent = this;
			
            $(".tabMenu").find("li.selected").removeClass("selected");
            $(this).parent().addClass("selected");
            $(".tabConnected").hide();
            if (cls == "datings") {
                $(".datingForm").find(".notification").hide();
            }
            $("." + cls).show();
            $(this).next("div").show(1, function(){
                var position = $(parent).parent("li").offset();
                var posLeft = position.left + 1;
                var w = $(parent).parent("li").width();
                $(parent).next("div.fixed").width(parseInt(w) + 24 + "px");	
                $(parent).next("div.fixed").css("left", posLeft );
            });
			
        });
    },
    onTabMenuDefault:function() {
        var position = $("ul").find("li.selected").offset();
        var posLeft = position.left + 1;
        var w = $("ul").find("li.selected").width();
        $("ul").find("li.selected").find("div.fixed").width(parseInt(w) + 24 + "px");	
        $("ul").find("li.selected").find("div.fixed").css("left", posLeft );
        $("ul").find("li.selected").find("div.fixed").show();
    },
    windowResize:function() {
        $(window).resize(function() {
            Activities.onTabMenuDefault();		
        });
    },
    textDatingText:"I am looking for a textmate...",
    textAreatext:"What is your story...",
    UsersPost: {
        init: function(){
            this.clonedElement = $(".clonedPostedElement").clone();
            this.bindForm();
            this.onPosting();
            this.onDeletePost();
            this.onAddPhoto();
            this.onUpload();
            this.onCancelPhoto();
        },
        clonedElement: null,
        formClass: ".postForm",
        onPosting: function(){
            var form = this.formClass;
            $(form).find('.inputPost').click(function(){
                if (Activities.textAreatext == $(this).val()) {
                    $(this).val("").removeClass("gray");
                }
                $(form).find('.showHide').slideDown("fast");
				
                if($("input.sourceImage").val() == "") {
                    $(".addPhoto").hide();	
                }
				
            }).blur(function(){
                if ($(this).val().length <= 0) {
                    $(form).find('.showHide').slideUp("fast");
                    $(this).val(Activities.textAreatext).addClass("gray");
                }
            });
        },
        bindForm: function(){
            var formClass = this.formClass;
            var options = {
                target: $(formClass).find('.ajaxTarget'),
                beforeSubmit: showRequest,
                success: showResponse,
                resetForm: true
            };
            $(formClass).ajaxForm(options);
            function showRequest(formData, jqForm, options){	
                var valid = true;
                $.each(formData, function(i, item){
                    var val = $.trim(item.value);
                    if (item.name == "data[message]") {
                        if (val.length <= 0 || val == Activities.textAreatext) {
                            valid = false;
                        }
                    }	
                });	
				
                if(!valid) {
                    $(".loader").hide();
                    return false;	
                }	
            }
            function showResponse(responseText, statusText, xhr, $form){
                Activities.UsersPost.displayNew(responseText, statusText, xhr, $form);
                Activities.UsersPostsComment.onCommenting();
                App.init.popupBox();
                return false;
            }
        },
        displayNew: function(responseText, statusText, xhr, $form){
            
            var parsedJson = jQuery.parseJSON(responseText);
            var clonedElement = Activities.UsersPost.clonedElement;
            if (App.checkSessions(parsedJson)) {
                var shown = $(clonedElement).removeClass("hide").removeClass("clonedPostedElement");
                var postId = 0;
                $.each(parsedJson, function(i, item){
                    var photo = item.photo;
                    var video = item.video_url;
                    if(photo!="") {
                        var path = basePath + "img/?w=140&r=4:3&s=photos/activities/" + photo;
                        var activityPhoto = "<img src='"+path+"' class='activity-photo bordered-gray'>";
                        var exist = $(shown).find(".activity-photo").length;
                        if (!exist) {
                            $(shown).find("div.photoAndvideo").append(activityPhoto);
                        } else {
                            $(shown).find(".activity-photo").attr("src", path);
                        }	
                    }
                    if(video!="") {
                        var path = item.video_thumb_url;
                        var activityVideo = "<img src='"+path+"' class='activity-video bordered-gray' width='140' height='105'>";
                        var exist = $(shown).find(".activity-video").length;
                        if (!exist) {
                            $(shown).find("div.photoAndvideo").append(activityVideo);
                        } else {
                            $(shown).find(".activity-video").attr("src", path);
                        }	
                    }
                    postId = item.id;
                    $(shown).find("span.postedMessage").text(item.message);
                    $(shown).find("li.dateCreated").text(item.created);
                    $(shown).find("a.onDeletePost").attr("param_id", postId);
                    $(shown).find("a.showComment").attr("param_id", postId);
                    $(shown).find("a.iLikePost").attr("param_id", postId);
					
                });
				
                var rands = Math.random(123456789);
                $(shown).find("form.commentForm").attr("id", rands);
                $(shown).find("form.commentForm").find("input.activityId").val(postId);
				
                var wrap = "<div class='posted postedBox'>" + shown.html() + "</div>";
                $(".boxOnPost").after(wrap);
            }
            Activities.UsersPost.resetUploadedPhoto();	
            Activities.UsersPost.resetAddedVideo();
        },
        deletePost: function(responseText, statusText, xhr, $form){
            $.fancybox.close();
            Activities.UsersPost.deleteParentObject.delay(500).fadeOut(500);
        },
        /* on clicking the delete link */
        onDeletePost: function(){
            $('.postedBox').find(".onDeletePost").live('click', function(){
                $('.popupBox').find(".messageSubject").text("POST");
                Activities.deleteParentObject = $(this).parent().parent().parent().parent().parent("div");
                var paramId = $(this).attr("param_id");
                $(".popupBox").find("input.paramId").val(paramId);
                
                var action = $(".popupBox").find("form.deleteForm").attr("action");
                var path = action.charAt(0);
                if (path == "/") {
                    var actionURL = path + "usersActivities" + "/deletePost";
                }
                else {
                    var actionURL = "usersActivities" + "/deletePost";
                }
                $(".popupBox").find("form.deleteForm").attr("action", actionURL);
            });
        },
        onAddPhoto: function(){
            $('.onAddPhoto').live('click', function(){
                $(".boxOnPost").find(".addPhoto").show();
                $(".boxOnPost").find(".showHide").hide();
                $(".fileUploadContainer").show();
            });	
        },
        onCancelPhoto: function(){
            $('.onCancelPhoto').live('click', function(){
                var sourceImage = $("input.sourceImage").val();
                if (sourceImage != "") {
                    $.post(basePath + 'UsersActivities/removePhoto', {
                        sourceImage: sourceImage
                    }, function(data){
                        });
                }
                Activities.UsersPost.resetUploadedPhoto();
                $(".boxOnPost").find(".inputPost").focus();	
            });	
        },
        resetUploadedPhoto:function() {
            var sourceImage = $("input.sourceImage").val();
            if (sourceImage != "") {
                $(".targetPhoto").attr("src", "").hide();
                $(".sourceImage").val("");
                $(".onAddPhoto").text("Add Photo");
            }	
            $(".boxOnPost").find(".inputPost").trigger("click");
        },
        resetAddedVideo:function() {
            var sourceImage = $("input.sourceVideo").val();
            if (sourceImage != "") {
                $(".imageContainer").html();
                $(".addVideoContainer").hide();
            }	
        },
        onUpload: function(){
            $('.fileUpload').live('change', function() {
                $(".uploadForm").find(".loader").show().find(".loader").show();
                $(".actionType").val("upload");
                $(".fileUploadContainer").hide();
                $(".onAddPhoto").text("Replace Photo");
                Activities.showResponse = "upload";
                Activities.submitForm($(".uploadForm"));
            });
		   
        },
        uploadResponse: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            if (parsedJson.imagefile) {
                var photoPath = basePath + "img/?w=100&r=4:3&s=photos/activities/" + parsedJson.imagefile;
                $("img.targetPhoto").attr("src", photoPath).hide();
                $("input.sourceImage").val(parsedJson.imagefile);
                $('img.targetPhoto').load(function() {
                    $("img.targetPhoto").show();
                    $(".boxOnPost").find(".inputPost").trigger("click").focus();
                });
				
            } else {
                $(".uploadForm").find(".notification").show();
            }
            $($form).find(".loader").hide();   
        }
    },
    
    showResponse: "new",
    responseAction: null,
    deleteParentObject: null,
    submitForm: function(form){
        var options = {
            target: $(form).find('.ajaxTarget'),
            beforeSubmit: showRequest,
            success: showResponse,
            resetForm: true
        };
        $(form).ajaxSubmit(options);
        function showRequest(formData, jqForm, options){
            var val = $(jqForm).find(".paramId").val();
            if(typeof(val)=="undefined") {
                val = $(jqForm).find(".inputComment").val();
            }
            if(val.length<=0) {
                $("img.loader").hide();
                return false;
            }
        }
        function showResponse(responseText, statusText, xhr, $form){
            if (Activities.showResponse == "new") {
                Activities.UsersPostsComment.displayNew(responseText, statusText, xhr, $form);
                App.init.popupBox();
            }
            else 
            if (Activities.showResponse == "delete") {
                Activities.deletePostOrComment(responseText, statusText, xhr, $form);
            }
		    	
            if (Activities.showResponse == "upload") {
                Activities.UsersPost.uploadResponse(responseText, statusText, xhr, $form);
            }		
        }
    },
    deletePostOrComment: function(responseText, statusText, xhr, $form){
        $.fancybox.close();
        Activities.deleteParentObject.delay(500).fadeOut(500);
    },
    onPopupButtonYes: function(){
        var formClass;
        $('.popupBox').find(".buttonYes").click(function(){
            formClass = $(this).parent("form");
            Activities.showResponse = "delete";
            Activities.submitForm(formClass);
        });
    },
    
    UsersPostsComment: {
        init: function(){
            this.onCommenting();
            this.bindCommentButton();
            this.onDeleteComment();
            //this.onCommentKeyPress();
            this.countComment();
            this.onHideComment();
        },
        formClass: ".commentForm",
        onCommenting: function(){
            $('.showComment').toggle(function(){
                $(this).parent().parent().parent().next("div").slideDown();
                /* focus textarea */
                $(this).parent().parent().parent().next(".commentBox").find(".inputComment").focus();
            }, function(){
                $(this).parent().parent().parent().next("div").slideUp();
            });
        },
        /* submitting new comment */
        bindCommentButton: function(){
            var formClass;
            $('.commentButton').live('click', function(){
                Activities.showResponse = "new";
                formClass = $(this).parent().parent("form");
                Activities.submitForm(formClass);
            });
        },
        displayNew: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            var clonedElement = $(".clonedReplyElement").clone();
            var shown = $(clonedElement);
            $.each(parsedJson, function(i, item){
                $(shown).find(".commentMessage").text(item.comment);
                $(shown).find("a.dateCreated").text(item.created);
                $(shown).find(".onDeleteComment").attr("param_id", item.id);
            });
            var wrap = "<div class='reply cleared marginbottom10 clonedReplyElement'>" + shown.html() + "</div>";
            $($form).before(wrap);
        },
        onCommentKeyPress: function() {
            $('input.inputComment').live('keypress', function(e) {
                if(e.keyCode == 13) {
                    e.preventDefault();
                    $(this).next().next("div").find(".button").trigger("click");
                    if($(this).val().length<=0){
                        $(".loader").hide();
                    }
                    return false;
                }
				
            });
        },
        onHideComment:function() {
            $('.hideComment').live('click', function(e) {
                $(this).parent().parent().parent().prev().find(".showComment").trigger("click");
            });
        },
        onDeleteComment: function(){
            $('.commentBox').find(".onDeleteComment").live('click', function() {
                $('.popupBox').find(".messageSubject").text("COMMENT");
                Activities.deleteParentObject = $(this).parent().parent().parent().parent("div");
                var paramId = $(this).attr("param_id");
                $(".popupBox").find("input.paramId").val(paramId);
                
                var action = $(".popupBox").find("form.deleteForm").attr("action");
                var path = action.charAt(0);
                if (path == "/") {
                    var actionURL = path + "UsersActivitiesComments" + "/deleteComment";
                }
                else {
                    var actionURL = "UsersActivitiesComments" + "/deleteComment";
                }
                $(".popupBox").find("form.deleteForm").attr("action", actionURL);
            });
        },
        countComment: function(){
            $(".postedBox").each(function(index) {
                var total = $(this).find(".commentBox").find(".reply").length;
                var plural = "";
                if (parseInt(total) >= 2) {
                    plural = "s";
                }	
                if (parseInt(total) >= 1) {
                    var textTotal = total + " " + $(this).find("a.showComment").text();
                    $(this).find("a.showComment").text(textTotal + plural);
                }	
            });	
        }
    },
    PostAndCommentContent: {
        init: function(){
        //this.onScrollEnd();
        },
        onScrollEnd: function(){
            var self = this;
            $(window).scroll(function(){
                if  ($(window).scrollTop() == $(document).height() - $(window).height()){
                    self.loadMoreContent();  
                }
            });
        },
        loadMoreContent:function() {
            $.post(basePath + 'usersActivities/loadMorePost', function(data) {
                $($("div.postedBox").last()).after(data);
                Activities.UsersPostsComment.onCommenting();
            });	
        }
    },	
    PostAndCommentLike: {
        init: function(){
            this.onLike();
        },
        assignCommentLiked: function(){
            var postIds = $(".boxCommentLikeIds").val();
            if(postIds.length <=0) {
                return false;
            }
            var likeId;
            $.post(basePath + 'usersActivitiesComments/lookLiked',{
                postIds:postIds
            }, function(data) {
                var parsedJson = jQuery.parseJSON(data);
                $.each(parsedJson, function(i, item){
                    likeId = parseInt(item.UsersLike.like_id);
                    var val = $(".clk-" + likeId).text();
					
                    if (val.length <= 0) {
                        val = 1;
                    }else {
                        val = parseInt(val) + 1;
                    }	
                    $(".clk-" + likeId).text(val);
                //$(".lk-" + likeId).prev("a").show();
                //$(".lk-" + likeId).next("a").show();
                });			
            }); 
        },
        onLike: function(){
            $('.iLikePost').live('click', function(e) {
                var text = $(this).text();
                var postId = $(this).attr("param_id");
                var total = $(this).parent().next().next().next().find(".showLookup").text();
                var getNumber = parseInt(total.charAt(0));
				
                if(!getNumber) {
                    total = 0;
                }else {
                    total = getNumber;
                }
                if(text == "Like") {
                    $(this).text("Unlike");
                    total = total + 1;
                } else {
                    $(this).text("Like");	
                    total = total - 1;
                }
				
                if (total == 1) {
                    $(this).parent().next().next().next(".liked").html("<a class='nohover'>you like this</a>");
                }	
                if (total>=1) {
                    $(this).parent().next().next().next().find(".showLookup").text(total + " like this");
                }else {
                    $(this).parent().next().next().next(".liked").html("");
                }
				
                $.post(basePath + 'usersActivities/iLike', {
                    likeType:text, 
                    postId:postId
                },function(data) {
                    $($("div.postedBox").last()).after(data);
                    Activities.UsersPostsComment.onCommenting();
                });
            });		
        }
    }
};
