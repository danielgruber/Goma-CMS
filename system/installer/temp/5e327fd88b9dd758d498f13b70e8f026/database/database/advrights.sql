-- Table advrights

DROP TABLE IF EXISTS {!#PREFIX}advrights;
  -- Create 
CREATE TABLE {!#PREFIX}advrights (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('advrights') NOT NULL,
 `created` int(90) NOT NULL,
 `name` varchar(200) NOT NULL,
 `_default` int(10) NOT NULL,
 `title` varchar(200) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
UNIQUE `name` (name),
KEY `autorid` (autorid),
KEY `editorid` (editorid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}advrights (`id` , `autorid` , `last_modified` , `class_name` , `created` , `name` , `_default` , `title` , `editorid` , `recordid` , `snap_priority`) VALUES  ( '1','3','1320229623','advrights','1320229623','DATA_ALL','7','{$_lang_dataobject_all}','3','1','2' )
, ( '2','3','1320229623','advrights','1320229623','DATA_EDIT','7','{$_lang_dataobject_edit}','3','2','2' )
, ( '3','3','1320229623','advrights','1320229623','DATA_DELETE','7','{$_lang_dataobject_delete}','3','3','2' )
, ( '4','3','1320229623','advrights','1320229623','DATA_INSERT','7','{$_lang_dataobject_add}','3','4','2' )
, ( '5','3','1320229623','advrights','1320229623','ADMIN_ALL','7','{$_lang_administration}','3','5','2' )
, ( '6','3','1320229623','advrights','1320229623','SETTINGS_ALL','7','{$_lang_edit_settings}','3','6','2' )
, ( '7','3','1320229623','advrights','1320229623','BOXES_ALL','7','{$_lang_admin_boxes}','3','7','2' )
, ( '8','3','1320229623','advrights','1320229623','PAGES_ALL','9','{$_lang_sites_edit}','3','8','2' )
, ( '9','3','1320229623','advrights','1320229623','PAGES_DELETE','9','{$_lang_pages_delete}','3','9','2' )
, ( '10','3','1320229623','advrights','1320229623','PAGES_INSERT','7','{$_lang_pages_add}','3','10','2' )
, ( '11','3','1320229623','advrights','1320229623','PAGES_WRITE','7','{$_lang_pages_edit}','3','11','2' )
, ( '12','3','1320229623','advrights','1320229623','PAGES_PUBLISH','7','{$_lang_publish}','3','12','2' )
, ( '13','3','1320229623','advrights','1320229623','RATING_ALL','7','','3','13','2' )
, ( '14','3','1320229623','advrights','1320229623','RATING_DELETE','7','{$_lang_rating.perms_delete}','3','14','2' )
, ( '15','3','1320229623','advrights','1320229623','admin_smilies','7','{$_lang_admin_smilies}','3','15','2' )
; 





