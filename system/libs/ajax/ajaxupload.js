(function( $ ){
	var lang_wrong_filetype = (self.lang_wrong_filetypes == null) ? "Please upload a file with the correct filetype." : self.lang_wrong_filetypes;
	$.fn.goma_ajax_upload() = function(options) {
	
		var defaults = {
			"filetypes"		: /^.+$/Usi,
			"path"	   		: "server/uploadserver",
			"reshandler"	: function(response) {
				alert(response);
			}
		};
		var o = $.extend(defaults, options);
		this.each(function(){
			var element = $(this).get(0);
			element.addEventListener("dragenter", function(e) {
				e.stopPropagation();  
  				e.preventDefault(); 
			}, false);
			element.addEventListener("dragenter", function(e) {
				e.stopPropagation();  
  				e.preventDefault(); 
			}, false);
			element.addEventListener("drop", function(e) {
				e.stopPropagation();  
  				e.preventDefault();  
  
  				var dt = e.dataTransfer;  
  				var files = dt.files;  
  
  				for(i in files) {
  					var file = files[i];
  					
  					if(o.filetypes.test(file)) {
  						var reader = new FileReader();
    					reader.onload = (function(aImg) { return function(e) { aImg.src = e.target.result; }; })(img);
    					reader.readAsDataURL(file);
  					} else {
  						
  					}
  				}
			}, false);
			
			
		});
		
		function upload(event) {
		    
		    var data = event.dataTransfer;
		
		    var boundary = '------multipartformboundary' + (new Date).getTime();
		    var dashdash = '--';
		    var crlf     = '\r\n';
		
		    /* Build RFC2388 string. */
		    var builder = '';
		
		    builder += dashdash;
		    builder += boundary;
		    builder += crlf;
		    
		    var xhr = new XMLHttpRequest();
		    
		    /* For each dropped file. */
		    for (var i = 0; i < data.files.length; i++) {
		        var file = data.files[i];
		
		        /* Generate headers. */            
		        builder += 'Content-Disposition: form-data; name="user_file[]"';
		        if (file.fileName) {
		          builder += '; filename="' + file.fileName + '"';
		        }
		        builder += crlf;
		
		        builder += 'Content-Type: application/octet-stream';
		        builder += crlf;
		        builder += crlf; 
		
		        /* Append binary data. */
		        builder += file.getAsBinary();
		        builder += crlf;
		
		        /* Write boundary. */
		        builder += dashdash;
		        builder += boundary;
		        builder += crlf;
		    }
		    
		    /* Mark end of the request. */
		    builder += dashdash;
		    builder += boundary;
		    builder += dashdash;
		    builder += crlf;
		
		    xhr.open("POST", o.path, true);
		    xhr.setRequestHeader('content-type', 'multipart/form-data; boundary=' 
		        + boundary);
		    xhr.sendAsBinary(builder);        
		    
		    xhr.onload = function(event) { 
		        /* If we got an error display it. */
		        if (xhr.responseText) {
		            alert(xhr.responseText);
		        }
		        $("#dropzone").load("list.php?random=" +  (new Date).getTime());
		    };
		    
		    /* Prevent FireFox opening the dragged file. */
		    event.stopPropagation();
		    
		}
		
		
	};
})( jQuery );