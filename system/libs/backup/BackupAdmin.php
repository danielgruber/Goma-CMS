<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 12.12.2012
  * $Version 2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang('backup');

class BackupAdmin extends TableView
{
		/**
		 * url-handlers
		 *
		 *@name url_handlers
		 *@access public
		*/
		public $url_handlers = array(
			"execRestore/\$rand!"	=> "execRestore"
		);
		
		/**
		 * allowed actions
		 *
		 *@name allowed_actions
		 *@access public
		*/
		public $allowed_actions = array(
			"execRestore"
		);
		
		/**
		 * title of this view in admin
		 *
		 *@name text
		 *@access public
		*/
		public $text = '{$_lang_backups}';
		
		/**
		 * permissions needed to view this in admin
		 *
		 *@name rights
		 *@access public
		*/
		public $rights = "ADMIN_BACKUP";
		
		/**
		 * models, which are assigned to this admin-view
		 *
		 *@name models
		 *@access public
		*/
		public $models = array("BackupModel");	
		
		/**
		 * fields we want to show in the table
		 *
		 *@name fields
		 *@access public
		*/
		public $fields = array(
			"name" 			=> '{$_lang_filename}',
			"create_date"	=> '{$_lang_backup_create_date}',
			"size"			=> '{$_lang_files.size}',
			"type"			=> '{$_lang_backup_type}'
		);
		
		
		/**
		 * actions, which are shown in the table or below
		 *
		 *@name actions
		 *@access public
		*/
		public $actions = array(
			"restore"		=> '<img src="images/icons/fatcow-icons/16x16/site_backup_and_restore.png" alt="{$_lang_backup_restore}" title="{$_lang_backup_restore}" />',
			"download"		=> '<img src="images/icons/fatcow-icons/16x16/database_save.png" alt="{$_lang_download}" title="{$_lang_download}" />',
			"delete"		=> '<img src="images/icons/fatcow-icons/16x16/delete.png" alt="{$_lang_delete}" title="{$_lang_delete}" />',
			"add_complete"	=> array("{\$_lang_backup_create_complete}"),
			"add_db"		=> array("{\$_lang_backup_create_sql}"),
			"upload"		=> array('{$_lang_backup_upload}')
		);
		
		/**
		 * sends the file to the browser
		 *
		 *@name download
		 *@access public
		*/
		public function download() {
			$file = DataObject::get_by_id("BackupModel", $this->getParam("id"));
			if(!$file)
				return false;
			
			$file = $file->name;
			$path = BackupModel::BACKUP_PATH . "/" .  $file;
			FileSystem::sendFile($path);
			exit;
		}
		
		/**
		 * upload a backup
		 *
		 *@name upload
		 *@access public
		*/
		public function upload() {
			
			$form = new Form($this, "Backup_Upload", array(
				new FileUpload("file", lang("backup_upload", "Upload a Backup..."), array("gfs", "sgfs"))
			), array(
				new CancelButton("cancel", lang("cancel")),
				new FormAction("submit", lang("submit"), "saveupload")
			), array(
				new RequiredFields(array("file"), "validate")
			));
			return $form->render();
		}
		
		/**
		 * saves the upload
		*/
		public function saveUpload($data) {
			// delete cache
			$gfs = new GFS($data["file"]->realfile);
			$plist = new CFPropertyList();
			if($gfs->valid) {
				$plist->parse($gfs->getFileContents("info.plist"));
				$_data = $plist->toArray();

				if(!isset($_data["backuptype"])) {
					@unlink($data["file"]->realfile);
					return false;
				}
				if($_data["backuptype"] == "SQLBackup") {
					$file = "sql.upload." . date("m-d-y_H-i-s") . ".sgfs";
				} else {
					$file = "complete.upload." . date("m-d-y_H-i-s") . ".gfs";
				}
			} else {
				@unlink($data["file"]->realfile);
				return false;
			}
			
			if(rename($data["file"]->realfile, BackupModel::BACKUP_PATH . "/" . basename($file))) {
				$this->redirectBack();
			} else {
				AddContent::addError(lang("backup_write_error"));
				$this->redirectBack();
			}
		}
		
		/**
		 * creates a db-backup
		 *
		 *@name add_db
		*/
		public function add_db() {
			Backup::generateDBBackup(BackupModel::BACKUP_PATH . "/sql." . randomString(5) . "." . date("m-d-y_H-i-s", NOW) . ".sgfs");
			$this->redirectBack();
		}
		
		/**
		 * creates a backup
		 *
		 *@name add_complete
		*/
		public function add_complete() {
			if(class_exists("SettingsController")) {
				$exclude = (array) unserialize(SettingsController::get("excludeFolders"));
			} else {
				$exclude = array();
			}
			
			foreach($exclude as $key => $val) {
				$exclude[$key] = "/" . $val;
			}
			
			Backup::generateBackup(BackupModel::BACKUP_PATH . "/full." . randomString(5) . "." . date("m-d-y_H-i-s", NOW) . ".gfs", $exclude);
			$this->redirectBack();
		}
		
		/**
		 * restore
		*/
		public function restore() {
			$file = DataObject::get_by_id("BackupModel", $this->getParam("id"));
			if(!$file)
				return false;
			
			$file = $file->name;
			
			$file = BackupModel::BACKUP_PATH . "/" . $file;
			if(!file_exists($file))
				HTTPResponse::redirect($this->namespace);
				
			$gfs = new GFS($file);
			$info = $gfs->parsePlist("info.plist");
			
			if($info["type"] == "backup" && $info["backuptype"] == "SQLBackup") {
				$str = sprintf(lang("restore_sql_sure"), (string) goma_date(DATE_FORMAT, $info["created"]));
				if($this->confirm($str)) {
					$gfs->unpack(ROOT . APPLICATION . "/" . getPrivateKey() . "-install/", "/database");
					Dev::redirectToDev();
				}
			} else {
				$t = G_SoftwareType::getByType($info["type"], $file);
			
				$data = $t->getRestoreInfo();
				if(is_array($data)) {
					$rand = randomString(20);
					$data["rand"] = $rand;
					$_SESSION["restore"] = array();
					$_SESSION["restore"][$rand] = $data;
					
					$dataset = new ViewAccessableData($data);
					return $dataset->renderWith("admin/restoreInfo.html");
				} else {
					return $data;
				}
			}
		}
		
		/**
		 * executes the restore
		 *
		 *@name execRestore
		 *@access public
		*/
		public function execRestore() {
			$rand = $this->getParam("rand");
			if(isset($_SESSION["restore"][$rand])) {
				$data = $_SESSION["restore"][$rand];
				G_SoftwareType::install($data);
				HTTPResponse::redirect(BASE_URI);
			} else {
				HTTPResponse::redirect(BASE_URI);
			}
		}
		
		/**
		 * provides permissions
		 *
		 *@name providePerms
		 *@access public
		*/
		public function providePerms() {
			return array(
				"ADMIN_BACKUP"	=> array(
					"title" 	=> '{$_lang_administration}: {$_lang_backups}',
					"default"	=> array(
						"type" 			=> "admins",
						"inherit"		=> "ADMIN"
					),
					"category"	=> "ADMIN"
				)
			);
		}
}

if(class_exists("NewSettings")) {
	class BackupSettings extends NewSettings {
		/**
		 * name of the tab
		 *
		 *@name tab
		 *@access public
		*/
		public $tab = "{\$_lang_backups}";
		
		/**
		 * db-fields
		 *
		 *@name db_fields
		 *@access public
		*/ 
		public $db_fields = array(
			"excludeFolders" => "text"
		);
		
		/**
		 * generates the form
		 *
		 *@name getFormFromDB
		 *@access public
		*/
		public function getFormFromDB(&$form) {
			$excludedFolders = $this->excludeFolders;
			if($excludedFolders)
				$excludedFolders = (array) unserialize($excludedFolders);
			else
				$excludedFolders = array();
				
			$files = ArrayLib::key_value(scandir(ROOT . APPLICATION));
			$notExcludable = array("info.plist", "application", "templates", ".htaccess");
			foreach($files as $key => $file) {
				if($file == "." || $file == ".." || in_array($file, Backup::$fileExcludeList)  || in_array("/" . $file, Backup::$fileExcludeList) || in_array($file, $notExcludable)) {
					unset($files[$key]);	
				} else
				
				if(is_dir(ROOT . APPLICATION . "/" . $file)) {
					$files[$key] = $file . "/";
				}
			}
			
			$form->add($dropdown = new MultiSelectDropDown("excludeFolders", lang("backup_exclude_files"), $files, $excludedFolders));
			$form->form()->addDataHandler(array($this, "handleData"));
		}
		
		/**
		 * data-handler
		 *
		 *@name handleData
		 *@access public
		*/
		public function handleData($data) {
			$data["excludeFolders"] = serialize($data["excludeFolders"]);
			return $data;
		}
	}
}