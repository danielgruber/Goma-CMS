-- Table newsettings

DROP TABLE IF EXISTS {!#PREFIX}newsettings;
  -- Create 
CREATE TABLE {!#PREFIX}newsettings (
 `id` int(10) NOT NULL auto_increment,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('metasettings','templatesettings','newsettings') NOT NULL,
 `created` int(90) NOT NULL,
 `register` varchar(100) NOT NULL,
 `register_enabled` enum('0','1') NOT NULL,
 `titel` varchar(50) NOT NULL DEFAULT 'Goma - Open Source CMS / Framework',
 `gzip` enum('0','1') NOT NULL,
 `livecounter` enum('0','1') NOT NULL,
 `autorid` int(10) NOT NULL,
 `register_email` enum('0','1') NOT NULL DEFAULT '1',
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `autorid` (autorid),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}newsettings (`id` , `last_modified` , `class_name` , `created` , `register` , `register_enabled` , `titel` , `gzip` , `livecounter` , `autorid` , `register_email` , `editorid` , `recordid` , `snap_priority`) VALUES  ( '29','1320229616','newsettings','1317048275','','0','test','1','0','15','1','3','1','1' )
; 





