<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 22.12.2011
  * $Version 1.2.6
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
				if(ClassInfo::exists("pages")) Resources::add("system/libs/ckeditor_goma/pagelinks.js", "js");
				Resources::add("ckeditor_goma.css", "css");
				Resources::addData("var lang_page = '".lang("page")."';");
				
				$accessToken = randomString(20);
				$_SESSION["uploadTokens"][$accessToken] = true;
				Resources::addData("var CKEditor_Upload_Path = ".var_export(BASE_URI . BASE_SCRIPT.'/system/ck_uploader/?accessToken='.$accessToken, true)."; var CKEditor_ImageUpload_Path = ".var_export(BASE_URI . BASE_SCRIPT.'/system/ck_imageuploader/?accessToken='.$accessToken, true).";");
				
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
        		contentsCss: "'.self::buildEditorCSS().'",
        		filebrowserUploadUrl: self.CKEditor_Upload_Path,
        		filebrowserImageUploadUrl : self.CKEditor_ImageUpload_Path,
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
    		contentsCss: "'.self::buildEditorCSS().'",
    		filebrowserUploadUrl: self.CKEditor_Upload_Path,
        	filebrowserImageUploadUrl : self.CKEditor_ImageUpload_Path,
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
		
	/**
	 * builds editor.css
	 *
	 *@name buildEditorCSS
	*/
	public function buildEditorCSS() {
		$cache = CACHE_DIRECTORY . "/htmleditor_compare_" . Core::GetTheme() . ".css";
		if(/*(!file_exists($cache) || filemtime($cache) < TIME + 300) && */file_exists("tpl/" . Core::getTheme() . "/editor.css")) {
			$css = self::importCSS("tpl/" . Core::getTheme() . "/editor.css");
			
			// parse CSS
			//$css = preg_replace_callback('/([\.a-zA-Z0-9_\-,#\>\s\:\[\]\=]+)\s*{/Usi', array("historyController", "interpretCSS"), $css);
			FileSystem::write($cache, $css);
			
			return BASE_URI . $cache;
		} else {
			return false;
		}
	}	
	
	/**
	 * gets a consolidated CSS-File, where imports are merged with original file
	 *
	 *@name importCSS
	 *@param string - file
	*/
	public static function importCSS($file) {
		if(file_exists($file)) {
			$css = file_get_contents($file);
			// import imports
			preg_match_all('/\@import\s*url\(("|\')([^"\']+)("|\')\)\;/Usi', $css, $m);
			foreach($m[2] as $key => $_file) {
				$css = str_replace($m[0][$key], self::importCSS(dirname($file) . "/" . $_file), $css);
			}
			
			return $css;
		}
		
		return "";
	}
}
