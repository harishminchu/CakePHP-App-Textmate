var Galleriffic = { 
    ready:function() {
        this.init();	
    },
    init:function() {
		
        $('div.navigation').css({
            'width' : '200px', 
            'float' : 'left',
            "margin-left" : "15px"
        });
        $('div.contents').css('display', 'block');

	  
        var onMouseOutOpacity = 0.90;
        $('#thumbs ul.thumbs li').opacityrollover({
            mouseOutOpacity:   onMouseOutOpacity,
            mouseOverOpacity:  1.0,
            fadeSpeed:         'fast',
            exemptionSelector: '.selected'
        });
    
        // Initialize Advanced Galleriffic Gallery
        var gallery = $('#thumbs').galleriffic({
            delay:                     2500,
            numThumbs:                 10,
            preloadAhead:              10,
            enableTopPager:            true,
            enableBottomPager:         true,
            maxPagesToShow:            7,
            imageContainerSel:         '#slideshow',
            controlsContainerSel:      '#controls',
            captionContainerSel:       '#caption',
            loadingContainerSel:       '#loading',
            renderSSControls:          true,
            renderNavControls:         true,
            playLinkText:              'Play Slideshow',
            pauseLinkText:             'Pause Slideshow',
            prevLinkText:              '&lsaquo; Previous',
            nextLinkText:              'Next &rsaquo;',
            nextPageLinkText:          'Next &rsaquo;',
            prevPageLinkText:          '&lsaquo; Prev',
            enableHistory:             false,
            autoStart:                 false,
            syncTransitions:           true,
            defaultTransitionDuration: 900,
            enableKeyboardNavigation :false, 
            onSlideChange:             function(prevIndex, nextIndex) {
                // 'this' refers to the gallery, which is an extension of $('#thumbs')
                this.find('ul.thumbs').children()
                .eq(prevIndex).fadeTo('fast', onMouseOutOpacity).end()
                .eq(nextIndex).fadeTo('fast', 1.0);
					    
            },
            onPageTransitionOut:       function(callback) {
                this.fadeTo('fast', 0.0, callback);
            },
            onPageTransitionIn:        function() {
                this.fadeTo('fast', 1.0, function(){
                    var paramId = $(this).find("li.selected").find("img").attr("param_id");
                    Users.PublicProfile.AlbumsAndPhotos.showPhotosComment(paramId);
                });	
            }
        });
    }				
			
}			