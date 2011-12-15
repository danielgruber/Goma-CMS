<?php
/**
  * Goma Test-Framework
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2010  Goma-Team
  * last modified: 29.07.2010
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class TestController extends RequestHandler
{
		public function handleRequest($request)
		{
				if(!DEV_MODE)
					return false;
				$this->request = $request;
				
				$this->init();
				
				$test = $request->getParam("test");
				
				
				
				$cacher = new Cacher("test_" . $test);
				if($cacher->checkValid())
				{
						$curr = $cacher->getData();
				} else
				{
						foreach($this->getExtensions() as $ext)
						{
								if($inst = $this->getinstance($ext))
								{
										if($inst->name == $test)
										{
												$cacher->write($ext);
												$curr = $ext;
												break;
										}
								}
						}
				}
				
				if(!isset($curr))
				{
						return show404();
				}
				
				SiteController::addTitle($curr);
				SiteController::addBreadCrumb($curr, URL);
				
				if(isset($curr))
				{
						if($inst = $this->getinstance($curr))
						{
								Profiler::mark("test::run");
								$data = $inst->render();
								Profiler::unmark("test::run");
								if($data)
								{
										return showSite($data, "Test");
								}
						}
				}
				
				show404();
				
		}
		
}

abstract class Test extends Extension
{
		public $name = "";
		public function render()
		{
				
		}
}

Core::addRules(array(
	'test//$test!' => "TestController"
), 50);
