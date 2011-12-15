/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 28.05.2010
*/ 
function resize(file, width, height)
{
	$("#resize_image").remove();
	$("body").append('<div id="resize_image"></div>');
	$("#resize_image").html('<div id="resize_loading" style="display: none; text-align: center;"><img src="images/loading.gif" alt="Loading..." /></div>\
							<table id="resize_table" width="100%">\
								<tr>\
									<td>&nbsp;</td>\
									<td>\
										'+self.lang.width+'\
									</td>\
									<td>\
										'+self.lang.height+'\
									</td>\
								</tr>\
								<tr>\
									<td>\
										'+self.lang.original+'\
									</td>\
									<td>\
										'+width+'\
									</td>\
									<td>\
										'+height+'\
									</td>\
								</tr>\
								<tr>\
									<td>\
										'+self.lang._new+'\
									</td>\
									<td>\
										<input type="text" name="width" id="resized_width" />\
									</td>\
									<td>\
										<input type="text" name="height" id="resized_height" />\
									</td>\
								</tr>\
							</table>');
	var f = file;+
	gloader.load("uidialog");
	$("#resize_image").dialog({
		buttons: 
		{
			'Save': function()
			{
				if($("#resized_height").val() != "" && $("#resized_width").val() != "")
				{
					var s = this;
					$("#resize_loading").css("display", "block");
					ajaxrequest('filemanager_resize', {file: f,height: $("#resized_height").val(), "width": $("#resized_width").val()}, function(){
						$(s).dialog("close");
						$("#resize_loading").css("display", "none");
					});
				} else if($("#resized_width").val() == "")
				{
					$("#resized_width").focus();
				} else if($("#resized_height").val() == "")
				{
					$("#resized_height").focus();
				}
				
			},
			'Cancel' : function()
			{
				$(this).dialog("close");
			}
		},
		width: 600,
		modal: true,
		resizable: false,
		draggable: false
	});
}