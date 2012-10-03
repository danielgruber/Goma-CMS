<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 08.08.2012
  * $Version 1.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class smilies extends DataObject
{
		/**
		 * fields of the table to this Model
		 *
		 *@name db_fields
		 *@access public
		*/
		public $db_fields = array(		'image'			=> 'Image',
										'description'	=> 'varchar(200)',
										'code'			=> 'varchar(200)');
		
		/**
		 * admin-permissions
		 *
		 *@name admin_rights
		 *@access public
		*/
		public $admin_rights = "admin_smilies";
		
		/**
		 * generates the form for this model
		 *
		 *@name getForm
		 *@access public
		*/
		public function getForm(&$form)
		{
				$form->add(new textField('code',lang("smiliecode")));
				$form->add(new textField('description',lang("description")));
				$form->add(new imageupload('image', lang("pic", "Image")));
				
				$form->addAction(new CancelButton('cancel',lang("cancel")));
				$form->addAction(new FormAction('submit',lang("save"), null, array("green")));

				$form->addValidator(new requiredFields(array('code', 'description','image')), "required_fields");
		}
		
		/**
		 * provides the needed permissions
		 *
		 *@name providePerms
		 *@access public
		*/
		public function providePerms() {
			return array(
				"SMILIE_ADMIN" => array(
					"title" 		=> '{$_lang_smilies}',
					"forceGroup"	=> true,
					"default"		=> array(
						"type"		=> "admins"
					)
				)
			);
		}
		
}

class SmilieBBCodeExtension extends Extension {
	/**
	 * cache
	 *
	 *@name smilies
	 *@access protected
	*/
	protected static $smilies;
	
	/**
	 * parses smiliecodes, e.g. ;)
	 *
	 *@name: parseBBCode
	 *@param: string - text
	*/
	public function parseBBCode(&$text)
	{
		if(isset(self::$smilies))
		{
			$smilies = self::$smilies;
		} else
		{
			$smilies = DataObject::get("smilies");
			self::$smilies = $smilies;						
		}
		
		foreach($smilies as $d)
		{
			if($d->image()) {
				$text = str_replace($d->code,'<img src="'.$d->image()->raw().'" alt="'.convert::raw2text($d->description).'" />',$text);
			}
		}
	}
}
Object::extend("BBCode", "SmilieBBCodeExtension");