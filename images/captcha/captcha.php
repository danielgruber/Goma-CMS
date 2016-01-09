<?php
/**
  * bugfix:
  * thanks to triopsi ;) @link http://www.triopsi.com
  *@package goma
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 04.08.2010
*/
session_start();
unset($_SESSION['goma_captcha_spam']);

function randomString($len) 
{
	function make_seed()
	{
		list($usec , $sec) = explode (' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
	srand(make_seed());  
				   
	//a string of allowed chars
	$possible="ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
	$str="";
	while(strlen($str)<$len) 
	{
		$str.=substr($possible,(rand()%(strlen($possible))),1);
	}
	return($str);
}

$text = randomString(5);  //how long is the string? here: 5
$_SESSION['goma_captcha_spam'] = $text;
$backgroundnum = round(rand(1, 5));
$back = 'captcha'.$backgroundnum.'.png';
header('Content-type: image/png');
$img = imagecreatefrompng($back); //backgroundimage
$color = ImageColorAllocate($img, 0,0,0); //font-color
$ttf = './schrift.TTF'; //font-family
$ttfsize = '25'; //font-sizr
$angle = rand(0,5);
$t_x = rand(5,30);
$t_y = 35;
imagettftext($img, $ttfsize, $angle, $t_x, $t_y, $color, $ttf, $text);
imagepng($img);
imagedestroy($img);