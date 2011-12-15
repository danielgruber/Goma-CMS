/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 04.11.2010
*/ 

$(function(){
	$(".directorylink").click( function(){
		loaddir($(this).attr("title"));
		return false;
	});
	register_filemanager_ajaxload(function(a){
		if(
			a == "init" || 
			a == "filemanager_files" || 
			a == "filemanager_upload" || 
			a == "filemanager_delete" || 
			a == "filemanager_rename" || 
			a == "filemanager_create_file" || 
			a == "filemanager_create_dir" ||
			a == "filemanager_copy"
			)
		{
			$("#dirs > ul").treeview({
				animated: "fast",
				persist: "cookie",
				cookieId: "filetree",
				collapsed: true
			});
			$(".directorylink").click( function(){
				loaddir($(this).attr("title"));
				return false;
			});
			
			$("#filesholder").css("height", $(window).height() - 400);
		}
	});
	
	register_filemanager_ajaxload(function(){
		$(".folder").removeClass("active");
		$(".directorylink[title="+self.current_dir+"]").parent().addClass("active");
		$(".directorylink[title="+self.current_dir+"/]").parent().addClass("active");
		if(self.current_file != "")
		{
			$(".file[title="+self.current_file+"]").addClass("active");
			$(".folder[title="+self.current_file+"]").addClass("active");
		}
	});
});

function loaddir(dir)
{
		var regexp = /^\/.*/;
		if(regexp.test(dir))
		{
				location.href = location.pathname + location.search + "#!" + dir;
				self.current_hash = "#!" + dir;
		} else
		{
				location.href = location.pathname + location.search + "#!/" + dir;
				self.current_hash = "#!/" + dir;
		}
		self.current_dir = dir.replace('//','/');
		var regexp = /^\//;
		if(regexp.test(self.current_dir))
		{
				self.current_dir = self.current_dir.substr(1);
		}
		$("#files").html('<div align="center"><img src="images/loading.gif" alt="loading..." /></div>');
		ajaxrequest('filemanager_files', {dir: dir}, function(){
			$("#file").css("display","none");
			self.current_file = "";
		});
}