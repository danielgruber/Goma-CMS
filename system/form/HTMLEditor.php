<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 20.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLEditor extends Textarea
{
		public function field()
		{
				$this->callExtending("beforeField");
				
				$this->setValue();
				
				$this->container->append(new HTMLNode("label", array(
					'for'	=> $this->ID(),"style"	=> "display: block;"
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
		public function JS()
		{
				Resources::addData('var CKEDITOR_BASEPATH = "'.BASE_URI.'system/libs/thirdparty/ckeditor/";');
				Resources::add("system/libs/thirdparty/ckeditor/ckeditor.js", "js");
				Resources::add("system/libs/ckeditor_goma/pagelinks.js", "js");
				Resources::add("ckeditor_goma.css", "css");
				Resources::addData("var lang_page = '".lang("page")."';");

				$js = '
$(function(){
	// apple bug with contenteditable of iOS 4 and lower
	// firefox 3 and above are supported, otherwise dont load up
	if((!isIDevice() || isiOS5()) && (getFirefoxVersion() > 2 || getFirefoxVersion() == -1)) {
		setTimeout(function(){
			
			if(CKEDITOR.instances.'.$this->input->id.' != null) CKEDITOR.remove(CKEDITOR.instances.'.$this->input->id.');
			CKEDITOR.replace("'.$this->input->id.'", {
        		toolbar : "Goma",
        		resize_enabled: false,
        		language: "'.Core::getCMSVar("lang").'",
        		baseHref: "'.BASE_URI.'",
        		contentsCss: "'.BASE_URI . 'tpl/' .  Core::getTheme().'/typography.css",
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
    		resize_enabled: false,
    		language: "'.Core::getCMSVar("lang").'",
    		baseHref: "'.BASE_URI.'",
    		contentsCss: "'.BASE_URI . 'tpl/' .  Core::getTheme().'/typography.css",
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