-- Table metasettings

DROP TABLE IF EXISTS {!#PREFIX}metasettings;
  -- Create 
CREATE TABLE {!#PREFIX}metasettings (
 `id` int(10) NOT NULL auto_increment,
 `meta_keywords` varchar(100) NOT NULL,
 `meta_description` varchar(100) NOT NULL,
 `autorid` int(10) NOT NULL,
 `editorid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `autorid` (autorid),
KEY `editorid` (editorid));

-- INSERT 
 INSERT INTO {!#PREFIX}metasettings (`id` , `meta_keywords` , `meta_description` , `autorid` , `editorid`) VALUES  ( '29','','','15','3' )
; 





