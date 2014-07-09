<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 13.11.2010
  * $Version: 2.0.0 - 001
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class rawHeaders extends ViewAccessAbleData
{
		/**
		 *@name __construct
		 *@access public
		 *@param array|string headers as array or string
		*/
		public function __construct($headers = array())
		{
				parent::__construct();
				
				/* --- */
				
				if(is_array($headers))
				{
						foreach($headers as $key => $value)
						{
								$this[strtolower($key)] = $value;
						}
				} else if(is_string($headers))
				{
						$lines = explode("\n", $headers);
						foreach($lines as $line)
						{
								$keyvalue = explode(":", $line, 2);
								if(count($keyvalue) > 1)
								{
										$key = trim($keyvalue[0]);
										$data = trim($keyvalue[1]);
										unset($keyvalue);
										if(strpos($data, ";"))
										{
												$parts  = explode(";", $data);
												$value = array();
												$i = 0;
												foreach($parts as $part)
												{
														if($i == 0)
														{
																$value[strtolower($key)] = $part;
																$i++;
														} else
														{
																$keyvalue = explode("=", $part, 2);
																// trim for whitespace and substr for the two "
																$value[strtolower(trim($keyvalue[0]))] = stripslashes(substr(trim($keyvalue[1]), 1, -1));
														}
												}
										} else
										{
												$value = $data;
										}
										
										$this[strtolower($key)] = $value;
								}
						}
				}
		}
		/**
		 * gets the headers as string
		 *@name __toString
		 *@access public
		*/
		public function __toString()
		{
				$str = "";
				foreach($this->data as $key => $value)
				{
						if(is_string($value))
						{
								$str .= "".$key.": ".$value."\n";
						} else if(is_array($value))
						{
								$str .= "".$key.": ";
								$i = 0;
								foreach($value as $_key => $_value)
								{
										if($i == 0)
										{
												$i++;
										} else
										{
												$str .= ";";
										}
										if($_key == 0 || $_key == $key)
												$str .= $_value;
										else
												$str .= "".$_key."=\"".addslashes($_value)."\"";
								}
								$str .= "\n";
						}
				}
				return $str;
		}
}