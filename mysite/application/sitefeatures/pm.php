<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 13.06.2011
  * $Version 2.0.0 - 002
*/   
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang("pm");

class PM extends DataObject
{
		public $db_fields = array(
			"subject"	=> "varchar(200)",
			"text"		=> "text",
			"time"		=> "int(20)",
			"sig"		=> "int(1)",
			"hasread"	=> "int(1)",
			"tid"		=> "int(10)"
		);
		public $has_one = array(
			"from"		=> "user", 
			"to"		=> "user"
		);
		public $writeFields = array(
			'fromid' => 'userids'
		);
		public $add_rights = 2;
		public $orderby = array("type" => "DESC", "field" => "time");
		public $pages = true;
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
						// check if new message
						if($data["id"] == $data["tid"])
								return true;
						
						// check if right thread
						$d = DataObject::get("pm", array("id" => $data["tid"]));
						if(($d["fromid"] == member::$id || $d["toid"] == member::$id) && member::$id != 0)
								return true;
								
						return false;
				}
		}
		public function getForm(&$form, $data)
		{
				if(defined("THREAD_ID"))
				{
						$form->addValidator(new RequiredFields(array("text")), "required_fields");
						$form->name = "pm";
						$_data = DataObject::get("pm", array("id" => THREAD_ID));
						$form->add(new HiddenField("tid", THREAD_ID));
						if($_data["fromid"] == $_SESSION["user_id"])
								$form->add(new HiddenField("toid", $_data["toid"]));
						else
								$form->add(new HiddenField("toid", $_data["fromid"]));
						
						$form->add(new HiddenField("fromid", $_SESSION["user_id"]));
						$form->add(new HiddenField("subject", $_data["subject"]));
						$form->add(new BBCodeEditor("text", lang("pm_reply", "Reply")));
						$form->add(new timefield("time"));
						$form->add(new Checkbox("sig",lang("pm_add_sig"), 0));
						$form->addAction(new AjaxSubmitButton("submit", lang("pm_send", "send"), "ajaxsave", "safe"));
						
				} else
				{
						$form->add(new HTMLField("title", "<h2>" . lang("mem_send_message") . "</h2>"));
						$form->addValidator(new RequiredFields(array("text")), "required_fields");
						$form->add(new HiddenField("fromid", $_SESSION["user_id"]));
						$form->add(new HasOneDropDown("to", lang("pm_to", "to"), "nickname"));
						$form->add(new TextField("subject", lang("pm_subject", "Subject")));
						$form->add(new BBCodeEditor("text",  lang("message", "Message")));
						$form->add(new timefield("time"));
						$form->add(new Checkbox("sig",lang("pm_add_sig"), 1));
						if(Core::is_ajax())
								$form->addAction(new AjaxSubmitButton("send", lang("save", "save"), "ajaxsend", "safe"));
						else
								$form->addAction(new FormAction("send", lang("save", "save")));
						
				}
				
				
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
				$data = DataObject::get("pm", array("tid" => $this->tid, "OR", "id" => $this->tid), array("pm.id", "DESC"), array(0, 1));
				if(strlen($data["text"]) > 99)
				{
						return substr($this->fieldGet("text"), 0, 96) . "...";
				} else
				{
						return $data["text"];
				}
		}
		/**
		 * sets the id
		*/
		public function setID($id)
		{
				$this->setField("id", $id);
				if($this->fieldGet("tid") == "")
					$this->setField("tid", $id);
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
			if($this->fieldGet("tid") == 0) {
				return $this->id;
			} else {
				$this->fieldGet("tid");
			}
		}
}

class PMController extends FrontedController
{
		public $url_handlers = array(
			"inbox" 	=> "showInBox",
			'new/$id'	=> "new_message",
			'del/$id!'  => "deleteThread",
			'delm/$id!' => "delete",
			'$id!'		=> "showThread"
		);
		
		public $allowed_actions = array(
			"showInBox"		=> 2,
			"showThread"	=> 2,
			"new_message"	=> 2,
			"deleteThread",
			"delete"
		);
		
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
		 * deletes a thread
		 *@name deleteThread
		 *@access public
		*/
		public function deleteThread($object = null)
		{
				
				$id = $this->request->getParam("id");
				$data = DataObject::get($this->model, array("tid" => $id));
				
				if(!$this->modelInst()->canDelete($data))
					return $GLOBALS["lang"]["less_rights"];
				
					
				if($this->confirm(lang("delete_confirm", "Do you really want to delete this record?"))) {
					$_data = clone $data;
					$data->_delete();
					if(request::isJSResponse()) {
						$response = new AjaxResponse();
						if($object !== null)
							return $object->hideDeletedObject($response, $_data);
						else 
							return $this->hideDeletedObject($response, $_data);
					} else {
						$this->redirectback();
					}
				}
				
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
				
				Core::addBreadCrumb(lang("pm_inbox"), "pm/");
				Core::setTitle(lang("pm_inbox"));
				$object = DataObject::get("pm", ' `toid` = "'.member::$id.'"');
				$data = $object->getGroupedSet("tid");
				return $data->renderWith("pm/inbox.html");
		}
		/**
		 * counts how much new messages exists
		*/
		public static function countNew()
		{
				if(isset($_SESSION["user_id"]))
						return DataObject::count("pm", array("toid" => $_SESSION["user_id"], "hasread" => 0), array(), "tid");
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
				$data = DataObject::get("pm", array(array("tid" => $id, "OR", "id" => $id), array("fromid" => member::$id, "OR", "toid" => member::$id)));
											
				if($data->Count() > 0)
				{
						DataObject::update("pm", array("hasread" => 1), "(pm.tid = '".dbescape($id)."' OR pm.id = '".dbescape($id)."') AND toid = '".dbescape(member::$id)."'");
						Core::addBreadCrumb(lang('pm_inbox', 'InBox'), 'pm/');
						Core::addBreadCrumb(lang("pm_read", "read message"), "pm/" . $id . URLEND);
						
						
						define("THREAD_ID", $id);
						
						if($data->first()->subject) {
							Core::setTitle($data->first()->subject . " - " . lang("pm_read", "read message"));
						} else {
							Core::setTitle(lang("pm_read", "read message"));
						}
						
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
						
						$response->prependHighlighted("#message_thread",$model->renderWith("pm/message.html"));
						$response->exec("location.href = location.pathname + '#message_".$model->id."';");
						$response->exec("$('#".$form->fields["text"]->id()."').val('');");
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
				$response->exec(dialog::closeByID($_GET["boxid"]));
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

Core::addRules(array("pm" => "PMController"), 10);