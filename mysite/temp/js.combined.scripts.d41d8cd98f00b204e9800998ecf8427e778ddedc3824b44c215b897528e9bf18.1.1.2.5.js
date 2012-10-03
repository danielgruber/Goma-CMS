/**
 *@builder goma resources 1.2.5
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


$(function(){var _9f19dc58bdcb1db842a88f6bb0a4f503=new DropDown('form_field_multiselectdropdown_7b38365c24b7ee13ac3e51603a8f363c_excludeFolders','/system/forms/newsettings_72_1/excludeFolders',true);});

/* RAW */


(function($){$(function(){$("#form_newsettings_72_1 .err").remove();$("#form_newsettings_72_1").bind("formsubmit",function(){$("#form_newsettings_72_1 .err").remove();});});$(function(){$("#form_field_tabset_643a8eb5d896b7059270be6c7919140d_tabs_div").gtabs({"animation":true,"cookiename":"tabs_tabs"});});$(function(){var obj=$("#form_field_checkbox_07352d439af7aebc0fa77f3c32988f83_register_enabled").iphoneStyle();interval=setInterval(function(){if($("#form_field_checkbox_07352d439af7aebc0fa77f3c32988f83_register_enabled").length>0){$("#form_field_checkbox_07352d439af7aebc0fa77f3c32988f83_register_enabled").iphoneStyle("initialPosition");}else{clearInterval(interval);}},500);$("#form_field_checkbox_07352d439af7aebc0fa77f3c32988f83_register_enabled_div .iPhoneCheckContainer").css("float","right");});$(function(){var obj=$("#form_field_checkbox_e9868c43f21106086a1defd2f1d65967_register_email").iphoneStyle();interval=setInterval(function(){if($("#form_field_checkbox_e9868c43f21106086a1defd2f1d65967_register_email").length>0){$("#form_field_checkbox_e9868c43f21106086a1defd2f1d65967_register_email").iphoneStyle("initialPosition");}else{clearInterval(interval);}},500);$("#form_field_checkbox_e9868c43f21106086a1defd2f1d65967_register_email_div .iPhoneCheckContainer").css("float","right");});$(function(){var obj=$("#form_field_checkbox_b925435d49b5b5abf945128112ea2b2e_gzip").iphoneStyle();interval=setInterval(function(){if($("#form_field_checkbox_b925435d49b5b5abf945128112ea2b2e_gzip").length>0){$("#form_field_checkbox_b925435d49b5b5abf945128112ea2b2e_gzip").iphoneStyle("initialPosition");}else{clearInterval(interval);}},500);$("#form_field_checkbox_b925435d49b5b5abf945128112ea2b2e_gzip_div .iPhoneCheckContainer").css("float","right");});$(function(){$("#form_643a8eb5d896b7059270be6c7919140d").bind("formsubmit",function(){self.leave_check=true;});$(function(){$("#form_643a8eb5d896b7059270be6c7919140d").submit(function(){var eventb=jQuery.Event("beforesubmit");$("#form_643a8eb5d896b7059270be6c7919140d").trigger(eventb);if(eventb.result===false){return false;}
var event=jQuery.Event("formsubmit");$("#form_643a8eb5d896b7059270be6c7919140d").trigger(event);if(event.result===false){return false;}});});$("#form_643a8eb5d896b7059270be6c7919140d").find("select, input[type=text], input[type=hidden], input[type=radio], input[type=checkbox], input[type=password], textarea").change(function(){self.leave_check=false;});$("#form_643a8eb5d896b7059270be6c7919140d > .default_submit").click(function(){$("#form_643a8eb5d896b7059270be6c7919140d > .actions  input[type=submit]").each(function(){if($(this).attr("name")!="cancel"&&!$(this).hasClass("cancel")){$(this).click();return false;}});return false;});});})(jQuery);

/* RAW */


$(function(){$(".userbar div form .logoutButton").hover(function(){$(this).attr("src","system/templates/admin/images/power_off_hover.png");},function(){$(this).attr("src","system/templates/admin/images/power_off.png");});var hide=function(){$(".userbar .langSelect").find(".langSelection").stop().slideUp(300);$(".userbar .langSelect").find(".langSelection").removeClass("visisble");$(".userbar .langSelect > a").removeClass("active");}
$(".userbar .langSelect > a").click(function(){if(!$(this).parent().find(".langSelection").hasClass("visisble")){$(this).parent().find(".langSelection").stop().slideUp(1).slideDown(100);$(this).parent().find(".langSelection").addClass("visisble");$(this).addClass("active");}else{hide();}
return false;});CallonDocumentClick(hide,[$(".userbar .langSelect > a"),$(".userbar .langSelect").find(".langSelection")]);});

