<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 01.12.2011
  * $Version 1.2.4
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLEditor extends Textarea
{
		/**
		 * generates the field
		 *
		 *@name field
		 *@access public
		*/
		public function field()
		{
				$this->callExtending("beforeField");
				
				$this->setValue();
				
				$this->container->append(new HTMLNode("label", array(
					'for'	=> $this->ID()
				), $this->title));
				
				$this->container->append(array(
					new HTMLNode("a", array(
						'href'		=> 'javascript:;',
						'onclick'	=> 'toggleEditor_'.$this->input->id.'()',
						"style"		=> "display: none;",
						"class"		=> "editor_toggle"
					), lang("editor_toggle", "Toggle Editor"))
				));
				
				$this->container->append($this->input);
				
				$this->callExtending("afterRender");
				
				return $this->container;
		}
		
		/**
		 * generates the JavaScript
		 *
		 *@name JS
		 *@access public
		*/
		public function JS()
		{
				if($this->width == "100%") {
					$width = "";
				} else if(strpos($this->width, "px") && (int) $this->width < 350) {
					$width = "350px";
				} else if($this->width < 350) {
					$width = "350px";
				} else {
					$width = $this->width;
				}
				
				Resources::addData('var CKEDITOR_BASEPATH = "'.BASE_URI.'system/libs/thirdparty/ckeditor4/";');
				Resources::add("system/libs/thirdparty/ckeditor4/ckeditor.js", "js");
				Resources::add("system/libs/ckeditor_goma/pagelinks.js", "js");
				Resources::add("ckeditor_goma.css", "css");
				Resources::addData("var lang_page = '".lang("page")."';");
				
				$accessToken = randomString(20);
				$_SESSION["uploadTokens"][$accessToken] = true;
				
				$js = '
var bindIEClickPatch = function() {
	if(getInternetExplorerVersion() != -1) {
		$(document).on("click", "a.cke_dialog_ui_button", function(){
			self.leave_check = true;
			setTimeout(function(){
				self.leave_check = false;
			}, 100);
		});
	}
}

$(function(){
	// apple bug with contenteditable of iOS 4 and lower
	// firefox 3 and above are supported, otherwise dont load up
	if((!isIDevice() || isiOS5()) && (getFirefoxVersion() > 2 || getFirefoxVersion() == -1)) {
		bindIEClickPatch();
		setTimeout(function(){
			
			if(CKEDITOR.instances.'.$this->input->id.' != null) CKEDITOR.remove(CKEDITOR.instances.'.$this->input->id.');
			CKEDITOR.replace("'.$this->input->id.'", {
        		toolbar : "Goma",
        		language: "'.Core::getCMSVar("lang").'",
        		baseHref: "'.BASE_URI.'",
        		contentsCss: "'.BASE_URI . 'tpl/' .  Core::getTheme().'/editor.css",
        		filebrowserUploadUrl : "'.BASE_URI . BASE_SCRIPT.'/system/ck_uploader/?accessToken='.$accessToken.'",
        		width: "'.$width.'",
        		resize_dir: "vertical",
        		autoGrow_maxHeight: $(document).height() - 300
    		});
    		CKEDITOR.instances.'.$this->input->id.'.on("focus", function(){
				self.leave_check = false;
			});
		}, 100);
		
		
		$("#'.$this->form()->ID().'").bind("beforesubmit",function(){
			$("#'.$this->input->id.'").val(CKEDITOR.instances.'.$this->input->id.'.getData());
		});
		$("#'.$this->input->id.'").change(function(){
			
			CKEDITOR.instances.'.$this->input->id.'.setData($("#'.$this->input->id.'").val());
		});
		$(".editor_toggle").css("display", "block");
	}
});
window.toggleEditor_'.$this->input->id.' = function() {
	if(CKEDITOR.instances["'.$this->input->id.'"] != null) {
		CKEDITOR.instances["'.$this->input->id.'"].destroy();
	} else {
		CKEDITOR.replace("'.$this->input->id.'", {
    		toolbar : "Goma",
    		language: "'.Core::getCMSVar("lang").'",
    		baseHref: "'.BASE_URI.'",
    		contentsCss: "'.BASE_URI . 'tpl/' .  Core::getTheme().'/typography.css",
    		filebrowserUploadUrl : "'.BASE_URI . BASE_SCRIPT.'/system/ck_uploader/",
        	width: "'.$width.'",
        	resize_dir: "vertical",
        	autoGrow_maxHeight : $(document).height() - 300
		});
		CKEDITOR.instances.'.$this->input->id.'.on("focus", function(){
			self.leave_check = false;
		});
	}
		
}
						';
				return $js;
		}
}