<?php defined('IN_GOMA') OR die();

/**
 * handles Gravatar-Images. It generates URLs for Gravatar out of the email-adresse.
 *
 *	@package 	goma framework
 *	@link 		http://goma-cms.org
 *	@license: 	LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *	@author 	Goma-Team
 * @Version 	1.5.2
 *
 * last modified: 19.09.2015
 */
class GravatarImageHandler extends ImageUploads {

	/**
	 * add db-fields for email
	 *
	 *@name db
	 */
	static $db = array(
		"email"	=> "varchar(200)"
	);

	/**
	 * extensions in this files are by default handled by this class
	 *
	 *@name file_extensions
	 *@access public
	 */
	static $file_extensions = array();

	/**
	 * Get either a Gravatar URL or complete image tag for a specified email address.
	 *
	 * @param string $email The email address
	 * @param string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
	 * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
	 * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
	 * @param boole $img True to return a complete IMG tag False for just the URL
	 * @param array $atts Optional, additional key/value attributes to include in the IMG tag
	 * @return String containing either just a URL or a complete image tag
	 * @source http://gravatar.com/site/implement/images/php/
	 */
	function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array(), $html = "", $style = "" ) {
		if(isset($_SERVER["HTTPS"])) {
			$url = 'https://secure.gravatar.com/avatar/';
		} else {
			$url = 'http://www.gravatar.com/avatar/';
		}
		$url .= md5( strtolower( trim( $email ) ) );
		$urlRetina = $url;

		$url .= "?s=$s&d=$d&r=$r&.jpg";
		$sR = $s * 2;
		$urlRetina .= "?s=$sR&d=$d&r=$r&.jpg";

		if ( $img ) {
			$url = '<img src="' . $url . '" data-retina="'.$urlRetina.'"';
			foreach ( $atts as $key => $val )
				$url .= ' ' . $key . '="' . $val . '"';
			$url .= ' style="'.$style.'" '.$html.' />';
		}
		return $url;
	}

	/**
	 * returns the raw-path
	 *
	 * @name raw
	 * @access public
	 * @return string
	 */
	public function raw() {
		return self::get_gravatar($this->email, 500);
	}

	/**
	 * returns path.
	 *
	 * @return string
	 */
	public function getPath() {
		return $this->raw();
	}

	/**
	 * returns url.
	 */
	public function getUrl() {
		return $this->getPath();
	}

	/**
	 * to string
	 *
	 * @name __toString
	 * @access public
	 * @return null|string
	 */
	public function __toString() {
		return '<img src="'.$this->raw().'" alt="'.$this->filename.'" />';
	}

	/**
	 * returns the path to the icon of the file
	 *
	 * @name getIcon
	 * @access public
	 * @param int - size; support for 16, 32, 64 and 128
	 * @return string
	 */
	public function getIcon($size = 128, $retina = false) {
		if($retina) {
			$size = $size * 2;
		}

		return self::get_gravatar($this->email, $size);
	}

	/**
	 * sets the height
	 *
	 * @name setHeight
	 * @access public
	 * @return string
	 */
	public function setHeight($height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $height, "mm", "g", true, array("height" => $height), $html, $style);
	}

	/**
	 * sets the width
	 *
	 * @name setWidth
	 * @access public
	 * @return string
	 */
	public function setWidth($width, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array("width" => $width), $html, $style);
	}

	/**
	 * sets the Size
	 *
	 * @name setSize
	 * @access public
	 * @return string
	 */
	public function setSize($width, $height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array("height" => $height, "width" => $width), $html, $style);
	}

	/**
	 * sets the size on the original,  so not the thumbnail we saved
	 *
	 * @name orgSetSize
	 * @access public
	 * @return string
	 */
	public function orgSetSize($width, $height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array(), $html, $style);
	}

	/**
	 * sets the width on the original, so not the thumbnail we saved
	 *
	 * @name orgSetWidth
	 * @access public
	 * @return string
	 */
	public function orgSetWidth($width, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $width, "mm", "g", true, array(), $html, $style);
	}

	/**
	 * sets the height on the original, so not the thumbnail we saved
	 *
	 * @name orgSetHeight
	 * @access public
	 * @return string
	 */
	public function orgSetHeight($height, $absolute = false, $html = "", $style = "") {
		return self::get_gravatar($this->email, $height, "mm", "g", true, array(), $html, $style);
	}

	/**
	 * returns width
	 *
	 * @name width
	 * @access public
	 * @return int
	 */
	public function width() {
		return 500;
	}

	/**
	 * returns height
	 *
	 * @name height
	 * @access public
	 * @return int
	 */
	public function height() {
		return 500;
	}
}
