<?php
/**
 * this file sends files as download to the user specified by session-var
 * we use this as a patch for PHP-Restrictions for execution of a script
 *
 *@package goma framework
 *@link http://goma-cms.org
 *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 *@author Goma-Team
 * last modified: 29.08.2012
 * $Version 1.1.2
 */

if(!isset($_GET["downloadID"])) {
	header("Location: ..././../../");
	exit;
}

if(file_exists("../../../temp/download." . basename($_GET["downloadID"]) . ".goma")) {
	$data = unserialize(file_get_contents("../../../temp/download." . basename($_GET["downloadID"]) . ".goma"));
	if(isset($data["file"]) && file_exists($data["file"])) {
		$file = $data["file"];


		if(isset($data["filename"]))
			$filename = $data["filename"];
		else
			$filename = basename($file);

		$range = 0;
		$size = filesize($file);

		$handle = fopen($file, 'rb');

		fseek($handle,$range);

		if ($handle === false) {
			echo "error with handle";
			exit;
		}

		if(isset($_SERVER['HTTP_RANGE'])) {
			list($a, $range) = explode("=",$_SERVER['HTTP_RANGE']);
			str_replace($range, "-", $range);
			$size2 = $size - 1;
			$new_length = $size - $range;
			header('HTTP/1.1 206 Partial Content');
			header("content-length:" . $new_length);
			header("content-range: bytes " . $range . $size2 . "/" . $size);
		} else {
			$size2 = $size-1;
			header('HTTP/1.1 200 OK');
			header("content-range: bytes 0-".$size2 . "/" . $size."");
			header("content-length:" . $size);
		}


		header('Content-Type: application/octed-stream');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: post-check=0, pre-check=0');

		header("Accept-Ranges: bytes");


		ini_set('max_execution_time', '0');
		$chunksize = 1*(1024*1024); // how many bytes per chunk

		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			print $buffer;
			@ob_flush();
			@flush();
		}
		fclose($handle);
		exit;
	}
} else {
	echo "File not found";
	exit;
}
