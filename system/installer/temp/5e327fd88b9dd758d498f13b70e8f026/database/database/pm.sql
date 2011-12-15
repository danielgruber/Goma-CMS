-- Table pm

DROP TABLE IF EXISTS {!#PREFIX}pm;
  -- Create 
CREATE TABLE {!#PREFIX}pm (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('pm') NOT NULL,
 `fromid` int(10) NOT NULL,
 `toid` int(10) NOT NULL,
 `subject` varchar(200) NOT NULL,
 `text` text NOT NULL,
 `sig` int(1) NOT NULL,
 `time` int(20) NOT NULL,
 `tid` int(10) NOT NULL,
 `created` int(90) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
 `hasread` int(1) NOT NULL,
PRIMARY KEY (id),
KEY `fromid` (fromid),
KEY `toid` (toid),
KEY `autorid` (autorid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

-- INSERT 
 INSERT INTO {!#PREFIX}pm (`id` , `autorid` , `last_modified` , `class_name` , `fromid` , `toid` , `subject` , `text` , `sig` , `time` , `tid` , `created` , `editorid` , `recordid` , `snap_priority` , `hasread`) VALUES  ( '1','15','1317062482','pm','15','15','test','[b]Hallo Welt[/b]','1','1317054596','0','1317054596','15','2','1','1' )
; 





