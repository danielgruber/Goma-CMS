/**
  * extends the CKEditor-Link-Dialog with a new Link-Type
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 09.10.2011
  * $Version 2.0.0 - 002
*/
$(function(){
	CKEDITOR.on( 'dialogDefinition', function( ev )
	{
		// Take the dialog name and its definition from the event data.
		var dialogName = ev.data.name;
		var dialogDefinition = ev.data.definition;
		var editor = ev.editor;
		var dialog = ev.data;
 		
 		var url = "";
 		
		// Check if the definition is from the dialog we're
		// interested on (the Link dialog).
		if ( dialogName == 'link' ) {
		
			// rebuild event for change
			
			var linkTypeField = dialogDefinition.getContents("info").get("linkType");
			var oldevent = linkTypeField.onChange;
			linkTypeField.items.push([self.lang_page, 'page']);
			dialogDefinition.getContents("info").add({
				id: 	"pageOptions",
				type : 'hbox',
				children :
				[
					{
						type : 'text',
						id : 'pagename',
						commit: function(data) {
							if ( !data.url )
								data.url = {};
							var dialog = this.getDialog();
							if(data.type == "page") {
								data.type = "url";
								dialog.setValueOf("info", "protocol", "");
								dialog.setValueOf("info", "url", url);
							}
						},
						onLoad: function() {
							var timeout;
							var ajax;
							var $edit = this;
							$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').css('position', 'relative');
							$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').append('<a href="javascript:;" class="cancelButton"></a>');
							$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').append('<div class="textDropDown"><ul></ul></div>');
							$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(" > .textDropDown").css({
								left: 0,
								top: $("#" + $edit.getInputElement().getId() ).outerHeight() - 1,
								width: $("#" + $edit.getInputElement().getId() ).outerWidth() - ($("#" + $edit.getInputElement().getId() ).outerWidth() - $("#" + $edit.getInputElement().getId() ).width()) / 2,
								display: "none"
							});
							$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(".cancelButton").click(function(){
								url = "";
								$("#" + $edit.getInputElement().getId() ).prop("disabled", false);
								$("#" + $edit.getInputElement().getId() ).val("");
								$("#" + $edit.getInputElement().getId() ).focus();
								$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(" > .textDropDown").css("display", "block");
								$("#" + $edit.getInputElement().getId() ).keydown();
							});
							
							if($("#" + $edit.getInputElement().getId() ).val() == "") {
								$("#" + $edit.getInputElement().getId() ).prop("disabled", false);
							}
							
							$("#" + this.getInputElement().getId() ).keydown(function(){
								if($("#" + $edit.getInputElement().getId() ).val() == "") {
									$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(".cancelButton").css("display", "none");
								} else {
									$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(".cancelButton").css("display", "block");
								}
								clearTimeout(timeout);
								var $this = $(this);
								timeout = setTimeout(function(){
									$.ajax({
										url: "api/pagelinks/search",
										type: "post",
										data: {search: $("#" + $edit.getInputElement().getId() ).val()},
										dataType: "html",
										success: function(data) {
											var data = eval_global('(' + data + ')');
											if(data.count > 0) {
												var ul = $("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(" > .textDropDown > ul");
												ul.html("");
												var i;
												for(i in data.nodes) {
													var record = data.nodes[i];
													ul.append('<li><a href="'+record.url+'" class="pagenode_'+record.id+'">'+record.title+'</a></li>');
													$('.pagenode_'+record.id).click(function(){
														url = $(this).attr("href");
														$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(" > .textDropDown").css("display", "none");
														$("#" + $edit.getInputElement().getId() ).val($(this).text());
														$("#" + $edit.getInputElement().getId() ).prop("disabled", "disabled");
														$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(".cancelButton").css("display", "block");
														return false;
													});
												}
												$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(" > .textDropDown").css("display", "block");
											} else {
												$("#" + $edit.getInputElement().getId() ).parents('.cke_dialog_ui_text').find(" > .textDropDown").css("display", "none");
											}
											
										}
									});
								}, 300);
							});
							
							$("#" + this.getInputElement().getId() ).keydown();
						}
					}
				]
			});
			
			var content = dialogDefinition.contents[0].elements[0];
			
			content.onChange = CKEDITOR.tools.override(content.onChange, function(original) {
				return function() {
					
					original.call(this);
					var dialog = this.getDialog();
					var element = dialog.getContentElement( 'info',"pageOptions" ).getElement().getParent().getParent();
					if(this.getValue() == "page") {
						element.show();
					 	if (editor.config.linkShowTargetTab) {
              		 	 	dialog.showPage('target');
             		 	}
             		 	dialog.hidePage( 'advanced' );
					} else {
              			element.hide();
              			if(editor.config.linkShowAdvancedTab)
              				dialog.showPage( 'advanced' );
            		}
					
					
					
					dialog.layout();
				}
			});	
			
			
			
		}
	});
});