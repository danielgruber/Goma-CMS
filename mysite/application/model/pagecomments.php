<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 13.07.2012
  * $Version 1.1.4
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang('comments');

class PageComments extends DataObject
{

		public $db_fields = array('name' 		=> 'varchar(200)',
								  'text'        => 'text',
								  'timestamp'   => 'int(200)');
								  
		/**
		 * has-one-relation to page
		 *
		 *@name has_one
		 *@access public
		*/
		public $has_one = array('page' => 'pages'); // has one page
		
		/**
		 * sort
		 *
		 *@name default_sort
		 *@access public
		*/
		public static $default_sort = "timestamp DESC";
		
		/**
		 * rights
		*/
		public $writeField = "autorid";
		
		/**
		 * indexes for faster look-ups
		*/
		public $indexes = array("name" => true);
		
		/**
		 * insert is always okay
		*/ 
		public function canInsert() {
			return true;
		}
		
		/**
		 * generates the form
		 *
		 *@name getForm
		 *@access public
		*/
		public function getForm(&$form)
		{
				if(member::$nickname)
				{
						$form->add(new HiddenField("name", member::$nickname));
				} else
				{
						$form->add(new TextField("name", lang("name", "Name")));
				}
				$form->add(new TimeField("timestamp"));
				$form->add(new BBCodeEditor("text", lang("text", "text"), null, null, null, array("showAlign" => false)));
				if(!member::login())
					$form->add(new Captcha("captcha"));
				$form->addAction(new AjaxSubmitButton("save", lang("co_add_comment", "add comment"),  "ajaxsave","safe"));
				$form->addValidator(new RequiredFields(array("text", "name", "captcha")), "fields");
		}
		
		/**
		 * edit-form
		 *
		 *@name getEditForm
		 *@access public
		*/
		public function getEditForm(&$form)
		{
				$form->add(new HTMLField("heading", "<h3>".lang("co_edit", "edit comments")."</h3>"));
				$form->add(new BBCodeEditor("text", lang("text", "text")));
				
				$form->addAction(new FormAction("save", lang("save", "save")));
				$form->addAction(new CancelButton("cancel", lang("cancel", "cancel")));
		}
}

class PageCommentsController extends FrontedController
{
		public $allowed_actions = array("edit", "delete");
		
		public $template = "comments/comments.html";
		/**
		 * ajax-save
		*/
		public function ajaxsave($data, $response, $form)
		{
				if($model = $this->save($data))
				{
						$response->prepend(".comments", $model->renderWith("comments/onecomment.html"));
						$response->exec('$(".comments").find(".comment:first").css("display", "none").slideDown("fast");');
						$response->exec("$('#".$form->fields["text"]->id()."').val(''); $('#".$form->fields["text"]->id()."').change();");
						return $response->render();
				} else
				{
						debug_log(print_r(debug_backtrace(), true));
						$response->exec(new Dialog("Could not save data.", "error"));
						return $response->render();
				}
		}
		
		
		/**
		 * hides the deleted object
		 *
		 *@name hideDeletedObject
		 *@access public
		*/
		public function hideDeletedObject($response, $data) {
			$response->exec("$('#comment_".$data["id"]."').slideUp('fast', function() { \$('#comment_".$data["id"]."').remove();});");
			return $response;
		}
}

/**
 * extends the page
*/ 
class PageCommentsDataObjectExtension extends DataObjectExtension {
	/**
	 * make relation
	*/
	public $has_many = array(
		"comments"	=> "pagecomments"
	);
	/**
	 * make field for enable/disable
	*/
	public $db_fields = array(
		"showcomments"	=> "int(1)"
	);
	
	public $defaults = array(
		"showcomments"	=> 0
	);
	/**
	 * make extra fields to form
	*/
	public function getForm(&$form) {
		$form->meta->add(new Checkbox("showcomments", lang("co_comments")));
	}
	/**
	 * append content to sites if needed
	*/
	public function appendContent(&$object) {
		if($this->getOwner()->showcomments) {
			$object->append((string) $this->getOwner()->comments());
		}
	}
}
/**
 * extends the controller
*/
class PageCommentsControllerExtension extends ControllerExtension {
	/**
	 * make the method work
	*/
	public static $extra_methods = array(
		"pagecomments"
	);
	public $allowed_actions = array(
		"pagecomments"
	);
	public function pagecomments()  {
		if($this->getOwner()->modelInst()->showcomments)
			return $this->getOwner()->modelInst()->comments()->controller()->handleRequest($this->getOwner()->request);
	}
}

Object::extend("pages", "PageCommentsDataObjectExtension");
Object::extend("contentController", "PageCommentsControllerExtension");