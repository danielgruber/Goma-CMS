<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 24.03.2013
  * $Version 1.4.5
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

interface DataBaseField {
	/**
	 * constructor
	*/
	public function __construct($name, $value, $args = array());
	
	/**
	 * set the value of the field
	 *
	 *@name setValue
	*/
	public function setValue($value);
	
	/**
	 * gets the value of the field
	 *
	 *@name getValue
	*/
	public function getValue();
	
	/**
	 * sets the name of the field
	 *
	 *@name setName
	*/
	public function setName($name);
	
	/**
	 * gets the name of the field
	 *
	 *@name getName
	*/
	public function getName();
	
	/**
	 * gets the raw-data of the field
	 * should be give back the same as getValue
	 *
	 *@name raw
	*/
	public function raw();
	
	/**
	 * generates the default form-field for this field
	 *
	 *@name formfield
	 *@access public
	 *@param string - title
	*/
	public function formfield($title = null);
	
	/**
	 * search-field for searching
	 *
	 *@name searchfield
	 *@access public
	 *@param string - title
	*/
	public function searchfield($title = null);
	
	/**
	 * this function uses more than one convert-method
	 *
	 *@name convertMulti
	 *@access public
	 *@param array - methods
	*/
	public function convertMulti($arr);
	
	/**
	 * gets the field-type for the database, for example if you want to have the type varchar instead of the name of this class
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array());
	
	/**
	 * toString-Method
	 * should call default-convert
	 *
	 *@name __toString
	 *@access public
	*/
	public function __toString();
	
	/**
	 * bool - for IF in template
	 * should give back if the value of this field represents a false or true
	 *
	 *@name toBool
	 *@access public
	*/
	public function toBool();
	
	/**
	 * to don't give errors for unknowen calls, should always give back raw-data
	 *
	 *@name __call
	 *@access public
	*/
	public function __call($name, $args);
	
	/**
	 * bool, like toBool
	*/
	public function bool();
	
	/**
	 * returns datatype for view
	*/
	public function forTemplate();
}


/**
 * Every value of an field can used as object if you call doObject($offset)
 * This Object has some very cool methods to convert the field
*/
class DBField extends Object implements DataBaseField
{
	/**
	 * this var contains the value
	 *@name value
	 *@var mixed
	*/
	protected $value;
	
	/**
	 * this field contains the field-name of this object
	 *@name name
	 *@access protected
	*/
	protected $name;
	
	/**
	 * args
	 *
	 *@name args
	 *@access public
	*/
	public $args = array();
	
	/**
	 * cache for casting
	 *
	 *@name castingCache
	 *@access private
	*/
	private static $castingCache = array();
	
	/**
	 *@name __construct
	 *@access public
	 *@param mixed - value
	*/
	public function __construct($name, $value, $args = array())
	{
			$this->name = $name;
			$this->value = $value;
			$this->args = $args;
			parent::__construct();
	}
	/**
	 * sets the value
	 *@name setvalue
	 *@access public
	*/
	public function setValue($value)
	{
			$this->value = $value;
	}
	/**
	 * gets the value
	 *@name getValue
	 *@access public
	*/
	public function getValue()
	{
			return $this->value;
	}
	/**
	 * sets the name
	 *@name setName
	 *@access public
	*/
	public function setName($name)
	{
			$this->name = $name;
	}
	/**
	 * gets the anme
	 *@name getName
	 *@access public
	*/
	public function getName()
	{
			return $this->name;
	}
	
	/**
	 *@name raw
	 *@access public
	*/
	public function raw()
	{
			return $this->value;
	}
	
	/**
	 * get it as text
	 *@name text
	 *@access public
	*/
	public function text()
	{
		return convert::raw2xml($this->value);
	}
	
	/**
	 * get it as text and correct  linebreaks
	 *@name textLines
	 *@access public
	*/
	public function textLines()
	{
		return convert::raw2xmlLines($this->value);
	}
	
	/**
	 * get this as url
	 *@name url
	 *@access public
	*/
	public function url()
	{
			return convert::raw2text(urlencode($this->value));
	}
	/**
	 * for js
	 *@name JS
	 *@access public
	*/
	public function js()
	{
			return str_replace(array("\n","\t","\r","\"","'"), array('\n', '\t', '\r', '\\"', '\\\''), $this->value);
	}
	/**
	 * converts string to uppercase
	 *@name uppercase
	 *@access public
	*/ 
	public function UpperCase()
	{
			return strtoupper($this->value);
	}
	/**
	 * converts string to lowerase
	 *@name uppercase
	 *@access public
	*/ 
	public function LowerrCase()
	{
			return strtolower($this->value);
	}
	
	
	/**
	 * Layer for form-fields
	*/
	
	/**
	 * generates the default form-field for this field
	 *@name formfield
	 *@access public
	 *@param string - title
	*/
	public function formfield($title = null)
	{
			$field = new TextField($this->name, $title, $this->value);
			
			return $field;
	}
	
	/**
	 * search-field for searching
	 *@name searchfield
	 *@access public
	 *@param string - title
	*/
	public function searchfield($title = null)
	{
			return $this->formfield($title);
	}
	
	/**
	 * this function uses more than one convert-method
	 *@name convertMulti
	 *@access public
	 *@param array - methods
	*/
	public function convertMulti($arr)
	{
			$new = clone $this;
			foreach($arr as $method)
			{
					if(Object::method_exists($new, $method))
					{
							$new->setValue($new->$method());
					}
			}
			return $new->getValue();
	}
	
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return "";
	}
	
	/**
	 * toString-Method
	 *@name __toString
	 *@access public
	*/
	public function __toString()
	{
		return $this->forTemplate();
	}
	
	/**
	 * gets Data Converted for Template
	 *
	 *@name forTemplate
	*/
	public function forTemplate() {
		return (string) $this->value;
	}
	
	/**
	 * bool - for IF in template
	 *
	 *@name toBool
	 *@access public
	*/
	public function toBool() {
		return (bool) $this->value;
	}
	/**
	 * calls
	 *
	 *@name __call
	 *@access public
	*/
	public function __call($name, $args) {
		if(DEV_MODE) {
			$trace = debug_backtrace();
			log_error('Warning: Call to undefined method ' . $this->class . '::' . $name . ' in '.$trace[0]['file'].' on line '.$trace[0]['line']);
			if(DEV_MODE)
		    		AddContent::add('<div class="error"><b>Warning</b> Call to undefined method ' . $this->class . '::' . $name . '</div>');
		}
		return $this->__toString();
	}
	
	/**
	 * bool
	*/
	public function bool() {
		return ($this->value);
	}
		
	/**
	 * parses casting-args and gives back the result 
	 *
	 *@name parseCasting
	 *@access public
	 *@param string - casting
	*/
	public static function parseCasting($casting) {
		
		if(isset(self::$castingCache[$casting]))
			return self::$castingCache[$casting];
			
		if(PROFILE) Profiler::mark("DBField::parseCasting");
		
		if(is_array($casting))
			return $casting;

		if(preg_match('/\-\>([a-zA-Z0-9_]+)\s*$/Usi', $casting, $matches)) {
			$method = $matches[1];
			$casting = substr($casting, 0, 0 - strlen($method) - 2);
			unset($matches);
		}
		
		if(strpos($casting, "(")) {
			
			$name = trim(substr($casting, 0, strpos($casting, "(")));
			if(preg_match('/\(([^\(\)]+)\)?/', $casting, $matches)) {
				$args = $matches[1];
				$args = eval('return array('.$args.');');
			} else {
				$args = array();
			}
			unset($matches);
		} else {
			$args = array();
			$name = trim($casting);
		}
		
		
		if(ClassInfo::exists($name) && ClassInfo::hasInterface($name, "DataBaseField")) {
			$valid = true;
		} else if (ClassInfo::exists($name . "SQLField") && ClassInfo::hasInterface($name . "SQLField", "DataBaseField")) {
			$name = $name . "SQLField";
			$valid = true;
		}
		
		if(!isset($valid)) {
			self::$castingCache[$casting] = null;
			if(PROFILE) Profiler::unmark("DBField::parseCasting");
			return null;
		}
		
		$data = array(
			"class" => $name
		);
		
		if(!empty($args))
			$data["args"] = $args;
		
		if(ClassInfo::hasInterface($name, "DefaultConvert")) {
			$data["convert"] = true;
		}
		
		if(isset($method)) {
			$data["method"] = $method;
		}
		
		self::$castingCache[$casting] = $data;
		if(PROFILE) Profiler::unmark("DBField::parseCasting");
		
		return $data;
	}
	
	/**
	 * gets a var for template
	 *
	 *@name getTemplateVar
	*/
	public function getTemplateVar($var) {
		if(strpos($var, ".")) {
			throwError(6, "Invalid Argument Error", "Argument " . $var . " is not allowed in a DB-Field, because it's recursive.");
		}
		
		// check for args
		if(strpos($var, "(") && substr($var, -1) == ")") {
			$args = eval("return array(" . substr($var, strpos($var, "(") + 1, -1) . ");");
			$var = substr($var, 0, strpos($var, "("));
		} else {
			$args = array();
		}
		
		return call_user_func_array(array($this, $var), $args);
	}
	
	/**
	 * converts by casting
	 *
	 *@name convertByCasting
	 *@access public
	 *@param string|array casting
	 *@param string - name
	 *@param mixed - value
	*/
	public static function convertByCasting($casting, $name, $value) {
		if(!is_string($name)) {
			throwError(6, "Invalid Argument", "Second argument (\$name) of DBField::convertByCasting must be an string.");
		}
		$casting = self::parseCasting($casting);
		if(isset($casting)) {
			$object = new $casting["class"]($name, $value, isset($casting["args"]) ? $casting["args"] : array());
			if(isset($casting["method"])) {
				return call_user_func_array(array($object, $casting["method"]), array());
			} else {
				return $object->__toString();
			}
		} else {
			throwError(6, "Logical Exception", "Invalid casting given to DBField::convertByCasting");
		}
	}
	
	/**
	 * converts by casting if convertDefault
	 *
	 *@name convertByCastingIfDefault
	 *@access public
	 *@param string|array casting
	 *@param string - name
	 *@param mixed - value
	*/
	public static function convertByCastingIfDefault($casting, $name, $value) {
		if(!is_string($name)) {
			throwError(6, "Invalid Argument", "Second argument (\$name) of DBField::convertByCastingIfDefault must be an string.");
		}
		$casting = self::parseCasting($casting);
		if(isset($casting["convert"]) && $casting["convert"]) {
			return self::convertByCasting($casting, $name, $value);
		}
		
		return $value;
	}
	
	/**
	 * gets an object by casting
	 *
	 *@name getObjectByCasting
	 *@access public
	 *@param string|array casting
	 *@param string - name
	 *@param mixed - value
	*/
	public static function getObjectByCasting($casting, $name, $value, $throwErrorOnFail = false) {
		if(!is_string($name)) {
			throwError(6, "Invalid Argument", "Second argument (\$name) of DBField::getObjectByCasting must be an string.");
		}
		$casting = self::parseCasting($casting);
		if(isset($casting)) {
			return new $casting["class"]($name, $value, isset($casting["args"]) ? $casting["args"] : array());
		} else if($throwErrorOnFail) {
			throwError(6, "Logical Exception", "Invalid casting given to DBField::getObjectByCasting '".$casting."'");
		} else {
			return new DBField($name, $value, array());
		}
	}
	
	/**
	 * returns false because no object can be done
	 *
	 *@name canDoObject
	*/
	public function canDoObject() {
		return false;
	}
}

/**
 * Every value of an field can used as object if you call doObject($offset) for varchar-fields
 * This Object has some very cool methods to convert the field
*/
class Varchar extends DBField
{
		/**
		 * strips all tags of the value
		 *@name striptags
		 *@access public
		*/
		public function strtiptags()
		{
				return striptags($this->value);
		}
		
		/**
		 * makes a substring of this value
		 *@name substr
		 *@access public
		*/
		public function substr($start, $length = null)
		{
				if($length === null)
				{
						return substr($this->value, $start);
				} else
				{
						return substr($this->value, $start, $length);
				}
		}
		/**
		 * this returns the length of the string
		 *@name length
		 *@access public
		*/
		public function length()
		{
				return strlen($this->value);
		}
		
		/**
		 * generates a special dynamic form-field
		 *@name formfield
		 *@access public
		 *@param string - title
		*/
		public function formfield($title = null)
		{
				
				if(strpos($this->value, "\n"))
				{
						return new TextArea($this->name, $title);
				} else
				{
						return parent::formfield($title);
				}
		}
		
		/**
		 * renders text as BBcode
		 *@name bbcode
		 *@access public
		*/
		public function bbcode()
		{
				$text = new Text($this->value);
				return $text->bbcode();
		}
		
		/**
		 * converts this with date
		 *@name date
		 *@access public
		*/
		public function date($format =	DATE_FORMAT)
		{	
			return goma_date($format, $this->value);
		}
		
		/**
		 * for template
		 *
		 *@name forTemplate
		*/
		public function forTemplate() {
			return $this->text();
		}
}



/**
 * Every value of an field can used as object if you call doObject($offset) for text-fields
 * This Object has some very cool methods to convert the field
*/
class TextSQLField extends Varchar
{
		/**
		 * converts the text to one line
		 *@name oneline
		 *@access public
		*/
		public function oneline()
		{
				return str_replace(array("\n", "\r"), '', $this->value);
		}
		/**
		 * niceHTML
		 *@name niceHTML
		 *@access public
		 *@param string - left
		*/
		public function niceHTML($left = "	")
		{
				echo 1;
				$value = $this->value;
				$value = str_replace("\n", "\n" . $left, $value);
				
				return "\n" . $value;
		}
		/**
		 * generatesa a textarea
		 *@name formfield
		 *@access public
		 *@param string - title
		*/
		public function formfield($title = null)
		{
				return new TextArea($this->name, $title);
		}
}

/**
 * Every value of an field can used as object if you call doObject($offset) for Int-fields
 * This Object has some very cool methods to convert the field
*/
class intSQLField extends Varchar
{
		/**
		 * generatesa a numeric field
		 *@name formfield
		 *@access public
		 *@param string - title
		*/
		public function formfield($title = null)
		{
				return new NumberField($this->name, $title);
		}
}

class CheckBoxSQLField extends DBField {
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return 'enum("0","1")';
	}
	/**
	 * generatesa a numeric field
	 *@name formfield
	 *@access public
	 *@param string - title
	*/
	public function formfield($title = null)
	{
			return new Checkbox($this->name, $title, $this->value);
	}
}

class SwitchSQLField extends DBField {
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return 'enum("0","1")';
	}
	/**
	 * generatesa a numeric field
	 *@name formfield
	 *@access public
	 *@param string - title
	*/
	public function formfield($title = null)
	{
			return new Checkbox($this->name, $title, $this->value);
			//return new radiobutton($this->name, $title, array(1 => lang("active"),0 => lang("disabled"))); 
	}
}

/**
 * timezone-field
*/
class TimeZone extends DBField {
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return 'enum("'.implode('","', i18n::$timezones).'")';
	}
	
	/**
	 * generatesa a numeric field
	 *@name formfield
	 *@access public
	 *@param string - title
	*/
	public function formfield($title = null)
	{
			return new Select($this->name, $title, ArrayLib::key_value(i18n::$timezones), $this->value);
	}
}

class DateSQLField extends DBField {
	
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return "int(30)";
	}
	
	/**
	 * default convert
	*/
	public function forTemplate() {
		if(isset($this->args[0]))
			return $this->date($this->args[0]);
		else
			return $this->date();
	}
	
	/**
	 * converts this with date
	 *@name date
	 *@access public
	*/
	public function date($format =	DATE_FORMAT)
	{	
		return goma_date($format, $this->value);
	}
	
	/**
	 * returns date as ago
	 *
	 *@name ago
	 *@access public
	*/
	public function ago($fullSentence = true) {
		if(NOW - $this->value < 60) {
			return '<span title="'.$this->forTemplate().'" class="ago-date">' . sprintf(lang("ago.seconds", "about %d seconds ago"), round(NOW - $this->value)) . '</span>';
		} else if(NOW - $this->value < 90) {
			return '<span title="'.$this->forTemplate().'" class="ago-date">' . lang("ago.minute", "about one minute ago") . '</span>';
		} else {
			$diff = NOW - $this->value;
			$diff = $diff / 60;
			if($diff < 60) {
				return '<span title="'.$this->forTemplate().'">' . sprintf(lang("ago.minutes", "%d minutes ago"), round($diff)) . '</span>';
			} else {
				$diff = round($diff / 60);
				if($diff == 1) {
					return '<span title="'.$this->forTemplate().'">' . lang("ago.hour", "about one hour ago") . '</span>';
				} else {
					if($diff < 24) {
						return '<span title="'.$this->forTemplate().'">' . sprintf(lang("ago.hours", "%d hours ago"), round($diff)) . '</span>';
					} else {
						$diff = $diff / 24;
						$diffRound = round($diff, 1);
						if($diff <= 1.1) {
							return '<span title="'.$this->forTemplate().'">' . lang("ago.day", "about one day ago") . '</span>';
						} else if($diff <= 7) {
							$pre = ($fullSentence) ? lang("version_at") . " " : "";
							return '<span title="'.$this->forTemplate().'">' . $pre . sprintf(lang("ago.weekday", "%s at %s"), $this->date("l"), $this->date("H:i")) . '</span>';
						} else {
							if($fullSentence)
								return lang("version_at") . " " . $this->forTemplate();
							else
								return $this->forTemplate();
						}
					}
				}
			}
		}
	}
}

class HTMLText extends Varchar {
	/**
	 * gets the field-type
	 *
	 *@name getFieldType
	 *@access public
	*/
	static public function getFieldType($args = array()) {
		return "mediumtext";
	}
	
	/**
	 * generatesa a numeric field
	 *
	 *@name formfield
	 *@access public
	 *@param string - title
	*/
	public function formfield($title = null)
	{
			return new HTMLEditor($this->name, $title, $this->value);
	}
	
	/**
	 * for template
	 *
	 *@name forTemplate
	*/
	public function forTemplate() {
		// parse a bit
		$value = $this->value;
		
		preg_match_all('/\<img[^\>]+src\="([^"]+)"[^\>]*>/Usi', $value, $matches);
		
		foreach($matches[1] as $k => $m) {
			
			// match if may be upload
			if(preg_match('/^\.?\/?Uploads\/([a-zA-Z0-9_\-\.]+)\/([a-zA-Z0-9_\-\.]+)\/([a-zA-Z0-9_\-\.]+)\/?(index\.[a-zA-Z0-9_]+)?$/Ui', $m, $params)) {
				
				// match for size
				if(preg_match('/style\="[^"]*[\s*?|;?](width|height)\s*:\s*([0-9]+)(px)[^"]*[\s*?|;?](width|height)\s*:\s*([0-9]+)(px)[^"]*"/Ui', $matches[0][$k], $sizes)) {
					// sizes
					$$sizes[1] = $sizes[2];
					$$sizes[4] = $sizes[5];
					
				} else if(preg_match('/style\="[^"]*[\s*?|;?](width|height)\s*:\s*([0-9]+)(px)[^"]*"/Ui', $matches[0][$k], $sizes)) {
				
					$$sizes[1] = $sizes[2];
					
				} else {
					continue;
				}
				
				$data = DataObject::Get("Uploads", array("path" => $params[1] . "/" . $params[2] . "/" . $params[3]));
		
				if($data->count() == 0) {
					return false;
				}
				
				if(isset($width, $height) && $data->width && $data->height) {
					
					if($data->width > $width && $data->width < 4000 && $data->height > $height && $data->height < 4000) {
												
						$url = "./" . $data->path . '/orgSetSize/'.$width.'/'.$height . substr($data->filename, strrpos($data->filename, "."));
						// retina
						if($width * 2 < $data->width && $height * 2 < $data->height) {
							$retinaURL = "./" . $data->path . '/orgSetSize/'.($width * 2).'/'.($height * 2) . substr($data->filename, strrpos($data->filename, "."));
						} else {
							$retinaURL = "./" . $data->path;
						}
						$value = str_replace($m, $url . '" data-retina="' . $retinaURL, $value);
					}
				} else if(isset($width)) {
					if($data->width > $width && $data->width < 4000) {
						$url =  "./" . $data->path . '/orgSetWidth/' . $width . substr($data->filename, strrpos($data->filename, "."));
						// retina
						if($width * 2 < $data->width) {
							$retinaURL =  "./" . $data->path . '/orgSetWidth/' . ($width * 2) . substr($data->filename, strrpos($data->filename, "."));
						} else {
							$retinaURL = "./" . $data->path;
						}
						$value = str_replace($m, $url . '" data-retina="' . $retinaURL, $value);
					}
				} else {
					if($data->height > $height && $data->height < 4000) {
						$url = "./" . $data->path . '/orgSetHeight/' . $height . substr($data->filename, strrpos($data->filename, "."));
						// retina
						if($height * 2 < $data->height) {
							$retinaURL =  "./" . $data->path . '/orgSetWidth/' . ($height * 2) . substr($data->filename, strrpos($data->filename, "."));
						} else {
							$retinaURL = "./" . $data->path;
						}
						$value = str_replace($m, $url . '" data-retina="' . $retinaURL, $value);
					}
				}
			}
		}
		
		return $value;
	}
}
