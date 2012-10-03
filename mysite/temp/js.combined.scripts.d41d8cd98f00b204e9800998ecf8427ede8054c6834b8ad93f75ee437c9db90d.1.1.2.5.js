/**
 *@builder goma resources 1.2.5
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


(function($){$(function(){$("#form_mailer .err").remove();$("#form_mailer").bind("formsubmit",function(){$("#form_mailer .err").remove();});});$(function(){$("#form_99bb411b44b7a1084ac25aaf1169fb8e").bind("ajaxresponded",function(){$("#form_field_captcha_052e98be45df0269909c4391222647dc_captcha_captcha").attr("src","images/captcha/captcha.php?"+Math.random());$("#form_field_captcha_052e98be45df0269909c4391222647dc_captcha").val("");});});$(function(){$("#form_99bb411b44b7a1084ac25aaf1169fb8e").bind("formsubmit",function(){self.leave_check=true;});$(function(){$("#form_99bb411b44b7a1084ac25aaf1169fb8e").submit(function(){var eventb=jQuery.Event("beforesubmit");$("#form_99bb411b44b7a1084ac25aaf1169fb8e").trigger(eventb);if(eventb.result===false){return false;}
var event=jQuery.Event("formsubmit");$("#form_99bb411b44b7a1084ac25aaf1169fb8e").trigger(event);if(event.result===false){return false;}});});$("#form_99bb411b44b7a1084ac25aaf1169fb8e").find("select, input[type=text], input[type=hidden], input[type=radio], input[type=checkbox], input[type=password], textarea").change(function(){self.leave_check=false;});$("#form_99bb411b44b7a1084ac25aaf1169fb8e > .default_submit").click(function(){$("#form_99bb411b44b7a1084ac25aaf1169fb8e > .actions  input[type=submit]").each(function(){if($(this).attr("name")!="cancel"&&!$(this).hasClass("cancel")){$(this).click();return false;}});return false;});});$(function(){if($("#form_mailer").length>0)
{$("#form_mailer").bind("formsubmit",function()
{var require_lang="<div class=\"err\" style=\"color: #ff0000;\">Dieses Feld ist obligatorisch!</div>";var valid=true;var v_name=function(){}
if(v_name()===false){valid=false;}if($("#form_field_textfield_c4e53aa08a0470315c353f8d9919b122_name").length>0)
{if($("#form_field_textfield_c4e53aa08a0470315c353f8d9919b122_name").length>0)
{if($("#form_field_textfield_c4e53aa08a0470315c353f8d9919b122_name").val()=="")
{$("#form_field_textfield_c4e53aa08a0470315c353f8d9919b122_name").parent().append(require_lang);valid=false;}}}
var v_text=function(){}
if(v_text()===false){valid=false;}if($("#form_field_textarea_6439ce78b8dc5450af21107b153501a2_text").length>0)
{if($("#form_field_textarea_6439ce78b8dc5450af21107b153501a2_text").length>0)
{if($("#form_field_textarea_6439ce78b8dc5450af21107b153501a2_text").val()=="")
{$("#form_field_textarea_6439ce78b8dc5450af21107b153501a2_text").parent().append(require_lang);valid=false;}}}
var v_email=function(){var regexp=/^([a-zA-Z0-9\-\._]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9]{2,9})$/;if(regexp.test($("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email").val()))
{}else
{$("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email_div").find(".err").remove();$("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email").after("<div class=\"err\" style=\"color: #ff0000;\">Bitte geben Sie eine g&uuml;ltige Email-Adresse in das Feld ein</div>");return false;}}
if(v_email()===false){valid=false;}if($("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email").length>0)
{if($("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email").length>0)
{if($("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email").val()=="")
{$("#form_field_email_f7e084fcf43ca58a1b1c64b61327fecb_email").parent().append(require_lang);valid=false;}}}
if(valid==false)
return false;});}});})(jQuery);

