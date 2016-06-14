<?php defined("IN_GOMA") OR die();

/**
 * This is the generator and connect from Object-Based-Queries to the
 * SQL-Based-Queries.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0.9
 */

class SelectQuery {
	/**
	 * own data
	 *
	 */
	public $data = array();

	/**
	 * this var contains the SQL-Stament
	 */
	protected $sql = "";

	/**
	 * this var contains the WHERE-clause-Array
	 */
	public $filter = array();

	/**
	 * this var contains the limit
	 * e.g. array(0, 1); or array(1);
	 *@var array
	 */
	protected $limit = array();

	/**
	 * this var contains the result after the query
	 */
	public $result;

	/**
	 * from-part
	 *@var array
	 */
	public $from = array();

	/**
	 * orderby
	 *@var array
	 */
	public $orderby = array();

	/**
	 * this var defines if this is a DISTINCT-Query
	 *@name distinct
	 *@var bool
	 */
	public $distinct = false;

	/**
	 * this var contains the HAVING-part as array
	 *
	 *@var array
	 */
	public $having = array();

	/**
	 * group by
	 *@name groupby
	 *@var string
	 */
	public $groupby = array();

	/**
	 * the fields
	 *
	 *@var array
	 */
	public $fields = array();

	/**
	 * here you can define some db_fields, so for example if you want to define, that
	 * id is get from a specific table, define it here in the form:
	 * "id" => "myTable"
	 *
	 */
	public $db_fields = array();

	/**
	 * @var array
	 */
	static $aliases = array("group" => "_group");

	/**
	 * this is a cache for generating field-list and coliding fields
	 *
	 * @var array
	 */
	protected static $new_field_cache = array();

	/**
	 * from-hash.
	 */
	private $fromHash;

	/**
	 * @param string $table
	 * @return mixed
	 */
	public static function getAlias($table) {
		if(isset(self::$aliases[$table])) {
			return self::$aliases[$table];
		}
		return $table;
	}

	/**
	 * __construct
	 *@name __consturct
	 *@param string $table
	 *@param array $fields
	 *@param array $filter
	 */
	public function __construct($table = "", $fields = array(), $filter = array()) {
		if($table != "")
			$this->from($table);
		$this->fields = $fields;
		$this->filter = $filter;

	}

	/**
	 * this var adds a table to the from-part
	 * @param string $table
	 * @return $this
	 */
	public function from($table) {
		if(self::getAlias($table) != $table) {
			$this->from[str_replace(array('`', '"'), '', self::getAlias($table))] = array("table" => $table, "statement" => DB_PREFIX . $table . ' AS _' . $table . '');
		} else {
			$this->from[str_replace(array('`', '"'), '', $table)] = DB_PREFIX . $table . ' AS ' . $table . '';
		}
		return $this;
	}

	/**
	 * filters
	 *
	 *@name filter
	 */
	public function filter($filter) {
		if(!is_bool($filter))
			$this->filter = $filter;
	}

	/**
	 * adds a filter
	 * @param array|string $filter
	 */
	public function addFilter($filter) {
		if(is_string($this->filter)) {
			$this->filter = array($this->filter, $filter);
		} else if(is_array($filter)) {
			foreach($filter as $k => $v) {
				if(is_int($k)) {
					$this->filter[] = $v;
				} else if(isset($this->filter[$k])) {
					if(is_array($this->filter[$k])) {
						$this->filter[$k] = array_intersect($this->filter[$k], $v);
					} else {
						$this->filter[$k] = array_merge((array)$this->filter[$k], (array)$v);
					}
				} else {
					$this->filter[$k] = $v;
				}
			}
		} else {
			$this->filter[] = $filter;
		}
	}

	/**
	 * removes filter
	 *
	 */
	public function removeFilter($filter) {
		if(isset($this->filter[$filter]))
			unset($this->filter[$filter]);
		else
			foreach($this->filter as $key => $value) {
				if(is_array($value)) {
					if(isset($value[$filter]))
						unset($this->filter[$key][$filter]);
				}

				if($value == $filter) {
					unset($this->filter[$key]);
				}
			}
	}

	/**
	 * @param string $field
	 * @param string|array $type
	 * @param int $order
	 * @return $this
	 */
	public function sort($field, $type = "ASC", $order = 0) {

		$collate = null;

		if(is_array($field)) {
			$fieldValues = array_values($field);
			if(isset($field["field"], $field["type"])) {

				if(isset($field["collate"])) {
					$collate = $field["collate"];
				}

				$type = $field["type"];
				$field = $field["field"];
			} else if(count($field) == 2 && !is_array($fieldValues[0]) && !in_array(strtolower($fieldValues[0]), array("desc", "asc"))) {
				if(isset($fieldValues[1])) {
					$type = $fieldValues[1];
				}
				$field = $fieldValues[0];
			} else {
				foreach($field as $fieldName => $type) {
					if(is_string($fieldName) && !RegexpUtil::isNumber($fieldName)) {
						if(is_array($type) || in_array(strtolower($type), array("desc", "asc"))) {
							$this->sort($fieldName, $type);
						} else if(is_bool($type)) {
							$this->sort($fieldName, $type ? "asc" : "desc");
						}
					} else if(is_array($type)) {
						$this->sort($type);
					}
				}

				return $this;
			}
		} else {
			if(preg_match('/^(.*)\s*(asc|desc)$/i', $field, $matches)) {
				$field = $matches[1];
				$type = $matches[2];
				unset($matches);
			}
			$field = trim($field);
		}

		if($field == "")
			return $this;

		if(is_string($type)) {
			if (strtolower(trim($type)) == "desc") {
				$type = "DESC";
			} else {
				$type = "ASC";
			}
		} else if(!is_array($type)) {
			throw new InvalidArgumentException("Type not supported for sort.");
		}

		$order = ($order == 0) ? count($this->orderby) : $order;
		while(isset($this->orderby[$order])) {
			$order++;
		}
		$this->orderby[$order] = array($field, $type, $collate);

		return $this;
	}

	/**
	 * adds group-by
	 *
	 * @param string|array fields
	 * @return $this
	 */
	public function groupby($fields, $prepend = false) {
		if($prepend) {
			$this->groupby = array_merge((array)$fields, $this->groupby);
		} else {
			if(is_array($fields)) {
				$this->groupby = array_merge($this->groupby, $fields);
			} else if(!empty($fields)) {
				$this->groupby[] = $fields;
			}
		}

		return $this;
	}

	/**
	 * adds one to the having-part
	 *@name having
	 *@param string - clause
	 *@return gObject
	 */
	public function having($str) {
		$this->having[] = $str;
		return $this;
	}

	/**
	 * adds a field or more than one field as array to field-list
	 *
	 *@param string|array - fields
	 *@return gObject
	 */
	public function fields($fields, $table = "") {
		if(is_array($fields)) {
			if($table != "") {
				foreach($fields as $key => $field) {
					$fields[$key] = $table . "." . $field;
				}
			}
			$this->fields = array_merge($this->fields, $fields);
		} else {
			if(is_array($this->fields)) {
				if($table != "") {
					$this->fields[] = $table . "." . $fields;
				} else {
					$this->fields[] = $fields;
				}
			} else {
				$this->fields .= "," . $fields;
			}
		}
		return $this;
	}

	/**
	 * sets the fields
	 *@param array $field new value
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}

	/**
	 * adds an outer-join
	 *
	 * @param string $table
	 * @param string $statement after the ON
	 * @param string $alias : for joining the same table more than one time
	 * @return $this
	 */
	public function outerJoin($table, $statement, $alias = "") {
		$this->getAliasAndStatement($table, $statement, $alias);

		$this->from[$alias] = array("table" => $table, "statement" => " OUTER JOIN " . DB_PREFIX . $table . " AS " . $alias . " ON " . $statement . " ");
		return $this;
	}

	/**
	 * adds an inner-join
	 *
	 * @param string $table
	 * @param string $statement after the ON
	 * @param string $alias
	 * @return $this
	 */
	public function innerJoin($table, $statement, $alias = "") {
		$this->getAliasAndStatement($table, $statement, $alias);

		$this->from[$alias] = array("table" => $table, "statement" => " INNER JOIN " . DB_PREFIX . $table . " AS " . $alias . " ON " . $statement . " ");
		return $this;
	}

	/**
	 * adds an left-join
	 *
	 * @param string $table
	 * @param string $statement after the ON
	 * @param string $alias to use in join
	 * @return $this
	 */
	public function leftJoin($table, $statement, $alias = "") {
		$this->getAliasAndStatement($table, $statement, $alias);
		$this->from[$alias] = array("table" => $table, "statement" => " LEFT JOIN " . DB_PREFIX . $table . " AS " . $alias . " ON " . $statement . " ");
		return $this;
	}

	/**
	 * adds an right-join
	 *
	 * @param string $table table
	 * @param string $statement after the ON
	 * @param string $alias use in join
	 * @return $this
	 */
	public function rightJoin($table, $statement, $alias = "") {
		$this->getAliasAndStatement($table, $statement, $alias);
		$this->from[$alias] = array("table" => $table, "statement" => " RIGHT JOIN " . DB_PREFIX . $table . " AS " . $alias . " ON " . $statement . " ");
		return $this;
	}

	/**
	 * replaces user-defined alias with internal used alias to know the right table.
	 * it is used to improve uniqueness.
	 *
	 * @param string $table
	 * @param string $statement
	 * @param string $alias
	 */
	public function getAliasAndStatement(&$table, &$statement, &$alias) {
		$alias = ($alias == "") ? self::getAlias($table) : $alias;

		if($alias != $table) {
			$statement = str_replace(" " . $table . ".", " " . $alias . ".", $statement);
		}

		$statement = $this->replaceAliasInStatement($statement);

	}

	/**
	 * replaces user-defined table-aliases with internal table-aliases.
	 *
	 * @param $statement
	 * @return mixed
	 */
	public function replaceAliasInStatement($statement) {
		foreach($this->from as $a => $data) {
			if(is_array($data)) {
				$statement = str_replace(" " . $data["table"] . ".", " " . $a . ".", $statement);
			}
		}

		foreach(self::$aliases as $k => $v) {
			$statement = str_replace(" " . $k . ".", " " . $v . ".", $statement);
			$statement = str_replace("AS " . $k . " ", "AS " . $v . " ", $statement);
		}

		return $statement;
	}

	/**
	 * checks if joined
	 * @param string $table
	 * @return bool
	 */
	public function isJoinedTo($table) {
		return isset($this->from[$table]);
	}

	/**
	 * resolves correct identifier for field.
	 * @param string $field
	 * @return string
	 */
	public function getFieldIdentifier($field) {
		$field = strtolower(trim($field));

		$data = $this->generateDBFieldColidingCache();

		if(isset($data["dbfields"][$field])) {
			return $data["dbfields"][$field][0] . "." . $data["dbfields"][$field][1];
		}

		return $field;
	}

	/**
	 * parses DB-Field-Data.
	 * @param string $key
	 * @param string|array $data
	 * @return array
	 */
	protected function parseDBFieldData($key, $data) {
		if(is_array($data) && count($data) == 2) {
			$parsedValue = self::parseDBFieldData($key, $data[0]);
			return array($parsedValue[0], $data[1]);
		} else if(!strpos($data, ".")) {
			return array($data, $key);
		} else {
			return array($this->replaceAliasInStatement($data), $key);
		}
	}

	/**
	 * from-hash.
	 */
	protected function fromHash() {
		if(!isset($this->fromHash) || $this->from != $this->fromHash["from"] || $this->db_fields != $this->fromHash["db"]) {
			$this->fromHash["hash"] = md5(var_export($this->from, true) . serialize($this->db_fields));
			$this->fromHash["from"] = $this->from;
			$this->fromHash["db"] = $this->db_fields;
		}

		return $this->fromHash["hash"];
	}

	/**
	 * generates DB-Field + Coliding-Cache.
	 */
	protected function generateDBFieldColidingCache() {
		if(!isset(self::$new_field_cache[$this->fromHash()])) {
			$DBFields = $this->db_fields;

			foreach($DBFields as $k => $v) {
				$DBFields[$k] = $this->parseDBFieldData($k, $v);
			}

			$colidingFields = array();
			$foundFields = array();

			foreach($this->from as $alias => $statement) {
				if(is_array($statement)) {
					$table = $statement["table"];
					$statement = $statement["statement"];
				} else {
					$table = $alias;
				}

				$tablefields = array_keys(ClassInfo::getTableFields($table));
				foreach($tablefields as $field) {
					if(!isset($DBFields[$field])) {
						if (!isset($foundFields[$field])) {
							$foundFields[$field] = $alias;
						} else {
							if (!isset($colidingFields[$field])) {
								$colidingFields[$field] = array(self::getAlias($foundFields[$field]));
							}

							$colidingFields[$field][] = $alias;
						}
					}
				}
			}
			self::$new_field_cache[$this->fromHash()]["coliding"] = $colidingFields;
			self::$new_field_cache[$this->fromHash()]["dbfields"] = $DBFields;
			unset($alias, $statement, $tablefields, $field);
		}

		return self::$new_field_cache[$this->fromHash()];
	}


	/**
	 * builds the SQL-Query
	 *
	 * @param string - override fields part
	 * @return string
	 * @throws Exception
	 */
	public function build($fields = null) {

		if(PROFILE)
			Profiler::mark("SelectQuery::build");

		// begin SQL
		$sql = "SELECT ";

		if($this->distinct)
			$sql .= " DISTINCT ";

		// FIELDS

		if(!isset($fields))
			$fields = $this->fields;

		if(!is_string($fields) && (!is_array($fields) || count($fields) == 0)) {
			$fields = array("*");
		}

		if(is_array($fields) && count($fields) > 0) {
			$sql .= $this->generateFieldSQLFromArray($fields);
		} else if(is_string($fields)) {
			$sql .= " " . $fields . " ";
		}

		// FROM

		$sql .= " FROM ";

		$fromHash = $this->from;

		// validate from
		foreach($fromHash as $alias => $data) {
			if(RegexpUtil::isNumber($alias)) {
				if(is_array($data)) {
					$data = $data["statement"];
					$fromHash[$alias] = $this->replaceAliasInStatement($data);
				} else {
					$fromHash[$alias] = $this->replaceAliasInStatement($fromHash[$alias]);
				}
				continue;
			}

			if(is_array($data)) {
				$table = $data["table"];
				$data = $data["statement"];
				$fromHash[$alias] = $data;
			} else {
				$table = $alias;
			}

			if(is_string($table)) {
				if(ClassInfo::$database && !isset(ClassInfo::$database[$table])) {
					throw new Exception("Table " . $table . " does not exist! " . print_r($fromHash, true));
				}
			}
		}

		$sql .= implode(" ", $fromHash);

		// WHERE

		$data = $this->generateDBFieldColidingCache();
		$sql .= SQL::extractToWhere($this->filter, true, $data["dbfields"], $data["coliding"]);

		//$sql .= $this->addWhere;
		// GROUP BY
		if(count($this->groupby) > 0) {
			$sql .= " GROUP BY ";
			$sql .= implode(",", $this->groupby);
		}


		// HAVING
		if(count($this->having) > 0) {
			$sql .= " HAVING ";
			$sql .= SQL::ExtractToWhere($this->having, false, $data["dbfields"], $data["coliding"]);
		}

		ksort($this->orderby);
		// ORDER BY
		if(count($this->orderby) > 0) {
			$sql .= " ORDER BY ";
			$i = 0;
			foreach($this->orderby as $data) {
				if($i == 0) {
					$i++;
				} else {
					$sql .= ",";
				}

				if(is_array($data[1])) {
					$sql .= "FIELD(" . $this->getFieldIdentifier($data[0]) . ", '" . implode("','", $data[1]) . "')";
				} else {
					$collate = isset($data[2]) ? " COLLATE " . $data[2] : "";

					$sql .= $this->getFieldIdentifier($data[0]) . $collate . " " . $data[1];
				}
			}
		}

		// LIMIT
		if(is_array($this->limit)) {
			if(count($this->limit) > 0 && !empty($this->limit)) {
				$sql .= " LIMIT ";
				if(count($this->limit) == 2) {
					if($this->limit[0] < 0)
						$this->limit[0] = 0;

					if($this->limit[1] < 0)
						$this->limit[0] = 0;

					$sql .= " " . $this->limit[0] . ", " . $this->limit[1] . "";
				} else {
					$sql .= $this->limit[0];
				}
			}
		} else if(!empty($this->limit))
			$sql .= " LIMIT " . $this->limit;

		unset($DBFields, $colidingFields);
		if(PROFILE)
			Profiler::unmark("SelectQuery::build");

		return $sql;
	}

	/**
	 * generates the coliding SQL
	 * @return string
	 */
	protected function generateColidingSQL() {
		// some added caches ;)
		if(isset(self::$new_field_cache[$this->fromHash()]["colidingSQL"])) {
			return self::$new_field_cache[$this->fromHash()]["colidingSQL"];
		} else {
			$data = $this->generateDBFieldColidingCache();
			$colidingSQL = array();

			foreach($data["dbfields"] as $field => $info) {
				$colidingSQL[] = $info[0] . "." . $info[1] . " as " . $field;
			}

			// fix coliding fields
			foreach($data["coliding"] as $field => $tables) {

				if(is_string($tables)) {
					if(strpos($tables, ".")) {
						$colidingSQL[] = $tables;
					} else {
						$colidingSQL[] = self::getAlias($tables) . "." . $field . " AS " . $field . " ";
					}
					continue;
				}

				$fieldSQL = " coalesce( ";
				foreach($tables as $table) {
					$fieldSQL .= self::getAlias($table) . "." . $field . ",";
				}
				$fieldSQL .= "'') as " . $field;
				$colidingSQL[] = $fieldSQL;
			}

			self::$new_field_cache[$this->fromHash()]["colidingSQL"] = $colidingSQL;

			return $colidingSQL;
		}
	}

	/**
	 * sets an limit
	 *
	 * @param array - limitarr
	 * @return $this
	 */
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * executes the query
	 * @param string|null $fields
	 * @return $this
	 * @throws SQLException
	 */
	public function execute($fields = null) {

		if($result = sql::query($this->build($fields))) {
			$this->result = $result;
			return $this;
		} else {
			log_error(print_r($this, true));
			throw new SQLException();
		}
	}

	/**
	 * gets the result as object
	 */
	public function fetch_object() {
		return sql::fetch_object($this->result);
	}

	/**
	 * gets the result as array
	 */
	public function fetch_assoc() {
		return sql::fetch_assoc($this->result);
	}

	/**
	 * frees the result and query-cleanup
	 *
	 */
	public function free() {
		SQL::free_result($this->result);
		unset($this->result);
	}

	/**
	 * @param array $fields
	 * @return string
	 */
	protected function generateFieldSQLFromArray($fields)
	{
		$fieldsData = array();

		if(in_array("*", $fields)) {
			// join all from-tables
			foreach($this->from as $alias => $statement) {
				if(RegexpUtil::isNumber($alias))
					continue;

				if(!empty($alias)) {
					$fieldsData[] = $alias . ".*";
				}
			}
		}

		$fieldsData = array_merge($fieldsData, $this->generateColidingSQL());

		foreach($fields as $key => $field) {
			// some basic filter
			if(is_array($field))
				continue;

			$field = str_replace("`", "", $field);

			if($field == "*")
				continue;

			/* --- */

			if(!RegexpUtil::isNumber($key)) {
				$alias = $key;
			} else {
				if(isset($DBFields[$field]) && !isset($colidingFields[$field])) {
					$alias = $field;
					$field = self::getAlias($DBFields[$field][0]) . "." . $DBFields[$field][1];
				} else if(isset($colidingFields[$field])) {
					continue;
				} else {
					$fieldsData[] = $field;
					continue;
				}
			}

			$fieldsData[] .= $field . " AS " . $alias;
		}

		return implode(",", $fieldsData);
	}
}
