<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 24.05.2012
  * $Version 2.0.5
*/   
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang("pm");

class PM extends DataObject
{
		public $db_fields = array(
			"text"		=> "text",
			"time"		=> "int(20)",
			"sig"		=> "int(1)",
			"hasread"	=> "int(1)",
		);
		
		/**
		 * relations to the users
		 *
		 *@name has_one
		 *@access public
		*/
		public $has_one = array(
			"from"		=> "user", 
			"to"		=> "user"
		);
		
		/**
		 * define insert-right
		*/
		public $add_rights = 2;
		
		/**
		 * define default-sort
		*/
		public static $default_sort = array("field" => "time", "type" => "asc");
		/**
		 * from 
		*/
		public $writeField = "fromid";
		/**
		 * write-rights
		*/
		public function canInsert($data)
		{
				if(parent::canInsert($data))
				{
						return true;
				} else
				{
						if($data["fromid"] == member::$id && member::login())
								return true;
								
						return false;
				}
		}
		/**
		 * generates the form for this model
		 *
		 *@name getForm
		 *@access public
		*/
		public function getForm(&$form)
		{
				$form->addValidator(new RequiredFields(array("text", "toid")), "required_fields");
				$form->add(new HiddenField("fromid", member::$id));
				$form->add(new HasOneDropDown("to", lang("pm_to", "to"), "nickname"));
				
				$form->add(new BBCodeEditor("text",  lang("message", "Message")));
				$form->add(new timefield("time"));
				$form->add(new Checkbox("sig",lang("pm_add_sig"), 1));
				$form->addAction(new AjaxSubmitButton("send", lang("lp_submit", "send"), "ajaxsave"));
				
		}
		/**
		 * sets fromid
		 *@name setFromID
		 *@access public
		*/
		public function setFromID($value)
		{
				$this->setField("fromid", member::$id);
		}
		/**
		 * gets a preview of the message
		 *@name getPreview
		 *@access public
		*/
		public function getPreview()
		{
				if($data = DataObject::get_one("pm", array("fromid" => $this->fromid, "OR", "toid" => $this->fromid), array("pm.id", "DESC"))) {
					if(strlen($data["text"]) > 99)
					{
							return substr($this->fieldGet("text"), 0, 96) . "...";
					} else
					{
							return $data["text"];
					}	
				} else {
					return falsE;
				}
		}
		
		/**
		 * returns if thread was read
		 *
		 *@name threadRead
		 *@access public
		*/
		public function threadRead() {
			return !(DataObject::Count("pm", array("fromid" => $this->fromid, "hasread" => 0)) > 0);
		}
		
		/**
		 * returns the date the thread was last touched
		 *
		 *@name threadLastModified
		 *@access public
		*/
		public function threadLastModified() {
			return DataObject::get_one("pm", array(array("fromid" => $this->fromid, "OR", "toid" => $this->fromid)), array("id", "DESC"))->time;
		}
		
		/**
		 * gets the text
		*/
		public function getText()
		{
				if($this->fieldGet("sig") == 1 && $this->from()->signatur)
				{
						return $this->fieldGet("text") . "[color=#4f4f4f]\n________________________\n" . $this->from()->signatur . "[/color]";
				} else
				{
						return $this->fieldGet("text");
				}
		}
		/**
		 * gets the tid
		 *
		 *@name getTID
		 *@access public
		*/
		public function getTid() {
			return $this->fromid;
		}
		
		/**
		 * reply-form
		 *
		 *@name reply_form
		 *@access public
		*/
		public function reply_form() {
			$form = $this->controller()->buildForm("reply_form", new PM());
			$form->remove("toid");
			$form->add(new HiddenField("toid", $this->with->id));
			return $form->render();
		}
}

class PMController extends FrontedController
{
		/**
		 * url handlers
		 *
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array(
			"inbox" 	=> "showInBox",
			'new/$id'	=> "new_message",
			'del/$id!'  => "deleteThread",
			'delm/$id!' => "delete",
			'$id!'		=> "showThread"
		);
		
		/**
		 * allowed actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array(
			"showInBox"		=> 2,
			"showThread"	=> 2,
			"new_message"	=> 2,
			"deleteThread",
			"delete"
		);
		
		/**
		 * activates the live-counter on this controller
		 *
		 *@name live_counter
		 *@access public
		*/
		public static $live_counter = true;
		
		/**
		 * always show inbox in breadcrumb
		 *
		 *@name pagetitle
		 *@access public
		*/
		public function pagetitle() {
			return lang("pm_inbox");
		}
		
		/**
		 * gcreate new message
		 *
		 *@name new_message
		 *@access public
		*/
		public function new_message()
		{
				$to = $this->getParam("id");
				$model = new PM();
				if($to != "")
				{
						$model->toid = $to;
				}
				Core::addBreadCrumb(lang("mem_send_message"), URL);
				Core::setTitle(lang("mem_send_message", "send message"));
				return $this->Form("pm", $model);
		}
		
		/**
		 * index
		*/
		public function index()
		{
				return $this->showInBox();
		}
		/**
		 * shows the inbox
		*/
		public function showInBox()
		{
				if(!Member::login()) 
					HTTPResponse::redirect(BASE_URI);
				
				$object = DataObject::get("pm", array("toid" => member::$id));
				$data = $object->getGroupedSet("fromid");
				return $data->renderWith("pm/inbox.html");
		}
		/**
		 * counts how much new messages exists
		*/
		public static function countNew()
		{
				if(isset(member::$id))
						return DataObject::count("pm", array("toid" => member::$id, "hasread" => 0), array(), "fromid");
				else
						return 0;
		}
		/**
		 * shows a thread
		*/
		public function showThread()
		{
				
				$id = $this->getParam("id");
				if($id == 0)
					return false;
				$data = DataObject::get("pm", array(array("fromid" => $id, "OR", "toid" => $id)));
				if($data->Count() > 0)
				{
						DataObject::update("pm", array("hasread" => 1), array("fromid" => $id));
						Core::addBreadCrumb(lang('pm_inbox', 'InBox'), 'pm/');
						Core::addBreadCrumb(lang("pm_read", "read message"), "pm/" . $id . URLEND);
						Core::setTitle(lang("pm_read", "read message"));
						
						$data->customise(array("with" => DataObject::Get_One("user", array("id" => $id))));
						
						return $data->renderWith("pm/thread.html");
				} else
				{
						HTTPResponse::redirect(ROOT_PATH . BASE_SCRIPT . "pm/");
				}
		}
		/**
		 * saves data via ajax
		 *@name saveAjax
		 *@access public
		*/
		public function ajaxsave($data,$response, $form)
		{
				if($model = $this->save($data))
				{
						$model->nickname = member::$nickname;
						$model->avatar = $model->from()->avatar;
						$model->user_id = member::$id;
						
						$response->appendHighlighted("#message_thread",$model->renderWith("pm/message.html"));
						$response->exec("location.href = location.pathname + '#message_".$model->id."';");
						$response->exec("$('#".$form->fields["text"]->id()."').val('');");
						$response->exec('if(ajax_button.parents(".dropdownDialog").length > 0) { var dialog = dropdownDialog.get(ajax_button.parents(".dropdownDialog").attr("id")); dialog.closeButton = false; dialog.setContent("<div class=\"success\">'.lang("pm_sent").'</div>"); setTimeout(function(){ dialog.hide();}, 3000); }');
						return $response->render();
				} else
				{
						$response->exec(new Dialog(lang("less_rights", "You aren't permitted to submit this form."), lang("error")));
						return $response->render();
				}
		}
		/**
		 * sends a message via ajax
		 *@name saveAjax
		 *@access public
		*/
		public function ajaxsend($data,$response)
		{
				$model = $this->save($data);
				$response->exec('dropdownDialog.get(ajax_button.parents(".dropdownDialog").attr("id")).hide();');
				return $response->render();
		}
		/**
		 * serve-method
		*/
		public function serve($content) {
			if(Core::is_ajax()) {
				return $content;
			}
			
			return parent::serve($content);
		}
		
		/**
		 * default save-method for forms
		 * it's the new one, the old one was @safe
		 *
		 *@name submit_form
		 *@access public
		*/
		public function submit_form($data) {
			if($this->save($data) !== false)
			{
				AddContent::addSuccess(lang("pm_sent", "The message was successfully sent!"));
				$this->redirectBack();
			} else
			{
				$debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
				throwError(6, 'Server-Error', 'Could not save data in '.$debug[0]["file"].' on line '.$debug[0]["line"].'.');
			}
		}
		
}

class PMProfileExtension extends ControllerExtension {
	/**
	 * before render ends we add send message action
	 *
	 *@name beforeRender
	 *@access public
	*/
	public function beforeRender($userdata) {
		if(member::login()) $this->getOwner()->profile_actions->append(new HTMLNode("li", array(), new HTMLNode("a", array("href" => "pm/new/" . $userdata->id, "rel" => "dropdownDialog", "class" => "noAutoHide"), lang("mem_send_message", "send message"))));
	}
}
Object::extend("ProfileController", "PMProfileExtension");

class PMTemplateExtension extends Extension {
	/**
	 * adds a method to view in template how many message are unread
	*/
	
	/**
	 * register method
	 *
	 *@name extra_methods
	 *@access public
	*/
	public static $extra_methods = array(
		"PM_Unread"
	);
	
	/**
	 * method
	 *
	 *@name PM_Unread
	 *@access public
	*/
	public function PM_Unread() {
		return DataObject::get("pm", array("toid" => member::$id, "hasRead" => 0))->getGroupedSet("fromid")->Count();
	}
}

Object::extend("tplCaller", "PMTemplateExtension");

Core::addRules(array("pm" => "PMController"), 10);