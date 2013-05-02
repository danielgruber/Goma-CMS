/**
  * simple fileupload
  * 
  * thanks to https://github.com/pangratz/dnd-file-upload/blob/master/jquery.dnd-file-upload.js
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 07.05.2012
  * $Version 2.0.1
*/

var FileUploadSet = function(name, table, url) {
	this.table = $(table);
	this.url = url;
	this.tbody = this.table.find("tbody");
	this.name = name;
	
	
	// rebuild the no-js-code to a browse-button
	this.table.find("thead .no-js").html('<div class="uploadBtn"><input type="button" class="button uploadbutton" value="'+lang("files.browse")+'" /></div>');
	this.table.find("thead .no-js").removeClass("no-js");
	this.browse = this.table.find("thead .uploadbutton");
	
	var $this = this;
	
	// bind delete actions and remove no-js-code
	this.tbody.find("tr td.actions .delete").html('<img src="images/16x16/del.png" height="16" width="16" alt="del" title="'+lang("delete")+'" />');
	
	// bindings
	var bindDeleteEvent = function(node) {
		$(node).click(function(){
			var tr = $(this).parent().parent().parent();
			var id = $(this).parent().parent().parent().attr("name");
			$(this).attr("src", "images/16x16/loading.gif");
			$.ajax({
				url: $this.url + "/remove/" + id,
				type: "post"
			}).done(function(){
				tr.fadeOut("fast", function(){
					tr.remove();
					if($this.tbody.find(" > tr").length == 0) {
						$this.tbody.append('<tr><th class="empty" colspan="3">'+lang("files.no_file")+'</th></tr>');
					}
					redraw();
				});
			});
		});
		$(node).css("cursor", "pointer");
	};
	
	// redraw
	var redraw = function() {
		if($this.tbody.find("tr").length > 1) {
			var i = 1;
			$this.tbody.find("tr").each(function(){
				$(this).removeClass("grey");
				if(i == 0) {
					i++;
				} else {
					$(this).addClass("grey");
					i = 0;
				}
			});
		}
	};
	
	bindDeleteEvent(this.tbody.find("tr td.actions .delete img"));
	
	lang("loading");
	lang("waiting");
	
	// register the uploader
	this.uploader = new AjaxUpload(this.table, {
		url: url + "/frameUpload/",
		ajaxurl: url + "/ajaxUpload/",
		browse: this.browse,
		
		multiple: true,
		
		// events
		uploadStarted: function(fileIndex, upload) {
			var that = this;
			var id = $this.name+'_upload_'+fileIndex;
			if($this.tbody.find("tr:last").hasClass("grey")) {
				var color = "white";
			} else {
				var color = "grey";
			}
			if($this.tbody.find(" > tr > th").length > 0) {
				$this.tbody.find(" > tr > th").parent().remove();
			}
			
			this.queue[fileIndex].tableid = id;
			
			$this.tbody.append('<tr class="'+color+'" id="'+id+'">\
				<td class="icon"></td>\
				<td class="filename" title="'+lang("loading")+'"><span class="filename">'+upload.fileName+'</span><div class="progressbar"><div class="progress"></div><span>'+lang("waiting")+'</span></div></div></td>\
				<td class="actions"><div class="delete"><img src="images/16x16/del.png" height="16" width="16" alt="del" title="'+lang("delete")+'" /></div></td>\
			</tr>');
			
			
			$("#" + id).find(".delete img").click(function(){
				that.abort(fileIndex);
			});
			$("#" + id).find(".delete img").css("cursor", "pointer");
			
			redraw();
		},
		
		dragInDocument: function() {
			$this.table.find(".filetable").addClass("dragInDoc");
		},
		dragLeaveDocument: function() {
			$this.table.find(".filetable").removeClass("dragInDoc");
			$this.table.find(".filetable").removeClass("dragOver");
		},
		
		dragOver: function() {
			$this.table.find(".filetable").addClass("dragOver");
		},
		
		/**
		 * called when the speed was updated, just for ajax-upload
		*/
		speedUpdate: function(fileIndex, file, KBperSecond) {
			if(typeof this.queue[fileIndex].tableid != "undefined") {
				var ext = "KB/s";
				KBperSecond = Math.round(KBperSecond);
				if(KBperSecond > 1000) {
					KBperSecond = Math.round(KBperSecond / 1000, 2);
					ext = "MB/s";
				}
				$("#" + this.queue[fileIndex].tableid).find(".progressbar span").html(KBperSecond + ext);
				
			}
		},
		
		/**
		 * called when the progress was updated, just for ajax-upload
		*/
		progressUpdate: function(fileIndex, file, newProgress) {
			if(typeof this.queue[fileIndex].tableid != "undefined") {
				$("#" + this.queue[fileIndex].tableid).find(".filename").attr("title", lang("loading") + " ("+newProgress+"%)");
				$("#" + this.queue[fileIndex].tableid).find(".progress").stop().animate({width: newProgress + "%"}, 200);
			}
		},
		
		/**
		 * event is called when the upload is done
		*/
		always: function(time, fileIndex) {
			if(typeof this.queue[fileIndex].tableid != "undefined") {
				$("#" + this.queue[fileIndex].tableid).find(".progress span").html("100%");
				$("#" + this.queue[fileIndex].tableid).find(".progress").css("width", "100%");
				$("#" + this.queue[fileIndex].tableid).removeAttr("title");
			}
		},
		
		/**
		 * method which is called, when we receive the response
		*/
		done: function(html, fileIndex) {
			if(typeof this.queue[fileIndex].tableid != "undefined") {
				var tablerow = $("#" + this.queue[fileIndex].tableid);
				try {
					var data = eval('('+html+');');
					if(data.status == 0) {
						tablerow.find(".filename").html('<div class="error">'+data.errstring+'</div>');
						setTimeout(function(){
							tablerow.fadeOut(300, function(){
								tablerow.remove();
								if($this.tbody.find(" > tr").length == 0) {
									$this.tbody.append('<tr><th class="empty" colspan="3">'+lang("files.no_file")+'</th></tr>');
								}
							});
						}, 2000);
						tablerow.find(".delete img").remove();
					} else {
						if(data.multiple) {
							for(i in data.files) {
								file = data.files[i];
								
								// the current we can just update
								if(i == 0) {
									tablerow.find(".filename").fadeTo(0, 0.0);
									tablerow.attr("name", file.id);
									tablerow.find(".icon").fadeTo(0, 0.0, function(){
										tablerow.find(".icon").html('<img src="'+file.icon16+'" alt="icon" />');
										tablerow.find(".filename").attr("title", file.name);
										if(file.path)
											tablerow.find(".filename").html('<a href="'+file.path+'" target="_blank">'+file.name+'</a>');
										else
											tablerow.find(".filename").html('<a target="_blank">'+file.name+'</a>');
										
										tablerow.find(".filename").fadeTo(200, 1.0);
										tablerow.find(".icon").fadeTo(200, 1.0);	
									});

									
									
									tablerow.find(".delete img").unbind("click");
									bindDeleteEvent(tablerow.find(".delete img"));
								} else {
									
									
									// now add some records
									this.currentIndex++;
									var id = $this.name+'_frameupload_'+ this.currentIndex;
									tablerow.after('<tr class="" id="'+id+'">\
				<td class="icon"></td>\
				<td class="filename" title="'+file.name+'"><span class="filename">'+file.name+'</span></div></td>\
				<td class="actions"><div class="delete"><img src="images/16x16/del.png" height="16" width="16" alt="del" title="'+lang("delete")+'" /></div></td>\
			</tr>');
									tablerow = $("#" + id);
									tablerow.find(".icon").fadeTo(0, 0.0, function(){
										tablerow.find(".icon").html('<img src="'+file.icon16+'" alt="icon" />');
										tablerow.find(".filename").attr("title", file.name);
										if(file.path)
											tablerow.find(".filename").html('<a href="'+file.path+'" target="_blank">'+file.name+'</a>');
										else
											tablerow.find(".filename").html('<a target="_blank">'+file.name+'</a>');	
											
										tablerow.find(".filename").fadeTo(200, 1.0);
										tablerow.find(".icon").fadeTo(200, 1.0);
									});
									
									tablerow.find(".delete img").unbind("click");
									bindDeleteEvent(tablerow.find(".delete img"));
								}
							}
							
							$this.table.find("tr").removeClass("grey").removeClass("white");
							$this.table.find("tr:even").addClass("grey");
						} else {
							tablerow.find(".filename").fadeTo(200, 0.0);
							tablerow.attr("name", data.file.id);
							tablerow.find(".icon").fadeTo(200, 0.0, function(){
								tablerow.find(".icon").html('<img src="'+data.file.icon16+'" alt="icon" />');
								tablerow.find(".filename").attr("title", data.file.name);
								if(data.file.path)
									tablerow.find(".filename").html('<a href="'+data.file.path+'" target="_blank">'+data.file.name+'</a>');
								else
									tablerow.find(".filename").html('<a target="_blank">'+data.file.name+'</a>');	
							});
							setTimeout(function(){
								tablerow.find(".filename").fadeTo(200, 1.0);
								tablerow.find(".icon").fadeTo(200, 1.0);
							}, 220);
							
							tablerow.find(".delete img").unbind("click");
							bindDeleteEvent(tablerow.find(".delete img"));
						}
					}
				} catch(err) {
					if(this.isAbort) {
					
					} else {
						tablerow.find(".filename").html('<div class="error">'+data.errstring+'</div>');
						setTimeout(function(){
							tablerow.fadeOut(300, function(){
								tablerow.remove();
								if($this.tbody.find(" > tr").length == 0) {
									$this.tbody.append('<tr><th class="empty" colspan="3">'+lang("files.no_file")+'</th></tr>');
								}
							});
						}, 2000);
						tablerow.find(".delete img").remove();
					}
				}
				
				redraw();
			}
		},
		
		/**
		 * called on cancel
		*/
		cancel: function(fileIndex) {	
			if(typeof this.queue[fileIndex].tableid != "undefined") {
				var tablerow = $("#" + this.queue[fileIndex].tableid);
				tablerow.slideUp(300, function(){
					tablerow.remove();
					redraw();
					if($this.tbody.find(" > tr").length == 0) {
						$this.tbody.append('<tr><th class="empty" colspan="3">'+lang("files.no_file")+'</th></tr>');
					}
				});
			}
			redraw();
			
		}
		
	});
}