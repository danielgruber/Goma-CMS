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

	static $aliases = array("group" => "_group");

	public static function getAlias($c) {
		if(isset(self::$aliases[$c])) {
			return self::$aliases[$c];
		}
		return $c;
	}

	/**
	 * __construct
	 *@name __consturct
	 *@param string - table
	 *@param array - fields
	 *@param array - where
	 */
	public function __construct($table = "", $fields = array(), $filter = array()) {
		if($table != "")
			$this->from($table);
		$this->fields = $fields;
		$this->filter = $filter;

	}

	/**
	 * this var adds a table to the from-part
	 *@param string
	 */
	public function from($str) {
		if(self::getAlias($str) != $str) {
			$this->from[str_replace(array('`', '"'), '', self::getAlias($str))] = array("table" => $str, "statement" => DB_PREFIX . $str . ' AS _' . $str . '');
		} else {
			$this->from[str_replace(array('`', '"'), '', $str)] = DB_PREFIX . $str . ' AS ' . $str . '';
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
	 *
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
			} else if(count($field) == 2 && !in_array(strtolower($fieldValues[0]), array("desc", "asc"))) {
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
						} else if(is_array($type)) {
							$this->sort($field, $type);
						}
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
	 *@param new value
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}

	/**
	 * adds an outer-join
	 *
	 *@param string - table
	 *@param string - statement after the ON
	 *@param string - alias: for joining the same table more than one time
	 */
	public function outerJoin($table, $statement, $alias = "") {
		$this->getAliasAndStatement($table, $statement, $alias);

		$this->from[$alias] = array("table" => $table, "statement" => " OUTER JOIN " . DB_PREFIX . $table . " AS " . $alias . " ON " . $statement . " ");
		return $this;
	}

	/**
	 * adds an inner-join
	 *
	 *@param string - table
	 *@param string - statement after the ON
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
			$statement = str_replace($table . ".", $alias . ".", $statement);
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
	 *
	 *@param string - table
	 *@return bool
	 */
	public function isJoinedTo($table) {
		return isset($this->from[$table]);
	}

	/**
	 * this is a cache for generating field-list and coliding fields
	 */
	protected static $new_field_cache = array();

	/**
	 * builds the SQL-Query
	 *
	 * @param string - override fields part
	 * @return string
	 * @throws SQLException
	 */
	public function build($fields = null) {

		if(PROFILE)
			Profiler::mark("SelectQuery::build");

		// first make a index of all fields and check for coliding fields
		// we cache this part, because we need this just one time foreach from-array
		$fromHash = md5(var_export($this->from, true) . implode($this->db_fields));
		if(isset(self::$new_field_cache[$fromHash])) {
			$colidingFields = self::$new_field_cache[$fromHash]["coliding"];
			$DBFields = self::$new_field_cache[$fromHash]["dbfields"];
		} else {
			$DBFields = $this->db_fields;

			foreach($DBFields as $k => $v) {
				if(!strpos($v, ".")) {
					$DBFields[$k] = self::getAlias($v);
				} else {
					$DBFields[$k] = $this->replaceAliasInStatement(" " . $v);
				}
			}

			$predefinedFields = $DBFields;
			$colidingFields = $DBFields;

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
						$DBFields[$field] = $alias;
					} else {
						if(isset($predefinedFields[$field])) {
							//$colidingFields[$field] = $predefinedFields[$field];
						} else {
							if(!isset($colidingFields[$field]))
								$colidingFields[$field] = array(self::getAlias($DBFields[$field]));
							$colidingFields[$field][] = $alias;
						}
					}
				}
			}
			self::$new_field_cache[$fromHash]["coliding"] = $colidingFields;
			self::$new_field_cache[$fromHash]["dbfields"] = $DBFields;
			unset($alias, $statement, $tablefields, $field);
		}

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
			$sql .= $this->generateFieldSQLFromArray($fields, $fromHash, $this->from, $colidingFields);
		} else if(is_string($fields)) {
			$sql .= " " . $fields . " ";
			if(is_array($this->fields) && $fieldsSQL = $this->generateFieldSQLFromArray($this->fields, "withoutfrom_" . $fromHash, array(), array())) {
				$sql .= "," . $fieldsSQL;
			}
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
					throw new SQLException("Table " . $table . " does not exist!");
				}
			}
		}

		$sql .= implode(" ", $fromHash);

		// WHERE

		$sql .= SQL::extractToWhere($this->filter, true, $DBFields/*, $colidingFields*/);

		//$sql .= $this->addWhere;
		// GROUP BY
		if(count($this->groupby) > 0) {
			$sql .= " GROUP BY ";
			$sql .= implode(",", $this->groupby);
		}


		// HAVING
		if(count($this->having) > 0) {
			$sql .= " HAVING ";
			$sql .= SQL::ExtractToWhere($this->having, false, $DBFields, $colidingFields);
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
					$sql .= "FIELD('.$data[0].', '".implode("','", $data[1])."')";
				} else {
					$collate = isset($data[2]) ? " COLLATE " . $data[2] : "";

					if (isset($DBFields[$data[0]])) {
						$sql .= $DBFields[$data[0]] . "." . $data[0] . $collate . " " . $data[1];
					} else {
						$sql .= $data[0] . $collate . " " . $data[1];
					}
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
	 *
	 * @param string $from
	 * @param array $colidingFields
	 * @return string
	 */
	protected function generateColidingSQL($from, $colidingFields) {

		// some added caches ;)
		if(isset(self::$new_field_cache[$from]["colidingSQL"])) {
			return self::$new_field_cache[$from]["colidingSQL"];
		} else {
			$colidingSQL = array();

			// fix coliding fields
			foreach($colidingFields as $field => $tables) {

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
				$fieldSQL .= "'')";
				$colidingSQL[] = $fieldSQL;
			}

			self::$new_field_cache[$from]["colidingSQL"] = $colidingSQL;

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
	 */
	public function execute($fields = null) {

		if($result = sql::query($this->build($fields))) {
			$this->result = $result;
			return $this;
		} else {
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
	 * @param string $fromHash
	 * @param array $from
	 * @param array $colidingFields
	 * @return string
	 */
	protected function generateFieldSQLFromArray($fields, $fromHash, $from, $colidingFields)
	{
		$fieldsData = array();

		if(in_array("*", $fields)) {
			// join all from-tables
			foreach($from as $alias => $statement) {
				if(RegexpUtil::isNumber($alias))
					continue;

				if(!empty($alias)) {
					$fieldsData[] = $alias . ".*";
				}
			}
		}

		$fieldsData = array_merge($fieldsData, $this->generateColidingSQL($fromHash, $colidingFields, $i = 0));

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
					$field = self::getAlias($DBFields[$field]) . "." . $field;
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
