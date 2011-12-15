/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.04.2011
*/ 

$(function(){
		var settings_object = { 
					flash_url : "adm/tpl/" + self.atpl + "/filemanager/swfupload/swfupload.swf",
					upload_url: self.ROOT_PATH + BASE_SCRIPT + "filemanager/upload/",
					post_params: {"PHPSESSID" : self.sessid, 'base_dir': self.base_dir, 'OPTIONS': self.options},
					file_types : "*.*",
					file_types_description : "All Files",
					file_upload_limit : 100,
					file_queue_limit : 0,
					custom_settings : {
						progressTarget : "fsUploadProgress",
						cancelButtonId : "btnCancel"
					},
					debug: false,
					file_post_name : "file", 
					// Button settings
					button_width: "65",
					button_height: "29",
					button_placeholder_id: "buttonupload",
					button_text: ""+self.lang.upload+"",

					button_text_left_padding: 12,
					button_text_top_padding: 3,
					
					// The event handler functions are defined in handlers.js
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,
					upload_start_handler : uploadStart,
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : uploadSuccess,
					upload_complete_handler : uploadComplete,
					queue_complete_handler : queueComplete	// Queue plugin event
					

	 }; 
	 var swfu = new SWFUpload(settings_object); 
});