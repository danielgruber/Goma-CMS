<?php
/**
  *@todo comments
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 19.06.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class smilies extends DataObject
{
		public $db_fields = array(		'image'			=> 'Image',
										'description'	=> 'varchar(200)',
										'code'			=> 'varchar(200)');
		public $admin_rights = "admin_smilies";
		public function getForm(&$form)
		{
				$form->add(new textField('code',$GLOBALS['lang']['smiliecode']));
				$form->add(new textField('description',$GLOBALS['lang']['description']));
				$form->add(new imageupload('image',$GLOBALS['lang']['pic']));
				$form->addAction(new FormAction('_submit',$GLOBALS['lang']['save']));

				$form->addValidator(new requiredFields(array('code', 'description','image')), "required_fields");
		}
		
		public function providePermissions() {
			return array(
				"admin_smilies"	=> array(
					'title' => '{$_lang_admin_smilies}', 
					'default' => 7
				)
			);
		}
		
}


class smiliesController extends Controller
{
		
}