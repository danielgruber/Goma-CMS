/**
  *@package rating-plugin
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 13.03.2012
*/


$(function(){
	$(".stars").each(function(){
		safestars($(this).attr("title"));
	});
	
	$(".star").hover(function(){
		var idenfifier = $(this).attr("id").substring(5, $(this).attr("id").length - 2);
		highlightstars($(this).attr("title"),idenfifier);
	}, function(){
		var idenfifier = $(this).attr("id").substring(5, $(this).attr("id").length - 2);
		restorestars(idenfifier);
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