-- Table templatesettings

DROP TABLE IF EXISTS {!#PREFIX}templatesettings;
  -- Create 
CREATE TABLE {!#PREFIX}templatesettings (
 `id` int(10) NOT NULL auto_increment,
 `stpl` varchar(64) NOT NULL DEFAULT 'default',
 `autorid` int(10) NOT NULL,
 `css_standard` text NOT NULL,
 `editorid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `autorid` (autorid),
KEY `editorid` (editorid));

-- INSERT 
 INSERT INTO {!#PREFIX}templatesettings (`id` , `stpl` , `autorid` , `css_standard` , `editorid`) VALUES  ( '29','Paderklicke','15','','3' )
; 





