/**
 * bluebox
 *@author Daniel Gruber
 *@name bluebox
 *@package goma
 *@subpackage javascript
 *@requires draggable
*/


/* bluebox */
var boxcount = 0;
var blueboxes = [];

function getblueboxbyid(id)
{
		return self.blueboxes[id];
}

function bluebox(url, title, _class, drag)
{
		this.url = url;
		this.title = title;
		self.boxcount++;
		this.id = self.boxcount;
		if(_class == null)
		{
				var addclass = "";
		} else
		{
				var addclass = " " + _class;
		}
		getDocRoot().append('<div id="bluebox_'+self.boxcount+'" class="bluebox bluebox_wrapper '+addclass+'">\
							<table class="bluebox_container windowzindex" cellspacing="0" cellpadding="0">\
								<tr>\
									<td class="con_shadow" style="width: 16px;height: 16px;background-image: url(system/templates/css/images/tl.png);background-repeat: no-repeat;"></td>\
									<td class="con_shadow bluebox_border" style="height: 16px;background-repeat: repeat-x;"></td>\
									<td class="con_shadow" style="width: 16px;height: 16px;background-image: url(system/templates/css/images/tr.png);background-repeat: no-repeat;"></td>\
								</tr>\
								<tr>\
									<td class="con_shadow bluebox_border" style="width: 16px;background-repeat: repeat-y;"></td>\
									<td class="bluebox_inner">\
										<div align="center" class="bluebox_loading"><img src="images/loading.gif" alt="loading..." /></div>\
										<div class="bluebox_data">\
											<span class="bluebox_close" style="display: none;" onmouseover="this.style.color = \'#ffffff\';" onmouseout="this.style.color = \'#afafaf\';" onclick="getblueboxbyid($(this).parents(\'.bluebox_wrapper\').attr(\'id\').replace(\'bluebox_\',\'\')).close();">x</span>\
											<div class="bluebox_title"></div>\
											<div class="bluebox_content"></div>\
											<div class="bluebox_placeholder"></div>\
										</div>\
									\
									</td>\
									<td class="con_shadow bluebox_border" style="width: 16px;background-repeat: repeat-y;"></td>\
								</tr>\
								<tr>\
									<td class="con_shadow" style="width: 16px;height: 16px;background-image: url(system/templates/css/images/bl.png);background-repeat: no-repeat;"></td>\
									<td class="con_shadow bluebox_border" style="height: 16px;background-repeat: repeat-x;"></td>\
									<td class="con_shadow" style="width: 16px;height: 16px;background-image: url(system/templates/css/images/br.png);background-repeat: no-repeat;"></td>\
								</tr>\
								\
							</table>\
						</div>');
		$(".con_shadow").fadeTo('slow', 0.7);
		this.div = $('#bluebox_' + self.boxcount).get(0);
		if(drag !== false)
		{
			var _ = this;
			$(this.div).draggable({handle: '.bluebox_title', start: function()
			{
				$(".windowzindex").parent().css('z-index', 900);
				$(_.div).parent().css("z-index", 901);
			}});
		} else
		{
			$(this.div).find(".bluebox_title").css("cursor", "default");
		}
		
		
		this.id = self.boxcount;
		/* --- */
		this.reset();
		this.removable = true;
		
		if(url != null)
			this.load(url, title);
		
		self.blueboxes[this.id] = this;
		return this;
}

bluebox.prototype = 
{
	reset: function() {
		var width = $(document).width();
		var hwidth = width / 2;
		var scroll = this.pagescroll();
		var top = scroll[1];
		var height = $(window).height();
		var top = top + height / 6;
		
		
		
		$(this.div).css({top: top, left: hwidth - 56});
		
	},
	setTitle: function(title) {
		this.title = title;
		if(title != "")
		{
				$(this.div).find('.bluebox_title').text(title);
		} else
		{
				$(this.div).find('.bluebox_title').html("&nbsp;");
		}
	},
	getTitle: function() {
		return this.title;
	},
	load: function(href, title) {
		this.setTitle(title);
		this.url = href;
		
		var d = this.div;
		$(d).find(".bluebox_loading").css('display','block');
		$(d).find(".bluebox_data").css('display','none');
		$(d).find(".bluebox_nav").css('display','none');
		
		
		var that = this;
		$(d).find(".bluebox_container").fadeIn('slow', function(){
			if(that.url.indexOf(' ') != -1){
				urls = that.url.split(' ');
				var u = urls[0];
				var s = urls[1];
			} else {
				var u = that.url;
				var s = "";
			}
			if(u.match(/^.*\.(img|png|jpg|gif|bmp)$/i))
			{
				that.player_image(u);
			} else if(u.match(/^#.*$/))
			{
				that.player_html(u);
			} else 
			{
				that.player_ajax(u, s);
			}
		});
		
	},
	player_image: function(url)
	{
		var href = url;
		var preloader = new Image();
		var _ = this;
		preloader.onload = function(){
			var height = preloader.height;
			var width = preloader.width;
			var sv = width / height;
			var dheight = $(window).height() - 300;
			var dwidth = $(window).width() - 400;
			if(height > dheight ){
				var height = dheight;
				var width = height * sv;
			}
			_.content('<div align="center"><img src="'+href+'" alt="'+href+'" height="'+height+'" width="'+width+'" /><br /></div>');
		}
		preloader.onerror = function(){
			_.content('<h3>Connection error!</h3> <br /> Please try again later!');
		}
		preloader.src = href;
	},
	player_html: function(url)
	{
		var data = $(url).clone(true, true);
		$(url).remove();
		this.content(data, true);
	},
	player_ajax: function(href, s)
	{
		var _ = this;
		$.ajax({
			method: "get",
			url: href,
			data: {"boxid": this.id},
			error: function(jqXHR){
				var req = jqXHR;
				LoadAjaxResources(jqXHR);
				var html = jqXHR.responseText;
				if(html != null) {
					var regexp = new RegExp("<body>");
					if(regexp.test(html)) {
						var id = randomString(5);
						top[id + "_html"] = html;
						html = '<iframe src="javascript:document.write(top.'+id+'_html);" height="500" width="100%" name="'+id+'" frameborder="0"></iframe>';
						if(s != ""){
							_.content(jQuery(html.replace(/<script(.|\s)*?\/script>/g, "")).find(s), null, function(){
							RunAjaxResources(req);
						});
						} else {
							_.content(html, null, function(){
							RunAjaxResources(req);
						});
						}
					} else {
						if(s != ""){
							_.content(jQuery(html.replace(/<script(.|\s)*?\/script>/g, "")).find(s), null, function(){
							RunAjaxResources(req);
						});
						} else {
							_.content(html, null, function(){
							RunAjaxResources(req);
						});
						}
					}
				} else {
					_.content('The request to the server wasn\'t complete.');
				}
			},
			success: function(html, code, jqXHR){
				var req =  jqXHR;
				LoadAjaxResources(jqXHR);
				var regexp = new RegExp("<body>");
				if(regexp.test(html)) {
					var id = randomString(5);
					top[id + "_html"] = html;
					html = '<iframe src="javascript:document.write(top.'+id+'_html);" height="500" width="100%" name="'+id+'" frameborder="0"></iframe>';
					if(s != ""){
						_.content(jQuery(html.replace(/<script(.|\s)*?\/script>/g, "")).find(s), null, function(){
							RunAjaxResources(req);
						});
					} else {
						_.content(html, null, function(){
							RunAjaxResources(req);
						});
					}
				} else {
					if(s != ""){
						_.content(jQuery(html.replace(/<script(.|\s)*?\/script>/g, "")).find(s), null, function(){
							RunAjaxResources(req);
						});
					} else {
						_.content(html, null, function(){
							RunAjaxResources(req);
						});
					}
				}
				
					
			}

		});
		
	},
	
	close: function()
	{
		$(this.div).find(".bluebox_container").fadeOut(300);
		if(this.removable) {
			var s = this;
			setTimeout(function(){
				$(s.div).remove();
			},1000);
		}
	},
	content: function(html, object, callback)
	{
		$(this.div).css("display", "block");
		// set new data to box
		var d = $(this.div);
		d.find(".bluebox_loading").css("display", "block");
		d.find(".bluebox_data").css("display", "none");
		
		if(object == null)
			d.find(".bluebox_content").html(html);
		else {
			html.appendTo(d.find(".bluebox_content"));
			html.css("display", "block");
		}
		
		this.animateContent(callback);
	},
	animateContent: function(callback) {
		var fn = callback;
		var d = $(this.div);
		d.find(".bluebox_close").css("display","block");
		
		/* now find position */
		
		// we have to show the item to get good results
		d.find(".bluebox_data").css("display", "block");
		var nwidth = d.find(".bluebox_content").width();
		var theight = d.find(".bluebox_content").height();
		d.find(".bluebox_data").css("display", "none");
		
		var width = $(document).width();
		var hwidth = width / 2;
		
		var s = this;
		// hide loading and show content
		d.find(" > table .bluebox_inner").css("width", "52");
		d.find(" > table .bluebox_inner").animate({"width": nwidth + 32}, 150);
		
		d.animate({'left': hwidth -  parseInt(nwidth) / 2 + "px"}, 150);
		
		setTimeout(function(){
			s.hideLoading();
			
			$(s.div).find(".bluebox_data").css("display", "block");
			$(s.div).find(".bluebox_data").css("height", "64px");
			d.find(" > table .bluebox_inner").css("width", "");
			$(s.div).find(".bluebox_data").animate({"height": theight + 14}, 200, function(){
				$(".bluebox_container").css("width", "");
				$(s.div).find(".bluebox_data").css("height", "");
			});
			$(".bluebox_container").css("width", "");
			if(fn != null)
				fn();
		}, 160);
		setTimeout(function(){
			$(".bluebox_container").css("width", "");
			$(".ui-resizable-handle").css("z-index", 899);
		}, 400)
	},
	showLoading: function() {
		$(this.div).css("display", "block");
		if($(this.div).find(".bluebox_container").css("display") == "none") {
			$(this.div).find(".bluebox_container").fadeIn('slow');	
		}
		$(this.div).find(".bluebox_loading").css("display", "block");
		$(this.div).find(".bluebox_data").css("display", "none");
		
		
	},
	hideLoading: function() {
		$(this.div).find(".bluebox_loading").css("display", "none");
	},
	pagescroll: function()
	{
		return new Array($(window).scrollLeft(),$(window).scrollTop())
	},
	hide: function() {
		$(this.div).find(".bluebox_container").fadeOut(300);
	}
}

var dialog = bluebox;

function ExistingBluebox(id, drag) {
	
	this.id = id;
	this.drag = drag;
	
	
	
	$(".con_shadow").fadeTo('slow', 0.7);
	document.getElementById(id).id = "bluebox_" + id;
	this.div = document.getElementById("bluebox_" + id);
	$(this.div).css("display", "none");
	if(drag !== false)
	{
		var _ = this;
		$(this.div).draggable({handle: '.bluebox_title', start: function()
		{
			$(".windowzindex").parent().css('z-index', 900);
			$(_.div).parent().css("z-index", 901);
		}});
	} else
	{
		$(this.div).find(".bluebox_title").css("cursor", "default");
	}
	
	var that = this;
	
	this.reset();
	this.removable = true;
	$(this.div).find(".bluebox_container").css("display", "block");
	$(this.div).find(".bluebox_container").fadeIn("fast", function(){
		that.showLoading();		
	});
	
	var that = this;
	$(this.div).find(".bluebox_close").click(function(){
		that.close();
	});
	
	
	setTimeout(function(){
		that.animateContent();
	}, 500);
	
	
	self.blueboxes[this.id] = this;
	return this;
}
ExistingBluebox.prototype = bluebox.prototype;