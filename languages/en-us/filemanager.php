<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.07.2011
*/   

   $filemanager_lang = array('new_directory'   => "Create Folder",
							 'new_file'        => "Create File",
							 'new_name'			=> "New filename",
							 'current_dir'     => "Current directory",
							 'actions'         => "actions",
							 'flush'           => "empty",
							 'upload'          => "Upload",
							 'unzip'           => "Extract",
							 'edit_file'       => "Edit file",
							 'parent_directory'=> "Parent directory",
							 'delete_dir'      => "delete folder",
							 'del_dir_rekursiv'=> "delete recursive",
							 'rename_dir'      => "rename folder",
							 'delete_file'     => "delete file",
							 'rename_file'     => "rename file",
							 "rename"          => "rename",
							 'fileinfos'       => "Information",
							 'upload_ok'       => "File %file% was successfully uploaded!",
							 'upload_not_ok'   => "Upload failed",
							 'file_exist'      => 'Upload failed, because file exists.',
							 'file_renamed'    => 'File %file% successfully renamed!',
							 'file_not_renamed'=> 'reanme failed!',
							 'new_file_exist'  => 'This filename is already in use!',
							 'file_created'    => "file crated!",
							 'file_not_created'=> "failed to create file!",
							 'file_delete_ok'  => "File %file% successfully deleted!",
							 'file_delete_bad' => "failed to delete file!",
							 'dir_creat_ok'    => "Folder created!",
							 'dir_creat_bad'   => "failed to create folder!",
							 'dir_rm_ok'       => "folder successfully deleted!",
							 'dir_rm_bad'      => "failed to delete folder!",
							 'savefile_ok'     => "filecontent was successfully updated.",
							 'unzip_ok'        => "archive was successfully extracted!",
							 'unzip_bad'       => "failed to extract archive!",
							 'filesize'        => "Filesize",
							 'fileext'         => "File-extension",
							 'last_modified'   => "Last modfied",
							 'show_file'       => "show file",
							 'auswahl'         => "select file",
							 'js_noauswahl'    => "You're not allowed to select a file!",
							 'filetype_bad'    => "The file-extension is not valid!",
							 "sites"           => "Pages",
							 'files'           => "Files",
							 'directories'     => "Folder",
							 'or'              => "or",
							 "filename"        => "filename",
							 'js_copy_dest'    => "Please enter a destination",
							 "copy_bad"        => "failed to copy.",
							 'copy_ok'         => "The file/folder was successfully copied!",
							 'copy'            => "copy",
							 "too_big"		   => "The file is too big!",
							 'view'			   => "browse file",
							 'resize'		   => 'Resize Image',
							 'height'	  	   => 'height',
							 'width'	       => 'width',
							 'original'		   => 'orginal',
							 '_new'			   => 'new',
							 'save'			   => $GLOBALS["lang"]["save"],
							 'cancel'		   => $GLOBALS["lang"]["cancel"]);
					
					
foreach($filemanager_lang as $key => $value)
{
		$GLOBALS['lang']['filemanager_'.$key] = $value;
}

