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
			
			var ajaxUpload = new AjaxUpload(editor, {
				ajaxurl: url,
				uploadStarted: function(index, upload) {
					try {
						if(upload.fileName.match(/\.(jpg|jpeg|png|gif)$/i)) {
							editor.insertHtml('<span id="upload_'+index+'" class="img"><i>'+lang("loading")+'</i></span>');
						} else {
							editor.insertHtml('<a href="#" width="" height="" id="upload_'+index+'">'+upload.fileName+' <i id="upload_loading_'+index+'">'+lang("loading")+'</i></a>');
						}
					} catch(e) {
						alert(e);
					}
				},
				done: function(response, index) {
					var imageUrl = response.match(/2,\s*'(.*?)',/)[1];

					var theObject = editor.document.getById( "upload_" + index );
					console.log(editor);

					if(theObject.hasClass("img")) {
						theObject.remove();
						editor.insertHtml('<img src="'+imageUrl+'" alt="'+imageUrl+'" />');
					} else {
						theObject.setAttribute( 'href', imageUrl);
						theObject.data( 'cke-saved-href', imageUrl);
						editor.document.getById( "upload_loading_" + index ).remove();
					}
				}
			});
		}

		editor.on('contentDom', function (e) {
			ajaxUpload.DropZone = editor.document.getBody();
			
			// now bind events to dropzone
			editor.document.on("dragenter", function(event){
				ajaxUpload._dragEnter(event.data.$);
			});
			editor.document.on("dragover", function(event){
				ajaxUpload._dragOver(event.data.$);
			});
			editor.document.on("dragleave", function(event){
				ajaxUpload._dragLeaveDocument(event.data.$);
			});
			
			editor.document.on("drop", function(ev) {
				ajaxUpload._drop(ev.data.$);
			});
		});
	
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