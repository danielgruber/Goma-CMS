/**
 * The JS for FileUpload-Fields.
 *
 * @author Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package Goma\Form
 * @version 1.1
 */
function FileUpload(form, field, formelement, url, size, types) {
	var $this = this;

	this.form = form;
	this.field = field;

	field.fileUpload = this;
	this.url = url;

	this.formelement = $(formelement);
	this.element = $(formelement).find(".icon").get(0);
	this.destInput = $(formelement).find(".FileUploadValue");
	this.defaultIcon = field.defaultIcon;

	this.actions = this.formelement.find(".actions");
	// the info-zone
	this.infoZone = this.formelement.find(".progress_info");
	
	// append fallback for not drag'n'drop-browsers
	this.browse = this.formelement.find(".fileSelect");

	this.deleteButton = this.formelement.find(".delete-file-button");
	this.deleteButton.click(function(){
		this.uploader.updateFile(null);
		return false;
	}.bind(this));
	
	this.uploader = new AjaxUpload("#" + this.element.id, {
		url: url + "/frameUpload/",
		ajaxurl: url + "/ajaxUpload/",
		browse: this.browse,
		
		max_size: size,
		
		allowed_types: types,
		
		// events
		uploadStarted: function() {
			var that = this;
			$this.infoZone.html('<div class="progressbar"><div class="progress"></div><span><img src="images/16x16/loading.gif" alt="Uploading.." /></span><div class="cancel"></div></div>');
			$this.infoZone.find(".cancel").click(function(){
				that.abort();
			});
			$($this.element).append('<div class="loading"></div>');
		},
		dragInDocument: function() {
			$($this.element).addClass("active");
		},
		dragLeaveDocument: function() {
			$($this.element).removeClass("active");
			$($this.element).removeClass("beforeDrop");
		},
		
		dragOver: function() {
			$($this.element).addClass("active");
			$($this.element).addClass("beforeDrop");
		},
		
		/**
		 * called when the speed was updated, just for ajax-upload
		*/
		speedUpdate: function(fileIndex, file, KBperSecond) {
			var ext = "KB/s";
			KBperSecond = Math.round(KBperSecond);
			if(KBperSecond > 1000) {
				KBperSecond = Math.round(KBperSecond / 1000, 2);
				ext = "MB/s";
			}
			$this.infoZone.find("span").html(KBperSecond + ext);
		},
		
		/**
		 * called when the progress was updated, just for ajax-upload
		*/
		progressUpdate: function(fileIndex, file, newProgress) {
			$this.infoZone.find(".progress").stop().animate({width: newProgress + "%"}, 500);
		},
		
		/**
		 * event is called when the upload is done
		*/
		always: function() {
			$this.infoZone.find("span").html("100%");
			$this.infoZone.find(".progress").css("width", "100%");
			setTimeout(function(){
				$this.infoZone.find(".progressbar").slideUp("fast", function(){
					$this.infoZone.html("");
				});
			}, 1000);
			
			$($this.element).find(".loading").remove();
		},
		
		/**
		 * method which is called, when we receive the response
		*/
		done: function(html) {
			try {
				var data = eval('('+html+');');
				this.updateFile(data);
			} catch(err) {
				if(this.isAbort) {
				
				} else {
					$this.infoZone.html('<div class="error">An Error occured. '+err+'</div>');
				}
			}
		},

		updateFile: function(data) {
			if(data == null) {
				$this.formelement.find("input.FileUploadValue").val("");
				$($this.element).find("img").attr({
					"src": $this.defaultIcon,
					"alt": "",
					"style": ""
				});
				$($this.element).find("a").removeAttr("href");
				$($this.element).find("span").html("");

				$this.field.upload = null;
			} else if(data.status == 0) {
				$this.infoZone.html('<div class="error">'+data.errstring+'</div>');
			} else {
				$this.field.upload = data.file;
				if(data.file["icon128"]) {
					if(window.devicePixelRatio > 1.5 && data.file["icon128@2x"]) {
						this.updateIcon(data.file["icon128@2x"]);
					} else {
						this.updateIcon(data.file.icon128);
					}
				} else {
					this.updateIcon(data.file.icon);
				}
				if(data.file.path)
					$($this.element).find("a").attr("href", data.file.path);
				else
					$($this.element).find("a").removeAttr("href");
				$($this.element).find("span").html(data.file.name);
				$this.destInput.val(data.file.realpath);
			}
		},

		updateIcon: function(icon) {
			$($this.element).find("img").attr("src", icon);
		},
		
		failSize: function(i) {
			$this.infoZone.html('<div class="error">'+lang("files.filesize_failure")+'</div>');
		},
		
		failExt: function() {
			$this.infoZone.html('<div class="error">'+lang("files.filetype_failure")+'</div>');
		}
		
	});
	
	// now hide original file-upload-field
	this.formelement.find(".no-js-fallback").css("display", "none");
	
	return this;
}
