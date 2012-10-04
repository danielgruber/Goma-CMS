/**
  * ajax fileupload
  * 
  * thanks to https://github.com/pangratz/dnd-file-upload/blob/master/jquery.dnd-file-upload.js
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 08.05.2012
  * $Version 2.0.2
*/

var AjaxUpload = function(DropZone, options) {
	this.DropZone = $(DropZone);
	this.url = location.href;
	this.ajaxurl = location.href;
	this["multiple"] = false;
	this.id = randomString(10);
	for(i in options) {
		this[i] = options[i];
	}
	this.loading = false;
	
	var $this =  this;
	
	
	// bind events on document to stop the browser showing the file, if the user does not hit the dropzone
	$(document).bind("dragenter", function(event){
		$this.dragEnterDocument(event);
		return $this._dragInDocument(event);
	});
	
	$(document).bind("dragleave", function(event){
		return $this._dragLeaveDocument(event);
	});
	
	$(document).bind("dragover", function(event){
		return $this._dragInDocument(event);
	});
	
	if(document.addEventListener) {
		document.addEventListener("drop", function(event){
			return $this._dragLeaveDocument(event);
		});
	}
	
	// now bind events to dropzone
	this.DropZone.bind("dragenter", function(event){
		return $this._dragEnter(event);
	});
	this.DropZone.bind("dragover", function(event){
		return $this._dragOver(event);
	});
	
	if(this.DropZone.get(0).addEventListener) {
		this.DropZone.get(0).addEventListener("drop", function(ev) {
			return $this._drop(ev);
		});
	}
	
	// browse-button for old-browser-fallback via iFrame
	if(typeof this.browse != "undefined") {
		this.browse = $(this.browse);
		// bind events to browse-button
		this.browse.hover(function(){
			if(!$this.loading) {
				$this.browse.removeAttr("disabled");
				$this.placeBrowseHandler();
			} else {
				$this.browse.attr("disabled", "disabled");
				$this.hideBrowseHandler();
			}
		});
		
		this.placeBrowseHandler();
	}
	
	return this;
}

AjaxUpload.prototype = {
	uploadRateRefreshTime: 500,
	frames: [],
	
	queue: [],
	currentIndex: 0,
	
	// events
	
	// document events
	dragInDocument: function(ev) {
		this.DropZone.addClass("fileupload-drag");
	},
	dragLeaveDocument: function(ev) {
		this.DropZone.removeClass("fileupload-drag");
		this.DropZone.removeClass("fileupload-drag-over");
	},
	dragEnterDocument: function(ev) {
		
	},
	
	// dropzone events
	dragEnter: function(ev) {
		this.DropZone.addClass("fileupload-drag-over");
	},
	dragOver: function(ev) {
		this.DropZone.addClass("fileupload-drag-over");
	},
	
	newFilesDropped: function() {
	
	},
	
	// upload-handlers
	/**
	 * called when upload started
	 *
	 *@name uploadStarted
	*/
	uploadStarted: function(index, upload) {
		
	},
	
	/*
	 * progress and speed
	*/
	
	/**
	 * called when progress was updated
	 *
	 *@name progressUpdate
	 *@param int - fileindex
	 *@param file - file
	 *@param int - current progress in percent
	*/
	progressUpdate: function(index, file, currentProgress) {
	
	},
	
	/**
	 * called when speed was updated
	 *
	 *@name speedUpdate
	 *@param int - fileIndex
	 *@param file - file
	 *@param int - current progress in percent
	*/
	speedUpdate: function(index, file, currentSpeed) {
	
	},
	
	/**
	 * always called regardless of an error
	*/
	always: function(time, index) {
		
	},
	
	/**
	 * if succeeded
	*/
	done: function(response, index) {
	
	},

	// error-handlers
	errTooManyFiles: function() {
		
	},
	
	fail: function(index) {
		
	},
	
	/**
	 * called on cancel
	*/
	cancel: function(index) {
	
	},
	
	/**
	 * PRIVATE METHODS, YOU SHOULD NOT REDECLARE THEM
	*/
	
	/**
	 * when the user drags a file over the document
	 *
	 *@name _dragInDocument
	 *@access public
	*/
	_dragInDocument: function(event) {
		if(!this.loading) {
			this.dragInDocument(event);
		}
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * when a user leaves the document with the file
	 *
	 *@name _dragLeaveDocument
	*/
	_dragLeaveDocument: function(event) {
		if(!this.loading) {
			this.dragLeaveDocument(event);
		}
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * when the user drags the file in the dropzone
	 *
	 *@name _dragEnter
	*/
	_dragEnter: function(event) {
		if(!this.loading) {
			this.dragEnter(event);
			this.dragOver(event);
		}
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * when the user drags the file within the dropzone
	 *
	 *@name _dragOver
	*/
	_dragOver: function(event) {
		if(!this.loading) {
			this.dragOver(event);
		}
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * when the user drops the file
	 *
	 *@name _drop
	*/
	_drop: function(event) {
		if(this.multiple || !this.loading) {
			this.dragLeaveDocument();
			this.newFilesDropped();
			var dt = event.dataTransfer;
			var files = dt.files;
			this.transferAjax(files);
		}
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * is always called when transfer is completed, regardless whether succeeded or not
	 *
	 *@name _complete
	*/
	_complete: function(event, upload, fileIndex) {
		var now = new Date().getTime();
		var timeDiff = now - upload.downloadStartTime;
		
		this.browse.removeAttr("disabled");
		
		this.queue[fileIndex].loading = false;
		this.queue[fileIndex].loaded = true;
		
		this.loading = false;
		this.always(timeDiff, fileIndex);
	},
	
	/**
	 * progress-handler
	 *
	 *@name progress
	 *@access public
	 *@method event
	*/
	_progress: function(event, upload) {
		if (event.lengthComputable) {
			var percentage = Math.round((event.loaded * 100) / event.total);
			if (upload.currentProgress != percentage) {

				// log(this.fileIndex + " --> " + percentage + "%");

				upload.currentProgress = percentage;
				this.progressUpdate(upload.fileIndex, upload.fileObj, upload.currentProgress);
				
				var elapsed = new Date().getTime();
				var diffTime = elapsed - upload.currentStart;
				if (diffTime >= this.uploadRateRefreshTime) {
					var diffData = event.loaded - upload.startData;
					var speed = diffData / diffTime; // in KB/sec
					
					this.speedUpdate(upload.fileIndex, upload.fileObj, speed);

					return elapsed;
				}
			}
		}
	},
	
	/**
	 * called when request succeeded
	 *
	 *@name _success
	 *@param response
	*/
	_success: function(response, fileIndex) {
		this.done(response, fileIndex);
	},
	
	/**
	 * transfer methods
	*/
	
	/**
	 * ajax upload
	 *
	 *@name transferAjax
	 *@param files
	*/
	transferAjax: function(files) {
		if(!this.multiple) {
			if(files.length > 1) {
				this.errTooManyFiles();
				return false;
			}
		}
		
		if(this.loading && !this.multiple)
			return false;
		
		var $this = this;
		
		for ( var i = 0; i < files.length; i++) {
			var file = files[i];
			
			if(typeof file.name != "undefined") {
				file.fileName = file.name;
			}
			
			if(typeof file.size != "undefined") {
				file.fileSize = file.size;
			}
			
			var _xhr = this.generateXHR(i, file);
						
			this.queue[_xhr.upload.fileIndex] = {
				send: function() {
					return this.xhr.send(this.upload.fileObj);
				},
				abort: function() {
					return this.xhr.abort();
				},
				fileIndex: _xhr.upload.fileIndex,
				xhr: _xhr,
				upload: _xhr.upload
			};
			
			this.loading = true;
			if(!this.multiple)
				this.hideBrowseHandler();
		}
		
		this.processQueue();
	},
	
	/**
	 * generates the XML-HTTP-Request
	 *
	 *@name generateXHR
	 *@access public
	*/
	generateXHR: function(i, file) {
		
		var $this = this;
		
		// create a new xhr object
		var xhr = new XMLHttpRequest();
		var upload = xhr.upload;
		upload.fileIndex = i + this.queue.length;
		upload.fileObj = file;
		upload.downloadStartTime = new Date().getTime();
		upload.currentStart = new Date().getTime();
		upload.currentProgress = 0;
		upload.startData = 0;
		upload.fileName = file.fileName;
		
		// add listeners
		upload.addEventListener("progress", function(event){
			if(currentStart = $this._progress(event, this)) {
				this.startData = event.loaded;
				this.currentStart = currentStart;
			}
		}, false);

		xhr.open("POST", this.ajaxurl);
		xhr.setRequestHeader("Cache-Control", "no-cache");
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhr.setRequestHeader("X-File-Name", file.fileName);
		xhr.setRequestHeader("X-File-Size", file.fileSize);
		xhr.setRequestHeader("Content-Type", "multipart/form-data");
		
		xhr.onreadystatechange = function (event) {
			if (xhr.readyState == 4) {
				$this._complete(event, this, this.upload.fileIndex);
			}
			
    		if (xhr.readyState == 4 && xhr.responseText != "") {
      		  	$this._success(xhr.responseText, this.upload.fileIndex);
   			}
		};
		
		return xhr;
	},
	
	/**
	 * starts the upload
	 *
	 *@name processQueue
	 *@access public
	*/
	processQueue: function() {
		for(i in this.queue) {
			if(!this.queue[i].loading && !this.queue[i].loaded) {
				this.queue[i].loading = true;
				this.queue[i].send();
				this.uploadStarted(i, this.queue[i].upload);
			}
		}
	},
	
	/**
	 * transports the files of a specific formfield via iframe
	 *
	 *@name transportFrame
	*/
	transportFrame: function(field) {
		if(this.loading)
			return false;
		
		var $this = this;
		if(false) { //field.files) {
			this.hideBrowseHandler();
			
			var form = $(field).parents("form");
			
			form.attr("id", "");
			
			// we can upload through the file-handler, yeah :)
			this.transferAjax(field.files);
			return true;
		} else {
			// okay, let's make iframe-upload
			
			// first create the iframe, we want to send the file through
			
			this.loading = true;
			
			var iframe = randomString(10);
			this.frameID = iframe;
			
			var upload = {};
			upload.downloadStartTime = new Date().getTime();
			upload.fileIndex = this.queue.length;
			var val = $(field).val();
			val = val.substring(val.lastIndexOf("\\") + 1, val.length);
			val = val.substring(val.lastIndexOf("/") + 1, val.length);
			upload.fileName = val;
			
			$("body").append('<iframe name="'+iframe+'" id="frame_'+iframe+'" frameborder="0" height="1" width="1" src="about:blank;"></iframe>');
			$(field).parents("form").attr("target", iframe);
			
			this.hideBrowseHandler();
			
			var form = $(field).parents("form");
			
			form.attr("id", "");
			
			this.queue[upload.fileIndex] = {
				send: function() {
					form.submit();
				},
				abort: function() {
					form.remove();
				},
				fileIndex: upload.fileIndex,
				upload: upload
			};
			
			var i = document.getElementById("frame_" + iframe);
			
			
			var testing = function(){
				if (i.contentDocument) {
					var d = i.contentDocument;
				} else if (i.contentWindow) {
					var d = i.contentWindow.document;
				} else {
					var d = window.frames[iframe].document;
				}
				$this._complete(null, upload, upload.fileIndex);
				$this._success(d.body.innerHTML, upload.fileIndex);
				
				$this.loading = false;
				
			};
			i.onload = testing;
			
			this.processQueue();
		}
	},
	
	/**
	 * browse-button-implementation
	*/
	
	/**
	 * placed the file-input over the browse-button
	 *
	 *@name placeBrowseHandler
	*/
	placeBrowseHandler: function() {
		if(this.loading)
			this.hideBrowseHandler();
		
		if(typeof this.browse == "undefined")
			return false;
		
		var $this = this;
		
		// now create the form
		if($("#" + this.id + "_uploadForm").length == 0) {
			$("body").append('<form id="' + this.id+'_uploadForm" style="position: absolute; left: -500px;z-index: 999;" method="post" action="'+this.url+'" enctype="multipart/form-data"><input name="file" style="font-size: 200px;float: right;" type="file" class="fileSelectInput" /></form>');
			$("#" + this.id + "_uploadForm").find(".fileSelectInput").change(function(){
				$this.transportFrame(this);
			});
			
			if(this.multiple) {
				$("#" + this.id + "_uploadForm").find(".fileSelectInput").attr("multiple", "multiple");
				$("#" + this.id + "_uploadForm").find(".fileSelectInput").attr("name", "file[]");
			}
			
			$("#" + this.id + "_uploadForm").hover(function(){
				if(!$this.loading) {
					$this.browse.removeAttr("disabled");
					$this.placeBrowseHandler();
				} else {
					$this.browse.attr("disabled", "disabled");
					$this.hideBrowseHandler();
				}
			});
		}
		
		var $form = $("#" + this.id + "_uploadForm");
		$form.css("display", "block");
		$form.css({top: this.browse.offset().top, left: this.browse.offset().left + this.browse.outerWidth() - $form.width(), width: this.browse.outerWidth(), height: this.browse.outerHeight(), overflow: "hidden"});
		$form.fadeTo(0, 0);
	},
	
	/**
	 * places the file-input out of the document
	 *
	 *@name hideBrowseHandler
	*/
	hideBrowseHandler: function() {
		if(typeof this.browse == "undefined")
			return false;
		
		var $form = $("#" + this.id + "_uploadForm");
		$form.css({top: - 400, left: this.browse.offset().left});
	},
	
	/**
	 * aborts the upload(s)
	 *
	 *@name abort
	*/
	abort: function(fileIndex) {
		if(typeof fileIndex == "undefined") {
			for(i in this.queue) {
				if(this.queue[i] != null) {
					this.queue[i].abort();
					this.cancel(i);
					this._complete(this, this.queue[i].upload, i);
					this.queue[i].loading = false;
					this.queue[i].loaded = true;
				}
			}
			
			this.queue = [];
					
			this.loading = false;
		} else {
			if(typeof this.queue[fileIndex] != "undefined") {
				this.queue[fileIndex].abort();
				this.cancel(fileIndex);
			}
		} 
		
	}
};