/**
  *@package goma css framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013 Goma-Team
  * last modified: 17.02.2013
*/

(function($){
	$(function(){
		$(".templateSwitcher .template").click(function(){
			$(".templateSwitcher .template.selected").removeClass("selected");
			$(this).addClass("selected");
			$(this).parent().parent().find(" > input").val($(this).attr("name"));
		});
	});
})(jQuery);