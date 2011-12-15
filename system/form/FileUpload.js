/**
  * simple fileupload
  * 
  * thanks to https://github.com/pangratz/dnd-file-upload/blob/master/jquery.dnd-file-upload.js
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see "license.txt"
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 10.12.2011
*/


function AjaxfiedUpload(formelement, url, success) {
	this.formelement = formelement;
	this.element = $(formelement).find(".icon").get(0);
	this.destInput = $(formelement).find(".FileUploadValue");
	this.url = url;
	// so first great thing: add Ajax-Upload
	
	var $this =  this;
	
	var jQueryDropzone = $("#" + this.element.id);
	
	// bind events on dropzone
	this.element.addEventListener("drop", function(event){
		return $this.drop(event);
	}, true);
	
	jQueryDropzone.bind("dragenter", function(event){
		return $this.dragenter(event);
	});
	jQueryDropzone.bind("dragover", function(event){
		return $this.dragover(event);
	});
	
	// bind events on document to stop the browser showing the file, if the user does not hit the dropzone
	$(document).bind("dragenter", function(event){
		return $this.dragEnterDocument(event);
	});
	
	$(document).bind("dragleave", function(event){
		return $this.dragLeaveDocument(event);
	});
	
	$(document).bind("dragover", function(event){
		return $this.dragEnterDocument(event);
	});
	
	document.addEventListener("drop", function(event){
		$this.dragLeaveDocument(event);
		event.stopPropagation();
		event.preventDefault();
		return false;
	});
	
	// the info-zone
	this.formelement.find(".actions").append('<div class="progress_info"></div>');
	this.infoZone = this.formelement.find(".actions").find(".progress_info");
	
	// if the programmer wants to redefine the success-method
	if(typeof success != "undefined")
		this.success = success;
	
	// append fallback for not drag'n'drop-browsers
	this.formelement.find(".actions").append('<input type="button" class="button fileSelect" value="'+lang_browse+'" />');
	this.browse = this.formelement.find(".actions").find(".fileSelect");
	
	
	// bind events to browse-button
	this.browse.hover(function(){
		if(!$this.loading)
			$this.placeHandler();
	});
	
	// now hide original file-upload-field
	
	this.formelement.find(".input[type=file]").css("display", "none");
	
	return this;
}

AjaxfiedUpload.prototype = {
	uploadRateRefreshTime: 100,
	loading: false,
	
	// events
	
	/**
	 * event called when new files were dropped
	*/
	newFilesDropped: function() {
		$(this.element).removeClass("active");
		$(this.element).removeClass("beforeDrop");
	},
	
	/**
	 * event called when the upload was started, either through ajax or frame
	*/
	uploadStarted: function() {
		var $this = this;
		this.infoZone.html('<div class="progressbar"><div class="progress"></div><span><img src="images/16x16/loading.gif" alt="Uploading.." /></span><div class="cancel"></div></div>');
		this.infoZone.find(".cancel").click(function(){
			$this.abort();
		});
		//this.formelement.find(".icon").find("span").html(file.fileName);
		$(this.element).append('<div class="loading"></div>');
		this.loading = true;
	},
	
	/**
	 * called when the speed was updated, just for ajax-upload
	*/
	speedUpdate: function(fileIndex, file, KBperSecond) {
		var ext = "KB/s";
		KBperSecond = Math.round(KBperSecond);
		if(KBperSecond > 1000, 2) {
			KBperSecond = Math.round(KBperSecond / 1000, 2);
			ext = "MB/s";
		}
		this.infoZone.find("span").html(KBperSecond + ext);
	},
	
	/**
	 * called when the progress was updated, just for ajax-upload
	*/
	progressUpdate: function(fileIndex, file, newProgress) {
		this.infoZone.find(".progress").css("width", newProgress + "%");
		
	},
	
	/**
	 * event is called when the upload is done
	*/
	done: function() {
		this.infoZone.find("span").html("100%");
		this.infoZone.find(".progress").css("width", "100%");
		var $this = this;
		setTimeout(function(){
			$this.infoZone.find(".progressbar").slideUp("fast", function(){
				$this.infoZone.html("");
			});
		}, 1000);
		
		$(this.element).find(".loading").remove();
		this.loading = false;
	},
	
	/**
	 * event is called when the upload was cancelled by the user
	*/
	cancel: function() {
		var $this = this;
		this.infoZone.find(".progressbar").slideUp("fast", function(){
			$this.infoZone.html("");
		});
		$(this.element).find(".loading").remove();
	},
	
	/**
	 * method which is called, when we receive the response
	*/
	success: function(html) {
		try {
			var data = eval('('+html+');');
			if(data.status == 0) {
				
				this.infoZone.html('<div class="error">'+data.errstring+'</div>');
			} else {
				$(this.element).find("img").attr("src", data.file.icon);
				$(this.element).find("a").attr("href", data.file.path);
				$(this.element).find("span").html(data.file.name);
				this.destInput.val(data.file.realpath);
			}
		} catch(err) {
			if(this.isAbort) {
			
			} else {
				this.infoZone.html('<div class="error">An Error occured.</div>');
			}
		}
	},
	
	// defines of this class
	
	dragEnterDocument: function(event) {
		
		
		event.stopPropagation();
		event.preventDefault();
		
		$(this.element).addClass("active");
		
		return false;
	},
	
	dragLeaveDocument: function(event) {
		$(this.element).removeClass("active");
		$(this.element).removeClass("beforeDrop");
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	dragenter: function(event) {
		
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * this is the event, which is called when the user drags over the dropzone
	*/
	dragover: function(event) {
		event.stopPropagation();
		event.preventDefault();
		if(!this.loading) {
			$(this.element).addClass("active");
			$(this.element).addClass("beforeDrop");
		}
		
		return false;
	},
	
	/**
	 * this is the event, which is called when the user drops on the DropZone
	*/
	drop: function(event) {
		if(!this.loading) {
			var dt = event.dataTransfer;
			var files = dt.files;
			
			$(this.element).addClass("active");
			
			this.uploadFiles(files);
		}
		event.stopPropagation();
		event.preventDefault();
		return false;
	},
	
	/**
	 * uploads the filelist with the file-API
	 * files can get from drag'n'drop or file-input
	 *
	 *@name uploadFiles
	 *@access public
	 *@param FILELIST files
	*/
	uploadFiles: function(files) {
		this.frameUpload = false;
		this.newFilesDropped();
		var $this = this;
		
		for ( var i = 0; i < files.length; i++) {
			var file = files[i];
			
			if(typeof file.name != "undefined") {
				file.fileName = file.name;
			}
			
			if(typeof file.size != "undefined") {
				file.fileSize = file.size;
			}
			
			// create a new xhr object
			var xhr = new XMLHttpRequest();
			var upload = xhr.upload;
			this.fileIndex = i;
			this.fileObj = file;
			this.downloadStartTime = new Date().getTime();
			this.currentStart = this.downloadStartTime;
			this.currentProgress = 0;
			this.startData = 0;

			// add listeners
			upload.addEventListener("progress", function(event){
				$this.progress(event, $this);
			}, false);
			upload.addEventListener("load", function(event){
				$this.load(event, this);
			}, false);
			
			xhr.open("POST", this.url + "/ajaxUpload/");
			xhr.setRequestHeader("Cache-Control", "no-cache");
			xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			xhr.setRequestHeader("X-File-Name", file.fileName);
			xhr.setRequestHeader("X-File-Size", file.fileSize);
			xhr.setRequestHeader("Content-Type", "multipart/form-data");
			
			
			xhr.onreadystatechange = function () {
        		if (xhr.readyState == 4) {
          		  	$this.success(xhr.responseText);
       			}
    		};
			
			xhr.send(file);
			
			this.xhr = xhr;

			this.uploadStarted();
			
			if(i > 0)
				break;
		}
	},
	
	/**
	 * if the request is completed, but there is no response, yet
	 *
	 *@name load
	 *@access public
	 *@method event
	*/
	load: function(event, $this) {
		var now = new Date().getTime();
		var timeDiff = now - $this.downloadStartTime;
		
		this.done();
	},
	
	/**
	 * progress-handler
	 *
	 *@name progress
	 *@access public
	 *@method event
	*/
	progress: function(event, $this) {
		if (event.lengthComputable) {
			var percentage = Math.round((event.loaded * 100) / event.total);
			if (this.currentProgress != percentage) {

				// log(this.fileIndex + " --> " + percentage + "%");

				this.currentProgress = percentage;
				this.progressUpdate(this.fileIndex, this.fileObj, this.currentProgress);

				var elapsed = new Date().getTime();
				var diffTime = elapsed - this.currentStart;
				if (diffTime >= this.uploadRateRefreshTime) {
					var diffData = event.loaded - this.startData;
					var speed = diffData / diffTime; // in KB/sec
					
					this.speedUpdate($this.fileIndex, $this.fileObj, speed);

					this.startData = event.loaded;
					this.currentStart = elapsed;
				}
			}
		}
	},
	
	// invoked when the input field has changed and new files have been dropped
	// or selected
	change: function(field) {
		if(this.loading)
			return false;
		
		var $this = this;
		if(field.files) {
			// we can upload through the file-handler, yeah :)
			this.uploadFiles(field.files);
			this.hideHandler();
			return true;
		} else {
			// okay, let's make iframe-upload
			
			// first create the iframe, we want to send the file through
			
			this.frameUpload = true;
			this.loading = true;
			
			var iframe = randomString(10);
			this.frameID = iframe;
			$("body").append('<iframe name="'+iframe+'" id="frame_'+iframe+'" frameborder="0" height="1" width="1" src="about:blank;"></iframe>');
			$(field).parent().attr("target", iframe);
			this.uploadStarted();
			$(field).parent().submit();
			
			this.hideHandler();
			var i = document.getElementById("frame_" + iframe);
			
			
			var testing = function(){
				if (i.contentDocument) {
					var d = i.contentDocument;
				} else if (i.contentWindow) {
					var d = i.contentWindow.document;
				} else {
					var d = window.frames[iframe].document;
				}
				$this.done();
				$this.loading = false;
				$this.success(d.body.innerHTML);
				
			};
			i.onload = testing;
		}
	},
	
	/**
	 * aborts the upload
	*/
	abort: function() {	
		
		this.isAbort = true;
		if(this.frameUpload) {
			$("#frame_" + this.frameID).remove();
			this.placeHandler();
		} else {
			this.xhr.abort();
		}
		this.isAbort = false;
		
		this.loading = false;
		this.cancel();
		
	},
	
	/**
	 * placed the file-input over the browse-button
	*/
	placeHandler: function() {
		var $this = this;
		// now create the form
		if($("#" + this.formelement.attr("id") + "_uploadForm").length == 0) {
			$("body").append('<form id="' + this.formelement.attr("id")+'_uploadForm" style="position: absolute; left: -500px;" method="post" action="'+this.url+'/frameUpload/" enctype="multipart/form-data"><input name="file" style="height: 50px; width: 200px;" type="file" class="fileSelectInput" /></form>');
			$("#" + this.formelement.attr("id") + "_uploadForm").find(".fileSelectInput").change(function(){
				$this.change(this);
			});
		}
		
		var $form = $("#" + this.formelement.attr("id") + "_uploadForm");
		$form.css("display", "block");
		$form.css({top: this.browse.offset().top, left: this.browse.offset().left});
		$form.fadeTo(0,0);
	},
	
	/**
	 * placed the file-input out of the document
	*/
	hideHandler: function() {
		var $form = $("#" + this.formelement.attr("id") + "_uploadForm");
		$form.css({top: - 100, left: this.browse.offset().left});
	}
};