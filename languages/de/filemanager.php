<?php
/**
  *@package goma
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 02.07.2011
*/   

   $filemanager_lang = array('new_directory'   	=> "Neuer Ordner",
							 'new_file'        	=> "Neue Datei",
							 'new_name'			=> "Neuer Dateiname",
							 'current_dir'     	=> "Aktuelles Verzeichnis",
							 'actions'         	=> "Aktionen",
							 'flush'           	=> "leeren",
							 'upload'          	=> "Hochladen",
							 'unzip'           	=> "Archiv entpacken",
							 'edit_file'       	=> "Datei bearbeiten",
							 'parent_directory'	=> "&Uuml;bergeordnetes Verzeichnis",
							 'delete_dir'      	=> "Ordner l&ouml;schen",
							 'del_dir_rekursiv'	=> "Ordner rekursiv l&ouml;schen",
							 'rename_dir'      	=> "Ordner umbenennen",
							 'delete_file'     	=> "Datei l&ouml;schen",
							 'rename_file'     	=> "Datei umbenennen",
							 "rename"          	=> "umbenennen",
							 'fileinfos'       	=> "Dateiinformationen",
							 'upload_ok'       	=> "Datei %file% hochgeladen!",
							 'upload_not_ok'   	=> "Datei nicht hochgeladen",
							 'file_exist'      	=> 'Datei nicht hochgeladen! Datei existiert schon!',
							 'file_renamed'    	=> 'Datei %file% umbenannt!',
							 'file_not_renamed'	=> 'Datei %file% nicht umbenannt!',
							 'new_file_exist'  	=> 'Dieser Dateiname wird schon verwendet!',
							 'file_created'    	=> "Datei erstellt!",
							 'file_not_created'	=> "Datei konnte nicht erstellt werden!",
							 'file_delete_ok'  	=> "Datei %file% gel&ouml;scht!",
							 'file_delete_bad' 	=> "Datei %file% nicht gel&ouml;scht!",
							 'dir_creat_ok'    	=> "Ordner erstellt!",
							 'dir_creat_bad'   	=> "Konnte ordner nicht erstellen!",
							 'dir_rm_ok'      	=> "Ordner gel&ouml;scht!",
							 'dir_rm_bad'      => "Konnte ordner nicht l&ouml;schen!",
							 'savefile_ok'     => "Habe neuen Inhalt geschrieben.",
							 'unzip_ok'        => "Archiv entpackt!",
							 'unzip_bad'       => "Konnte Archiv nicht entpacken!",
							 'filesize'        => "Dateigr&ouml;&szlig;e",
							 'fileext'         => "Dateiendung",
							 'last_modified'   => "Letzte &Auml;nderung",
							 'show_file'       => "Datei anzeigen",
							 'auswahl'         => "Datei w&auml;hlen",
							 'js_noauswahl'    => "Sie können leider keine Datei wählen!",
							 'filetype_bad'    => "Die Datei hat keine gültige Endung!",
							 "sites"           => "Seiten",
							 'files'           => "Dateien",
							 'directories'     => "Ordner",
							 'or'              => "oder",
							 "filename"        => "Dateiname",
							 'js_copy_dest'    => "Bitte Ziel eingeben",
							 "copy_bad"        => "Die Datei/ Der Ordner konnte nicht kopiert werden.",
							 'copy_ok'         => "Die Datei/ Der Ordner wurde erfolgreich kopiert!",
							 'copy'            => "Kopieren",
							 "too_big"		   => "Die Datei ist zu groß!",
							 'view'			   => 'Datei aufrufen',
							 'resize'		   => 'Bildgröße ändern',
							 'height'	  	   => 'Höhe',
							 'width'	       => 'Breite',
							 'original'		   => 'Orginal',
							 '_new'			   => 'Neu',
							 'save'			   => $GLOBALS["lang"]["save"],
							 'cancel'		   => $GLOBALS["lang"]["cancel"]);
					
					
foreach($filemanager_lang as $key => $value)
{
		$GLOBALS['lang']['filemanager_'.$key] = $value;
}

