<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 02.12.2012
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class smilieAdmin extends TableView
{
		// config
		public $text = '{$_lang_smilies}';
		
		public $rights = "SMILIE_ADMIN";
		
		
		public $models = array("smilies");	
		
		public $fields = array(
			"code"			=> '{$_lang_smiliecode}',
			"image"			=> '{$_lang_pic}',
			"description"	=> '{$_lang_smilie_title}'
		);
}