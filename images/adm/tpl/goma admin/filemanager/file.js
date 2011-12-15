/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 15.05.2010
*/ 
var current_file = "";
$(function(){
	$("#files tr[title]").live('click',function(){
		$("#file").slideUp("fast");
		var name = $(this).attr("title");
		var dir = self.current_dir;
		self.current_file = name;
		$("#files .active").removeClass("active");
		$(".file[title=\""+self.current_file+"\"]").addClass("active");
		$(".folder[title=\""+self.current_file+"\"]").addClass("active");
		ajaxrequest('filemanager_file', {file: name, dir: dir}, function(){
			$("#file").slideDown("slow");
		});
	});
	$("#file .control").live('click', function(){
		$(this).parent().addClass("loading");
		var s = this;
		ajaxrequest($(this).attr("name"), {dir: self.current_dir, file: self.current_file}, function(){
			$(s).parent().removeClass("loading");
		});
	});
});