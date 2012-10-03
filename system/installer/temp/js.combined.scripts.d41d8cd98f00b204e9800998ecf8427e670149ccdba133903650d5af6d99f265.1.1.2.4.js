/**
 *@builder goma resources 1.2.4
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


$(function(){$("#wrapper_logout").fadeTo(0,0);$("body").append('<div id="introAnimation"><img src="http://dev.filmteam-kraus.de/system/templates/admin/images/logo.png" alt="Logo" /><p>Open Source CMS/Framework</p></div>');$("#introAnimation img").css({display:"block",position:"absolute",top:"50%",left:"50%","margin-left":"-96px","margin-top":"-30px"});$("#introAnimation img").css({top:$("#introAnimation img").offset().top,left:$("#introAnimation img").offset().left,"margin-left":"0","margin-top":0});$("#introAnimation p").fadeTo(0,0);setTimeout(function(){var textLeftPos=$("#introAnimation img").offset().left+(192/2)-$("#introAnimation p").outerWidth()/2;$("#introAnimation p").css({position:"absolute",top:$("#introAnimation img").offset().top+60+5,left:textLeftPos});$("#introAnimation p").fadeTo(500,1);},100);var logoTop=$("#logo").offset().top+(($("#logo").outerHeight()-$("#logo").height())/2);var logoLeft=$("#logo").offset().left+(($("#logo").outerWidth()-$("#logo").width())/2);setTimeout(function(){$("#introAnimation p").fadeTo(400,0);$("#introAnimation img").animate({top:logoTop,left:logoLeft},750,function(){$("#wrapper_logout").fadeTo(500,1,function(){$("#introAnimation img").remove();});});},2000);});

/* RAW */


var welcome=["Herzlich Willkommen","welcome","benvenuto","welkom","velkommen","bienvenue","w&euml;llkom","f&agrave;ilte","ben&egrave;nnidu","tonga soa","haere mai","dobredojde","Sean bienvenidos"];welcome=array_shuffle(welcome);$(function(){var i=0;var a=0;var last;var beforeLast;$("#welcome_animation").css("display","block");var intro=function(init){if(i==welcome.length){i=0;welcome=array_shuffle(welcome);}else{i++;}
$("#welcome_animation").append('<span class="welcome_lang"></span>');$("#welcome_animation span:last").html(welcome[i]);$("#welcome_animation span:last").css({"left":$("#wrapper_logout .content").width(),"top":a*30+13});if(typeof init!="undefined"){$("#welcome_animation span:last").css({"left":Math.round(Math.random()*$("#wrapper_logout .content").width()/2)});var duration=10000;}else{var duration=14000;}
$("#welcome_animation span:last").animate({left:"-400"},duration,function(){$(this).remove();});a++;if(a==3){a=0;}}
intro(true);intro(true);intro(true);intro();intro();setInterval(intro,1200);});

