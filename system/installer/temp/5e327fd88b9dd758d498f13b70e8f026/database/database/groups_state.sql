-- Table groups_state

DROP TABLE IF EXISTS {!#PREFIX}groups_state;
  -- Create 
CREATE TABLE {!#PREFIX}groups_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

-- INSERT 
 INSERT INTO {!#PREFIX}groups_state (`id` , `stateid` , `publishedid`) VALUES  ( '1','1','1' )
, ( '3','3','3' )
, ( '5','5','5' )
; 





