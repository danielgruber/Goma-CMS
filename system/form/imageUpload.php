<?php
/**
  *@package goma form framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 21.12.2011
  * $Version 2.0
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class imageUpload extends FileUpload
{
		/**
		 * all allowed file-extensions
		 *@name allowed_file_types
		 *@access public
		*/
		public $allowed_file_types = array(
			"jpg",
			"png",
			"bmp",
			"gif",
			"jpeg"
		);
		/**
		 * upload-class
		*/
		protected $uploadClass = "ImageUploads";
}