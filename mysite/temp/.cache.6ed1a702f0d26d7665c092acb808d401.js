/* File system/templates/admin/admin.js */

self.JSLoadedResources["system/templates/admin/admin.js"] = true;

$(document).ready(function(){$("#content > .header > .userbar-js").addClass("userbar");var update=function(){var headerWidth=$("#content > .header").width();var userbarWidth=$("#content > .header > .userbar").outerWidth();var naviWidth=$("#navi").outerWidth();var naviWidthMax=headerWidth-userbarWidth-25;var curNode;var active=$("#navi > ul > li.active").index()+1;if(naviWidth<=naviWidthMax)
{if($("#navMore-sub li").length>0)
{curNode=$("#navi li.nav-inactive").first();if(naviWidthMax-naviWidth>curNode.outerWidth(true))
{$("#navMore-sub li").first().remove();curNode.removeClass("nav-inactive");}}
else
{$("#navMore").css("display","none");}}
else
{while($("#navi").outerWidth()>naviWidthMax)
{curNode=$("#navi").find(" > ul > li").not($("#navMore")).not($("#navi li.active")).not($("#navi li.nav-inactive")).last();curNode.clone().prependTo("#navMore-sub");curNode.addClass("nav-inactive");$("#navMore").css("display","block");}}}
$("#navMore > a").click(function(){if($(this).parent().hasClass("open")){$("#navMore-sub").slideUp("fast");$(this).parent().removeClass("open");}else{$("#navMore-sub").slideDown("fast");$(this).parent().addClass("open");}
return false;});CallonDocumentClick(function(){$("#navMore-sub").slideUp("fast");$("#navMore").removeClass("open");},[$("#navMore")]);$(window).resize(update);$("#wrapper").bind("resize",update);update();});

