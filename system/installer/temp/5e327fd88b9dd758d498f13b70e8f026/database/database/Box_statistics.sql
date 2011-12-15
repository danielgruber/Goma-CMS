-- Table Box_statistics

DROP TABLE IF EXISTS {!#PREFIX}Box_statistics;
  -- Create 
CREATE TABLE {!#PREFIX}Box_statistics (
 `id` int(10) NOT NULL auto_increment,
 `autorid` int(10) NOT NULL,
 `today` int(1) NOT NULL,
 `last2` int(1) NOT NULL,
 `last30d` int(1) NOT NULL,
 `whole` int(1) NOT NULL,
 `online` int(1) NOT NULL,
 `editorid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `autorid` (autorid),
KEY `editorid` (editorid));

-- INSERT 
 INSERT INTO {!#PREFIX}Box_statistics (`id` , `autorid` , `today` , `last2` , `last30d` , `whole` , `online` , `editorid`) VALUES  ( '13','1','0','0','0','0','0','1' )
, ( '9','1','0','0','0','0','0','1' )
, ( '10','1','0','0','0','0','0','1' )
; 





