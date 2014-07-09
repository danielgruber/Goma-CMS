<?php defined("IN_GOMA") OR die();


interface FormActionHandler {
	/**
	 * returns if this action can submit the form
	 *
	 *@name canSubmit
	 *@return bool
	 */
	public function canSubmit($data);

	/**
	 *@name getsubmit
	 *@return string - method on controller OR @default for Default-Submission
	 */
	public function getSubmit();
}