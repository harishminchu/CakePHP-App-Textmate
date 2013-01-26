var validateHelper = {
		model:null,
		formClass:null,
		successMessage:null,
		target:".ajaxTarget",
		type:2,
		ready: function(formClass, model, jsonObject, successMessage) {
			this.model = model;
			this.successMessage = "<div class='message'>" + successMessage + "</div>";
			this.formClass = $("." + formClass);
			
			$(jsonObject).each( function(i, object) {
				$.each(object, function(property, innerOBject) {
       				var element = validateHelper.setFormElementId(model, property);
					$.each(innerOBject, function(rule, objectMessage) {
       				 	var modelRule = rule;
						var title = objectMessage.message;
						if(validateHelper.type == 2){
							var message = " ";
						}else {
							var message = objectMessage.message;
						}
						
						var validationClass = validateHelper.interpretRule(modelRule);
						$(element).addClass(validationClass).attr("title", title);
						
				 	});	 
				 });
			});
			
			$.validator.setDefaults({
				submitHandler: function(form) {
					jQuery(form).ajaxSubmit({
						target: $(form).find(validateHelper.target),
						beforeSubmit:showResponse,
						success:showRequest
					});
				}});
			
			function showResponse(formData, jqForm, options ) {
						
			}
			function showRequest(responseText, statusText, xhr, $form ) {
				var message = App.Notification.showSuccess($form);
				var parsedJson = jQuery.parseJSON(responseText);
				if (typeof(parsedJson.redirect) != "undefined") {
					if (parsedJson.redirect.length) {
						window.location.href = parsedJson.redirect;
					}
				}	
			}
			
			 var valids = {
			 	   invalidHandler: function(form, validator) {
						App.Notification.showError(validateHelper.formClass);
		   		   }
			 }    
			$(this.formClass).validate(valids);
			validateHelper.initPoshyTip();
		},
		setFormElementId: function(model, field) {
			var name = "[name='data[" + model + "][" + field +"]']";
			return $(name).attr("id", field);
		},
		interpretRule: function(modelRule){
			switch(modelRule) {
				case "blank":
				case "notempty":
				    return "required";
					break;
				case "rating":
				    return "rating";
					break;	
				case "cc":
				    return "creditcard";
					break;
				case "email":
				    return "email";
					break;
				case "equalTo":
				    return "equalTo";
					break;	
				case "minLength":
				    return "minlength";
					break;				
				default: 
					return null;
			}
		},
	    initPoshyTip: function(){
	     	$('.required').poshytip({
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
			
};	
	
