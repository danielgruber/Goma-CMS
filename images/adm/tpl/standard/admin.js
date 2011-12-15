$(function(){
$("#nav a").next('.overview').css({display: 'none'}).fadeTo('fast',0.0);
$("#nav a").next('.overview').css({display: 'none'});

   $("#nav a").hover(function(){
   var pos = $(this).position();
   var posnav = $("#nav").position();
     $(this).next('.overview').css({display: 'block',zIndex: 998, position: 'absolute', left: pos.left, top: pos.top + 21}).fadeTo('fast',0.85);
   },function(){
     $(this).next('.overview').css({display: 'none'}).fadeTo('fast',0.0).css("display", "none");
   });


});