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
class SearchIndex extends DataObjectExtension {
	/**
	 * index-version.
	*/
	const VERSION = "1.0";
	
	/**
	 * db.
	*/
	static $db = array(
		"indexversion"	=> "decimal(6,2)"
	);

	/**
	 * indexes a given record.
	 *
	 * @param DataObject $record
	 * @return bool
	 */
	static function indexRecord($record) {
		$many_many_data = $record->ManyManyRelationships();
		
		if(isset($many_many_data["searchIndex"])) {
			/** @var ModelManyManyRelationShipInfo $relationship */
			$relationship = $many_many_data["searchIndex"];
			// generate words
			$content = $record->getSearchRepresentation();
			$wordList = (array) HTMLCacheController::getWordsRated($content);
			
			// insert to DB
			$manipulation = array(
				"index" => array(
					"command"		=> "insert",
					"table_name"	=> $many_many_data["searchIndex"]["table"],
					"fields"		=> array(
						
					)
				)
			);
			foreach($wordList as $word => $percent) {
				$manipulation["index"]["fields"][] = array(
					$relationship->getTargetField()	=> Word::requireWord($word),
					$relationship->getOwnerField()	=> $record->versionid,
					"relevance"						=> $percent
				);
			}
			
			return SQL::Manipulate($manipulation);
		} else {
			return false;
		}
	}

	/**
	 * searches in a DataObject with this index.
	 *
	 * @param    string $class class
	 * @param    string $search words or search-terms
	 * @param    string $filter additional filter
	 * @param    string $limits limits
	 * @param    string $joins additional joins
	 * @param    int|boolean $pagination false for disabling or int for items per page
	 * @param    boolean|string $groupby group by which field or disabled
	 *
	 * @return DataObjectSet|DataSet
	 */
	static function search($class, $search = "", $filter = array(), $limits = array(), $join = array(), $pagination = false, $groupby = false) {
		$DataSet = new DataObjectSet($class, $filter, array(), $limits, $join, $search);
		
		if ($pagination !== false) {
			if (is_int($pagination)) {
				$DataSet->activatePagination($pagination);
			} else {
				$DataSet->activatePagination();
			}
		}
		
		if ($groupby !== false) {
			return $DataSet->getGroupedSet($groupby);
		}
			
		
		return $DataSet;
	}
	
	/**
	 * has-many-extension
	*/
	public static function many_many($class) {
		if(strtolower(get_parent_class($class)) != "dataobject")
			return array();
			
		return array(
			"searchIndex" => "word"
		);
	}
	
	/**
	 * extend the extra-fields.
	*/
	public static function many_many_extra_fields($class) {
		if(strtolower(get_parent_class($class)) != "dataobject")
			return array();
			
		return array(
			"searchIndex" => array(
				"relevance"	=> "float"
			)
		);
	}
	
	/**
	 * on after writing we write the many-many-table for the searchIndex.
	*/
	public function onAfterWrite() {
		self::indexRecord($this->getOwner());
	}
	
	/**
	 * on before write.
	*/
	public function onBeforeWrite()  {
		$this->getOwner()->indexversion = self::VERSION;
	}
}
