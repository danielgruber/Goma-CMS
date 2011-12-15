/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 25.05.2010
*/ 
$(function(){
	$("#pub_actions .control").live('click', function(){
		$(this).parent().addClass("loading");
		var s = this;
		ajaxrequest($(this).attr("name"), {}, function(){
			$(s).parent().removeClass("loading");
		});
	});
});