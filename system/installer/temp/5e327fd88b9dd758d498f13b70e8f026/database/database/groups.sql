-- Table groups

DROP TABLE IF EXISTS {!#PREFIX}groups;
  -- Create 
CREATE TABLE {!#PREFIX}groups (
 `id` int(8) NOT NULL auto_increment,
 `name` varchar(100) NOT NULL,
 `rights` int(2) NOT NULL,
 `autorid` int(10) NOT NULL,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('group') NOT NULL,
 `created` int(90) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `searchable_fields` (name),
KEY `autorid` (autorid),
KEY `editorid` (editorid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}groups (`id` , `name` , `rights` , `autorid` , `last_modified` , `class_name` , `created` , `editorid` , `recordid` , `snap_priority`) VALUES  ( '1','Superadministratoren','10','0','1312153318','group','1312153318','0','1','0' )
, ( '3','Benutzer','4','0','1307994590','group','1307994590','0','3','0' )
, ( '5','Administratoren','9','15','1318799188','group','1307994676','15','4','1' )
; 





