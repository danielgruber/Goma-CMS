<?php
/**
  *@package goma
  *@link http://goma-cms.ath.cx
  *@lisence: http://www.gnu.org/licenses/gpl-3.0.html
  *@Copyright (C) 2009  Daniel Gruber

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
  * last modified: 19.12.2009
*/
require('../../includes/cms.php');
if(!isset($_GET['key']))
{
$email = "undefined";
} else
{
$email = $_SESSION[$_GET['key']];
}
header("Content-type: image/png"); 
$length    =    (strlen($email)*8);
$im = ImageCreate ($length, 20);
$weis=imagecolorallocate($im, 255, 255, 255);
$trans = imagecolortransparent($im,$weis);
$hintergrund=$trans;
$text_color = ImageColorAllocate($im, 0, 0, 0); //font-color
imagestring($im, 3,5,2,$email, $text_color);
imagepng ($im); 
imagedestroy($im); 