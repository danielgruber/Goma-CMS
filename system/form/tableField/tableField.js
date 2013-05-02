/**
  * inspiration by Silverstripe 3.0 GridField
  * http://silverstripe.org
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 25.11.2012
  * $Version - 1.0
 */
(function($){
	$(function(){
		$(".tablefield-filter").keyup(function(event){
		    if(event.keyCode == 13){
		        $(this).parents("form").submit();
		    }
		});
	});
})(jQuery);