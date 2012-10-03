/* RAW */


(function($){$(function(){$("#form_form .err").remove();$("#form_form").bind("formsubmit",function(){$("#form_form .err").remove();});});$(function(){$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182").bind("formsubmit",function(){self.leave_check=true;});$(function(){$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182").submit(function(){var eventb=jQuery.Event("beforesubmit");$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182").trigger(eventb);if(eventb.result===false){return false;}
var event=jQuery.Event("formsubmit");$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182").trigger(event);if(event.result===false){return false;}});});$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182").find("select, input[type=text], input[type=hidden], input[type=radio], input[type=checkbox], input[type=password], textarea").change(function(){self.leave_check=false;});$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182 > .default_submit").click(function(){$("#form_3fcdb73d36d54f2cc22d0f68e6b6e182 > .actions  input[type=submit]").each(function(){if($(this).attr("name")!="cancel"&&!$(this).hasClass("cancel")){$(this).click();return false;}});return false;});});})(jQuery);

/* RAW */


$(function(){$(".userbar div form .logoutButton").hover(function(){$(this).attr("src","system/templates/admin/images/power_off_hover.png");},function(){$(this).attr("src","system/templates/admin/images/power_off.png");});var hide=function(){$(".userbar .langSelect").find(".langSelection").stop().slideUp(300);$(".userbar .langSelect").find(".langSelection").removeClass("visisble");$(".userbar .langSelect > a").removeClass("active");}
$(".userbar .langSelect > a").click(function(){if(!$(this).parent().find(".langSelection").hasClass("visisble")){$(this).parent().find(".langSelection").stop().slideUp(1).slideDown(100);$(this).parent().find(".langSelection").addClass("visisble");$(this).addClass("active");}else{hide();}
return false;});CallonDocumentClick(hide,[$(".userbar .langSelect > a"),$(".userbar .langSelect").find(".langSelection")]);});

