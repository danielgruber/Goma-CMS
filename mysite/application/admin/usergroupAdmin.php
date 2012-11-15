<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 13.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class userGroupAdmin extends LeftAndMain
{
		// config
		public $text = '{$_lang_user_groups}';
		
		public $rights = "10";
		
		public $template = "admin/usergroup_index.html";
		
		public $models = array("group", "user");	
		
		public $sort = 970;		
		
		public $tree_class = "group";
		
		/**
		 * icons
		 *
		 *@name icons
		 *@access public
		*/
		public $icons = array("root"	=> "images/icons/fatcow16/group.png","user"	=> "images/icons/fatcow16/user.png");
		
		public $colors = array(
			"activated"	=> array(
				"color"	=> "#146800",
				"name"	=> "{\$_lang_user}: {\$_lang_not_locked}"
			),
			"disabled" 	=> array(
				"color"	=> "#c60004",
				"name"	=> "{\$_lang_user}: {\$_lang_locked}"
			),
			"group"		=> array(
				"color"	=> "#2f2f2f",
				"name"	=> "{\$_lang_group}"
			),
			"not_unlocked" => array(
				"color"	=> "#7f7c00",
				"name" => '{$_lang_user}: {$_lang_not_unlocked}'
			)
		);
		
		public $root_node = '{$_lang_user_groups}';
		
		/**
		 * generates the options for the create-select-field
		 *
		 *@name CreateOptions
		 *@access public
		*/
		public function createOptions() {
			return array(
				"user"	=> lang("user", "User"),
				"group"	=> lang("group", "Group")
			);
		}
		
	/**
	 * saves data for editing a site via ajax
	 *
	 *@name ajaxSave
	 *@access public
	 *@param array - data
	 *@param object - response
	*/
	public function ajaxSave($data, $response) {
		$model = $this->save($data);
		$dialog = new Dialog(lang("successful_saved", "The data was successfully written!"), "Okay");
		$dialog->close(5);
		$response->exec($dialog);
		if($model->RecordClass == "group")
			$response->exec("reloadTree(function(){ LoadTreeItem(".var_export("group_" . $model->id, true)."); });");
		else
			$response->exec("reloadTree(function(){ LoadTreeItem(".$model->id."); });");
		return $response;
	}
	
	/**
	 * patch for group
	 *
	 *@name handleRecordForModel
	 *@access public
	*/
	public function handleRecordForModel() {
		if(_ereg('^group_', $this->getParam("id"))) {
			$this->request->params["id"] = substr($this->getParam("id"), 6);
		}
		return parent::handleRecordForModel();
	}
	/**
	 * patch for group
	 *
	 *@name record
	 *@access public
	*/
	public function record() {
		if(_ereg('^group_', $this->getParam("id"))) {
			$this->request->params["id"] = substr($this->getParam("id"), 6);
		}
		return parent::record();
	}
}