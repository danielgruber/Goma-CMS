/**
 *@builder goma resources 1.2.5
 *@license to see license of the files, go to the specified path for the file 
*/

/* RAW */


(function($){$(function(){$("#boxes_new_4 .box_new").resizable({autoHide:true,handles:'e',minWidth:100,grid:[10,10],stop:function(event,ui){$.ajax({url:root_path+"boxes_new/4/saveBoxWidth/"+ui.element.attr("id").replace("box_new_",""),type:"post",data:{width:ui.element.width()},dataType:"html"});},resize:function(event,ui){ui.element.css('height','auto');}});$("#boxes_new_4").sortable({opacity:0.6,handle:'.adminhead',helper:'clone',placeholder:'placeholder',revert:true,tolerance:'pointer',start:function(event,ui){$(".placeholder").css({'width':ui.item.width(),'height':ui.item.height()});$(".placeholder").attr("class",ui.item.attr("class")+" placeholder");},update:function(event,ui){var data=$(this).sortable("serialize");$.ajax({url:root_path+"boxes_new/4/saveBoxOrder",data:data,type:"post",dataType:"html"});},distance:10,items:" > .box_new"});$("#boxes_new_4 > .box_new .adminhead").css("cursor","move");});})(jQuery);

