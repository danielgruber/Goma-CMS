<?php
ClassInfo::$appENV = array (
  'framework' => 
  array (
    'type' => 'framework',
    'name' => 'goma',
    'autor' => 'Goma Team',
    'version' => '2.0',
    'build' => '048',
    'icon' => 'templates/images/app-icon.png',
    'Codename' => 'Dandelion',
    'title' => 'Goma Dandelion',
  ),
  'app' => 
  array (
    'type' => 'app',
    'name' => 'gomacms',
    'title' => 'Goma CMS',
    'autor' => 'Goma Team',
    'version' => '2.0',
    'build' => '034',
    'requireFrameworkVersion' => '2.0-044',
    'icon' => 'templates/images/app-icon.png',
    'excludeModelsFromDistro' => 
    array (
      0 => 'pm',
    ),
    'excludeFiles' => 
    array (
      0 => 'application/.WELCOME_RUN',
    ),
    'SQL' => true,
  ),
  'expansion' => 
  array (
    'gomacms_rating' => 
    array (
      'type' => 'expansion',
      'name' => 'gomacms_rating',
      'title' => 'Goma CMS Rating',
      'autor' => 'Goma Team',
      'version' => '1.0',
      'viewFolder' => 'templates',
      'langFolder' => 'languages',
      'loadCode' => 'classes',
      'defaultLang' => 'en-us',
      'folder' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/plugins/rating/contents/',
      'classes' => 
      array (
        0 => 'rating',
        1 => 'ratingcontroller',
        2 => 'ratingdataobjectextension',
      ),
    ),
  ),
);
ClassInfo::$class_info = array (
  'permissionprovider' => 
  array (
    'abstract' => true,
    'interface' => true,
  ),
  'permprovider' => 
  array (
    'abstract' => true,
    'interface' => true,
  ),
  'classinfo' => 
  array (
    'parent' => 'object',
  ),
  'savevarsetter' => 
  array (
    'abstract' => true,
    'interface' => true,
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
      ),
      11 => 
      array (
        'rate/$name/$rate' => 'ratingController',
        'search' => 'searchController',
        'boxes_new' => 'boxesController',
      ),
      10 => 
      array (
        'pm' => 'PMController',
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
        'gloader' => 'Gloader',
      ),
      9 => 
      array (
        'system' => 'SystemController',
      ),
      1 => 
      array (
        '' => 'HomePageController',
        '$path!//$Action/$id/$otherid' => 'SiteController',
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
        'pmprofileextension' => '',
      ),
      'tplcaller' => 
      array (
        'boxestplextension' => '',
        'contenttplextension' => '',
        'pmtemplateextension' => '',
      ),
      'pages' => 
      array (
        'pagecommentsdataobjectextension' => '',
        'ratingdataobjectextension' => '',
        'searchpageextension' => '',
      ),
      'contentcontroller' => 
      array (
        'pagecommentscontrollerextension' => '',
      ),
      'bbcode' => 
      array (
        'smiliebbcodeextension' => '',
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
        'pmprofileextension' => '',
      ),
      'tplcaller' => 
      array (
        'boxestplextension' => '',
        'contenttplextension' => '',
        'pmtemplateextension' => '',
      ),
      'pages' => 
      array (
        'pagecommentsdataobjectextension' => '',
        'ratingdataobjectextension' => '',
        'searchpageextension' => '',
      ),
      'contentcontroller' => 
      array (
        'pagecommentscontrollerextension' => '',
      ),
      'bbcode' => 
      array (
        'smiliebbcodeextension' => '',
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
      'tplcaller' => 
      array (
        'boxes' => 
        array (
          0 => 'EXT:boxestplextension',
          1 => 'boxes',
        ),
        'level' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'level',
        ),
        'mainbar' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'mainbar',
        ),
        'active_mainbar_title' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'active_mainbar_title',
        ),
        'mainbarbyid' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'mainbarByID',
        ),
        'prendedcontent' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'prendedContent',
        ),
        'appendedcontent' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'appendedContent',
        ),
        'active_mainbar_url' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'active_mainbar_url',
        ),
        'pagebyid' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'pageByID',
        ),
        'pagebypath' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'pageByPath',
        ),
        'active_mainbar' => 
        array (
          0 => 'EXT:contenttplextension',
          1 => 'active_mainbar',
        ),
        'pm_unread' => 
        array (
          0 => 'EXT:pmtemplateextension',
          1 => 'PM_Unread',
        ),
      ),
      'contentcontroller' => 
      array (
        'pagecomments' => 
        array (
          0 => 'EXT:pagecommentscontrollerextension',
          1 => 'pagecomments',
        ),
      ),
    ),
  ),
  'extensionmodel' => 
  array (
    'abstract' => true,
    'interface' => true,
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
  'databasefield' => 
  array (
    'abstract' => true,
    'interface' => true,
  ),
  'defaultconvert' => 
  array (
    'abstract' => true,
    'interface' => true,
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
      6 => '/st',
      7 => '/comments',
      8 => '/article',
      9 => '/pm',
      10 => '/search',
    ),
    'defaultLanguagefiles' => 
    array (
      '/form' => 'de',
      '/backup' => 'de',
      '/files' => 'de',
      '/st' => 'de',
      '/bbcode' => 'de',
      '/members' => 'de',
      '/comments' => 'de',
      '/article' => 'de',
      '/pm' => 'de',
      '/search' => 'de',
    ),
  ),
  'addcontent' => 
  array (
    'parent' => 'object',
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
      1 => 'mysite/templates',
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
      'system/templates/admin/leftandmain_add.html' => true,
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
      'system/templates/css/dialog.css' => true,
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
      'system/templates/form/tableField/sortableHeader.html' => true,
      'system/templates/framework/503.html' => true,
      'system/templates/framework/buildDistro.html' => true,
      'system/templates/framework/dev.html' => true,
      'system/templates/framework/dialog.html' => true,
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
      'mysite/templates/account/memberlist.html' => true,
      'mysite/templates/admin/columnedadmin.html' => true,
      'mysite/templates/admin/content_index.html' => true,
      'mysite/templates/admin/header_userbar.html' => true,
      'mysite/templates/admin/preview.html' => true,
      'mysite/templates/admin/settings.html' => true,
      'mysite/templates/admin/usergroup_index.html' => true,
      'mysite/templates/articlesystem/article.css' => true,
      'mysite/templates/articlesystem/article.html' => true,
      'mysite/templates/articlesystem/category.html' => true,
      'mysite/templates/boxes/boxes.css' => true,
      'mysite/templates/boxes/boxes.html' => true,
      'mysite/templates/comments/comments.css' => true,
      'mysite/templates/comments/comments.html' => true,
      'mysite/templates/comments/onecomment.html' => true,
      'mysite/templates/pages/box.html' => true,
      'mysite/templates/pages/mod.html' => true,
      'mysite/templates/pages/page.html' => true,
      'mysite/templates/pages/search.css' => true,
      'mysite/templates/pages/search.html' => true,
      'mysite/templates/pm/inbox.html' => true,
      'mysite/templates/pm/message.html' => true,
      'mysite/templates/pm/pm.css' => true,
      'mysite/templates/pm/thread.html' => true,
      'mysite/templates/search/search.html' => true,
      'mysite/templates/slider/slider.html' => true,
      'mysite/templates/welcome/finish.html' => true,
      'mysite/templates/welcome/step2.html' => true,
      'mysite/templates/welcome/step3.html' => true,
      'mysite/templates/welcome/welcome.css' => true,
      'mysite/templates/welcome/welcome.html' => true,
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
    'interfaces' => 
    array (
      0 => 'formactionhandler',
    ),
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
  'formactionhandler' => 
  array (
    'abstract' => true,
    'interface' => true,
  ),
  'formaction' => 
  array (
    'parent' => 'formfield',
    'interfaces' => 
    array (
      0 => 'formactionhandler',
    ),
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
    'interfaces' => 
    array (
      0 => 'formactionhandler',
    ),
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
    'interfaces' => 
    array (
      0 => 'formactionhandler',
    ),
  ),
  'cancelbutton' => 
  array (
    'parent' => 'formaction',
    'interfaces' => 
    array (
      0 => 'formactionhandler',
    ),
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
    'interfaces' => 
    array (
      0 => 'formactionhandler',
    ),
  ),
  'numberfield' => 
  array (
    'parent' => 'formfield',
  ),
  'tablefieldcomponent' => 
  array (
    'abstract' => true,
    'interface' => true,
  ),
  'tablefield_htmlprovider' => 
  array (
    'parent' => 'tablefieldcomponent',
    'abstract' => true,
    'interface' => true,
  ),
  'tablefield_datamanipulator' => 
  array (
    'parent' => 'tablefieldcomponent',
    'abstract' => true,
    'interface' => true,
  ),
  'tablefield_columnprovider' => 
  array (
    'parent' => 'tablefieldcomponent',
    'abstract' => true,
    'interface' => true,
  ),
  'tablefield_urlhandler' => 
  array (
    'parent' => 'tablefieldcomponent',
    'abstract' => true,
    'interface' => true,
  ),
  'tablefield_actionprovider' => 
  array (
    'parent' => 'tablefieldcomponent',
    'abstract' => true,
    'interface' => true,
  ),
  'tablefieldconfig' => 
  array (
    'parent' => 'object',
  ),
  'tablefieldconfig_base' => 
  array (
    'parent' => 'tablefieldconfig',
  ),
  'tablefielddatacolumns' => 
  array (
    'interfaces' => 
    array (
      0 => 'tablefield_columnprovider',
    ),
  ),
  'tablefieldpaginator' => 
  array (
    'interfaces' => 
    array (
      0 => 'tablefield_htmlprovider',
      1 => 'tablefield_datamanipulator',
      2 => 'tablefield_actionprovider',
    ),
  ),
  'tablefieldsortableheader' => 
  array (
    'interfaces' => 
    array (
      0 => 'tablefield_htmlprovider',
      1 => 'tablefield_datamanipulator',
      2 => 'tablefield_actionprovider',
    ),
  ),
  'tablefieldtoolbarheader' => 
  array (
    'interfaces' => 
    array (
      0 => 'tablefield_htmlprovider',
    ),
  ),
  'tablefield' => 
  array (
    'parent' => 'formfield',
  ),
  'tablefieldfilterheader' => 
  array (
    'interfaces' => 
    array (
      0 => 'tablefield_htmlprovider',
      1 => 'tablefield_datamanipulator',
      2 => 'tablefield_actionprovider',
    ),
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
      'class_name' => 'enum("backupsettings","metasettings","templatesettings","newsettings")',
      'created' => 'date()',
      'titel' => 'varchar(50)',
      'register' => 'varchar(100)',
      'register_enabled' => 'Switch',
      'register_email' => 'Switch',
      'gzip' => 'Switch',
      'excludefolders' => 'text',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'excludeFolders' => 'text',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'backupsettings',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'backupsettings',
    ),
    'baseclass' => 'newsettings',
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
    'table_exists' => true,
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
    'table_exists' => true,
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
    'table_exists' => true,
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
    'parent' => 'controller',
    'resources' => 
    array (
      'rating' => 
      array (
        'file' => 'mysite/application/plugins/rating/contents/classes/rating.js',
        'required' => 
        array (
        ),
      ),
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
        'file' => 'system/libs/javascript/dropdownDialog.js',
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
      'htmllib' => 
      array (
        'file' => 'system/libs/javascript/html.js',
        'required' => 
        array (
        ),
      ),
      'history' => 
      array (
        'file' => 'system/libs/javascript/history/history.js',
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
      'referer' => 'varchar(400)',
      'ip' => 'varchar(30)',
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
      'referer' => 'varchar(400)',
      'ip' => 'varchar(30)',
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
    'table_exists' => true,
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
  'sqldriver' => 
  array (
    'abstract' => true,
    'interface' => true,
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
      'formodel' => 'varchar(100)',
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
      'forModel' => 'varchar(100)',
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
    'table_exists' => true,
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
      'admin_backup' => 
      array (
        'title' => '{$_lang_backups}',
        'default' => 
        array (
          'type' => 'admins',
          'inherit' => 'ADMIN',
        ),
      ),
      'settings_admin' => 
      array (
        'title' => '{$_lang_edit_settings}',
        'default' => 
        array (
          'type' => 'admins',
        ),
        'forceGroups' => true,
        'inherit' => 'ADMIN',
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
      'canmanagepermissions' => 
      array (
        'title' => '{$_lang_rights_manage}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'boxes' => 
      array (
        'title' => '{$_lang_admin_boxes}',
        'default' => 
        array (
          'type' => 'admins',
          'inherit' => 'ADMIN',
        ),
      ),
      'pages_delete' => 
      array (
        'title' => '{$_lang_pages_delete}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'pages_insert' => 
      array (
        'title' => '{$_lang_pages_add}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'pages_write' => 
      array (
        'title' => '{$_lang_pages_edit}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'pages_publish' => 
      array (
        'title' => '{$_lang_publish}',
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
      'smilie_admin' => 
      array (
        'title' => '{$_lang_smilies}',
        'forceGroup' => true,
        'default' => 
        array (
          'type' => 'admins',
        ),
      ),
    ),
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
      'permissions' => 'permission',
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
      'permissions' => 
      array (
        'table' => 'many_many_permission_groups_group',
        'field' => 'groupid',
        'extfield' => 'permissionid',
      ),
    ),
    'table_name' => 'groups',
    'table_exists' => true,
    'baseclass' => 'group',
    'belongs_many_many_extra' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'groupid',
        'extfield' => 'pagesid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'groupid',
        'extfield' => 'pagesid',
      ),
    ),
  ),
  'groupcontroller' => 
  array (
    'parent' => 'controller',
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
    'table_exists' => true,
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
  'contentadmin' => 
  array (
    'parent' => 'leftandmain',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'settingsadmin' => 
  array (
    'parent' => 'adminitem',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'smilieadmin' => 
  array (
    'parent' => 'tableview',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'usergroupadmin' => 
  array (
    'parent' => 'leftandmain',
    'interfaces' => 
    array (
      0 => 'permprovider',
    ),
  ),
  'frontedcontroller' => 
  array (
    'parent' => 'controller',
  ),
  'sitecontroller' => 
  array (
    'parent' => 'controller',
  ),
  'homepagecontroller' => 
  array (
    'parent' => 'sitecontroller',
  ),
  'cms_filemanager' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => false,
    'table_exists' => false,
    'baseclass' => 'cms_filemanager',
  ),
  'cms_filemanagercontroller' => 
  array (
    'parent' => 'controller',
  ),
  'contentcontroller' => 
  array (
    'parent' => 'frontedcontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'newsettings' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'dataclasses' => 
    array (
      0 => 'backupsettings',
      1 => 'metasettings',
      2 => 'templatesettings',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("backupsettings","metasettings","templatesettings","newsettings")',
      'created' => 'date()',
      'titel' => 'varchar(50)',
      'register' => 'varchar(100)',
      'register_enabled' => 'Switch',
      'register_email' => 'Switch',
      'gzip' => 'Switch',
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
      'class_name' => 'enum("backupsettings","metasettings","templatesettings","newsettings")',
      'created' => 'date()',
      'titel' => 'varchar(50)',
      'register' => 'varchar(100)',
      'register_enabled' => 'Switch',
      'register_email' => 'Switch',
      'gzip' => 'Switch',
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
    'table_name' => 'newsettings',
    'table_exists' => true,
    'baseclass' => 'newsettings',
  ),
  'settingscontroller' => 
  array (
    'parent' => 'controller',
  ),
  'metasettings' => 
  array (
    'parent' => 'newsettings',
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
      'class_name' => 'enum("backupsettings","metasettings","templatesettings","newsettings")',
      'created' => 'date()',
      'titel' => 'varchar(50)',
      'register' => 'varchar(100)',
      'register_enabled' => 'Switch',
      'register_email' => 'Switch',
      'gzip' => 'Switch',
      'meta_keywords' => 'varchar(100)',
      'meta_description' => 'varchar(100)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'meta_keywords' => 'varchar(100)',
      'meta_description' => 'varchar(100)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'metasettings',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'metasettings',
    ),
    'baseclass' => 'newsettings',
  ),
  'templatesettings' => 
  array (
    'parent' => 'newsettings',
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
      'class_name' => 'enum("backupsettings","metasettings","templatesettings","newsettings")',
      'created' => 'date()',
      'titel' => 'varchar(50)',
      'register' => 'varchar(100)',
      'register_enabled' => 'Switch',
      'register_email' => 'Switch',
      'gzip' => 'Switch',
      'stpl' => 'varchar(64)',
      'css_standard' => 'text',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'stpl' => 'varchar(64)',
      'css_standard' => 'text',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'templatesettings',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'templatesettings',
    ),
    'baseclass' => 'newsettings',
  ),
  'welcomecontroller' => 
  array (
    'parent' => 'controller',
  ),
  'phpbox' => 
  array (
    'parent' => 'box',
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
      'class_name' => 'enum("phpbox","statistics","box","login_meinaccount","boxes")',
      'created' => 'date()',
      'title' => 'varchar(100)',
      'text' => 'text',
      'border' => 'switch',
      'sort' => 'int(3)',
      'seiteid' => 'varchar(50)',
      'width' => 'varchar(5)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'searchable_fields' => 
    array (
      0 => 'text',
      1 => 'title',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'phpbox',
      1 => 'box',
    ),
    'baseclass' => 'boxes',
  ),
  'statistics' => 
  array (
    'parent' => 'box',
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
      'class_name' => 'enum("phpbox","statistics","box","login_meinaccount","boxes")',
      'created' => 'date()',
      'title' => 'varchar(100)',
      'text' => 'text',
      'border' => 'switch',
      'sort' => 'int(3)',
      'seiteid' => 'varchar(50)',
      'width' => 'varchar(5)',
      'today' => 'int(1)',
      'last2' => 'int(1)',
      'last30d' => 'int(1)',
      'whole' => 'int(1)',
      'online' => 'int(1)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'today' => 'int(1)',
      'last2' => 'int(1)',
      'last30d' => 'int(1)',
      'whole' => 'int(1)',
      'online' => 'int(1)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'searchable_fields' => 
    array (
      0 => 'text',
      1 => 'title',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'Box_statistics',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'statistics',
      1 => 'box',
    ),
    'baseclass' => 'boxes',
  ),
  'boxes' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'dataclasses' => 
    array (
      0 => 'statistics',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("phpbox","statistics","box","login_meinaccount","boxes")',
      'created' => 'date()',
      'title' => 'varchar(100)',
      'text' => 'text',
      'border' => 'switch',
      'sort' => 'int(3)',
      'seiteid' => 'varchar(50)',
      'width' => 'varchar(5)',
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
      'class_name' => 'enum("phpbox","statistics","box","login_meinaccount","boxes")',
      'created' => 'date()',
      'title' => 'varchar(100)',
      'text' => 'text',
      'border' => 'switch',
      'sort' => 'int(3)',
      'seiteid' => 'varchar(50)',
      'width' => 'varchar(5)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'searchable_fields' => 
    array (
      0 => 'text',
      1 => 'title',
    ),
    'indexes' => 
    array (
      'view' => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'seiteid',
          1 => 'sort',
        ),
        'name' => '_show',
      ),
      'searchable_fields' => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'text(166)',
          1 => 'title',
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
    ),
    'table_name' => 'boxes',
    'table_exists' => true,
    'baseclass' => 'boxes',
  ),
  'boxescontroller' => 
  array (
    'parent' => 'frontedcontroller',
  ),
  'box' => 
  array (
    'parent' => 'boxes',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'dataclasses' => 
    array (
      0 => 'statistics',
      1 => 'box',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("phpbox","statistics","box","login_meinaccount","boxes")',
      'created' => 'date()',
      'title' => 'varchar(100)',
      'text' => 'text',
      'border' => 'switch',
      'sort' => 'int(3)',
      'seiteid' => 'varchar(50)',
      'width' => 'varchar(5)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'searchable_fields' => 
    array (
      0 => 'text',
      1 => 'title',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => false,
    'table_exists' => false,
    'baseclass' => 'boxes',
  ),
  'boxcontroller' => 
  array (
    'parent' => 'boxescontroller',
  ),
  'login_meinaccount' => 
  array (
    'parent' => 'box',
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
      'class_name' => 'enum("phpbox","statistics","box","login_meinaccount","boxes")',
      'created' => 'date()',
      'title' => 'varchar(100)',
      'text' => 'text',
      'border' => 'switch',
      'sort' => 'int(3)',
      'seiteid' => 'varchar(50)',
      'width' => 'varchar(5)',
    ),
    'has_one' => 
    array (
      'autor' => 'user',
      'editor' => 'user',
    ),
    'searchable_fields' => 
    array (
      0 => 'text',
      1 => 'title',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'login_meinaccount',
      1 => 'box',
    ),
    'baseclass' => 'boxes',
  ),
  'boxestplextension' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'boxpage' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'boxpage',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'boxpagecontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'page' => 
  array (
    'parent' => 'pages',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'page',
      1 => 'virtualpage',
      2 => 'article',
      3 => 'contact',
      4 => 'errorpage',
    ),
    'baseclass' => 'pages',
  ),
  'pagecontroller' => 
  array (
    'parent' => 'contentcontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'pagecomments' => 
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
      'class_name' => 'enum("pagecomments")',
      'created' => 'date()',
      'name' => 'varchar(200)',
      'text' => 'text',
      'timestamp' => 'int(200)',
      'pageid' => 'int(10)',
    ),
    'has_one' => 
    array (
      'page' => 'pages',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("pagecomments")',
      'created' => 'date()',
      'name' => 'varchar(200)',
      'text' => 'text',
      'timestamp' => 'int(200)',
      'pageid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'name' => true,
      'pageid' => 'INDEX',
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'pagecomments',
    'table_exists' => true,
    'baseclass' => 'pagecomments',
  ),
  'pagecommentscontroller' => 
  array (
    'parent' => 'frontedcontroller',
  ),
  'pagecommentsdataobjectextension' => 
  array (
    'parent' => 'dataobjectextension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'pagecommentscontrollerextension' => 
  array (
    'parent' => 'controllerextension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
    ),
  ),
  'virtualpage' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
      'regardingpageid' => 'int(10)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'regardingpage' => 'pages',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'regardingPageid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'indexes' => 
    array (
      'regardingPageid' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'Page_virtualpage',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'virtualpage',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'virtualpagecontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'articlecategory' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
      'categories' => 'articlecategory',
      'articles' => 'article',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'articlecategory',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'articlecategorycontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'article' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
      'description' => 'text',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'description' => 'text',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'Page_article',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'article',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'articlecontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'articles' => 
  array (
    'parent' => 'articlecategory',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
      'categories' => 'articlecategory',
      'articles' => 'article',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'articles',
      1 => 'articlecategory',
      2 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'articlescontroller' => 
  array (
    'parent' => 'articlecategorycontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'contact' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
      'email' => 'varchar(200)',
      'requireemailfield' => 'Checkbox',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'email' => 'varchar(200)',
      'requireemailfield' => 'Checkbox',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'Page_contact',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'contact',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'contactcontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'errorpage' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
      'code' => 'varchar(50)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'code' => 'varchar(50)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'Page_errorpage',
    'table_exists' => true,
    'dataclasses' => 
    array (
      0 => 'errorpage',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'errorpagecontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'members' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'members',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'memberscontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'phppage' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'phppage',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'phppagecontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'redirector' => 
  array (
    'parent' => 'page',
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
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => false,
    'table_exists' => false,
    'dataclasses' => 
    array (
      0 => 'redirector',
      1 => 'page',
    ),
    'baseclass' => 'pages',
  ),
  'redirectorcontroller' => 
  array (
    'parent' => 'pagecontroller',
    'allowed_actions' => 
    array (
      0 => 'pagecomments',
    ),
  ),
  'pages' => 
  array (
    'parent' => 'dataobject',
    'interfaces' => 
    array (
      0 => 'permprovider',
      1 => 'savevarsetter',
      2 => 'iterator',
      3 => 'arrayaccess',
    ),
    'dataclasses' => 
    array (
      0 => 'virtualpage',
      1 => 'article',
      2 => 'contact',
      3 => 'errorpage',
    ),
    'casting' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
    ),
    'has_one' => 
    array (
      'parent' => 'pages',
      'read_permission' => 'permission',
      'edit_permission' => 'permission',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'has_many' => 
    array (
      'children' => 'pages',
      'comments' => 'pagecomments',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("boxpage","page","virtualpage","articlecategory","article","articles","contact","errorpage","members","phppage","redirector","pages")',
      'created' => 'date()',
      'path' => 'varchar(500)',
      'rights' => 'int(2)',
      'mainbar' => 'int(1)',
      'mainbartitle' => 'varchar(200)',
      'title' => 'varchar(200)',
      'data' => 'text',
      'sort' => 'int(8)',
      'search' => 'int(1)',
      'editright' => 'text',
      'meta_description' => 'varchar(200)',
      'meta_keywords' => 'varchar(200)',
      'parentid' => 'int(10)',
      'read_permissionid' => 'int(10)',
      'edit_permissionid' => 'int(10)',
      'showcomments' => 'int(1)',
      'rating' => 'int(1)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'many_many' => 
    array (
      'edit_groups' => 'group',
      'viewer_groups' => 'group',
    ),
    'defaults' => 
    array (
      'showcomments' => 0,
      'rating' => 0,
    ),
    'searchable_fields' => 
    array (
      0 => 'data',
      1 => 'title',
      2 => 'mainbartitle',
      3 => 'meta_keywords',
    ),
    'indexes' => 
    array (
      0 => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'path(111)',
          1 => 'sort',
          2 => 'class_name',
        ),
        'name' => 'path',
      ),
      1 => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'parentid',
          1 => 'mainbar',
          2 => 'class_name',
        ),
        'name' => 'mainbar',
      ),
      2 => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'class_name',
          1 => 'data(55)',
          2 => 'title(55)',
          3 => 'mainbartitle(55)',
          4 => 'meta_keywords(55)',
          5 => 'id',
        ),
        'name' => 'sitesearch',
      ),
      'parentid' => 'INDEX',
      'read_permissionid' => 'INDEX',
      'edit_permissionid' => 'INDEX',
      'searchable_fields' => 
      array (
        'type' => 'INDEX',
        'fields' => 
        array (
          0 => 'data(83)',
          1 => 'title(83)',
          2 => 'mainbartitle(83)',
          3 => 'meta_keywords(83)',
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
      'edit_groups' => 
      array (
        'table' => 'many_many_pages_edit_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
      'viewer_groups' => 
      array (
        'table' => 'many_many_pages_viewer_groups_group',
        'field' => 'pagesid',
        'extfield' => 'groupid',
      ),
    ),
    'table_name' => 'pages',
    'table_exists' => true,
    'baseclass' => 'pages',
  ),
  'contenttplextension' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'rating' => 
  array (
    'parent' => 'dataobject',
    'inExpansion' => 'gomacms_rating',
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
      'class_name' => 'enum("rating")',
      'created' => 'date()',
      'name' => 'varchar(200)',
      'rates' => 'int(10)',
      'rating' => 'int(11)',
      'rators' => 'text',
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
      'class_name' => 'enum("rating")',
      'created' => 'date()',
      'name' => 'varchar(200)',
      'rates' => 'int(10)',
      'rating' => 'int(11)',
      'rators' => 'text',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'name' => 'INDEX',
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'rating',
    'table_exists' => true,
    'baseclass' => 'rating',
  ),
  'ratingcontroller' => 
  array (
    'parent' => 'controller',
    'inExpansion' => 'gomacms_rating',
  ),
  'ratingdataobjectextension' => 
  array (
    'parent' => 'dataobjectextension',
    'inExpansion' => 'gomacms_rating',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'pm' => 
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
      'class_name' => 'enum("pm")',
      'created' => 'date()',
      'text' => 'text',
      'time' => 'int(20)',
      'sig' => 'int(1)',
      'hasread' => 'int(1)',
      'fromid' => 'int(10)',
      'toid' => 'int(10)',
    ),
    'has_one' => 
    array (
      'from' => 'user',
      'to' => 'user',
      'autor' => 'user',
      'editor' => 'user',
    ),
    'db_fields' => 
    array (
      'id' => 'INT(10) AUTO_INCREMENT  PRIMARY KEY',
      'last_modified' => 'date()',
      'class_name' => 'enum("pm")',
      'created' => 'date()',
      'text' => 'text',
      'time' => 'int(20)',
      'sig' => 'int(1)',
      'hasread' => 'int(1)',
      'fromid' => 'int(10)',
      'toid' => 'int(10)',
      'autorid' => 'int(10)',
      'editorid' => 'int(10)',
    ),
    'indexes' => 
    array (
      'fromid' => 'INDEX',
      'toid' => 'INDEX',
      'last_modified' => 'INDEX',
    ),
    'iDBFields' => 
    array (
    ),
    'many_many_tables' => 
    array (
    ),
    'table_name' => 'pm',
    'table_exists' => true,
    'baseclass' => 'pm',
  ),
  'pmcontroller' => 
  array (
    'parent' => 'frontedcontroller',
  ),
  'pmprofileextension' => 
  array (
    'parent' => 'controllerextension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
    ),
  ),
  'pmtemplateextension' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'searchcontroller' => 
  array (
    'parent' => 'frontedcontroller',
  ),
  'searchpageextension' => 
  array (
    'parent' => 'dataobjectextension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'smilies' => 
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
      'class_name' => 'enum("smilies")',
      'created' => 'date()',
      'image' => 'Image',
      'description' => 'varchar(200)',
      'code' => 'varchar(200)',
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
      'class_name' => 'enum("smilies")',
      'created' => 'date()',
      'image' => 'Image',
      'description' => 'varchar(200)',
      'code' => 'varchar(200)',
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
    'table_name' => 'smilies',
    'table_exists' => true,
    'baseclass' => 'smilies',
  ),
  'smiliebbcodeextension' => 
  array (
    'parent' => 'extension',
    'interfaces' => 
    array (
      0 => 'extensionmodel',
      1 => 'iterator',
      2 => 'arrayaccess',
    ),
  ),
  'exception' => 
  array (
  ),
);
ClassInfo::$files = array (
  'permissionprovider' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/application.php',
  'permprovider' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/application.php',
  'classinfo' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/ClassInfo.php',
  'savevarsetter' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/ClassInfo.php',
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
  'extensionmodel' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/Object.php',
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
  'databasefield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'defaultconvert' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/DBField.php',
  'imagesqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/Image.php',
  'selectsqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/selects.php',
  'radiossqlfield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/fields/selects.php',
  'i18n' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/i18n.php',
  'addcontent' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/core/model/addcontent.php',
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
  'formactionhandler' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/Form.php',
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
  'tablefieldcomponent' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldComponent.php',
  'tablefield_htmlprovider' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldComponent.php',
  'tablefield_datamanipulator' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldComponent.php',
  'tablefield_columnprovider' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldComponent.php',
  'tablefield_urlhandler' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldComponent.php',
  'tablefield_actionprovider' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldComponent.php',
  'tablefieldconfig' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldConfig.php',
  'tablefieldconfig_base' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldConfig.php',
  'tablefielddatacolumns' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldDataColumns.php',
  'tablefieldpaginator' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldPaginator.php',
  'tablefieldsortableheader' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldSortableHeader.php',
  'tablefieldtoolbarheader' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/TableFieldToolbarHeader.php',
  'tablefield' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/tableField.php',
  'tablefieldfilterheader' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/form/tableField/tableFieldFilterHeader.php',
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
  'sqldriver' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/libs/sql/sql.php',
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
  'group' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/group.php',
  'groupcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/security/group.php',
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
  'contentadmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/admin/content.php',
  'settingsadmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/admin/settingsAdmin.php',
  'smilieadmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/admin/smilieAdmin.php',
  'usergroupadmin' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/admin/usergroupAdmin.php',
  'frontedcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/FrontedController.php',
  'sitecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/FrontedController.php',
  'homepagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/FrontedController.php',
  'cms_filemanager' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/cms_filemanager.php',
  'cms_filemanagercontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/cms_filemanager.php',
  'contentcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/contentcontroller.php',
  'newsettings' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/settingscontroller.php',
  'settingscontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/settingscontroller.php',
  'metasettings' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/settingscontroller.php',
  'templatesettings' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/settingscontroller.php',
  'welcomecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/control/welcomeController.php',
  'phpbox' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes/phpbox.php',
  'statistics' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes/statisticbox.php',
  'boxes' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'boxescontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'box' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'boxcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'login_meinaccount' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'boxestplextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'boxpage' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'boxpagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/boxes.php',
  'page' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/page.php',
  'pagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/page.php',
  'pagecomments' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pagecomments.php',
  'pagecommentscontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pagecomments.php',
  'pagecommentsdataobjectextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pagecomments.php',
  'pagecommentscontrollerextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pagecomments.php',
  'virtualpage' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/VirtualPage.php',
  'virtualpagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/VirtualPage.php',
  'articlecategory' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/article.php',
  'articlecategorycontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/article.php',
  'article' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/article.php',
  'articlecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/article.php',
  'articles' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/article.php',
  'articlescontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/article.php',
  'contact' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/contact.php',
  'contactcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/contact.php',
  'errorpage' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/error.php',
  'errorpagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/error.php',
  'members' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/members.php',
  'memberscontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/members.php',
  'phppage' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/phpPage.php',
  'phppagecontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/phpPage.php',
  'redirector' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/redirectorpage.php',
  'redirectorcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages/redirectorpage.php',
  'pages' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages.php',
  'contenttplextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/model/pages.php',
  'rating' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/plugins/rating/contents/classes/rating.php',
  'ratingcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/plugins/rating/contents/classes/rating.php',
  'ratingdataobjectextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/plugins/rating/contents/classes/rating.php',
  'pm' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/pm.php',
  'pmcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/pm.php',
  'pmprofileextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/pm.php',
  'pmtemplateextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/pm.php',
  'searchcontroller' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/search.php',
  'searchpageextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/search.php',
  'smilies' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/smilies.php',
  'smiliebbcodeextension' => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/sitefeatures/smilies.php',
);
ClassInfo::$tables = array (
  'backupsettings' => 'backupsettings',
  'backupmodel' => 'backupmodel',
  'uploads' => 'uploads',
  'imageuploads' => 'imageuploads',
  'statistics' => 'livecounter',
  'permission' => 'permission',
  'groups' => 'group',
  'users' => 'user',
  'newsettings' => 'newsettings',
  'metasettings' => 'metasettings',
  'templatesettings' => 'templatesettings',
  'Box_statistics' => 'statistics',
  'boxes' => 'boxes',
  'pagecomments' => 'pagecomments',
  'Page_virtualpage' => 'virtualpage',
  'Page_article' => 'article',
  'Page_contact' => 'contact',
  'Page_errorpage' => 'errorpage',
  'pages' => 'pages',
  'rating' => 'rating',
  'pm' => 'pm',
  'smilies' => 'smilies',
);
ClassInfo::$database = array (
  'backupsettings' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'excludeFolders' => 'text',
  ),
  'backupmodel' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'backupmodel\')',
    'created' => 'int(30)',
    'name' => 'varchar(200)',
    'create_date' => 'varchar(200)',
    'justSQL' => 'int(1)',
    'size' => 'bigint(30)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'type' => 'varchar(40)',
  ),
  'uploads' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'imageuploads\',\'uploads\')',
    'created' => 'int(30)',
    'filename' => 'varchar(100)',
    'realfile' => 'varchar(300)',
    'path' => 'varchar(200)',
    'type' => 'enum(\'collection\',\'file\')',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'collectionid' => 'int(10)',
    'deletable' => 'enum(\'0\',\'1\')',
    'md5' => 'text',
  ),
  'imageuploads' => 
  array (
    'id' => 'int(10)',
    'width' => 'int(5)',
    'height' => 'int(5)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'thumbLeft' => 'int(3)',
    'thumbTop' => 'int(3)',
    'thumbWidth' => 'int(3)',
    'thumbHeight' => 'int(3)',
    'collectionid' => 'int(10)',
  ),
  'statistics' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'livecounter\')',
    'created' => 'int(30)',
    'user' => 'varchar(200)',
    'phpsessid' => 'varchar(800)',
    'mobile' => 'int(1)',
    'browser' => 'varchar(200)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'referer' => 'varchar(400)',
    'ip' => 'varchar(30)',
  ),
  'permission' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'permission\')',
    'created' => 'int(30)',
    'name' => 'varchar(100)',
    'type' => 'enum(\'all\',\'users\',\'admins\',\'password\',\'groups\')',
    'password' => 'varchar(100)',
    'invert_groups' => 'int(1)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'inheritorid' => 'int(10)',
    'forModel' => 'varchar(100)',
  ),
  'groups' => 
  array (
    'id' => 'int(8)',
    'name' => 'varchar(100)',
    'autorid' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'group\')',
    'created' => 'int(30)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'type' => 'enum(\'0\',\'1\',\'2\')',
  ),
  'users' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'user\')',
    'created' => 'int(30)',
    'nickname' => 'varchar(200)',
    'name' => 'varchar(200)',
    'email' => 'varchar(200)',
    'password' => 'varchar(200)',
    'signatur' => 'text',
    'status' => 'int(2)',
    'phpsess' => 'varchar(200)',
    'code' => 'varchar(200)',
    'timezone' => 'enum(\'Europe/Berlin\',\'Europe/London\',\'Europe/Paris\',\'Europe/Helsinki\',\'Europe/Moscow\',\'Europe/Madrid\',\'Pacific/Kwajalein\',\'Pacific/Samoa\',\'Pacific/Honolulu\',\'America/Juneau\',\'America/Los_Angeles\',\'America/Denver\',\'America/Mexico_City\',\'America/New_York\',\'America/Caracas\',\'America/St_Johns\',\'America/Argentina/Buenos_Aires\',\'Atlantic/Azores\',\'Atlantic/Azores\',\'Asia/Tehran\',\'Asia/Baku\',\'Asia/Kabul\',\'Asia/Karachi\',\'Asia/Calcutta\',\'Asia/Colombo\',\'Asia/Bangkok\',\'Asia/Singapore\',\'Asia/Tokyo\',\'Australia/ACT\',\'Australia/Currie\',\'Australia/Lindeman\',\'Australia/Perth\',\'Australia/Victoria\',\'Australia/Adelaide\',\'Australia/Darwin\',\'Australia/Lord_Howe\',\'Australia/Queensland\',\'Australia/West\',\'Australia/Brisbane\',\'Australia/Eucla\',\'Australia/Melbourne\',\'Australia/South\',\'Australia/Yancowinna\',\'Australia/Broken_Hill\',\'Australia/Hobart\',\'Australia/North\',\'Australia/Sydney\',\'Australia/Canberra\',\'Australia/LHI\',\'Australia/NSW\',\'Australia/Tasmania\',\'Pacific/Guam\',\'Asia/Magadan\',\'Asia/Kamchatka\',\'Africa/Abidjan\',\'Africa/Asmera\',\'Africa/Blantyre\',\'Africa/Ceuta\',\'Africa/Douala\',\'Africa/Johannesburg\',\'Africa/Windhoek\',\'Africa/Sao_Tome\',\'Africa/Timbuktu\',\'Africa/Niamey\')',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'avatarid' => 'int(10)',
    'custom_lang' => 'varchar(10)',
  ),
  'newsettings' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'backupsettings\',\'metasettings\',\'templatesettings\',\'newsettings\')',
    'created' => 'int(30)',
    'register' => 'varchar(100)',
    'register_enabled' => 'enum(\'0\',\'1\')',
    'titel' => 'varchar(50)',
    'gzip' => 'enum(\'0\',\'1\')',
    'autorid' => 'int(10)',
    'register_email' => 'enum(\'0\',\'1\')',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
  ),
  'metasettings' => 
  array (
    'id' => 'int(10)',
    'meta_keywords' => 'varchar(100)',
    'meta_description' => 'varchar(100)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
  ),
  'templatesettings' => 
  array (
    'id' => 'int(10)',
    'stpl' => 'varchar(64)',
    'autorid' => 'int(10)',
    'css_standard' => 'text',
    'editorid' => 'int(10)',
  ),
  'Box_statistics' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'today' => 'int(1)',
    'last2' => 'int(1)',
    'last30d' => 'int(1)',
    'whole' => 'int(1)',
    'online' => 'int(1)',
    'editorid' => 'int(10)',
  ),
  'boxes' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'phpbox\',\'statistics\',\'box\',\'login_meinaccount\',\'boxes\')',
    'created' => 'int(30)',
    'seiteid' => 'varchar(50)',
    'border' => 'enum(\'0\',\'1\')',
    'title' => 'varchar(100)',
    'sort' => 'int(3)',
    'text' => 'text',
    'width' => 'varchar(5)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
  ),
  'pagecomments' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'pagecomments\')',
    'autorid' => 'int(10)',
    'name' => 'varchar(200)',
    'text' => 'text',
    'timestamp' => 'int(200)',
    'created' => 'int(30)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'pageid' => 'int(10)',
  ),
  'Page_virtualpage' => 
  array (
    'id' => 'int(10)',
    'regardingPageid' => 'int(10)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
  ),
  'Page_article' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'description' => 'text',
    'editorid' => 'int(10)',
  ),
  'Page_contact' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'email' => 'varchar(200)',
    'editorid' => 'int(10)',
    'requireemailfield' => 'enum(\'0\',\'1\')',
  ),
  'Page_errorpage' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'code' => 'varchar(50)',
    'editorid' => 'int(10)',
  ),
  'pages' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'boxpage\',\'page\',\'virtualpage\',\'articlecategory\',\'article\',\'articles\',\'contact\',\'errorpage\',\'members\',\'phppage\',\'redirector\',\'pages\')',
    'created' => 'int(30)',
    'path' => 'varchar(500)',
    'rights' => 'int(2)',
    'mainbar' => 'int(1)',
    'mainbartitle' => 'varchar(200)',
    'title' => 'varchar(200)',
    'data' => 'text',
    'sort' => 'int(8)',
    'search' => 'int(1)',
    'editright' => 'text',
    'showcomments' => 'int(1)',
    'meta_description' => 'varchar(200)',
    'meta_keywords' => 'varchar(200)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'snap_priority' => 'int(10)',
    'rating' => 'int(1)',
    'parentid' => 'int(10)',
    'read_permissionid' => 'int(10)',
    'edit_permissionid' => 'int(10)',
  ),
  'rating' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'rating\')',
    'created' => 'int(30)',
    'name' => 'varchar(200)',
    'rates' => 'int(10)',
    'rating' => 'int(11)',
    'rators' => 'text',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
  ),
  'pm' => 
  array (
    'id' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'pm\')',
    'created' => 'int(30)',
    'text' => 'text',
    'time' => 'int(20)',
    'sig' => 'int(1)',
    'hasread' => 'int(1)',
    'autorid' => 'int(10)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
    'fromid' => 'int(10)',
    'toid' => 'int(10)',
  ),
  'smilies' => 
  array (
    'id' => 'int(10)',
    'autorid' => 'int(10)',
    'last_modified' => 'int(30)',
    'class_name' => 'enum(\'smilies\')',
    'image' => 'varchar(200)',
    'description' => 'varchar(200)',
    'code' => 'varchar(200)',
    'created' => 'int(30)',
    'editorid' => 'int(10)',
    'recordid' => 'int(10)',
  ),
  'newsettings_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'backupmodel_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'uploads_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'statistics_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'permission_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'groups_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'users_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'boxes_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'pages_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'pagecomments_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'rating_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'pm_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
  'smilies_state' => 
  array (
    'id' => 'int(10)',
    'stateid' => 'int(10)',
    'publishedid' => 'int(10)',
  ),
);
ClassManifest::$preload = array (
  0 => '/var/www/vhosts/ibpg.eu/filmteam-dev/system/_config.php',
  1 => '/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/_config.php',
);
$root = '/var/www/vhosts/ibpg.eu/filmteam-dev/';$version = '3.6';
include_once('/var/www/vhosts/ibpg.eu/filmteam-dev/system/_config.php');
include_once('/var/www/vhosts/ibpg.eu/filmteam-dev/mysite/application/_config.php');