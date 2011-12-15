-- Table newsettings_state

DROP TABLE IF EXISTS {!#PREFIX}newsettings_state;
  -- Create 
CREATE TABLE {!#PREFIX}newsettings_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

-- INSERT 
 INSERT INTO {!#PREFIX}newsettings_state (`id` , `stateid` , `publishedid`) VALUES  ( '1','29','29' )
; 





