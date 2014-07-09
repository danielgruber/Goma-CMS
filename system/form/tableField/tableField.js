/**
 * TableField JavaScript, which handles some basic User-Actions.
 *
 * @package     Goma\Form-Framework\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
(function($){
	$(function(){
		$(".tablefield-filter").keyup(function(event){
		    if(event.keyCode == 13){
		        $(this).parents("form").submit();
		    }
		});
		
		$(".tablefield-item td").each(function(){
			if(!$(this).hasClass("col-checkboxes") && !$(this).hasClass("col-buttons")) {
				$(this).on("click touchend", function(){
					if($(this).parent().find(".col-checkboxes").length == 1) {
						if($(this).parent().find(".col-checkboxes input").prop("checked")) {
							$(this).parent().find(".col-checkboxes input").prop("checked", false);
						} else {
							$(this).parent().find(".col-checkboxes input").prop("checked", true);
						}
						$(this).parent().find(".col-checkboxes input").change();
					}
				});
			} else {
				$(this).find("input").change(function(){
					if($(this).prop("checked")) {
						$(this).parent().parent().addClass("checked");
					} else {
						$(this).parent().parent().removeClass("checked");
					}
				});
			}
		});
	});
})(jQuery);