<?php defined('IN_GOMA') OR die();
/**
 * Formats file human readable.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 *
 * last modified: 30.05.2015
 */

class FileSizeFormatter {

    /**
     * formats given size as human readable filesize.
     *
     * @param int $size
     * @param int $prec
     * @return string
     */
    public static function format_nice($size, $prec = 1) {
        $ext = "B";
        if($size > 1300) {
            $size = round($size / 1024, $prec);
            $ext = "K";
            if($size > 1300) {
                $size = round($size / 1024, $prec);
                $ext = "M";
                if($size > 1300) {
                    $size = round($size / 1024, $prec);
                    $ext = "G";
                }
            }
        }

        return $size . $ext;
    }
}