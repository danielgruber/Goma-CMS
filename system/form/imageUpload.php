<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 05.12.2011
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