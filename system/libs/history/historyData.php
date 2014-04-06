<?php defined("IN_GOMA") OR die();


interface HistoryData {
	/**
	 * returns text what to show about the event
	 *
	 *@name generateHistoryData
	 *@access public
	 *@return array("icon" => ..., "text" => ...)
	*/
	public static function generateHistoryData($record);
}