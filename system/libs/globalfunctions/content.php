<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 23.07.2011
*/   
/**
 * this is for the boxsystem
 *@name boxes
 *@param string - id of the boxsystem
 *@param numeric - maxwidth of the boxes
 *@return generated boxsystem
*/
function boxes($id,$maxwidth = false)
{
		return boxesController::renderBoxes($id, $maxwidth);
}

/**
 * gets a page with all tags, but black
 *@name getPage
 *@param string - content
 *@param string - title
 *@return string
*/
function getPage($content, $title)
{
		$template = new template;
		$template->assign('content',$content);
		$template->assign('title',$title);
		return $template->display('blankpage.html');
}
/**
 * shows a normals site with given content
 *@name showSite
 *@access public
 *@param string - content
 *@param string - title
*/
function showsite($content, $title)
{
		if($title) {
			Core::setTitle($title);
		}
		
		return Core::serve($content);
}
/**
 * renders the data to the frontedcontroller
 *
 *@name renderWithFronted
 *@access public
 *@param string - title
 *@param string - content
*/ 
function renderWithFronted($title, $content) {
	
}

/**
 * shows a 404-site
 *@name show404
 *@access public
*/
function show404()
{
		$request = new Request(
			(isset($_SERVER['X-HTTP-Method-Override'])) ? $_SERVER['X-HTTP-Method-Override'] : $_SERVER['REQUEST_METHOD'],
			URL,
			$_GET,
			array_merge((array)$_POST, (array)$_FILES)
		);
		$site = new siteController();
		return $site->handleRequest($request);
}