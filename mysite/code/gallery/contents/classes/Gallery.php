<?php

/**
 *@author Daniel Gruber
 *@copyright 2012 - Daniel Gruber
 *@licensed for: MBG
*/

defined("IN_GOMA") OR die();

class Galleries extends Page {
	/**
	 * category-icon
	*/ 
	static public $icon = "images/icons/goma/16x16/image.png";
	
	/**
	 * title in admin-panel
	*/
	public $name = '{$_lang_exp_gallery.galleries}';
	
	/**
	 * allow the following children
	*/
	public $allowed_children = array(
		"gallery", "galleries"
	);
	
	/**
	 * allow the following parents
	*/
	public $can_parent = array(
		"galleries", "pages", "page", "SlideShowPage", "boxpage"
	);
	
	/**
	 * generates the form
	 *
	 *@name getForm
	 *@access public
	*/
	public function getForm(&$form) {
		parent::getForm($form);
		
		$form->add(new HTMLEditor("data", lang("description")),0, "content");
	}
	
	public function images() {
		if(!$this->children)
			return false;
		
		if(!$this->children()->images)
			return false;
		
		return $this->children()->images();
	}
}

class GalleriesController extends PageController {
	public $template = "gallery/galleries.html";
}

class Gallery extends Page {
	/**
	 * category-icon
	*/ 
	static public $icon = "images/icons/goma/16x16/image.png";
	
	/**
	 * name of this page-model
	*/
	public $name = '{$_lang_exp_gallery.gallery}';
	
	/**
	 * images
	*/
	public $many_many = array(
		"images"	=> "ImageUploads"
	);
	
	/**
	 * root-image
	*/
	public $has_one = array(
		"rootimage"	=> "ImageUploads"
	);
	
	public $can_parent = array(
		"galleries",
		"page",
		"boxpage",
		"slideshowpage"
	);
	
	/**
	 * generates the form
	 *
	 *@name getForm
	 *@access public
	*/
	public function getForm(&$form) {
		parent::getForm($form);
		
		$form->add(new HTMLEditor("data", lang("description")),0, "content");
		
		$form->add(new Tab("imagestab", array(
			new FileUpload("rootimage", lang("exp_gallery.rootimage"), array("jpg", "png", "jpeg", "gif"), null, "gallery"),
			new FileUploadSet("images", lang("exp_gallery.images"), array("jpg", "png", "jpeg", "gif"), null, "gallery")
		), lang("exp_gallery.images")), 0, "tabs");
	}
}

class GalleryController extends PageController {
	public $template = "gallery/gallery.html";
}