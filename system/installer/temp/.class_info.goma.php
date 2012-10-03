<?php
ClassInfo::$appENV = array (
  'framework' => 
  array (
    'type' => 'framework',
    'name' => 'goma',
    'autor' => 'Goma Team',
    'version' => '2.0',
    'build' => '044',
    'icon' => 'templates/images/app-icon.png',
    'Codename' => 'Dandelion',
    'title' => 'Goma Dandelion',
  ),
  'app' => 
  array (
    'type' => 'installer',
    'name' => 'goma_installer',
    'autor' => 'Goma Team',
    'version' => '2.0',
    'build' => '008',
    'requireFrameworkVersion' => '2.0-040',
    'langPath' => 'lang',
    'langName' => 'install',
    'defaultLang' => 'en-us',
    'title' => 'Goma Installer',
    'enableAdmin' => false,
    'SQL' => false,
  ),
);
ClassInfo::$class_info = array (
  'classinfo' => 
  array (
    'parent' => 'object',
  ),
  'classmanifest' => 
  array (
  ),
  'core' => 
  array (
    'parent' => 'object',
    'rules' => 
    array (
      100 => 
      array (
        'forms/ajax//$form!/$handler!' => 'AjaxSubmitButton',
      ),
      50 => 
      array (
        'system/forms/$form!/$field!' => 'ExternalForm',
        'api/pagelinks/' => 'PageLinksController',
        'install//$Action' => 'InstallController',
      ),
      10 => 
      array (
        'dev' => 'dev',
        'admin//$item' => 'adminController',
        'adm//' => 'adminRedirectController',
        'system/ajax//link/$id' => 'ajaxlink',
        'system/ajax//popup/$id' => 'ajaxlink',
        'api/v1//$ClassName!' => 'RestfulServer',
        'treeserver' => 'TreeServer',
        'uploaded/images/resampled/$width!/$height!' => 'imageResize',
        'uploaded/images/resampled/$width!' => 'imageResize',
        'images/resampled/$width!/$height!' => 'imageResize',
        'images/resampled/$width!' => 'imageResize',
        'profile//$Action' => 'ProfileController',
        'member/$id!' => 'ProfileController',
        'uploads' => 'UploadController',
      ),
      9 => 
      array (
        'system' => 'SystemController',
      ),
      1 => 
      array (
        '' => 'HomePageController',
      ),
    ),
    'hooks' => 
    array (
      'rebuilddbindev' => 
      array (
        0 => 
        array (
          0 => 'BackupModel',
          1 => 'forceSyncFolder',
        ),
      ),
    ),
  ),
  'dev' => 
  array (
    'parent' => 'requesthandler',
  ),
  'dataobject' => 
  array (
    'parent' => 'viewaccessabledata',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'abstract' => true,
    'extensions' => 
    array (
      'classinfo' => 
      array (
        'dataobjectclassinfo' => '',
        'controllerclassinfo' => '',
      ),
      'form' => 
      array (
        'formdisabler' => '',
      ),
      'formfield' => 
      array (
        'infotextfield' => '',
      ),
      'profilecontroller' => 
      array (
        'lost_passwordextension' => '',
        'registerextension' => '',
      ),
    ),
  ),
  'dataobjectextension' => 
  array (
    'parent' => 'extension',
    'abstract' => true,
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'dataobjectclassinfo' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'dataset' => 
  array (
    'parent' => 'viewaccessabledata',
    'interfaces' => 
    array (
      0 => 'countable',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'dataobjectset' => 
  array (
    'parent' => 'dataset',
    'interfaces' => 
    array (
      0 => 'countable',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'hasmany_dataobjectset' => 
  array (
    'parent' => 'dataobjectset',
    'interfaces' => 
    array (
      0 => 'countable',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'manymany_dataobjectset' => 
  array (
    'parent' => 'hasmany_dataobjectset',
    'interfaces' => 
    array (
      0 => 'countable',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'datavalidator' => 
  array (
    'parent' => 'formvalidator',
  ),
  'extension' => 
  array (
    'parent' => 'viewaccessabledata',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
    'abstract' => true,
  ),
  'object' => 
  array (
    'abstract' => true,
    'extensions' => 
    array (
      'classinfo' => 
      array (
        'dataobjectclassinfo' => '',
        'controllerclassinfo' => '',
      ),
      'form' => 
      array (
        'formdisabler' => '',
      ),
      'formfield' => 
      array (
        'infotextfield' => '',
      ),
      'profilecontroller' => 
      array (
        'lost_passwordextension' => '',
        'registerextension' => '',
      ),
    ),
    'extra_methods' => 
    array (
      'form' => 
      array (
        'disable' => 
        array (
          0 => 'EXT:formdisabler',
          1 => 'disable',
        ),
        'reenable' => 
        array (
          0 => 'EXT:formdisabler',
          1 => 'reenable',
        ),
        'disableactions' => 
        array (
          0 => 'EXT:formdisabler',
          1 => 'disableActions',
        ),
        'enableactions' => 
        array (
          0 => 'EXT:formdisabler',
          1 => 'enableActions',
        ),
      ),
      'profilecontroller' => 
      array (
        'lost_password' => 
        array (
          0 => 'EXT:lost_passwordextension',
          1 => 'lost_password',
        ),
        'register' => 
        array (
          0 => 'EXT:registerextension',
          1 => 'register',
        ),
      ),
    ),
  ),
  'selectquery' => 
  array (
    'parent' => 'object',
  ),
  'columnedadmin' => 
  array (
    'parent' => 'adminitem',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'updatecontroller' => 
  array (
    'parent' => 'admincontroller',
  ),
  'adminitem' => 
  array (
    'parent' => 'admincontroller',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'admincontroller' => 
  array (
    'parent' => 'controller',
  ),
  'admin' => 
  array (
    'parent' => 'viewaccessabledata',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'adminredirectcontroller' => 
  array (
    'parent' => 'requesthandler',
  ),
  'leftandmain' => 
  array (
    'parent' => 'adminitem',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'sortabletableview' => 
  array (
    'parent' => 'tableview',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'tableview' => 
  array (
    'parent' => 'adminitem',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'useradmin' => 
  array (
    'parent' => 'adminitem',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'imageresize' => 
  array (
    'parent' => 'requesthandler',
  ),
  'systemcontroller' => 
  array (
    'parent' => 'controller',
  ),
  'controllerextension' => 
  array (
    'parent' => 'controller',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
    ),
    'abstract' => true,
  ),
  'controller' => 
  array (
    'parent' => 'requesthandler',
  ),
  'controllerclassinfo' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'convert' => 
  array (
    'parent' => 'object',
  ),
  'dbfield' => 
  array (
    'parent' => 'object',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'varchar' => 
  array (
    'parent' => 'dbfield',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'textsqlfield' => 
  array (
    'parent' => 'varchar',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'intsqlfield' => 
  array (
    'parent' => 'varchar',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'checkboxsqlfield' => 
  array (
    'parent' => 'dbfield',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'switchsqlfield' => 
  array (
    'parent' => 'dbfield',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'timezone' => 
  array (
    'parent' => 'dbfield',
    'interfaces' => 
    array (
      0 => 'databasefield',
    ),
  ),
  'datesqlfield' => 
  array (
    'parent' => 'dbfield',
    'interfaces' => 
    array (
      0 => 'defaultconvert',
      1 => 'databasefield',
    ),
  ),
  'imagesqlfield' => 
  array (
    'parent' => 'dbfield',
    'interfaces' => 
    array (
      0 => 'defaultconvert',
      1 => 'databasefield',
    ),
  ),
  'selectsqlfield' => 
  array (
    'parent' => 'object',
  ),
  'radiossqlfield' => 
  array (
    'parent' => 'object',
  ),
  'i18n' => 
  array (
    'parent' => 'object',
    'languagefiles' => 
    array (
      0 => '/form',
      1 => '/backup',
      2 => '/files',
      3 => '/st',
      4 => '/bbcode',
      5 => '/members',
      6 => '/backup',
    ),
    'defaultLanguagefiles' => 
    array (
      '/form' => 'de',
      '/backup' => 'de',
      '/files' => 'de',
      '/st' => 'de',
      '/bbcode' => 'de',
      '/members' => 'de',
    ),
  ),
  'addcontent' => 
  array (
    'parent' => 'object',
  ),
  'group' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("group")',
      'created' => 'date()',
      'name' => 'varchar(100)',
      'type' => 'enum("0", "1", "2")',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("group")',
      'created' => 'date()',
      'name' => 'varchar(100)',
      'type' => 'enum("0", "1", "2")',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'belongs_many_many' => 
    array (
      'users' => 'user',
    ),
    'searchable_fields' => 
    array (
      0 => 'name',
    ),
    'indexes' => 
    array (
      'searchable_fields' => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'name',
        ),
        'name' => 'searchable_fields',
      ),
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'users' => 
      array (
        'table' => 'many_many_user_groups_group',
        'field' => 'groupid',
        'extfield' => 'userid',
      ),
    ),
    'table_name' => 'groups',
    'table_exists' => false,
    'baseclass' => 'group',
    'belongs_many_many_extra' => 
    array (
      'groups' => 
      array (
        'table' => 'many_many_permission_groups_group',
        'field' => 'groupid',
        'extfield' => 'permissionid',
      ),
    ),
  ),
  'groupcontroller' => 
  array (
    'parent' => 'controller',
  ),
  'profiler' => 
  array (
  ),
  'request' => 
  array (
    'parent' => 'object',
  ),
  'requesthandler' => 
  array (
    'parent' => 'object',
  ),
  'resources' => 
  array (
    'parent' => 'object',
    'names' => 
    array (
    ),
    'scanFolders' => 
    array (
      0 => 'system/templates',
      1 => 'system/installer/templates',
    ),
    'files' => 
    array (
      'system/templates/GFSUnpacker.html' => true,
      'system/templates/admin/admin.js' => true,
      'system/templates/admin/columnedadmin.html' => true,
      'system/templates/admin/header_userbar.html' => true,
      'system/templates/admin/index.html' => true,
      'system/templates/admin/index_not_permitted.html' => true,
      'system/templates/admin/leftandmain.css' => true,
      'system/templates/admin/leftandmain.html' => true,
      'system/templates/admin/restoreInfo.html' => true,
      'system/templates/admin/sortableTableview.html' => true,
      'system/templates/admin/style.css' => true,
      'system/templates/admin/tableview.html' => true,
      'system/templates/admin/update.html' => true,
      'system/templates/admin/updateInfo.html' => true,
      'system/templates/admin/users.html' => true,
      'system/templates/admin/versionsview/main.html' => true,
      'system/templates/admin/versionsview/versions.css' => true,
      'system/templates/admin/versionsview/versions.js' => true,
      'system/templates/blankpage.html' => true,
      'system/templates/boxes/login.html' => true,
      'system/templates/css/FileUpload.css' => true,
      'system/templates/css/bbcode.css' => true,
      'system/templates/css/box.css' => true,
      'system/templates/css/ckeditor_goma.css' => true,
      'system/templates/css/complexTableField.css' => true,
      'system/templates/css/default.css' => true,
      'system/templates/css/dropdown.css' => true,
      'system/templates/css/form.css' => true,
      'system/templates/css/frontedbar.css' => true,
      'system/templates/css/hidableFieldSet.css' => true,
      'system/templates/css/orangebox/orangebox.css' => true,
      'system/templates/css/tablefield.css' => true,
      'system/templates/css/tabs.css' => true,
      'system/templates/css/tickbox.css' => true,
      'system/templates/css/tree.css' => true,
      'system/templates/form/form.html' => true,
      'system/templates/framework/503.html' => true,
      'system/templates/framework/buildDistro.html' => true,
      'system/templates/framework/dev.html' => true,
      'system/templates/framework/error.html' => true,
      'system/templates/framework/mysql_connect_error.html' => true,
      'system/templates/framework/nginx_no_rewrite.html' => true,
      'system/templates/framework/permission_fail.html' => true,
      'system/templates/framework/php5.html' => true,
      'system/templates/framework/software_run_fail.html' => true,
      'system/templates/includes/frontedbar.html' => true,
      'system/templates/includes/lang.html' => true,
      'system/templates/includes/langform.html' => true,
      'system/templates/includes/mobile.html' => true,
      'system/templates/includes/pages.html' => true,
      'system/templates/mail.html' => true,
      'system/templates/page_maintenance.html' => true,
      'system/templates/profile/info.html' => true,
      'system/templates/profile/login.html' => true,
      'system/templates/profile/profile.css' => true,
      'system/templates/profile/profile.html' => true,
      'system/templates/restricted_access.html' => true,
      'system/templates/switchlang.html' => true,
      'system/templates/test/dropdownDialog.html' => true,
      'system/templates/test/jquery.scale.rotate.html' => true,
      'system/installer/templates/install/index.html' => true,
      'system/installer/templates/install/install.css' => true,
      'system/installer/templates/install/install.html' => true,
      'system/installer/templates/install/langselect.html' => true,
      'system/installer/templates/install/selectApp.html' => true,
      'system/installer/templates/install/showInfo.html' => true,
      'system/installer/templates/restore/showInfo.html' => true,
    ),
  ),
  'viewaccessabledata' => 
  array (
    'parent' => 'object',
    'interfaces' => 
    array (
      0 => 'iterator',
      1 => 'arrayaccess',
    ),
  ),
  'ajaxsubmitbutton' => 
  array (
    'parent' => 'formaction',
  ),
  'autoformfield' => 
  array (
    'parent' => 'formfield',
  ),
  'bbcodeeditor' => 
  array (
    'parent' => 'textarea',
  ),
  'clusterformfield' => 
  array (
    'parent' => 'formfield',
    'abstract' => true,
  ),
  'dropdown' => 
  array (
    'parent' => 'formfield',
  ),
  'email' => 
  array (
    'parent' => 'formfield',
  ),
  'fieldset' => 
  array (
    'parent' => 'formfield',
  ),
  'fileupload' => 
  array (
    'parent' => 'formfield',
  ),
  'fileuploadset' => 
  array (
    'parent' => 'formfield',
  ),
  'form' => 
  array (
    'parent' => 'object',
  ),
  'externalform' => 
  array (
    'parent' => 'requesthandler',
  ),
  'formstate' => 
  array (
    'parent' => 'object',
  ),
  'formaction' => 
  array (
    'parent' => 'formfield',
  ),
  'formdecorator' => 
  array (
    'parent' => 'extension',
    'abstract' => true,
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'formdisabler' => 
  array (
    'parent' => 'formdecorator',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'formfield' => 
  array (
    'parent' => 'requesthandler',
  ),
  'formvalidator' => 
  array (
    'parent' => 'object',
  ),
  'htmlaction' => 
  array (
    'parent' => 'formaction',
  ),
  'htmleditor' => 
  array (
    'parent' => 'textarea',
  ),
  'htmlfield' => 
  array (
    'parent' => 'formfield',
  ),
  'hasonedropdown' => 
  array (
    'parent' => 'singleselectdropdown',
  ),
  'hiddenfield' => 
  array (
    'parent' => 'formfield',
  ),
  'javascriptfield' => 
  array (
    'parent' => 'formfield',
  ),
  'manymanydropdown' => 
  array (
    'parent' => 'multiselectdropdown',
  ),
  'multiselectdropdown' => 
  array (
    'parent' => 'dropdown',
  ),
  'objectradiobutton' => 
  array (
    'parent' => 'radiobutton',
  ),
  'passwordfield' => 
  array (
    'parent' => 'formfield',
  ),
  'permissionfield' => 
  array (
    'parent' => 'clusterformfield',
  ),
  'radiobutton' => 
  array (
    'parent' => 'formfield',
  ),
  'requestform' => 
  array (
    'parent' => 'object',
  ),
  'requiredfields' => 
  array (
    'parent' => 'formvalidator',
  ),
  'select' => 
  array (
    'parent' => 'formfield',
  ),
  'singleselectdropdown' => 
  array (
    'parent' => 'dropdown',
  ),
  'tab' => 
  array (
    'parent' => 'fieldset',
  ),
  'textfield' => 
  array (
    'parent' => 'formfield',
  ),
  'textarea' => 
  array (
    'parent' => 'formfield',
  ),
  'timefield' => 
  array (
    'parent' => 'hiddenfield',
  ),
  'ajaxexternalform' => 
  array (
    'parent' => 'formfield',
  ),
  'button' => 
  array (
    'parent' => 'formaction',
  ),
  'cancelbutton' => 
  array (
    'parent' => 'formaction',
  ),
  'captcha' => 
  array (
    'parent' => 'formfield',
  ),
  'checkbox' => 
  array (
    'parent' => 'formfield',
  ),
  'hidablefieldset' => 
  array (
    'parent' => 'fieldset',
  ),
  'imageupload' => 
  array (
    'parent' => 'fileupload',
  ),
  'infofield' => 
  array (
    'parent' => 'htmlfield',
  ),
  'infotextfield' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'langselect' => 
  array (
    'parent' => 'select',
  ),
  'linkaction' => 
  array (
    'parent' => 'formaction',
  ),
  'numberfield' => 
  array (
    'parent' => 'formfield',
  ),
  'tablefieldconfig' => 
  array (
    'parent' => 'object',
  ),
  'tablefieldconfig_base' => 
  array (
    'parent' => 'tablefieldconfig',
  ),
  'tablefield' => 
  array (
    'parent' => 'formfield',
  ),
  'tabset' => 
  array (
    'parent' => 'fieldset',
  ),
  'g_softwaretype' => 
  array (
    'abstract' => true,
  ),
  'g_frameworksoftwaretype' => 
  array (
    'parent' => 'g_softwaretype',
  ),
  'g_appsoftwaretype' => 
  array (
    'parent' => 'g_softwaretype',
  ),
  'g_expansionsoftwaretype' => 
  array (
    'parent' => 'g_softwaretype',
  ),
  'gfs' => 
  array (
    'parent' => 'object',
  ),
  'gfs_package_installer' => 
  array (
    'parent' => 'gfs',
  ),
  'gfs_package_creator' => 
  array (
    'parent' => 'gfs',
  ),
  'ajaxlink' => 
  array (
    'parent' => 'requesthandler',
  ),
  'ajaxresponse' => 
  array (
    'parent' => 'javascriptresponse',
  ),
  'javascriptresponse' => 
  array (
    'parent' => 'object',
  ),
  'dialog' => 
  array (
    'parent' => 'ajaxresponse',
  ),
  'restfulserver' => 
  array (
    'parent' => 'requesthandler',
    'api_accesses' => 
    array (
    ),
  ),
  'arraylib' => 
  array (
    'parent' => 'object',
  ),
  'backup' => 
  array (
    'parent' => 'object',
    'excludeList' => 
    array (
      0 => 'statistics',
      1 => 'statistics_state',
    ),
    'fileExcludeList' => 
    array (
      0 => '/uploads/d05257d352046561b5bfa2650322d82d',
      1 => 'temp',
      2 => '/backups',
      3 => '/config.php',
      4 => '/backup',
    ),
  ),
  'backupadmin' => 
  array (
    'parent' => 'tableview',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'backupsettings' => 
  array (
    'parent' => 'newsettings',
  ),
  'backupmodel' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("backupmodel")',
      'created' => 'date()',
      'name' => 'varchar(200)',
      'create_date' => 'varchar(200)',
      'justsql' => 'int(1)',
      'size' => 'bigint(30)',
      'type' => 'varchar(40)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("backupmodel")',
      'created' => 'date()',
      'name' => 'varchar(200)',
      'create_date' => 'varchar(200)',
      'justSQL' => 'int(1)',
      'size' => 'bigint(30)',
      'type' => 'varchar(40)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'backupmodel',
    'table_exists' => false,
    'baseclass' => 'backupmodel',
  ),
  'cacher' => 
  array (
    'parent' => 'object',
  ),
  'pagelinkscontroller' => 
  array (
    'parent' => 'requesthandler',
  ),
  'cookies' => 
  array (
    'parent' => 'object',
  ),
  'cssmin' => 
  array (
    'parent' => 'object',
  ),
  'csv' => 
  array (
    'parent' => 'object',
    'interfaces' => 
    array (
      0 => 'iterator',
    ),
  ),
  'filesystem' => 
  array (
    'parent' => 'object',
  ),
  'uploads' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("imageuploads","uploads")',
      'created' => 'date()',
      'filename' => 'varchar(100)',
      'realfile' => 'varchar(300)',
      'path' => 'varchar(200)',
      'type' => 'enum(\'collection\',\'file\')',
      'deletable' => 'enum(\'0\', \'1\')',
      'md5' => 'text',
      'collectionid' => 'int(10)',
    ),
    'has_one' => 
    array (
      'collection' => 'uploads',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("imageuploads","uploads")',
      'created' => 'date()',
      'filename' => 'varchar(100)',
      'realfile' => 'varchar(300)',
      'path' => 'varchar(200)',
      'type' => 'enum(\'collection\',\'file\')',
      'deletable' => 'enum(\'0\', \'1\')',
      'md5' => 'text',
      'collectionid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'collectionid' => 'INDEX',
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'uploads',
    'table_exists' => false,
    'baseclass' => 'uploads',
    'dataclasses' => 
    array (
      0 => 'imageuploads',
    ),
  ),
  'uploadscontroller' => 
  array (
    'parent' => 'controller',
  ),
  'imageuploads' => 
  array (
    'parent' => 'uploads',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("imageuploads","uploads")',
      'created' => 'date()',
      'filename' => 'varchar(100)',
      'realfile' => 'varchar(300)',
      'path' => 'varchar(200)',
      'type' => 'enum(\'collection\',\'file\')',
      'deletable' => 'enum(\'0\', \'1\')',
      'md5' => 'text',
      'collectionid' => 'int(10)',
      'width' => 'int(5)',
      'height' => 'int(5)',
      'thumbleft' => 'int(3)',
      'thumbtop' => 'int(3)',
      'thumbwidth' => 'int(3)',
      'thumbheight' => 'int(3)',
    ),
    'has_one' => 
    array (
      'collection' => 'uploads',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'width' => 'int(5)',
      'height' => 'int(5)',
      'thumbLeft' => 'int(3)',
      'thumbTop' => 'int(3)',
      'thumbWidth' => 'int(3)',
      'thumbHeight' => 'int(3)',
      'collectionid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'collectionid' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'imageuploads',
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'imageuploads',
    ),
    'baseclass' => 'uploads',
  ),
  'imageuploadscontroller' => 
  array (
    'parent' => 'uploadscontroller',
  ),
  'uploadcontroller' => 
  array (
    'parent' => 'controller',
  ),
  'gd' => 
  array (
    'parent' => 'object',
  ),
  'image' => 
  array (
    'parent' => 'gd',
  ),
  'rootimage' => 
  array (
    'parent' => 'gd',
  ),
  'htmlnode' => 
  array (
    'parent' => 'object',
  ),
  'httpresponse' => 
  array (
    'parent' => 'object',
  ),
  'htmlparser' => 
  array (
    'parent' => 'object',
  ),
  'rawheaders' => 
  array (
    'parent' => 'viewaccessabledata',
    'interfaces' => 
    array (
      0 => 'iterator',
      1 => 'arrayaccess',
    ),
  ),
  'gloader' => 
  array (
    'parent' => 'object',
    'resources' => 
    array (
      'dialog' => 
      array (
        'file' => 'system/libs/javascript/bluebox.min.js',
        'required' => 
        array (
          0 => 'draggable',
        ),
      ),
      'draggable' => 
      array (
        'file' => 'system/libs/javascript/ui/draggable.js',
        'required' => 
        array (
        ),
      ),
      'dropable' => 
      array (
        'file' => 'system/libs/javascript/ui/dropable.js',
        'required' => 
        array (
        ),
      ),
      'sortable' => 
      array (
        'file' => 'system/libs/javascript/ui/sortable.js',
        'required' => 
        array (
        ),
      ),
      'selectable' => 
      array (
        'file' => 'system/libs/javascript/ui/selectable.js',
        'required' => 
        array (
        ),
      ),
      'resizable' => 
      array (
        'file' => 'system/libs/javascript/ui/resizable.js',
        'required' => 
        array (
        ),
      ),
      'accordion' => 
      array (
        'file' => 'system/libs/javascript/ui/accordion.js',
        'required' => 
        array (
        ),
      ),
      'autocomplete' => 
      array (
        'file' => 'system/libs/javascript/ui/autocomplete.js',
        'required' => 
        array (
        ),
      ),
      'button' => 
      array (
        'file' => 'system/libs/javascript/ui/button.js',
        'required' => 
        array (
        ),
      ),
      'uidialog' => 
      array (
        'file' => 'system/libs/javascript/ui/dialog.js',
        'required' => 
        array (
          0 => 'button',
          1 => 'resizable',
          2 => 'draggable',
        ),
      ),
      'slider' => 
      array (
        'file' => 'system/libs/javascript/ui/slider.js',
        'required' => 
        array (
        ),
      ),
      'tabs' => 
      array (
        'file' => 'system/libs/javascript/ui/tabs.js',
        'required' => 
        array (
        ),
      ),
      'gtabs' => 
      array (
        'file' => 'system/libs/tabs/tabs.js',
        'required' => 
        array (
        ),
      ),
      'progessbar' => 
      array (
        'file' => 'system/libs/javascript/ui/progessbar.js',
        'required' => 
        array (
        ),
      ),
      'tree' => 
      array (
        'file' => 'system/libs/javascript/tree.js',
        'required' => 
        array (
        ),
      ),
      'orangebox' => 
      array (
        'file' => 'system/libs/javascript/orangebox/js/orangebox.min.js',
        'required' => 
        array (
        ),
      ),
      'datepicker' => 
      array (
        'file' => 'system/libs/javascript/ui/datepicker.js',
        'required' => 
        array (
        ),
      ),
      'uiEffects' => 
      array (
        'file' => 'system/libs/javascript/ui/effects.js',
        'required' => 
        array (
        ),
      ),
      'touch' => 
      array (
        'file' => 'system/libs/javascript/ui/jquery.ui.touch.js',
        'required' => 
        array (
        ),
      ),
      'jquery.scale.rotate' => 
      array (
        'file' => 'system/libs/javascript/jquery.scale.rotate.js',
        'required' => 
        array (
        ),
      ),
      'g_infobox' => 
      array (
        'file' => 'system/libs/javascript/infobox.js',
        'required' => 
        array (
        ),
      ),
      'dropdownDialog' => 
      array (
        'file' => 'system/libs/javascript/dropdownDialog.min.js',
        'required' => 
        array (
        ),
      ),
      'ajaxupload' => 
      array (
        'file' => 'system/libs/ajax/ajaxupload.js',
        'required' => 
        array (
        ),
      ),
    ),
  ),
  'livecounter' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("livecounter")',
      'created' => 'date()',
      'user' => 'varchar(200)',
      'phpsessid' => 'varchar(800)',
      'mobile' => 'int(1)',
      'browser' => 'varchar(200)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("livecounter")',
      'created' => 'date()',
      'user' => 'varchar(200)',
      'phpsessid' => 'varchar(800)',
      'mobile' => 'int(1)',
      'browser' => 'varchar(200)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'recordid' => false,
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'statistics',
    'table_exists' => false,
    'baseclass' => 'livecounter',
  ),
  'livecountercontroller' => 
  array (
    'parent' => 'controller',
  ),
  'libmail' => 
  array (
  ),
  'mail' => 
  array (
    'parent' => 'object',
  ),
  'sql' => 
  array (
    'parent' => 'object',
  ),
  'stringlib' => 
  array (
    'parent' => 'object',
  ),
  'tabs' => 
  array (
    'parent' => 'object',
  ),
  'template' => 
  array (
    'parent' => 'object',
  ),
  'tpl' => 
  array (
    'parent' => 'object',
  ),
  'tplcaller' => 
  array (
    'parent' => 'object',
    'interfaces' => 
    array (
      0 => 'arrayaccess',
    ),
  ),
  'tplcacher' => 
  array (
    'parent' => 'object',
  ),
  'bbcode' => 
  array (
    'parent' => 'texttransformer',
  ),
  'text' => 
  array (
    'parent' => 'object',
  ),
  'texttransformer' => 
  array (
    'parent' => 'object',
    'abstract' => true,
  ),
  'jsmin' => 
  array (
    'parent' => 'object',
  ),
  'jsminexception' => 
  array (
    'parent' => 'exception',
  ),
  'fastjson' => 
  array (
    'parent' => 'object',
  ),
  'cfbinarypropertylist' => 
  array (
    'parent' => 'object',
    'abstract' => true,
  ),
  'cfpropertylist' => 
  array (
    'parent' => 'cfbinarypropertylist',
    'interfaces' => 
    array (
      0 => 'iterator',
    ),
  ),
  'cftype' => 
  array (
    'abstract' => true,
  ),
  'cfstring' => 
  array (
    'parent' => 'cftype',
  ),
  'cfnumber' => 
  array (
    'parent' => 'cftype',
  ),
  'cfdate' => 
  array (
    'parent' => 'cftype',
  ),
  'cfboolean' => 
  array (
    'parent' => 'cftype',
  ),
  'cfdata' => 
  array (
    'parent' => 'cftype',
  ),
  'cfarray' => 
  array (
    'parent' => 'cftype',
    'interfaces' => 
    array (
      0 => 'iterator',
      1 => 'arrayaccess',
    ),
  ),
  'cfdictionary' => 
  array (
    'parent' => 'cftype',
    'interfaces' => 
    array (
      0 => 'iterator',
    ),
  ),
  'cftypedetector' => 
  array (
  ),
  'ioexception' => 
  array (
    'parent' => 'exception',
  ),
  'plistexception' => 
  array (
    'parent' => 'exception',
  ),
  'simple_html_dom_node' => 
  array (
  ),
  'simple_html_dom' => 
  array (
  ),
  'versionsviewcontroller' => 
  array (
    'parent' => 'controller',
  ),
  'permission' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("permission")',
      'created' => 'date()',
      'name' => 'varchar(100)',
      'type' => 'enum(\'all\', \'users\', \'admins\', \'password\', \'groups\')',
      'password' => 'varchar(100)',
      'invert_groups' => 'int(1)',
      'inheritorid' => 'int(10)',
    ),
    'has_one' => 
    array (
      'inheritor' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("permission")',
      'created' => 'date()',
      'name' => 'varchar(100)',
      'type' => 'enum(\'all\', \'users\', \'admins\', \'password\', \'groups\')',
      'password' => 'varchar(100)',
      'invert_groups' => 'int(1)',
      'inheritorid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'groups' => 'group',
    ),
    'indexes' => 
    array (
      'name' => 'INDEX',
      'inheritorid' => 'INDEX',
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'groups' => 
      array (
        'table' => 'many_many_permission_groups_group',
        'field' => 'permissionid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'permission',
    'table_exists' => false,
    'baseclass' => 'permission',
    'providedPermissions' => 
    array (
      'superadmin' => 
      array (
        'title' => '{$_lang_full_admin_permissions}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'admin' => 
      array (
        'title' => '{$_lang_administration}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'data_write' => 
      array (
        'title' => '{$_lang_dataobject_edit}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'data_delete' => 
      array (
        'title' => '{$_lang_dataobject_delete}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'data_insert' => 
      array (
        'title' => '{$_lang_dataobject_add}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'admin_backup' => 
      array (
        'title' => '{$_lang_backups}',
        'default' => 
        array (
          'type' => 'admins',
          'inherit' => 'ADMIN',
        ),
      ),
    ),
  ),
  'hash' => 
  array (
    'parent' => 'object',
  ),
  'md5hash' => 
  array (
    'parent' => 'hash',
  ),
  'gomahash' => 
  array (
    'parent' => 'hash',
  ),
  'lost_passwordextension' => 
  array (
    'parent' => 'controllerextension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
    ),
  ),
  'usercontroller' => 
  array (
    'parent' => 'controller',
  ),
  'user' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("user")',
      'created' => 'date()',
      'nickname' => 'varchar(200)',
      'name' => 'varchar(200)',
      'email' => 'varchar(200)',
      'password' => 'varchar(200)',
      'signatur' => 'text',
      'status' => 'int(2)',
      'phpsess' => 'varchar(200)',
      'code' => 'varchar(200)',
      'timezone' => 'timezone',
      'custom_lang' => 'varchar(10)',
      'avatarid' => 'int(10)',
    ),
    'has_one' => 
    array (
      'avatar' => 'uploads',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("user")',
      'created' => 'date()',
      'nickname' => 'varchar(200)',
      'name' => 'varchar(200)',
      'email' => 'varchar(200)',
      'password' => 'varchar(200)',
      'signatur' => 'text',
      'status' => 'int(2)',
      'phpsess' => 'varchar(200)',
      'code' => 'varchar(200)',
      'timezone' => 'timezone',
      'custom_lang' => 'varchar(10)',
      'avatarid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'groups' => 'group',
    ),
    'searchable_fields' => 
    array (
      0 => 'nickname',
      1 => 'name',
      2 => 'email',
      3 => 'signatur',
    ),
    'indexes' => 
    array (
      'avatarid' => 'INDEX',
      'searchable_fields' => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'nickname(83)',
          1 => 'name(83)',
          2 => 'email(83)',
          3 => 'signatur(83)',
        ),
        'name' => 'searchable_fields',
      ),
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'groups' => 
      array (
        'table' => 'many_many_user_groups_group',
        'field' => 'userid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'users',
    'table_exists' => false,
    'baseclass' => 'user',
  ),
  'member' => 
  array (
    'parent' => 'object',
  ),
  'profilecontroller' => 
  array (
    'parent' => 'frontedcontroller',
    'allowed_actions' => 
    array (
      0 => 'lost_password',
      1 => 'register',
    ),
  ),
  'registerextension' => 
  array (
    'parent' => 'controllerextension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
    ),
  ),
  'treeserver' => 
  array (
    'parent' => 'requesthandler',
  ),
  'frontedcontroller' => 
  array (
    'parent' => 'requesthandler',
  ),
  'homepagecontroller' => 
  array (
    'parent' => 'requesthandler',
  ),
  'installcontroller' => 
  array (
    'parent' => 'requesthandler',
  ),
  'newsettings' => 
  array (
  ),
  'exception' => 
  array (
  ),
);
ClassInfo::$files = array (
  'classinfo' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/ClassInfo.php',
  'classmanifest' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/ClassManifest.php',
  'core' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/Core.php',
  'dev' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/Core.php',
  'dataobject' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObject.php',
  'dataobjectextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObject.php',
  'dataobjectclassinfo' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObjectClassInfo.php',
  'dataset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObjectSet.php',
  'dataobjectset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObjectSet.php',
  'hasmany_dataobjectset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObjectSet.php',
  'manymany_dataobjectset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataObjectSet.php',
  'datavalidator' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/DataObject/DataValidator.php',
  'extension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/Extension.php',
  'object' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/Object.php',
  'selectquery' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/SelectQuery.php',
  'columnedadmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/ColumnedAdmin.php',
  'updatecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/UpdateController.php',
  'adminitem' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/adminItem.php',
  'admincontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/admincontroller.php',
  'admin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/admincontroller.php',
  'adminredirectcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/admincontroller.php',
  'leftandmain' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/leftandmain.php',
  'sortabletableview' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/sortableTableview.php',
  'tableview' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/tableview.php',
  'useradmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/admin/userAdmin.php',
  'imageresize' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/control/imageresize.php',
  'systemcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/control/systemController.php',
  'controllerextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/controller/ControllerExtension.php',
  'controller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/controller/controller.php',
  'controllerclassinfo' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/controller/controllerClassInfo.php',
  'convert' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/convert.php',
  'dbfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'varchar' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'textsqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'intsqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'checkboxsqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'switchsqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'timezone' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'datesqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'imagesqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/Image.php',
  'selectsqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/selects.php',
  'radiossqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/selects.php',
  'i18n' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/i18n.php',
  'addcontent' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/model/addcontent.php',
  'group' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/model/group.php',
  'groupcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/model/group.php',
  'profiler' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/profiler.php',
  'request' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/request.php',
  'requesthandler' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/requesthandler.php',
  'resources' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/resources.php',
  'viewaccessabledata' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/viewaccessabledata.php',
  'ajaxsubmitbutton' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/AjaxSubmitButton.php',
  'autoformfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/AutoFormField.php',
  'bbcodeeditor' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/BBcodeEditor.php',
  'clusterformfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/ClusterFormField.php',
  'dropdown' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/DropDown.php',
  'email' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/EMail.php',
  'fieldset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FieldSet.php',
  'fileupload' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FileUpload.php',
  'fileuploadset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FileUploadSet.php',
  'form' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Form.php',
  'externalform' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Form.php',
  'formstate' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Form.php',
  'formaction' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FormAction.php',
  'formdecorator' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FormDecorator.php',
  'formdisabler' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FormDisabler.php',
  'formfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FormField.php',
  'formvalidator' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/FormValidator.php',
  'htmlaction' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/HTMLAction.php',
  'htmleditor' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/HTMLEditor.php',
  'htmlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/HTMLField.php',
  'hasonedropdown' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/HasOneDropdown.php',
  'hiddenfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Hiddenfield.php',
  'javascriptfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/JavaScriptField.php',
  'manymanydropdown' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/ManyManyDropDown.php',
  'multiselectdropdown' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/MultiSelectDropDown.php',
  'objectradiobutton' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/ObjectRadioButton.php',
  'passwordfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/PasswordField.php',
  'permissionfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/PermissionField.php',
  'radiobutton' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Radiobutton.php',
  'requestform' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/RequestForm.php',
  'requiredfields' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/RequiredFields.php',
  'select' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Select.php',
  'singleselectdropdown' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/SingleSelectDropDown.php',
  'tab' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Tab.php',
  'textfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/TextField.php',
  'textarea' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Textarea.php',
  'timefield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/TimeField.php',
  'ajaxexternalform' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/ajaxExternalForm.php',
  'button' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/button.php',
  'cancelbutton' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/cancelButton.php',
  'captcha' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/captcha.php',
  'checkbox' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/checkbox.php',
  'hidablefieldset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/hidableFieldSet.php',
  'imageupload' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/imageUpload.php',
  'infofield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/infoField.php',
  'infotextfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/infotext.php',
  'langselect' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/langSelect.php',
  'linkaction' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/link.php',
  'numberfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/number.php',
  'tablefieldconfig' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldConfig.php',
  'tablefieldconfig_base' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldConfig.php',
  'tablefield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/tableField.php',
  'tabset' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tabset.php',
  'g_softwaretype' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/SoftwareType.php',
  'g_frameworksoftwaretype' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/SoftwareType.php',
  'g_appsoftwaretype' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/SoftwareType.php',
  'g_expansionsoftwaretype' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/SoftwareType.php',
  'gfs' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/gfs.php',
  'gfs_package_installer' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/gfs.php',
  'gfs_package_creator' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/GFS/gfs.php',
  'ajaxlink' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/ajax/ajaxlink.php',
  'ajaxresponse' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/ajax/ajaxresponse.php',
  'javascriptresponse' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/ajax/ajaxresponse.php',
  'dialog' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/ajax/dialog.php',
  'restfulserver' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/api/RestfulServer.php',
  'arraylib' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/array/arraylib.php',
  'backup' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/backup/Backup.php',
  'backupadmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/backup/BackupAdmin.php',
  'backupsettings' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/backup/BackupAdmin.php',
  'backupmodel' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/backup/BackupModel.php',
  'cacher' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/cache/cacher.php',
  'pagelinkscontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/ckeditor_goma/pagelinks.php',
  'cookies' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/cookies/cookies.php',
  'cssmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/css/cssmin.php',
  'csv' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/csv/csv.php',
  'filesystem' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/file/FileSystem.php',
  'uploads' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/file/Uploads.php',
  'uploadscontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/file/Uploads.php',
  'imageuploads' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/file/Uploads.php',
  'imageuploadscontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/file/Uploads.php',
  'uploadcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/file/Uploads.php',
  'gd' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/gd/gd.php',
  'image' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/gd/image.php',
  'rootimage' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/gd/rootimage.php',
  'htmlnode' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/html/HTMLNode.php',
  'httpresponse' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/http/httpresponse.php',
  'htmlparser' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/http/httpresponse.php',
  'rawheaders' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/http/rawheaders.php',
  'gloader' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/javascript/gloader.php',
  'livecounter' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/livecounter/livecounter.php',
  'livecountercontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/livecounter/livecounter.php',
  'libmail' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/mail/libmail_162.php',
  'mail' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/mail/mail.php',
  'sql' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/sql/sql.php',
  'stringlib' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/string/stringlib.php',
  'tabs' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/tabs/tabs.php',
  'template' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/template/template.php',
  'tpl' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/template/tpl.php',
  'tplcaller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/template/tpl.php',
  'tplcacher' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/template/tpl.php',
  'bbcode' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/text/bbcode.php',
  'text' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/text/text.php',
  'texttransformer' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/text/text.php',
  'jsmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/jsmin/jsmin.php',
  'jsminexception' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/jsmin/jsmin.php',
  'fastjson' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/json/fastjson.php',
  'cfbinarypropertylist' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFBinaryPropertyList.php',
  'cfpropertylist' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFPropertyList.php',
  'cftype' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfstring' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfnumber' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfdate' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfboolean' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfdata' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfarray' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cfdictionary' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFType.php',
  'cftypedetector' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/CFTypeDetector.php',
  'ioexception' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/IOException.php',
  'plistexception' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/plist/PListException.php',
  'simple_html_dom_node' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/simple_html_dom/simple_html_dom.php',
  'simple_html_dom' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/thirdparty/simple_html_dom/simple_html_dom.php',
  'versionsviewcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/versions/versionsViewController.php',
  'permission' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/Permission.php',
  'hash' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/hash.php',
  'md5hash' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/hash.php',
  'gomahash' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/hash.php',
  'lost_passwordextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/lost_password.php',
  'usercontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/member.php',
  'user' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/member.php',
  'member' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/member.php',
  'profilecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/profile.php',
  'registerextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/register.php',
  'treeserver' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/servers/treeserver.php',
  'frontedcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/installer/application/control/FrontedController.php',
  'homepagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/installer/application/control/HomePageController.php',
  'installcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/installer/application/control/installController.php',
);
ClassInfo::$tables = array (
  'groups' => 'group',
  'backupmodel' => 'backupmodel',
  'uploads' => 'uploads',
  'imageuploads' => 'imageuploads',
  'statistics' => 'livecounter',
  'permission' => 'permission',
  'users' => 'user',
);
ClassInfo::$database = array (
);
ClassManifest::$preload = array (
  0 => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/_config.php',
  1 => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/installer/application/_config.php',
);
$root = '/var/www/vhosts/ibpg.eu/filmteam-dev/';$version = '3.5.1';
include_once('/var/www/vhosts/ibpg.eu/filmteam-dev/system/_config.php');
include_once('/var/www/vhosts/ibpg.eu/filmteam-dev/system/installer/application/_config.php');