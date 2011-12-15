/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 10.06.2011
*/


$(function(){
	$(".stars").each(function(){
		safestars($(this).attr('title'));
	});
	
	$(".star").hover(function(){
		highlightstars($(this).attr("title"),$(this).attr("field"));
	}, function(){
		restorestars($(this).attr("field").substring(5, -2));
	});
});

function highlightstars(element,title)
{
	element = parseInt(element);
	for(i = 1;i < 6;i++)
	{
		if(i < element + 1)
		{
			var id = "star_"+title+"_"+i;
			$("#"+id).find("img").attr("src","images/star_yellow.png");
		} else
		{
			var id = "star_"+title+"_"+i;
			$("#"+id).find("img").attr("src","images/star_grey.png");
		}
	}
}


var rating_src = [];
function safestars(title)
{   
	self.rating_src[title] = [];
	for(i = 1;i < 6;i++)
	{
		var id = "star_"+title+"_"+i;
		self.rating_src[title][i] = $("#"+id).find("img").attr("src");
	}
}

function restorestars(title){
	for(i = 1;i < 6;i++)
	{
		var id = "star_"+title+"_"+i;
		$("#"+id).find("img").attr("src",self.rating_src[title][i]);
	}
}