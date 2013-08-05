<?php
define('ROOT', realpath(dirname(__FILE__) . "/") . "/");
function decodeSize( $bytes )
{
    $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
    for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
    return( round( $bytes, 2 ) . " " . $types[$i] );
}
var_dump(decodeSize(disk_free_space("/")));
var_dump(decodeSize( disk_total_space("/")));