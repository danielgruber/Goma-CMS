/* File system/libs/javascript/loader.js */

self.JSLoadedResources["system/libs/javascript/loader.js"] = true;

if(typeof self.loader=="undefined"){self.loader=true;var json_regexp=/^\(?\{/;var html_regexp=new RegExp("<body");var external_regexp=/https?\:\/\/|ftp\:\/\//;var gloader={load:function(component,fn)
{if(gloader.loaded[component]==null)
{if(self.gloader_data[component]!=null)
{var i;if(self.gloader_data[component]["required"])
for(i in self.gloader_data[component]["required"])
{gloader.load(self.gloader_data[component]["required"][i]);}
$("body").css("cursor","wait");$.ajax({cache:true,noRequestTrack:true,url:self.gloader_data[component]["file"],dataType:"script",async:false});$("body").css("cursor","auto");if(fn!=null)
fn();}
gloader.loaded[component]=true;}},loaded:[]};array_shuffle=function(array){var tmp,rand;for(var i=0;i<array.length;i++){rand=Math.floor(Math.random()*array.length);tmp=array[i];array[i]=array[rand];array[rand]=tmp;}
return array;};(function($,w){$.fn.inlineOffset=function(){var el=$('<i/>').css('display','inline').insertBefore(this[0]);var pos=el.offset();el.remove();return pos;};$(function(){$("a[rel=ajaxfy]").live("click",function()
{var $this=$(this);var _html=$this.html();$this.html("<img src=\"images/16x16/ajax-loader.gif\" alt=\"loading...\" />");var $container=$this.parents(".record").attr("id");$.ajax({url:$this.attr("href"),data:{ajaxfy:true,"ajaxcontent":true,"container":$container},dataType:"html",success:function(html,code,ajaxreq){eval_script(html,ajaxreq);$this.html(_html);},error:function(ajaxreq){eval_script(ajaxreq.responseText,ajaxreq);$this.html(_html);}});return false;});$('a[rel*=orangebox]').live('click',function(){gloader.load("orangebox");$(this).orangebox();$(this).removeAttr("rel");$(this).click();});$("a[rel*=bluebox], a[rel*=facebox]").live('click',function(){gloader.load("dialog");if($(this).hasClass("nodrag"))
{new bluebox($(this).attr('href'),$(this).attr('title'),$(this).attr('name'),false);}else
{new bluebox($(this).attr('href'),$(this).attr('title'),$(this).attr('name'));}
return false;});$("a[rel*=dropdownDialog]").live("click",function()
{gloader.load("dropdownDialog");var options={uri:$(this).attr("href")};if($(this).attr("rel")=="dropdownDialog[left]")
options.position="left";else if($(this).attr("rel")=="dropdownDialog[center]")
options.position="center";else if($(this).attr("rel")=="dropdownDialog[right]")
options.position="right";else if($(this).attr("rel")=="dropdownDialog[bottom]")
options.position="bottom";$(this).dropdownDialog(options);return false;});$(".windowzindex").live('click',function(){$(".windowzindex").parent().css('z-index',900);$(this).parent().css("z-index",901);});$("input").each(function(){if(($(this).attr("type")=="text"||$(this).attr("type")=="search")&&($(this).val()==""||$(this).val()==$(this).attr("placeholder"))&&$(this).attr("placeholder")!=""){gloader.load("modernizr");if(!Modernizr.input.placeholder){$(this).val($(this).attr("placeholder"));$(this).css("color","#999");$(this).focus(function(){if($(this).val()==$(this).attr("placeholder")){$(this).val("");}
$(this).css("color","");});$(this).blur(function(){if($(this).val()==""){$(this).val($(this).attr("placeholder"));$(this).css("color","#999");}});}}});});var lang=[];w.lang=function(name,_default){if(typeof profiler!="undefined")profiler.mark("lang");if(typeof lang[name]=="undefined"){var jqXHR=$.ajax({async:false,cache:true,url:ROOT_PATH+BASE_SCRIPT+"system/getLang/"+escape(name),dataType:"json"});try{var data=eval('('+jqXHR.responseText+')');for(i in data){lang[i]=data[i];}}catch(e){lang[name]=null;}}
if(typeof profiler!="undefined")profiler.unmark("lang");if(lang[name]==null){return _default;}else{return lang[name];}}
w.getDocRoot=function(){if($(".documentRoot").length==1){return $(".documentRoot");}else{return $("body");}}
w.preloadLang=function(_names,async){if(typeof profiler!="undefined")profiler.mark("preloadLang");if(typeof async=="undefined")
async=false;var names=[];for(i in _names){if(typeof lang[_names[i]]=="undefined")
names.push(_names[i]);}
if(names.length==0)
return true;var jqXHR=$.ajax({async:false,cache:true,data:{"lang":names},url:ROOT_PATH+"system/getLang/",dataType:"json"});try{var data=eval('('+jqXHR.responseText+')');for(i in data){lang[i]=data[i];}}catch(e){}
if(typeof profiler!="undefined")profiler.unmark("preloadLang");}
w.eval_script=function(html,ajaxreq,object){LoadAjaxResources(ajaxreq);if(typeof profiler!="undefined")profiler.mark("eval_script");var content_type=ajaxreq.getResponseHeader("content-type");if(content_type=="text/javascript"){if(typeof object!="undefined"){var method=eval_global('(function(){'+html+'});');method.call(object);}else{eval_global(html);}}else if(content_type=="text/x-json"){var object=eval_global("("+html+")");var _class=object["class"];var i;for(i in object["areas"]){$("#"+_class+"_"+i+"").html(object["areas"][i]);}}else{gloader.load("orangebox");var id=randomString(5);if(html_regexp.test(html)){self[id+"_html"]=html;$("body").append('<div id="'+id+'_div" style="display: none;width: 800px;hieght: 300px;"><iframe src="javascript:document.write(top.'+id+'_html);" height="500" width="100%" name="'+id+'" frameborder="0" id="'+id+'"></iframe></div>');$("body").append('<a style="display: none;" href="#'+id+'_div" rel="orangebox" id="'+id+'_link"></a>');$("#"+id+"_link").click();}else{$("body").append('<div id="'+id+'_div" style="display: none;">'+html+'</div>');$("body").append('<a style="display: none;" href="#'+id+'_div" rel="orangebox" id="'+id+'_link"></a>');$("#"+id+"_link").click();}}
if(typeof profiler!="undefined")profiler.unmark("eval_script");RunAjaxResources(ajaxreq);}
w.renderResponseTo=function(html,node,ajaxreq,object){LoadAjaxResources(ajaxreq);if(typeof profiler!="undefined")profiler.mark("renderResponseTo");if(ajaxreq!=null){var content_type=ajaxreq.getResponseHeader("content-type");if(content_type=="text/javascript"){if(typeof object!="undefined"){var method=eval_global('(function(){'+html+'});');method.call(object);}else{eval_global(html);}
return true;}else if(content_type=="text/x-json"&&json_regexp.test(html)){var object=eval_global("("+html+")");var _class=object["class"];var i;for(i in object["areas"]){$("#"+_class+"_"+i+"").html(object["areas"][i]);}}}
var regexp=new RegExp("<body");if(regexp.test(html)){var id=randomString(5);top[id+"_html"]=html;node.html('<iframe src="javascript:document.write(top.'+id+'_html);" height="500" width="100%" name="'+id+'" frameborder="0"></iframe>');}else{node.html(html);}
if(typeof profiler!="undefined")profiler.unmark("renderResponseTo");RunAjaxResources(ajaxreq);}
w.ajax_submit=function(obj)
{var $this=$(obj);$form=$this.parents("form");var data=$form.serialize();var url=$form.attr("action");var method=$form.attr("method");$this.before('<img src="images/16x16/loading.gif" class="loader" alt="loading..." />');$.ajax({url:url,type:method,data:data,dataType:"script",complete:function()
{$form.find(".loader").remove();}});return false;}
if(typeof w.JSLoadedResources=="undefined")
w.JSLoadedResources=[];if(typeof w.CSSLoadedResources=="undefined")
w.CSSLoadedResources=[];if(typeof w.CSSIncludedResources=="undefined")
w.CSSIncludedResources=[];w.LoadAjaxResources=function(request){var css=request.getResponseHeader("X-CSS-Load");var js=request.getResponseHeader("X-JavaScript-Load");if(css!=null){var cssfiles=css.split(";");var i;for(i in cssfiles){var file=cssfiles[i];if(!external_regexp.test(file)){if(typeof w.CSSLoadedResources[file]=="undefined"){$.ajax({cache:true,url:file,noRequestTrack:true,async:false,dataType:"html",success:function(css){var base=file.substring(0,file.lastIndexOf("/"));css=css.replace(/url\(("|')?(.*)("|')?\)/gi,'url('+root_path+base+'/$2)');w.CSSLoadedResources[file]=css;}});}
if(typeof w.CSSIncludedResources[file]=="undefined"){$("head").prepend('<style type="text/css" id="css_'+file.replace(/[^a-zA-Z0-9_\-]/,"_")+'">'+CSSLoadedResources[file]+'</style>');w.CSSIncludedResources[file]=true;}}else{w.CSSLoadedResources[file]=css;if($("head").html().indexOf(file)!=-1){$("head").prepend('<link rel="stylesheet" href="'+file+'" type="text/css" />');}}}}
if(js!=null){var jsfiles=js.split(";");var i;for(i in jsfiles){var file=jsfiles[i];if(file!=""){var regexp=/\/[^\/]*(script|raw)[^\/]+\.js/;var alwaysLoad=/\/[^\/]*(data)[^\/]+\.js/;if((!regexp.test(file)&&w.JSLoadedResources[file]!==true)||alwaysLoad.test(file)){w.JSLoadedResources[file]=true;$.ajax({cache:true,url:file,noRequestTrack:true,async:false,dataType:"script"});}
regexp=null;}}}}
w.RunAjaxResources=function(request){var js=request.getResponseHeader("X-JavaScript-Load");if(js!=null){var jsfiles=js.split(";");var i;for(i in jsfiles){var file=jsfiles[i];if(file!=""){var regexp=/\/[^\/]*(script|raw)[^\/]+\.js/;if(regexp.test(file)){$.ajax({cache:true,url:file,noRequestTrack:true,async:false,dataType:"html",success:function(js){eval_global(js);}});}
regexp=null;}}}}
w.unbindFromFormSubmit=function(node){var active=false;$(node).focus(function(){active=true;});$(node).blur(function(){active=false;});$(node).parents("form").bind("formsubmit",function(){if(active)
return false;});$(node).parents("form").bind("submit",function(){if(active)
return false;});$(node).keydown(function(e){if(e.keyCode==13){return false;}});}
w.CallonDocumentClick=function(call,exceptions){var fn=call;var mouseover=false;var timeout;var i;if(exceptions){var i;for(i in exceptions){$(exceptions[i]).mousedown(function(){clearTimeout(timeout);mouseover=true;timeout=setTimeout(function(){mouseover=false;},300);});$(exceptions[i]).mouseup(function(){clearTimeout(timeout);mouseover=true;timeout=setTimeout(function(){mouseover=false;},300);});}}
$(document).mouseup(function(){setTimeout(function(){if(mouseover===false){fn();}},10);});$("iframe").each(function(){var w=$(this).get(0).contentWindow;if(w)
$(w).mouseup(function(){setTimeout(function(){if(mouseover===false){fn();}},10);})});}
w.callOnDocumentClick=w.CallonDocumentClick;$.fn.extend
({removeCSS:function(cssName){return this.each(function(){return $(this).attr('style',$.grep($(this).attr('style').split(";"),function(curCssName){if(curCssName.toUpperCase().indexOf(cssName.toUpperCase()+':')<=0)
return curCssName;}).join(";"));});}});w.request_history=[];$.ajaxPrefilter(function(options,originalOptions,jqXHR){if(originalOptions.noRequestTrack==null){var data=originalOptions;jqXHR.always(function(){w.request_history.push(data);});}
jqXHR.setRequestHeader("X-Referer",location.href);});})(jQuery,window);String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g,"");}
String.prototype.ltrim=function(){return this.replace(/^\s+/,"");}
String.prototype.rtrim=function(){return this.replace(/\s+$/,"");}
function randomString(string_length){var chars="0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";var randomstring='';for(var i=0;i<string_length;i++){var rnum=Math.floor(Math.random()*chars.length);randomstring+=chars.substring(rnum,rnum+1);}
return randomstring;}
function is_string(input){return(typeof(input)=='string');}
function getLastRequest(){return self.request_history[self.request_history.length-1];}
function getPreRequest(i){return self.request_history[self.request_history.length-1-parseInt(i)];}
function getInternetExplorerVersion()
{var rv=-1;if(navigator.appName=='Microsoft Internet Explorer')
{var ua=navigator.userAgent;var re=new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");if(re.exec(ua)!=null)
rv=parseFloat(RegExp.$1);}
return rv;}
function getFirefoxVersion()
{var rv=-1;var ua=navigator.userAgent;var regexp_firefox=/Firefox/i;if(regexp_firefox.test(ua)){var re=new RegExp("Firefox/([0-9]{1,}[\.0-9]{0,})");if(re.exec(ua)!=null)
rv=parseFloat(RegExp.$1);}
return rv;}
function setCookie(c_name,value,exdays)
{var exdate=new Date();exdate.setDate(exdate.getDate()+exdays);var c_value=escape(value)+((exdays==null)?"":"; expires="+exdate.toUTCString())+"; path=/";document.cookie=c_name+"="+c_value;}
function getCookie(c_name)
{var i,x,y,ARRcookies=document.cookie.split(";");for(i=0;i<ARRcookies.length;i++)
{x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);x=x.replace(/^\s+|\s+$/g,"");if(x==c_name)
{return unescape(y);}}}
function isIDevice(){return/(iPad|iPhone|iPod)/.test(navigator.userAgent);}
function isiOS5(){return isIDevice()&&navigator.userAgent.match(/AppleWebKit\/(\d*)/)[1]>=534;}
function isJSON(content){return json_regexp.test(content);}
var code_evaled;function eval_global(codetoeval){if(window.execScript)
window.execScript('code_evaled = '+'('+codetoeval+')','');else
code_evaled=eval(codetoeval);return code_evaled;}
function microtime(get_as_float){var now=new Date().getTime()/1000;var s=parseInt(now,10);return(get_as_float)?now:(Math.round((now-s)*1000)/1000)+' '+s;}
function str_repeat(input,multiplier){return new Array(multiplier+1).join(input);}}

