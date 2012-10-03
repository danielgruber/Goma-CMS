/**
 *@builder goma resources 1.2.4
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


var welcome=["Herzlich Willkommen","welcome","benvenuto","welkom","velkommen","bienvenue","w&euml;llkom","f&agrave;ilte","ben&egrave;nnidu","tonga soa","haere mai","dobredojde","Sean bienvenidos"];welcome=array_shuffle(welcome);$(function(){var i=0;var a=0;var last;var beforeLast;$("#welcome_animation").css("display","block");var intro=function(init){if(i==welcome.length){i=0;welcome=array_shuffle(welcome);}else{i++;}
$("#welcome_animation").append('<span class="welcome_lang"></span>');$("#welcome_animation span:last").html(welcome[i]);$("#welcome_animation span:last").css({"left":$("#wrapper_logout .content").width(),"top":a*30+13});if(typeof init!="undefined"){$("#welcome_animation span:last").css({"left":Math.round(Math.random()*$("#wrapper_logout .content").width()/2)});var duration=10000;}else{var duration=14000;}
$("#welcome_animation span:last").animate({left:"-400"},duration,function(){$(this).remove();});a++;if(a==3){a=0;}}
intro(true);intro(true);intro(true);intro();intro();setInterval(intro,1200);});

