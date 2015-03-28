<?php defined("IN_GOMA") OR die();
/**
 * handles some user-specific actions.
 *
 * @package		Goma\Security\Users
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.2.5
 */
class userController extends Controller
{
	/**
	 * gets userdata
	 *@name getuserdata
	 *@param id - userid
	 *@return array
	*/
	public function getuserdata($id)
	{
		return DataObject::get_one($this, array('id' => $id));
	}
	
	/**
	 * saves the user-pwd
	 *@access public
	 *@name savepwd
	*/
	public function pwdsave($result)
	{
		AddContent::add('<div class="success">'.lang("edit_password_ok", "The password were successfully changed!").'</div>');
		DataObject::update("user", array("password" => Hash::getHashFromDefaultFunction($result["password"])), array('recordid' => $result["id"]));
		$this->redirectback();
	}
	
	/**
	 * this is the method, which is called when a action was completed successfully or not.
	 *
	 * it is called when actions of this controller are completed and the user should be notified. For example if the user saves data and it was successfully saved, this method is called with the param save_success. It is also called if an error occurs.
	 *
	 * @param 	string $action the action called
	 * @param	object $record optional: record if available
	 * @access 	public
	*/
	public function actionComplete($action, $record = null) {
		if($action == "publish_success") {
			AddContent::addSuccess(lang("successful_saved", "The data was successfully saved."));
			$this->redirectback();
			return true;
		}
		
		return parent::actionComplete($action, $record);
	}

	/**
	 * in the end this function is called to do last modifications
	 *
	 *@name serve
	 *@access public
	 *@param string - content
	 */
	public function serve($content) {
		if(class_exists("FrontedController")) {
			$c = new FrontedController();
			return $c->serve($content);
		}
		
		return $content;
	}
}