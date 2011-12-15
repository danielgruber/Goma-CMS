<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 06.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class BBcodeEditor extends HTMLEditor
{
		public function JS()
		{
				Resources::addData('var CKEDITOR_BASEPATH = "'.BASE_URI.'system/libs/thirdparty/ckeditor/";');
				Resources::add("system/libs/thirdparty/ckeditor/ckeditor.js", "js");

				$js = '
$(function(){
	// apple bug with contenteditable of iOS 4 and lower
	// firefox 3 and above are supported, otherwise dont load up
	if((!isIDevice() || isiOS5()) && (getFirefoxVersion() > 2 || getFirefoxVersion() == -1)) {
		// we need this timeout for some ajax-document-load
		setTimeout(function(){
			
			
			if(CKEDITOR.instances.'.$this->input->id.' != null) CKEDITOR.remove(CKEDITOR.instances.'.$this->input->id.');
			CKEDITOR.replace("'.$this->input->id.'", {
        		extraPlugins : "bbcode",
				toolbar :
				[
					["Source", "-","Undo","Redo"],
					["Find","Replace","-","SelectAll","RemoveFormat"],
					["Link", "Unlink", "Image"],
					"/",
					["Bold", "Italic","Underline"],
					["NumberedList","BulletedList","-","Blockquote"],
					["TextColor", "-", "Smiley","SpecialChar", "-", "Maximize"]
				],
        		resize_enabled: false,
        		language: "'.Core::getCMSVar("lang").'",
        		baseHref: "'.BASE_URI.'",
        		contentsCss: "'.BASE_URI . 'tpl/' .  Core::getTheme().'/typography.css"
    		});
    		
			$("#'.$this->form()->ID().'").bind("beforesubmit",function(){
				$("#'.$this->input->id.'").val(CKEDITOR.instances.'.$this->input->id.'.getData());
			});
			$("#'.$this->input->id.'").change(function(){
				CKEDITOR.instances.'.$this->input->id.'.setData($("#'.$this->input->id.'").val());
			});
		}, 100);
		$(".editor_toggle").css("display", "block");
	}
});
window.toggleEditor_'.$this->input->id.' = function() {
	if(CKEDITOR.instances["'.$this->input->id.'"] != null) {
		CKEDITOR.instances["'.$this->input->id.'"].destroy();
	} else {
		CKEDITOR.replace("'.$this->input->id.'", {
    		extraPlugins : "bbcode",
			toolbar :
			[
				["Source", "-","Undo","Redo"],
				["Find","Replace","-","SelectAll","RemoveFormat"],
				["Link", "Unlink", "Image"],
				"/",
				["Bold", "Italic","Underline"],
				["NumberedList","BulletedList","-","Blockquote"],
				["TextColor", "-", "Smiley","SpecialChar", "-", "Maximize"]
			],
    		resize_enabled: false,
    		language: "'.Core::getCMSVar("lang").'",
    		baseHref: "'.BASE_URI.'",
    		contentsCss: "'.BASE_URI . 'tpl/' .  Core::getTheme().'/typography.css"
		});
	}
		
}


						';
				return $js;
		}
}