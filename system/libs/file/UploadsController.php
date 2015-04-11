<?php defined('IN_GOMA') OR die();

/**
  *	@package 	goma framework
  *	@link 		http://goma-cms.org
  *	@license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *	@author 	Goma-Team
  * @Version 	1.5
  *
  * last modified: 26.01.2015
*/
class UploadsController extends Controller {
    
	/**
	 * index
	*/
	public function index() {
		if($this->modelInst()->checkPermission()) {
			if(preg_match('/\.(pdf)$/i', $this->modelInst()->filename)) {
				HTTPResponse::setHeader("content-type", "application/pdf");
				HTTPResponse::sendHeader();
				readfile($this->modelInst()->realfile);
				exit;
			}
			FileSystem::sendFile($this->modelInst()->realfile, $this->modelInst()->filename);
		}
	}

    /**
     * checks for the permission to do anything
     *
     * @param string $action
     * @return bool
     */
	public function checkPermission($action) {
		return (parent::checkPermission($action) && $this->modelInst()->checkPermission());
	}
}
