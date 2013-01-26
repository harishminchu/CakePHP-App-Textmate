/*
 * Photos JS
 * Copyright 2012 Christopher Natan
 */

var Photos = {
    ready: function(){
        this.ProfilePhoto.init();
        App.init.popupBox();
    },
    isError:function(errorMsg, option) {
        if (typeof(option) == "undefined" || option == 1) {
            $(".uploadForm").find(".notification").text(errorMsg);
            $(".uploadForm").find(".notification").show();
        } else {
            $("uploadForm").find(".notification").hide();
        }	
    },
    jcropAPI:null,
    defaultBigPhoto:"profile-photo-big-none.png",
    defaultSmallPhoto:"profile-photo-small-none.png",
    submitType:"upload",
    ProfilePhoto:{
        init: function(){
            this.onUpload();
            this.onCropApplyChanges();
            this.onCancelCrop();
            this.onEdit();
            this.resizePhoto();
            this.onRemovePhoto();
            this.onYesRemovePhoto(); 
        },
        onUpload: function(){
            $('.fileUpload').live('change', function() {
                $(this).parent().prev().find(".loader").show();
                Photos.submitType = "upload";
                $(".actionType").val("upload");
                Photos.submitForm($(".uploadForm"));
                $("span.editPhoto").show();
                $("span.optionsChanges").hide();
                $(".loader").show();
            });
            $('.fileUpload').live('click', function() {	 
                Photos.destroyJcrop();	
            });
        },
        onCropApplyChanges: function(){
            $('.applyChanges').live('click', function() {
                $("span.editPhoto").show();
                $("span.optionsChanges").hide()
                $(".actionType").val("crop");
                Photos.submitType = "crop";
                Photos.submitForm($(".uploadForm"));
                Photos.destroyJcrop();
            });	 
		   
        },
        onCancelCrop: function(){
            $('.cancelCrop').live('click', function() {
                $("span.editPhoto").show();
                $("span.optionsChanges").hide()
                Photos.destroyJcrop();
            });	  
        },
        onRemovePhoto: function(){
            $('.removePhoto').live('click', function() {
                $(".popupBox").find(".messageSubject").text("PHOTO");
            });	 
		   
        },
        onYesRemovePhoto: function(){
            $('.buttonYes').live('click', function() {
                var sourceImage = $("input.sourceImage").val();
                $.post(basePath + 'Users/removePhoto', {
                    sourceImage:sourceImage
                }, function(data) {
                    $.fancybox.close();
                    $("img.targetPhoto").attr("src", basePath + "img/" + Photos.defaultBigPhoto);
                    $("img.cropPreview").attr("src", basePath + "img/" + Photos.defaultSmallPhoto);
                    $("span.editPhoto").hide();
                });	
            });	   
        },
        uploadResponse: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            if (parsedJson.imagefile) {
                var photoPath = basePath + "img/photos/members/" + parsedJson.imagefile;
                $("img.targetPhoto").attr("src", photoPath).hide();
                $("img.cropPreview").attr("src", photoPath);
                $("input.sourceImage").val(parsedJson.imagefile);
                $('img.targetPhoto').load(function() {
                    $("img.targetPhoto").show();
                    Photos.ProfilePhoto.resizePhoto(); 
                    $(".loader").hide();
                });
				
            } else {
                Photos.isError(parsedJson.error);
            }
            $($form).find(".loader").hide();   
        },
        cropResponse: function(responseText, statusText, xhr, $form){
            var parsedJson = jQuery.parseJSON(responseText);
            if (parsedJson.imagefile) {
                var rand = "?random=" + Math.random(123456789);
                var photoPath = basePath + "img/photos/members/" + parsedJson.imagefile + rand;
                $(".targetPhoto").attr("src",photoPath);
            } else {
                Photos.isError(parsedJson.error);
            }
        },
        onEdit:function() {
            $('.onEdit').click(function(e) {
                var photoPath = $("img.targetPhoto").attr("src");
                photoPath = photoPath.replace("_cropped", "");
                $("img.targetPhoto").attr("src", photoPath);
                $("span.editPhoto").hide();
                $("span.optionsChanges").show();
                Photos.initJcrop(); 
            });
        },
        resizePhoto:function(){
            Photos.resizeImageToAspectRatio($(".targetPhoto"), 200,200);
        }
    },
    submitForm: function(form,showDefineResponse){
        var options = {
            target: $(form).find('.ajaxTarget'),
            beforeSubmit: showRequest,
            success: showResponse,
            resetForm: true
        };
        $(".uploadPhoto").find(".error").hide();
        $(form).ajaxSubmit(options);
        
        function showRequest(formData, jqForm, options){
			
        }
        function showResponse(responseText, statusText, xhr, $form){
            if (Photos.submitType == "upload") {
                Photos.ProfilePhoto.uploadResponse(responseText, statusText, xhr, $form);
            } else {
                Photos.ProfilePhoto.cropResponse(responseText, statusText, xhr, $form);
            }		   
        }
    },
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
    },
    destroyJcrop:function() {
        if (Photos.jcropAPI) {
            Photos.jcropAPI.destroy();
        }
    },
    initJcrop:function() {
        var jcrop_api, boundx, boundy;
		
        $('.targetPhoto').Jcrop({
            onChange: updatePreview,
            onSelect: updateCoords,
            allowSelect :false,
            aspectRatio: 1,
            allowResize:false,
            minSize: [ 150, 150 ],
            maxSize: [ 200, 200 ]
					
        },function(){
            var bounds = this.getBounds();
            boundx = bounds[0];
            boundy = bounds[1];
            jcrop_api = this;
            Photos.jcropAPI = this; 
            this.setSelect(getRandom());
        });
        function updatePreview(c) {
            if (parseInt(c.w) > 0) {
                var rx = 50 / c.w;
                var ry = 50 / c.h;
	
                $('.cropPreview').css({
                    width: Math.round(rx * boundx) + 'px',
                    height: Math.round(ry * boundy) + 'px',
                    marginLeft: '-' + Math.round(rx * c.x) + 'px',
                    marginTop: '-' + Math.round(ry * c.y) + 'px'
                });
            }
        }
        function updateCoords(c) {
            $('#x').val(c.x);
            $('#y').val(c.y);
            $('#w').val(c.w);
            $('#h').val(c.h);
        };
        function getRandom() {
            var dim = Photos.jcropAPI.getBounds();
            return [
            Math.round(Math.random() * dim[0]),
            Math.round(Math.random() * dim[1]),
            Math.round(Math.random() * dim[0]),
            Math.round(Math.random() * dim[1])
            ];
        }	
    }
	
};
