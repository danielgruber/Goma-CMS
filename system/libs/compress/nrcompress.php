<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.11.2010
  * 
  *@author Navid Roux alias ComFreek    
*/   
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

/* The HTML code won't be compressed -> Does nothing! */
define('CM_NONE', 0);
/* Compress the HTML in the gzip format. Note that this will also add the header Content-Encoding! */
define('CM_GZIP', 2);
/* Removes control characters (spaces, tabs, returns,...) from the HTML code */
define('CM_REMOVESPACES', 4);
/* Converts the images to Base64 strings so the images won't be loaded from the server. */
define('CM_IMAGEENCODE', 8);
/* Converts the images in the CSS file to Base64 data URLs */
define('CM_CSSIMAGEENCODE', 16);
/* Executes all compression methods. See the flags CM_GZIP, CM_REMOVESPACES, CM_IMAGEENCODE for further information. */
define('CM_ALL', CM_GZIP|CM_REMOVESPACES|CM_IMAGEENCODE);


/**
 * The callback function which is used in NRCompress to process the found images (converts to Base64)
 * You should not call this function directly! NRCompress with the flag CM_IMAGEENCODE does that for you! 
 * For this reason there is no documentation. See the code for further information!
 */ 
function NRCompress_ImageEncodeCallback($matches)
{
  if (!file_exists($matches[1])) return $matches[0].$matches[1]; /* If the file doesn't exists, the return value will be the same as in the HTML code */
  $file = file_get_contents($matches[1]);
  $file_extension = substr($matches[1], strripos($matches[1], ".")+1); /* Extract the file extension (without the point) */
  $base64 = "data:image/".$file_extension.";base64,".base64_encode($file); /* Encodes the image to Base64 */
  
  
  $end = str_replace($matches[1], $base64, $matches[0]); /* Assembly the img-tag and the new src-attribute */
  return $end;
}
/**
 * The callback function which is used in NRCompress to process the found images (converts to Base64)
 * You should not call this function directly! NRCompress with the flag CM_CSSIMAGEENCODE does that for you! 
 * For this reason there is no documentation. See the code for further information!
 */ 
function NRCompress_CSSImageEncodeCallback($matches)
{
	if(_eregi("^".ROOT_PATH, $matches[3]))
	{
			$url = substr($matches[3], strlen(ROOT_PATH));
	} else
	{
			$url = $matches[3];
	}
	if (!file_exists($url)) return $matches[0]; /* If the file doesn't exists, the return value will be the same as in the HTML code */
	$file = file_get_contents($url);
	$file_extension = substr($matches[3], strripos($matches[3], ".")+1); /* Extract the file extension (without the point) */
	$base64 = "data:image/".$file_extension.";base64,".base64_encode($file); /* Encodes the image to Base64 */


	$end = str_replace($matches[3], $base64, $matches[0]); /* Assembly the img-tag and the new src-attribute */
	return $end;
}

/**
 * Minimize the traffic between the browser and server (made for Goma!)
 * @param string $html The HTML (should be HTTPresponse::getBody())
 * @param integer $compress_methods Flags which define the methods to be used
 * @param boolean $cache Specifies whether the compressed content should be cached and loaded next time from it (if there aren't any changes)
 * @return The compressed HTML (note that CM_GZIP adds also the header Content-Encoding (=gzip))    
 */ 
function NRCompress($code, $compress_methods=CM_ALL, $cache=true)
{
  if (Core::is_ajax()) return $code;
  
  
  $cachefilename = "includes/temp/" . "nr_" . md5($code) . "_" . (string)$compress_methods;
  if ($cache)
  {
    if (file_exists($cachefilename))
    {
      if ($compress_methods & CM_GZIP) HTTPresponse::addHeader("Content-Encoding", "gzip");
      return file_get_contents( $cachefilename );
    }
  }
  
  if ($compress_methods & CM_REMOVESPACES) /* Not working right yet! */
  {    
    $control_chars = array("\a", "\t", "\b", "\v", "\f", "\r");
    $nothing = array("",   "",   "",   "",  "","", "");
    
    $code = str_replace($control_chars, $nothing, $code);
  }
  if ($compress_methods & CM_IMAGEENCODE)
  {
    $pattern = '/<img.+?src\s*=\s*"([^"]+)"/i';
    $code = preg_replace_callback($pattern, "NRCompress_ImageEncodeCallback", $code);
  }
  if ($compress_methods & CM_CSSIMAGEENCODE)
  {
    $pattern = '/url\(((\'|"|\s?)(.*)(\"|\'|\s?))\)/Usi';
    $code = preg_replace_callback($pattern, "NRCompress_CSSImageEncodeCallback", $code); 
  }
  if ($compress_methods & CM_GZIP)
  {
    /* If the Zlib extension isn't installed...*/
    if (!function_exists("gzencode"))
    {
      trigger_error("You use NRCompress with the flag CM_GZIP.\r\nBut you haven't installed the Zlib extension!".
                    "So there won't be a gzip compression!", E_USER_NOTICE);     
    }
    if (request::CheckBrowserGZIPSupport())
    {
      HTTPresponse::addHeader("Content-Encoding", "gzip");
      $code = gzencode($code, 9);
    }
  }
  if ($cache)
  {
    $file = fopen($cachefilename, "wb");
    fwrite($file, $code);
    fclose($file);
  }
  
  
  
  return $code;
}

?>