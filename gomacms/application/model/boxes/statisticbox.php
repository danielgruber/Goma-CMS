<?php defined("IN_GOMA") OR die();
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 23.03.2015
*/

loadlang('st');

class statistics extends box
{
		/**
		 * title of this dataobject
		*/
		public static $cname = '{$_lang_st_stats}';
		
		/**
		 * additional database-fields needed for this box
		 *
		 *@name db
		*/
		static $db = array
		(
			"today" 		=> "int(1)",
			"last2" 		=> "int(1)",
			"last30d"		=> "int(1)",
			"whole"			=> 'int(1)',
			'online'		=> 'int(1)'
		);
		
		/**
		 * gets checkboxes for editing
		 *
		 *@name getEditForm
		*/
		public function getEditForm(&$form)
		{
				parent::getEditForm($form);
				
				$form->add(new checkbox('today', $GLOBALS["lang"]["st_today"]));
				$form->add(new checkbox('last2', $GLOBALS["lang"]["st_2 hours ago"]));
				$form->add(new checkbox('last30d',$GLOBALS["lang"]["st_last 30 days"]));
				$form->add(new checkbox('whole', $GLOBALS["lang"]["st_whole"]));
				if(settingsController::get("livecounter"))
					$form->add(new checkbox("online", $GLOBALS["lang"]["st_online"]));
		}
		
		/**
		 * renders the whole box
		 *
		 *@name getContent
		 *@access public
		*/
		public function getContent()
		{
				$output = "<table>";

				if($this->today == 1)
				{
						$count = LiveCounter::countUsersByLast(NOW - 60 * 60 * 24);
						$num = sprintf("%05d",$count);
						$output .= "<tr><td>".$GLOBALS['lang']['st_today'].": </td><td>".$num."</td></tr>";
				}
				
				if($this->last2 == 1)
				{
						$count = LiveCounter::countUsersByLast(NOW - 7200);
						$num = sprintf("%05d",$count);
						$output .= "<tr><td>".$GLOBALS['lang']['st_2 hours ago'].": </td><td>".$num."</td></tr>";
				}
				if($this->last30d == 1)
				{
						$count = LiveCounter::countUsersByLast(NOW - 60 * 60 * 24 * 30);
						$num = sprintf("%05d",$count);
						$output .= "<tr><td>".$GLOBALS['lang']['st_last 30 days'].": </td><td>".$num."</td></tr>";
				}
				if($this->whole == 1)
				{
						$count = LiveCounter::countUsersByLast(0);
						$num = sprintf("%05d",$count);
						$output .= "<tr><td>".$GLOBALS['lang']['st_whole'].": </td><td>".$num."</td></tr>";
				}

				$output .= "</table>";
				if(settingsController::get("livecounter") && $this->online == 1)
				{
						$on = LiveCounter::countUsersOnline();
						if($on == 1) {
							$text = lang("visitor_online_1", "We've got one visitor online");
						} else {
							$text = var_lang('visitor_online_multi', array("user" => $on));
						}
						$output .= "<br />".$text."";
				}
				return $output;
		}
}
