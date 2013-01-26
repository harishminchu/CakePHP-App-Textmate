/*
 * Comments JS
 * Copyright 2012 Christopher Natan
 */

var Comments = {
		
    deletePopupBoxClass:null, /* ".deletePhotoComment"        */
    commentBoxClass:null,     /* ".commentPhotoBox"           */
    commentForm:null,         /* ".photoCommentForm"          */
    clonedElement:null,       /* ".clonedPhotoCommentElement" */
    appendTarget:null,        /* ".loadPhotoComments"         */
		
    ready:function() {
        this.onPopupButtonYesDelete();
        this.bindCommentForm();
        $(".autoGrow").autogrow({
            height:"16px"
        });
    },
    onPopupButtonYesDelete: function(){
        var formClass;
        $('.popupBox').find(".buttonYesDeleteComment").live("click", function(){
            formClass = $(this).parent("form");
            App.Form.submit(formClass, Comments.deleteRequest, Comments.deleteResponse);
        });
    },
    deleteRequest:function(formData, jqForm, options) {},
    deleteResponse: function(responseText, statusText, xhr, $form){
        var paramId = $(Comments.deletePopupBoxClass).find(".paramId").val();
        $(Comments.commentBoxClass + "." + paramId).fadeOut("slow");
        $.fancybox.close();
    },
    bindCommentForm: function(){
        var formClass = $(Comments.commentForm); 
        App.Form.bind(formClass, Comments.commentRequest, Comments.commentResponse);
    },
    commentRequest:function(formData, jqForm, options) {
        var commentVal = $(jqForm).find(".inputComment").val();
        if ($.trim(commentVal) == "") {
            $(".loader").hide();
            return false;
        }	
    },
    commentResponse: function(responseText, statusText, xhr, $form){
        var parsedJson = jQuery.parseJSON(responseText);
        var clonedElement = $(Comments.clonedElement).clone();
        var shown = $(clonedElement);
        var commentBoxClass = Comments.commentBoxClass.replace(".", "");
        var commentId;
        $.each(parsedJson, function(i, item){
            $(shown).find(".commentMessage").text(item.comment);
            $(shown).find("a.dateCreated").text(item.created);
            $(shown).find(".onDeleteComment").attr("param_id", item.id);
            $(shown).find(".commentPhotoBox").addClass(item.id);
            commentId = item.id;
        });
        var wrap = "<div class='posted "+commentBoxClass+" "+ commentId +"'>" + shown.html() + "</div>";
        $(Comments.appendTarget).append(wrap);
        App.init.popupBox();
    }	
			
}	
