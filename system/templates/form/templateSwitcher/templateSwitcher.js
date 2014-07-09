/**
  *@package goma css framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
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