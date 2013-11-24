<?php defined("IN_GOMA") OR die();

/**
 * Date-Field for SQL-Date.
 *
 * @package		Goma\Core\Model
 * @version		1.5
 */
class DateField extends FormField
{
		/**
		 * generates this field.
		 *
		 * @name 	__construct
		 * @param 	string $name name
		 * @param 	string $title title
		 * @param 	string $value value
		 * @param 	array $between key 0 for start and key 1 for end and key 2 indicates whether to allow the values given
		 * @param 	object $form
		*/
		public function __construct($name, $title = null, $value = null, $between = null, $form = null)
		{
				$this->between = $between;
				parent::__construct($name, $title, $value, $form);
		}
		
		/**
		 * creates the field.
		 *
		 *@name createNode
		 *@access public
		*/
		public function createNode() {
			$node = parent::createNode();
			$node->type = "text";
			$node->addClass("datepicker");
			return $node;
		}
		
		/**
		 * validate
		 *
		 *@name validate
		*/
		public function validate($value) {
			if (($timestamp = strtotime($value)) === false) {
			    return lang("no_valid_date", "No valid date.");
			} else {
				if($this->between && is_array($this->between)) {
					$between = array_values($this->between);
					$start = strtotime($between[0]);
					$end = strtotime($between[1]);
					if((!isset($between[2]) || $between[2] === false) && $start < $timestamp && $timestamp < $end) {
						return true;
					} if(isset($between[2]) && $between[2] === true && $start <= $timestamp && $timestamp <= $end) { 
						return true;
					} else {
						$err = lang("time_not_in_range", "The given time is not between the range \$start and \$end.");
						$err = str_replace('$start', date("H:i:s", $start), $err);
						$err = str_replace('$end', date("H:i:s", $end), $err);
						return $err;
					}
				}
				$this->value = date("H:i:s", $timestamp);
				return true;
			}
		}
		
		/**
		 * render JavaScript
		*/
		public function JS() {
			Resources::add("system/libs/javascript/ui/datepicker.js");
			Resources::add("system/libs/javascript/ui/datepicker.i18n.js");
			return '$(function(){
			if($.datepicker.regional["'.Core::$lang.'"] !== undefined) { $.datepicker.setDefaults( $.datepicker.regional[ "'.Core::$lang.'" ] ); }
			$("#'.$this->ID().'").datepicker();});';
		}
}