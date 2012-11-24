/**
  * inspiration by Silverstripe 3.0 GridField
  * http://silverstripe.org
  *
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
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