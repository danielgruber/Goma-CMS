-- Table pagecomments

DROP TABLE IF EXISTS {!#PREFIX}pagecomments;
  -- Create 
CREATE TABLE {!#PREFIX}pagecomments (
 `id` int(10) NOT NULL auto_increment,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('pagecomments') NOT NULL,
 `autorid` int(10) NOT NULL,
 `name` varchar(200) NOT NULL,
 `text` text NOT NULL,
 `timestamp` int(200) NOT NULL,
 `pageid` int(10) NOT NULL,
 `created` int(90) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `name` (name),
KEY `pageid` (pageid),
KEY `autorid` (autorid),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

; 





