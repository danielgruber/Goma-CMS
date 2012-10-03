/**
 *@builder goma resources 1.2.5
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


$(function(){$(".userbar div form .logoutButton").hover(function(){$(this).attr("src","system/templates/admin/images/power_off_hover.png");},function(){$(this).attr("src","system/templates/admin/images/power_off.png");});var hide=function(){$(".userbar .langSelect").find(".langSelection").stop().slideUp(300);$(".userbar .langSelect").find(".langSelection").removeClass("visisble");$(".userbar .langSelect > a").removeClass("active");}
$(".userbar .langSelect > a").click(function(){if(!$(this).parent().find(".langSelection").hasClass("visisble")){$(this).parent().find(".langSelection").stop().slideUp(1).slideDown(100);$(this).parent().find(".langSelection").addClass("visisble");$(this).addClass("active");}else{hide();}
return false;});CallonDocumentClick(hide,[$(".userbar .langSelect > a"),$(".userbar .langSelect").find(".langSelection")]);});

