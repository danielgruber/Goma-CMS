<?php defined('IN_GOMA') OR die(); 


/**
  * @package    goma framework
  * @link       http://goma-cms.org
  * @license:   LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  * @author     Goma-Team
  * @version 	4.1.7
*/
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
						$classInstance = Object::instance($class);

						$has_one = ModelInfoGenerator::generateHas_one($class);
						$has_many = ModelInfoGenerator::generateHas_many($class);
						
						// generate table_name
						if(StaticsManager::hasStatic($class, "table")) {
							$table_name = StaticsManager::getStatic($class, "table");
						} else {
							$table_name = $classInstance->prefix . str_replace("\\", "_", $class);
						}
						
						
						$many_many = ModelInfoGenerator::generateMany_many($class);
						$db_fields = ModelInfoGenerator::generateDBFields($class);
						$belongs_many_many = ModelInfoGenerator::generateBelongs_many_many($class);
						
						$searchable_fields = ModelInfoGenerator::generate_search_fields($class);
						
						$indexes = ModelInfoGenerator::generateIndexes($class);

						/* --- */
						
						foreach($indexes as $key => $value)
						{
								if(is_array($value))
								{
										$fields = $value["fields"];
										$indexes[$key]["fields"] = array();
										if(!is_array($fields))
											$fields = explode(",", $fields);
											
										$maxlength = $length = floor(333 / count($fields));
										$fields_ordered = array();
			
										foreach($fields as $field)
										{
												if(isset($db_fields[$field]))
												{
													if(preg_match('/\(\s*([0-9]+)\s*\)/Us', $db_fields[$field], $matches))
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
												} else if(preg_match('/enum/i', $db_fields[$field])) {
													$indexes[$key]["fields"][] = $field;
												} else {
													$length = $maxlength;
													// support for ASC/DESC
													if(_eregi("(ASC|DESC)", $field, $matches)) {
														$field = preg_replace("/(ASC|DESC)/i", "", $field);
														$indexes[$key]["fields"][] = $field . " (".$length.") ".$matches[1]."";
													} else {
														$indexes[$key]["fields"][] = $field . " (".$length.")";
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
						if($casting = $classInstance->generateCasting())
							if(count($casting) > 0)
								ClassInfo::$class_info[$class]["casting"] = $casting;
					
						if(count($has_one) > 0) ClassInfo::$class_info[$class]["has_one"] = $has_one;
						if(count($has_many) > 0) ClassInfo::$class_info[$class]["has_many"] = $has_many;
						if(count($db_fields) > 0) ClassInfo::$class_info[$class]["db"] = $db_fields;
						if(count($many_many) > 0) ClassInfo::$class_info[$class]["many_many"] = $many_many;
						if(count($belongs_many_many) > 0) ClassInfo::$class_info[$class]["belongs_many_many"] = $belongs_many_many;

						if(count($searchable_fields) > 0) ClassInfo::$class_info[$class]["search"] = $searchable_fields;
						if(count($indexes) > 0) ClassInfo::$class_info[$class]["index"] = $indexes;

						
						
						/* --- */
						
						
						ClassInfo::$class_info[$class]["many_many_tables"] = ModelManyManyRelationShipInfo::generateManyManyTables($class);
						
						// many-many
						foreach($many_many as $key => $targetClass) {
							$table = ClassInfo::$class_info[$class]["many_many_tables"][$key]["table"];
							if(!ClassInfo::isAbstract($targetClass)) {
    							$many_many_tables_belongs = ModelManyManyRelationShipInfo::generateManyManyTables($targetClass);
    							
    							foreach($many_many_tables_belongs as $data) {
    								if($data["table"] == $table) {
    									continue 2;
    								}
    							}
							}
							
							
							ClassInfo::$class_info[$targetClass]["belongs_many_many_extra"][$table] = array(
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
						unset($_c, $parent, $classInstance);
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
