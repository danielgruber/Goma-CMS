<?php
/**
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * last modified: 15.09.2013
 */

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/**
 * defines
 */

// REGEXP for your SQL
define('SQL_REGEXP', 'RLIKE');
// LIKE for your SQL without a differnet between A and a
define('SQL_LIKE', 'LIKE');

/* --- */

/* class */

class mysqliDriver extends object implements SQLDriver
{

    /**
     * @access public
     * @var resource connection
     * @use for the mysql connetion
     **/
    public $_db;

    public $version;
    public $engines;
    public $tableStatuses;

    /**
     * @access public
     * @use: connect to db
     **/
    public function __construct()
    {
        parent::__construct();

        /* --- */
        if (!defined("NO_AUTO_CONNECT")) {
            global $dbuser;

            global $dbdb;
            global $dbpass;
            global $dbhost;
            if (!isset(self::$db)) {
                self::connect($dbuser, $dbdb, $dbpass, $dbhost);
            }
        }

    }

    /**
     * @access public
     * @use: connect to db
     **/
    public function connect($dbuser, $dbdb, $dbpass, $dbhost)
    {
        $this->_db = new MySQLi($dbhost, $dbuser, $dbpass, $dbdb);
        if (!mysqli_connect_errno()) {
            self::setCharsetUTF8();
            $this->query("SET sql_mode = '';");
            return true;
        } else {
            die(str_replace('{BASE_URI}', BASE_URI, file_get_contents(ROOT . 'system/templates/framework/database_connect_error.html')));
        }
    }
    /**
     * tests the connection
     * @name test
     * @access public
     */
    /**
     * @access public
     * @use: connect to db
     **/
    public static function test($dbuser, $dbdb, $dbpass, $dbhost)
    {
        $test = new MySQLi($dbhost, $dbuser, $dbpass, $dbdb);
        if (!mysqli_connect_errno()) {
            $test->close();
            return true;
        } else {
            if ($test = new MySQLi($dbhost, $dbuser, $dbpass)) {
                if ($test->query("CREATE DATABASE " . $dbdb . " DEFAULT COLLATE = utf8_general_ci"))
                    return true;
            }
            return false;
        }
    }

    /**
     * @access public
     * @use: run a query
     **/
    public function query($sql, $unbuffered = false, $debug = true)
    {
        if (!$this->_db->ping()) {
            $this->__construct();
        }

        if ($result = $this->_db->query($sql))
            return $result;
        else {
            if ($debug) {
                $trace = debug_backtrace();
                log_error('SQL-Error in Statement: ' . $sql . ' in ' . $trace[1]["file"] . ' on line ' . $trace[1]["line"] . '.');
                $this->runDebug($sql);
            }

            return false;
        }
    }

    /**
     * some debug-operations
     */
    public function runDebug($sql)
    {
        SQL::$track = false;
        if ($this->errno() == 1054) {
            // match out table
            if (preg_match('/from\s+([a-zA-Z0-9_\-]+)/i', $sql, $matches)) {
                $table = $matches[1];
                if (substr($table, 0, strlen(DB_PREFIX))) {
                    $table = substr($table, strlen(DB_PREFIX));
                }

                if (isset(ClassInfo::$tables[$table])) {
                    $class = ClassInfo::$tables[$table];
                    $c = new $class();
                    $c->buildDB(DB_PREFIX);
                }
            }
        }
        SQL::$track = true;
    }

    /**
     * @access public
     * @use: fetch_row
     **/
    public function fetch_row($result)
    {
        return $result->fetch_row();
    }

    /**
     * @access public
     * @use to diconnect
     **/
    public function close()
    {
        $this->_db->close();
    }

    /**
     * @access public
     * @use to fetch object
     **/
    public function fetch_object($result)
    {
        return $result->fetch_object();
    }

    /**
     * @access public
     * @use to fetch array
     */
    public function fetch_array($result)
    {
        return $result->fetch_array();
    }

    /**
     * @access public
     * @use to fetch assoc
     */
    public function fetch_assoc($result)
    {
        return $result->fetch_assoc();
    }

    /**
     * @access public
     * @use to fetch num rows
     */
    public function num_rows($result)
    {
        return $result->num_rows;
    }

    /**
     * @access public
     * @use to fetch error
     */
    public function error()
    {
        return $this->_db->error;
    }

    /**
     * @access public
     * @use to fetch errno
     */
    public function errno()
    {
        return $this->_db->errno;
    }

    /**
     * @access public
     * @use to fetch insert id
     */
    public function insert_id()
    {
        return $this->_db->insert_id;
    }

    /**
     * @access public
     * @use to get memory
     */
    public function free_result($result)
    {
        return $result->free();
    }

    /**
     * @access public
     * @use to protect
     */
    public function escape_string($str)
    {
        if (is_array($str)) {
            throw new LogicException("Array is not allowed as given value for escape_string. Expected string.");
        }
        if (is_object($str)) {
            throw new LogicException("Object is not allowed as given value for escape_string. Expected string.");
        }

        return $this->_db->real_escape_string((string)$str);
    }

    /**
     * @access public
     * @use to protect
     */
    public function real_escape_string($str)
    {
        if (is_array($str)) {
            throw new LogicException("Array is not allowed as given value for escape_string. Expected string.");
        }
        if (is_object($str)) {
            throw new LogicException("Object is not allowed as given value for escape_string. Expected string.");
        }

        return $this->_db->real_escape_string((string)$str);
    }

    /**
     * @access public
     * @use to protect
     */
    public function protect($str)
    {
        return self::real_escape_string($str);
    }

    /**
     * @access public
     * @use to split queries
     */
    public function split($sql)
    {
        $queries = preg_split('/;\s*\n/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        return $queries;
    }

    /**
     * affected rows
     *
     * @name affected_rows
     * @access public
     */
    public function affected_rows()
    {
        return $this->_db->affected_rows;
    }

    /**
     * @access public
     * @use to view tables
     */
    public function list_tables($database)
    {
        $list = array();
        if ($result = sql::query("SHOW TABLES FROM " . $database . "")) {
            while ($row = $this->fetch_array($result)) {
                $list[] = $row[0];
            }
        }
        return $list;
    }

    /**
     * this function checks, if the table exists and get all fields
     * it returns false when table doesn't exist
     *
     * @param string $table table-name without prefix
     * @param bool $prefix
     * @param bool $track
     * @return array|false
     */
    public function getFieldsOfTable($table, $prefix = false, $track = true)
    {
        if ($prefix === false)
            $prefix = DB_PREFIX;

        $sql = "SHOW COLUMNS FROM " . $prefix . $table . "";
        if ($result = sql::query($sql, false, $track, false)) {
            $fields = array();
            while ($row = $this->fetch_object($result)) {
                $fields[$row->Field] = $row->Type;
            }
            return $fields;
        } else {
            return false;
        }
    }

    //!Index-Methods
    /**
     * INDEX FUNCTIONS
     */
    /**
     * adds an index to a table
     * @name addIndex
     * @access public
     * @param string - table
     * @param string|array field /fields
     * @param string - type: unique|fulltext|index
     * @param string - db_prefix optional
     */
    public function addIndex($table, $field, $type, $name = null, $db_prefix = null)
    {
        if ($db_prefix === null)
            $db_prefix = DB_PREFIX;

        switch (strtolower($type)) {
            case "unique":
                $type = "UNIQUE";
                break;
            CASE "fulltext":
                $type = "FULLTEXT";
                break;
            default:
                $type = "INDEX";
                break;
        }

        if (is_array($field)) {
            $field = implode(',', $field);
        } else {
            $field = $field;
        }

        $name = ($name === null) ? "" : $name;

        $sql = "ALTER TABLE " . DB_PREFIX . $table . " ADD " . $type . " " . $name . " (" . $field . ")";
        if (sql::query($sql)) {
            return true;
        } else {
            throw new MySQLException();
        }
    }

    /**
     * drops an index from a table
     * @name dropIndex
     * @param string - table
     * @param string - name
     * @param
     */
    public function dropIndex($table, $name, $db_prefix = null)
    {
        if ($db_prefix === null)
            $db_prefix = DB_PREFIX;

        $sql = "ALTER TABLE " . DB_PREFIX . $table . " DROP INDEX " . $name;
        if (sql::query($sql)) {
            return true;
        } else {
            throw new MySQLException();
        }
    }

    /**
     * gets the indexes of a table
     * @name getIndexes
     * @param string - table
     * @param string - DB_prefix - optional
     */
    public function getIndexes($table, $db_prefix = null)
    {
        if ($db_prefix === null)
            $db_prefix = DB_PREFIX;

        $indexes = array();
        $sql = "SHOW INDEXES FROM " . $db_prefix . $table . "";
        if ($result = sql::query($sql)) {
            while ($row = sql::fetch_object($result)) {
                if (!isset($indexes[$row->Key_name])) {
                    if ($row->Index_type == "FULLTEXT")
                        $type = "FULLTEXT";
                    else if ($row->Key_name == "PRIMARY")
                        $type = "PRIMARY";
                    else if ($row->Non_unique == 0)
                        $type = "UNIQUE";
                    else
                        $type = "INDEX";


                    $indexes[$row->Key_name] = array("fields" => array(), "type" => $type);
                }
                $indexes[$row->Key_name]["fields"][] = $row->Column_name;
            }
            return $indexes;
        } else {
            return false;
        }
    }


    /**
     * table-functions V2
     */
    //!Table-API

    /**
     * gets much information about a table, e.g. field-names, default-values, field-types
     *
     * @name showTableDetails
     * @access public
     * @param string - table
     * @param bool - if track query
     * @param string - prefix
     */
    public function showTableDetails($table, $track = true, $prefix = false)
    {
        if ($prefix === false)
            $prefix = DB_PREFIX;


        $sql = "SHOW COLUMNS FROM " . $prefix . $table;
        if ($result = sql::query($sql, false, $track, false)) {
            $fields = array();
            while ($row = $this->fetch_object($result)) {
                $fields[$row->Field] = array(
                    "type" => $row->Type,
                    "key" => $row->Key,
                    "default" => $row->Default,
                    "extra" => $row->Extra
                );
            }
            return $fields;
        } else {
            return false;
        }
    }

    /**
     * requires, that a table is exactly in this form
     *
     * @name requireTable
     * @access public
     * @param string - table
     * @param array - fields
     * @param array - indexes
     * @param array - defaults
     * @param string - prefix
     */
    public function requireTable($table, $fields, $indexes, $defaults, $prefix = false)
    {
        if ($prefix === false)
            $prefix = DB_PREFIX;

        $log = "";

        $updates = "";

        if ($data = $this->showTableDetails($table, true, $prefix)) {
            $editsql = 'ALTER TABLE ' . $prefix . $table . ' ';

            // get fields missing

            foreach ($fields as $name => $type) {
                if ($name == "id")
                    continue;

                if (!isset($data[$name])) {
                    $editsql .= ' ADD ' . $name . ' ' . $type . ' ';
                    if (isset($defaults[$name])) {
                        $editsql .= ' DEFAULT "' . addslashes($defaults[$name]) . '"';
                        $updates .= ' ' . $name . ' = "' . addslashes($defaults[$name]) . '",';
                    }
                    $editsql .= " NOT NULL,";

                    $log .= "ADD Field " . $name . " " . $type . "\n";
                } else {

                    // correct fields with edited type or default-value
                    $type = str_replace(", ", ",", $type);
                    if (str_replace('"', "'", $data[$name]["type"]) != $type && str_replace("'", '"', $data[$name]["type"]) != $type && $data[$name]["type"] != $type) {
                        $editsql .= " MODIFY " . $name . " " . $type . ",";
                        $log .= "Modify Field " . $name . " from " . $data[$name]["type"] . " to " . $type . "\n";
                    } else

                        if (!_eregi('enum', $fields[$name])) {
                            if (!isset($defaults[$name]) && $data[$name]["default"] != "") {
                                $editsql .= " ALTER COLUMN " . $name . " DROP DEFAULT,";
                            }

                            if (isset($defaults[$name]) && $data[$name]["default"] != $defaults[$name]) {
                                $editsql .= " ALTER COLUMN " . $name . " SET DEFAULT \"" . addslashes($defaults[$name]) . "\",";
                            }
                        }
                }
            }

            // get fields too much
            foreach ($data as $name => $_data) {
                if ($name != "id" && !isset($fields[$name])) {
                    // patch
                    if ($name == "default") $name = '`default`';
                    if ($name == "read") $name = '`read`';
                    $editsql .= ' DROP COLUMN ' . $name . ',';
                    $log .= "Drop Field " . $name . "\n";
                }
            }

            // @todo indexes

            $currentindexes = $this->getIndexes($table, $prefix);
            $allowed_indexes = array(); // for later delete

            // sort sql, so first drop and then add
            $removeindexsql = "";
            $addindexsql = "";


            // check indexes
            foreach ($indexes as $key => $data) {
                if (!$data)
                    continue;

                if (is_array($data)) {
                    $name = $data["name"];
                    $ifields = $data["fields"];
                    $type = $data["type"];
                } else if (preg_match("/\(/", $data)) {
                    $name = $key;
                    $allowed_indexes[$name] = true;
                    if (isset($currentindexes[$key])) {
                        $removeindexsql .= " DROP INDEX " . $key . ",";
                    }
                    $addindexsql .= " ADD " . $data . ",";
                    continue;
                } else {
                    $name = $key;
                    $ifields = array($key);
                    $type = $data;
                }
                $allowed_indexes[$name] = true;
                switch (strtolower($type)) {
                    case "unique":
                        $type = "UNIQUE";
                        break;
                    case "fulltext":
                        $type = "FULLTEXT";
                        break;
                    case "index":
                        $type = "INDEX";
                        break;
                }

                if (!isset($currentindexes[$name])) { // we have to create the index
                    $addindexsql .= " ADD " . $type . " " . $name . " (" . implode(",", $ifields) . "),";
                    $log .= "Add Index " . $name . "\n";
                } else {
                    // create matchable fields
                    $mfields = array();
                    foreach ($ifields as $key => $value) {
                        $mfields[$key] = preg_replace('/\((.*)\)/', "", $value);
                    }

                    if ($currentindexes[$name]["type"] != $type || count(array_diff($currentindexes[$name]["fields"], $mfields)) > 0) {
                        $removeindexsql .= " DROP INDEX " . $name . ",";
                        $addindexsql .= " ADD " . $type . " " . $name . "  (" . implode(",", $ifields) . "),";
                        $log .= "Change Index " . $name . "\n";
                    }
                    unset($mfields, $ifields);
                }
            }

            // check not longer needed indexes
            foreach ($currentindexes as $name => $data) {
                if ($data["type"] != "PRIMARY" && !isset($allowed_indexes[$name])) {
                    // sry, it's a hack for older versions
                    if ($name == "show") $name = '`' . $name . '`';
                    $removeindexsql .= " DROP INDEX " . $name . ", ";
                    $log .= "Drop Index " . $name . "\n";
                }
            }

            // add sql
            $editsql .= $removeindexsql;
            $editsql .= $addindexsql;
            unset($removeindexsql, $addindexsql);

            // run query
            $editsql = trim($editsql);

            if (substr($editsql, -1) == ",") {
                $editsql = substr($editsql, 0, -1);
            }

            if (sql::query($editsql)) {
                if ($updates) {
                    $updates = "UPDATE " . $prefix . $table . " SET " . $updates;
                    if (substr($updates, -1) == ",") {
                        $updates = substr($updates, 0, -1);
                    }
                    if (!SQL::Query($updates)) {
                        throw new MySQLException();
                    }
                }

                if ($version = $this->getServerVersion()) {
                    $engines = $this->listStorageEngines();
                    $tableStatuses = $this->listStorageEnginesByTable();

                    if (version_compare($version, "5.6", ">=") && isset($engines["innodb"])) {
                        if ($tableStatuses[strtolower($prefix . $table)]["Engine"] != "InnoDB") {
                            $this->setStorageEngine($prefix . $table, "InnoDB");
                        }
                    } else if (isset($engines["myisam"])) {
                        if ($tableStatuses[strtolower($prefix . $table)]["Engine"] != "MyISAM") {
                            $this->setStorageEngine($prefix . $table, "MyISAM");
                        }
                    }
                }

                ClassInfo::$database[$table] = $fields;
                return $log;
            } else
                throw new MySQLException();


        } else {
            $sql = "CREATE TABLE " . $prefix . $table . " ( ";
            $i = 0;
            foreach ($fields as $name => $value) {
                if ($i == 0) {
                    $i++;
                } else {
                    $sql .= ",";
                }
                $sql .= ' ' . $name . ' ' . $value . ' ';
                if (isset($defaults[$name])) {
                    $sql .= " DEFAULT '" . addslashes($defaults[$name]) . "'";
                }

            }

            foreach ($indexes as $key => $data) {
                if ($i == 0) {
                    $i++;
                } else {
                    $sql .= ",";
                }
                if (is_array($data)) {
                    $name = $data["name"];
                    $type = $data["type"];
                    $ifields = $data["fields"];
                } else if (_ereg("\(", $data)) {
                    $sql .= $data;
                    continue;
                } else {
                    $name = $field = $key;
                    $ifields = array($field);
                    $type = $data;
                }

                switch (strtolower($type)) {
                    case "fulltext":
                        $type = "FULLTEXT";
                        break;
                    case "unique":
                        $type = "UNIQUE";
                        break;
                    case "index":
                    default:
                        $type = "INDEX";
                        break;
                }

                $sql .= '' . $type . ' ' . $name . ' (' . implode(',', $ifields) . ')';
            }
            $sql .= ") DEFAULT CHARACTER SET 'utf8' COLLATE utf8_general_ci";
            $log .= $sql . "\n";

            if (sql::query($sql)) {
                ClassInfo::$database[$table] = $fields;

                if ($version = $this->getServerVersion()) {
                    $engines = $this->listStorageEngines();

                    if (version_compare($version, "5.6", ">=") && isset($engines["innodb"])) {
                        $this->setStorageEngine($prefix . $table, "InnoDB");
                    } else if (isset($engines["myisam"])) {
                        $this->setStorageEngine($prefix . $table, "MyISAM");
                    }
                }

                return $log;
            } else {
                throw new MySQLException();
            }
        }
    }

    /**
     * sets the default sort of a specific table
     *
     * @name setDefaultSort
     * @access public
     * @param string - table
     * @param string - field
     * @param string - optional type: DESC/ASC
     */
    public function setDefaultSort($table, $field, $type = "ASC", $prefix = false)
    {
        if (!$prefix)
            $prefix = DB_PREFIX;

        $sql = "ALTER TABLE " . $prefix . $table . " ORDER BY " . $field . " " . $type . "";
        if (SQL::Query($sql))
            return true;
        else
            return false;
    }

    /**
     * deletes a table
     *
     * @name dontRequireTable
     * @access public
     * @param string - table
     * @param string - prefix
     */
    public function dontRequireTable($table, $prefix = false)
    {
        if ($prefix === false)
            $prefix = DB_PREFIX;
        if ($data = $this->showTableDetails($table, true, $prefix)) {
            return sql::query('DROP TABLE ' . $prefix . $table . '');
        }
        return true;
    }

    /**
     * writes the manipulation-array in the database
     * there are three types of manipulation:
     * - insert
     * - update
     * - delete
     *
     * @name writeManipulation
     * @access public
     * @param array - manipulation
     */
    public function writeManipulation($manipulation)
    {
        if (PROFILE) Profiler::mark("MySQLi::writeManipulation");
        foreach ($manipulation as $class => $data) {
            switch (strtolower($data["command"])) {
                case "update":
                    if (isset($data["id"])) {
                        if (count($data["fields"]) > 0) {
                            if (
                                (isset($data["table_name"]) && $table_name = $data["table_name"]) ||
                                (ClassInfo::classTable($class) && $table_name = ClassInfo::classTable($class))
                            ) {
                                if (isset($data["ignore"]) && $data["ignore"])
                                    $sql = "UPDATE IGNORE " . DB_PREFIX . $table_name . " SET ";
                                else
                                    $sql = "UPDATE " . DB_PREFIX . $table_name . " SET ";

                                $i = 0;
                                foreach ($data["fields"] as $field => $value) {
                                    if ($i == 0) {
                                        $i++;
                                    } else {
                                        $sql .= " , ";
                                    }
                                    $sql .= " " . $field . " = '" . convert::raw2sql($value) . "' ";

                                }
                                unset($i);

                                if (isset($data["id"])) {
                                    $id = $data["id"];

                                    $sql .= " WHERE id = '" . convert::raw2sql($id) . "'";
                                } else if (isset($data["where"])) {
                                    $where = $data["where"];
                                    $where = SQL::extractToWhere($where);
                                    $sql .= $where;
                                    unset($where);
                                } else {
                                    return false;
                                }

                                if (SQL::query($sql)) {
                                    unset($id);
                                    // everything is fine
                                } else {
                                    throw new MySQLException();
                                }
                            }
                        }
                    }
                    break;
                case "insert":
                    if (count($data["fields"]) > 0) {
                        if (
                            (isset($data["table_name"]) && $table_name = $data["table_name"]) ||
                            (ClassInfo::classTable($class) && $table_name = ClassInfo::classTable($class))
                        ) {
                            if (isset($data["ignore"]) && $data["ignore"])
                                $sql = 'INSERT IGNORE INTO ' . DB_PREFIX . $table_name . ' ';
                            else
                                $sql = 'INSERT INTO ' . DB_PREFIX . $table_name . ' ';

                            $fields = ' (';
                            $values = ' VALUES (';

                            // multi data
                            if (isset($data["fields"][0])) {
                                $a = 0;
                                foreach ($data["fields"] as $fields_data) {
                                    if ($a == 0) {
                                        // do nothing, it will be done at the end, because we need it above

                                    } else {
                                        $values .= " ) , ( ";
                                    }

                                    $i = 0;
                                    foreach ($fields_data as $field => $value) {
                                        if ($i == 0) {
                                            $i++;
                                        } else {
                                            if ($a == 0) {
                                                $fields .= ",";
                                            }

                                            $values .= ", ";
                                        }

                                        if ($a == 0) {
                                            $fields .= convert::raw2sql($field);
                                        }
                                        $values .= "'" . convert::raw2sql($value) . "'";
                                    }

                                    if ($a == 0) {
                                        $a++; // now we can edit it
                                    }

                                    unset($i);
                                }
                                unset($a, $field_data);

                                // just one record
                            } else {
                                $i = 0;
                                foreach ($data["fields"] as $field => $value) {
                                    if ($i == 0) {
                                        $i++;
                                    } else {
                                        $fields .= ",";
                                        $values .= ",";
                                    }
                                    $fields .= convert::raw2sql($field);
                                    $values .= "'" . convert::raw2sql($value) . "'";
                                }
                                unset($i);
                            }
                            $fields .= ")";
                            $values .= ")";
                            $sql .= $fields . $values;
                            if (sql::query($sql)) {
                                unset($fields, $values);
                                // everything is fine
                            } else {
                                throw new MySQLException();
                            }
                        }
                    }
                    break;
                case "delete":
                    if (!isset($data["where"]) && isset($data["id"]))
                        $data["where"]["id"] = $data["id"];

                    if (isset($data["where"])) {
                        if (
                            (isset($data["table_name"]) && $table_name = $data["table_name"]) ||
                            (ClassInfo::classTable($class) && $table_name = ClassInfo::classTable($class))
                        ) {
                            $where = $data["where"];
                            $where = SQL::extractToWhere($where);

                            $sql = "DELETE FROM " . DB_PREFIX . $table_name . $where;

                            if (sql::query($sql)) {
                                // everything is fine
                            } else {
                                throw new MySQLException();
                            }
                        }
                    }

                    break;
                default:
                    if (PROFILE) Profiler::unmark("MySQLi::writeManipulation");
                    return false;
                    break;
            }
        }
        if (PROFILE) Profiler::unmark("MySQLi::writeManipulation");
        return true;
    }

    /**
     * storage engines.
     */
    public function listStorageEngines()
    {

        if ($this->engines) {
            return $this->engines;
        }

        $sql = "SHOW ENGINES";
        if ($result = self::query($sql)) {
            $data = array();
            while ($row = self::fetch_assoc($result)) {
                if (strtolower($row["Support"]) != "NO") {
                    $data[strtolower($row["Engine"])] = strtolower($row["Engine"]);
                }
            }

            $this->engines = $data;
            return $data;
        }

        return array();
    }

    public function setStorageEngine($table, $engine)
    {

        $sql = "ALTER TABLE " . $table . " ENGINE = " . $engine . "";
        if (self::query($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function getServerVersion()
    {
        if ($this->version) {
            return $this->version;
        }

        $sql = "SHOW VARIABLES LIKE 'version'";
        if ($result = $this->Query($sql)) {
            if ($row = $this->fetch_assoc($result)) {
                $this->version = $row["Value"];
                return $this->version;
            }
        }

        return false;
    }

    public function listStorageEnginesByTable()
    {
        if ($this->tableStatuses) {
            return $this->tableStatuses;
        }

        $data = array();
        $sql = "SHOW TABLE STATUS";
        if ($result = $this->query($sql)) {
            while ($row = $this->fetch_assoc($result)) {
                $data[strtolower($row["Name"])] = $row;
            }

            $this->tableStatuses = $data;
            return $data;
        }

        return false;
    }

    /**
     * sets the charset to utf-8
     *
     * @name setCharsetUTF8
     * @access public
     */
    public function setCharsetUTF8()
    {
        $this->_db->set_charset("utf8");
    }
}
