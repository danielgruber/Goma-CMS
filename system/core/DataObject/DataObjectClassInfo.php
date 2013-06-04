<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 24.01.2013
  * $Version 4.1.2
*/
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class DataObjectClassInfo extends Extension
{
		/**
		 * generates extra class-info for dataobject
		 *@name generate
		 *@access public
		 *@param string - class
		*/
		public function generate($class)
		{
				if(PROFILE) Profiler::mark("DataObjectClassInfo::generate");
				if(class_exists($class) && class_exists("DataObject") && is_subclass_of($class, "DataObject"))
				{
						
						// do nothing, so just get class
						// first argument where
						// second fields
						// third, force do nothing
						$c = Object::instance($class);
							
						$has_one = $c->GenerateHas_One();
						$has_many = $c->GenerateHas_Many();
						
						// generate table_name
						if(ClassInfo::hasStatic($c->classname, "table")) {
							$table_name = ClassInfo::getStatic($c->classname, "table");
						} else if(isset($c->table_name)) {
							Core::deprecate("2.0", "Class ".$this->classname." uses old table_name-Attribute, use static \$table instead.");
							$table_name = $c->table_name;
						} else {
							$table_name = $c->prefix . $class;
						}
						
						
						$many_many = $c->GenerateMany_Many();
						$db_fields = $c->generateDBFields();
						$belongs_many_many = $c->GenerateBelongs_Many_Many();
						
						$searchable_fields = Object::hasStatic($class, "search_fields") ? Object::getStatic($class, "search_fields") : array();
						if(isset($class->searchable_fields)) {
							Core::deprecate("2.0", "Class ".$this->classname." uses old searchable_fields-Attribute, use static \$search_fields instead.");
							$searchable_fields = array_merge($searchable_fields, $class->searchable_fields);
						}
						
						$indexes = $c->generateIndexes();
						
						$many_many_tables = array();
						
						/* --- */
						
						foreach($indexes as $key => $value)
						{
								if(is_array($value))
								{
										$fields = $value["fields"];
										$indexes[$key]["fields"] = array();
										$fields = explode(",", $fields);
										$maxlength = $length = floor(333 / count($fields));
										$fields_ordered = array();
										
										foreach($fields as $field)
										{
												if(isset($db_fields[$field]))
												{
													if(_ereg('\(\s*([0-9]+)\s*\)', $db_fields[$field], $matches))
													{
														
														$fields_ordered[$field] = $matches[1] - 1;
													} else
													{
														$fields_ordered[$field] = $maxlength;
													}
												} else {
													unset($indexes[$key]);
													unset($fields_ordered);
													break;
												}
										}
										if(isset($fields_ordered)) {
											$indexlength = 333;
											
											$i = 0;
											foreach($fields_ordered as $field => $length) {
												if($length < $maxlength) {
													
													$maxlength = floor($indexlength / (count($fields) - $i));
													$indexlength -= $length;
													$indexes[$key]["fields"][] = $field;
												} else if(_eregi('enum', $db_fields[$field])) {
													$indexes[$key]["fields"][] = $field;
												} else {
													$length = $maxlength;
													// support for ASC/DESC
													if(_eregi("(ASC|DESC)", $field, $matches)) {
														$field = preg_replace("/(ASC|DESC)/i", "", $field);
														$indexes[$key]["fields"][] = $field . "(".$length.") ".$matches[1]."";
													} else {
														$indexes[$key]["fields"][] = $field . "(".$length.")";
													}
													unset($matches);
												}
												
												
												$i++;
											}
										}
										
								} else if(isset($db_fields[$key]))
								{
										$indexes[$key] = $value;
								} else if(!$value) {
									unset($db_fields[$key]);
								}
								unset($key, $value, $fields, $maxlength, $fields_ordered, $i);
						}
						
						
						/*
						 * get SQL-Types, so objects for parsing special data in sql-fields
						*/
						if($casting = $c->generateCasting())
							if(count($casting) > 0)
								ClassInfo::$class_info[$class]["casting"] = $casting;
					
						if(count($has_one) > 0) ClassInfo::$class_info[$class]["has_one"] = $has_one;
						if(count($has_many) > 0) ClassInfo::$class_info[$class]["has_many"] = $has_many;
						if(count($db_fields) > 0) ClassInfo::$class_info[$class]["db"] = $db_fields;
						if(count($many_many) > 0) ClassInfo::$class_info[$class]["many_many"] = $many_many;
						if(count($belongs_many_many) > 0) ClassInfo::$class_info[$class]["belongs_many_many"] = $belongs_many_many;
						//ClassInfo::$class_info[$class]["prefix"] = $c->prefix;
						if(count($searchable_fields) > 0) ClassInfo::$class_info[$class]["search"] = $searchable_fields;
						if(count($indexes) > 0) ClassInfo::$class_info[$class]["index"] = $indexes;
						//Classinfo::$class_info[$class]["iDBFields"] = arraylib::map_key($c->generateiDBFields(), "strtolower");
						
						
						/* --- */
						
						
						ClassInfo::$class_info[$class]["many_many_tables"] = $c->generateManyManyTables();
						
						// many-many
						foreach($many_many as $key => $_class) {
							$table = ClassInfo::$class_info[$class]["many_many_tables"][$key]["table"];
							$many_many_tables_belongs = Object::instance($_class)->generateManyManyTables();
							
							foreach($many_many_tables_belongs as $data) {
								if($data["table"] == $table) {
									continue 2;
								}
							}
							
							ClassInfo::$class_info[$_class]["belongs_many_many_extra"][$key] = array(
								"table" 	=> $table,
								"field"		=> ClassInfo::$class_info[$class]["many_many_tables"][$key]["extfield"],
								"extfield"	=> ClassInfo::$class_info[$class]["many_many_tables"][$key]["field"]
							);
							
							unset($table, $many_many_table_belongs);
						}
						
						foreach(ClassInfo::$class_info[$class]["many_many_tables"] as $data) {
							if(defined("SQL_LOADUP") && $fields = SQL::getFieldsOfTable($data["table"])) {
								ClassInfo::$database[$data["table"]] = $fields;
								unset($fields, $data);
							}
						}
						
						unset($key, $data, $fields);
						
						/*
						 * check if we need a sql-table
						*/
						
						if(count($db_fields) == 0)
						{
								ClassInfo::$class_info[$class]["table"] = false;
								ClassInfo::$class_info[$class]["table_exists"] = false;
						} else
						{
								ClassInfo::$class_info[$class]["table"] = $table_name;
								ClassInfo::addTable($table_name, $class);
								if(defined("SQL_LOADUP") && $fields = SQL::getFieldsOfTable($table_name))
								{
										ClassInfo::$database[$table_name] = $fields;
										ClassInfo::$class_info[$class]["table_exists"] = true;
								} else
								{
										ClassInfo::$class_info[$class]["table_exists"] = false;
								}
						}
						
						unset($db_fields, $many_many, $has_one, $has_many, $searchable_fields, $belongs_many_many);
						
						// get data classes
						
						$parent = strtolower(get_parent_class($class));
						
						if($parent == "dataobject" || $parent == "array_dataobject")
						{
								ClassInfo::$class_info[$class]["baseclass"] = $class;
						}
						
						if($parent != "dataobject" && $parent != "array_dataobject")
						{
								ClassInfo::$class_info[$class]["dataclasses"][] = $class;
						}
						
						$_c = $parent;
						while($_c != "dataobject" && $_c != "array_dataobject")
						{
								if(ClassInfo::$class_info[$class]["table"] !== false)
								{
										ClassInfo::$class_info[$_c]["dataclasses"][] = $class;
								}
								if(strtolower(get_parent_class($_c)) == "dataobject")
								{
										ClassInfo::$class_info[$class]["baseclass"] = $_c;
								} else
								{
										ClassInfo::$class_info[$class]["dataclasses"][] = $_c;
								}
								
								$_c = strtolower(get_parent_class($_c));												
						}
						unset($_c, $parent, $c);
				}
		
				if(class_exists($class) && class_exists("viewaccessabledata") && is_subclass_of($class, "viewaccessabledata") && !ClassInfo::isAbstract($class)) {
					if(!class_exists("DataObject") || !is_subclass_of($class, "DataObject"))
						if($casting = Object::instance($class)->generateCasting())
							if(count($casting) > 0)
								ClassInfo::$class_info[$class]["casting"] = $casting;
					
					if($defaults = Object::instance($class)->generateDefaults())
						if(count($defaults) > 0)
							ClassInfo::$class_info[$class]["defaults"] = $defaults;
				}
				
				if(PROFILE) Profiler::unmark("DataObjectClassInfo::generate");
		}
		
}

Object::extend("ClassInfo", "DataObjectClassInfo");