-- Table Page_article

DROP TABLE IF EXISTS {!#PREFIX}Page_article;
  -- Create 
CREATE TABLE {!#PREFIX}Page_article (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `description` text NOT NULL,
 `editorid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `autorid` (autorid),
KEY `editorid` (editorid));

-- INSERT 
 INSERT INTO {!#PREFIX}Page_article (`id` , `autorid` , `description` , `editorid`) VALUES  ( '1','1','','1' )
, ( '2','1','','1' )
, ( '3','1','','1' )
, ( '4','1','','1' )
, ( '5','1','','1' )
, ( '6','1','','1' )
, ( '7','1','<p>\\n\\n	Der Erste Artikel in dieser Brandneuen Goma-Installation.</p>\\n\\n','1' )
, ( '8','1','','1' )
, ( '9','1','','1' )
, ( '10','1','','1' )
, ( '11','1','','1' )
, ( '12','1','','1' )
, ( '13','1','','1' )
, ( '14','1','','1' )
; 





