<?php defined("IN_GOMA") OR die();
/**
 * SMTP-Connector-Ajax-Interface.
 *
 * @package		Goma\SMTPConnector
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */
class SMTPConnector extends Controller
{

	/**
	 * adds this client to be allowed for SMTP-Connector.
	 * returns a token which is used for authentification.
	*/
	public static function allowSMTPConnect() {
		GlobalSessionManager::globalSession()->set("allow_smtp", randomString(20));
		return GlobalSessionManager::globalSession()->get("allow_smtp");
	}

	/**
	 * index.
	*/
	public function index() {

		HTTPResponse::setHeader("content-type", "text/plain");
		
		if(!isset($_POST["allow_smtp"]) || GlobalSessionManager::globalSession()->get("allow_smtp") != $_POST["allow_smtp"]) {
			HTTPResponse::setResHeader(403);
			HTTPResponse::sendHeader();
			echo "AUTH_ERROR";
			exit;
		}

		GlobalSessionManager::globalSession()->stopSession();

		HTTPResponse::sendHeader();
		if(isset($_POST["host"], $_POST["auth"], $_POST["user"], $_POST["pwd"], $_POST["secure"], $_POST["port"])) {
			$this->checkSMTP($_POST["host"], $_POST["auth"], $_POST["user"], $_POST["pwd"], $_POST["secure"], $_POST["port"]);
		}

		exit;
	}

	/**
	 * checks for SMTP connection.
	*/
	protected function checkSMTP($host, $auth, $user, $pwd, $secure, $port) {
		//Create a new SMTP instance
		$smtp = new SMTP;

		//Enable connection-level debug output
		$smtp->do_debug = SMTP::DEBUG_CONNECTION;

		$ssl = false;
		if(strtolower($secure) == "ssl") {
			$host = "ssl://" . $host;
			$ssl = true;
		}

		$port = $port ?: 25;

		try {
			//Connect to an SMTP server
		    if ($smtp->connect($host, $port, 5)) {

		    	if ($smtp->hello($_SERVER["SERVER_NAME"])) { //Put your host name in here		
			    	if(!$ssl) {
			    		if(!$smtp->startTLS()) {
			    			 throw new Exception('Connect failed');
			    		}

			    		$smtp->hello($_SERVER["SERVER_NAME"]);
			    	}

		            //Authenticate
		            if (!$auth || $smtp->authenticate('username', 'password')) {
		                echo "CONNECTED";
		            } else {
		                throw new Exception('Authentication failed: ' . $smtp->getLastReply());
		            }
		        } else {
		            throw new Exception('HELO failed: '. $smtp->getLastReply());
   			     }
		    } else {
		        throw new Exception('Connect failed');
		    }
		} catch(Exception $e) {
			echo $e->getMessage();
			echo "ERROR";
		}

		$smtp->quit(true);
	}
}