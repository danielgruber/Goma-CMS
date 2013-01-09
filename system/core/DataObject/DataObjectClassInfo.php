<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 09.01.2013
  * $Version 4.1.1
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
				DataObject::$donothing = true;
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
						if(ClassInfo::hasStatic($c->class, "table"))
							$table_name = ClassInfo::getStatic($c->class, "table");
						else
							$table_name = ($c->table_name == "") ? $c->prefix . $class : $c->table_name;
						
						
						$defaults = $c->GenerateDefaults();
						$many_many = $c->GenerateMany_Many();
						$db_fields = $c->generateDBFields();
						$belongs_many_many = $c->GenerateBelongs_Many_Many();
						
						$searchable_fields = Object::hasStatic($class, "search_fields") ? Object::getStatic($class, "search_fields") : array();
						if(isset($class->searchable_fields))
							$searchable_fields = array_merge($searchable_fields, $class->searchable_fields);
						
						$indexes = $c->generateIndexes();
						
						$many_many_tables = array();
						
						$has_one["autor"] = "user";
						if(isset($db_fields["class_name"])) {
							//$indexes["class_name"] = "INDEX";
							$indexes["last_modified"] = "INDEX";
						}
						
						if(count($db_fields) > 0)
						{
							$db_fields["autorid"] = "int(10)";
							$db_fields["editorid"] = "int(10)";
						}
						
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
						
						$has_one = arraylib::map_key($has_one, "strtolower");
						$has_many = arraylib::map_key($has_many, "strtolower");
						$many_many = arraylib::map_key($many_many, "strtolower");
						$belongs_many_many = arraylib::map_key($belongs_many_many, "strtolower");
						
						$has_one["editor"] = "user";
						$has_one["autor"] = "user";
						
						if(count($has_one) > 0) ClassInfo::$class_info[$class]["has_one"] = $has_one;
						if(count($has_many) > 0) ClassInfo::$class_info[$class]["has_many"] = $has_many;
						if(count($db_fields) > 0) ClassInfo::$class_info[$class]["db"] = $db_fields;
						if(count($many_many) > 0) ClassInfo::$class_info[$class]["many_many"] = $many_many;
						if(count($belongs_many_many) > 0) ClassInfo::$class_info[$class]["belongs_many_many"] = $belongs_many_many;
						if(count($defaults) > 0) ClassInfo::$class_info[$class]["defaults"] = $defaults;
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
								if(strtolower(get_parent_class($_c)) == "dataobject" || strtolower(get_parent_class($_c)) == "array_dataobject")
								{
										ClassInfo::$class_info[$class]["baseclass"] = $_c;
								} else
								{
										ClassInfo::$class_info[$class]["dataclasses"][] = $_c;
								}
								
								$_c = strtolower(get_parent_class($_c));												
						}
						unset($_c, $parent, $c);
				} else
		
				if(class_exists($class) && class_exists("viewaccessabledata") && is_subclass_of($class, "viewaccessabledata") && !ClassInfo::isAbstract($class)) {
					if($casting = Object::instance($class)->generateCasting())
						if(count($casting) > 0)
							ClassInfo::$class_info[$class]["casting"] = $casting;
				}
				DataObject::$donothing = false;
				
				if(PROFILE) Profiler::unmark("DataObjectClassInfo::generate");
		}
		
}

Object::extend("ClassInfo", "DataObjectClassInfo");