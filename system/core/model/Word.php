<?php defined('IN_GOMA') OR die();
/**
 * @package 	goma framework
 * @link 		http://goma-cms.org
 * @license 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
*/

/**
 * every class which that should search with relevance should extend this controller.
 *
 * @author 	Goma-Team
 * @version 	1.0
*/
class Word extends DataObject {
	/**
	 * gets all words as an array. The key is in this case the ID of the word and the value is the word.
	*/
	static function getWords() {
		$cacher = new Cacher("words", true);
		if($cacher->checkValid()) {
			return $cacher->getData();
		} else {
			$array = array();
			$data = DataObject::get("word");
			foreach($data as $record) {
				$array[$record->versionid] = strtolower($record->word);
			}
			$cacher->write($array, 7 * 24 * 60 * 60);
			return $array;
		}
	}
	
	/**
	 * adds a word or returns versionid of existing word.
	*/
	static function requireWord($word) {
		if($key = array_search(strtolower($word), $this->getWords())) {
			return $key;
		} else {
			$words = $this->getWords();
			
			// add to DataBase
			$word = new Word(array("word" => strtolower($word)));
			$word->writeToDB(true, true);
			
			// add to Cache
			$words[$word->versionid] = $word->word;
			$cacher = new Cacher("words", true);
			$cacher->write($words, 7 * 24 * 60 * 60);
			
			// return versionid
			return $word->versionid;
		}
	}
	
	/**
	 * database-fields.
	*/
	static $db = array(
		"word"		=> "varchar(50)"
	);

	static $search_fields = false;
}
