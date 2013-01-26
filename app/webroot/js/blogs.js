/*
 * Blogs JS
 * Copyright 2012 Christopher Natan
 */
var Blogs = { 
    ready:function() {	
		
    },
    ViewEntry:{
        ready:function() {	
            Comments.deletePopupBoxClass = ".deleteBlogComment";
            Comments.commentBoxClass     = ".commentBlogBox";
            Comments.commentForm         = ".blogCommentForm";
            Comments.clonedElement       = ".clonedBlogCommentElement";
            Comments.appendTarget        = ".loadBlogComments";
            Comments.ready();
            App.init.popupBox();	
        }
    },
    WriteEntry:{
        ready:function() {},
    }
			
};			