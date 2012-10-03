/**
 *@builder goma resources 1.2.5
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


(function($){$(function(){$("#form_request .err").remove();$("#form_request").bind("formsubmit",function(){$("#form_request .err").remove();});});$(function(){$("#form_10573b873d2fa5a365d558a45e328e47").bind("formsubmit",function(){self.leave_check=true;});$(function(){$("#form_10573b873d2fa5a365d558a45e328e47").submit(function(){var eventb=jQuery.Event("beforesubmit");$("#form_10573b873d2fa5a365d558a45e328e47").trigger(eventb);if(eventb.result===false){return false;}
var event=jQuery.Event("formsubmit");$("#form_10573b873d2fa5a365d558a45e328e47").trigger(event);if(event.result===false){return false;}});});$("#form_10573b873d2fa5a365d558a45e328e47").find("select, input[type=text], input[type=hidden], input[type=radio], input[type=checkbox], input[type=password], textarea").change(function(){self.leave_check=false;});$("#form_10573b873d2fa5a365d558a45e328e47 > .default_submit").click(function(){$("#form_10573b873d2fa5a365d558a45e328e47 > .actions  input[type=submit]").each(function(){if($(this).attr("name")!="cancel"&&!$(this).hasClass("cancel")){$(this).click();return false;}});return false;});});})(jQuery);

