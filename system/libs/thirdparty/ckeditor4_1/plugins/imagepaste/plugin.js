/*
 * @file image paste plugin for CKEditor
	Feature introduced in: https://bugzilla.mozilla.org/show_bug.cgi?id=490879
	doesn't include images inside HTML (paste from word): https://bugzilla.mozilla.org/show_bug.cgi?id=665341
 * Copyright (C) 2011-13 Alfonso Martínez de Lizarrondo
 *
 * edited by Daniel Gruber, changes licensed under the same licenses as below
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 */

 // Handles image pasting in Firefox
CKEDITOR.plugins.add( 'imagepaste',
{
	init : function( editor )
	{

		// Paste from clipboard:
		editor.on( 'paste', function(e) {
			var data = e.data,
				html = (data.html || ( data.type && data.type=='html' && data.dataValue));
			if (!html)
				return;

			// strip out webkit-fake-url as they are useless:
			if (CKEDITOR.env.webkit && (html.indexOf("webkit-fake-url")>0) )
			{
				//alert("Sorry, the images pasted with Safari aren't usable");
				//window.open("https://bugs.webkit.org/show_bug.cgi?id=49141");
				html = html.replace( /<img src="webkit-fake-url:.*?">/g, "<img src='' alt='Upload Your Image here' width='100' height='50' />");
			}

			// Replace data: images in Firefox and upload them
			html = html.replace( /<img src="data:image\/png;base64,.*?" alt="">/g, function( img )
				{
					var data = img.match(/"data:image\/png;base64,(.*?)"/)[1];
					var id = CKEDITOR.tools.getNextId();

					var url= editor.config.filebrowserImageUploadUrl + '&CKEditor=' + editor.name + '&CKEditorFuncNum=2&langCode=' + editor.langCode;

					var xhr = new XMLHttpRequest();

					xhr.open("POST", url);
					xhr.onload = function() {
						// Upon finish, get the url and update the file
						var imageUrl = xhr.responseText.match(/2,\s*'(.*?)',/)[1];
						var theImage = editor.document.getById( id );
						
						theImage.data( 'cke-saved-src', imageUrl);
						theImage.setAttribute( 'src', imageUrl);
						theImage.removeAttribute( 'id' );
					}

					// Create the multipart data upload. Is it possible somehow to use FormData instead?
					var BOUNDARY = "---------------------------1966284435497298061834782736";
					var rn = "\r\n";
					var req = "--" + BOUNDARY;

					  req += rn + "Content-Disposition: form-data; name=\"upload\"";

						var bin = window.atob( data );
						// add timestamp?
						req += "; filename=\"" + id + ".png\"" + rn + "Content-type: image/png";

						req += rn + rn + bin + rn + "--" + BOUNDARY;

					req += "--";

					xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + BOUNDARY);
					xhr.sendAsBinary(req);

					return img.replace(/>/, ' id="' + id + '">')

				});

			if (e.data.html)
				e.data.html = html;
			else
				e.data.dataValue = html;
		});

		/**
		* drag'n'drop
		*/
		
		if(editor.config.filebrowserUploadUrl) {
			var url= editor.config.filebrowserUploadUrl + '&CKEditor=' + editor.name + '&CKEditorFuncNum=2&langCode=' + editor.langCode;
			new CKAjaxUpload(editor, {
				ajaxurl: url,
				uploadStarted: function(index, upload) {
					try {
						if(upload.fileName.match(/\.(jpg|jpeg|png|gif)$/i)) {
							editor.insertHtml('<img src="images/16x16/loading.png" width="" height="" id="upload_'+index+'" />');
							
	 						if(FileReader !== undefined) { 
								var reader = new FileReader();
	      
							    //attach event handlers here...
							    reader.onloadend = function() {
								  	var theImage = editor.document.getById( "upload_" + index );
								  	
								  	var img = new Image();
								  	img.onload = function() {
									  	if(this.width > 1000) {
										  	theImage.setAttribute("width", 1000 + "px");
										  	theImage.setAttribute("height", this.height / this.width * 1000 + "px");
									  	}
								  	};
								  	
								  	img.src = this.result;
								  	
								  	theImage.setAttribute( 'src', this.result);
							    };
							   
							    reader.readAsDataURL(upload.fileObj);
							}
						} else {
							editor.insertHtml('<a href="#" width="" height="" id="upload_'+index+'">'+upload.fileName+' <i id="upload_loading_'+index+'">Loading...</i></a>');
						}
					} catch(e) {
						alert(e);
					}
				},
				done: function(response, index) {
					var imageUrl = response.match(/2,\s*'(.*?)',/)[1];
					var theObject = editor.document.getById( "upload_" + index );
					
					if(theObject.getName() == "img") {
						theObject.setAttribute( 'src', imageUrl);
						theObject.data( 'cke-saved-src', imageUrl);
					} else {
						theObject.setAttribute( 'href', imageUrl);
						theObject.data( 'cke-saved-href', imageUrl);
						editor.document.getById( "upload_loading_" + index ).remove();
					}
				}
			});
		}
		/*var dragEnter = function (event) {                 
             editor.document.getBody().addClass("drag-enter");
        },
        dragLeave = function() {
	        editor.document.getBody().removeClass("drag-enter");
        },
        dragOver = function() {
	        
        }, 
        drop = function(f) {
        	editor.document.getBody().removeClass("drag-enter");
        	var a = f.data.$.dataTransfer;
	        f.data.preventDefault();
            return false;
        };

        editor.on('contentDom', function (e) {                
            editor.document.on('dragenter', dragEnter);
            editor.document.on('dragleave', dragLeave);
            editor.document.on('dragover', dragOver);
            editor.document.on('drop', drop);
            // editor.document.on('drop', onDrop);
            // etc.
        });*/
		
	} //Init
} );


/**
  * ajax fileupload for ckeditor
  * 
  * thanks to https://github.com/pangratz/dnd-file-upload/blob/master/jquery.dnd-file-upload.js
  * @package goma
  * @link http://goma-cms.org
  * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  * @author Goma-Team
  * last modified: 09.10.2013
  * $Version 2.0.5
*/

var CKAjaxUpload = function(editor, options) {
	this.editor = editor;
	this.DropZone = null;
	this.url = location.href;
	this.ajaxurl = location.href;
	this["multiple"] = true;
	this.id = randomString(10);
	this.max_size = -1;
	for(i in options) {
		if(typeof options[i] != "undefined")
			this[i] = options[i];
	}
	
	var $this =  this;
	
	
	// bind events on document to stop the browser showing the file, if the user does not hit the dropzone
	$(document).on("dragenter", function(event){
		$this.dragEnterDocument(event);
		return $this._dragInDocument(event);
	});
	
	$(document).on("dragleave", function(event){
		return $this._dragLeaveDocument(event);
	});
	
	$(document).on("dragover", function(event){
		return $this._dragInDocument(event);
	});
	
	if(document.addEventListener) {
		document.addEventListener("drop", function(event){
			var dt = event.dataTransfer;
			if(dt.files) {
				
				event.stopPropagation();
				event.preventDefault();
				return false;
			}

			return $this._dragLeaveDocument(event);
		});
	}
	
	editor.on('contentDom', function (e) {
		$this.DropZone = editor.document.getBody();
		
		// now bind events to dropzone
		editor.document.on("dragenter", function(event){
			$this._dragEnter(event.data.$);
		});
		editor.document.on("dragover", function(event){
			$this._dragOver(event.data.$);
		});
		editor.document.on("dragleave", function(event){
			$this._dragLeaveDocument(event.data.$);
		});
		
		editor.document.on("drop", function(ev) {
			$this._drop(ev.data.$);
		});
	});
	
	return this;
}

CKAjaxUpload.prototype = {
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
	
	failSize: function(index) {
		
	},
	
	failExt: function(index) {
		
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
		
		this.dragInDocument(event);
		
		
		/*event.stopPropagation();
		event.preventDefault();
		return false;*/
	},
	
	/**
	 * when a user leaves the document with the file
	 *
	 *@name _dragLeaveDocument
	*/
	_dragLeaveDocument: function(event) {
		
		this.dragLeaveDocument(event);
		
		
		/*event.stopPropagation();
		event.preventDefault();
		return false;*/
	},
	
	/**
	 * when the user drags the file in the dropzone
	 *
	 *@name _dragEnter
	*/
	_dragEnter: function(event) {
		
		this.dragEnter(event);
		this.dragOver(event);
		
		/*event.stopPropagation();
		event.preventDefault();
		return false;*/
	},
	
	/**
	 * when the user drags the file within the dropzone
	 *
	 *@name _dragOver
	*/
	_dragOver: function(event) {
		
		this.dragOver(event);
		
		/*event.stopPropagation();
		event.preventDefault();
		return false;*/
	},
	
	/**
	 * when the user drops the file
	 *
	 *@name _drop
	*/
	_drop: function(event) {
		
		this.dragLeaveDocument();
		
		var dt = event.dataTransfer;
		if(dt.types && dt.types == "Files" && dt.files) {
			this.newFilesDropped();
			
			var files = dt.files;
			this.transferAjax(files);
			
			event.stopPropagation();
			event.preventDefault();
			return false;
		}
	
		return true;
	},
	
	/**
	 * is always called when transfer is completed, regardless whether succeeded or not
	 *
	 *@name _complete
	*/
	_complete: function(event, upload, fileIndex) {
		var now = new Date().getTime();
		var timeDiff = now - upload.downloadStartTime;
		
		this.queue[fileIndex].loading = false;
		this.queue[fileIndex].loaded = true;
		
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
			
			// update for percentage only every percent
			if (upload.currentProgress != percentage) {

				// log(this.fileIndex + " --> " + percentage + "%");

				upload.currentProgress = percentage;
				this.progressUpdate(upload.fileIndex, upload.fileObj, upload.currentProgress);
			}
			
			// update speed
			var elapsed = new Date().getTime();
			var diffTime = elapsed - upload.currentStart;
			if (diffTime >= this.uploadRateRefreshTime) {
				var diffData = event.loaded - upload.startData;
				var speed = diffData / diffTime; // in KB/sec
				
				this.speedUpdate(upload.fileIndex, upload.fileObj, speed);

				return elapsed;
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
		upload.fileSize = file.fileSize;
		
		// add listeners
		upload.addEventListener("progress", function(event){
			if(currentStart = $this._progress(event, this)) {
				this.startData = event.loaded;
				this.currentStart = currentStart;
			}
		}, false);

		xhr.open("PUT", this.ajaxurl);
		xhr.setRequestHeader("Cache-Control", "no-cache");
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhr.setRequestHeader("X-File-Name", file.fileName);
		xhr.setRequestHeader("X-File-Size", file.fileSize);
		xhr.setRequestHeader("content-type", "application/octet-stream");
		
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
				if(this.max_size == -1 || typeof this.queue[i].upload.fileSize == "undefined" || this.queue[i].upload.fileSize <= this.max_size) {
					this.queue[i].loading = true;
					this.queue[i].send();
					this.uploadStarted(i, this.queue[i].upload);
				} else {
					this.abort(i);
					this.failSize(i);
				}
			}
		}
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
		} else {
			if(typeof this.queue[fileIndex] != "undefined") {
				this.queue[fileIndex].abort();
				this.cancel(fileIndex);
				this._complete(this, this.queue[fileIndex].upload, fileIndex);
				this.queue[fileIndex].loading = false;
				this.queue[fileIndex].loaded = true;
			}
		} 
		
	}
};

