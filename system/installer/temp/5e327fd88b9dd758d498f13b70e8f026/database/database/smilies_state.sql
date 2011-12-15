-- Table smilies_state

DROP TABLE IF EXISTS {!#PREFIX}smilies_state;
  -- Create 
CREATE TABLE {!#PREFIX}smilies_state (
 `id` int(10) NOT NULL auto_increment,
 `stateid` int(10) NOT NULL,
 `publishedid` int(10) NOT NULL,
PRIMARY KEY (id),
KEY `publishedid` (publishedid),
KEY `stateid` (stateid));

-- INSERT 
 INSERT INTO {!#PREFIX}smilies_state (`id` , `stateid` , `publishedid`) VALUES  ( '1','1','1' )
, ( '2','2','2' )
, ( '3','3','3' )
, ( '4','4','4' )
, ( '5','5','5' )
, ( '6','6','6' )
, ( '7','7','7' )
, ( '8','8','8' )
, ( '9','9','9' )
; 





