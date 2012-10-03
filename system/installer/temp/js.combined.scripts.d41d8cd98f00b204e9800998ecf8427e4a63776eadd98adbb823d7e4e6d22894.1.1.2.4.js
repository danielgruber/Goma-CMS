/**
 *@builder goma resources 1.2.4
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


(function($){$(function(){$("#form_selectRestore .err").remove();$("#form_selectRestore").bind("formsubmit",function(){$("#form_selectRestore .err").remove();});});$(function(){$("#form_103fdcc3686b97093fcda09649c4153f").bind("formsubmit",function(){self.leave_check=true;});$(function(){$("#form_103fdcc3686b97093fcda09649c4153f").submit(function(){var eventb=jQuery.Event("beforesubmit");$("#form_103fdcc3686b97093fcda09649c4153f").trigger(eventb);if(eventb.result===false){return false;}
var event=jQuery.Event("formsubmit");$("#form_103fdcc3686b97093fcda09649c4153f").trigger(event);if(event.result===false){return false;}});});$("#form_103fdcc3686b97093fcda09649c4153f").find("select, input[type=text], input[type=hidden], input[type=radio], input[type=checkbox], input[type=password], textarea").change(function(){self.leave_check=false;});$("#form_103fdcc3686b97093fcda09649c4153f > .default_submit").click(function(){$("#form_103fdcc3686b97093fcda09649c4153f > .actions  input[type=submit]").each(function(){if($(this).attr("name")!="cancel"&&!$(this).hasClass("cancel")){$(this).click();return false;}});return false;});});})(jQuery);

