<?php
/**
 * Created by IntelliJ IDEA.
 * User: D
 * Date: 31.05.15
 * Time: 01:19
 */

class FileSizeFormatter {

    /**
     * formats given size as human readable filesize.
     *
     * @param int $size
     * @param int $prec
     * @return string
     */
    public static function fomat_size($size, $prec = 1) {
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