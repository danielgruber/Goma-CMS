<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 30.03.2013
  * $Version 2.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HelpController extends FrontedController {
	/**
	 * index
	 *
	 *@name index
	*/
	public function index() {
		if($this->getParam("yt") || $this->getParam("wiki")) {
			$tabs = new Tabs("help");
			if($this->getParam("yt")) {
				$yt = $this->getParam("yt");
				if(preg_match('/^http(s)?\:\/\/(www\.)?youtube\.com\/watch\?v\=([a-zA-Z0-9_\-]+)/', $yt, $matches)) {
					$yt = $matches[3];
				} else if(preg_match('/^http(s)?\:\/\/youtu\.be\/([a-zA-Z0-9_\-]+)/', $yt, $matches)) {
					$yt = $matches[2];
				}
				$tabs->addTab(lang("video"), '<iframe width="780" height="439" src="https://www.youtube-nocookie.com/embed/'.$yt.'" frameborder="0" allowfullscreen></iframe>', "video");
			}
			
			if($this->getParam("wiki")) {
				$tabs->addTab(lang("help_article"), '<iframe width="780" height="500" src="http://wiki.goma-cms.org/wiki/'.$this->getParam("wiki").'#without-navi" frameborder="0" allowfullscreen></iframe><a href="http://wiki.goma-cms.org/wiki/'.$this->getParam("wiki").'" target="_blank">'.lang("help_article").'</a>', "article");
			}
			
			return $tabs->render();
		}
	}
}