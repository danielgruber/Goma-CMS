<?php
/**
  *@package goma cms
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2011  Goma-Team
  * last modified: 31.10.2011
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang('backup');

class BackupAdmin extends TableView
{
		// config
		public $text = '{$_lang_backups}';
		
		public $rights = "10";
		
		
		public $models = array("backups");	
		
		public $fields = array(
			"id" 		=> '{$_lang_backup}',
			"date"		=> '{$_lang_db_create_date}'
		);
		
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
			$file = $this->getParam("id");
			
			$path = Backups::$backup_dir . "/" .  $file;
			HTTPResponse::sendFile($path);
			HTTPResponse::sendHeader();
			readfile($path);
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
			$cacher = new Cacher("backups");
			$cacher->delete();
			$gfs = new GFS($data["file"]);
			$plist = new CFPropertyList();
			if($gfs->valid) {
				$plist->parse($gfs->getFileContents("info.plist"));
				$_data = $plist->toArray();
				if(!isset($_data["backuptype"])) {
					@unlink($data["file"]);
					return false;
				}
				if($_data["backuptype"] == "SQLBackup") {
					$file = "sql.upload." . date("m-d-y_H-i-s") . ".sgfs";
				} else {
					$file = "complete.upload." . date("m-d-y_H-i-s") . ".gfs";
				}
			} else {
				@unlink($data["file"]);
				return false;
			}
			
			copy($data["file"], backups::$backup_dir . "/" . basename($file));
			@unlink($data["file"]);
			$this->redirectBack();
		}
		/**
		 * creates a db-backup
		 *
		 *@name add_db
		*/
		public function add_db() {
			$cacher = new Cacher("backups");
			$cacher->delete();
			
			Backup::generateDBBackup(Backups::$backup_dir . "/sql." . date("m-d-y_H-i-s") . ".sgfs");
			$this->redirectBack();
		}
		/**
		 * creates a backup
		 *
		 *@name add_complete
		*/
		public function add_complete() {
			$cacher = new Cacher("backups");
			$cacher->delete();
			
			Backup::generateBackup(Backups::$backup_dir . "/full." . date("m-d-y_H-i-s") . ".gfs");
			$this->redirectBack();
		}
		/**
		 * restore
		*/
		public function restore() {
			return "restore is not implemented, yet.";
		}
}