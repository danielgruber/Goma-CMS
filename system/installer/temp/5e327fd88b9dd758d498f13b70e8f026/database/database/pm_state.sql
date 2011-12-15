-- Table pm_state

DROP TABLE IF EXISTS {!#PREFIX}pm_state;
  -- Create 
CREATE TABLE {!#PREFIX}pm_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

-- INSERT 
 INSERT INTO {!#PREFIX}pm_state (`id` , `stateid` , `publishedid`) VALUES  ( '1','1','1' )
, ( '2','1','1' )
; 





