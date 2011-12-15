-- Table boxes_state

DROP TABLE IF EXISTS {!#PREFIX}boxes_state;
  -- Create 
CREATE TABLE {!#PREFIX}boxes_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

-- INSERT 
 INSERT INTO {!#PREFIX}boxes_state (`id` , `stateid` , `publishedid`) VALUES  ( '1','10','10' )
, ( '2','13','13' )
, ( '3','9','9' )
; 





