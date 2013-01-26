	var TinyMce = {
		ready: function(){
			tinyMCE.init({
				mode: "textareas",
				theme: "simple",
				theme_advanced_path: false,
				plugins: "maxchars",
				theme_advanced_buttons1: "bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright, justifyfull,bullist,numlist,undo,redo,link,unlink,image,media,anchor,formatselect",
				theme_advanced_buttons2: "",
				theme_advanced_buttons3: "",
				theme_advanced_toolbar_location: "top",
				theme_advanced_toolbar_align: "left",
				extended_valid_elements: "",
				/* update validation status on change */
				onchange_callback: function(editor){
					tinyMCE.triggerSave();
					$("#" + editor.id).valid();
				}
			});
		}
	};
	TinyMce.ready();
