<?php defined("IN_GOMA") OR die();
/**
 * @package goma framework
 * @subpackage template framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @contains classes: tpl, tplcacher, tplcaller
 * @author Goma-Team
 * last modified: 27.08.2015
 * $Version 3.6.6
 */

class tpl extends gObject
{
	/**
	 * @var string
	 */
	public static $tplpath = "tpl/";

	/**
	 * found areas
	 *
	 * @name areas
	 * @access private
	 */
	private static $areas = array();

	/**
	 * important areas
	 *
	 * @name iAreas
	 * @access private
	 */
	private static $iAreas = array();

	/**
	 * currently parsed template
	 *
	 * @name tpl
	 * @access public
	 */
	public static $tpl = "";

	/**
	 * dataStack
	 *
	 * @name dataStack
	 */
	public static $dataStack = array();

	/**
	 * some words you can use like this: <% WORD data %> will be like <% word(data); %>
	 *
	 * @name language_reserved_words
	 * @access public
	 */
	public static $language_reserved_words = array(
		"INCLUDE_JS_MAIN",
		"INCLUDE_JS",
		"INCLUDE_CSS",
		"INCLUDE",
		"INCLUDE_CSS_MAIN",
		"GLOAD",
		"CACHED",
		"ENDCACHED"
	);

	static $cacheTime = 86400;

	/**
	 * this is a static array for convert_vars
	 *
	 * @name convert_var_temp
	 * @access public
	 */
	private static $convert_vars_temp = array();

	/**
	 * @access public
	 * @param string - filename
	 * @param bollean - to follow <!tpl inc:"neu"> or not
	 * @param array - for replacement like {$content}
	 * @use: parse tpl
	 */
	public static function init($name, $follow = true, $replacement = array(), $ifsa = array(), $blockvars = array(), $class = "", $required_areas = array())
	{
		Core::deprecate(2.0, "TPL::render");
		$file = self::getFilename($name, $class);
		if ($file !== false) {
			return self::parser($file, $replacement, realpath($file), $class, $required_areas);
		} else {
			HTTPresponse::setResHeader(500);
			/* an error so show an error ;-) */
			throw new LogicException("Could not open Template-File '" . $name . "'");
		}
	}

	/**
	 * new init method
	 *
	 * @name render
	 * @access public
	 * @param string - filename
	 * @param array - for replacement like {$content}
	 * @param object - class
	 * @param array - required areas
	 * @use: parse tpl
	 */
	public static function render($name, $replacement = array(), $class = "", $expansion = null)
	{
		$file = self::getFilename($name, $class, false, $expansion);
		if ($file !== false) {
			return self::parser($file, $replacement, realpath($file), $class);
		} else {
			HTTPresponse::setResHeader(500);
			/* an error so show an error ;-) */
			throw new LogicException("Could not open Template-File '" . $name . "'");
		}
	}

	/**
	 * gets the filename of a given template-name
	 *
	 * @param string $name
	 * @param object|string $class
	 * @param bool $inc
	 * @param string|null $expansion
	 * @return bool|string
	 */
	public static function getFilename($name, $class = "", $inc = false, $expansion = null)
	{
		if (preg_match('/^\//', $name)) {
			if (is_file(ROOT . $name)) {
				return ROOT . $name;
			} else {
				return false;
			}
		} else {
			if (is_file(ROOT . self::$tplpath . Core::getTheme() . "/" . $name)) {
				return ROOT . self::$tplpath . Core::getTheme() . "/" . $name;
			}

			if ($inc === true && is_file(ROOT . self::$tplpath . Core::getTheme() . "/includes/" . $name)) {
				return ROOT . self::$tplpath . Core::getTheme() . "/includes/" . $name;
			}

			if (Resources::file_exists(APPLICATION_TPL_PATH . "/" . $name)) {
				return ROOT . APPLICATION_TPL_PATH . '/' . $name;
			}

			if ($inc === true && Resources::file_exists(APPLICATION_TPL_PATH . "/includes/" . $name)) {
				return ROOT . APPLICATION_TPL_PATH . '/includes/' . $name;
			}

			if (is_object($class) && $class->inExpansion) {
				$viewpath = isset(ClassInfo::$appENV["expansion"][$class->inExpansion]["viewFolder"]) ? ExpansionManager::getExpansionFolder($class->inExpansion) . ClassInfo::$appENV["expansion"][$class->inExpansion]["viewFolder"] : ExpansionManager::getExpansionFolder($class->inExpansion) . "views";
				if (Resources::file_exists($viewpath . "/" . $name)) {
					return $viewpath . "/" . $name;
				} else if ($inc === true && Resources::file_exists($viewpath . "/includes/" . $name)) {
					return $viewpath . "/includes/" . $name;
				}
			}

			if (isset($expansion)) {
				$viewpath = isset(ClassInfo::$appENV["expansion"][$expansion]["viewFolder"]) ? ExpansionManager::getExpansionFolder($expansion) . ClassInfo::$appENV["expansion"][$expansion]["viewFolder"] : ExpansionManager::getExpansionFolder($expansion) . "views";
				if (Resources::file_exists($viewpath . "/" . $name)) {
					return $viewpath . "/" . $name;
				} else if ($inc === true && Resources::file_exists($viewpath . "/includes/" . $name)) {
					return $viewpath . "/includes/" . $name;
				}
			}

			if (Resources::file_exists(SYSTEM_TPL_PATH . "/" . $name)) {
				return ROOT . SYSTEM_TPL_PATH . '/' . $name;
			}

			if ($inc === true && Resources::file_exists(SYSTEM_TPL_PATH . '/includes/' . $name)) {
				return ROOT . SYSTEM_TPL_PATH . '/includes/' . $name;
			}

			return self::getFileNameUncached($name, $class, $inc, $expansion);
		}
	}

	/**
	 * gets the filename of a given template-name uncached!
	 * just returns false
	 *
	 * @param string $name
	 * @param object|string $class
	 * @param bool $inc use include folder
	 * @param string $expansion
	 * @return bool|string
	 */
	public static function getFilenameUncached($name, $class = "", $inc = false, $expansion = null)
	{
		if (preg_match('/^\//', $name)) {
			if (is_file(ROOT . $name)) {
				return ROOT . $name;
			} else {
				return false;
			}
		} else {

			if (is_file(ROOT . self::$tplpath . Core::getTheme() . "/" . $name)) {
				return ROOT . self::$tplpath . Core::getTheme() . "/" . $name;
			}

			if ($inc === true && is_file(ROOT . self::$tplpath . Core::getTheme() . "/includes/" . $name)) {
				return ROOT . self::$tplpath . Core::getTheme() . "/includes/" . $name;
			}

			if (file_exists(APPLICATION_TPL_PATH . "/" . $name)) {
				return ROOT . APPLICATION_TPL_PATH . '/' . $name;
			}

			if ($inc === true && file_exists(APPLICATION_TPL_PATH . "/includes/" . $name)) {
				return ROOT . APPLICATION_TPL_PATH . '/includes/' . $name;
			}

			if (is_object($class) && $class->inExpansion) {
				$viewpath = isset(ClassInfo::$appENV["expansion"][$class->inExpansion]["viewFolder"]) ? ExpansionManager::getExpansionFolder($class->inExpansion) . ClassInfo::$appENV["expansion"][$class->inExpansion]["viewFolder"] : ExpansionManager::getExpansionFolder($class->inExpansion) . "views";
				if (file_exists($viewpath . "/" . $name)) {
					return $viewpath . "/" . $name;
				} else if ($inc === true && file_exists($viewpath . "/includes/" . $name)) {
					return $viewpath . "/includes/" . $name;
				}
			}

			if (isset($expansion)) {
				$viewpath = isset(ClassInfo::$appENV["expansion"][$expansion]["viewFolder"]) ? ExpansionManager::getExpansionFolder($expansion) . ClassInfo::$appENV["expansion"][$expansion]["viewFolder"] : ExpansionManager::getExpansionFolder($expansion) . "views";
				if (file_exists($viewpath . "/" . $name)) {
					return $viewpath . "/" . $name;
				} else if ($inc === true && file_exists($viewpath . "/includes/" . $name)) {
					return $viewpath . "/includes/" . $name;
				}
			}

			if (file_exists(SYSTEM_TPL_PATH . "/" . $name)) {
				return ROOT . SYSTEM_TPL_PATH . '/' . $name;
			}

			if ($inc === true && file_exists(SYSTEM_TPL_PATH . '/includes/' . $name)) {
				return ROOT . SYSTEM_TPL_PATH . '/includes/' . $name;
			}

			return false;

		}
	}

	private static $cacheCache = array();

	/**
	 * build all files needed for a template
	 *
	 * @param string $template
	 * @param string $tmpname
	 * @return bool|string
	 */
	public static function buildFilesForTemplate($template, $tmpname)
	{
		if (PROFILE) Profiler::mark("tpl::buildFilesForTemplate");

		if (isset(self::$cacheCache[$tmpname])) {
			if (PROFILE) Profiler::unmark("tpl::buildFilesForTemplate");

			return self::$cacheCache[$tmpname]->filename();
		}

		// caching
		$lastModified = filemtime($tmpname);

		$cacher = new tplcacher($tmpname, $lastModified);
		self::$cacheCache[$tmpname] = $cacher;
		if ($cacher->checkvalid() === true) {
			if (PROFILE) Profiler::unmark("tpl::buildFilesForTemplate");

			return $cacher->filename();
		} else {
			$data = file_get_contents($template);

			array_push(self::$dataStack, array("tpl" => self::$tpl, "areas" => self::$areas, "iAreas" => self::$iAreas));

			self::$tpl = $template;
			$tpldata = self::compile($data);

			$olddata = array_pop(self::$dataStack);
			self::$tpl = $olddata["tpl"];
			self::$areas = $olddata["areas"];
			self::$iAreas = $olddata["iAreas"];


			if ($cacher->write($tpldata)) {
				unset($data, $tpldata, $olddata);
				if (PROFILE) Profiler::unmark("tpl::buildFilesForTemplate");

				return $cacher->filename();
			} else {
				if (PROFILE) Profiler::unmark("tpl::buildFilesForTemplate");

				return false;
			}
		}


	}

	/**
	 * @access public
	 * @param string - filename
	 * @param bollean - to follow <!tpl inc:"neu"> or not
	 * @param array - for replacement like {$content}
	 * @use: parse tpl
	 */
	public static function parser($tpl, $replacement = array(), $tmpname, $class, $required_areas = array())
	{
		if (PROFILE) Profiler::mark("tpl::parser");

		$filename = self::buildFilesForTemplate($tpl, $tmpname);
		if ($filename === false) {
			throw new LogicException("Could not open Template-File '" . $filename . "'");
		}


		if (!is_object($class)) {
			$class = gObject::instance("viewaccessabledata");
		}
		$class->customise($replacement);

		if (PROFILE) Profiler::mark("tpl::parser Run " . $tmpname);

		$caller = new tplCaller($class, $tpl);
		$data = $class;
		$callerStack = array();
		$dataStack = array();
		$cacheBuffer = array();
		$cacher = array();


		$hash = "<!--" . microtime(true) . '-->';
		ob_start();
		echo $hash;
		$filename = str_replace('//', '/', $filename);
		include($filename);

		$content = ob_get_contents(); // get contents

		unset($data, $callerStack, $dataStack, $caller);

		ob_end_clean(); // clean contents

		if ($contents = explode($hash, $content)) {
			if (count($contents) > 1) {
				echo $contents[0];
				$tpl = $contents[1];
			} else {
				$tpl = str_replace($hash, '', $content);
			}
		} else {
			$tpl = str_replace($hash, '', $content);
		}

		if (PROFILE) Profiler::unmark("tpl::parser Run " . $tmpname);
		if (PROFILE) Profiler::unmark("tpl::parser");

		return $tpl;
	}

	/**
	 * compiles a tpl-file
	 *
	 * @param $tpl
	 * @return string
	 * @throws TemplateParserException
	 */
	public static function compile($tpl)
	{
		if (PROFILE) Profiler::mark("tpl::compile");

		/**
		 * replace comments
		 * <!--- comment --->
		 */
		$tpl = preg_replace('/<!---(.*)--->/Usi', "", $tpl);
		// we need an empty array
		self::$convert_vars_temp = array();

		// constants
		while (preg_match('/([^{]){([a-zA-Z0-9_]+)}([^}])/Usi', $tpl))
			$tpl = preg_replace('/([^{]){([a-zA-Z0-9_]+)}([^}])/Usi', '\\1<?php echo defined("\\2") ? constant("\\2") : null; ?>\\3', $tpl);

		$tpl = preg_replace('/<%\s*(' . implode("|", self::$language_reserved_words) . ')\s+(.*)\s*%>/Usi', '<% \\1(\\2) %>', $tpl);

		// this array is for storing the percent and prohibit twice parsing of variables
		$percents = array();
		/* variables in <% %> */
		preg_match_all("/<%(.*)%>/Usi", $tpl, $percent);
		foreach ($percent[1] as $key => $data) {
			if (strtolower(trim($data)) == "ignore_vars" || strtolower(trim($data)) == "end_ignore_vars") {
				continue;
			}

			$data = preg_replace_callback('/([a-zA-Z0-9_\.]+)\(/si', array("tpl", "functions"), $data);

			$data = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("tpl", "convert_vars"), $data);
			$data = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("tpl", "percent_vars"), $data);

			$data = preg_replace('/exit;?\s*$/Usi', "", $data);
			$data = str_replace('$caller->.', '->', $data);
			$data = preg_replace('/\)\.([a-zA-Z_])/', ")->\\1", $data);

			$_key = md5($data);
			$percents[$_key] = $data;
			$tpl = preg_replace('/' . preg_quote($percent[0][$key], '/') . '/si', '<%' . $_key . '%>', $tpl, 1);
			unset($data);
		}
		unset($percent);

		$ignores = array();
		preg_match_all("/<%\s*IGNORE_VARS\s*%>(.*)<%\s*END_IGNORE_VARS\s*%>/si", $tpl, $matches);
		foreach ($matches[1] as $key => $val) {
			$ignores[$key] = $val;
			$tpl = str_replace($matches[0][$key], "ignore______that_____" . $key, $tpl);
		}

		// normal vars
		$tpl = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)\.([a-zA-Z0-9_]+)\((.*?)\)}?/si', array("tpl", "convert_vars_echo"), $tpl);
		$tpl = preg_replace_callback('/\{?\$([a-zA-Z0-9_][a-zA-Z0-9_\-\.]+)}?/si', array("tpl", "vars"), $tpl);

		foreach ($ignores as $key => $val) {
			$tpl = str_replace("ignore______that_____" . $key, $val, $tpl);
		}

		foreach ($percents as $key => $data) {
			$tpl = str_replace('<%' . $key . '%>', '<%' . $data . '%>', $tpl);
		}
		foreach (self::$convert_vars_temp as $key => $value) {
			$tpl = str_replace('\\convert_var_' . $key . '\\', $value, $tpl);
		}

		// free memory
		self::$convert_vars_temp = array();

		$tpl = preg_replace_callback('/<%\s*IF\s+(.+)\s*%>/Usi', array("tpl", "PHPrenderIF"), $tpl);
		$tpl = preg_replace_callback('/<%\s*ELSE\s*IF\s+(.+)\s*%>/Usi', array("tpl", "PHPrenderELSEIF"), $tpl);
		$tpl = preg_replace('/<%\s*ELSE\s*%>/Usi', '<?php } else { ?>', $tpl);
		$tpl = preg_replace('/<%\s*ENDIF\s*%>/Usi', '<?php } ?>', $tpl);
		$tpl = preg_replace('/<%\s*END\s*%>/Usi', '<?php }  ?>', $tpl);
		// parse cached
		$tpl = preg_replace('/<%\s*(\$)(caller\-\>Cached)\((.*)\);?\s*%>/Usi', '<?php if(\\1\\2(\\3)) { ?>', $tpl);
		$tpl = preg_replace('/<%\s*(\$)(caller\-\>ENDCached)\((.*)\);?\s*%>/Usi', '<?php \\1\\2(\\3); } ?>', $tpl);

		// parse functions
		$tpl = preg_replace('/<%\s*(\$)([a-z0-9_\.\->\(\)\$\-]+)\((.*)\);?\s*%>/Usi', '<?php $currentObjectForPrinting = \\1\\2(\\3); if(!is_object($currentObjectForPrinting) && !is_array($currentObjectForPrinting)) { echo $currentObjectForPrinting; } ?>', $tpl);

		$tpl = preg_replace('/<%\s*echo\s+(.*)\s*%>/Usi', '<?php echo \\1; ?>', $tpl);
		$tpl = preg_replace('/<%\s*print\s+(.*)\s*%>/Usi', '<?php print \\1; ?>', $tpl);
		$tpl = preg_replace('/<%\s*config\s+(.*)\s*%>/Usi', '<?php \\1; ?>', $tpl);

		$controlCount = 0;

		// CONTROL
		$tpl = preg_replace('/<%\s*CONTROL\s+(\$)([a-z0-9_\.\-\>\(\)\s]+)\->([a-zA-Z0-9_]+)\(([^%]*)\)\s+as\s+\$data\[["\']([a-zA-Z0-9_\-]+)["\']\]\s*%>/Usi',
			'
<?php 
	// begin control
	$callerStack[] = clone $caller;
	$dataStack[] = clone $data;

	$value = \\1\\2->\\3(\\4);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customise(array("\\5" => $data_loop));
			if(is_object($data_loop)) 
				$caller->callers[strtolower("\\5")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("\\5")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>
', $tpl, -1, $controlCount);
		$controlCount2 = 0;
		$tpl = preg_replace('/<%\s*CONTROL\s+(\$)([a-z0-9_\.\-\>\(\)]+?)\->([a-z0-9_]+)\((.*)\)\s*%>/Usi',
			'
<?php 
	// begin control
	$callerStack[] = clone $caller;
	$dataStack[] = clone $data;
	
	$value = \\1\\2->\\3(\\4);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customise(array("\\3" => $data_loop));
			if(is_object($data_loop)) 
				$caller->callers[strtolower("\\3")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("\\3")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>
', $tpl, -1, $controlCount2);

		$controlCount3 = 0;
		$tpl = preg_replace('/<%\s*CONTROL\s+array\((.*)\)\s+as\s+\$data\[["\']([a-zA-Z0-9_\-]+)["\']\]\s*%>/Usi',
			'
<?php 
	// begin control
	$callerStack[] = clone $caller;
	$dataStack[] = clone $data;
	
	$value = array(\\1);
	if(is_object($value) && is_a($value, "DataObject")) {
		$value = new DataSet(array($value));
	}
	if(is_array($value) || (is_object($value) && $value instanceof Traversable))
		foreach($value as $data_loop) {
			$data->customise(array("\\2" => $data_loop));
			if(is_object($data_loop)) 
				$caller->callers[strtolower("\\2")] = new tplCaller($data_loop); 
			else  
				$caller->callers[strtolower("\\2")] = new tplCaller(new ViewAccessableData(array($data_loop))); 
?>
', $tpl, -1, $controlCount3);

		$endControlCount = 0;

		$tpl = preg_replace('/<%\s*ENDCONTROL(.*)\s*%>/Usi', '
<?php 
// end control
		}

$caller = array_pop($callerStack); 
$data = array_pop($dataStack); 
?>
', $tpl, -1, $endControlCount);

		$controlCount = $controlCount + $controlCount2 + $controlCount3;
		unset($controlCount2);

		// validate counters
		if ($controlCount > $endControlCount) {
			throw new TemplateParserException('Expected <% ENDCONTROL %> ' . ($controlCount - $endControlCount) . ' more time(s) in ' . self::$tpl . '.', self::$tpl);
		} else if ($endControlCount > $controlCount) {
			throw new TemplateParserException('Expected <% CONTROL [method] %> ' . ($endControlCount - $controlCount) . ' more time(s) in ' . self::$tpl . '.', self::$tpl);
		}

		// areas, DEPRECATED!
		$tpl = preg_replace_callback('/\<garea\s+name=("|\')([a-zA-Z0-9_-]+)\1\s*\>(.*?)\<\/garea\s*\>/si', array("tpl", "areas"), $tpl);
		$tpl = preg_replace_callback('/\<garea\s+name=("|\')([a-zA-Z0-9_-]+)\1\s+reload=("|\')([a-zA-Z0-9_-]+)\3\s*\>(.*?)\<\/garea\s*\>/si', array("tpl", "iareas"), $tpl);

		// check areas in includes
		preg_match_all('/' . preg_quote('<?php echo $caller->INCLUDE(', '/') . '("|\')([a-zA-Z0-9_\.\-\/]+)\1\); \?\>/Usi', $tpl, $matches);
		foreach ($matches[2] as $file) {
			$filename = self::getFilename($file, "", true);
			if (self::$tpl == $file) {
				continue;
			}
			self::buildFilesForTemplate($filename, realpath($filename));
		}
		unset($matches);

		// you can hook into it
		Core::callHook("compileTPL", $tpl);
		if (PROFILE) Profiler::unmark("tpl::compile");

		return $tpl;
	}

	/**
	 * this function parses functions in the tpl
	 *
	 * @name functions
	 * @return string
	 */
	public static function functions(array $matches)
	{
		$name = trim(strtolower($matches[1]));
		if ($name == "print" || $name == "echo" || $name == "array") {
			return $matches[0];
		} else {
			if (strpos($name, ".")) {
				$names = explode(".", $name);
				$count = count($names);
				$data = '$caller';
				foreach ($names as $key => $name) {
					if ($key == ($count - 1)) {
						$data .= '->' . $name;
					} else {
						$data .= '->' . $name . '()';
					}
				}
				$name = $data;
			} else {
				$name = '$caller->' . $matches[1];
			}

			$php = $name . '(';

			return $php;
		}
	}

	/**
	 * parses areas
	 *
	 * @name areas
	 * @access public
	 * @return string
	 */
	public static function areas($matches)
	{
		Core::deprecate(2.0, "Use of areas is Deprecated! Please use normal vars instead in " . self::$tpl);

		return '<?php if($data->getTemplateVar(' . var_export($matches[2], true) . ')) { echo $data->getTemplateVar(' . var_export($matches[2], true) . '); } else { ?>' . $matches[3] . "<?php } ?>";
	}

	/**
	 * parses areas
	 *
	 * @name areas
	 * @access public
	 * @return string
	 */
	public static function iareas($matches)
	{
		Core::deprecate(2.0, "Use of areas is Deprecated! Please use normal vars instead in " . self::$tpl);

		return '<?php if($data->getTemplateVar(' . var_export($matches[2], true) . ')) { echo $data->getTemplateVar(' . var_export($matches[2], true) . '); } else { ?>' . $matches[5] . "<?php } ?>";
	}


	/**
	 * renders the IF with php-tags
	 *
	 * @name PHPRenderIF
	 * @access public
	 * @return string
	 */
	public static function PHPRenderIF($matches)
	{
		return '<?php ' . self::renderIF($matches) . ' ?>';
	}

	/**
	 * renders the ELSEIF with php-tags
	 *
	 * @name PHPRenderELSEIF
	 * @access public
	 * @return string
	 */
	public static function PHPRenderELSEIF($matches)
	{
		return '<?php ' . self::renderELSEIF($matches) . ' ?>';
	}

	/**
	 * callback for vars in <% %>
	 *
	 * @name percent_vars
	 * @return string
	 */
	public static function percent_vars($matches)
	{

		$name = $matches[1];

		if ($name == "caller")
			return '$caller';

		if ($name == "data")
			return '$data';

		if (substr($name, -1) == ".") {
			$name = substr($name, 0, -1);
			$point = ".";
		} else {
			$point = "";
		}

		if (preg_match('/^_lang_([a-zA-Z0-9\._-]+)/i', $name, $data)) {
			return 'lang("' . $data[1] . '", "' . $data[1] . '")' . $point;
		}

		if (preg_match('/^_cms_([a-zA-Z0-9_-]+)/i', $name, $data)) {
			return 'Core::getCMSVar(' . var_export($data[1], true) . ')' . $point;
		}


		if (strpos($name, ".")) {
			$parts = explode(".", $name);
			$php = '$data';
			foreach ($parts as $part) {
				$php .= '[' . var_export($part, true) . ']';
			}

			return $php . $point;
		} else {
			return '$data[' . var_export($name, true) . ']' . $point;
		}
	}

	/**
	 * vars with convertion
	 *
	 * @name convert_vars
	 * @access public
	 * @return string
	 */
	public static function convert_vars($matches)
	{

		$php = '$data';
		$var = $matches[1];
		$function = $matches[2];
		$params = $matches[3];

		// isset-part
		$isset = 'isset($data';
		// parse params
		$params = preg_replace_callback('/\$([a-zA-Z0-9_][a-zA-Z0-9_\.]+)/si', array("tpl", "percent_vars"), $params);
		// parse functions in params
		$params = preg_replace_callback('/([a-zA-Z0-9_\.]+)\((.*)\)/si', array("tpl", "functions"), $params);

		if (strpos($var, ".")) {
			$varparts = explode(".", $var);
			$i = 0;
			$count = count($varparts);
			$count--;
			foreach ($varparts as $part) {
				if ($count == $i) {
					// last
					$php .= '->doObject("' . $part . '")';
					$isset .= '["' . $part . '"]';
				} else {
					$php .= '["' . $part . '"]';
					$isset .= '["' . $part . '"]';
				}
				$i++;
			}
		} else {
			$php .= '->doObject("' . $var . '")';
			$isset .= '["' . $var . '"]';
		}
		$isset .= ')';
		$php .= "->" . $function . "(" . $params . ")";
		$php = '(' . $isset . ' ? ' . $php . ' : "")';

		$key = count(self::$convert_vars_temp);
		self::$convert_vars_temp[$key] = $php;

		return '\\convert_var_' . $key . '\\';
	}

	/**
	 * convert vars with echo
	 *
	 * @name convert_vars_echo
	 * @access public
	 * @return string
	 */
	public static function convert_vars_echo($matches)
	{
		return '<?php echo ' . self::convert_vars($matches) . '; ?>';
	}

	/**
	 * callback for vars
	 *
	 * @name vars
	 * @return string
	 */
	public static function vars($matches)
	{
		$name = $matches[1];
		if (preg_match('/^_lang_([a-zA-Z0-9\._-]+)/i', $name, $data)) {
			return '<?php echo lang("' . $data[1] . '", "' . $data[1] . '"); ?>';
		}

		if (preg_match('/^_cms_([a-zA-Z0-9_-]+)/i', $name, $data)) {
			return '<?php echo Core::getCMSVar(' . var_export($data[1], true) . '); ?>';
		}

		return '<?php echo $data->getTemplateVar(' . var_export($name, true) . '); ?>';
	}

	/**
	 * renders IF-clauses
	 *
	 * @name renderIF
	 * @access public
	 * @return string
	 */
	public static function renderIF($matches)
	{
		$clause = $matches[1];
		// first parse
		$clause = str_replace("=", '==', $clause);
		$clause = str_replace("====", '==', $clause);
		$clause = str_replace("!==", '!=', $clause);
		$clause = preg_replace('/NOT/i', '!', $clause);

		// second partse parts for just bool-values
		$clauseparts = preg_split('/( or | and |\|\||&&)/i', $clause, -1, PREG_SPLIT_DELIM_CAPTURE);
		$newclause = "";
		foreach ($clauseparts as $part) {
			if (strtolower($part) == " and " || $part == "&&") {
				$newclause .= "&&";
			} else if (strtolower($part) == " or " || $part == "||") {
				$newclause .= "||";
			} else {
				if (preg_match("/\=/", $part)) { // clause with =
					$newclause .= $part;
				} else if (preg_match('/\$data\[["\'](.*)["\']\]$/', trim($part), $matches)) {
					$dataparts = preg_split('/["\']\]\[["\']/', $matches[1]);
					$cond = '$data';
					foreach ($dataparts as $_part) {
						$cond .= '->doObject("' . $_part . '")';
					}
					unset($dataparts, $_part, $matches);
					if (preg_match('/!/', trim($part))) {
						$newclause .= '(!' . $cond . ' || !' . $cond . "->bool())";
					} else {
						$newclause .= '(' . $cond . ' && ' . $cond . "->bool())";
					}

				} else {
					$newclause .= $part;
				}
			}
		}

		$newclause = str_replace('$$', '$', $newclause);

		// render clause
		return 'if(' . $newclause . ') {';
	}

	/**
	 * renders ELSEIF-clauses
	 *
	 * @name renderELSEIF
	 * @access public
	 * @return string
	 */
	public static function renderELSEIF($matches)
	{
		return '} else ' . self::renderIF($matches) . " ";
	}

	/**
	 * returns a filename, which you can include
	 *
	 * @param string $name
	 * @param string $class
	 * @return array
	 */
	public static function getIncludeName($name, $class = "")
	{
		$file = self::getFilename($name, $class, true);
		$filename = self::BuildFilesForTemplate($file, realpath($file));
		if ($filename === false) {
			throw new LogicException("Could not create Template-Cache-Files for Template <strong>" . $file . "</strong>");
		}
		unset($tpl);

		return array($filename, $file);
	}

	/**
	 * @access public
	 * @param string - filename
	 * @param bollean - to follow <!tpl inc:"neu"> or not
	 * @param array - for replacement like {$content}
	 * @use: parse tpl
	 * @return string
	 */
	public static function includeparser($tpl, $tmpname)
	{
		if (PROFILE) Profiler::mark("tpl::includeparser");

		if (!($t = filemtime($tmpname))) {
			$t = 0;
		}

		$cacher = new tplcacher($tmpname, $t);
		if ($cacher->checkvalid() !== true) {
			$data = file_get_contents($tpl);
			$tpldata = self::compile($data);
			$cacher->write($tpldata);
			unset($tpldata, $data);
		}

		if (PROFILE) Profiler::unmark("tpl::includeparser");

		return $cacher->filename();
	}
}

/**
 * this class is a class for calling functions from the template
 * it provides functions to add allowed methods to the template
 *
 * @name tplCaller
 * @package goma
 * @subpackage template
 */
class tplCaller extends gObject implements ArrayAccess
{
	/**
	 * goma adds all caller in CONTROL xxx to this var
	 */
	public $callers = array();

	/**
	 * current template-file
	 *
	 * @name tpl
	 * @access public
	 */
	public $tpl;

	/**
	 * current template-base
	 *
	 * @name tplBase
	 * @access public
	 */
	public $tplBase;

	/**
	 * sub-path, for example admin/ or history/
	 * so this is not connected with template-root
	 *
	 * @name subPath
	 * @access public
	 */
	public $subPath;

	/**
	 * this var contains the dataobject for this caller
	 *
	 * @name dataobject
	 * @access private
	 */
	private $dataobject;

	/**
	 * cache-bufffar.
	 */
	private $cacheBuffer = array();

	/**
	 * cachers.
	 */
	private $cacher = array();

	/**
	 * @name __construct
	 * @param object - dataobject
	 */
	public function __construct(gObject &$dataobject, $tpl = "")
	{
		parent::__construct();

		/* --- */

		$this->setTplPath($tpl);
		$this->dataobject = $dataobject;
		$this->inExpansion = $this->dataobject->inExpansion;

	}

	/**
	 * sets tpl-paths
	 *
	 * @name setTplPath
	 * @access public
	 */
	public function setTplPath($tpl)
	{

		$this->tplBase = substr($tpl, 0, strrpos($tpl, "/"));
		if (substr($this->tplBase, 0, strlen(ROOT)) == ROOT)
			$this->tplBase = substr($this->tplBase, strlen(ROOT));

		if (substr(SYSTEM_TPL_PATH, -1) == "/") {
			$systemL = strlen(SYSTEM_TPL_PATH) + 1;
		} else {
			$systemL = strlen(SYSTEM_TPL_PATH);
		}

		if (substr(APPLICATION_TPL_PATH, -1) == "/") {
			$appL = strlen(APPLICATION_TPL_PATH) + 1;
		} else {
			$appL = strlen(APPLICATION_TPL_PATH);
		}

		if (substr($this->tplBase, 0, strlen("tpl/" . Core::getTheme())) == "tpl/" . Core::getTheme()) {
			$this->subPath = substr($this->tplBase, strlen("tpl/" . Core::getTheme()));
		} else if (substr($this->tplBase, 0, strlen(SYSTEM_TPL_PATH)) == SYSTEM_TPL_PATH) {
			$this->subPath = substr($this->tplBase, $systemL + 1);
		} else if (substr($this->tplBase, 0, strlen(APPLICATION_TPL_PATH)) == APPLICATION_TPL_PATH) {
			$this->subPath = substr($this->tplBase, $appL + 1);
		}

		if (isset($this->subPath) && !$this->subPath)
			$this->subPath = "";

		$this->tpl = $tpl;
	}

	/**
	 * prints a debug
	 *
	 * @name printDebug
	 * @access public
	 */
	public function printDebug()
	{
		$data = debug_backtrace();
		unset($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
		$data = array_values($data);
		if (count($data) > 6) {
			$data = array($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
		}
		echo convert::raw2text(print_r($data, true));
	}

	/**
	 * gets resource-path of given expansion or class-expansion
	 *
	 * @name resource_path
	 * @access public
	 * @return string
	 */
	public function resource_path($exp = null)
	{
		if (!isset($exp))
			$exp = $this->dataobject->inExpansion;

		if (!$exp)
			return "";

		if (!isset(ClassInfo::$appENV["expansion"][$exp]))
			return "";

		$extFolder = ExpansionManager::getExpansionFolder($exp, false);

		return isset(ClassInfo::$appENV["expansion"][$exp]["resourceFolder"]) ? $extFolder . ClassInfo::$appENV["expansion"][$exp]["resourceFolder"] : $extFolder . "resources";
	}

	/**
	 * gets resource-path of given expansion or class-expansion
	 *
	 * @name resource_path
	 * @access public
	 */
	public function ResourcePath($exp = null)
	{
		return $this->resource_path($exp);
	}

	/**
	 * to include another template
	 *
	 * @name include
	 * @access public
	 */
	public function _include($name, $data = null)
	{
		if (tpl::getFilename($name, $this->dataobject, true)) {
			$tpl = tpl::getIncludeName($name, $this->dataobject);
		} else if (tpl::getFilename($this->subPath . "/" . $name, $this->dataobject, true)) {
			$tpl = tpl::getIncludeName($this->subPath . "/" . $name, $this->dataobject);
		} else {
			throwError(7, "Template-file missing", "Could not include Template-File '" . $name . "'");
		}

		$caller = clone $this;
		$caller->setTplPath($tpl[1]);
		if (!isset($data))
			$data = $this->dataobject;

		$caller->dataobject = $data;

		$callerStack = array();
		$dataStack = array();
		include($tpl[0]);
	}

	/**
	 * gets a variable of this dataobject by name
	 *
	 * @name getVar
	 * @access public
	 * @param string - name
	 */
	public function getVar($name)
	{
		return $this->getTemplateVar($name);
	}

	/**
	 * to include another template
	 *
	 * @name include
	 * @access public
	 */
	public function is_ajax()
	{
		return Core::is_ajax();
	}

	/**
	 * to include another template
	 *
	 * @name include
	 * @access public
	 */
	public function is_ajaxfy()
	{
		return (Core::is_ajax() && isset($_GET["ajaxfy"]));
	}

	/**
	 * returns if the current admin wants to see the view as user
	 *
	 * @name adminAsUser
	 * @access public
	 */
	public function adminAsUser()
	{
		return Core::adminAsUser();
	}

	/**
	 * shows statistics
	 *
	 * @name stats
	 * @access public
	 * @param text
	 */
	function stats($text)
	{
		$c = new statistics;
		$parts = explode("|", $text);
		$c->today = $parts[0];
		$c->last2 = $parts[1];
		$c->last30d = $parts[2];
		$c->whole = $parts[3];
		$c->clicks = $parts[4];
		$c->online = $parts[5];

		return $c->getBoxContent();
	}

	/**
	 * starts a cache-block.
	 */
	public function cached()
	{
		if (PROFILE) Profiler::mark("tplcaller::cached");

		$args = func_get_args();
		foreach ($args as $k => $v) {
			if (is_object($v)) {
				if (is_a($v, "DataObjectSet")) {
					$args[$k] = md5(serialize($v->forceData()->toArray()));
				} else if (gObject::method_exists($v, "toArray")) {
					$args[$k] = md5(serialize($v->ToArray()));
				} else {
					$args[$k] = md5(serialize($v));
				}
			} else if (is_array($v)) {
				$args[$k] = md5(serialize($v));
			}
		}

		array_push($this->cacheBuffer, ob_get_clean());
		ob_start();
		$cacher = new Cacher("tpl_" . $this->tpl . "_" . $this->dataobject->versionid . "_" . implode("_", $args));
		array_push($this->cacher, $cacher);
		if ($cacher->checkValid()) {
			echo array_pop($this->cacheBuffer);
			echo $cacher->getData();

			if (PROFILE) Profiler::unmark("tplcaller::cached", "tplcaller::cached load");

			return false;
		} else {
			if (PROFILE) Profiler::unmark("tplcaller::cached");

			return true;
		}
	}

	/**
	 * ends cache-block.
	 */
	public function endcached()
	{
		$dataUntilNow = ob_get_clean();
		ob_start();
		if ($cacher = array_pop($this->cacher)) {
			$cacher->write($dataUntilNow, tpl::$cacheTime);
		}
		echo array_pop($this->cacheBuffer);
		echo $dataUntilNow;
	}

	/**
	 * gets the current theme
	 *
	 * @name getTheme
	 * @access public
	 */
	public function getTheme()
	{
		return self::getTheme();
	}

	/**
	 * includes CSS
	 *
	 * @param string $name
	 */
	public function include_css($name)
	{
		$this->includeCssByType($name);
	}

	/**
	 * includes CSS in main-class
	 *
	 * @param string $name
	 */
	public function include_css_main($name)
	{
		$this->includeCssByType($name, "main");
	}

	/**
	 * includes css.
	 *
	 * @param string $name
	 * @param string $type
	 * @return string
	 */
	protected function includeCssByType($name, $type = "") {
		if (preg_match("/\.(css|scss|sass|less)$/i", $name)) {
			if (isset($this->subPath)) {
				if (self::file_exists("tpl/" . Core::getTheme() . "/" . $this->subPath . "/" . $name)) {
					$name = "tpl/" . Core::getTheme() . "/" . $this->subPath . "/" . $name;
					Resources::add($name, "css", $type);

					return "";
				} else if (self::file_exists(APPLICATION_TPL_PATH . $this->subPath . "/" . $name)) {
					$name = APPLICATION_TPL_PATH . $this->subPath . "/" . $name;
					Resources::add($name, "css", $type);

					return "";
				} else if (self::file_exists(SYSTEM_TPL_PATH . $this->subPath . "/" . $name)) {
					$name = SYSTEM_TPL_PATH . $this->subPath . "/" . $name;
					Resources::add($name, "css", $type);

					return "";
				}
			}
			if (self::file_exists($this->tplBase . "/" . $name)) {
				$name = $this->tplBase . "/" . $name;
			}
		}
		Resources::add($name, "css", $type);
	}

	/**
	 * includes JS
	 *
	 * @name INCLUDE_JS
	 * @access public
	 */
	public function include_js($name)
	{
		if (preg_match("/\.js$/i", $name)) {
			if (isset($this->subPath)) {
				if (self::file_exists("tpl/" . Core::getTheme() . "/" . $this->subPath . "/" . $name)) {
					$name = "tpl/" . Core::getTheme() . "/" . $this->subPath . "/" . $name;
					Resources::add($name, "js", "tpl");

					return "";
				} else if (self::file_exists(APPLICATION_TPL_PATH . $this->subPath . "/" . $name)) {
					$name = APPLICATION_TPL_PATH . $this->subPath . "/" . $name;
					Resources::add($name, "js", "tpl");

					return "";
				} else if (self::file_exists(SYSTEM_TPL_PATH . $this->subPath . "/" . $name)) {
					$name = SYSTEM_TPL_PATH . $this->subPath . "/" . $name;
					Resources::add($name, "js", "tpl");

					return "";
				}
			}
			if (self::file_exists($this->tplBase . "/" . $name)) {
				$name = $this->tplBase . "/" . $name;
			} else if (!isset($this->subPath) && file_exists("tpl/" . Core::getTheme() . "/" . $name)) {
				$name = "tpl/" . Core::getTheme() . "/" . $name;
			}
		}
		Resources::add($name, "js", "tpl");
	}

	/**
	 * includes JS as "main"
	 *
	 * @name INCLUDE_JS_MAIN
	 * @access public
	 */
	public function include_js_main($name)
	{
		if (preg_match("/\.js$/i", $name)) {
			if (isset($this->subPath)) {
				if (self::file_exists("tpl/" . Core::getTheme() . "/" . $this->subPath . "/" . $name)) {
					$name = "tpl/" . Core::getTheme() . "/" . $this->subPath . "/" . $name;
					Resources::add($name, "js", "main");

					return "";;
				} else if (self::file_exists(APPLICATION_TPL_PATH . $this->subPath . "/" . $name)) {
					$name = APPLICATION_TPL_PATH . $this->subPath . "/" . $name;
					Resources::add($name, "js", "main");

					return "";
				} else if (self::file_exists(SYSTEM_TPL_PATH . $this->subPath . "/" . $name)) {
					$name = SYSTEM_TPL_PATH . $this->subPath . "/" . $name;
					Resources::add($name, "js", "main");

					return "";
				}
			}
			if (self::file_exists($this->tplBase . "/" . $name)) {
				$name = $this->tplBase . "/" . $name;
			} else if (!isset($this->subPath) && file_exists("tpl/" . Core::getTheme() . "/" . $name)) {
				$name = "tpl/" . Core::getTheme() . "/" . $name;
			}
		}
		Resources::add($name, "js", "main");
	}

	/**
	 * returns if homepage
	 */
	public function is_homepage()
	{
		return (defined("HOMEPAGE") && HOMEPAGE);
	}

	/**
	 * returns if homepage
	 */
	public function isHomepage()
	{
		return (defined("HOMEPAGE") && HOMEPAGE);
	}

	/**
	 * for language
	 *
	 * @name lang
	 * @access public
	 * @param string - name
	 * @param string - if not set use this
	 */
	public function lang($name, $else = "")
	{
		return lang($name, $else);
	}

	/**
	 * gloader::load
	 *
	 * @name gload
	 * @access public
	 */
	public function gload($name)
	{
		gloader::load($name);
	}
	/**
	 * Layer for right-management
	 */

	/**
	 * returns true if current user is admin
	 *
	 * @name admin
	 * @access public
	 */
	public function admin()
	{
		return Permission::check("ADMIN");
	}

	/**
	 * returns true if the user is logged in
	 *
	 * @name login
	 * @access public
	 */
	public function login()
	{
		return member::login();
	}

	/**
	 * returns true if the current user is not logged in
	 *
	 * @name logout
	 * @access public
	 */
	public function logout()
	{
		return member::logout();
	}

	/**
	 * checks given permission
	 *
	 * @name permission
	 * @access public
	 */
	public function permission($perm)
	{
		return Permission::check($perm);
	}

	/**
	 * disables the whole mobile functionallity
	 * calls (@link Core::disableMobile)
	 *
	 * @name disableMobile
	 * @access public
	 */
	public function disableMobile()
	{
		Core::disableMobile();
	}

	/**
	 * enables the mobile functionallity
	 * calls (@link Core::enableMobile)
	 *
	 * @name enableMobile
	 * @access public
	 */
	public function enableMobile()
	{
		Core::enableMobile();
	}

	/**
	 * gets all languages
	 *
	 * @name languages
	 * @access public
	 */
	public function languages()
	{
		$arr = array();
		$data = i18n::listLangs();
		foreach ($data as $lang => $contents) {
			if ($lang == Core::$lang)
				$arr[] = array_merge($contents, array("code" => $lang, "name" => $lang, "active" => true));
			else
				$arr[] = array_merge($contents, array("code" => $lang, "name" => $lang, "active" => false));

		}

		return new ViewAccessAbleData($arr);
	}

	/**
	 * returns info about current lang
	 *
	 * @name currentLang
	 * @access public
	 */
	public function currentLang()
	{
		return new ViewAccessableData(array_merge(i18n::getLangInfo(), array("code" => Core::$lang)));
	}

	/**
	 * breadcrumbs
	 */
	public function breadcrumbs()
	{
		$data = new DataSet();
		foreach (Core::$breadcrumbs as $link => $title) {
			$data->push(array('link' => $link, 'title' => convert::raw2text($title)));
		}

		return $data;
	}

	/**
	 * gets all headers
	 *
	 * @name headers
	 * @access public
	 */
	public function header()
	{
		return Core::getHeader();
	}

	/**
	 * gets all headers as HTML
	 * @return string
	 */
	public function headerHTML()
	{
		return Core::getHeaderHTML();
	}

	/**
	 * some math operations
	 */

	/**
	 * sums all given values
	 *
	 * @sum
	 * @access public
	 */
	public function sum()
	{
		$args = func_get_args();
		$value = 0;
		foreach ($args as $val) {
			$value += $val;
		}

		return $value;
	}

	/**
	 * multiply
	 *
	 * @name multiply
	 * @access public
	 */
	public function multiply()
	{
		$args = func_get_args();
		foreach ($args as $val) {
			if (!isset($value)) {
				$value = $val;
			} else {
				$value = $value * $val;
			}
		}

		return $value;
	}

	/**
	 * devide
	 *
	 * @return float
	 */
	public function devide()
	{
		$args = func_get_args();
		foreach ($args as $val) {
			if (!isset($value)) {
				$value = $val;
			} else {
				$value = $value / $val;
			}
		}

		return $value;
	}

	/**
	 * date-method
	 *
	 * @name date
	 * @access public
	 */
	public function date($format, $time = NOW)
	{
		return goma_date($format, $time);
	}

	/**
	 * adds URL-param to URL
	 * @param string $url
	 * @param string $param
	 * @param string $value
	 * @return string
	 */
	public function addParamToUrl($url, $param, $value)
	{
		return Controller::addParamToUrl($url, $param, $value);
	}

	/**
	 * returns if matches url.
	 */
	public function urlMatches($regexp)
	{
		$regexp = str_replace('/', '\\/', $regexp);

		return preg_match('/' . $regexp . '/Usi', URL);
	}

	/**
	 * returns Core::$title
	 *
	 * @name title
	 * @access public
	 */
	public function title()
	{
		return Core::$title;
	}


	/**
	 * returns addcontent
	 */
	public function addcontent($flush = false)
	{
		$c = addcontent::get();
		if ($flush) {
			addcontent::flush();
		}

		return $c;
	}

	/**
	 * API for resizing images
	 *
	 * @name imageSetSize
	 * @param string - file
	 * @param int - width
	 * @param int - height
	 */
	public function imageSetSize($file, $width, $height)
	{

		$url = "images/resampled/" . $width . "/" . $height . "/" . $file;
		if (!file_exists($url)) {
			FileSystem::requireDir(dirname($url));
			FileSystem::Write($url . ".permit");
		}

		return $url;
	}

	/**
	 * API for resizing images by width
	 *
	 * @name imageSetWidth
	 * @param string - file
	 * @param int - width
	 */
	public function imageSetWidth($file, $width)
	{
		$url = "images/resampled/" . $width . "/" . $file;

		if (!file_exists($url)) {
			FileSystem::requireDir(dirname($url));
			FileSystem::Write($url . ".permit");
		}

		return $url;
	}

	/**
	 * API for resizing images by height
	 *
	 * @name imageSetHeight
	 * @param string - file
	 * @param int - height
	 */
	public function imageSetHeight($file, $height)
	{
		$url = "images/resampled/x/" . $height . "/" . $file;

		if (!file_exists($url)) {
			FileSystem::requireDir(dirname($url));
			FileSystem::Write($url . ".permit");
		}

		return $url;
	}

	//! APIs

	/**
	 * ArrayAccess Layer
	 */

	public function offsetGet($offset)
	{
		if (gObject::method_exists($this->dataobject, $offset)) {
			$data = call_user_func_array(array($this->dataobject, $offset), array());
			if (is_object($data)) {
				return new tplCaller($data);
			} else {
				return $data;
			}
		} else if (gObject::method_exists($this, $offset)) {
			return call_user_func_array(array($this, $offset), array());
		} else {
			return false;
		}
	}

	public function offsetExists($offset)
	{
		if (gObject::method_exists($this->dataobject, $offset) || gObject::method_exists($this, $offset)) {
			return true;
		} else {
			return false;
		}
	}

	public function offsetSet($offset, $value)
	{
		$this->dataobject[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->dataobject[$offset]);
	}

	/**
	 * on clone
	 *
	 * @name __clone
	 * @accessp public
	 */
	public function __clone()
	{
		$this->dataobject = clone $this->dataobject;
		if ($this->callers)
			foreach ($this->callers as $key => $caller) {
				$this->callers[$key] = clone $caller;
			}
	}

	/**
	 * checks if a file exists
	 *
	 * @name file_exists
	 * @access protected
	 */
	protected static function file_exists($filename)
	{
		return Resources::file_exists($filename);
	}

	/**
	 * gets current object
	 *
	 * @name doObject
	 * @access public
	 */
	public function doObject()
	{
		return $this;
	}

	/**
	 * checks if method can call
	 *
	 * @name __cancall
	 * @param string - name
	 */
	public function __cancall($name)
	{
		if (parent::method_exists($this->classname, $name)) {
			return true;
		} else if (parent::method_exists($this->classname, "_" . $name)) {
			return true;
		} else {
			if (gObject::method_exists($this->dataobject, $name)) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * @param string $methodName
	 * @param array $args
	 * @return bool|mixed
	 */
	public function __call($methodName, $args)
	{
		if (gObject::method_exists($this->classname, $methodName)) {
			if (method_exists($this->classname, $methodName))
				return call_user_func_array(array("gObject", $methodName), $args);
			else
				return call_user_func_array(array("gObject", "__call"), array($methodName, $args));
		} else if (gObject::method_exists($this->classname, "_" . $methodName)) {
			return call_user_func_array(array($this, "_" . $methodName), $args);
		} else if (isset($this->callers[strtolower($methodName)])) {
			return $this->callers[strtolower($methodName)];
		} else {
			if (gObject::method_exists($this->dataobject, $methodName)) {
				return call_user_func_array(array($this->dataobject, $methodName), $args);
			} else {
				return false;
			}
		}
	}

	/**
	 * retina.
	 */
	public function RetinaPath($file)
	{
		return RetinaPath($file);
	}

}

/**
 * tpl-cacher
 * caches compiled files
 *
 * @name tplcacher
 * @access public
 */
class tplcacher extends gObject
{
	/**
	 * @name filename
	 * @var string - the filename of the cachefile
	 * @access private
	 */
	private $filename;
	/**
	 * @var string - last modfied of the template
	 * @access private
	 */
	private $lastmodify;
	/**
	 * @var string - last modified of the cachefile
	 * @access private
	 */
	private $clastmodify;
	/**
	 * @var bool - whether cache is valid
	 * @access private
	 */
	private $valid;
	/**
	 * settingscache
	 *
	 * @name settingscache
	 * @access public
	 */
	public static $settingsCache;

	/**
	 * @access public
	 * @param name - name of the cache
	 * @param numeric - last modify of the source
	 */
	public function __construct($name, $lastmodify)
	{
		if (PROFILE) Profiler::mark("tplcache");

		$name = preg_replace("/[^a-zA-Z0-9_\-]/", "_", $name);
		$this->filename = ROOT . CACHE_DIRECTORY . "/tpl." . $name . ".php";
		$this->lastmodify = $lastmodify;
		if (!isset($_GET["flush"]) && is_file($this->filename)) {
			$this->clastmodify = filemtime($this->filename);
			if ($this->clastmodify > $this->lastmodify) {
				$this->valid = true;
			} else {
				$this->valid = false;
			}
		} else {
			$this->valid = false;
		}

		if (PROFILE) Profiler::unmark("tplcache");
	}

	/**
	 * gets the filename
	 *
	 * @name filename
	 * @access public
	 */
	public function filename()
	{
		return $this->filename;
	}

	/**
	 * @access public
	 * @use to check whether cache is valid
	 * @return bool - whether valid or not
	 */
	public function checkvalid()
	{
		return $this->valid;
	}

	/**
	 * @access public
	 * @use to write new data
	 * @return null
	 */
	public function write($data)
	{
		if (PROFILE) Profiler::mark("tplcache::write");
		$d = '<?php
defined(\'IN_GOMA\') OR die(\'<!-- restricted access -->\'); // silence is golden ;) 
if(!isset($data))
	return false;
?>
';

		/*if(function_exists("__autoload")) {
            Profiler::mark("tplcacher::settings");
            if(!is_array(self::$settingsCache)) {
                self::$settingsCache = array();
                if(file_exists(ROOT . "tpl/" . Core::getCMSVar("tpl") . "/info.plist") && file_exists(ROOT . CURRENT_PROJECT . "/config/" . strtolower(Core::getCMSVar("tpl")) . ".plist")) {

                    $tplinfo = new CFPropertyList(ROOT . "tpl/" . Core::getCMSVar("tpl") . "/info.plist");
                    $settingsplist = new CFPropertyList(ROOT . CURRENT_PROJECT . "/config/" . strtolower(Core::getCMSVar("tpl")) . ".plist");
                    $tplinfo = $tplinfo->toArray();
                    $settingsplist = $settingsplist->toArray();
                    foreach($tplinfo["Settings"] as $dict) {
                        $name = $dict["name"];
                        if(isset($settingsplist[$name])) {


                            self::$settingsCache[$dict["name"]] = array("casting" => $dict["type"], "value" => $settingsplist[$name]);
                        }
                    }

                }
            }
            $d .= "<?php \$data->customised['template'] = ".var_export(self::$settingsCache, true)."; ?>";
            Profiler::unmark("tplcacher::settings");
        }*/

		if (FileSystem::write($this->filename, $d . $data)) {
			if (PROFILE) Profiler::unmark("tplcache::write");

			return true;
		} else {
			if (PROFILE) Profiler::unmark("tplcache::write");

			return false;
		}
	}
}
