<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 25.12.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class pagesTreeTest extends Test
{
		public $name = "pagestree";
		public function render()
		{
				$pages = new Pages;
				$q = (isset($_GET["q"])) ? $_GET["q"] : "Artikel";
				$searchtree = "<h1>There are two Tree-Types: Searchable Tree and Normal Tree</h1>
				<p>You can use Searchtree for everything, because if no Search-Context is given, there is the normal tree. Try it!</p>
				<h2>Searchtree</h2><form method=\"get\"><input type=\"text\" name=\"q\" value=\"".$q."\" /></form>
				" . $pages->renderTree("?r=\$id",0, array($q)) . "";
				$normaltree = "<h2>Normal Tree</h2>" . $pages->renderTree("?r=\$id") . "";
				return $searchtree . $normaltree;
		}
}

Object::extend("TestController", "PagesTreeTest");