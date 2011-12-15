-- Table rating

DROP TABLE IF EXISTS {!#PREFIX}rating;
  -- Create 
CREATE TABLE {!#PREFIX}rating (
 `id` int(10) NOT NULL auto_increment,
 `last_modified` int(90) NOT NULL,
 `class_name` enum('rating') NOT NULL,
 `created` int(90) NOT NULL,
 `name` varchar(200) NOT NULL,
 `rates` int(10) NOT NULL,
 `rating` int(11) NOT NULL,
 `rators` text NOT NULL,
 `autorid` int(10) NOT NULL,
 `editorid` int(10) NOT NULL,
 `recordid` int(10) NOT NULL,
 `snap_priority` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `name` (name),
KEY `class_name` (class_name),
KEY `last_modified` (last_modified),
KEY `autorid` (autorid),
KEY `editorid` (editorid),
KEY `recordid` (recordid));

; 





