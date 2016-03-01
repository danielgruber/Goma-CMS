<?php defined("IN_GOMA") OR die();

/**
 * shows a dropdown-select, where the user can choose a language from the available languages
 *
 * @package 	goma form framework
 * @link 		http://goma-cms.org
 * @license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *	@author 	Goma-Team
 *
 * last modified: 28.02.2015
 */
class langSelect extends Select
{
	/**
	 * defines whether to include a first option like "all".
	 *
	 * if it is a string that is the option and title, if array then 0 is option 1 is title.
	 */
	public $includeFirstOption;

	/**
	 * @param 	string - name
	 * @param 	string - title
	 * @param 	string - select
	 * @param 	object - form
	 */
	public function __construct($name = null, $title = null, $selected = null, $form = null)
	{
		parent::__construct($name, $title, $this->options(), $selected, $form);
	}

	/**
	 * provides all options
	 *
	 * @name    options
	 * @access    public
	 * @return array
	 */
	public function options()
	{
		$options = array();

		if($this->includeFirstOption) {
			if(is_array($this->includeFirstOption)) {
				$options[$this->includeFirstOption[0]] = $this->includeFirstOption[1];
			} else if($this->includeFirstOption === true) {
				$options[""] = lang("all");
			} else {
				$options[$this->includeFirstOption] = $this->includeFirstOption;
			}
		}

		$data = i18n::listLangs();
		foreach($data as $lang => $contents) {
			$options[$lang] = $contents["title"];
		}


		return $options;
	}
}
